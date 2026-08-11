<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Name\UnqualifiedName;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260811194146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the city reference table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('city');
        $table->addColumn('insee_code', 'string', ['length' => 5]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('population', 'integer');
        $table->addColumn('latitude', 'float');
        $table->addColumn('longitude', 'float');
        $table->addColumn('tier', 'string', ['length' => 10]);
        $table->addPrimaryKeyConstraint(new PrimaryKeyConstraint(
            null,
            [UnqualifiedName::unquoted('insee_code')],
            false,
        ));
        $table->addIndex(['tier']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('city');
    }
}
