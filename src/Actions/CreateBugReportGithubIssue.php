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

        $placeholders = $this->placeholders($bugReport, $priority);

        $payload = [
            'title' => $this->title($bugReport, $placeholders),
            'body' => $this->body($bugReport, $placeholders),
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
     * The issue title. When `github.issue.title` holds a template it is filled
     * with the report's placeholders; otherwise the legacy `title_prefix.title`
     * is used so existing installs are untouched.
     *
     * @param  array<string, string>  $placeholders  brace-keyed, ready for strtr
     */
    private function title(BugReport $bugReport, array $placeholders): string
    {
        $template = config()->string('bug-reports.github.issue.title', '');

        if ($template !== '') {
            return strtr($template, $placeholders);
        }

        return config()->string('bug-reports.github.title_prefix', '') . $bugReport->title;
    }

    /**
     * The issue body. When `github.issue.body` holds a template it is filled
     * with the report's placeholders; otherwise the built-in, translated layout
     * is used so existing installs are untouched.
     *
     * @param  array<string, string>  $placeholders  brace-keyed, ready for strtr
     */
    private function body(BugReport $bugReport, array $placeholders): string
    {
        $template = config()->string('bug-reports.github.issue.body', '');

        if ($template !== '') {
            return strtr($template, $placeholders);
        }

        return $this->defaultBody($placeholders);
    }

    /**
     * The report's details as `{placeholder} => value` pairs, keyed with braces
     * so the map can be handed straight to strtr. A single strtr pass replaces
     * every token at once, so a value that itself contains a `{token}` (e.g. a
     * report title) is never re-interpolated.
     *
     * @return array<string, string>
     */
    private function placeholders(BugReport $bugReport, ?BugPriority $priority): array
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

        // The role is optional — `resolveReporterRoleUsing()` may never be set,
        // and the column defaults to ''. Without this, every issue in an app
        // that skipped it reads "Reported by: Stefan ()".
        if ($bugReport->role !== '') {
            $reporter .= ' (' . $bugReport->role . ')';
        }

        return [
            '{title}' => $bugReport->title,
            '{id}' => (string) $bugReport->id,
            '{reporter}' => $reporter,
            '{priority}' => $priority?->getLabel() ?? '—',
            '{app_version}' => $bugReport->app_version,
            '{reported_at}' => $bugReport->created_at?->format('d.m.Y. H:i') ?? '—',
            '{steps}' => $steps,
            '{screenshot}' => $screenshot,
        ];
    }

    /**
     * Build the package's default, translated Markdown body from the resolved
     * placeholders. Used whenever the host has not configured a body template.
     *
     * @param  array<string, string>  $placeholders  brace-keyed, ready for strtr
     */
    private function defaultBody(array $placeholders): string
    {
        return implode("\n", [
            '## ' . __('bug-reports::bug-reports.issue.details'),
            '**' . __('bug-reports::bug-reports.issue.reported_by') . ':** ' . $placeholders['{reporter}'],
            '**' . __('bug-reports::bug-reports.issue.priority') . ':** ' . $placeholders['{priority}'],
            '**' . __('bug-reports::bug-reports.issue.app_version') . ':** ' . $placeholders['{app_version}'],
            '**' . __('bug-reports::bug-reports.issue.reported_at') . ':** ' . $placeholders['{reported_at}'],
            '',
            '## ' . __('bug-reports::bug-reports.issue.steps'),
            $placeholders['{steps}'],
            '',
            '## ' . __('bug-reports::bug-reports.issue.screenshot'),
            $placeholders['{screenshot}'],
            '',
            '---',
            (string)__('bug-reports::bug-reports.issue.footer', ['id' => $placeholders['{id}']]),
        ]);
    }
}
