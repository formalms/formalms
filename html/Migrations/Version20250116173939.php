<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116173939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE IF EXISTS core_calendar');
        $this->addSql('DROP TABLE IF EXISTS core_wiki');
        $this->addSql('DROP TABLE IF EXISTS core_wiki_page');
        $this->addSql('DROP TABLE IF EXISTS core_wiki_page_info');
        $this->addSql('DROP TABLE IF EXISTS core_wiki_revision');
        $this->addSql('DROP TABLE IF EXISTS learning_calendar');
        $this->addSql('DROP TABLE IF EXISTS learning_classroom_calendar');
        $this->addSql('DROP TABLE IF EXISTS learning_notes');
        $this->addSql('DROP TABLE IF EXISTS learning_prj');
        $this->addSql('DROP TABLE IF EXISTS learning_prj_files');
        $this->addSql('DROP TABLE IF EXISTS learning_prj_news');
        $this->addSql('DROP TABLE IF EXISTS learning_prj_tasks');
        $this->addSql('DROP TABLE IF EXISTS learning_prj_todo');
        $this->addSql('DROP TABLE IF EXISTS learning_prj_users');
        $this->addSql('DROP TABLE IF EXISTS learning_wiki_course');
        $this->addSql('CREATE INDEX associated_template_idst_oc_idx ON core_org_chart_tree (associated_template, idst_oc)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
