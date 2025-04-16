<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410132244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX confirmed_idx ON core_user_temp (confirmed)');
        $this->addSql('CREATE INDEX id_member_cat_idx ON learning_catalogue_member (idCatalogue, idst_member)');
        $this->addSql('CREATE INDEX idx_cu_req_uns_idx ON learning_courseuser (requesting_unsubscribe)');
        $this->addSql('CREATE INDEX learning_kb_res_ ON learning_kb_res (r_item_id, r_type, r_env)');
        $this->addSql('DROP INDEX path ON learning_organization');
        $this->addSql('CREATE INDEX path ON learning_organization (path(255))');
        $this->addSql('CREATE INDEX idUser_act_last_idx ON learning_tracksession (idUser, active, lastTime)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX confirmed_idx ON core_user_temp');
        $this->addSql('DROP INDEX id_member_cat_idx ON learning_catalogue_member');
        $this->addSql('DROP INDEX idx_cu_req_uns_idx ON learning_courseuser');
        $this->addSql('DROP INDEX learning_kb_res_ ON learning_kb_res');
        $this->addSql('DROP INDEX path ON learning_organization');
        $this->addSql('CREATE INDEX path ON learning_organization (path)');
        $this->addSql('ALTER TABLE learning_organization_access CHANGE kind kind VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX idUser_act_last_idx ON learning_tracksession');
    }
}
