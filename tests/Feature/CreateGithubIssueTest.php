<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Actions\CreateBugReportGithubIssue;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('it creates a github issue with configured labels/prefix and stores the reference', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.labels', ['bug', 'in:app']);
    config()->set('bug-reports.github.title_prefix', '[In App] ');

    Http::fake([
        'api.github.com/*' => Http::response([
            'html_url' => 'https://github.com/acme/repo/issues/42',
            'number' => 42,
        ], 201),
    ]);

    $report = BugReport::factory()->create([
        'title' => 'Broken button',
        'steps' => ['Open page', 'Click button'],
    ]);

    $result = app(CreateBugReportGithubIssue::class)->handle($report);

    expect($result->github_issue_url)->toBe('https://github.com/acme/repo/issues/42')
        ->and($result->github_issue_number)->toBe(42)
        ->and($result->validated_at)->not->toBeNull();

    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Authorization', 'Bearer secret')
            && $request['title'] === '[In App] Broken button'
            && $request['labels'] === ['bug', 'in:app']
            && str_contains((string) $request['body'], '1. Open page')
            && str_contains((string) $request['body'], '2. Click button');
    });
});

test('it is idempotent and does not create a second issue', function (): void {
    Http::fake();
    $report = BugReport::factory()->validated()->create();
    $existingUrl = $report->github_issue_url;

    $result = app(CreateBugReportGithubIssue::class)->handle($report);

    expect($result->github_issue_url)->toBe($existingUrl);
    Http::assertNothingSent();
});

test('it throws a friendly error when github is not configured', function (): void {
    // "Not configured" is an empty string, not null — the config never yields
    // null, because these are read with config()->string(), which throws on it.
    config()->set('bug-reports.github.repository', '');
    config()->set('bug-reports.github.token', '');
    Http::fake();

    $report = BugReport::factory()->create();

    expect(fn (): BugReport => app(CreateBugReportGithubIssue::class)->handle($report))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});
