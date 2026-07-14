<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Models;

use CerealKiller97\FilamentBugReports\Database\Factories\BugReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Carbon;

/**
 * @property-read  int $id
 * @property-read  string $title
 * @property-read  array<int, string>|null $steps
 * @property-read  string|null $screenshot_path
 * @property-read  string $app_version
 * @property-read  string $role
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'validated_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    protected static function newFactory(): BugReportFactory
    {
        return BugReportFactory::new();
    }
}
