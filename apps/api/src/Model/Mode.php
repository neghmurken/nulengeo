<?php

declare(strict_types=1);

namespace App\Model;

enum Mode: string
{
    case Easy = 'easy';
    case Medium = 'medium';
    case Hard = 'hard';
    case Expert = 'expert';

    public function toTier(): string
    {
        return match ($this) {
            self::Easy => 'huge',
            self::Medium => 'large',
            self::Hard => 'medium',
            self::Expert => 'small',
        };
    }
}
