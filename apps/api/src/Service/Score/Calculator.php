<?php

declare(strict_types=1);

namespace App\Service\Score;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class Calculator
{
    public function __construct(
        #[Autowire('%app.game_max_score%')]
        private int $maxScore,
        #[Autowire('%app.game_calibration_distance_km%')]
        private float $calibrationDistanceKm,
    ) {
    }

    public function computeScore(float $distanceKm): int
    {
        return (int) round($this->maxScore * exp(-M_LN2 / $this->calibrationDistanceKm * $distanceKm));
    }
}
