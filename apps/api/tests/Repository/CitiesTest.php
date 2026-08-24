<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\Cities;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

final class CitiesTest extends TestCase
{
    private Connection $connection;
    private Cities $cities;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement(
            'CREATE TABLE city (
                insee_code VARCHAR(5) NOT NULL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                population INTEGER NOT NULL,
                latitude DOUBLE PRECISION NOT NULL,
                longitude DOUBLE PRECISION NOT NULL,
                tier VARCHAR(10) NOT NULL
            )',
        );

        foreach ($this->smallCities() as [$inseeCode, $name, $population]) {
            $this->insertCity($inseeCode, $name, $population, 'small');
        }
        $this->insertCity('75056', 'Paris', 2145906, 'large');

        $this->cities = new Cities($this->connection);
    }

    public function testDrawReturnsOnlyTheRequestedTier(): void
    {
        $drawn = $this->cities->draw('large', 10);

        self::assertCount(1, $drawn);
        self::assertSame('75056', $drawn[0]->inseeCode);
        self::assertSame('Paris', $drawn[0]->name);
        self::assertSame(2145906, $drawn[0]->population);
    }

    public function testDrawLimitsToTheRequestedCount(): void
    {
        $drawn = $this->cities->draw('small', 3);

        self::assertCount(3, $drawn);

        $inseeCodes = array_map(static fn ($city) => $city->inseeCode, $drawn);
        foreach ($inseeCodes as $inseeCode) {
            self::assertNotSame('75056', $inseeCode);
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: int}>
     */
    private function smallCities(): array
    {
        return [
            ['01001', 'Small One', 1500],
            ['01002', 'Small Two', 2500],
            ['01003', 'Small Three', 3500],
            ['01004', 'Small Four', 4500],
            ['01005', 'Small Five', 5500],
        ];
    }

    private function insertCity(string $inseeCode, string $name, int $population, string $tier): void
    {
        $this->connection->insert('city', [
            'insee_code' => $inseeCode,
            'name' => $name,
            'population' => $population,
            'latitude' => 45.0,
            'longitude' => 5.0,
            'tier' => $tier,
        ]);
    }
}
