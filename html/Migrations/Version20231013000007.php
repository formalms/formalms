<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231013000007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
       $this->addSql('ALTER TABLE core_privacypolicy CHANGE lastedit_date lastedit_date DATETIME DEFAULT NULL, CHANGE validity_date validity_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE core_privacypolicy_user CHANGE accept_date accept_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE dashboard_block_config CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE dashboard_blocks CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE dashboard_layouts CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE learning_communication_category_lang CHANGE description description MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_organization_access CHANGE kind kind ENUM(\'user\', \'group\')');
        $this->addSql('ALTER TABLE learning_testtrack_times CHANGE date_begin date_begin DATETIME DEFAULT NULL, CHANGE date_end date_end DATETIME DEFAULT NULL');
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('calendarId', 'learning_course_editions', 'VARCHAR(255) NOT NULL'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
 
        $this->addSql('ALTER TABLE core_privacypolicy CHANGE lastedit_date lastedit_date DATETIME NOT NULL, CHANGE validity_date validity_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE core_privacypolicy_user CHANGE accept_date accept_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE dashboard_block_config CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE dashboard_blocks CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE dashboard_layouts CHANGE created_at created_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_communication_category_lang CHANGE description description MEDIUMTEXT NOT NULL');
        $this->addSql('ALTER TABLE learning_organization_access CHANGE kind kind LONGTEXT DEFAULT \'\' NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE learning_testtrack_times CHANGE date_begin date_begin DATETIME NOT NULL, CHANGE date_end date_end DATETIME NOT NULL');
    }
}
