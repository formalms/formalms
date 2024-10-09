<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
      $this->addSql("INSERT INTO `core_calendar` CHANGE `start_date` `start_date` DATETIME NULL DEFAULT NULL"); 

      $this->addSql("UPDATE `core_menu_under` SET `default_name` = '_CATEGORIES' WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORY'");
      $this->addSql("UPDATE `core_menu` SET `name` = '_CATEGORIES' WHERE `idMenu` = ( SELECT max(`idMenu`) FROM `core_menu_under` WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORIES' )");
    }

    public function down(Schema $schema): void
    {
      $this->addSql("UPDATE `core_menu_under` SET `default_name` = '_CATEGORY' WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORIES'");
      $this->addSql("UPDATE `core_menu` SET `name` = '_CATEGORY' WHERE `idMenu` = ( SELECT max(`idMenu`) FROM `core_menu_under` WHERE `module_name` = 'reservation' AND `default_name` = '_CATEGORY' )");
    }
}
