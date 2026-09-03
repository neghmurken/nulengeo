<?php

declare(strict_types=1);

namespace App\Model;

final readonly class City
{
    public function __construct(
        public string $inseeCode,
        public string $name,
        public int $population,
        public float $latitude,
        public float $longitude,
        public float $areaKm2,
    ) {
    }
}
