<?php

namespace Workbench\App\Providers;

use CerealKiller97\FilamentBugReports\BugReportsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Contracts\Auth\Authenticatable as AuthUser;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Workbench\App\Http\Middleware\SetLocale;

/**
 * The panel served by `composer serve`. Mirrors the test fixture panel but adds
 * the SetLocale middleware so translations can be switched from the browser.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->plugin(
                BugReportsPlugin::make()
                    ->authorizeManagementUsing(fn (AuthUser $user): bool => (bool) $user->getAttribute('is_manager'))
                    ->authorizeReportingUsing(fn (AuthUser $user): bool => true)
                    ->resolveReporterRoleUsing(fn (AuthUser $user): string => $user->getAttribute('is_manager') ? 'manager' : 'user')
            )
            ->login()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
