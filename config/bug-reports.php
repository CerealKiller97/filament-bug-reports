<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | The model a bug report belongs to (the reporter). Defaults to the
    | framework's base authenticatable user.
    |
    */

    'user_model' => \Illuminate\Foundation\Auth\User::class,

    /*
    |--------------------------------------------------------------------------
    | App version
    |--------------------------------------------------------------------------
    |
    | Stamped onto every report so you know which build a bug was hit on.
    | When empty, the host app's `app.version` is used.
    |
    | Note the '' fallback: these values are read with `config()->string()`,
    | which throws on null. "Not configured" is an empty string here, never null.
    |
    */

    'app_version' => env('BUG_REPORTS_APP_VERSION', ''),

    /*
    |--------------------------------------------------------------------------
    | Screenshot upload
    |--------------------------------------------------------------------------
    */

    'screenshot' => [
        'disk' => env('BUG_REPORTS_SCREENSHOT_DISK', 'public'),
        'directory' => env('BUG_REPORTS_SCREENSHOT_DIRECTORY', 'bug-reports'),
        'max_size' => 5120, // KB
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub integration
    |--------------------------------------------------------------------------
    |
    | Where validated bug reports are pushed as issues. A token with `repo`
    | (or `issues:write`) scope and a target "owner/repo" are required.
    |
    | Empty (never null) when unset — see the note on `app_version`. An empty
    | token or repository is what raises the "GitHub is not configured" error.
    |
    */

    'github' => [
        'token' => env('BUG_REPORTS_GITHUB_TOKEN', env('GITHUB_TOKEN', '')),
        'repository' => env('BUG_REPORTS_GITHUB_REPOSITORY', env('GITHUB_BUG_REPOSITORY', '')),
        'labels' => ['bug'],
        'assignees' => [],
        'title_prefix' => '[In App] ',

        /*
        | The rest of the options GitHub's "create an issue" endpoint accepts.
        | Each is omitted from the request when left empty, so GitHub applies
        | its own default. A token without push access to the repository has
        | labels, assignees, milestone and type silently dropped by GitHub —
        | the issue is still created, just bare.
        |
        | https://docs.github.com/en/rest/issues/issues#create-an-issue
        */

        // The milestone's number (as shown in its URL), not its title.
        'milestone' => env('BUG_REPORTS_GITHUB_MILESTONE', ''),

        // The name of an issue type, e.g. 'Bug'. Organisation repositories only.
        'type' => env('BUG_REPORTS_GITHUB_TYPE', 'Bug'),

        // Issue field values, for organisation repositories that have them
        // enabled: [['field_id' => 123, 'value' => 'Platform'], ...].
        'issue_field_values' => [],

        /*
        | The label added alongside the ones above, based on the priority a
        | manager picks when marking a report as real. Drop a key (or set it
        | to '') to add no label for that priority.
        */

        'priority_labels' => [
            'low' => 'priority: low',
            'medium' => 'priority: medium',
            'high' => 'priority: high',
            'urgent' => 'priority: urgent',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync
    |--------------------------------------------------------------------------
    |
    | The package can automatically pull issue state back into reports (open =
    | in progress, closed = resolved). Set `enabled` to false to schedule it
    | yourself, or change `frequency` to any Schedule method name.
    |
    */

    'sync' => [
        'enabled' => true,
        'frequency' => 'hourly',
    ],

];
