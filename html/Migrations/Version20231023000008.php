<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231023000008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Deleting entry for managing YUI editor ';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM `core_hteditor` WHERE `core_hteditor`.`hteditor` = 'yui'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("INSERT INTO `core_hteditor` (`hteditor`, `hteditorname`) VALUES ('yui', '_YUI')");

    }
}
