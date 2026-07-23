<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Actions;

use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Pull the current state of each validated bug report's GitHub issue and mirror
 * it locally: a closed issue marks the report resolved (with the issue's
 * closed_at), a reopened one clears it. Returns how many reports changed.
 */
final class SyncBugReportGithubIssues
{
    /**
     * @throws RequestException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function handle(): int
    {
        $repository = config()->string('bug-reports.github.repository', '');
        $token = config()->string('bug-reports.github.token', '');

        throw_if($repository === '' || $token === '', RuntimeException::class, (string) __('bug-reports::bug-reports.issue.not_configured'));

        $reports = BugReport::query()->whereNotNull('github_issue_number')->get();

        $changed = 0;

        foreach ($reports as $report) {
            $response = Http::withToken($token)
                ->acceptJson()
                ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
                ->get(sprintf('https://api.github.com/repos/%s/issues/%d', $repository, $report->github_issue_number));

            // Skip issues that no longer exist rather than aborting the whole
            // run. GitHub answers 404 for an issue it won't show us (never
            // existed, or no access) and 410 for one that was deleted — both
            // mean "there is nothing left to mirror here".
            if (in_array($response->status(), [404, 410], true)) {
                continue;
            }

            $response->throw();

            if ($report->applyIssueState($response->json('state'), $response->json('closed_at'))) {
                $changed++;
            }
        }

        return $changed;
    }
}
