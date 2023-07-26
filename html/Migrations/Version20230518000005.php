<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230518000005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('SET FOREIGN_KEY_CHECKS=0');
        $this->addSql("SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");

        $this->addSql('DROP TABLE IF EXISTS `core_db_upgrades`');

        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "FormaSettingNotifier" WHERE `idConsumer` = 5');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "FormaOrgchartNotifier" WHERE `idConsumer` = 3');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "FormaCourseNotifier" WHERE `idConsumer` = 2');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "FormaUserNotifier" WHERE `idConsumer` = 1');

        $this->addSql('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE IF NOT EXISTS `core_db_upgrades` (
            `script_id` int(11) NOT NULL AUTO_INCREMENT,
            `script_name` varchar(255) NOT NULL,
            `script_description` text,
            `script_version` varchar(255) DEFAULT NULL,
            `core_version` varchar(255) DEFAULT NULL,
            `creation_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `execution_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`script_id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8");

        $this->addSql("INSERT INTO `core_db_upgrades` (`script_id`, `script_name`, `script_description`, `script_version`, `core_version`, `creation_date`, `execution_date`) VALUES(1, 'add_log_db_upgrades.sql', 'Creazione tabella di log per script update db', '1.0', '2.0', '2016-10-06 08:47:53', '2016-10-06 08:47:53')");

        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "DoceboSettingNotifier" WHERE `idConsumer` = 5');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "DoceboOrgchartNotifier" WHERE `idConsumer` = 3');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "DoceboCourseNotifier" WHERE `idConsumer` = 2');
        $this->addSql('UPDATE `core_event_consumer` SET `consumer_class`= "DoceboUserNotifier" WHERE `idConsumer` = 1');

    }
}