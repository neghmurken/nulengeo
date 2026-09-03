<?php

declare(strict_types=1);

namespace App\Tests\Service\Score;

use App\Service\Score\Calculator;
use PHPUnit\Framework\TestCase;

final class CalculatorTest extends TestCase
{
    private Calculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new Calculator(maxScore: 1000, calibrationDistanceKm: 25.0, toleranceMaxKm: 8.0);
    }

    public function testScoreIsMaximalAtZeroDistance(): void
    {
        self::assertSame(1000, $this->calculator->computeScore(0.0, areaKm2: 0.0));
    }

    public function testScoreIsHalvedAtTheCalibrationDistance(): void
    {
        self::assertSame(500, $this->calculator->computeScore(25.0, areaKm2: 0.0));
    }

    public function testScoreIsQuarteredAtTwiceTheCalibrationDistance(): void
    {
        self::assertSame(250, $this->calculator->computeScore(50.0, areaKm2: 0.0));
    }

    public function testScoreDecreasesAsDistanceIncreases(): void
    {
        self::assertGreaterThan(
            $this->calculator->computeScore(10.0, areaKm2: 0.0),
            $this->calculator->computeScore(5.0, areaKm2: 0.0),
        );
    }

    public function testScoreIsMaximalWithinTheCityTolerance(): void
    {
        // A city with an area of π × 3² km² has a 3km-radius tolerance: a 2km-off guess still lands within it.
        self::assertSame(1000, $this->calculator->computeScore(2.0, areaKm2: M_PI * 3 ** 2));
    }

    public function testScoreIsHalvedAtTheCalibrationDistanceRegardlessOfTolerance(): void
    {
        // The calibration distance is measured from the real distance, not the tolerance-shifted one,
        // so the "half score at 25km" invariant holds no matter how large the city's tolerance is.
        self::assertSame(500, $this->calculator->computeScore(25.0, areaKm2: M_PI * 4 ** 2));
    }

    public function testToleranceIsCappedAtTheConfiguredMaximum(): void
    {
        // This city's equivalent radius (15km) exceeds toleranceMaxKm (8km), so the tolerance clamps to 8km.
        $areaKm2 = M_PI * 15 ** 2;

        self::assertSame(1000, $this->calculator->computeScore(8.0, $areaKm2));
        self::assertLessThan(1000, $this->calculator->computeScore(9.0, $areaKm2));
    }

    public function testComputeToleranceReturnsTheEquivalentCircleRadius(): void
    {
        self::assertEqualsWithDelta(3.0, $this->calculator->computeTolerance(M_PI * 3 ** 2), 0.0001);
    }

    public function testComputeToleranceIsCappedAtTheConfiguredMaximum(): void
    {
        self::assertSame(8.0, $this->calculator->computeTolerance(M_PI * 15 ** 2));
    }
}
