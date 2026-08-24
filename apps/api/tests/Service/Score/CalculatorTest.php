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
        $this->calculator = new Calculator(maxScore: 1000, calibrationDistanceKm: 25.0);
    }

    public function testScoreIsMaximalAtZeroDistance(): void
    {
        self::assertSame(1000, $this->calculator->computeScore(0.0));
    }

    public function testScoreIsHalvedAtTheCalibrationDistance(): void
    {
        self::assertSame(500, $this->calculator->computeScore(25.0));
    }

    public function testScoreIsQuarteredAtTwiceTheCalibrationDistance(): void
    {
        self::assertSame(250, $this->calculator->computeScore(50.0));
    }

    public function testScoreDecreasesAsDistanceIncreases(): void
    {
        self::assertGreaterThan($this->calculator->computeScore(10.0), $this->calculator->computeScore(5.0));
    }
}
