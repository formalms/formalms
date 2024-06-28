<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormaLms\lib\Helpers\HelperTool;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240429000018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping signature field into core_user table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(HelperTool::dropFieldIfExistsQueryBuilder());
        $this->addSql($this->dropFieldIfExists('core_user','signature'));
        $this->addSql(HelperTool::dropProcedure('drop_field_if_exists'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE core_user ADD signature text NOT NULL');

    }

    private function dropFieldIfExists($table, $column) {

        return 'CALL drop_field_if_exists ( "'.$table.'", "'. $column .'")';
    }

}
