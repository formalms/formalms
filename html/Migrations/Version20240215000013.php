<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215000013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add indexes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE core_requests CHANGE name name VARCHAR(100)');
		$this->addSql('ALTER TABLE core_plugin CHANGE name name`VARCHAR(100)'); 
		// this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX active_idx ON core_plugin (active)');
        $this->addSql('CREATE INDEX app_name_idx ON core_requests (app, name)');
        $this->addSql('CREATE INDEX idTest_idUser_idx ON learning_testtrack (idTest, idUser)');
        $this->addSql('CREATE INDEX idUser_active_idx ON learning_tracksession (idUser, active)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idTest_idUser_idx ON learning_testtrack');
        $this->addSql('DROP INDEX app_name_idx ON core_requests');
        $this->addSql('DROP INDEX idUser_active_idx ON learning_tracksession');
        $this->addSql('DROP INDEX active_idx ON core_plugin');
    }
}
