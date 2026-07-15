<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages;

use CerealKiller97\FilamentBugReports\BugReportsPlugin;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Schemas\BugReportForm;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateBugReport extends CreateRecord
{
    protected static string $resource = BugReportResource::class;

    /**
     * Fixed page heading, overriding Filament's "Create {label}" default which
     * reads awkwardly here — this is a report action, not a CRUD create.
     */
    public function getTitle(): string|Htmlable
    {
        return __('bug-reports::bug-reports.create.title');
    }

    /**
     * Relabel the submit button to match the report framing.
     */
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__('bug-reports::bug-reports.create.actions.create'));
    }

    /**
     * Relabel the "create & create another" button to match the report framing.
     */
    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label(__('bug-reports::bug-reports.create.actions.create_another'));
    }

    /**
     * Match the report framing on the trailing breadcrumb, overriding Filament's
     * "Create" default.
     */
    public function getBreadcrumb(): string
    {
        return __('bug-reports::bug-reports.create.breadcrumb');
    }

    /**
     * The report list is manager-only, so send everyone else back to the
     * dashboard after reporting instead of a page they cannot open.
     */
    protected function getRedirectUrl(): string
    {
        return BugReportsPlugin::get()->canManage(auth()->user())
            ? $this->getResource()::getUrl('index')
            : Filament::getUrl();
    }

    /**
     * Hide the breadcrumb trail for reporters — it links back to the
     * manager-only list.
     *
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return BugReportsPlugin::get()->canManage(auth()->user()) ? parent::getBreadcrumbs() : [];
    }

    /**
     * Stamp every report with the reporter, their role and the running app
     * version — without asking them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        $data['user_id'] = $user?->getAuthIdentifier();
        $data['role'] = BugReportsPlugin::get()->resolveReporterRole($user);
        $data['app_version'] = BugReportForm::appVersion();

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('bug-reports::bug-reports.notifications.reported'));
    }
}
