<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000074 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
        
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('calendarId', 'learning_course_date', 'VARCHAR(255) NOT NULL'));
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
