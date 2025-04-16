<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410133730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX core_user_valid_idx ON core_user (valid)');
        $this->addSql('CREATE INDEX core_user_name_idx ON core_user (firstname, lastname)');
        $this->addSql('CREATE INDEX core_user_email_idx ON core_user (email)');
        $this->addSql('CREATE INDEX core_user_idx ON core_user (userid(240), email(240), firstname(140), lastname(140))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX core_user_valid_idx ON core_user');
        $this->addSql('DROP INDEX core_user_name_idx ON core_user');
        $this->addSql('DROP INDEX core_user_email_idx ON core_user');
        $this->addSql('DROP INDEX core_user_idx ON core_user');
    }
}
