<?php

declare(strict_types=1);

namespace App\Model;

final readonly class Position
{
    private const float EARTH_RADIUS_KM = 6371.0;

    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }

    public function toDistance(Position $other): float
    {
        $fromLatitudeRad = deg2rad($this->latitude);
        $toLatitudeRad = deg2rad($other->latitude);
        $deltaLatitudeRad = deg2rad($other->latitude - $this->latitude);
        $deltaLongitudeRad = deg2rad($other->longitude - $this->longitude);

        $a = sin($deltaLatitudeRad / 2) ** 2
            + cos($fromLatitudeRad) * cos($toLatitudeRad) * sin($deltaLongitudeRad / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
