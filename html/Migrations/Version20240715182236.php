<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240715182236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $arrayModulesOB = [
            '_WIKI',
            '_CALENDAR',
            '_NOTES',
            '_PROJECT'
        ];
       
        $arrayDefaultNameOB = implode(', ', array_map(function ($element) { return '"'.$element.'"'; }, $arrayModulesOB));

        $connection = $this->connection;

         // Step 1: Controlla se esiste il record
        $countModulesOB = $connection->fetchOne('SELECT COUNT(*) FROM `learning_module` WHERE `default_name` IN  ?', $arrayDefaultNameOB);

        // Step 2: Cancella il campo se esiste
        if ($countModulesOB > 0) {
            $connection->executeStatement('DELETE FROM `learning_module` WHERE `default_name` IN  ?', $arrayDefaultNameOB);
            
        }
        // cancellazione tabella progetto e derivate      
        $this->addSql("DELETE FROM `learning_sysforum` WHERE `learning_sysforum`.`key1` ='project_message' AND `learning_sysforum`.`key2` IN (SELECT `learning_prj`.`id` FROM `learning_prj`)");
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_files`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_tasks`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_news`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_todo`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj_users`');
        $this->addSql('DROP TABLE IF EXISTS `learning_prj`');
        
        // cancellazione tabella blocco note
        $this->addSql('DROP TABLE IF EXISTS `learning_notes`');

        //cancellazione tabella calendar e derivate
        $this->addSql('DROP TABLE IF EXISTS `learning_calendar`');
        $this->addSql('DROP TABLE IF EXISTS `learning_classroom_calendar`');
        $this->addSql('DROP TABLE IF EXISTS `core_calendar`');

        //cancellazione tabella wiki e derivate
        $this->addSql('DROP TABLE IF EXISTS `learning_wiki_course`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_page_info`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_revision`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki_page`');
        $this->addSql('DROP TABLE IF EXISTS `core_wiki`');
       


    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
