<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410102433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX idx_lang_code_stats ON core_lang_language (lang_code, lang_description)');
        $this->addSql('ALTER TABLE core_lang_translation ADD CONSTRAINT FK_49179944627579FF FOREIGN KEY (lang_code) REFERENCES core_lang_language (lang_code)');
        $this->addSql('CREATE INDEX idx_category_id ON learning_category (idCategory)');
        $this->addSql('CREATE INDEX idx_category_path ON learning_category (path(255))');
        $this->addSql('CREATE INDEX idx_course_category ON learning_course (idCategory)');
        $this->addSql('CREATE INDEX idx_course_name ON learning_course (name)');
        $this->addSql('CREATE INDEX idx_course_code ON learning_course (code)');
        $this->addSql('CREATE INDEX idx_course_dates ON learning_course (date_begin, date_end)');
        $this->addSql('CREATE INDEX idx_course_sub_dates ON learning_course (sub_start_date, sub_end_date)');
        $this->addSql('CREATE INDEX idx_course_create_date ON learning_course (create_date)');
        $this->addSql('CREATE INDEX idx_course_date_unsubscribe ON learning_course_date (unsubscribe_date_limit)');
        $this->addSql('CREATE INDEX idx_courseuser_waiting ON learning_courseuser (waiting)');
        $this->addSql('CREATE INDEX idx_courseuser_date_inscr ON learning_courseuser (date_inscr)');
        $this->addSql('CREATE INDEX idx_courseuser_date_first_access ON learning_courseuser (date_first_access)');
        $this->addSql('CREATE INDEX idx_courseuser_date_complete ON learning_courseuser (date_complete)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX idx_lang_code_stats ON core_lang_language');
        $this->addSql('ALTER TABLE core_lang_translation DROP FOREIGN KEY FK_49179944627579FF');
        $this->addSql('DROP INDEX idx_category_id ON learning_category');
        $this->addSql('DROP INDEX idx_category_path ON learning_category');
        $this->addSql('DROP INDEX idx_course_category ON learning_course');
        $this->addSql('DROP INDEX idx_course_name ON learning_course');
        $this->addSql('DROP INDEX idx_course_code ON learning_course');
        $this->addSql('DROP INDEX idx_course_dates ON learning_course');
        $this->addSql('DROP INDEX idx_course_sub_dates ON learning_course');
        $this->addSql('DROP INDEX idx_course_create_date ON learning_course');
        $this->addSql('DROP INDEX idx_course_date_unsubscribe ON learning_course_date');
        $this->addSql('DROP INDEX idx_courseuser_waiting ON learning_courseuser');
        $this->addSql('DROP INDEX idx_courseuser_date_inscr ON learning_courseuser');
        $this->addSql('DROP INDEX idx_courseuser_date_first_access ON learning_courseuser');
        $this->addSql('DROP INDEX idx_courseuser_date_complete ON learning_courseuser');
    }
}
