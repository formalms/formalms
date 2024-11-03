<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240115000011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding force scorm finish in core_setting';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES
                        ('force_scorm_finish', 'on', 'enum', 3, '0', 4, 17, 1, 0, '')");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM `core_setting` WHERE `core_setting`.`param_name` = 'force_scorm_finish'");

    }
}
