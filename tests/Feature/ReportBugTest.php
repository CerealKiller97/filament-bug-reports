<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Filament\Resources\BugReportResource;
use CerealKiller97\FilamentBugReports\Filament\Resources\BugReports\Pages\CreateBugReport;
use CerealKiller97\FilamentBugReports\Models\BugReport;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

test('any panel user can open the report page', function (bool $manager): void {
    actingAs(makeUser($manager));

    $this->get(BugReportResource::getUrl('create'))->assertSuccessful();
})->with([
    'manager' => true,
    'reporter' => false,
]);

test('reporting stamps the reporter, their role and the app version', function (): void {
    $user = makeUser(false);

    actingAs($user);

    livewire(CreateBugReport::class)
        ->assertFormFieldExists('role')
        ->fillForm([
            'title' => 'Cannot save',
            'steps' => [['text' => 'Open page'], ['text' => 'Click save']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $report = BugReport::query()->sole();

    expect($report->user_id)->toBe($user->id)
        ->and($report->role)->toBe('user')
        ->and($report->app_version)->toBe('test-version')
        ->and($report->validated_at)->toBeNull()
        ->and($report->steps)->toBe(['Open page', 'Click save']);
});

test('the reporter role is resolved via the configured closure', function (): void {
    actingAs(makeUser(true));

    livewire(CreateBugReport::class)
        ->assertFormSet([
            'role' => 'manager',
        ]);
});
