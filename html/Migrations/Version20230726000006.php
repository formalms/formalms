<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230726000006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        $this->addSql('DROP TABLE IF EXISTS `learning_certificate_meta`');
        $this->addSql('DROP TABLE IF EXISTS `learning_certificate_meta_assign`');
        $this->addSql('DROP TABLE IF EXISTS `learning_certificate_meta_course`');

        $this->addSql('ALTER TABLE `core_deleted_user` CHANGE lastenter lastenter DATETIME DEFAULT NULL, CHANGE pwd_expire_at pwd_expire_at DATETIME DEFAULT NULL, CHANGE register_date register_date DATETIME DEFAULT NULL, CHANGE deletion_date deletion_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_deleted_user` SET `lastenter` = NULL WHERE `lastenter` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_deleted_user` SET `pwd_expire_at` = NULL WHERE `pwd_expire_at` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_deleted_user` SET `register_date` = NULL WHERE `register_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_deleted_user` SET `deletion_date` = NULL WHERE `deletion_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_event_property` CHANGE property_date property_date DATE DEFAULT NULL');
        $this->addSql('UPDATE `core_event_property` SET `property_date` = NULL WHERE `property_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_lang_translation` CHANGE save_date save_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_lang_translation` SET `save_date` = NULL WHERE `save_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_message` CHANGE posted posted DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_message` SET `posted` = NULL WHERE `posted` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_newsletter` CHANGE stime stime DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_newsletter` SET `stime` = NULL WHERE `stime` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_newsletter_sendto` CHANGE stime stime DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_newsletter_sendto` SET `stime` = NULL WHERE `stime` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_password_history` CHANGE pwd_date pwd_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_password_history` SET `pwd_date` = NULL WHERE `pwd_date` = "0000-00-00 00:00:00"');

        $this->addSql('UPDATE `core_privacypolicy` SET `lastedit_date` = NULL WHERE `lastedit_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_privacypolicy` SET `validity_date` = NULL WHERE `validity_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_pwd_recover` CHANGE request_date request_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_pwd_recover` SET `request_date` = NULL WHERE `request_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rest_authentication` CHANGE generation_date generation_date DATETIME DEFAULT NULL, CHANGE `expiry_date` `expiry_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rest_authentication` SET `generation_date` = NULL WHERE `generation_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_rest_authentication` SET `expiry_date` = NULL WHERE `expiry_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_revision` CHANGE rev_date rev_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_revision` SET `rev_date` = NULL WHERE `rev_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rules` CHANGE creation_date creation_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rules` SET `creation_date` = NULL WHERE `creation_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rules_log` CHANGE log_time log_time DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rules_log` SET `log_time` = NULL WHERE `log_time` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_transaction` CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date_activated date_activated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_transaction` SET `date_creation` = NULL WHERE `date_creation` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_transaction` SET `date_activated` = NULL WHERE `date_activated` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user` CHANGE pwd_expire_at pwd_expire_at DATETIME DEFAULT NULL, CHANGE register_date register_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user` SET `pwd_expire_at` = NULL WHERE `pwd_expire_at` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_user` SET `register_date` = NULL WHERE `register_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_file` CHANGE uldate uldate DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_file` SET `uldate` = NULL WHERE `uldate` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_log_attempt` CHANGE attempt_at attempt_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_log_attempt` SET `attempt_at` = NULL WHERE `attempt_at` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_profileview` CHANGE date_view date_view DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_profileview` SET `date_view` = NULL WHERE `date_view` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_temp` CHANGE request_on request_on DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_temp` SET `request_on` = NULL WHERE `request_on` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_wiki` CHANGE creation_date creation_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_wiki` SET `creation_date` = NULL WHERE `creation_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_wiki_page_info` CHANGE last_update last_update DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_wiki_page_info` SET `last_update` = NULL WHERE `last_update` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_wiki_revision` CHANGE rev_date rev_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_wiki_revision` SET `rev_date` = NULL WHERE `rev_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_advice` CHANGE posted posted DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_advice` SET `posted` = NULL WHERE `posted` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_certificate_assign` CHANGE on_date on_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_certificate_assign` SET `on_date` = NULL WHERE `on_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_classroom_calendar` CHANGE `start_date` `start_date` DATETIME DEFAULT NULL, CHANGE `end_date` `end_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_classroom_calendar` SET `start_date` = NULL WHERE `start_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_classroom_calendar` SET `end_date` = NULL WHERE `end_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_comment_ajax` CHANGE posted_on posted_on DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_comment_ajax` SET `posted_on` = NULL WHERE `posted_on` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_commontrack` CHANGE `dateAttempt` `dateAttempt` DATETIME DEFAULT NULL, CHANGE `firstAttempt` `firstAttempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_commontrack` SET `dateAttempt` = NULL WHERE `dateAttempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_commontrack` SET `firstAttempt` = NULL WHERE `firstAttempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_communication` CHANGE publish_date publish_date DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_communication` SET `publish_date` = NULL WHERE `publish_date` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_communication_track` CHANGE `dateAttempt` `dateAttempt` DATETIME DEFAULT NULL, CHANGE `firstAttempt` `firstAttempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_communication_track` SET `dateAttempt` = NULL WHERE `dateAttempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_communication_track` SET `firstAttempt` = NULL WHERE `firstAttempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_competence_track` CHANGE date_assignment date_assignment DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_competence_track` SET `date_assignment` = NULL WHERE `date_assignment` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_competence_user` CHANGE last_assign_date last_assign_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_competence_user` SET `last_assign_date` = NULL WHERE `last_assign_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course` CHANGE `date_begin` `date_begin` DATE DEFAULT NULL, CHANGE `date_end` `date_end` DATE DEFAULT NULL, CHANGE `create_date` `create_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course` SET `date_begin` = NULL WHERE `date_begin` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `date_end` = NULL WHERE `date_end` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `create_date` = NULL WHERE `create_date` = "0000-00-00 00:00:00"');
        
        $this->addSql('ALTER TABLE `learning_coursepath_user` CHANGE `date_assign` `date_assign` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_coursepath_user` SET `date_assign` = NULL WHERE `date_assign` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_coursereport_score` CHANGE `date_attempt` `date_attempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_coursereport_score` SET `date_attempt` = NULL WHERE `date_attempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course_date` CHANGE `sub_start_date` `sub_start_date` DATETIME DEFAULT NULL, CHANGE `sub_end_date` `sub_end_date` DATETIME DEFAULT NULL, CHANGE `unsubscribe_date_limit` `unsubscribe_date_limit` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_date` SET `sub_start_date` = NULL WHERE `sub_start_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date` SET `sub_end_date` = NULL WHERE `sub_end_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date` SET `unsubscribe_date_limit` = NULL WHERE `unsubscribe_date_limit` = "0000-00-00 00:00:00"');
        
        $this->addSql('ALTER TABLE `learning_course_date_day` CHANGE `date_begin` `date_begin` DATETIME DEFAULT NULL, CHANGE `date_end` `date_end` DATETIME DEFAULT NULL, CHANGE `pause_begin` `pause_begin` DATETIME DEFAULT NULL, CHANGE `pause_end` `pause_end` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_date_day` SET `date_begin` = NULL WHERE `date_begin` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date_day` SET `date_end` = NULL WHERE `date_end` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date_day` SET `pause_begin` = NULL WHERE `pause_begin` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date_day` SET `pause_end` = NULL WHERE `pause_end` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course_date_presence` CHANGE `day` `day` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_date_presence` SET `day` = NULL WHERE `day` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_course_date_user` CHANGE `date_subscription` `date_subscription` DATETIME DEFAULT NULL, CHANGE `date_complete` `date_complete` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_date_user` SET `date_subscription` = NULL WHERE `date_subscription` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_date_user` SET `date_complete` = NULL WHERE `date_complete` = "0000-00-00 00:00:00"');
       
        $this->addSql('ALTER TABLE `learning_course_editions` CHANGE `date_begin` `date_begin` DATE DEFAULT NULL, CHANGE `date_end` `date_end` DATETIME DEFAULT NULL, CHANGE `sub_date_begin` `sub_date_begin` DATE DEFAULT NULL, CHANGE `sub_date_end` `sub_date_end` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_editions` SET `date_begin` = NULL WHERE `date_begin` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course_editions` SET `date_end` = NULL WHERE `date_end` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_editions` SET `sub_date_begin` = NULL WHERE `sub_date_begin` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course_editions` SET `sub_date_end` = NULL WHERE `sub_date_end` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_course_editions_user` CHANGE `date_subscription` `date_subscription` DATETIME DEFAULT NULL, CHANGE `date_complete` `date_complete` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course_editions_user` SET `date_subscription` = NULL WHERE `date_subscription` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_course_editions_user` SET `date_complete` = NULL WHERE `date_complete` = "0000-00-00 00:00:00"');
       
        $this->addSql('ALTER TABLE `learning_forummessage` CHANGE `posted` `posted` DATETIME DEFAULT NULL, CHANGE `modified_by_on` `modified_by_on` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_forummessage` SET `posted` = NULL WHERE `posted` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_forummessage` SET `modified_by_on` = NULL WHERE `modified_by_on` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_forumthread` CHANGE `posted` `posted` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_forumthread` SET `posted` = NULL WHERE `posted` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_forum_timing` CHANGE `last_access` `last_access` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_forum_timing` SET `last_access` = NULL WHERE `last_access` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_games` CHANGE `start_date` `start_date` DATE DEFAULT NULL, CHANGE `end_date` `end_date` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_games` SET `start_date` = NULL WHERE `start_date` = "0000-00-00"');
        $this->addSql('UPDATE `learning_games` SET `end_date` = NULL WHERE `end_date` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_games_track` CHANGE `dateAttempt` `dateAttempt` DATETIME DEFAULT NULL, CHANGE `firstAttempt` `firstAttempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_games_track` SET `dateAttempt` = NULL WHERE `dateAttempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_games_track` SET `firstAttempt` = NULL WHERE `firstAttempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_homerepo` CHANGE `dateInsert` `dateInsert` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_homerepo` SET `dateInsert` = NULL WHERE `dateInsert` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_instmsg` CHANGE `data` `data` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_instmsg` SET `data` = NULL WHERE `data` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_light_repo_files` CHANGE `post_date` `post_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_light_repo_files` SET `post_date` = NULL WHERE `post_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_light_repo_user` CHANGE `last_enter` `last_enter` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_light_repo_user` SET `last_enter` = NULL WHERE `last_enter` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_news_internal` CHANGE `publish_date` `publish_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_news_internal` SET `publish_date` = NULL WHERE `publish_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_notes` CHANGE `data` `data` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_notes` SET `data` = NULL WHERE `data` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_organization` CHANGE `dateInsert` `dateInsert` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_organization` SET `dateInsert` = NULL WHERE `dateInsert` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_polltrack` CHANGE `date_attempt` `date_attempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_polltrack` SET `date_attempt` = NULL WHERE `date_attempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_prj_news` CHANGE `ndate` `ndate` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_prj_news` SET `ndate` = NULL WHERE `ndate` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_repo` CHANGE `dateInsert` `dateInsert` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_repo` SET `dateInsert` = NULL WHERE `dateInsert` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_report_filter` CHANGE `creation_date` `creation_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_report_filter` SET `creation_date` = NULL WHERE `creation_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_report_schedule` CHANGE `creation_date` `creation_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_report_schedule` SET `creation_date` = NULL WHERE `creation_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_reservation_events` CHANGE `date` `date` DATE DEFAULT NULL, CHANGE `deadLine` `deadLine` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_reservation_events` SET `date` = NULL WHERE `date` = "0000-00-00"');
        $this->addSql('UPDATE `learning_reservation_events` SET `deadLine` = NULL WHERE `deadLine` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_scorm_tracking_history` CHANGE `date_action` `date_action` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_scorm_tracking_history` SET `date_action` = NULL WHERE `date_action` = "0000-00-00 00:00:00"');
        
        $this->addSql('ALTER TABLE `learning_statuschangelog` CHANGE `when_do` `when_do` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_statuschangelog` SET `when_do` = NULL WHERE `when_do` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_sysforum` CHANGE `posted` `posted` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_sysforum` SET `posted` = NULL WHERE `posted` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_testtrack` CHANGE `date_attempt` `date_attempt` DATETIME DEFAULT NULL, CHANGE `date_end_attempt` `date_end_attempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_testtrack` SET `date_attempt` = NULL WHERE `date_attempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_testtrack` SET `date_end_attempt` = NULL WHERE `date_end_attempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_testtrack_times` CHANGE `date_attempt` `date_attempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_testtrack_times` SET `date_attempt` = NULL WHERE `date_attempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_time_period` CHANGE `start_date` `start_date` DATE DEFAULT NULL, CHANGE `end_date` `end_date` DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_time_period` SET `start_date` = NULL WHERE `start_date` = "0000-00-00"');
        $this->addSql('UPDATE `learning_time_period` SET `end_date` = NULL WHERE `end_date` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_trackingeneral` CHANGE `timeof` `timeof` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_trackingeneral` SET `timeof` = NULL WHERE `timeof` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_tracksession` CHANGE `enterTime` `enterTime` DATETIME DEFAULT NULL, CHANGE `lastTime` `lastTime` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_tracksession` SET `enterTime` = NULL WHERE `enterTime` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_tracksession` SET `lastTime` = NULL WHERE `lastTime` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_transaction` CHANGE `date` `date` DATETIME DEFAULT NULL, CHANGE `date_confirm` `date_confirm` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_transaction` SET `date` = NULL WHERE `date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_transaction` SET `date_confirm` = NULL WHERE `date_confirm` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `dashboard_layouts` CHANGE `created_at` `created_at` TIMESTAMP DEFAULT NULL');
        $this->addSql('UPDATE `dashboard_layouts` SET `created_at` = NULL WHERE `created_at` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `dashboard_block_config` CHANGE `created_at` `created_at` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `dashboard_block_config` SET `created_at` = NULL WHERE `created_at` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `dashboard_blocks` CHANGE `created_at` `created_at` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `dashboard_blocks` SET `created_at` = NULL WHERE `created_at` = "0000-00-00 00:00:00"');


        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
      
    }
}