<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Widgets;

use CerealKiller97\FilamentBugReports\Enums\BugPriority;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class BugReportsStatsWidget extends StatsOverviewWidget
{
    /**
     * A widget is its own Livewire component, so nothing on the page tells it
     * that a report changed — it would happily show counts from before the
     * click until something forced a re-render. Everything that mutates a
     * report dispatches this instead of the stats polling for changes.
     */
    public const string REFRESH_EVENT = 'bug-reports::stats-stale';

    protected ?string $pollingInterval = null;

    /**
     * Livewire re-renders a component after a listener runs, and the stats are
     * recounted on render — so receiving the event is the whole job.
     */
    #[On(self::REFRESH_EVENT)]
    public function refreshStats(): void {}

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        // One pass over the table rather than four count queries.
        $counts = BugReport::query()
            ->selectRaw('count(*) as total')
            ->selectRaw('sum(case when github_issue_url is null then 1 else 0 end) as untriaged')
            ->selectRaw('sum(case when github_issue_url is not null and resolved_at is null then 1 else 0 end) as in_progress')
            ->selectRaw('sum(case when resolved_at is not null then 1 else 0 end) as resolved')
            ->selectRaw('sum(case when resolved_at is null and priority in (?, ?) then 1 else 0 end) as burning', [
                BugPriority::Urgent->value,
                BugPriority::High->value,
            ])
            ->first();

        $untriaged = (int) ($counts?->getAttribute('untriaged') ?? 0);
        $burning = (int) ($counts?->getAttribute('burning') ?? 0);

        return [
            Stat::make(__('bug-reports::bug-reports.stats.untriaged'), $untriaged)
                ->description(__('bug-reports::bug-reports.stats.untriaged_description'))
                ->descriptionIcon(Heroicon::OutlinedInbox)
                // Only worth shouting about when there is actually a queue.
                ->color($untriaged > 0 ? 'warning' : 'gray'),

            Stat::make(__('bug-reports::bug-reports.stats.burning'), $burning)
                ->description(__('bug-reports::bug-reports.stats.burning_description'))
                ->descriptionIcon(Heroicon::OutlinedFire)
                ->color($burning > 0 ? 'danger' : 'gray'),

            Stat::make(__('bug-reports::bug-reports.stats.in_progress'), (int) ($counts?->getAttribute('in_progress') ?? 0))
                ->description(__('bug-reports::bug-reports.stats.in_progress_description'))
                ->descriptionIcon(Heroicon::OutlinedWrench)
                ->color('info'),

            Stat::make(__('bug-reports::bug-reports.stats.resolved'), (int) ($counts?->getAttribute('resolved') ?? 0))
                ->description(__('bug-reports::bug-reports.stats.resolved_description'))
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),
        ];
    }
}
