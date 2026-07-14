<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Tables;

use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Actions\MarkBugReportAsRealAction;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BugReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('bug-reports::bug-reports.table.empty'))
            ->deferLoading()
            ->columns([
                TextColumn::make('title')
                    ->label(__('bug-reports::bug-reports.table.problem'))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('github_issue_number')
                    ->label(__('bug-reports::bug-reports.table.github'))
                    ->badge()
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '—' : '#'.$state)
                    ->url(fn (BugReport $record): ?string => $record->github_issue_url)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
                TextColumn::make('resolved_at')
                    ->label(__('bug-reports::bug-reports.table.state'))
                    ->badge()
                    ->state(fn (BugReport $record): string => match (true) {
                        ! $record->isValidated() => '—',
                        $record->isResolved() => (string) __('bug-reports::bug-reports.table.state_resolved'),
                        default => (string) __('bug-reports::bug-reports.table.state_pending'),
                    })
                    ->color(fn (BugReport $record): string => match (true) {
                        ! $record->isValidated() => 'gray',
                        $record->isResolved() => 'success',
                        default => 'warning',
                    })
                    ->icon(fn (BugReport $record): Heroicon => match (true) {
                        ! $record->isValidated() => Heroicon::OutlinedMinus,
                        $record->isResolved() => Heroicon::OutlinedCheckCircle,
                        default => Heroicon::OutlinedWrench,
                    }),
                IconColumn::make('screenshot_path')
                    ->label(__('bug-reports::bug-reports.table.screenshot'))
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedPhoto)
                    ->falseIcon(Heroicon::OutlinedMinus),
                TextColumn::make('app_version')
                    ->label(__('bug-reports::bug-reports.table.version'))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('user.name')
                    ->label(__('bug-reports::bug-reports.table.reported_by'))
                    ->description(fn (BugReport $record): string => $record->role)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('bug-reports::bug-reports.table.reported_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('validated_at')
                    ->label(__('bug-reports::bug-reports.filters.validated'))
                    ->nullable()
                    ->trueLabel(__('bug-reports::bug-reports.filters.validated_true'))
                    ->falseLabel(__('bug-reports::bug-reports.filters.validated_false')),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                MarkBugReportAsRealAction::make(),
                DeleteAction::make()->successNotificationTitle(__('bug-reports::bug-reports.notifications.deleted')),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
