<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221012000024 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

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
