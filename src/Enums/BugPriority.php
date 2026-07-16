<?php

declare(strict_types=1);

namespace CerealKiller97\FilamentBugReports\Enums;

use Filament\Support\Colors\Color;
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

    /**
     * Green, amber, red, magenta — the ramp climbs to a colour that reads as
     * "not like the others". Urgent takes a raw palette because Filament's
     * registered colours stop at danger (red), and urgent has to out-shout it.
     *
     * @return string|array<int, string>
     */
    public function getColor(): string|array
    {
        return match ($this) {
            self::Low => 'success',
            self::Medium => 'warning',
            self::High => 'danger',
            self::Urgent => Color::Pink,
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
     * Resolve a priority from form input. A manager who does not choose one
     * gets the lowest, so an unanswered radio never blocks a triage — it just
     * means "nothing to see here".
     */
    public static function fromInput(self|string|null $value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        return ($value === null ? null : self::tryFrom($value)) ?? self::Low;
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
