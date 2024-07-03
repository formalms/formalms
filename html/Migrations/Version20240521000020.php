<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use FormaLms\lib\Helpers\HelperTool;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521000020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add unique coursereport table';
    }

    public function up(Schema $schema): void
    {

        $this->addSql(HelperTool::dropIndexIfExistsQueryBuilder());

        $this->addSql($this->dropIndexIfExists('unique_coursereport', 'learning_coursereport'));

        $this->addSql(HelperTool::dropProcedure('drop_index_if_exists'));

        $connection = $this->connection;

        $courseReportDataToDelete = $connection->fetchAllAssociative('SELECT MIN(id_report) as min_id_report FROM learning_coursereport GROUP BY source_of, id_course, id_source');

        $courseReportScoreDataToDelete = $connection->fetchAllAssociative('SELECT id_report FROM learning_coursereport');

        $courseReportDataToDelete = array_map(function($item) {
            return $item['min_id_report'];
        }, $courseReportDataToDelete);

        $courseReportScoreDataToDelete = array_map(function($item) {
            return $item['id_report'];
        }, $courseReportScoreDataToDelete);
        
        if (!empty($courseReportDataToDelete)) {
            //delete all duplicates
            $this->addSql('DELETE FROM learning_coursereport WHERE id_report NOT IN (' . implode(',',$courseReportDataToDelete) . ')');
        }

        $this->addSql('DELETE FROM learning_coursereport WHERE id_course = 0;');
        if (!empty($courseReportScoreDataToDelete)) {
            $this->addSql('DELETE FROM learning_coursereport_score WHERE id_report NOT IN (' . implode(', ', $courseReportScoreDataToDelete) . ')');
        }

        $this->addSql('ALTER TABLE learning_coursereport ADD CONSTRAINT unique_coursereport UNIQUE (source_of, id_course, id_source)');

        $connection = $this->connection;

        // Step 1: Controlla se esiste il record
        $count = $connection->fetchOne('SELECT COUNT(*) FROM `core_setting` WHERE `param_name` = ?', ['force_scorm_finish']);

        // Step 2: Inserisci il record se non esiste
        if ($count == 0) {
            $connection->executeStatement('INSERT INTO `core_setting` (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                'force_scorm_finish', 'on', 'enum', 3, '0', 4, 17, 1, 0, ''
            ]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE learning_coursereport
        DROP INDEX unique_coursereport');
    }

    private function dropIndexIfExists($index, $table)
    {

        return 'CALL drop_index_if_exists ( "' . $index . '", "' . $table . '")';
    }
}
