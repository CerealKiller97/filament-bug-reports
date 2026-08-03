<?php

namespace Workbench\Database\Seeders;

use CerealKiller97\FilamentBugReports\Tests\Fixtures\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed a manager (sees the triage table + stats) so the served panel is
     * immediately useful for eyeballing translations.
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager',
                'is_manager' => true,
                'password' => Hash::make('password'),
            ],
        );
    }
}
