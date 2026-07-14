# Filament Bug Reports

Collect bug reports from inside your Filament panel, and push the ones you confirm are real straight to GitHub as issues.

Your users get a **Report a bug** button in the panel's topbar and a short, plain-language form — no Markdown, no issue templates, no GitHub account. You get a triage table where a single click turns a report into a proper GitHub issue, and the issue's state (open/closed) is mirrored back onto the report automatically.

![Triage table](art/triage-table.png)

## How it works

1. **Anyone in the panel reports a bug.** They describe the problem, list the steps that led to it, and optionally attach a screenshot. The report is stamped with the reporter, their role and the running app version — they aren't asked for any of it.
2. **A manager triages.** Reports land in a table only managers can see. Noise gets deleted; the real ones get **Mark as real**.
3. **A GitHub issue is created**, with the steps and screenshot formatted into the body. The issue number and URL are stored on the report.
4. **State syncs back.** An hourly command checks each linked issue: closed becomes *Resolved*, reopened flips back to *In progress*.

## Requirements

- PHP 8.3+
- Laravel 13.x
- Filament 5.x

## Installation

```bash
composer require cerealkiller97/filament-bug-reports
```

Publish and run the migration:

```bash
php artisan vendor:publish --tag=bug-reports-migrations
php artisan migrate
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=bug-reports-config
```

Then register the plugin on the panel you want it in:

```php
use CerealKiller97\FilamentBugReports\BugReportsPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(
            BugReportsPlugin::make()
                ->authorizeManagementUsing(fn (User $user): bool => $user->isAdmin()),
        );
}
```

That single `authorizeManagementUsing` call matters: **management defaults to nobody**. Until you opt someone in, no one can see the report list — though everyone can still file a report.

## Who can do what

| | Report a bug | See the list, view, triage, delete |
|---|---|---|
| **Default** | any authenticated panel user | nobody |
| **Configured with** | `authorizeReportingUsing()` | `authorizeManagementUsing()` |

```php
BugReportsPlugin::make()
    // Who may triage reports and push them to GitHub. Default: nobody.
    ->authorizeManagementUsing(fn (User $user): bool => $user->hasRole('developer'))

    // Who may file a report. Default: every authenticated user.
    ->authorizeReportingUsing(fn (User $user): bool => $user->isStaff())

    // A label stored alongside the report, so you know who hit the bug.
    // Shows under the reporter's name in the table and in the GitHub issue.
    ->resolveReporterRoleUsing(fn (User $user): string => $user->role->label());
```

Non-managers never see the resource in the navigation, and the list page returns a 403 for them. They only ever reach the create form — via the topbar button — and are redirected back to the panel home after submitting.

## Reporting a bug

The **Report a bug** button is injected into the topbar next to global search, so it's reachable from every page in the panel.

![Report form](art/report-form.png)

Steps are a repeater — reporters add and reorder them one at a time, which tends to produce far better reproduction steps than a free-text box. The screenshot is optional and capped at 5 MB by default.

## Triaging and creating the issue

**Mark as real** asks for confirmation, then creates the GitHub issue:

![Mark as real](art/mark-as-real.png)

The action is idempotent and disappears once a report is linked, so a report can't produce two issues. If GitHub rejects the call, the error is surfaced in a notification and nothing is written locally.

The issue body is assembled from the report:

```markdown
## Details
**Reported by:** Marcus Reed (user)
**App version:** 2.7.1
**Reported at:** 11.07.2026. 10:45

## Steps to reproduce
1. Opened the "Billing" page
2. Added the coupon SPRING25 to an invoice of $400
3. The total showed $400 instead of $300

## Screenshot
https://acme.test/storage/bug-reports/invoice-total.png

---
_Automatically created from in-app bug report #12._
```

The screenshot is embedded as a URL, so GitHub can only render it if the disk you store it on is publicly reachable.

## Configuration

Point the package at a repository and give it a token with the `repo` (or `issues:write`) scope:

```dotenv
BUG_REPORTS_GITHUB_TOKEN=ghp_xxxxxxxxxxxx
BUG_REPORTS_GITHUB_REPOSITORY=acme/platform
```

Both fall back to `GITHUB_TOKEN` and `GITHUB_BUG_REPOSITORY` if you already have those set. Everything else lives in `config/bug-reports.php`:

```php
'user_model' => \App\Models\User::class,

// Stamped onto every report. Falls back to the app's `app.version`.
'app_version' => env('BUG_REPORTS_APP_VERSION'),

'screenshot' => [
    'disk' => env('BUG_REPORTS_SCREENSHOT_DISK', 'public'),
    'directory' => env('BUG_REPORTS_SCREENSHOT_DIRECTORY', 'bug-reports'),
    'max_size' => 5120, // KB
],

'github' => [
    'labels' => ['bug'],          // applied to every created issue
    'assignees' => [],
    'title_prefix' => '[In App] ',
],

'sync' => [
    'enabled' => true,
    'frequency' => 'hourly',      // any Laravel Schedule method name
],
```

## Keeping reports in sync

While `sync.enabled` is true, the package schedules itself — no entry in your `routes/console.php` needed. `frequency` accepts any method on Laravel's `Schedule` (`everyFifteenMinutes`, `daily`, …).

You can also run it by hand, or from the **Sync with GitHub** button on the list page:

```bash
php artisan bug-reports:sync
```

Each linked issue is fetched and mirrored: a closed issue sets `resolved_at` to the issue's `closed_at`, reopening clears it. Issues that have been deleted from GitHub (404) are skipped rather than failing the run.

If you'd rather schedule it yourself, set `sync.enabled` to `false` and call `bug-reports:sync` from your own scheduler.

## Translations

Ships with English (`en`) and Serbian (`sr`). To change any wording:

```bash
php artisan vendor:publish --tag=bug-reports-translations
```

## Testing

```bash
composer test
```

## License

Apache License 2.0. See [LICENSE.md](LICENSE.md).
