<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000014 extends AbstractMigration
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

        /*Some installtions misses this table */
        $this->addSql("CREATE TABLE IF NOT EXISTS learning_communication_lang (
            id_comm int,
            lang_code varchar(255),
            title varchar(255),
            description text not null
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        /** FOREIGN KEYS **/
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_lang_translation_ibfk_1', 'core_lang_translation'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_lang_translation_ibfk_2', 'core_lang_translation'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_role_ibfk_1', 'core_role'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('core_role_members_ibfk_1', 'core_role_members'));
        $this->addSql(HelperTool::dropForeignKeyIfExistsQueryBuilder('config_layout_fk', 'dashboard_block_config'));
        /** FOREIGN KEYS **/       

        $this->addSql($this->convertCollation());
     
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
