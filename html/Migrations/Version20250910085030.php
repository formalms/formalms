<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910085030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'remove orphan classroom course user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        \Events::trigger('platform.upgrade', [_upgradeclass_ => formatUpgradeClass(__CLASS__, "post")]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
