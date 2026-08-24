<?php

declare(strict_types=1);

namespace App\Model;

final readonly class RoundResult
{
    public function __construct(
        public string $inseeCode,
        public Position $guessPosition,
        public float $distanceKm,
        public int $score,
    ) {
    }
}
