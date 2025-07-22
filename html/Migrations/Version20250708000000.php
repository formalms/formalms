<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250708000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE t1 FROM core_field_type t1
                            JOIN core_field_type t2 
                            ON t1.type_file = t2.type_file AND t1.id > t2.id");

        $this->addSql("ALTER TABLE core_field_type ADD UNIQUE KEY unique_typefile (type_file)");
        $this->addSql("DELETE t1 FROM core_setting t1
                            JOIN core_setting t2 
                            ON t1.param_name = t2.param_name AND t1.regroup = t2.regroup AND t1.id > t2.id");
        $this->addSql("ALTER TABLE core_setting ADD UNIQUE KEY unique_config (param_name, regroup)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs


    }
}
