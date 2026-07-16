<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages;

use CerealKiller97\FilamentBugReports\Actions\SyncBugReportGithubIssues;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Widgets\BugReportsStatsWidget;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;
use Throwable;

class ListBugReports extends ListRecords
{
    protected static string $resource = BugReportResource::class;

    /**
     * ListRecords does not authorize by itself, so restrict the list to
     * managers here.
     */
    protected function authorizeAccess(): void
    {
        abort_unless(BugReportResource::canViewAny(), 403);
    }

    /**
     * @return array<class-string<Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [BugReportsStatsWidget::class];
    }

    /**
     * @return array<Action|CreateAction>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('syncGithub')
                ->label(__('bug-reports::bug-reports.actions.sync'))
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->action(function (): void {
                    try {
                        $changed = resolve(SyncBugReportGithubIssues::class)->handle();
                    } catch (Throwable $throwable) {
                        Notification::make()
                            ->danger()
                            ->title(__('bug-reports::bug-reports.notifications.sync_failed'))
                            ->body($throwable->getMessage())
                            ->send();

                        return;
                    }

                    $this->dispatch(BugReportsStatsWidget::REFRESH_EVENT);

                    Notification::make()
                        ->success()
                        ->title(__('bug-reports::bug-reports.notifications.synced'))
                        ->body(__('bug-reports::bug-reports.notifications.synced_body', ['count' => $changed]))
                        ->send();
                }),
            CreateAction::make()
                ->label(__('bug-reports::bug-reports.report_button')),
        ];
    }
}
