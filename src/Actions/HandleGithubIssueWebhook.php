<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Actions;

use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Support\Arr;

/**
 * Apply a GitHub `issues` webhook payload to the matching bug report, mirroring
 * the issue's open/closed state the same way the scheduled sync does — just the
 * instant GitHub sends it rather than up to an hour later.
 *
 * Nothing is trusted about which repository sent the event until the signature
 * middleware has run; on top of that this only touches a report whose stored
 * `github_issue_number` matches, and — when a repository is configured — only
 * when the payload's repository matches it too, so one secret shared across
 * repositories can't cross-close issues.
 *
 * @see https://docs.github.com/en/webhooks/webhook-events-and-payloads#issues
 */
final class HandleGithubIssueWebhook
{
    /**
     * @param array<string, mixed> $payload
     * @return BugReport|null  the report that was matched, or null when the
     *                         event referenced no report we track
     */
    public function handle(array $payload): ?BugReport
    {
        $issue = Arr::get($payload, 'issue');

        if (!is_array($issue)) {
            return null;
        }

        $number = $issue['number'] ?? null;

        if (!is_int($number)) {
            return null;
        }

        // When a target repository is configured, ignore events from any other
        // one. Match case-insensitively — GitHub's "owner/repo" is not case
        // sensitive, so 'Acme/Repo' and 'acme/repo' are the same repository.
        $repository = config()->string('bug-reports.github.repository', '');
        $fullName = Arr::get($payload, 'repository.full_name');

        if ($repository !== '' && is_string($fullName) && strcasecmp($fullName, $repository) !== 0) {
            return null;
        }

        $report = BugReport::query()
            ->where('github_issue_number', $number)
            ->first();

        if ($report === null) {
            return null;
        }

        $report->applyIssueState(
            state: $issue['state'] ?? null,
            closedAt: $issue['closed_at'] ?? null
        );

        return $report;
    }
}
