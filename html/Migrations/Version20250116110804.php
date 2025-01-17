<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use FormaLms\lib\Helpers\HelperTool;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116110804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(HelperTool::dropIndexIfExistsQueryBuilder());

        $this->addSql('ALTER TABLE core_domain_configs CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');

        $this->addSql('ALTER TABLE core_plugin CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE core_requests CHANGE app app VARCHAR(70) NOT NULL, CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE dashboard_permission CHANGE idst_list idst_list LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE learning_communication_lang CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE learning_course CHANGE sub_start_date sub_start_date DATETIME DEFAULT NULL, CHANGE sub_end_date sub_end_date DATETIME DEFAULT NULL');
        $this->addSql($this->dropIndexIfExists('idPlugin','core_role'));
        $this->addSql($this->dropIndexIfExists('config_layout_fk','dashboard_block_config'));
        $this->addSql($this->dropIndexIfExists('idCourse_idReport_idx','learning_coursereport'));
        $this->addSql('CREATE INDEX status_course_type_idx ON learning_course (status, course_type)');
        $this->addSql('CREATE INDEX status_id_course_idx ON learning_course_editions (status, id_course)');
        $this->addSql('CREATE INDEX course_stats_idx ON learning_courseuser (idUser, idCourse, status, level)');
    }

    public function down(Schema $schema): void
    {

    }

    private function dropIndexIfExists($index, $table) {

        return 'CALL drop_index_if_exists ( "'.$index.'", "'. $table .'")';
    }
}
