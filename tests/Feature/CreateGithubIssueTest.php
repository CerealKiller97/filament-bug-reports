<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Actions\CreateBugReportGithubIssue;
use CerealKiller97\FilamentBugReports\Enums\BugPriority;
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

test('it adds the mapped priority label and records the priority', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.labels', ['bug']);
    config()->set('bug-reports.github.priority_labels', ['low' => 'P3']);

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/1', 'number' => 1], 201),
    ]);

    $report = app(CreateBugReportGithubIssue::class)
        ->handle(BugReport::factory()->create(), BugPriority::Low);

    expect($report->priority)->toBe(BugPriority::Low);

    Http::assertSent(fn (Request $request): bool => $request['labels'] === ['bug', 'P3']
        && str_contains((string) $request['body'], 'Low'));
});

test('an unmapped priority adds no label but is still recorded', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.labels', ['bug']);
    config()->set('bug-reports.github.priority_labels', []);

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/2', 'number' => 2], 201),
    ]);

    $report = app(CreateBugReportGithubIssue::class)
        ->handle(BugReport::factory()->create(), BugPriority::High);

    expect($report->priority)->toBe(BugPriority::High);

    Http::assertSent(fn (Request $request): bool => $request['labels'] === ['bug']);
});

test('it sends every configured github option', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.labels', ['bug']);
    config()->set('bug-reports.github.assignees', ['octocat']);
    config()->set('bug-reports.github.milestone', '4');
    config()->set('bug-reports.github.type', 'Bug');
    config()->set('bug-reports.github.issue_field_values', [['field_id' => 9, 'value' => 'Platform']]);

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/3', 'number' => 3], 201),
    ]);

    app(CreateBugReportGithubIssue::class)->handle(BugReport::factory()->create());

    Http::assertSent(fn (Request $request): bool => $request['assignees'] === ['octocat']
        // A milestone is addressed by number, so it must go over the wire as an
        // integer even though it is configured as a string.
        && $request['milestone'] === 4
        && $request['type'] === 'Bug'
        && $request['issue_field_values'] === [['field_id' => 9, 'value' => 'Platform']]);
});

test('unset github options are omitted rather than sent as null', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.milestone', '');
    config()->set('bug-reports.github.type', '');
    config()->set('bug-reports.github.issue_field_values', []);

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/4', 'number' => 4], 201),
    ]);

    app(CreateBugReportGithubIssue::class)->handle(BugReport::factory()->create());

    Http::assertSent(function (Request $request): bool {
        $keys = array_keys((array) $request->data());

        return ! in_array('milestone', $keys, true)
            && ! in_array('type', $keys, true)
            && ! in_array('issue_field_values', $keys, true);
    });
});

test('it omits the reporter role when there is none rather than printing empty parens', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/5', 'number' => 5], 201),
    ]);

    // `resolveReporterRoleUsing()` is optional, so the column defaults to ''.
    $user = makeUser(false);
    $user->forceFill(['name' => 'Stefan'])->save();

    $report = BugReport::factory()->create(['role' => '', 'user_id' => $user->getKey()]);

    app(CreateBugReportGithubIssue::class)->handle($report);

    Http::assertSent(fn (Request $request): bool => str_contains((string) $request['body'], 'Reported by:** Stefan')
        && ! str_contains((string) $request['body'], 'Stefan ()'));
});

test('it keeps the reporter role when one is configured', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/6', 'number' => 6], 201),
    ]);

    $user = makeUser(false);
    $user->forceFill(['name' => 'Stefan'])->save();

    $report = BugReport::factory()->create(['role' => 'developer', 'user_id' => $user->getKey()]);

    app(CreateBugReportGithubIssue::class)->handle($report);

    Http::assertSent(fn (Request $request): bool => str_contains((string) $request['body'], 'Reported by:** Stefan (developer)'));
});

test('it fills a custom body template with named placeholders', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.issue.body', "## {title}\nBy {reporter} — {priority}\n\n{steps}\n\n_#{id}_");

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/7', 'number' => 7], 201),
    ]);

    $user = makeUser(false);
    $user->forceFill(['name' => 'Stefan'])->save();

    $report = BugReport::factory()->create([
        'title' => 'Broken button',
        'steps' => ['Open page', 'Click button'],
        'role' => '',
        'user_id' => $user->getKey(),
    ]);

    app(CreateBugReportGithubIssue::class)->handle($report, BugPriority::High);

    Http::assertSent(function (Request $request) use ($report): bool {
        $body = (string) $request['body'];

        return $body === "## Broken button\nBy Stefan — High\n\n1. Open page\n2. Click button\n\n_#{$report->id}_";
    });
});

test('it fills a custom title template with named placeholders', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.title_prefix', '[In App] ');
    config()->set('bug-reports.github.issue.title', 'Bug #{id}: {title} ({priority})');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/8', 'number' => 8], 201),
    ]);

    $report = BugReport::factory()->create(['title' => 'Broken button']);

    app(CreateBugReportGithubIssue::class)->handle($report, BugPriority::Urgent);

    // The template replaces the title entirely — `title_prefix` is ignored.
    Http::assertSent(fn (Request $request): bool => $request['title'] === "Bug #{$report->id}: Broken button (Urgent)");
});

test('an empty template falls back to the built-in title and body', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.title_prefix', '[In App] ');
    config()->set('bug-reports.github.issue.title', '');
    config()->set('bug-reports.github.issue.body', '');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/9', 'number' => 9], 201),
    ]);

    $report = BugReport::factory()->create(['title' => 'Broken button']);

    app(CreateBugReportGithubIssue::class)->handle($report);

    Http::assertSent(fn (Request $request): bool => $request['title'] === '[In App] Broken button'
        && str_contains((string) $request['body'], 'Steps to reproduce'));
});

test('a report title containing a placeholder token is not re-interpolated', function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.github.token', 'secret');
    config()->set('bug-reports.github.issue.title', '{title}');

    Http::fake([
        'api.github.com/*' => Http::response(['html_url' => 'https://github.com/acme/repo/issues/10', 'number' => 10], 201),
    ]);

    // strtr replaces every token in one pass, so the literal "{id}" a user typed
    // into their report title survives verbatim rather than becoming the id.
    $report = BugReport::factory()->create(['title' => 'Weird {id} title']);

    app(CreateBugReportGithubIssue::class)->handle($report);

    Http::assertSent(fn (Request $request): bool => $request['title'] === 'Weird {id} title');
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
