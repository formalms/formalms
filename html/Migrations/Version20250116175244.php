<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116175244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_lo_main ON learning_organization (idCourse, idResource, visible, dateInsert)');
        $this->addSql('CREATE INDEX idx_lo_join ON learning_organization (idOrg)');
        $this->addSql('CREATE INDEX idx_lo_composite ON learning_organization (idCourse, idResource, visible, dateInsert, idOrg)');
        $this->addSql('ALTER TABLE learning_organization_access CHANGE kind kind ENUM(\'user\', \'group\')');
        $this->addSql('CREATE INDEX idx_loa_main ON learning_organization_access (idOrgAccess, kind, value)');
        $this->addSql('ALTER TABLE learning_coursepath CHANGE img_coursepath img_coursepath VARCHAR(255) NOT NULL, CHANGE check_path_info check_path_info TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
