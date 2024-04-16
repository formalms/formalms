<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240416000017 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'change varchar 100';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE core_requests CHANGE name name VARCHAR(100)');
		$this->addSql('ALTER TABLE core_plugin CHANGE name name VARCHAR(100)'); 
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE core_requests CHANGE name name VARCHAR(255)');
		$this->addSql('ALTER TABLE core_plugin CHANGE name name VARCHAR(255)'); 
    }
}
