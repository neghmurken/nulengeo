<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260903120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add the city area, used to derive the per-city guess tolerance';
    }

    public function up(Schema $schema): void
    {
        // Defaulted to 0 only so SQLite accepts adding a NOT NULL column to the already-populated
        // table; app:city:import replaces every row right after, overwriting this placeholder.
        $schema->getTable('city')->addColumn('area_km2', 'float', ['default' => 0]);
    }

    public function down(Schema $schema): void
    {
        $schema->getTable('city')->dropColumn('area_km2');
    }
}
