<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240923151023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
		$this->addSql("INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES ('exclude_completed_courses', 'off', 'enum', 3, '0', 4, 18, 1, 0, '')"); 
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM `core_setting` WHERE `core_setting`.`param_name` = 'exclude_completed_courses'");
    }
}
