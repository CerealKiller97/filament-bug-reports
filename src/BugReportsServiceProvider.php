<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports;

use CerealKiller97\FilamentBugReports\Commands\SyncBugReportsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class BugReportsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bug-reports')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasMigrations(['create_bug_reports_table'])
            ->hasRoute('webhooks')
            ->hasCommand(SyncBugReportsCommand::class);
    }

    public function packageBooted(): void
    {
        $this->app->booted(function (): void {
            if (config()->boolean('bug-reports.sync.enabled') !== true) {
                return;
            }

            $schedule = $this->app->make(Schedule::class);

            $event = $schedule->command('bug-reports:sync')
                ->onOneServer()
                ->runInBackground();

            $frequency = config()->string('bug-reports.sync.frequency', 'hourly');

            method_exists($event, $frequency) ? $event->{$frequency}() : $event->hourly();
        });
    }
}
