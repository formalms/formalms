<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        $this->addSql('DROP TABLE IF EXISTS `learning_certiticate_meta`');
        $this->addSql('DROP TABLE IF EXISTS `learning_certiticate_meta_assign`');
        $this->addSql('DROP TABLE IF EXISTS `learning_certiticate_meta_course`');
        $this->addSql('DROP TABLE IF EXISTS `conference_teleskill`');
        $this->addSql('DROP TABLE IF EXISTS `conference_teleskill_log`');
        $this->addSql('DROP TABLE IF EXISTS `conference_teleskill_room`');
        $this->addSql('DROP TABLE IF EXISTS `conference_chat_msg`');
        $this->addSql('DROP TABLE IF EXISTS `conference_chatperm`');
        $this->addSql('DROP TABLE IF EXISTS `conference_dimdim`');

        /** FOREIGN KEYS **/
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_lang_translation_ibfk_1', 'core_lang_translation'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_lang_translation_ibfk_2', 'core_lang_translation'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_role_ibfk_1', 'core_role'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_role_members_ibfk_1', 'core_role_members'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('config_layout_fk', 'dashboard_block_config'));
        /** FOREIGN KEYS **/       
        
        /** INDEXES **/
        $this->addSql(HelperTool::dropIndexIfExistsQueryBuilder());

        $this->addSql($this->dropIndexIfExists('class','core_event_class'));
        $this->addSql($this->dropIndexIfExists('id_common','core_field'));
        $this->addSql($this->dropIndexIfExists('idstMember','core_group_members'));
        $this->addSql($this->dropIndexIfExists('lang_code','core_lang_translation'));
        $this->addSql($this->dropIndexIfExists('roleid','core_role'));
        $this->addSql($this->dropIndexIfExists('google_id_2','core_user'));
        $this->addSql($this->dropIndexIfExists('twitter_id_2','core_user'));
        $this->addSql($this->dropIndexIfExists('linkedin_id_2','core_user'));
        $this->addSql($this->dropIndexIfExists('facebook_id_2','core_user'));
        $this->addSql($this->dropIndexIfExists('id_course','learning_certificate_assign'));
        $this->addSql($this->dropIndexIfExists('id_user','learning_certificate_assign'));
        $this->addSql($this->dropIndexIfExists('idcourse','learning_courseuser'));   
        $this->addSql($this->dropIndexIfExists('courseuser_course_idx','learning_courseuser'));       
        $this->addSql($this->dropIndexIfExists('iduser','learning_courseuser'));
        $this->addSql($this->dropIndexIfExists('iduser','learning_courseuser'));
        $this->addSql($this->dropIndexIfExists('kind','learning_organization_access'));
        $this->addSql($this->dropIndexIfExists('idObject','learning_organization_access'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_customfield_area'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_customfield_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_customfield_son_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_privacypolicy_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_communication_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_kb_tree_info'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_wiki_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_transaction_info'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_testtrack_times'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_testtrack_quest'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_org_chart_fieldentry'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_org_chart_field'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_org_chart'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_newsletter_sendto'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_message_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_lang_translation'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_lang_language'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_hteditor'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_group_user_waiting'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_group_members'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_group_fields'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_group'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_fncrole_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_fncrole_group_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_fncrole_competence'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_fncrole'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_field_userentry'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_field_type'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_event_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_event_property'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_event_consumer_class'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_customfield_type'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_customfield_entry'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_connector'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_connection'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_code_org'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_code_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_code_association'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_code'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_admin_tree'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_admin_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','conference_rules_root'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','conference_rules_admin'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_certificate_assign'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_catalogue_member'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_catalogue_entry'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_calendar'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_assessment_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_aggregated_cert_assign'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_adviceuser'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','dashboard_permission'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_wiki_revision'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_wiki_page_info'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_user_temp'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_user_profileview'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_transaction_info'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_tag_resource'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_tag_relation'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_setting_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_setting_list'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_setting_group'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_setting'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_rules_entity'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_role_members'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_role'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_revision'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_rest_authentication'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_reg_setting'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_reg_list'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_pwd_recover'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_privacypolicy_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_platform'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','core_password_history'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_certificate_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_certificate_tags'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_commontrack'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_communication_category_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_communication_track'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_competence_category_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_competence_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_competence_lang'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_competence_required'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_competence_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_course_date_presence'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_course_date_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_course_editions_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_coursepath_courses'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_coursepath_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_coursereport_score'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_courseuser'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_forum_access'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_forum_notifier'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_forum_timing'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_games_access'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_games_track'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_htmlfront'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_kb_rel'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_label'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_label_course'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_light_repo_user'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_lo_types'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_menucourse_under'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_menucustom_under'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_middlearea'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_organization_access'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_pollquest_extra'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_polltrack_answer'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_quest_type_poll'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_report_schedule_recipient'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_reservation_perm'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_reservation_subscribed'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_scorm_tracking_history'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_statuschangelog'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_teacher_profile'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_testquest_extra'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_testtrack_answer'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_testtrack_page'));
        $this->addSql($this->dropIndexIfExists('PRIMARY','learning_quest_type'));

        $this->addSql(HelperTool::dropProcedure('drop_index_if_exists'));
    
         /** INDEXES **/  

        $this->addSql("CREATE TABLE IF NOT EXISTS core_domain_configs (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255),
            `domain` varchar(255),
            `parentId` int NULL DEFAULT NULL,
            `template` varchar(255),
            `orgId` int NULL DEFAULT NULL,
            `mailConfigId` int NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->addSql("CREATE TABLE IF NOT EXISTS core_mail_configs (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `title` varchar(255),
            `system` boolean not null default 0
            )ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->addSql("CREATE TABLE IF NOT EXISTS core_mail_configs_fields (
            `id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `mailConfigId` int,
            `type` varchar(255),
            `value` varchar(255)
            )ENGINE=InnoDB DEFAULT CHARSET=utf8");

        //////////$this up() migration is auto-generated, please modify it to your needs

        /******** CONFERENCE **********/
        $this->addSql('ALTER TABLE conference_booking ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE room_id room_id INT NOT NULL, CHANGE platform platform VARCHAR(255) NOT NULL, CHANGE module module VARCHAR(100) NOT NULL, CHANGE user_idst user_idst INT NOT NULL, CHANGE approved approved TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE conference_menu ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE conference_menu_under ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idMenu idMenu INT NOT NULL, CHANGE module_name module_name VARCHAR(255) NOT NULL, CHANGE default_name default_name VARCHAR(255) NOT NULL, CHANGE default_op default_op VARCHAR(255) NOT NULL, CHANGE associated_token associated_token VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE class_file class_file VARCHAR(255) NOT NULL, CHANGE class_name class_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE conference_room ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCal idCal BIGINT NOT NULL, CHANGE idCourse idCourse BIGINT NOT NULL, CHANGE idSt idSt BIGINT NOT NULL, CHANGE bookable bookable TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE conference_rules_admin ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX server_status_idx ON conference_rules_admin (server_status)');
        $this->addSql('ALTER TABLE conference_rules_room ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE room_name room_name VARCHAR(255) NOT NULL, CHANGE id_source id_source INT NOT NULL, CHANGE room_parent room_parent INT NOT NULL');
        $this->addSql('ALTER TABLE conference_rules_root ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE max_user_at_time max_user_at_time INT UNSIGNED NOT NULL, CHANGE max_room_at_time max_room_at_time INT UNSIGNED NOT NULL, CHANGE max_subroom_for_room max_subroom_for_room INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX system_type_idx ON conference_rules_root (system_type)');
        $this->addSql('ALTER TABLE conference_rules_user ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE last_hit last_hit INT NOT NULL, CHANGE id_room id_room INT NOT NULL, CHANGE userid userid VARCHAR(255) NOT NULL, CHANGE user_ip user_ip VARCHAR(15) NOT NULL, CHANGE first_name first_name VARCHAR(255) NOT NULL, CHANGE last_name last_name VARCHAR(255) NOT NULL, CHANGE level level INT NOT NULL, CHANGE auto_reload auto_reload TINYINT(1) NOT NULL');
        /******** CONFERENCE **********/

        /******** CORE **********/
        $this->addSql('ALTER TABLE core_admin_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst_user idst_user INT NOT NULL, CHANGE type_of_entry type_of_entry VARCHAR(50) NOT NULL, CHANGE id_entry id_entry INT NOT NULL');
        $this->addSql('CREATE INDEX idst_user_idx ON core_admin_course (idst_user)');
        $this->addSql('CREATE INDEX type_of_entry_idx ON core_admin_course (type_of_entry)');
        $this->addSql('CREATE INDEX id_entry_idx ON core_admin_course (id_entry)');
        $this->addSql('ALTER TABLE core_admin_tree ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst VARCHAR(11) NOT NULL, CHANGE idstAdmin idstAdmin VARCHAR(11) NOT NULL');
        $this->addSql('CREATE INDEX idst_idx ON core_admin_tree (idst)');
        $this->addSql('CREATE INDEX idstAdmin_idx ON core_admin_tree (idstAdmin)');
        $this->addSql('ALTER TABLE core_calendar ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_code ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE code code VARCHAR(255) NOT NULL, CHANGE idCodeGroup idCodeGroup INT NOT NULL, CHANGE used used TINYINT(1) NOT NULL, CHANGE unlimitedUse unlimitedUse TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX code_idx ON core_code (code)');
        $this->addSql('ALTER TABLE core_code_association ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE code code VARCHAR(255) NOT NULL, CHANGE idUser idUser INT NOT NULL');
        $this->addSql('CREATE INDEX code_idx ON core_code_association (code)');
        $this->addSql('CREATE INDEX id_user_idx ON core_code_association (idUser)');
        $this->addSql('ALTER TABLE core_code_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCodeGroup idCodeGroup INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL');
        $this->addSql('ALTER TABLE core_code_groups ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_code_org ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCodeGroup idCodeGroup INT NOT NULL, CHANGE idOrg idOrg INT NOT NULL');
        $this->addSql('CREATE INDEX id_codegroup_idx ON core_code_org (idCodeGroup)');
        $this->addSql('CREATE INDEX id_org_idx ON core_code_org (idOrg)');
        $this->addSql('ALTER TABLE core_connection ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(50) NOT NULL, CHANGE type type VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX name_idx ON core_connection (name)');
        $this->addSql('ALTER TABLE core_connector ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE type type VARCHAR(25) NOT NULL, CHANGE file file VARCHAR(255) NOT NULL, CHANGE class class VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX type_idx ON core_connector (type)');
        $this->addSql('ALTER TABLE core_country ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name_country name_country VARCHAR(64) NOT NULL, CHANGE iso_code_country iso_code_country VARCHAR(3) NOT NULL, CHANGE id_zone id_zone INT NOT NULL');
        $this->addSql('ALTER TABLE core_customfield ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE code code VARCHAR(255) NOT NULL, CHANGE type_field type_field VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE use_multilang use_multilang TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_area ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE area_code area_code VARCHAR(255) NOT NULL, CHANGE area_name area_name VARCHAR(255) NOT NULL, CHANGE area_table area_table VARCHAR(255) NOT NULL, CHANGE area_field area_field VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_entry ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_field id_field VARCHAR(11) NOT NULL, CHANGE id_obj id_obj INT NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_field id_field INT NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_son ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE code code VARCHAR(255) NOT NULL, CHANGE id_field id_field INT NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_son_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_field_son id_field_son INT NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_customfield_type ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE type_field type_field VARCHAR(255) NOT NULL, CHANGE type_file type_file VARCHAR(255) NOT NULL, CHANGE type_class type_class VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX type_field_idx ON core_customfield_type (type_field)');
        $this->addSql('ALTER TABLE core_db_upgrades ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_deleted_user ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE userid userid VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE pass pass VARCHAR(50) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE avatar avatar VARCHAR(255) NOT NULL, CHANGE level level INT NOT NULL, CHANGE valid valid TINYINT(1) NOT NULL, CHANGE deleted_by deleted_by INT NOT NULL');
        $this->addSql('ALTER TABLE core_domain_configs CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_event ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idClass idClass INT NOT NULL, CHANGE module module VARCHAR(50) NOT NULL, CHANGE section section VARCHAR(50) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_event_class ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE class class VARCHAR(50) NOT NULL, CHANGE platform platform VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE core_event_consumer ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE consumer_class consumer_class VARCHAR(50) NOT NULL, CHANGE consumer_file consumer_file VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_event_consumer_class ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idConsumer idConsumer INT NOT NULL, CHANGE idClass idClass INT NOT NULL');
        $this->addSql('CREATE INDEX id_consumer_idx ON core_event_consumer_class (idConsumer)');
        $this->addSql('CREATE INDEX id_class_idx ON core_event_consumer_class (idClass)');
        $this->addSql('ALTER TABLE core_event_manager ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idClass idClass INT NOT NULL, CHANGE recipients recipients VARCHAR(255) NOT NULL, CHANGE show_level show_level LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE core_event_property ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE property_name property_name VARCHAR(50) NOT NULL, CHANGE idEvent idEvent INT NOT NULL');
        $this->addSql('CREATE INDEX property_name_idx ON core_event_property (property_name)');
        $this->addSql('CREATE INDEX id_event_idx ON core_event_property (idEvent)');
        $this->addSql('ALTER TABLE core_event_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE idEventMgr idEventMgr INT NOT NULL, CHANGE channel channel LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE INDEX idst_idx ON core_event_user (idst)');
        $this->addSql('CREATE INDEX id_event_mgr_idx ON core_event_user (idEventMgr)');
        $this->addSql('ALTER TABLE core_field ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_common id_common INT NOT NULL, CHANGE type_field type_field VARCHAR(255) NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE use_multilang use_multilang TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_field_son ADD selected INT NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idField idField INT NOT NULL, CHANGE id_common_son id_common_son INT NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE core_field_type ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE type_field type_field VARCHAR(255) NOT NULL, CHANGE type_file type_file VARCHAR(255) NOT NULL, CHANGE type_class type_class VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX type_field_idx ON core_field_type (type_field)');
        $this->addSql('ALTER TABLE core_field_userentry ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_common id_common INT NOT NULL, CHANGE id_common_son id_common_son INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX id_common_idx ON core_field_userentry (id_common)');
        $this->addSql('CREATE INDEX id_common_son_idx ON core_field_userentry (id_common_son)');
        $this->addSql('CREATE INDEX id_user_idx ON core_field_userentry (id_user)');
        $this->addSql('ALTER TABLE core_fncrole ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_fncrole id_fncrole INT UNSIGNED NOT NULL, CHANGE id_group id_group INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX id_fncrole_idx ON core_fncrole (id_fncrole)');
        $this->addSql('CREATE INDEX id_group_idx ON core_fncrole (id_group)');
        $this->addSql('ALTER TABLE core_fncrole_competence ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_fncrole id_fncrole INT UNSIGNED NOT NULL, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE score score INT UNSIGNED NOT NULL, CHANGE expiration expiration INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX id_fncrole_idx ON core_fncrole_competence (id_fncrole)');
        $this->addSql('CREATE INDEX id_competence_idx ON core_fncrole_competence (id_competence)');
        $this->addSql('ALTER TABLE core_fncrole_group ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_fncrole_group_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_group id_group INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_fncrole_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_fncrole id_fncrole INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_fncrole_idx ON core_fncrole_lang (id_fncrole)');
        $this->addSql('CREATE INDEX lang_code_idx ON core_fncrole_lang (lang_code)');
        $this->addSql('ALTER TABLE core_group ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE groupid groupid VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_group_fields ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE id_field id_field INT NOT NULL, CHANGE user_inherit user_inherit TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX idst_idx ON core_group_fields (idst)');
        $this->addSql('CREATE INDEX id_field_idx ON core_group_fields (id_field)');
        $this->addSql('ALTER TABLE core_group_members ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE idstMember idstMember INT NOT NULL, CHANGE filter filter VARCHAR(50) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX unique_relation ON core_group_members (idst, idstMember)');
        $this->addSql('ALTER TABLE core_group_user_waiting ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst_group idst_group INT NOT NULL, CHANGE idst_user idst_user INT NOT NULL');
        $this->addSql('CREATE INDEX idst_group_idx ON core_group_user_waiting (idst_group)');
        $this->addSql('CREATE INDEX idst_user_idx ON core_group_user_waiting (idst_user)');
        $this->addSql('ALTER TABLE core_hteditor ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE hteditor hteditor VARCHAR(255) NOT NULL, CHANGE hteditorname hteditorname VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX hteditor_idx ON core_hteditor (hteditor)');
        $this->addSql('ALTER TABLE core_lang_language ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE lang_description lang_description VARCHAR(255) NOT NULL, CHANGE lang_browsercode lang_browsercode VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX lang_code_idx ON core_lang_language (lang_code)');
        $this->addSql('ALTER TABLE core_lang_text ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE text_key text_key VARCHAR(255) NOT NULL, CHANGE text_module text_module VARCHAR(50) NOT NULL, CHANGE text_attributes text_attributes LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', CHANGE plugin_id plugin_id INT NOT NULL');
        $this->addSql('ALTER TABLE core_lang_translation ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_text id_text INT NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX id_text_idx ON core_lang_translation (id_text)');
        $this->addSql('CREATE INDEX lang_code_idx ON core_lang_translation (lang_code)');
        $this->addSql('ALTER TABLE core_menu ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE core_menu_under ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idMenu idMenu INT NOT NULL, CHANGE default_name default_name VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE core_message ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE sender sender INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE attach attach VARCHAR(255) NOT NULL, CHANGE priority priority INT NOT NULL');
        $this->addSql('ALTER TABLE core_message_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idMessage idMessage INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE `read` `read` TINYINT(1) NOT NULL, CHANGE deleted deleted TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX id_message_idx ON core_message_user (idMessage)');
        $this->addSql('CREATE INDEX id_user_idx ON core_message_user (idUser)');
        $this->addSql('ALTER TABLE core_newsletter ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_send id_send INT NOT NULL, CHANGE sub sub VARCHAR(255) NOT NULL, CHANGE fromemail fromemail VARCHAR(255) NOT NULL, CHANGE language language VARCHAR(255) NOT NULL, CHANGE tot tot INT NOT NULL');
        $this->addSql('ALTER TABLE core_newsletter_sendto ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_send id_send INT NOT NULL, CHANGE idst idst INT NOT NULL');
        $this->addSql('CREATE INDEX id_send_idx ON core_newsletter_sendto (id_send)');
        $this->addSql('CREATE INDEX idst_idx ON core_newsletter_sendto (idst)');
        $this->addSql('ALTER TABLE core_org_chart ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_dir id_dir INT NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_dir_idx ON core_org_chart (id_dir)');
        $this->addSql('CREATE INDEX lang_code_idx ON core_org_chart (lang_code)');
        $this->addSql('ALTER TABLE core_org_chart_field ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE id_field id_field VARCHAR(11) NOT NULL');
        $this->addSql('CREATE INDEX idst_idx ON core_org_chart_field (idst)');
        $this->addSql('CREATE INDEX id_field_idx ON core_org_chart_field (id_field)');
        $this->addSql('ALTER TABLE core_org_chart_fieldentry ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_common id_common VARCHAR(11) NOT NULL, CHANGE id_common_son id_common_son INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX id_common_idx ON core_org_chart_fieldentry (id_common)');
        $this->addSql('CREATE INDEX id_common_son_idx ON core_org_chart_fieldentry (id_common_son)');
        $this->addSql('CREATE INDEX id_user_idx ON core_org_chart_fieldentry (id_user)');
        $this->addSql('ALTER TABLE core_org_chart_tree ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParent idParent INT NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE iLeft iLeft INT NOT NULL, CHANGE iRight iRight INT NOT NULL, CHANGE code code VARCHAR(255) NOT NULL, CHANGE idst_oc idst_oc INT NOT NULL, CHANGE idst_ocd idst_ocd INT NOT NULL');
        $this->addSql('ALTER TABLE core_password_history ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst_user idst_user INT NOT NULL, CHANGE passw passw VARCHAR(100) NOT NULL, CHANGE changed_by changed_by INT NOT NULL');
        $this->addSql('CREATE INDEX idst_user_idx ON core_password_history (idst_user)');
        $this->addSql('ALTER TABLE core_platform ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE platform platform VARCHAR(255) NOT NULL, CHANGE class_file class_file VARCHAR(255) NOT NULL, CHANGE class_name class_name VARCHAR(255) NOT NULL, CHANGE class_file_menu class_file_menu VARCHAR(255) NOT NULL, CHANGE class_name_menu class_name_menu VARCHAR(255) NOT NULL, CHANGE class_name_menu_managment class_name_menu_managment VARCHAR(255) NOT NULL, CHANGE file_class_config file_class_config VARCHAR(255) NOT NULL, CHANGE class_name_config class_name_config VARCHAR(255) NOT NULL, CHANGE var_default_template var_default_template VARCHAR(255) NOT NULL, CHANGE class_default_admin class_default_admin VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('CREATE INDEX platform_idx ON core_platform (platform)');
        $this->addSql('ALTER TABLE core_plugin ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_privacypolicy ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(255) NOT NULL, CHANGE is_default is_default INT NOT NULL');
        $this->addSql('ALTER TABLE core_privacypolicy_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_policy id_policy INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_policy_idx ON core_privacypolicy_lang (id_policy)');
        $this->addSql('CREATE INDEX lang_code_idx ON core_privacypolicy_lang (lang_code)');
        $this->addSql('ALTER TABLE core_privacypolicy_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_pwd_recover ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst_user idst_user INT NOT NULL, CHANGE random_code random_code VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX idst_user_idx ON core_pwd_recover (idst_user)');
        $this->addSql('ALTER TABLE core_reg_list ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE region_id region_id VARCHAR(100) NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE region_desc region_desc VARCHAR(255) NOT NULL, CHANGE default_region default_region TINYINT(1) NOT NULL, CHANGE browsercode browsercode VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX region_id_idx ON core_reg_list (region_id)');
        $this->addSql('ALTER TABLE core_reg_setting ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE region_id region_id VARCHAR(100) NOT NULL, CHANGE val_name val_name VARCHAR(100) NOT NULL, CHANGE value value VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX region_id_idx ON core_reg_setting (region_id)');
        $this->addSql('CREATE INDEX val_name_idx ON core_reg_setting (val_name)');
        $this->addSql('ALTER TABLE core_requests ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_rest_authentication ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE token token VARCHAR(255) NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE user_level user_level INT NOT NULL');
        $this->addSql('CREATE INDEX token_idx ON core_rest_authentication (token)');
        $this->addSql('ALTER TABLE core_revision ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE parent_id parent_id INT NOT NULL, CHANGE version version INT NOT NULL, CHANGE sub_key sub_key VARCHAR(80) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('CREATE INDEX type_idx ON core_revision (type)');
        $this->addSql('CREATE INDEX parent_id_idx ON core_revision (parent_id)');
        $this->addSql('CREATE INDEX version_idx ON core_revision (version)');
        $this->addSql('CREATE INDEX sub_key_idx ON core_revision (sub_key)');
        $this->addSql('ALTER TABLE core_role ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE roleid roleid VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX idPlugin_idx ON core_role (idPlugin)');
        $this->addSql('CREATE INDEX idst_idx ON core_role (idst)');
        $this->addSql('CREATE INDEX roleid_idx ON core_role (roleid)');
        $this->addSql('ALTER TABLE core_role_members ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT NOT NULL, CHANGE idstMember idstMember INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX idst ON core_role_members (idst, idstMember)');
        $this->addSql('ALTER TABLE core_rules ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE rule_type rule_type VARCHAR(10) NOT NULL, CHANGE rule_active rule_active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_rules_entity ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_rule id_rule INT NOT NULL, CHANGE id_entity id_entity VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX id_rule_idx ON core_rules_entity (id_rule)');
        $this->addSql('CREATE INDEX id_entity_idx ON core_rules_entity (id_entity)');
        $this->addSql('ALTER TABLE core_rules_log ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE log_action log_action VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_setting ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE param_name param_name VARCHAR(255) NOT NULL, CHANGE regroup regroup INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE hide_in_modify hide_in_modify TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX param_name_idx ON core_setting (param_name)');
        $this->addSql('ALTER TABLE core_setting_group ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE path_name path_name VARCHAR(255) NOT NULL, CHANGE idst idst INT NOT NULL');
        $this->addSql('CREATE INDEX path_name_idx ON core_setting_group (path_name)');
        $this->addSql('CREATE INDEX idst_idx ON core_setting_group (idst)');
        $this->addSql('ALTER TABLE core_setting_list ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE path_name path_name VARCHAR(255) NOT NULL, CHANGE label label VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE visible visible TINYINT(1) NOT NULL, CHANGE load_at_startup load_at_startup TINYINT(1) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('CREATE INDEX path_name_idx ON core_setting_list (path_name)');
        $this->addSql('ALTER TABLE core_setting_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE path_name path_name VARCHAR(255) NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX path_name_idx ON core_setting_user (path_name)');
        $this->addSql('CREATE INDEX id_user_idx ON core_setting_user (id_user)');
        $this->addSql('ALTER TABLE core_st ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE core_tag ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE tag_name tag_name VARCHAR(255) NOT NULL, CHANGE id_parent id_parent INT NOT NULL');
        $this->addSql('ALTER TABLE core_tag_relation ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_tag id_tag INT NOT NULL, CHANGE id_resource id_resource INT NOT NULL, CHANGE resource_type resource_type VARCHAR(255) NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE private private TINYINT(1) NOT NULL, CHANGE id_course id_course INT NOT NULL');
        $this->addSql('CREATE INDEX id_tag_idx ON core_tag_relation (id_tag)');
        $this->addSql('CREATE INDEX id_resource_idx ON core_tag_relation (id_resource)');
        $this->addSql('CREATE INDEX resource_type_idx ON core_tag_relation (resource_type)');
        $this->addSql('CREATE INDEX id_user_idx ON core_tag_relation (id_user)');
        $this->addSql('ALTER TABLE core_tag_resource ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_resource id_resource INT NOT NULL, CHANGE resource_type resource_type VARCHAR(255) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_resource_idx ON core_tag_resource (id_resource)');
        $this->addSql('CREATE INDEX resource_type_idx ON core_tag_resource (resource_type)');
        $this->addSql('ALTER TABLE core_task ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(50) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE conn_source conn_source VARCHAR(50) NOT NULL, CHANGE conn_destination conn_destination VARCHAR(50) NOT NULL, CHANGE schedule schedule VARCHAR(50) NOT NULL, CHANGE import_type import_type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE core_transaction ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_user id_user INT NOT NULL, CHANGE location location VARCHAR(10) NOT NULL, CHANGE paid paid TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_transaction_info ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_trans id_trans INT NOT NULL, CHANGE id_course id_course INT NOT NULL, CHANGE id_date id_date INT NOT NULL, CHANGE id_edition id_edition INT NOT NULL, CHANGE code code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE price price VARCHAR(255) NOT NULL, CHANGE activated activated TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX id_trans_idx ON core_transaction_info (id_trans)');
        $this->addSql('CREATE INDEX id_course_idx ON core_transaction_info (id_course)');
        $this->addSql('CREATE INDEX id_date_idx ON core_transaction_info (id_date)');
        $this->addSql('CREATE INDEX id_edition_idx ON core_transaction_info (id_edition)');
        $this->addSql('ALTER TABLE core_user ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE userid userid VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(255) NOT NULL, CHANGE lastname lastname VARCHAR(255) NOT NULL, CHANGE pass pass VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE avatar avatar VARCHAR(255) NOT NULL, CHANGE level level INT NOT NULL, CHANGE force_change force_change TINYINT(1) NOT NULL, CHANGE privacy_policy privacy_policy TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_user_file ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE user_idst user_idst INT NOT NULL, CHANGE type type VARCHAR(20) NOT NULL, CHANGE fname fname VARCHAR(255) NOT NULL, CHANGE real_fname real_fname VARCHAR(255) NOT NULL, CHANGE media_url media_url VARCHAR(255) NOT NULL, CHANGE size size INT NOT NULL');
        $this->addSql('ALTER TABLE core_user_log_attempt ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE userid userid VARCHAR(255) NOT NULL, CHANGE attempt_number attempt_number INT NOT NULL, CHANGE user_ip user_ip VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_user_profileview ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_owner id_owner INT NOT NULL, CHANGE id_viewer id_viewer INT NOT NULL');
        $this->addSql('CREATE INDEX id_owner_idx ON core_user_profileview (id_owner)');
        $this->addSql('CREATE INDEX id_viewer_idx ON core_user_profileview (id_viewer)');
        $this->addSql('ALTER TABLE core_user_temp ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst idst INT AUTO_INCREMENT PRIMARY KEY NOT NULL, CHANGE userid userid VARCHAR(255) NOT NULL, CHANGE firstname firstname VARCHAR(100) NOT NULL, CHANGE lastname lastname VARCHAR(100) NOT NULL, CHANGE pass pass VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE random_code random_code VARCHAR(255) NOT NULL, CHANGE create_by_admin create_by_admin INT NOT NULL, CHANGE confirmed confirmed TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE core_wiki ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE source_platform source_platform VARCHAR(255) NOT NULL, CHANGE public public TINYINT(1) NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE page_count page_count INT NOT NULL, CHANGE revision_count revision_count INT NOT NULL');
        $this->addSql('ALTER TABLE core_wiki_page ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE page_code page_code VARCHAR(60) NOT NULL, CHANGE parent_id parent_id INT NOT NULL, CHANGE page_path page_path VARCHAR(255) NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE wiki_id wiki_id INT NOT NULL');
        $this->addSql('ALTER TABLE core_wiki_page_info ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE page_id page_id INT NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE version version INT NOT NULL, CHANGE wiki_id wiki_id INT NOT NULL');
        $this->addSql('CREATE INDEX page_id_idx ON core_wiki_page_info (page_id)');
        $this->addSql('CREATE INDEX language_idx ON core_wiki_page_info (language)');
        $this->addSql('ALTER TABLE core_wiki_revision ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE wiki_id wiki_id INT NOT NULL, CHANGE page_id page_id INT NOT NULL, CHANGE version version INT NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('CREATE INDEX wiki_id_idx ON core_wiki_revision (wiki_id)');
        $this->addSql('CREATE INDEX page_id_idx ON core_wiki_revision (page_id)');
        $this->addSql('CREATE INDEX version_idx ON core_wiki_revision (version)');
        $this->addSql('CREATE INDEX language_idx ON core_wiki_revision (language)');
        /******** CORE **********/

        /******** DASHBOARD **********/
        $this->addSql('ALTER TABLE dashboard_block_config CHANGE dashboard_id dashboard_id BIGINT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE dashboard_blocks CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE dashboard_layouts CHANGE `default` `default` TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE dashboard_permission ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('CREATE INDEX dashboard_id_idx ON dashboard_block_config (dashboard_id)');
        /******** DASHBOARD **********/
        
        /******** LEARNING **********/
        $this->addSql('ALTER TABLE learning_advice ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE author author INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE important important TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_adviceuser ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idAdvice idAdvice INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE archivied archivied TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX idAdvice_idx ON learning_adviceuser (idAdvice)');
        $this->addSql('CREATE INDEX idUser_idx ON learning_adviceuser (idUser)');
        $this->addSql('ALTER TABLE learning_aggregated_cert_assign ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idUser idUser INT NOT NULL, CHANGE idCertificate idCertificate INT NOT NULL, CHANGE cert_file cert_file VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_user_idx ON learning_aggregated_cert_assign (idUser)');
        $this->addSql('CREATE INDEX id_certificate_idx ON learning_aggregated_cert_assign (idCertificate)');
        $this->addSql('CREATE INDEX id_association_idx ON learning_aggregated_cert_assign (idAssociation)');
        $this->addSql('ALTER TABLE learning_aggregated_cert_course ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idAssociation idAssociation INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE idCourseEdition idCourseEdition INT NOT NULL');
        $this->addSql('ALTER TABLE learning_aggregated_cert_coursepath ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idAssociation idAssociation INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idCoursePath idCoursePath INT NOT NULL');
        $this->addSql('ALTER TABLE learning_aggregated_cert_metadata ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCertificate idCertificate INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_assessment_rule ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE test_id test_id INT NOT NULL, CHANGE category_id category_id INT NOT NULL, CHANGE from_score from_score DOUBLE PRECISION NOT NULL, CHANGE to_score to_score DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE learning_assessment_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_assessment id_assessment INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE type_of type_of VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_assessment_idx ON learning_assessment_user (id_assessment)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_assessment_user (id_user)');
        $this->addSql('ALTER TABLE learning_calendar ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL');
        $this->addSql('ALTER TABLE learning_catalogue ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_catalogue_entry ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCatalogue idCatalogue INT NOT NULL, CHANGE idEntry idEntry INT NOT NULL');
        $this->addSql('CREATE INDEX type_of_entry_idx ON learning_catalogue_entry (type_of_entry)');
        $this->addSql('CREATE INDEX id_catalogue_idx ON learning_catalogue_entry (idCatalogue)');
        $this->addSql('CREATE INDEX id_entry_idx ON learning_catalogue_entry (idEntry)');
        $this->addSql('ALTER TABLE learning_catalogue_member ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idst_member idst_member INT NOT NULL, CHANGE idCatalogue idCatalogue INT NOT NULL');
        $this->addSql('CREATE INDEX idst_member_idx ON learning_catalogue_member (idst_member)');
        $this->addSql('CREATE INDEX id_catalogue_idx ON learning_catalogue_member (idCatalogue)');
        $this->addSql('ALTER TABLE learning_category ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParent idParent INT DEFAULT NULL, CHANGE lev lev INT NOT NULL, CHANGE iLeft iLeft INT NOT NULL, CHANGE iRight iRight INT NOT NULL');
        $this->addSql('ALTER TABLE learning_certificate ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE code code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE base_language base_language VARCHAR(255) NOT NULL, CHANGE bgimage bgimage VARCHAR(255) NOT NULL, CHANGE meta meta TINYINT(1) NOT NULL, CHANGE user_release user_release TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_certificate_assign ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_certificate id_certificate INT NOT NULL, CHANGE id_course id_course INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE cert_file cert_file VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_certificate_idx ON learning_certificate_assign (id_certificate)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_certificate_assign (id_course)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_certificate_assign (id_user)');
        $this->addSql('ALTER TABLE learning_certificate_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_certificate id_certificate INT NOT NULL, CHANGE id_course id_course INT NOT NULL, CHANGE available_for_status available_for_status TINYINT(1) NOT NULL, CHANGE point_required point_required INT NOT NULL, CHANGE minutes_required minutes_required INT NOT NULL');
        $this->addSql('CREATE INDEX id_certificate_idx ON learning_certificate_course (id_certificate)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_certificate_course (id_course)');
        $this->addSql('ALTER TABLE learning_certificate_tags ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE file_name file_name VARCHAR(255) NOT NULL, CHANGE class_name class_name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX file_name_idx ON learning_certificate_tags (file_name)');
        $this->addSql('ALTER TABLE learning_class_location ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE location location VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_classroom ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(255) NOT NULL, CHANGE location_id location_id INT NOT NULL, CHANGE room room VARCHAR(255) NOT NULL, CHANGE street street VARCHAR(255) NOT NULL, CHANGE city city VARCHAR(255) NOT NULL, CHANGE state state VARCHAR(255) NOT NULL, CHANGE zip_code zip_code VARCHAR(255) NOT NULL, CHANGE phone phone VARCHAR(255) NOT NULL, CHANGE fax fax VARCHAR(255) NOT NULL, CHANGE capacity capacity VARCHAR(255) NOT NULL, CHANGE responsable responsable VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_classroom_calendar ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE classroom_id classroom_id INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE owner owner INT NOT NULL');
        $this->addSql('ALTER TABLE learning_comment_ajax ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE resource_type resource_type VARCHAR(50) NOT NULL, CHANGE external_key external_key VARCHAR(200) NOT NULL, CHANGE id_author id_author INT NOT NULL, CHANGE history_tree history_tree VARCHAR(255) NOT NULL, CHANGE id_parent id_parent INT NOT NULL, CHANGE moderated moderated TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_commontrack ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idReference idReference INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idTrack idTrack INT NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('CREATE INDEX id_track_idx ON learning_commontrack (idTrack)');
        $this->addSql('CREATE INDEX object_type_idx ON learning_commontrack (objectType)');
        $this->addSql('ALTER TABLE learning_communication ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL, CHANGE type_of type_of VARCHAR(15) NOT NULL, CHANGE id_resource id_resource INT NOT NULL, CHANGE id_category id_category INT UNSIGNED NOT NULL, CHANGE id_course id_course INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE learning_communication_access ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_comm id_comm INT NOT NULL, CHANGE idst idst INT NOT NULL');
        $this->addSql('ALTER TABLE learning_communication_category ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_parent id_parent INT UNSIGNED NOT NULL, CHANGE level level INT UNSIGNED NOT NULL, CHANGE iLeft iLeft INT UNSIGNED NOT NULL, CHANGE iRight iRight INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE learning_communication_category_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_category id_category INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE translation translation VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_category_idx ON learning_communication_category_lang (id_category)');
        $this->addSql('CREATE INDEX lang_code_idx ON learning_communication_category_lang (lang_code)');
        $this->addSql('ALTER TABLE learning_communication_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_comm id_comm INT DEFAULT NULL, CHANGE lang_code lang_code VARCHAR(255) DEFAULT NULL, CHANGE title title VARCHAR(255) DEFAULT NULL, CHANGE description description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_communication_track ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idReference idReference INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idTrack idTrack INT NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL');
        $this->addSql('CREATE INDEX idReference ON learning_communication_track (idReference)');
        $this->addSql('CREATE INDEX idUser ON learning_communication_track (idUser)');
        $this->addSql('ALTER TABLE learning_competence ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_category id_category INT UNSIGNED NOT NULL, CHANGE score score DOUBLE PRECISION NOT NULL, CHANGE expiration expiration INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE learning_competence_category ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_parent id_parent INT UNSIGNED NOT NULL, CHANGE level level INT UNSIGNED NOT NULL, CHANGE iLeft iLeft INT UNSIGNED NOT NULL, CHANGE iRight iRight INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE learning_competence_category_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_category id_category INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_category_idx ON learning_competence_category_lang (id_category)');
        $this->addSql('CREATE INDEX lang_code_idx ON learning_competence_category_lang (lang_code)');
        $this->addSql('ALTER TABLE learning_competence_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE id_course id_course INT UNSIGNED NOT NULL, CHANGE score score DOUBLE PRECISION NOT NULL, CHANGE retraining retraining TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX id_competence_idx ON learning_competence_course (id_competence)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_competence_course (id_course)');
        $this->addSql('ALTER TABLE learning_competence_lang ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_competence_idx ON learning_competence_lang (id_competence)');
        $this->addSql('CREATE INDEX lang_code_idx ON learning_competence_lang (lang_code)');
        $this->addSql('ALTER TABLE learning_competence_required ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE idst idst INT UNSIGNED NOT NULL, CHANGE type_of type_of VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX idst_idx ON learning_competence_required (idst)');
        $this->addSql('CREATE INDEX id_competence_idx ON learning_competence_required (id_competence)');
        $this->addSql('ALTER TABLE learning_competence_track ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE id_user id_user INT UNSIGNED NOT NULL, CHANGE operation operation VARCHAR(255) NOT NULL, CHANGE id_course id_course INT NOT NULL, CHANGE assigned_by assigned_by INT NOT NULL, CHANGE score_assigned score_assigned DOUBLE PRECISION NOT NULL, CHANGE score_total score_total DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE learning_competence_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_competence id_competence INT UNSIGNED NOT NULL, CHANGE id_user id_user INT UNSIGNED NOT NULL, CHANGE score_got score_got DOUBLE PRECISION NOT NULL');
        $this->addSql('CREATE INDEX id_competence_idx ON learning_competence_user (id_competence)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_competence_user (id_user)');
        $this->addSql('ALTER TABLE learning_course ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCategory idCategory INT NOT NULL, CHANGE code code VARCHAR(50) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE lang_code lang_code VARCHAR(100) NOT NULL, CHANGE status status INT NOT NULL, CHANGE level_show_user level_show_user INT NOT NULL, CHANGE subscribe_method subscribe_method TINYINT(1) NOT NULL, CHANGE linkSponsor linkSponsor VARCHAR(255) NOT NULL, CHANGE imgSponsor imgSponsor VARCHAR(255) NOT NULL, CHANGE img_course img_course VARCHAR(255) NOT NULL, CHANGE img_material img_material VARCHAR(255) NOT NULL, CHANGE img_othermaterial img_othermaterial VARCHAR(255) NOT NULL, CHANGE course_demo course_demo VARCHAR(255) NOT NULL, CHANGE mediumTime mediumTime INT UNSIGNED NOT NULL, CHANGE permCloseLO permCloseLO TINYINT(1) NOT NULL, CHANGE userStatusOp userStatusOp INT NOT NULL, CHANGE show_time show_time TINYINT(1) NOT NULL, CHANGE show_who_online show_who_online TINYINT(1) NOT NULL, CHANGE show_extra_info show_extra_info TINYINT(1) NOT NULL, CHANGE show_rules show_rules TINYINT(1) NOT NULL, CHANGE hour_begin hour_begin VARCHAR(5) NOT NULL, CHANGE hour_end hour_end VARCHAR(5) NOT NULL, CHANGE valid_time valid_time INT NOT NULL, CHANGE max_num_subscribe max_num_subscribe INT NOT NULL, CHANGE min_num_subscribe min_num_subscribe INT NOT NULL, CHANGE max_sms_budget max_sms_budget DOUBLE PRECISION NOT NULL, CHANGE selling selling TINYINT(1) NOT NULL, CHANGE prize prize VARCHAR(255) NOT NULL, CHANGE policy_point policy_point VARCHAR(255) NOT NULL, CHANGE point_to_all point_to_all INT NOT NULL, CHANGE course_edition course_edition TINYINT(1) NOT NULL, CHANGE classrooms classrooms VARCHAR(255) NOT NULL, CHANGE certificates certificates VARCHAR(255) NOT NULL, CHANGE security_code security_code VARCHAR(255) NOT NULL, CHANGE used_space used_space VARCHAR(255) NOT NULL, CHANGE course_vote course_vote DOUBLE PRECISION NOT NULL, CHANGE allow_overbooking allow_overbooking TINYINT(1) NOT NULL, CHANGE can_subscribe can_subscribe TINYINT(1) NOT NULL, CHANGE advance advance VARCHAR(255) NOT NULL, CHANGE autoregistration_code autoregistration_code VARCHAR(255) NOT NULL, CHANGE direct_play direct_play TINYINT(1) NOT NULL, CHANGE use_logo_in_courselist use_logo_in_courselist TINYINT(1) NOT NULL, CHANGE show_result show_result TINYINT(1) NOT NULL, CHANGE credits credits DOUBLE PRECISION NOT NULL, CHANGE auto_unsubscribe auto_unsubscribe TINYINT(1) NOT NULL, CHANGE sendCalendar sendCalendar TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_course_date ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT UNSIGNED NOT NULL, CHANGE code code VARCHAR(255) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE max_par max_par INT NOT NULL, CHANGE price price VARCHAR(255) NOT NULL, CHANGE overbooking overbooking TINYINT(1) NOT NULL, CHANGE test_type test_type TINYINT(1) NOT NULL, CHANGE status status INT UNSIGNED NOT NULL, CHANGE medium_time medium_time INT NOT NULL, CHANGE calendarId calendarId VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_course_date_day CHANGE id_day id_day INT NOT NULL, CHANGE id_date id_date INT NOT NULL, CHANGE classroom classroom INT UNSIGNED NOT NULL, CHANGE deleted deleted TINYINT(1) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE learning_course_date_presence ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_date id_date INT UNSIGNED NOT NULL, CHANGE id_user id_user INT UNSIGNED NOT NULL, CHANGE id_day id_day INT UNSIGNED NOT NULL, CHANGE presence presence TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX day_idx ON learning_course_date_presence (day)');
        $this->addSql('CREATE INDEX id_date_idx ON learning_course_date_presence (id_date)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_course_date_presence (id_user)');
        $this->addSql('ALTER TABLE learning_course_date_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_date id_date INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE subscribed_by subscribed_by INT UNSIGNED NOT NULL, CHANGE overbooking overbooking INT DEFAULT NULL');
        $this->addSql('CREATE INDEX id_date_idx ON learning_course_date_user (id_date)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_course_date_user (id_user)');
        $this->addSql('ALTER TABLE learning_course_editions  ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT NOT NULL, CHANGE code code VARCHAR(50) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE status status INT NOT NULL, CHANGE max_num_subscribe max_num_subscribe INT NOT NULL, CHANGE min_num_subscribe min_num_subscribe INT NOT NULL, CHANGE price price VARCHAR(255) NOT NULL, CHANGE overbooking overbooking TINYINT(1) NOT NULL, CHANGE can_subscribe can_subscribe TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_course_editions_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_edition id_edition INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE subscribed_by subscribed_by INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX id_edition_idx ON learning_course_editions_user (id_edition)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_course_editions_user (id_user)');
        $this->addSql('ALTER TABLE learning_course_file ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_coursepath ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE path_code path_code VARCHAR(255) NOT NULL, CHANGE path_name path_name VARCHAR(255) NOT NULL, CHANGE subscribe_method subscribe_method TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_coursepath_courses ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_path id_path INT NOT NULL, CHANGE id_item id_item INT NOT NULL, CHANGE in_slot in_slot INT NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('CREATE INDEX id_path_idx ON learning_coursepath_courses (id_path)');
        $this->addSql('CREATE INDEX id_item_idx ON learning_coursepath_courses (id_item)');
        $this->addSql('CREATE INDEX in_slot_idx ON learning_coursepath_courses (in_slot)');
        $this->addSql('ALTER TABLE learning_coursepath_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_path id_path INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE waiting waiting TINYINT(1) NOT NULL, CHANGE course_completed course_completed INT NOT NULL, CHANGE subscribed_by subscribed_by INT NOT NULL');
        $this->addSql('CREATE INDEX id_path_idx ON learning_coursepath_user (id_path)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_coursepath_user (idUser)');
        $this->addSql('ALTER TABLE learning_coursereport ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE max_score max_score DOUBLE PRECISION NOT NULL, CHANGE required_score required_score DOUBLE PRECISION NOT NULL, CHANGE weight weight INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE id_source id_source VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_course_id_report_source_of_idx ON learning_coursereport (id_course,id_report,source_of)');
        $this->addSql('CREATE INDEX id_course_id_report_source_of_id_source_idx ON learning_coursereport (id_course,id_report,source_of,id_source)');
        $this->addSql('CREATE INDEX id_course_id_report_idx ON learning_coursereport (id_course,id_report)');
        $this->addSql('ALTER TABLE learning_coursereport_score ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_report id_report INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX id_report_idx ON learning_coursereport_score (id_report)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_coursereport_score (id_user)');
        $this->addSql('ALTER TABLE learning_courseuser ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE edition_id edition_id INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE level level INT NOT NULL, CHANGE status status INT NOT NULL, CHANGE waiting waiting TINYINT(1) NOT NULL, CHANGE subscribed_by subscribed_by INT NOT NULL, CHANGE absent absent TINYINT(1) NOT NULL, CHANGE cancelled_by cancelled_by INT NOT NULL, CHANGE new_forum_post new_forum_post INT NOT NULL, CHANGE requesting_unsubscribe requesting_unsubscribe TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX edition_id_idx ON learning_courseuser (edition_id)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_courseuser (idCourse)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_courseuser (idUser)');
        $this->addSql('ALTER TABLE learning_faq ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCategory idCategory INT NOT NULL, CHANGE question question VARCHAR(255) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE learning_faq_cat ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('ALTER TABLE learning_forum ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE num_thread num_thread INT NOT NULL, CHANGE num_post num_post INT NOT NULL, CHANGE last_post last_post INT NOT NULL, CHANGE locked locked TINYINT(1) NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE emoticons emoticons VARCHAR(255) NOT NULL, CHANGE max_threads max_threads INT DEFAULT NULL, CHANGE threads_are_private threads_are_private TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_forum_access ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idForum idForum INT NOT NULL, CHANGE idMember idMember INT NOT NULL');
        $this->addSql('CREATE INDEX id_forum_idx ON learning_forum_access (idForum)');
        $this->addSql('CREATE INDEX id_member_idx ON learning_forum_access (idMember)');
        $this->addSql('ALTER TABLE learning_forum_notifier ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_notify id_notify INT NOT NULL, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX id_notify_idx ON learning_forum_notifier (id_notify)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_forum_notifier (id_user)');
        $this->addSql('CREATE INDEX notify_is_a_idx ON learning_forum_notifier (notify_is_a)');
        $this->addSql('ALTER TABLE learning_forum_timing ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL');
        $this->addSql('CREATE INDEX id_user_idx ON learning_forum_timing (idUser)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_forum_timing (idCourse)');
        $this->addSql('ALTER TABLE learning_forummessage ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idThread idThread INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE author author INT NOT NULL, CHANGE generator generator TINYINT(1) NOT NULL, CHANGE attach attach VARCHAR(255) NOT NULL, CHANGE locked locked TINYINT(1) NOT NULL, CHANGE modified_by modified_by INT NOT NULL');
        $this->addSql('ALTER TABLE learning_forumthread ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_edition id_edition INT NOT NULL, CHANGE idForum idForum INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE author author INT NOT NULL, CHANGE num_post num_post INT NOT NULL, CHANGE num_view num_view INT NOT NULL, CHANGE last_post last_post INT NOT NULL, CHANGE locked locked TINYINT(1) NOT NULL, CHANGE erased erased TINYINT(1) NOT NULL, CHANGE emoticons emoticons VARCHAR(255) NOT NULL, CHANGE rilevantForum rilevantForum TINYINT(1) NOT NULL, CHANGE privateThread privateThread TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_games ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL, CHANGE type_of type_of VARCHAR(15) NOT NULL, CHANGE id_resource id_resource INT NOT NULL, CHANGE play_chance play_chance VARCHAR(45) NOT NULL');
        $this->addSql('ALTER TABLE learning_games_access ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_game id_game INT NOT NULL, CHANGE idst idst INT NOT NULL');
        $this->addSql('CREATE INDEX id_game_idx ON learning_games_access (id_game)');
        $this->addSql('CREATE INDEX idst_idx ON learning_games_access (idst)');
        $this->addSql('ALTER TABLE learning_games_track ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idReference idReference INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idTrack idTrack INT NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE status status VARCHAR(20) NOT NULL, CHANGE num_attempts num_attempts INT NOT NULL');
        $this->addSql('CREATE INDEX id_track_idx ON learning_games_track (idTrack)');
        $this->addSql('CREATE INDEX object_type_idx ON learning_games_track (objectType)');
        $this->addSql('ALTER TABLE learning_glossary ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(150) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('ALTER TABLE learning_glossaryterm ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idGlossary idGlossary INT NOT NULL, CHANGE term term VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_homerepo ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParent idParent INT NOT NULL, CHANGE path path VARCHAR(255) NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE idResource idResource INT NOT NULL, CHANGE idCategory idCategory INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idAuthor idAuthor INT NOT NULL, CHANGE version version VARCHAR(8) NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE resource resource VARCHAR(255) NOT NULL, CHANGE idOwner idOwner INT NOT NULL');
        $this->addSql('ALTER TABLE learning_htmlfront ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT NOT NULL');
        $this->addSql('CREATE INDEX id_course_idx ON learning_htmlfront (id_course)');
        $this->addSql('ALTER TABLE learning_htmlpage ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(150) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('ALTER TABLE learning_htmlpage_attachment ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE learning_instmsg ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_sender id_sender INT NOT NULL, CHANGE id_receiver id_receiver INT NOT NULL, CHANGE status status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE learning_kb_rel ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE res_id res_id INT NOT NULL, CHANGE parent_id parent_id VARCHAR(45) NOT NULL');
        $this->addSql('CREATE INDEX res_id_idx ON learning_kb_rel (res_id)');
        $this->addSql('CREATE INDEX parent_id_idx ON learning_kb_rel (parent_id)');
        $this->addSql('CREATE INDEX rel_type_idx ON learning_kb_rel (rel_type)');
        $this->addSql('ALTER TABLE learning_kb_res ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE r_name r_name VARCHAR(255) NOT NULL, CHANGE r_item_id r_item_id INT NOT NULL, CHANGE r_type r_type VARCHAR(45) NOT NULL, CHANGE r_env r_env VARCHAR(45) NOT NULL, CHANGE r_lang r_lang VARCHAR(50) NOT NULL, CHANGE force_visible force_visible TINYINT(1) NOT NULL, CHANGE is_mobile is_mobile TINYINT(1) NOT NULL, CHANGE is_categorized is_categorized TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_kb_tag ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE tag_name tag_name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_kb_tree ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE parent_id parent_id INT NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE iLeft iLeft INT NOT NULL, CHANGE iRight iRight INT NOT NULL');
        $this->addSql('ALTER TABLE learning_kb_tree_info ADD id INT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_dir id_dir INT NOT NULL, CHANGE lang_code lang_code VARCHAR(50) NOT NULL, CHANGE node_title node_title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_label ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_common_label id_common_label INT NOT NULL, CHANGE lang_code lang_code VARCHAR(255) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE file_name file_name VARCHAR(255) NOT NULL, CHANGE sequence sequence INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX id_common_label_idx ON learning_label (id_common_label)');
        $this->addSql('CREATE INDEX lang_code_idx ON learning_label (lang_code)');
        $this->addSql('ALTER TABLE learning_label_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_common_label id_common_label INT NOT NULL, CHANGE id_course id_course INT NOT NULL');
        $this->addSql('CREATE INDEX id_common_label_idx ON learning_label_course (id_common_label)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_label_course (id_course)');
        $this->addSql('ALTER TABLE learning_light_repo ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_course id_course INT NOT NULL, CHANGE repo_title repo_title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_light_repo_files ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_repository id_repository INT NOT NULL, CHANGE file_name file_name VARCHAR(255) NOT NULL, CHANGE id_author id_author INT NOT NULL');
        $this->addSql('ALTER TABLE learning_light_repo_user ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_repo id_repo INT NOT NULL, CHANGE id_user id_user INT NOT NULL, CHANGE repo_lock repo_lock TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX id_repo_idx ON learning_light_repo_user (id_repo)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_light_repo_user (id_user)');
        $this->addSql('ALTER TABLE learning_link ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCategory idCategory INT NOT NULL, CHANGE title title VARCHAR(150) NOT NULL, CHANGE link_address link_address VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE learning_link_cat ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(150) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('ALTER TABLE learning_lo_param ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParam idParam INT NOT NULL, CHANGE param_name param_name VARCHAR(20) NOT NULL, CHANGE param_value param_value VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_lo_types ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE className className VARCHAR(20) NOT NULL, CHANGE fileName fileName VARCHAR(50) NOT NULL, CHANGE classNameTrack classNameTrack VARCHAR(255) NOT NULL, CHANGE fileNameTrack fileNameTrack VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX object_type_idx ON learning_lo_types (objectType)');
        $this->addSql('ALTER TABLE learning_materials_lesson ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE author author INT NOT NULL, CHANGE title title VARCHAR(100) NOT NULL, CHANGE path path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_materials_track ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idResource idResource INT NOT NULL, CHANGE idReference idReference INT NOT NULL, CHANGE idUser idUser INT NOT NULL');
        $this->addSql('ALTER TABLE learning_menucourse_main ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_menucourse_under ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE idModule idModule INT NOT NULL, CHANGE idMain idMain INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE my_name my_name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_course_idx ON learning_menucourse_under (idCourse)');
        $this->addSql('CREATE INDEX id_module_idx ON learning_menucourse_under (idModule)');
        $this->addSql('ALTER TABLE learning_menucustom ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_menucustom_main ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCustom idCustom INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_menucustom_under ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCustom idCustom INT NOT NULL, CHANGE idModule idModule INT NOT NULL, CHANGE idMain idMain INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE my_name my_name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX id_custom_idx ON learning_menucustom_under (idCustom)');
        $this->addSql('CREATE INDEX id_module_idx ON learning_menucustom_under (idModule)');
        $this->addSql('ALTER TABLE learning_middlearea ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE obj_index obj_index VARCHAR(255) NOT NULL, CHANGE disabled disabled TINYINT(1) NOT NULL, CHANGE is_home is_home TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX obj_index_idx ON learning_middlearea (obj_index)');
        $this->addSql('ALTER TABLE learning_module ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE module_name module_name VARCHAR(255) NOT NULL, CHANGE default_op default_op VARCHAR(255) NOT NULL, CHANGE default_name default_name VARCHAR(255) NOT NULL, CHANGE token_associated token_associated VARCHAR(100) NOT NULL, CHANGE file_name file_name VARCHAR(255) NOT NULL, CHANGE class_name class_name VARCHAR(255) NOT NULL, CHANGE module_info module_info VARCHAR(50) NOT NULL, CHANGE mvc_path mvc_path VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_news_internal ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(100) NOT NULL, CHANGE language language VARCHAR(100) NOT NULL, CHANGE important important TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_notes ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE owner owner INT NOT NULL, CHANGE title title VARCHAR(150) NOT NULL');
        $this->addSql('ALTER TABLE learning_organization ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParent idParent INT NOT NULL, CHANGE path path VARCHAR(255) NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE idResource idResource INT NOT NULL, CHANGE idCategory idCategory INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idAuthor idAuthor INT NOT NULL, CHANGE version version VARCHAR(8) NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE resource resource VARCHAR(255) NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE prerequisites prerequisites VARCHAR(255) NOT NULL, CHANGE isTerminator isTerminator TINYINT(1) NOT NULL, CHANGE idParam idParam INT NOT NULL, CHANGE width width VARCHAR(4) NOT NULL, CHANGE height height VARCHAR(4) NOT NULL, CHANGE publish_for publish_for INT NOT NULL, CHANGE ignoreScore ignoreScore TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX objectType_idRTesourse_idx ON learning_organization (objectType,idResource)');
        $this->addSql('ALTER TABLE learning_organization_access ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE value value INT NOT NULL, CHANGE idOrgAccess idOrgAccess INT NOT NULL');
        $this->addSql('CREATE INDEX value_idx ON learning_organization_access (value)');
        $this->addSql('CREATE INDEX kind_idx ON learning_organization_access (kind)');
        $this->addSql('ALTER TABLE learning_poll ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE author author INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_pollquest ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_poll id_poll INT NOT NULL, CHANGE id_category id_category INT NOT NULL, CHANGE type_quest type_quest VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE learning_pollquest_extra ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_quest id_quest INT NOT NULL, CHANGE id_answer id_answer INT NOT NULL');
        $this->addSql('CREATE INDEX id_quest_idx ON learning_pollquest_extra (id_quest)');
        $this->addSql('CREATE INDEX id_answer_idx ON learning_pollquest_extra (id_answer)');
        $this->addSql('ALTER TABLE learning_pollquestanswer ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_quest id_quest INT NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE learning_polltrack ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_user id_user INT NOT NULL, CHANGE id_reference id_reference INT NOT NULL, CHANGE id_poll id_poll INT NOT NULL');
        $this->addSql('ALTER TABLE learning_polltrack_answer ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_track id_track INT NOT NULL, CHANGE id_quest id_quest INT NOT NULL, CHANGE id_answer id_answer INT NOT NULL');
        $this->addSql('CREATE INDEX id_track_idx ON learning_polltrack_answer (id_track)');
        $this->addSql('CREATE INDEX id_quest_idx ON learning_polltrack_answer (id_quest)');
        $this->addSql('CREATE INDEX id_answer_idx ON learning_polltrack_answer (id_answer)');
        $this->addSql('ALTER TABLE learning_prj ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE ptitle ptitle VARCHAR(255) NOT NULL, CHANGE pgroup pgroup INT NOT NULL, CHANGE pprog pprog TINYINT(1) NOT NULL, CHANGE psfiles psfiles TINYINT(1) NOT NULL, CHANGE pstasks pstasks TINYINT(1) NOT NULL, CHANGE psnews psnews TINYINT(1) NOT NULL, CHANGE pstodo pstodo TINYINT(1) NOT NULL, CHANGE psmsg psmsg TINYINT(1) NOT NULL, CHANGE cid cid INT NOT NULL');
        $this->addSql('ALTER TABLE learning_prj_files ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE pid pid INT NOT NULL, CHANGE fname fname VARCHAR(255) NOT NULL, CHANGE ftitle ftitle VARCHAR(255) NOT NULL, CHANGE fver fver VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_prj_news ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE pid pid INT NOT NULL, CHANGE ntitle ntitle VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_prj_tasks ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE pid pid INT NOT NULL, CHANGE tprog tprog TINYINT(1) NOT NULL, CHANGE tname tname VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_prj_todo ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE pid pid INT NOT NULL, CHANGE ttitle ttitle VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_prj_users ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE pid pid INT NOT NULL, CHANGE userid userid INT NOT NULL, CHANGE flag flag TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_quest_category ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE name name VARCHAR(100) NOT NULL, CHANGE author author INT NOT NULL');
        $this->addSql('CREATE INDEX type_quest_idx ON learning_quest_type (type_quest)');
        $this->addSql('ALTER TABLE learning_quest_type ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE type_quest type_quest VARCHAR(255) NOT NULL, CHANGE type_file type_file VARCHAR(255) NOT NULL, CHANGE type_class type_class VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('ALTER TABLE learning_quest_type_poll ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE type_quest type_quest VARCHAR(255) NOT NULL, CHANGE type_file type_file VARCHAR(255) NOT NULL, CHANGE type_class type_class VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL');
        $this->addSql('CREATE INDEX type_quest_idx ON learning_quest_type_poll (type_quest)');
        $this->addSql('ALTER TABLE learning_repo ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idParent idParent INT NOT NULL, CHANGE path path VARCHAR(255) NOT NULL, CHANGE lev lev INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE objectType objectType VARCHAR(20) NOT NULL, CHANGE idResource idResource INT NOT NULL, CHANGE idCategory idCategory INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idAuthor idAuthor VARCHAR(11) NOT NULL, CHANGE version version VARCHAR(8) NOT NULL, CHANGE language language VARCHAR(50) NOT NULL, CHANGE resource resource VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_report ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE report_name report_name VARCHAR(255) NOT NULL, CHANGE class_name class_name VARCHAR(255) NOT NULL, CHANGE file_name file_name VARCHAR(255) NOT NULL, CHANGE enabled enabled TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_report_filter ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_report id_report INT UNSIGNED NOT NULL, CHANGE author author INT UNSIGNED NOT NULL, CHANGE filter_name filter_name VARCHAR(255) NOT NULL, CHANGE filter_data filter_data LONGTEXT NOT NULL, CHANGE is_public is_public TINYINT(1) NOT NULL, CHANGE views views INT NOT NULL');
        $this->addSql('ALTER TABLE learning_report_schedule ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_report_filter id_report_filter INT UNSIGNED NOT NULL, CHANGE id_creator id_creator INT UNSIGNED NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE period period VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_report_schedule_recipient ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_report_schedule id_report_schedule INT UNSIGNED NOT NULL, CHANGE id_user id_user INT UNSIGNED NOT NULL');
        $this->addSql('CREATE INDEX id_report_schedule_idx ON learning_report_schedule_recipient (id_report_schedule)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_report_schedule_recipient (id_user)');
        $this->addSql('ALTER TABLE learning_reservation_category ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE maxSubscription maxSubscription INT NOT NULL');
        $this->addSql('ALTER TABLE learning_reservation_events ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE idLaboratory idLaboratory INT NOT NULL, CHANGE idCategory idCategory INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE maxUser maxUser INT NOT NULL');
        $this->addSql('ALTER TABLE learning_reservation_perm ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE event_id event_id INT NOT NULL, CHANGE user_idst user_idst INT NOT NULL, CHANGE perm perm VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX event_id_idx ON learning_reservation_perm (event_id)');
        $this->addSql('CREATE INDEX user_idst_idx ON learning_reservation_perm (user_idst)');
        $this->addSql('CREATE INDEX perm_idx ON learning_reservation_perm (perm)');
        $this->addSql('ALTER TABLE learning_reservation_subscribed ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idstUser idstUser INT NOT NULL, CHANGE idEvent idEvent INT NOT NULL');
        $this->addSql('CREATE INDEX idst_user_idx ON learning_reservation_subscribed (idstUser)');
        $this->addSql('CREATE INDEX id_event_idx ON learning_reservation_subscribed (idEvent)');
        $this->addSql('ALTER TABLE learning_scorm_items ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idscorm_organization idscorm_organization INT NOT NULL, CHANGE title title VARCHAR(100) NOT NULL, CHANGE nChild nChild INT NOT NULL, CHANGE nDescendant nDescendant INT NOT NULL, CHANGE adlcp_completionthreshold adlcp_completionthreshold VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_items_track ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idscorm_organization idscorm_organization INT NOT NULL, CHANGE idReference idReference INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE nChild nChild INT NOT NULL, CHANGE nChildCompleted nChildCompleted INT NOT NULL, CHANGE nDescendant nDescendant INT NOT NULL, CHANGE nDescendantCompleted nDescendantCompleted INT NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_organizations ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE org_identifier org_identifier VARCHAR(255) NOT NULL, CHANGE idscorm_package idscorm_package INT NOT NULL, CHANGE nChild nChild INT NOT NULL, CHANGE nDescendant nDescendant INT NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_package ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idpackage idpackage VARCHAR(255) NOT NULL, CHANGE idProg idProg INT NOT NULL, CHANGE path path VARCHAR(255) NOT NULL, CHANGE defaultOrg defaultOrg VARCHAR(255) NOT NULL, CHANGE idUser idUser INT NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_resources ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idsco idsco VARCHAR(255) NOT NULL, CHANGE idscorm_package idscorm_package INT NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_tracking ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idUser idUser INT NOT NULL, CHANGE idReference idReference INT NOT NULL, CHANGE idscorm_item idscorm_item INT NOT NULL');
        $this->addSql('ALTER TABLE learning_scorm_tracking_history ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idscorm_tracking idscorm_tracking INT NOT NULL, CHANGE lesson_status lesson_status VARCHAR(24) NOT NULL');
        $this->addSql('CREATE INDEX idscorm_tracking_idx ON learning_scorm_tracking_history (idscorm_tracking)');
        $this->addSql('CREATE INDEX date_action_idx ON learning_scorm_tracking_history (date_action)');
        $this->addSql('ALTER TABLE learning_statuschangelog ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE status_user status_user TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX when_do_idx ON learning_statuschangelog (when_do)');
        $this->addSql('CREATE INDEX id_user_idx ON learning_statuschangelog (idUser)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_statuschangelog (idCourse)');
        $this->addSql('ALTER TABLE learning_sysforum ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE key1 key1 VARCHAR(255) NOT NULL, CHANGE key2 key2 INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE author author INT NOT NULL, CHANGE attach attach VARCHAR(255) NOT NULL, CHANGE locked locked TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_teacher_profile ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_user id_user INT NOT NULL');
        $this->addSql('CREATE INDEX id_user_idx ON learning_teacher_profile (id_user)');
        $this->addSql('ALTER TABLE learning_testquest ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idTest idTest INT NOT NULL, CHANGE idCategory idCategory INT NOT NULL, CHANGE type_quest type_quest VARCHAR(255) NOT NULL, CHANGE time_assigned time_assigned INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE page page INT NOT NULL, CHANGE shuffle shuffle TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_testquest_extra ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idQuest idQuest INT NOT NULL, CHANGE idAnswer idAnswer INT NOT NULL');
        $this->addSql('CREATE INDEX id_quest_idx ON learning_testquest_extra (idQuest)');
        $this->addSql('CREATE INDEX id_answer_idx ON learning_testquest_extra (idAnswer)');
        $this->addSql('ALTER TABLE learning_testquestanswer ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idQuest idQuest INT NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE is_correct is_correct INT NOT NULL, CHANGE score_correct score_correct DOUBLE PRECISION NOT NULL, CHANGE score_incorrect score_incorrect DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE learning_testquestanswer_associate ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idQuest idQuest INT NOT NULL');
        $this->addSql('ALTER TABLE learning_testtrack ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idUser idUser INT NOT NULL, CHANGE idReference idReference INT NOT NULL, CHANGE idTest idTest INT NOT NULL, CHANGE last_page_seen last_page_seen INT NOT NULL, CHANGE last_page_saved last_page_saved INT NOT NULL, CHANGE number_of_save number_of_save INT NOT NULL, CHANGE number_of_attempt number_of_attempt INT NOT NULL, CHANGE bonus_score bonus_score DOUBLE PRECISION NOT NULL, CHANGE attempts_for_suspension attempts_for_suspension INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE learning_testtrack_answer ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idTrack idTrack INT NOT NULL, CHANGE idQuest idQuest INT NOT NULL, CHANGE idAnswer idAnswer INT NOT NULL, CHANGE score_assigned score_assigned DOUBLE PRECISION NOT NULL, CHANGE manual_assigned manual_assigned TINYINT(1) NOT NULL, CHANGE user_answer user_answer TINYINT(1) DEFAULT NULL');
        $this->addSql('CREATE INDEX number_time_idx ON learning_testtrack_answer (number_time)');
        $this->addSql('CREATE INDEX id_track_idx ON learning_testtrack_answer (idTrack)');
        $this->addSql('CREATE INDEX id_quest_idx ON learning_testtrack_answer (idQuest)');
        $this->addSql('CREATE INDEX id_answer_idx ON learning_testtrack_answer (idanswer)');
        $this->addSql('ALTER TABLE learning_testtrack_page ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE page page INT NOT NULL, CHANGE idTrack idTrack INT NOT NULL, CHANGE accumulated accumulated INT NOT NULL');
        $this->addSql('CREATE INDEX page_idx ON learning_testtrack_page (page)');
        $this->addSql('CREATE INDEX id_track_idx ON learning_testtrack_page (idTrack)');
        $this->addSql('ALTER TABLE learning_testtrack_quest ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idTrack idTrack INT NOT NULL, CHANGE idQuest idQuest INT NOT NULL, CHANGE page page INT NOT NULL');
        $this->addSql('CREATE INDEX id_track_idx ON learning_testtrack_quest (idTrack)');
        $this->addSql('CREATE INDEX id_quest_idx ON learning_testtrack_quest (idQuest)');
        $this->addSql('ALTER TABLE learning_testtrack_times ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE number_time number_time TINYINT(1) NOT NULL, CHANGE idTrack idTrack INT NOT NULL, CHANGE idReference idReference INT NOT NULL, CHANGE idTest idTest INT NOT NULL, CHANGE score score DOUBLE PRECISION NOT NULL, CHANGE score_status score_status VARCHAR(50) NOT NULL');
        $this->addSql('CREATE INDEX number_time_idx ON learning_testtrack_times (number_time)');
        $this->addSql('CREATE INDEX id_track_idx ON learning_testtrack_times (idTrack)');
        $this->addSql('CREATE INDEX id_test_idx ON learning_testtrack_times (idTest)');
        $this->addSql('ALTER TABLE learning_time_period ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(255) NOT NULL, CHANGE label label VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_trackingeneral ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idEnter idEnter INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE idCourse idCourse INT NOT NULL, CHANGE session_id session_id VARCHAR(255) NOT NULL, CHANGE `function` `function` VARCHAR(250) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE ip ip VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE learning_tracksession ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE idCourse idCourse INT NOT NULL, CHANGE idUser idUser INT NOT NULL, CHANGE session_id session_id VARCHAR(255) NOT NULL, CHANGE numOp numOp INT NOT NULL, CHANGE lastFunction lastFunction VARCHAR(50) NOT NULL, CHANGE lastOp lastOp VARCHAR(5) NOT NULL, CHANGE ip_address ip_address VARCHAR(40) NOT NULL, CHANGE active active TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_transaction ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_user id_user INT NOT NULL, CHANGE price price INT NOT NULL, CHANGE payment_status payment_status TINYINT(1) NOT NULL, CHANGE course_status course_status TINYINT(1) NOT NULL, CHANGE method method VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE learning_transaction_info ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE id_transaction id_transaction INT NOT NULL, CHANGE id_course id_course INT NOT NULL, CHANGE id_date id_date INT NOT NULL');
        $this->addSql('CREATE INDEX id_transaction_idx ON learning_transaction_info (id_transaction)');
        $this->addSql('CREATE INDEX id_course_idx ON learning_transaction_info (id_course)');
        $this->addSql('CREATE INDEX id_date_idx ON learning_transaction_info (id_date)');
        $this->addSql('ALTER TABLE learning_webpages ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE title title VARCHAR(200) NOT NULL, CHANGE language language VARCHAR(255) NOT NULL, CHANGE sequence sequence INT NOT NULL, CHANGE publish publish TINYINT(1) NOT NULL, CHANGE in_home in_home TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE learning_wiki_course ADD id BIGINT AUTO_INCREMENT PRIMARY KEY NOT NULL, ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE course_id course_id INT NOT NULL, CHANGE wiki_id wiki_id INT NOT NULL, CHANGE is_owner is_owner TINYINT(1) NOT NULL');
        $this->addSql('CREATE INDEX course_id_idx ON learning_wiki_course (course_id)');
        $this->addSql('CREATE INDEX wiki_id_idx ON learning_wiki_course (wiki_id)');
          /******** LEARNING **********/



        $this->addSql('UPDATE `core_reg_setting` SET `value` = "-" WHERE `region_id` = "england" AND `val_name` = "date_sep"');


        $this->addSql("INSERT INTO `core_menu` ( `name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform`) VALUES('_MAIL_CONFIG', '', '1', TRUE, TRUE, 52, NULL, 'framework')");
        $this->addSql("INSERT INTO `core_menu_under` (`idUnder`,`idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` ) VALUES((select max(idMenu) FROM core_menu where name= '_MAIL_CONFIG'), (select max(idMenu) FROM core_menu where name= '_MAIL_CONFIG') ,'mailconfig','_MAIL_CONFIG',NULL,'view','framework',1,NULL,NULL,'adm/mailconfig/show')");


        $this->addSql("INSERT INTO `core_menu` (`name`, `image`, `sequence`, `is_active`, `collapse`, `idParent`, `idPlugin`, `of_platform` ) VALUES('_DOMAIN_CONFIG', '', '1', TRUE, TRUE, 52, NULL, 'framework')");
        $this->addSql("INSERT INTO `core_menu_under` (`idUnder`,`idMenu`, `module_name`, `default_name`, `default_op`, `associated_token`, `of_platform`, `sequence`, `class_file`, `class_name`, `mvc_path` ) VALUES((select max(idMenu) FROM core_menu where name= '_DOMAIN_CONFIG'),(select max(idMenu) FROM core_menu where name= '_DOMAIN_CONFIG'), 'domainconfig','_DOMAIN_CONFIG',NULL,'view','framework',1,NULL,NULL,'adm/domainconfig/show')");

        /** EX FOREIGN KEYS NOW INDEXES **/
    

 
        /** FOREIGN KEYS **/    
        $this->addSql($this->convertCollation());
     
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');


        \Events::trigger('platform.upgrade', [_upgradeclass_ => formatUpgradeClass(__CLASS__)]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }

    private function convertCollation() {

        return '
        DROP PROCEDURE IF EXISTS `convertcollation`;
        CREATE PROCEDURE convertcollation()
        BEGIN
          DECLARE done INT DEFAULT FALSE;
          DECLARE tblx CHAR(255);
          DECLARE cur1 CURSOR FOR 
          SELECT CONCAT("ALTER TABLE `", tbl.TABLE_SCHEMA, "`.`", tbl.TABLE_NAME, "` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" ) as tblname
            FROM
                information_schema.TABLES tbl 
            WHERE
                tbl.TABLE_SCHEMA = DATABASE() 
                AND tbl.TABLE_NAME != "core_migration_versions" ;
        
          DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        
          OPEN cur1;
        
          read_loop: LOOP
            FETCH cur1 INTO tblx;
        
            IF done THEN
              LEAVE read_loop;
            END IF;
        SET @stmt = tblx;
            PREPARE convertStatement FROM @stmt;
            EXECUTE convertStatement;
            DEALLOCATE PREPARE convertStatement;
        
          END LOOP;
        
          CLOSE cur1;
        
        END;
        
        CALL convertcollation();
        DROP PROCEDURE IF EXISTS `convertcollation`';
    }

    private function dropIndexIfExists($index, $table) {

            return 'CALL drop_index_if_exists ( "'.$index.'", "'. $table .'")';
    }
}
