<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;


/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241221130901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding index to learning_trackingeneral';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("create index learning_trackingeneral_timeof_index on learning_trackingeneral (timeof)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop index learning_trackingeneral_timeof_index on learning_trackingeneral');
    }
}
