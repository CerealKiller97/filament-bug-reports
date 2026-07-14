<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages;

use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Actions\MarkBugReportAsRealAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBugReport extends ViewRecord
{
    protected static string $resource = BugReportResource::class;

    /**
     * @return array<MarkBugReportAsRealAction|DeleteAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            MarkBugReportAsRealAction::make(),
            DeleteAction::make()
                ->label(__('bug-reports::bug-reports.actions.delete'))
                ->successNotificationTitle(__('bug-reports::bug-reports.notifications.deleted')),
        ];
    }
}
