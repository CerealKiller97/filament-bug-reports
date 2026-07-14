<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Actions\SyncBugReportGithubIssues;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
});

test('it marks a report resolved when its github issue is closed', function (): void {
    $report = BugReport::factory()->validated()->create(['github_issue_number' => 7]);

    Http::fake([
        'api.github.com/repos/acme/repo/issues/7' => Http::response([
            'state' => 'closed',
            'closed_at' => '2026-07-13T10:00:00Z',
        ], 200),
    ]);

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(1)
        ->and($report->refresh()->resolved_at)->not->toBeNull()
        ->and($report->resolved_at->toDateString())->toBe('2026-07-13');
});

test('it clears resolved_at when a previously closed issue is reopened', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 9,
        'resolved_at' => now(),
    ]);

    Http::fake([
        'api.github.com/repos/acme/repo/issues/9' => Http::response(['state' => 'open', 'closed_at' => null], 200),
    ]);

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(1)
        ->and($report->refresh()->resolved_at)->toBeNull();
});

test('it skips issues that no longer exist without failing', function (): void {
    $report = BugReport::factory()->validated()->create(['github_issue_number' => 404]);

    Http::fake([
        'api.github.com/repos/acme/repo/issues/404' => Http::response([], 404),
    ]);

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(0)
        ->and($report->refresh()->resolved_at)->toBeNull();
});

test('it ignores reports that were never pushed to github', function (): void {
    BugReport::factory()->create(['github_issue_number' => null]);
    Http::fake();

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(0);
    Http::assertNothingSent();
});

test('it throws a friendly error when github is not configured', function (): void {
    config()->set('bug-reports.github.repository', null);
    config()->set('bug-reports.github.token', null);
    Http::fake();

    expect(fn (): int => app(SyncBugReportGithubIssues::class)->handle())
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});
