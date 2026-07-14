#!/usr/bin/env bash
#
# Cut a SemVer release of filament-bug-reports.
#
#   ./release.sh patch          0.1.3 -> 0.1.4   backwards-compatible bug fixes
#   ./release.sh minor          0.1.3 -> 0.2.0   backwards-compatible features
#   ./release.sh major          0.1.3 -> 1.0.0   incompatible API changes
#   ./release.sh 1.4.2                           an explicit version
#
#   --dry-run       Print every step, change nothing, push nothing.
#   --skip-checks   Skip lint/analyse/tests/type-coverage (not recommended).
#
# What it does, in order:
#   1. Refuses to run unless you are on main, clean, and in sync with origin.
#   2. Runs the same gates CI runs: pint, phpstan, pest, type coverage.
#   3. Rolls CHANGELOG.md's [Unreleased] into the new version, dated today,
#      and opens a fresh [Unreleased].
#   4. Stamps the new version into the README's Packagist badge.
#   5. Commits, creates an annotated tag, and pushes both.
#   6. Creates the GitHub release (if `gh` is available).
#
# Packagist publishes the tag itself, via its GitHub service hook. There is
# nothing to do here for that.
#
set -euo pipefail

readonly BRANCH="main"
readonly REPO="CerealKiller97/filament-bug-reports"
readonly PACKAGE="cerealkiller97/filament-bug-reports"
readonly CHANGELOG="CHANGELOG.md"
readonly README="README.md"

# The only headings a release section may use (Keep a Changelog 1.1.0).
readonly ALLOWED_HEADINGS=(
    "### ✨ Added"
    "### 🔄 Changed"
    "### ⚠️ Deprecated"
    "### 🗑️ Removed"
    "### 🐛 Fixed"
    "### 🔒 Security"
)

DRY_RUN=false
SKIP_CHECKS=false
BUMP=""

bold()  { printf '\033[1m%s\033[0m\n' "$*"; }
info()  { printf '\033[34m•\033[0m %s\n' "$*"; }
ok()    { printf '\033[32m✓\033[0m %s\n' "$*"; }
warn()  { printf '\033[33m!\033[0m %s\n' "$*"; }
die()   { printf '\033[31m✗ %s\033[0m\n' "$*" >&2; exit 1; }

run() {
    if $DRY_RUN; then
        printf '\033[90m  would run: %s\033[0m\n' "$*"
    else
        "$@"
    fi
}

usage() {
    # Print the header comment block (everything after the shebang, up to the
    # first line of actual code).
    awk 'NR == 1 { next } /^#/ { sub(/^#[[:space:]]?/, ""); print; next } { exit }' "$0"
    exit "${1:-0}"
}

# ---------------------------------------------------------------- arguments

while [[ $# -gt 0 ]]; do
    case "$1" in
        major|minor|patch)          BUMP="$1" ;;
        [0-9]*.[0-9]*.[0-9]*)       BUMP="$1" ;;
        --dry-run)                  DRY_RUN=true ;;
        --skip-checks)              SKIP_CHECKS=true ;;
        -h|--help)                  usage 0 ;;
        *)                          die "Unknown argument: $1 (try --help)" ;;
    esac
    shift
done

[[ -n "$BUMP" ]] || usage 1

cd "$(dirname "$0")"

# ---------------------------------------------------------------- preflight

$DRY_RUN && bold "DRY RUN — nothing will be written, committed or pushed."

command -v git >/dev/null || die "git is required."
[[ -f composer.json ]] || die "Run this from the package root."

current_branch="$(git rev-parse --abbrev-ref HEAD)"
[[ "$current_branch" == "$BRANCH" ]] \
    || die "You are on '$current_branch'. Releases are cut from '$BRANCH'."

[[ -z "$(git status --porcelain)" ]] \
    || die "Working tree is dirty. Commit or stash first."

info "Fetching origin…"
# --no-prune/--no-prune-tags: a user with fetch.pruneTags=true in their global
# config would otherwise have local-only tags deleted out from under them here,
# which would silently reset our idea of the current version.
git fetch --quiet --no-prune --no-prune-tags --tags origin

local_head="$(git rev-parse @)"
remote_head="$(git rev-parse "origin/$BRANCH")"
[[ "$local_head" == "$remote_head" ]] \
    || die "Local '$BRANCH' differs from origin/$BRANCH. Pull or push first."

# ---------------------------------------------------------------- versioning

previous="$(git describe --tags --abbrev=0 --match 'v[0-9]*.[0-9]*.[0-9]*' 2>/dev/null || echo 'v0.0.0')"
prev_number="${previous#v}"

IFS='.' read -r major minor patch <<< "$prev_number"

case "$BUMP" in
    major)  version="$((major + 1)).0.0" ;;
    minor)  version="${major}.$((minor + 1)).0" ;;
    patch)  version="${major}.${minor}.$((patch + 1))" ;;
    *)      version="$BUMP" ;;
esac

[[ "$version" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]] \
    || die "'$version' is not a valid SemVer version (X.Y.Z)."

tag="v${version}"

git rev-parse -q --verify "refs/tags/$tag" >/dev/null \
    && die "Tag $tag already exists."

# Refuse to go backwards.
if [[ "$prev_number" != "0.0.0" ]]; then
    lowest="$(printf '%s\n%s\n' "$prev_number" "$version" | sort -t. -k1,1n -k2,2n -k3,3n | head -1)"
    [[ "$lowest" == "$prev_number" && "$version" != "$prev_number" ]] \
        || die "$version does not come after $prev_number."
fi

bold "Releasing ${previous} → ${tag}"

# ---------------------------------------------------------------- changelog

[[ -f "$CHANGELOG" ]] || die "$CHANGELOG not found."

grep -q '^## \[Unreleased\]' "$CHANGELOG" \
    || die "$CHANGELOG has no '## [Unreleased]' section."

# Everything between [Unreleased] and the next '## ' heading (or the link refs).
notes="$(awk '
    /^## \[Unreleased\]/ { collecting = 1; next }
    collecting && /^## / { exit }
    collecting && /^\[Unreleased\]:/ { exit }
    collecting { print }
' "$CHANGELOG" | sed -e 's/[[:space:]]*$//')"

# Trim leading/trailing blank lines.
notes="$(printf '%s\n' "$notes" | awk 'NF {found = 1} found' | tac | awk 'NF {found = 1} found' | tac)"

[[ -n "$notes" ]] \
    || die "Nothing under [Unreleased] in $CHANGELOG. Describe the release first."

# Every heading used must be one of the six Keep a Changelog categories.
while IFS= read -r heading; do
    [[ -n "$heading" ]] || continue
    allowed=false
    for candidate in "${ALLOWED_HEADINGS[@]}"; do
        [[ "$heading" == "$candidate" ]] && allowed=true && break
    done
    $allowed || die "Unknown heading in [Unreleased]: '$heading'
Allowed: $(printf '%s; ' "${ALLOWED_HEADINGS[@]}")"
done < <(printf '%s\n' "$notes" | grep '^### ' || true)

today="$(date +%Y-%m-%d)"

info "Release notes:"
printf '%s\n' "$notes" | sed 's/^/    /'

if [[ "$prev_number" == "0.0.0" ]]; then
    new_link="[${version}]: https://github.com/${REPO}/releases/tag/${tag}"
else
    new_link="[${version}]: https://github.com/${REPO}/compare/${previous}...${tag}"
fi

if ! $DRY_RUN; then
    tmp="$(mktemp)"

    # Open a fresh [Unreleased], and re-head the old one as this version.
    awk -v version="$version" -v today="$today" '
        /^## \[Unreleased\]/ {
            print "## [Unreleased]"
            print ""
            print "## [" version "] - " today
            next
        }
        { print }
    ' "$CHANGELOG" > "$tmp"

    # Rebuild the link references: Unreleased, then newest version first.
    other_links="$(grep -E '^\[[0-9]+\.[0-9]+\.[0-9]+\]:' "$tmp" || true)"
    grep -vE '^\[(Unreleased|[0-9]+\.[0-9]+\.[0-9]+)\]:' "$tmp" \
        | awk 'NF {blank = 0; print; next} {if (!blank) print; blank = 1}' > "${tmp}.body"

    {
        cat "${tmp}.body"
        printf '\n'
        printf '[Unreleased]: https://github.com/%s/compare/%s...HEAD\n' "$REPO" "$tag"
        printf '%s\n' "$new_link"
        [[ -n "$other_links" ]] && printf '%s\n' "$other_links"
    } > "$CHANGELOG"

    rm -f "$tmp" "${tmp}.body"
    ok "$CHANGELOG: [Unreleased] rolled into [${version}] - ${today}"

    # Keep the README's static Packagist badge honest.
    if grep -q 'badge/packagist-v[0-9]' "$README"; then
        perl -pi -e "s{badge/packagist-v[0-9]+\.[0-9]+\.[0-9]+-}{badge/packagist-v${version}-}g" "$README"
        ok "$README: Packagist badge stamped to v${version}"
    fi
else
    info "would roll [Unreleased] into [${version}] - ${today} and stamp the README badge"
fi

# ---------------------------------------------------------------- gates

if $SKIP_CHECKS; then
    warn "Skipping lint, static analysis, tests and type coverage."
else
    info "Installing dependencies…"
    run composer install --no-interaction --no-progress --quiet

    info "composer validate"
    run composer validate --strict --no-check-publish

    info "pint (lint)"
    run vendor/bin/pint --test

    info "phpstan (static analysis)"
    run php -d memory_limit=-1 vendor/bin/phpstan analyse --no-progress

    info "pest (tests)"
    run vendor/bin/pest --ci

    info "pest (type coverage)"
    run php -d memory_limit=-1 vendor/bin/pest --type-coverage --min=100

    ok "All gates passed."
fi

# ---------------------------------------------------------------- confirm

bold ""
bold "About to publish ${tag}:"
echo "  • commit  chore(release): ${tag}"
echo "  • tag     ${tag} (annotated)"
echo "  • push    origin ${BRANCH} + ${tag}"
echo "  • release github.com/${REPO}/releases/tag/${tag}"
echo "  • packagist.org/packages/${PACKAGE} picks it up from the tag"
bold ""

if ! $DRY_RUN; then
    read -r -p "Publish ${tag}? [y/N] " reply
    [[ "$reply" =~ ^[Yy]$ ]] || die "Aborted. (CHANGELOG/README edits are still in your working tree.)"
fi

# ---------------------------------------------------------------- publish

run git add "$CHANGELOG" "$README"
run git commit -m "chore(release): ${tag}"
run git tag -a "$tag" -m "${tag}

${notes}"

run git push origin "$BRANCH"
run git push origin "$tag"

ok "Pushed ${tag}."

# GitHub release — nice to have, not fatal.
if command -v gh >/dev/null 2>&1; then
    info "Creating GitHub release…"
    if $DRY_RUN; then
        printf '\033[90m  would run: gh release create %s --title %s --notes …\033[0m\n' "$tag" "$tag"
    else
        if printf '%s\n' "$notes" | gh release create "$tag" --title "$tag" --notes-file -; then
            ok "GitHub release created."
        else
            warn "Could not create the GitHub release; the tag is pushed, so do it by hand if you want one."
        fi
    fi
else
    warn "gh not found — skipping the GitHub release (the tag is pushed regardless)."
fi

# Packagist needs no nudge from us: its GitHub service hook publishes the tag
# instantly. If the package page ever warns that it is not auto-updated, the
# hook is missing — fix that on Packagist rather than scripting around it, or
# Packagist falls back to crawling the repo only once a week.

bold ""
ok "${tag} released."
echo "   https://packagist.org/packages/${PACKAGE}"
echo "   (published by Packagist's GitHub hook — give it a few seconds)"
