<?php

use CerealKiller97\FilamentBugReports\Tests\Fixtures\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auto-login the seeded manager and drop straight into the panel — skips the
// Filament login form during local translation testing.
Route::get('/', function () {
    Auth::login(User::query()->where('email', 'manager@example.com')->firstOrFail());

    return redirect('/admin');
})->middleware('web');
