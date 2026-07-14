<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Actions;

use CerealKiller97\FilamentBugReports\Models\BugReport;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * Create a GitHub issue for a bug report an admin confirmed is real, then store
 * the issue reference back onto the report. Idempotent: a report that already
 * has an issue is returned untouched.
 */
final class CreateBugReportGithubIssue
{
    /**
     * @throws RequestException
     * @throws Throwable
     * @throws ConnectionException
     */
    public function handle(BugReport $bugReport): BugReport
    {
        if ($bugReport->github_issue_url !== null) {
            return $bugReport;
        }

        $repository = config()->string('bug-reports.github.repository', '');
        $token = config()->string('bug-reports.github.token', '');

        throw_if($repository === '' || $token === '', RuntimeException::class, (string)__('bug-reports::bug-reports.issue.not_configured'));

        /** @var list<string> $labels */
        $labels = config()->array('bug-reports.github.labels', ['bug']);

        /** @var list<string> $assignees */
        $assignees = config()->array('bug-reports.github.assignees', []);

        $titlePrefix = config()->string('bug-reports.github.title_prefix', '');

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->post(sprintf('https://api.github.com/repos/%s/issues', $repository), [
                'title' => $titlePrefix . $bugReport->title,
                'body' => $this->body($bugReport),
                'labels' => $labels,
                'assignees' => $assignees,
            ])
            ->throw();

        $bugReport->forceFill([
            'github_issue_url' => (string)$response->json('html_url'),
            'github_issue_number' => (int)$response->json('number'),
            'validated_at' => CarbonImmutable::now('UTC'),
        ])->save();

        return $bugReport;
    }

    /**
     * Build a readable Markdown body from the report's details.
     */
    private function body(BugReport $bugReport): string
    {
        $steps = collect($bugReport->steps ?? [])
            ->filter(fn (string $step): bool => $step !== '')
            ->values()
            ->map(fn (string $step, int $index): string => ($index + 1) . '. ' . $step)
            ->implode("\n");

        if ($steps === '') {
            $steps = (string)__('bug-reports::bug-reports.issue.no_steps');
        }

        $disk = config()->string('bug-reports.screenshot.disk', 'public');
        $screenshot = $bugReport->screenshot_path !== null
            ? Storage::disk($disk)->url($bugReport->screenshot_path)
            : (string)__('bug-reports::bug-reports.issue.no_screenshot');

        $reporterName = $bugReport->user?->getAttribute('name');
        $reporter = is_string($reporterName) && $reporterName !== ''
            ? $reporterName
            : (string)__('bug-reports::bug-reports.issue.unknown_reporter');

        $reportedAt = $bugReport->created_at?->format('d.m.Y. H:i') ?? '—';

        return implode("\n", [
            '## ' . __('bug-reports::bug-reports.issue.details'),
            '**' . __('bug-reports::bug-reports.issue.reported_by') . ':** ' . $reporter . ' (' . $bugReport->role . ')',
            '**' . __('bug-reports::bug-reports.issue.app_version') . ':** ' . $bugReport->app_version,
            '**' . __('bug-reports::bug-reports.issue.reported_at') . ':** ' . $reportedAt,
            '',
            '## ' . __('bug-reports::bug-reports.issue.steps'),
            $steps,
            '',
            '## ' . __('bug-reports::bug-reports.issue.screenshot'),
            $screenshot,
            '',
            '---',
            (string)__('bug-reports::bug-reports.issue.footer', ['id' => $bugReport->id]),
        ]);
    }
}
