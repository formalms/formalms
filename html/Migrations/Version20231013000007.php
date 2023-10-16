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

        $this->addSql('ALTER TABLE `learning_organization` CHANGE `publish_from` `publish_from` DATETIME DEFAULT NULL, CHANGE `publish_to` `publish_to` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_organization` SET `publish_from` = NULL WHERE `publish_from` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_organization` SET `publish_to` = NULL WHERE `publish_to` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rest_authentication` CHANGE last_enter_date last_enter_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rest_authentication` SET `last_enter_date` = NULL WHERE `last_enter_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_commontrack` CHANGE `last_complete` `last_complete` DATETIME DEFAULT NULL, CHANGE `first_complete` `first_complete` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_commontrack` SET `last_complete` = NULL WHERE `last_complete` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_commontrack` SET `first_complete` = NULL WHERE `first_complete` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course` CHANGE `sub_start_date` `sub_start_date` DATE DEFAULT NULL, CHANGE `sub_end_date` `sub_end_date` DATE DEFAULT NULL, CHANGE `unsubscribe_date_limit` `unsubscribe_date_limit` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course` SET `sub_start_date` = NULL WHERE `sub_start_date` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `sub_end_date` = NULL WHERE `sub_end_date` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `unsubscribe_date_limit` = NULL WHERE `unsubscribe_date_limit` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course_date_user` CHANGE `requesting_unsubscribe_date` `requesting_unsubscribe_date` DATETIME DEFAULT NULL, CHANGE `date_complete` `date_complete` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_date_user` SET `requesting_unsubscribe_date` = NULL WHERE `requesting_unsubscribe_date` = "0000-00-00 00:00:00"');
       
        $this->addSql('ALTER TABLE `learning_course_editions_user` CHANGE `requesting_unsubscribe_date` `requesting_unsubscribe_date` DATETIME DEFAULT NULL, CHANGE `date_complete` `date_complete` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_editions_user` SET `requesting_unsubscribe_date` = NULL WHERE `requesting_unsubscribe_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_report_schedule` CHANGE `last_execution` `last_execution` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_report_schedule` SET `last_execution` = NULL WHERE `last_execution` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_testtrack` CHANGE `date_attempt_mod` `date_attempt_mod` DATETIME DEFAULT NULL, CHANGE `suspended_until` `suspended_until` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_testtrack` SET `date_attempt_mod` = NULL WHERE `date_attempt_mod` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_testtrack` SET `suspended_until` = NULL WHERE `suspended_until` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_testtrack_times` CHANGE `date_begin` `date_begin` DATETIME DEFAULT NULL, CHANGE `date_end` `date_end` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_testtrack_times` SET `date_begin` = NULL WHERE `date_begin` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_testtrack_times` SET `date_end` = NULL WHERE `date_end` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `conference_rules_user` CHANGE banned_until banned_until DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `conference_rules_user` SET `banned_until` = NULL WHERE `banned_until` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_calendar` CHANGE `create_date` `create_date` DATE DEFAULT NULL, CHANGE `start_date` `start_date` DATE DEFAULT NULL, CHANGE `end_date` `end_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_calendar` SET `create_date` = NULL WHERE `create_date` = "0000-00-00"');
        $this->addSql('UPDATE `core_calendar` SET `start_date` = NULL WHERE `start_date` = "0000-00-00"');
        $this->addSql('UPDATE `core_calendar` SET `end_date` = NULL WHERE `end_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_privacypolicy_user` CHANGE accept_date accept_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_privacypolicy_user` SET `accept_date` = NULL WHERE `accept_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_task` CHANGE last_execution last_execution DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_task` SET `last_execution` = NULL WHERE `last_execution` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_scorm_tracking` CHANGE `first_access` `first_access` DATETIME DEFAULT NULL, CHANGE `last_access` `last_access` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_scorm_tracking` SET `first_access` = NULL WHERE `first_access` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_scorm_tracking` SET `last_access` = NULL WHERE `last_access` = "0000-00-00 00:00:00"');


        $this->addSql('ALTER TABLE `learning_testtrack_page` CHANGE `display_from` `display_from` DATETIME DEFAULT NULL, CHANGE `display_to` `display_to` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_testtrack_page` SET `display_from` = NULL WHERE `display_from` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_testtrack_page` SET `display_to` = NULL WHERE `display_to` = "0000-00-00 00:00:00"');




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
