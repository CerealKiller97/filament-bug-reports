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
    | When null, the host app's `app.version` is used.
    |
    */

    'app_version' => env('BUG_REPORTS_APP_VERSION'),

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
    */

    'github' => [
        'token' => env('BUG_REPORTS_GITHUB_TOKEN', env('GITHUB_TOKEN')),
        'repository' => env('BUG_REPORTS_GITHUB_REPOSITORY', env('GITHUB_BUG_REPOSITORY')),
        'labels' => ['bug'],
        'assignees' => [],
        'title_prefix' => '[In App] ',
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
