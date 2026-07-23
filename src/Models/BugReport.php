<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Models;

use CerealKiller97\FilamentBugReports\Database\Factories\BugReportFactory;
use CerealKiller97\FilamentBugReports\Enums\BugPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;
use Carbon\CarbonImmutable;

/**
 * @property-read  int $id
 * @property-read  string $title
 * @property-read  array<int, string>|null $steps
 * @property-read  string|null $screenshot_path
 * @property-read  string $app_version
 * @property-read  string $role
 * @property-read  BugPriority|null $priority
 * @property-read  Carbon|null $validated_at
 * @property-read  string|null $github_issue_url
 * @property-read  int|null $github_issue_number
 * @property-read  Carbon|null $resolved_at
 * @property-read  int|null $user_id
 * @property-read  Carbon|null $created_at
 * @property-read  Carbon|null $updated_at
 * @property-read Model|null $user
 */
class BugReport extends Model
{
    /** @use HasFactory<BugReportFactory> */
    use HasFactory;

    protected $table = 'bug_reports';

    /** @var list<string> */
    protected $fillable = [
        'title',
        'steps',
        'screenshot_path',
        'app_version',
        'role',
        'priority',
        'validated_at',
        'github_issue_url',
        'github_issue_number',
        'resolved_at',
        'user_id',
    ];

    /**
     * The reporter, resolved from the configured user model.
     *
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        /** @var class-string<Model> $model */
        $model = config()->string('bug-reports.user_model', User::class);

        return $this->belongsTo($model, 'user_id');
    }

    /**
     * Whether the report has been confirmed real and pushed to GitHub.
     */
    public function isValidated(): bool
    {
        return $this->github_issue_url !== null;
    }

    /**
     * Whether the linked GitHub issue has been closed/finished.
     */
    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }

    /**
     * Mirror a GitHub issue's open/closed state onto this report. A closed
     * issue resolves the report — stamped with the issue's own `closed_at`, so
     * the report carries when GitHub closed it rather than when we noticed — and
     * an open one clears it. Returns whether anything actually changed, so the
     * scheduled sync and the webhook can both count and skip no-op writes.
     *
     * Both the polling response and the webhook payload feed this straight from
     * JSON, so the arguments are typed loosely and normalised here.
     */
    public function applyIssueState(mixed $state, mixed $closedAt): bool
    {
        $resolvedAt = $state === 'closed'
            ? CarbonImmutable::parse(is_string($closedAt) && $closedAt !== '' ? $closedAt : 'now')
            : null;

        $unchanged = ($this->resolved_at === null && ! $resolvedAt instanceof CarbonImmutable)
            || ($this->resolved_at !== null && $resolvedAt instanceof CarbonImmutable && $this->resolved_at->equalTo($resolvedAt));

        if ($unchanged) {
            return false;
        }

        $this->forceFill(['resolved_at' => $resolvedAt])->save();

        return true;
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'priority' => BugPriority::class,
            'validated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): BugReportFactory
    {
        return BugReportFactory::new();
    }
}
