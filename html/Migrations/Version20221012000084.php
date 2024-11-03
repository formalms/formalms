<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000084 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
        $this->addSql('ALTER TABLE learning_test ADD created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ADD updated_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL ON UPDATE CURRENT_TIMESTAMP, CHANGE author author INT NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE point_type point_type TINYINT(1) NOT NULL, CHANGE point_required point_required DOUBLE PRECISION NOT NULL, CHANGE display_type display_type TINYINT(1) NOT NULL, CHANGE order_type order_type TINYINT(1) NOT NULL, CHANGE shuffle_answer shuffle_answer TINYINT(1) NOT NULL, CHANGE question_random_number question_random_number INT NOT NULL, CHANGE save_keep save_keep TINYINT(1) NOT NULL, CHANGE show_only_status show_only_status TINYINT(1) NOT NULL, CHANGE show_score_cat show_score_cat TINYINT(1) NOT NULL, CHANGE show_doanswer show_doanswer TINYINT(1) NOT NULL, CHANGE show_solution show_solution TINYINT(1) NOT NULL, CHANGE time_dependent time_dependent TINYINT(1) NOT NULL, CHANGE time_assigned time_assigned INT NOT NULL, CHANGE penality_test penality_test TINYINT(1) NOT NULL, CHANGE penality_time_test penality_time_test DOUBLE PRECISION NOT NULL, CHANGE penality_quest penality_quest TINYINT(1) NOT NULL, CHANGE penality_time_quest penality_time_quest DOUBLE PRECISION NOT NULL, CHANGE max_attempt max_attempt INT NOT NULL, CHANGE hide_info hide_info TINYINT(1) NOT NULL, CHANGE use_suspension use_suspension TINYINT(1) NOT NULL, CHANGE suspension_num_attempts suspension_num_attempts INT UNSIGNED NOT NULL, CHANGE suspension_num_hours suspension_num_hours INT UNSIGNED NOT NULL, CHANGE suspension_prerequisites suspension_prerequisites TINYINT(1) NOT NULL, CHANGE mandatory_answer mandatory_answer TINYINT(1) NOT NULL, CHANGE score_max score_max INT NOT NULL, CHANGE retain_answers_history retain_answers_history TINYINT(1) NOT NULL');
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
