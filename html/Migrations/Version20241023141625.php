<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023141625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `core_menu_under` WHERE `default_name` IN ("_LABEL","_CONTEST","_NEWS_INTERNAL")');

        $this->addSql('DELETE FROM `core_menu` WHERE `name` IN ("_LABEL","_CONTEST","_NEWS_INTERNAL")');

        $this->addSql('DELETE FROM `core_setting` WHERE `param_name` IN ("use_course_label") OR `param_name` LIKE "ldap%" OR `param_name` LIKE "sso%"');

        $this->addSql('DELETE FROM `learning_middlearea` WHERE `obj_index` IN ("tb_games", "news")');

        $this->addSql('DELETE FROM `dashboard_blocks` WHERE `block_class` = "DashboardBlockNewsLms"');

        $this->addSql('DELETE FROM `dashboard_block_config` WHERE `block_class` = "DashboardBlockNewsLms"');
   
        $this->addSql("UPDATE core_menu_under set module_name = 'forum' WHERE default_name = '_PUBLIC_FORUM'");

        $this->addSql('DELETE FROM `learning_module` WHERE `default_name` IN ("_WIKI", "_CALENDAR", "_NOTES", "_PROJECT")');

        // cancellazione tabella progetto e derivate      
        $schemaTable = $this->connection->createSchemaManager();
        if ($schemaTable->tablesExist(['learning_prj'])) {
            $this->addSql("DELETE FROM `learning_sysforum` 
            WHERE `key1` = 'project_message' 
            AND `key2` IN (SELECT `id` FROM `learning_prj`)");
        }

        $this->addSql('DROP TABLE IF EXISTS `learning_prj_files`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_tasks`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_news`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_todo`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_users`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj`');

        // cancellazione tabella blocco note
        $this->addSql('DROP TABLE IF EXISTS `learning_notes`');

        //cancellazione tabella calendar e derivate
        $this->addSql('DROP TABLE IF EXISTS `learning_calendar`');
        $this->addSql('DROP TABLE IF EXISTS `learning_classroom_calendar`');
        $this->addSql('DROP TABLE IF EXISTS `core_calendar`');

        //cancellazione tabella wiki e derivate
        $this->addSql('DROP TABLE IF EXISTS `learning_wiki_course`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_page_info`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_revision`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_page`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki`');
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}