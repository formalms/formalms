<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301000015 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dropping signature field into core_user table and fix permission on dashboard';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE core_menu_under set module_name = 'dashboard' WHERE default_name = '_DASHBOARD' and of_platform = 'lms'");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        //
    }

}
