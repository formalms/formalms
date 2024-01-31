<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240131000012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change enum field in learning_report table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `learning_report` MODIFY COLUMN `use_user_selection` TINYINT(1) DEFAULT 0");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE `learning_report` MODIFY COLUMN `use_user_selection` ENUM('true','false') NOT NULL DEFAULT 'true'");

    }
}
