<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports;

use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Blade;

final class BugReportsPlugin implements Plugin
{
    /** @var (Closure(Authenticatable): bool)|null */
    protected ?Closure $authorizeManagementUsing = null;

    /** @var (Closure(Authenticatable): bool)|null */
    protected ?Closure $authorizeReportingUsing = null;

    /** @var (Closure(Authenticatable): string)|null */
    protected ?Closure $resolveReporterRoleUsing = null;

    public function getId(): string
    {
        return 'bug-reports';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            BugReportResource::class,
        ]);

        $panel->renderHook(
            PanelsRenderHook::GLOBAL_SEARCH_AFTER,
            fn (): string => $this->renderReportButton(),
        );
    }

    public function boot(Panel $panel): void
    {
        //
    }

    /**
     * Who may list, view, triage and delete reports (e.g. your dev admin).
     * Default: nobody — the host must opt in.
     *
     * @param  Closure(Authenticatable): bool  $callback
     */
    public function authorizeManagementUsing(Closure $callback): static
    {
        $this->authorizeManagementUsing = $callback;

        return $this;
    }

    /**
     * Who may report a bug. Default: any authenticated user.
     *
     * @param  Closure(Authenticatable): bool  $callback
     */
    public function authorizeReportingUsing(Closure $callback): static
    {
        $this->authorizeReportingUsing = $callback;

        return $this;
    }

    /**
     * How to derive the reporter's "role" label stored on the report.
     * Default: empty string.
     *
     * @param  Closure(Authenticatable): string  $callback
     */
    public function resolveReporterRoleUsing(Closure $callback): static
    {
        $this->resolveReporterRoleUsing = $callback;

        return $this;
    }

    public function canManage(?Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->authorizeManagementUsing !== null
            && (bool) ($this->authorizeManagementUsing)($user);
    }

    public function canReport(?Authenticatable $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $this->authorizeReportingUsing !== null
            ? (bool) ($this->authorizeReportingUsing)($user)
            : true;
    }

    public function resolveReporterRole(?Authenticatable $user): string
    {
        if ($user === null) {
            return '';
        }

        return $this->resolveReporterRoleUsing !== null
            ? (string) ($this->resolveReporterRoleUsing)($user)
            : '';
    }

    protected function renderReportButton(): string
    {
        if (! $this->canReport(auth()->user())) {
            return '';
        }

        return Blade::render(
            '<x-filament::button tag="a" :href="$href" icon="heroicon-o-bug-ant" color="gray" size="sm" class="me-2">{{ $label }}</x-filament::button>',
            [
                'href' => BugReportResource::getUrl('create'),
                'label' => __('bug-reports::bug-reports.report_button'),
            ],
        );
    }
}
