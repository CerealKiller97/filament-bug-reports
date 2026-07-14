<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Tests\Fixtures;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property bool $is_manager
 */
class User extends Authenticatable implements FilamentUser
{
    protected $table = 'users';

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = [
        'is_manager' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
