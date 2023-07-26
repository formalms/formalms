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

        $this->addSql('UPDATE `core_privacypolicy` SET `lastedit_date` = NULL WHERE `lastedit` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_privacypolicy` SET `validity_date` = NULL WHERE `validity` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_pwd_recover` CHANGE request_date request_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_pwd_recover` SET `request_date` = NULL WHERE `request_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rest_authentication` CHANGE generation_date generation_date DATETIME DEFAULT NULL, `expiry_date` `expiry_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rest_authentication` SET `generation_date` = NULL WHERE `generation_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_rest_authentication` SET `expiry_date` = NULL WHERE `expiry_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_revision` CHANGE request_date request_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_revision` SET `request_date` = NULL WHERE `request_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rules` CHANGE creation_date creation_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rules` SET `creation_date` = NULL WHERE `creation_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_rules_log` CHANGE log_time log_time DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_rules_log` SET `log_time` = NULL WHERE `log_time` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_transaction` CHANGE date_creation date_creation DATETIME DEFAULT NULL, CHANGE date_activated date_activated DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_transaction` SET `date_creation` = NULL WHERE `date_creation` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_transaction` SET `date_activated` = NULL WHERE `date_activated` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user` CHANGE pwd_expired_at pwd_expired_at DATETIME DEFAULT NULL, CHANGE register_date register_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user` SET `pwd_expired_at` = NULL WHERE `pwd_expired_at` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `core_user` SET `register_date` = NULL WHERE `register_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_file` CHANGE uldate uldate DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_file` SET `uldate` = NULL WHERE `uldate` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `core_user_log_attempt` CHANGE attempt_at attempt_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `core_user_log_attempts` SET `attempt_at` = NULL WHERE `attempt_at` = "0000-00-00 00:00:00"');

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

        $this->addSql('ALTER TABLE `learning_certificate_meta_assign` CHANGE on_date on_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_certificate_meta_assign` SET `on_date` = NULL WHERE `on_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_classroom_calendar` CHANGE `start_date` `start_date` DATETIME DEFAULT NULL, `end_date` `end_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_classroom_calendar` SET `start_date` = NULL WHERE `start_date` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_classroom_calendar` SET `end_date` = NULL WHERE `end_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_comment_ajax` CHANGE posted_on posted_on DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_comment_ajax` SET `posted_on` = NULL WHERE `posted_on` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_commontrack` CHANGE `dateAttempt` `dateAttempt` DATETIME DEFAULT NULL, `firstAttempt` `firstAttempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_commontrack` SET `dateAttempt` = NULL WHERE `dateAttempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_commontrack` SET `firstAttempt` = NULL WHERE `firstAttempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_communication` CHANGE publish_date publish_date DATE DEFAULT NULL');
        $this->addSql('UPDATE `learning_communication` SET `publish_date` = NULL WHERE `publish_date` = "0000-00-00"');

        $this->addSql('ALTER TABLE `learning_communication_track` CHANGE `dateAttempt` `dateAttempt` DATETIME DEFAULT NULL, `firstAttempt` `firstAttempt` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_communication_track` SET `dateAttempt` = NULL WHERE `dateAttempt` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_communication_track` SET `firstAttempt` = NULL WHERE `firstAttempt` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_competence_track` CHANGE date_assignment date_assignment DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_competence_track` SET `date_assignment` = NULL WHERE `date_assignment` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_competence_user` CHANGE last_assign_date last_assign_date DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_competence_user` SET `last_assign_date` = NULL WHERE `last_assign_date` = "0000-00-00 00:00:00"');

        $this->addSql('ALTER TABLE `learning_course` CHANGE `date_begin` `date_begin` DATE DEFAULT NULL, `date_end` `date_end` DATE DEFAULT NULL, `create_date` `create_date` DATETIME DEFAULT NULL');
        $this->addSql('UPDATE `learning_course` SET `date_begin` = NULL WHERE `date_begin` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `date_end` = NULL WHERE `date_end` = "0000-00-00"');
        $this->addSql('UPDATE `learning_course` SET `create_date` = NULL WHERE `create_date` = "0000-00-00 00:00:00"');
        

        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
      
    }
}