<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240228000015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust roles for privacy menu';
    }

    public function up(Schema $schema): void
    {


        $this->addRoleForGodAdminifNotExists('/framework/admin/privacypolicy/view');
        $this->addRoleForGodAdminifNotExists('/framework/admin/privacypolicy/mod');
        $this->addRoleForGodAdminifNotExists('/framework/admin/privacypolicy/del');
 

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    
    }

    private function addRoleForGodAdminifNotExists($role) {

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
