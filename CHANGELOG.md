# Changelog

All notable changes to `filament-bug-reports` will be documented in this file.

The format is based on [Keep a Changelog 1.1.0](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Each release groups its changes under the following headings, and omits any that are empty:

| Heading | Use it for |
| --- | --- |
| ✨ Added | New features. |
| 🔄 Changed | Changes in existing functionality. |
| ⚠️ Deprecated | Soon-to-be removed features. |
| 🗑️ Removed | Now removed features. |
| 🐛 Fixed | Any bug fixes. |
| 🔒 Security | In case of vulnerabilities. |

## [Unreleased]

### ✨ Added

- In-panel bug reporting: a **Report a bug** button in the panel topbar, and a form that captures the problem, step-by-step reproduction and an optional screenshot.
- Manager triage table listing every report, with its GitHub issue, state, app version and reporter.
- **Mark as real** action that creates a GitHub issue from a report and links the issue back to it. The action is idempotent — a report can never produce two issues.
- `bug-reports:sync` command, scheduled hourly by default, mirroring each linked issue's state back onto its report (closed becomes *Resolved*, reopened flips back to *In progress*).
- **Sync with GitHub** header action to run that sync on demand from the list page.
- Permission hooks on the plugin: `authorizeManagementUsing()`, `authorizeReportingUsing()` and `resolveReporterRoleUsing()`. Management defaults to nobody; reporting defaults to every authenticated user.
- Configurable GitHub labels, assignees and issue title prefix.
- Configurable screenshot disk, directory and max upload size.
- English and Serbian translations.

[Unreleased]: https://github.com/CerealKiller97/filament-bug-reports/commits/main
