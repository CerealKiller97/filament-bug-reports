<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use CerealKiller97\FilamentBugReports\BugReportsServiceProvider;
use CerealKiller97\FilamentBugReports\Tests\Fixtures\TestPanelProvider;
use CerealKiller97\FilamentBugReports\Tests\Fixtures\User;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Application;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Orchestra declares `$app` untyped, so a native type here narrows the
     * parameter and is a fatal LSP violation. Keep it in the docblock.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,

            // Order matters. Filament's SupportServiceProvider rebinds Livewire's
            // DataStore with a non-shared `bind()`, which drops any previously
            // registered shared instance. Livewire must register *after* it so
            // its `instance()` wins and the store stays a singleton — otherwise
            // every store() lookup returns a fresh DataStore, component error
            // bags read back as null, and every Livewire render blows up. This
            // mirrors real package discovery, where filament/support is
            // registered before livewire/livewire.
            SupportServiceProvider::class,
            LivewireServiceProvider::class,

            ActionsServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            FilamentServiceProvider::class,
            BugReportsServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.version', 'test-version');
        $app['config']->set('bug-reports.user_model', User::class);
        // GitHub unset by default; individual tests configure it.
        $app['config']->set('bug-reports.github.token', 'random-token');
        $app['config']->set('bug-reports.github.repository', 'CerealKiller97/SomeRepo');
        // Don't register the hourly schedule during tests.
        $app['config']->set('bug-reports.sync.enabled', false);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('is_manager')->default(false);
            $table->string('password')->nullable();
            $table->timestamps();
        });

        (include __DIR__.'/../database/migrations/create_bug_reports_table.php.stub')->up();
    }
}
