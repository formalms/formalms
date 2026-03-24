<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change mediumTime / medium_time columns from INT to DECIMAL(10,2) so that
 * courses with non-integer durations (e.g. 1.5 days) are stored and displayed
 * correctly.
 *
 * Affected tables:
 *   - learning_course.mediumTime      (was INT UNSIGNED NOT NULL DEFAULT 0)
 *   - learning_course_date.medium_time (was INT NOT NULL DEFAULT 0)
 */
final class Version20260323120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change mediumTime / medium_time from INT to DECIMAL(10,2) to support non-integer course durations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE learning_course MODIFY COLUMN mediumTime DECIMAL(10,2) UNSIGNED NOT NULL DEFAULT 0.00');
        $this->addSql('ALTER TABLE learning_course_date MODIFY COLUMN medium_time DECIMAL(10,2) NOT NULL DEFAULT 0.00');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE learning_course MODIFY COLUMN mediumTime INT UNSIGNED NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE learning_course_date MODIFY COLUMN medium_time INT NOT NULL DEFAULT 0');
    }
}
