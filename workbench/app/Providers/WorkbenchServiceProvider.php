<?php

namespace Workbench\App\Providers;

use CerealKiller97\FilamentBugReports\Tests\Fixtures\User;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Point the package at the fixture User (has the is_manager flag the
        // authorization callbacks read) and stamp a version onto new reports.
        config()->set('bug-reports.user_model', User::class);
        config()->set('app.version', 'workbench-dev');
    }

    public function boot(): void
    {
        // Workbench migrations aren't auto-discovered; register them so
        // migrate:fresh builds the users tweak + bug_reports table.
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
