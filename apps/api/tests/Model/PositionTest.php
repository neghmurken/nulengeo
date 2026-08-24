<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\Position;
use PHPUnit\Framework\TestCase;

final class PositionTest extends TestCase
{
    public function testDistanceIsZeroForTheSamePoint(): void
    {
        $paris = new Position(48.8566, 2.3522);

        self::assertEqualsWithDelta(0.0, $paris->toDistance($paris), 1e-9);
    }

    public function testDistanceForAQuarterOfTheEquator(): void
    {
        // A 90° longitude gap on the equator covers a quarter of the great circle.
        $from = new Position(0.0, 0.0);
        $to = new Position(0.0, 90.0);
        $expected = 6371.0 * M_PI / 2;

        self::assertEqualsWithDelta($expected, $from->toDistance($to), 1e-6);
    }

    public function testDistanceForAntipodalPoints(): void
    {
        // A 180° longitude gap on the equator covers half the great circle.
        $from = new Position(0.0, 0.0);
        $to = new Position(0.0, 180.0);
        $expected = 6371.0 * M_PI;

        self::assertEqualsWithDelta($expected, $from->toDistance($to), 1e-6);
    }
}
