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

test('a deleted issue is skipped and does not abort the rest of the run', function (): void {
    // GitHub answers 410 Gone once an issue has actually been deleted, which
    // used to blow up the whole run and leave every later report unsynced.
    $deleted = BugReport::factory()->validated()->create(['github_issue_number' => 410]);
    $closed = BugReport::factory()->validated()->create(['github_issue_number' => 11]);

    Http::fake([
        'api.github.com/repos/acme/repo/issues/410' => Http::response([
            'message' => 'This issue was deleted',
        ], 410),
        'api.github.com/repos/acme/repo/issues/11' => Http::response([
            'state' => 'closed',
            'closed_at' => '2026-07-13T10:00:00Z',
        ], 200),
    ]);

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(1)
        ->and($deleted->refresh()->resolved_at)->toBeNull()
        ->and($closed->refresh()->resolved_at)->not->toBeNull();
});

test('it ignores reports that were never pushed to github', function (): void {
    BugReport::factory()->create(['github_issue_number' => null]);
    Http::fake();

    $changed = app(SyncBugReportGithubIssues::class)->handle();

    expect($changed)->toBe(0);
    Http::assertNothingSent();
});

test('it throws a friendly error when github is not configured', function (): void {
    // "Not configured" is an empty string, not null — see CreateGithubIssueTest.
    config()->set('bug-reports.github.repository', '');
    config()->set('bug-reports.github.token', '');
    Http::fake();

    expect(fn (): int => app(SyncBugReportGithubIssues::class)->handle())
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});
