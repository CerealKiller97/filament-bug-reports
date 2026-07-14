<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Actions;

use CerealKiller97\FilamentBugReports\Actions\CreateBugReportGithubIssue;
use CerealKiller97\FilamentBugReports\BugReportsPlugin;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Throwable;

class MarkBugReportAsRealAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'markAsReal';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('bug-reports::bug-reports.actions.mark_as_real'));

        $this->icon(Heroicon::OutlinedCheckBadge);

        $this->color('success');

        // Only managers triage, and only until a report is pushed to GitHub.
        $this->visible(fn (BugReport $record): bool => BugReportsPlugin::get()->canManage(auth()->user())
            && $record->github_issue_url === null);

        $this->requiresConfirmation();

        $this->modalHeading(__('bug-reports::bug-reports.actions.mark_as_real_heading'));

        $this->modalDescription(__('bug-reports::bug-reports.actions.mark_as_real_description'));

        $this->modalSubmitActionLabel(__('bug-reports::bug-reports.actions.mark_as_real_submit'));

        $this->action(function (BugReport $record): void {
            try {
                $record = resolve(CreateBugReportGithubIssue::class)->handle($record);
            } catch (Throwable $throwable) {
                Notification::make()
                    ->danger()
                    ->title(__('bug-reports::bug-reports.notifications.issue_failed'))
                    ->body($throwable->getMessage())
                    ->send();

                return;
            }

            Notification::make()
                ->success()
                ->title(__('bug-reports::bug-reports.notifications.issue_created'))
                ->body(__('bug-reports::bug-reports.notifications.issue_created_body', ['number' => $record->github_issue_number]))
                ->actions([
                    Action::make('open')
                        ->label(__('bug-reports::bug-reports.actions.open_issue'))
                        ->url((string) $record->github_issue_url)
                        ->openUrlInNewTab(),
                ])
                ->send();
        });
    }
}
