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
       
        $this->addSql($this->dropIndexIfExists('unique_coursereport','learning_quest_type'));

        $this->addSql(HelperTool::dropProcedure('drop_index_if_exists'));
        //delete all duplicates
        $this->addSql('DELETE FROM learning_coursereport
                            WHERE id_report NOT IN (
                                SELECT MIN(id_report) 
                                FROM learning_coursereport 
                                GROUP BY source_of, id_course, id_source
                                )');

        $this->addSql('DELETE FROM learning_coursereport
                            WHERE id_course = 0;
                            DELETE FROM learning_coursereport_score
                            WHERE id_report NOT IN (
                                SELECT id_report
                                FROM learning_coursereport 
            )');

        $this->addSql('ALTER TABLE learning_coursereport
        ADD CONSTRAINT unique_coursereport UNIQUE (source_of, id_course, id_source)');


        $this->addSql(HelperTool::insertSettingIfNotExists('force_scorm_finish', ['param_value'=>'on', 
                                                                                'value_type'=>'enum', 
                                                                                'max_size'=>3, 
                                                                                'pack'=>'0', 
                                                                                'regroup'=>4, 
                                                                                'sequence'=>17, 
                                                                                'param_load'=>1, 
                                                                                'hide_in_modify'=>0, 
                                                                                'extra_info'=>'' ]));
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE learning_coursereport
        DROP INDEX unique_coursereport');
    }

    private function dropIndexIfExists($index, $table) {

        return 'CALL drop_index_if_exists ( "'.$index.'", "'. $table .'")';
    }
}
