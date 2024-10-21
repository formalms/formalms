<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021000019 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add column for course path logo';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE learning_coursepath ADD `img_coursepath` varchar(255)');
        $this->addSql('ALTER TABLE learning_coursepath ADD `check_path_info` int');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE learning_coursepath DROP COLUMN `img_coursepath`');
        $this->addSql('ALTER TABLE learning_coursepath DROP COLUMN `check_path_info`');

    }

}
