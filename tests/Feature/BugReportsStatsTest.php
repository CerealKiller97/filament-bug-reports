<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Enums\BugPriority;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Widgets\BugReportsStatsWidget;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Filament\Widgets\StatsOverviewWidget\Stat;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

/**
 * getStats() is protected, so reach it the way the widget itself would.
 *
 * @return array<int, int|string>
 */
function statValues(): array
{
    $widget = livewire(BugReportsStatsWidget::class)->instance();

    /** @var array<int, Stat> $stats */
    $stats = (fn (): array => $this->getStats())->call($widget);

    return array_map(fn (Stat $stat) => $stat->getValue(), $stats);
}

test('the stats count each stage of triage', function (): void {
    actingAs(makeUser(true));

    // Awaiting triage: never pushed to GitHub.
    BugReport::factory()->count(2)->create();

    // In progress: pushed, still open. One of them is on fire.
    BugReport::factory()->validated()->create(['priority' => BugPriority::Urgent, 'resolved_at' => null]);
    BugReport::factory()->validated()->create(['priority' => BugPriority::Low, 'resolved_at' => null]);

    // Resolved: the issue was closed. High, but no longer hurting.
    BugReport::factory()->validated()->create(['priority' => BugPriority::High, 'resolved_at' => now()]);

    livewire(BugReportsStatsWidget::class)
        ->assertSee('Awaiting triage')
        ->assertSee('Urgent and high')
        ->assertSee('In progress')
        ->assertSee('Resolved');

    // Awaiting triage, urgent+high still open, in progress, resolved.
    expect(statValues())->toBe([2, 1, 2, 1]);
});

test('the stats recount when told the data went stale', function (): void {
    actingAs(makeUser(true));

    $report = BugReport::factory()->create();

    $widget = livewire(BugReportsStatsWidget::class)
        ->assertSee('Awaiting triage');

    // Something else on the page changes a report...
    $report->forceFill(['github_issue_url' => 'https://github.com/acme/repo/issues/1', 'github_issue_number' => 1])->save();

    // ...and the widget is told, rather than sitting on a stale count.
    $widget->dispatch(BugReportsStatsWidget::REFRESH_EVENT);

    $stats = (fn (): array => $this->getStats())->call($widget->instance());

    expect($stats[0]->getValue())->toBe(0)
        ->and($stats[2]->getValue())->toBe(1);
});

test('a resolved report is not counted as burning even when it is urgent', function (): void {
    actingAs(makeUser(true));

    BugReport::factory()->validated()->create(['priority' => BugPriority::Urgent, 'resolved_at' => now()]);

    expect(statValues()[1])->toBe(0);
});
