<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
        $this->addSql('CREATE UNIQUE INDEX unique_relation_idx ON core_group_members (idst, idstMember)');
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
        $this->addSql('CREATE UNIQUE INDEX idst_idx ON core_role_members (idst, idstMember)');
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
     
        $this->addSql('SET FOREIGN_KEY_CHECKS=1');


   
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
