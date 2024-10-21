<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormaLms\lib\Helpers\HelperTool;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241029000018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping signature field into core_user table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(HelperTool::dropColumnIfExistsQueryBuilder());
        $this->addSql($this->dropColumnIfExists('core_user','signature'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE core_user ADD signature text NOT NULL');

    }

    private function dropColumnIfExists($table, $column) {

        return 'CALL drop_column_if_exists ( "'.$table.'", "'. $column .'")';
    }

}
