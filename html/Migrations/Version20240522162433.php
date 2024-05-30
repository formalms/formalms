<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240522162433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(HelperTool::createColumnIfNotExistsQueryBuilder('show_quest_score', 'learning_test', 'TINYINT(1) NOT NULL'));
     
        $this->addRoleForGodAdminIfNotExists('/lms/course/public/course/view');
        $this->addRoleForGodAdminIfNotExists('/lms/course/public/coursecatalogue/view');
        $this->addRoleForGodAdminIfNotExists('/lms/course/public/public_forum/view');
        $this->addRoleForGodAdminIfNotExists('/lms/course/public/helpdesk/view');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE learning_test DROP show_quest_score');
    }

    private function addRoleForGodAdminIfNotExists($role) {

        require_once _base_ . '/lib/lib.aclmanager.php';

        $aclManager = new \FormaACLManager();
        // this up() migration is auto-generated, please modify it to your needs
        $roleQuery = sql_query("select * from core_role where roleid = '".$role."' limit 1");
        $groupAdminSt =  $aclManager->getGroupST(ADMIN_GROUP_GODADMIN);

    
        if($roleQuery && sql_num_rows($roleQuery)) {
            $roleObject = sql_fetch_object($roleQuery);
            $roleIdst = $roleObject->idst;
        } else {
            $roleObject = sql_fetch_object(sql_query("select max(idst) + 1 as maxidst from `core_role`"));
            $roleIdst = $roleObject->maxidst;
            $this->addSql("INSERT INTO `core_role` (`idst`, `roleid`) VALUES (".$roleIdst .",  '".$role."' )");
        }

        $this->addSql("INSERT IGNORE INTO `core_role_members` (`idst`, `idstMember`) VALUES (" . $roleIdst . ", ".$groupAdminSt." )");

      
    }
}
