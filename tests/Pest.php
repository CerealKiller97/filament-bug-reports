<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Tests\Fixtures\User;
use CerealKiller97\FilamentBugReports\Tests\TestCase;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

uses(TestCase::class)->in('Feature');

uses()->beforeEach(function (): void {
    Filament::setCurrentPanel('admin');

    // Livewire component tests render without the session middleware that
    // normally shares the error bag; share an empty one so validation renders.
    View::share('errors', new ViewErrorBag());
})->in('Feature');

/**
 * Create a panel user; managers can triage reports, others can only report.
 */
function makeUser(bool $manager = false): User
{
    return User::query()->create([
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'is_manager' => $manager,
    ]);
}
