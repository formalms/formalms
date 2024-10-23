<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240629074312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $connection = $this->connection;

        // Step 1: Controlla se esiste il record
        $countDashBlockNew = $connection->fetchOne('SELECT COUNT(*) FROM `dashboard_blocks` WHERE `block_class` = ?', ['DashboardBlockNewsLms']);

        // Step 2: Cancella il campo se esiste
        if ($countDashBlockNew > 0) {
            $connection->executeStatement('DELETE FROM `dashboard_blocks` WHERE `block_class` = ?', ['DashboardBlockNewsLms']);
            $countDashBlockNewConf = $connection->fetchOne('SELECT COUNT(*) FROM `dashboard_block_config` WHERE `block_class` = ?', ['DashboardBlockNewsLms']);
            if ($countLabelUnder > 0) {
                $connection->executeStatement('DELETE FROM `dashboard_block_config` WHERE `block_class` = ?', ['DashboardBlockNewsLms']);
            }
        }

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
