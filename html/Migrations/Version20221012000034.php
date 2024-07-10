<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
