<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\ListBugReports;
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
        ->callTableAction('markAsReal', $report);

    $report->refresh();

    expect($report->github_issue_url)->toBe('https://github.com/acme/repo/issues/7')
        ->and($report->validated_at)->not->toBeNull();

    Http::assertSent(fn (Request $request): bool => $request['title'] === '[In App] Boom'
        && $request['labels'] === ['bug']);
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
