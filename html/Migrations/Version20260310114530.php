<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260310114530 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix learning_trackingeneral and learning_tracksession: add timestamps, fix encoding and datetime defaults';
    }

    public function up(Schema $schema): void
    {
        // Fix zero-date values BEFORE modifying the column (strict mode would reject them)
        $this->addSql("UPDATE learning_trackingeneral SET timeof = NULL WHERE timeof = '0000-00-00 00:00:00'");

        // Check which columns need to be added
        $hasCreatedAt = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learning_trackingeneral' AND COLUMN_NAME = 'created_at'"
        );
        $hasUpdatedAt = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learning_trackingeneral' AND COLUMN_NAME = 'updated_at'"
        );

        // Build a SINGLE combined ALTER TABLE statement to minimize table locks
        $alterParts = [];
        $alterParts[] = "MODIFY COLUMN timeof datetime DEFAULT NULL";

        if ((int) $hasCreatedAt === 0) {
            $alterParts[] = "ADD COLUMN created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";
        }
        if ((int) $hasUpdatedAt === 0) {
            $alterParts[] = "ADD COLUMN updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        }

        // Charset conversion in the same ALTER
        $alterParts[] = "CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        $this->addSql("ALTER TABLE learning_trackingeneral " . implode(', ', $alterParts));

        // Fix learning_tracksession datetime defaults (separate table, single ALTER)
        $this->addSql("UPDATE learning_tracksession SET enterTime = NULL WHERE enterTime = '0000-00-00 00:00:00'");
        $this->addSql("UPDATE learning_tracksession SET lastTime = NULL WHERE lastTime = '0000-00-00 00:00:00'");
        $this->addSql("ALTER TABLE learning_tracksession MODIFY COLUMN enterTime datetime DEFAULT NULL, MODIFY COLUMN lastTime datetime DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE learning_trackingeneral CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci");
        // Note: created_at and updated_at columns are NOT removed as they may contain data
        // Note: timeof/enterTime/lastTime defaults kept as NULL (safer than '0000-00-00')
        $this->write('WARNING: created_at/updated_at columns retained. timeof/enterTime/lastTime defaults kept as NULL.');
    }
}