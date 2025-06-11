<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250529000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
           $this->addSql("INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) 
                        SELECT  'sso_lifetime', `param_value`, `value_type`, `max_size`, '0', `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info` FROM `core_setting` WHERE param_name = 'rest_auth_lifetime' ");
   }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM `core_setting` WHERE  `param_name` = 'sso_lifetime'");
  
    }
}
