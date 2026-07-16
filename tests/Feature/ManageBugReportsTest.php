<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Enums\BugPriority;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\ListBugReports;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Widgets\BugReportsStatsWidget;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

test('a manager sees all reports in the list', function (): void {
    actingAs(makeUser(true));
    $reports = BugReport::factory()->count(3)->create();

    livewire(ListBugReports::class)
        ->call('loadTable')
        ->assertCanSeeTableRecords($reports);
});

test('a non-manager cannot reach the report list', function (): void {
    actingAs(makeUser(false));

    $this->get(BugReportResource::getUrl('index'))->assertForbidden();
});

test('a non-manager cannot view a single report', function (): void {
    actingAs(makeUser(false));
    $report = BugReport::factory()->create();

    $this->get(BugReportResource::getUrl('view', ['record' => $report]))->assertForbidden();
});

test('a manager marks a bug as real, creating a labelled github issue', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');

    Http::fake([
        'api.github.com/*' => Http::response([
            'html_url' => 'https://github.com/acme/repo/issues/7',
            'number' => 7,
        ], 201),
    ]);

    actingAs(makeUser(true));
    $report = BugReport::factory()->create(['title' => 'Boom', 'steps' => ['Step one']]);

    livewire(ListBugReports::class)
        ->callTableAction('markAsReal', $report, ['priority' => 'high']);

    $report->refresh();

    expect($report->github_issue_url)->toBe('https://github.com/acme/repo/issues/7')
        ->and($report->validated_at)->not->toBeNull()
        ->and($report->priority)->toBe(BugPriority::High);

    Http::assertSent(fn (Request $request): bool => $request['title'] === '[In App] Boom'
        && $request['labels'] === ['bug', 'priority: high']);
});

test('actions that change a report tell the stats to recount', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/8', 'number' => 8], 201),
        'api.github.com/repos/acme/repo/issues/*' => Http::response(['state' => 'open'], 200),
    ]);

    actingAs(makeUser(true));
    $report = BugReport::factory()->create();

    // The widget is a separate Livewire component: without the event it keeps
    // showing the counts from before the click.
    livewire(ListBugReports::class)
        ->callTableAction('markAsReal', $report, ['priority' => 'low'])
        ->assertDispatched(BugReportsStatsWidget::REFRESH_EVENT);

    livewire(ListBugReports::class)
        ->callAction('syncGithub')
        ->assertDispatched(BugReportsStatsWidget::REFRESH_EVENT);

    livewire(ListBugReports::class)
        ->callTableAction('delete', $report)
        ->assertDispatched(BugReportsStatsWidget::REFRESH_EVENT);
});

test('the priority column sorts by urgency, not alphabetically', function (): void {
    actingAs(makeUser(true));

    // Created out of order, and alphabetically these would come back as
    // high, low, medium, urgent.
    $medium = BugReport::factory()->create(['priority' => BugPriority::Medium]);
    $urgent = BugReport::factory()->create(['priority' => BugPriority::Urgent]);
    $untriaged = BugReport::factory()->create(['priority' => null]);
    $low = BugReport::factory()->create(['priority' => BugPriority::Low]);
    $high = BugReport::factory()->create(['priority' => BugPriority::High]);

    livewire(ListBugReports::class)
        ->call('loadTable')
        ->sortTable('priority', 'desc')
        ->assertCanSeeTableRecords([$urgent, $high, $medium, $low, $untriaged], inOrder: true)
        ->sortTable('priority', 'asc')
        ->assertCanSeeTableRecords([$untriaged, $low, $medium, $high, $urgent], inOrder: true);
});

test('grouping by priority orders the groups by urgency and names the untriaged one', function (): void {
    actingAs(makeUser(true));

    $urgent = BugReport::factory()->create(['priority' => BugPriority::Urgent]);
    $untriaged = BugReport::factory()->create(['priority' => null]);
    $low = BugReport::factory()->create(['priority' => BugPriority::Low]);

    livewire(ListBugReports::class)
        ->call('loadTable')
        ->set('tableGrouping', 'priority')
        ->assertCanSeeTableRecords([$untriaged, $low, $urgent], inOrder: true)
        // A report with no priority still needs a group heading.
        ->assertSee('Not triaged');
});

test('an unchosen priority falls back to low rather than blocking the triage', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.labels', ['bug']);

    Http::fake([
        'api.github.com/*' => Http::response([
            'html_url' => 'https://github.com/acme/repo/issues/9',
            'number' => 9,
        ], 201),
    ]);

    actingAs(makeUser(true));
    $report = BugReport::factory()->create();

    livewire(ListBugReports::class)
        ->callTableAction('markAsReal', $report, ['priority' => null])
        ->assertHasNoTableActionErrors();

    expect($report->refresh()->priority)->toBe(BugPriority::Low);

    Http::assertSent(fn (Request $request): bool => $request['labels'] === ['bug', 'priority: low']);
});

test('the mark as real action is hidden once the report is validated', function (): void {
    actingAs(makeUser(true));
    $report = BugReport::factory()->validated()->create();

    livewire(ListBugReports::class)
        ->assertTableActionHidden('markAsReal', $report);
});

test('the sync header button pulls issue state from github', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');

    Http::fake([
        'api.github.com/repos/acme/repo/issues/21' => Http::response([
            'state' => 'closed',
            'closed_at' => '2026-07-13T08:00:00Z',
        ], 200),
    ]);

    actingAs(makeUser(true));
    $report = BugReport::factory()->validated()->create(['github_issue_number' => 21]);

    livewire(ListBugReports::class)
        ->callAction('syncGithub');

    expect($report->refresh()->resolved_at)->not->toBeNull();
});

test('a manager can delete a report', function (): void {
    actingAs(makeUser(true));
    $report = BugReport::factory()->create();

    livewire(ListBugReports::class)
        ->callTableAction('delete', $report);

    expect(BugReport::query()->find($report->id))->toBeNull();
});
