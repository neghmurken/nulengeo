<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\City;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final readonly class Cities
{
    public function __construct(private Connection $connection)
    {
    }

    /**
     * @return list<City>
     */
    public function draw(string $tier, int $count): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT insee_code, name, population, latitude, longitude FROM city WHERE tier = ? ORDER BY RANDOM() LIMIT ?',
            [$tier, $count],
            [ParameterType::STRING, ParameterType::INTEGER],
        );

        return array_map(
            static fn (array $row): City => new City(
                $row['insee_code'],
                $row['name'],
                (int) $row['population'],
                (float) $row['latitude'],
                (float) $row['longitude'],
            ),
            $rows,
        );
    }
}
