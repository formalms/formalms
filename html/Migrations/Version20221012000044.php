<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
