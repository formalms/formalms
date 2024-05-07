<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240507000018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add unique coursereport table';
    }

    public function up(Schema $schema): void
    {
        //delete all duplicates
        $this->addSql('DELETE FROM learning_coursereport
                            WHERE id_report NOT IN (
                                SELECT MIN(id_report) 
                                FROM learning_coursereport 
                                GROUP BY source_of, id_course, id_source
                                )');

        $this->addSql('ALTER TABLE learning_coursereport
        ADD CONSTRAINT unique_coursereport UNIQUE (source_of, id_course, id_source)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE learning_coursereport
        DROP INDEX unique_coursereport');
    }
}
