<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormaLms\lib\Helpers\HelperTool;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113102634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropdown complex columns';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `core_field_type`(`type_field`, `type_file`, `type_class`, `type_category`) VALUES ("dropdown_complex", "class.dropdown_complex.php", "Field_DropdownComplex", "standard")');
        $this->addSql('INSERT INTO `core_field_type` (`type_field`, `type_file`, `type_class`, `type_category`) VALUES ("copy", "class.copy.php", "Copy", "standard")');

        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('belongs_to', 'core_field_son', 'INT(11) NOT NULL AFTER `idField`'));
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('father', 'core_field', 'INT(11) NOT NULL '));
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('son', 'core_field', 'INT(11) NOT NULL '));
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('selected', 'core_field_son', 'INT(11) NULL '));
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('copy_of', 'core_field', 'INT(11) NULL DEFAULT NULL AFTER `use_multilang` '));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `core_field_type` WHERE `type_field` = "dropdown_complex"');
        $this->addSql('DELETE FROM `core_field_type` WHERE `type_field` = "copy_of"');
        $this->addSql(HelperTool::dropColumnIfExistsQueryBuilder());
        $this->addSql($this->dropColumnIfExists('core_field_son','belongs_to'));
        $this->addSql($this->dropColumnIfExists('core_field','father'));
        $this->addSql($this->dropColumnIfExists('core_field','son'));
        $this->addSql($this->dropColumnIfExists('core_field_son','selected'));
        $this->addSql($this->dropColumnIfExists('core_field','copy_of'));

    }

    private function dropColumnIfExists($table, $column) {

        return 'CALL drop_column_if_exists ( "'.$table.'", "'. $column .'")';
    }


}
