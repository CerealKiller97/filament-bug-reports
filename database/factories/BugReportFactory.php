<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Database\Factories;

use CerealKiller97\FilamentBugReports\Enums\BugPriority;
use CerealKiller97\FilamentBugReports\Models\BugReport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BugReport>
 */
class BugReportFactory extends Factory
{
    /** @var class-string<BugReport> */
    protected $model = BugReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'steps' => [
                fake()->sentence(),
                fake()->sentence(),
            ],
            'screenshot_path' => null,
            'app_version' => 'v'.fake()->numerify('#.#.#'),
            'role' => fake()->randomElement(['admin', 'manager', 'user']),
            'priority' => null,
            'validated_at' => null,
            'github_issue_url' => null,
            'github_issue_number' => null,
            'resolved_at' => null,
            'user_id' => null,
        ];
    }

    /**
     * A report that has already been confirmed real and pushed to GitHub.
     */
    public function validated(): static
    {
        return $this->state(function (): array {
            $number = fake()->numberBetween(1, 999);

            return [
                'priority' => fake()->randomElement(BugPriority::cases()),
                'validated_at' => now(),
                'github_issue_number' => $number,
                'github_issue_url' => "https://github.com/acme/repo/issues/{$number}",
            ];
        });
    }
}
