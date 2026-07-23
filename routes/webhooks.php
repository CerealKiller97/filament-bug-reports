<?php

declare(strict_types=1);

use CerealKiller97\FilamentBugReports\Http\Controllers\GithubWebhookController;
use CerealKiller97\FilamentBugReports\Http\Middleware\VerifyGithubWebhookSignature;
use Illuminate\Support\Facades\Route;

// Always registered; the signature middleware 404s the request while no secret
// is configured, so an unused webhook is invisible. Deliberately outside the
// `web` group — GitHub sends no CSRF token. Host-supplied middleware (e.g.
// throttling) runs after verification.
Route::post(
    config()->string('bug-reports.webhook.path', 'bug-reports/github/webhook'),
    GithubWebhookController::class,
)
    ->middleware(array_merge(
        [VerifyGithubWebhookSignature::class],
        config()->array('bug-reports.webhook.middleware', []),
    ))
    ->name('bug-reports.github.webhook');
