<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240228000014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adjust roles for privacy menu';
    }

    public function up(Schema $schema): void
    {


        $this->addRoleForGodAdminIfNotExists('/framework/admin/privacypolicy/view');
        $this->addRoleForGodAdminIfNotExists('/framework/admin/privacypolicy/mod');
        $this->addRoleForGodAdminIfNotExists('/framework/admin/privacypolicy/del');
 

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    
    }

    private function addRoleForGodAdminIfNotExists($role)
    {
        require_once _base_ . '/lib/lib.aclmanager.php';
        $connection = $this->connection;

        // this up() migration is auto-generated, please modify it to your needs
        $roleData = $connection->fetchAssociative("select * from core_role where roleid = '" . $role . "' limit 1");
        $groupAdminSt = $connection->fetchAssociative('SELECT idst FROM core_group WHERE groupid = "' . ADMIN_GROUP_GODADMIN . '"');

        if (empty($roleData)) {
            $roleData = $connection->fetchAssociative("select max(idst) + 1 as idst from `core_role`");
            $this->addSql("INSERT INTO `core_role` (`idst`, `roleid`) VALUES (" . $roleData['idst'] . ",  '" . $role . "' )");
        }

        $this->addSql("INSERT IGNORE INTO `core_role_members` (`idst`, `idstMember`) VALUES (" . $roleData['idst'] . ", " . $groupAdminSt['idst'] . " )");
    }
}
