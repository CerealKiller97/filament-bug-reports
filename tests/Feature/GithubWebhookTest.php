<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Models\BugReport;

beforeEach(function (): void {
    config()->set('bug-reports.github.repository', 'acme/repo');
    config()->set('bug-reports.webhook.secret', 'whsec');
});

/**
 * Deliver a payload the way GitHub would: raw JSON body, the event header, and
 * an `X-Hub-Signature-256` computed over those exact bytes with the secret.
 *
 * @param  array<string, mixed>  $payload
 */
function deliver(array $payload, string $event = 'issues', ?string $secret = 'whsec'): Illuminate\Testing\TestResponse
{
    $body = json_encode($payload);

    $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_X-GitHub-Event' => $event];

    if ($secret !== null) {
        $server['HTTP_X-Hub-Signature-256'] = 'sha256=' . hash_hmac('sha256', (string) $body, $secret);
    }

    return test()->call('POST', route('bug-reports.github.webhook'), [], [], [], $server, (string) $body);
}

/**
 * @param  array<string, mixed>  $issue
 * @return array<string, mixed>
 */
function issueEvent(string $action, array $issue, string $repository = 'acme/repo'): array
{
    return [
        'action' => $action,
        'issue' => $issue,
        'repository' => ['full_name' => $repository],
    ];
}

test('a closed-issue event resolves the matching report', function (): void {
    $report = BugReport::factory()->validated()->create(['github_issue_number' => 7]);

    $response = deliver(issueEvent('closed', [
        'number' => 7,
        'state' => 'closed',
        'closed_at' => '2026-07-13T10:00:00Z',
    ]));

    $response->assertNoContent();

    expect($report->refresh()->resolved_at)->not->toBeNull()
        ->and($report->resolved_at->toDateString())->toBe('2026-07-13');
});

test('a reopened-issue event clears resolved_at', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 9,
        'resolved_at' => now(),
    ]);

    deliver(issueEvent('reopened', ['number' => 9, 'state' => 'open', 'closed_at' => null]))
        ->assertNoContent();

    expect($report->refresh()->resolved_at)->toBeNull();
});

test('a request with a bad signature is rejected and changes nothing', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 7,
        'resolved_at' => null,
    ]);

    $body = json_encode(issueEvent('closed', ['number' => 7, 'state' => 'closed', 'closed_at' => null]));

    test()->call('POST', route('bug-reports.github.webhook'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X-GitHub-Event' => 'issues',
        'HTTP_X-Hub-Signature-256' => 'sha256=deadbeef',
    ], (string) $body)->assertForbidden();

    expect($report->refresh()->resolved_at)->toBeNull();
});

test('the endpoint 404s while no secret is configured', function (): void {
    config()->set('bug-reports.webhook.secret', '');

    deliver(issueEvent('closed', ['number' => 7, 'state' => 'closed', 'closed_at' => null]), secret: null)
        ->assertNotFound();
});

test('a non-issues event is acknowledged and ignored', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 7,
        'resolved_at' => null,
    ]);

    deliver(['action' => 'opened', 'pull_request' => ['number' => 7]], event: 'pull_request')
        ->assertNoContent();

    expect($report->refresh()->resolved_at)->toBeNull();
});

test('an issue action that does not change state is ignored', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 7,
        'resolved_at' => null,
    ]);

    deliver(issueEvent('labeled', ['number' => 7, 'state' => 'open', 'closed_at' => null]))
        ->assertNoContent();

    expect($report->refresh()->resolved_at)->toBeNull();
});

test('an event for an untracked issue number is a no-op', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 7,
        'resolved_at' => null,
    ]);

    deliver(issueEvent('closed', ['number' => 999, 'state' => 'closed', 'closed_at' => '2026-07-13T10:00:00Z']))
        ->assertNoContent();

    expect($report->refresh()->resolved_at)->toBeNull();
});

test('an event from a different repository is ignored', function (): void {
    $report = BugReport::factory()->validated()->create([
        'github_issue_number' => 7,
        'resolved_at' => null,
    ]);

    deliver(issueEvent('closed', [
        'number' => 7,
        'state' => 'closed',
        'closed_at' => '2026-07-13T10:00:00Z',
    ], repository: 'someone/else'))->assertNoContent();

    expect($report->refresh()->resolved_at)->toBeNull();
});
