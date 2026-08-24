<?php

declare(strict_types=1);

namespace App\Model;

enum Mode: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';

    public function toTier(): string
    {
        return match ($this) {
            self::Easy => 'large',
            self::Medium => 'medium',
            self::Hard => 'small',
        };
    }
}
