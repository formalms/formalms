<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231023000009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change connectors references';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.formaadmin.php', `type` = 'forma-admin', `class` = 'FormaConnectorFormaAdmin' WHERE `file` = 'connector.doceboadmin.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.formacourses.php', `type` = 'forma-courses', `class` = 'FormaConnectorFormaCourses' WHERE `file` = 'connector.docebocourses.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.formacourseuser.php', `type` = 'forma-courseuser', `class` = 'FormaConnectorFormaCourseUser' WHERE `file` = 'connector.docebocourseuser.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.formaorgchart.php', `type` = 'forma-orgchart', `class` = 'FormaConnectorFormaOrgChart' WHERE `file` = 'connector.doceboorgchart.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.formausers.php', `type` = 'forma-users', `class` = 'FormaConnectorFormaUsers' WHERE `file` = 'connector.docebousers.php' ");
    }

    public function down(Schema $schema): void
    {

        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.doceboadmin.php', `type` = 'forma-admin', `class` = 'FormaConnectorFormaAdmin' WHERE `file` = 'connector.formaadmin.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.docebocourses.php', `type` = 'forma-courses', `class` = 'FormaConnectorFormaCourses' WHERE `file` = 'connector.formacourses.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.docebocourseuser.php', `type` = 'forma-courseuser', `class` = 'FormaConnectorFormaCourseUser' WHERE `file` = 'connector.formacourseuser.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.doceboorgchart.php', `type` = 'forma-orgchart', `class` = 'FormaConnectorFormaOrgChart' WHERE `file` = 'connector.formaorgchart.php' ");
        $this->addSql("UPDATE `core_connector` SET `file` = 'connector.docebousers.php', `type` = 'forma-users', `class` = 'FormaConnectorFormaUsers' WHERE `file` = 'connector.formausers.php' ");


    }
}
