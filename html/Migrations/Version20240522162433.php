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
