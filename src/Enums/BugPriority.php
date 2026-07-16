<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum BugPriority: string implements HasColor, HasIcon, HasLabel
{
    case Low = 'low';

    case Medium = 'medium';

    case High = 'high';

    case Urgent = 'urgent';

    public function getLabel(): string
    {
        return (string) __('bug-reports::bug-reports.priority.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Low => 'gray',
            self::Medium => 'info',
            self::High => 'warning',
            self::Urgent => 'danger',
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Low => Heroicon::OutlinedArrowDown,
            self::Medium => Heroicon::OutlinedArrowRight,
            self::High => Heroicon::OutlinedArrowUp,
            self::Urgent => Heroicon::OutlinedFire,
        };
    }

    /**
     * Ranked least to most urgent. Ordering by the column itself would sort the
     * values alphabetically, which means nothing.
     */
    public function rank(): int
    {
        return match ($this) {
            self::Low => 1,
            self::Medium => 2,
            self::High => 3,
            self::Urgent => 4,
        };
    }

    /**
     * The GitHub label this priority maps to, or null when unmapped.
     */
    public function githubLabel(): ?string
    {
        /** @var array<string, string> $labels */
        $labels = config()->array('bug-reports.github.priority_labels', []);

        $label = $labels[$this->value] ?? null;

        return is_string($label) && $label !== '' ? $label : null;
    }
}
