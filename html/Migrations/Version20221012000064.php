<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000064 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
