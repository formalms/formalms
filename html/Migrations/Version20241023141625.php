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
        $connection = $this->connection;

        $arrayCoreMenuUnder = [
            '_LABEL',
            '_CONTEST',
            '_NEWS_INTERNAL',
        ];

        $arrayDefaultNameCoreMenuUnder = $arrayDefaultNameCoreMenu =implode(', ', array_map(function ($element) {
            return '"' . $element . '"';
        }, $arrayCoreMenuUnder));

        $connection->executeStatement('DELETE FROM `core_menu_under` WHERE `default_name` IN (?)',
            [$arrayDefaultNameCoreMenuUnder]);

        $connection->executeStatement(
            'DELETE FROM `core_menu` WHERE `name` IN (?)',
            [$arrayDefaultNameCoreMenu]);


        $arrayCoreSetting = [
            'use_course_label',
            'ldap%',
            'sso%',
        ];

        $arrayParamNameCoreSetting = implode(', ', array_map(function ($element) {
            return '"' . $element . '"';
        }, $arrayCoreSetting));

        $connection->executeStatement('DELETE FROM `core_setting` WHERE `param_name` IN (?)', [$arrayParamNameCoreSetting]);


        $arrayLearningMiddleArea = [
            'tb_games',
            'news',
        ];

        $arrayObjIndexLearningMiddleArea = implode(', ', array_map(function ($element) {
            return '"' . $element . '"';
        }, $arrayLearningMiddleArea));

        $connection->executeStatement('DELETE FROM `learning_middlearea` WHERE `obj_index` IN (?)',
             [$arrayObjIndexLearningMiddleArea]
        );



       $connection->executeStatement('DELETE FROM `dashboard_blocks` WHERE `block_class` = (?)', ['DashboardBlockNewsLms']);
            
        $connection->executeStatement('DELETE FROM `dashboard_block_config` WHERE `block_class` = (?)', ['DashboardBlockNewsLms']);
            
        

       
        $this->addSql("UPDATE core_menu_under set module_name = 'forum' WHERE default_name = '_PUBLIC_FORUM'");

        $arrayModulesOB = [
            '_WIKI',
            '_CALENDAR',
            '_NOTES',
            '_PROJECT'
        ];

        $arrayDefaultNameOB = implode(', ', array_map(function ($element) {
            return '"' . $element . '"';
        }, $arrayModulesOB));
            $connection->executeStatement('DELETE FROM `learning_module` WHERE `default_name` IN  (?)', [$arrayDefaultNameOB]);
        
        // cancellazione tabella progetto e derivate      
        $this->addSql("DELETE FROM `learning_sysforum` WHERE `learning_sysforum`.`key1` ='project_message' AND `learning_sysforum`.`key2` IN (SELECT `learning_prj`.`id` FROM `learning_prj`)");
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