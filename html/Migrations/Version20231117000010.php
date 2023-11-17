<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231117000010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE `learning_organization` SET `publish_from` = NULL WHERE `publish_from` = "0000-00-00 00:00:00"');
        $this->addSql('UPDATE `learning_organization` SET `publish_to` = NULL WHERE `publish_to` = "0000-00-00 00:00:00"');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
