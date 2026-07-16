<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Actions;

use CerealKiller97\FilamentBugReports\Enums\BugPriority;
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
    public function handle(BugReport $bugReport, ?BugPriority $priority = null): BugReport
    {
        if ($bugReport->github_issue_url !== null) {
            return $bugReport;
        }

        $priority ??= $bugReport->priority;

        $repository = config()->string('bug-reports.github.repository', '');
        $token = config()->string('bug-reports.github.token', '');

        throw_if($repository === '' || $token === '', RuntimeException::class, (string)__('bug-reports::bug-reports.issue.not_configured'));

        $response = Http::withToken($token)
            ->acceptJson()
            ->withHeaders(['X-GitHub-Api-Version' => '2022-11-28'])
            ->post(sprintf('https://api.github.com/repos/%s/issues', $repository), $this->payload($bugReport, $priority))
            ->throw();

        $bugReport->forceFill([
            'priority' => $priority,
            'github_issue_url' => (string)$response->json('html_url'),
            'github_issue_number' => (int)$response->json('number'),
            'validated_at' => CarbonImmutable::now('UTC'),
        ])->save();

        return $bugReport;
    }

    /**
     * Every body parameter GitHub's "create an issue" endpoint accepts, built
     * from config. Optional ones are omitted entirely when unset rather than
     * sent as null, so GitHub applies its own defaults.
     *
     * Note that GitHub silently drops `labels`, `assignees`, `milestone` and
     * `type` when the token lacks push access — the issue is still created,
     * just bare. That is GitHub's behaviour, not something this package can
     * detect from the response.
     *
     * @see https://docs.github.com/en/rest/issues/issues#create-an-issue
     *
     * @return array<string, mixed>
     */
    private function payload(BugReport $bugReport, ?BugPriority $priority): array
    {
        /** @var list<string> $labels */
        $labels = config()->array('bug-reports.github.labels', ['bug']);

        $priorityLabel = $priority?->githubLabel();

        if ($priorityLabel !== null) {
            $labels[] = $priorityLabel;
        }

        /** @var list<string> $assignees */
        $assignees = config()->array('bug-reports.github.assignees', []);

        $titlePrefix = config()->string('bug-reports.github.title_prefix', '');

        $payload = [
            'title' => $titlePrefix.$bugReport->title,
            'body' => $this->body($bugReport, $priority),
            'labels' => $labels,
            'assignees' => $assignees,
        ];

        // A milestone is addressed by its number, but is read as a string so an
        // unset one stays an empty string rather than null. See the config.
        $milestone = config()->string('bug-reports.github.milestone', '');

        if ($milestone !== '') {
            $payload['milestone'] = ctype_digit($milestone) ? (int) $milestone : $milestone;
        }

        $type = config()->string('bug-reports.github.type', '');

        if ($type !== '') {
            $payload['type'] = $type;
        }

        /** @var list<array<string, mixed>> $fieldValues */
        $fieldValues = config()->array('bug-reports.github.issue_field_values', []);

        if ($fieldValues !== []) {
            $payload['issue_field_values'] = $fieldValues;
        }

        return $payload;
    }

    /**
     * Build a readable Markdown body from the report's details.
     */
    private function body(BugReport $bugReport, ?BugPriority $priority): string
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
            '**' . __('bug-reports::bug-reports.issue.priority') . ':** ' . ($priority?->getLabel() ?? '—'),
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
