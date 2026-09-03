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
        #[Autowire('%app.game_guess_tolerance_max_km%')]
        private float $toleranceMaxKm,
    ) {
    }

    public function computeTolerance(float $areaKm2): float
    {
        return min(sqrt($areaKm2 / M_PI), $this->toleranceMaxKm);
    }

    public function computeScore(float $distanceKm, float $areaKm2): int
    {
        $toleranceKm = $this->computeTolerance($areaKm2);
        $effectiveDistanceKm = max(0.0, $distanceKm - $toleranceKm);
        $decayDistanceKm = $this->calibrationDistanceKm - $toleranceKm;

        return (int) round($this->maxScore * exp(-M_LN2 / $decayDistanceKm * $effectiveDistanceKm));
    }
}
