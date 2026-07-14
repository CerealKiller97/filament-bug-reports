<?php

declare(strict_types=1);

use Livewire\Mechanisms\DataStore;

test('DataStore is a singleton', function (): void {
    $a = app(DataStore::class);
    $b = app(DataStore::class);
    dump(['same' => $a === $b, 'bound' => app()->bound(DataStore::class)]);
    expect($a)->toBe($b);
});
