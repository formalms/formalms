<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Add filter_data_json column to learning_report_filter table
 *
 * This migration adds a new JSON column for storing report filter data.
 * The old serialized column (filter_data) is kept for backward compatibility
 * during the transition period. Data will be migrated automatically on first read.
 *
 * DEPRECATION TIMELINE:
 * - 4.2.0: filter_data column deprecated, filter_data_json introduced
 *          Both columns are written to for backward compatibility
 *          Reading prioritizes JSON, falls back to serialized
 *          Auto-migration on read populates filter_data_json from filter_data
 *
 * - 5.0.0 (planned): filter_data column will be removed
 *          filter_data_json may be renamed to filter_data
 *
 * BENEFITS OF JSON OVER SERIALIZE:
 * - No UTF-8 byte counting issues (fixes "Número" bug)
 * - Human readable for debugging
 * - ~25% smaller storage
 * - No PHP object injection vulnerabilities
 * - Interoperable with other languages/systems
 *
 * @see https://www.php.net/manual/en/function.json-encode.php
 */
final class Version20260121000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '[4.2.0] Add filter_data_json column to learning_report_filter - deprecates serialized filter_data';
    }

    public function up(Schema $schema): void
    {
        // Add new JSON column (nullable to allow gradual migration)
        $this->addSql('ALTER TABLE learning_report_filter ADD COLUMN filter_data_json LONGTEXT NULL AFTER filter_data');

        // Add a comment to mark the old column as deprecated
        $this->addSql("ALTER TABLE learning_report_filter MODIFY COLUMN filter_data TEXT NOT NULL COMMENT 'DEPRECATED: Use filter_data_json instead. Will be removed in future version.'");
    }

    public function down(Schema $schema): void
    {
        // Remove the JSON column
        $this->addSql('ALTER TABLE learning_report_filter DROP COLUMN filter_data_json');

        // Remove the deprecation comment
        $this->addSql('ALTER TABLE learning_report_filter MODIFY COLUMN filter_data TEXT NOT NULL');
    }
}
