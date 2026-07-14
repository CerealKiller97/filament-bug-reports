<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Actions;

use CerealKiller97\FilamentBugReports\Models\BugReport;
use Carbon\CarbonImmutable;
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

            // Skip issues that no longer exist rather than aborting the whole run.
            if ($response->status() === 404) {
                continue;
            }

            $response->throw();

            $closedAt = $response->json('state') === 'closed'
                ? CarbonImmutable::parse((string) ($response->json('closed_at') ?? 'now'))
                : null;

            $unchanged = ($report->resolved_at === null && ! $closedAt instanceof CarbonImmutable)
                || ($report->resolved_at !== null && $closedAt instanceof CarbonImmutable && $report->resolved_at->equalTo($closedAt));

            if ($unchanged) {
                continue;
            }

            $report->forceFill(['resolved_at' => $closedAt])->save();

            $changed++;
        }

        return $changed;
    }
}
