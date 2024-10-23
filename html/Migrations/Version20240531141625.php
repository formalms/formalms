<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240531141625 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $connection = $this->connection;

        // Step 1: Controlla se esiste il record
        $countLabel = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu` WHERE `name` = ?', ['_LABEL']);

        // Step 2: Cancella il campo se esiste
        if ($countLabel > 0) {
            $connection->executeStatement('DELETE FROM `core_menu` WHERE `name` = ?', ['_LABEL']);
            $countLabelUnder = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu_under` WHERE `default_name` = ?', ['_LABEL']);
            if ($countLabelUnder > 0) {
                $connection->executeStatement('DELETE FROM `core_menu_under` WHERE `default_name` = ?', ['_LABEL']);
            }
        }
         
        // Step 1: Controlla se esiste il record
        $countContest = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu` WHERE `name` = ?', ['_CONTEST']);

        // Step 2: Cancella il campo se esiste
        if ($countContest > 0) {
            $connection->executeStatement('DELETE FROM `core_menu` WHERE `name` = ?', ['_CONTEST']);
            $countContestUnder = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu_under` WHERE `default_name` = ?', ['_CONTEST']);
            if ($countContestUnder > 0) {
                $connection->executeStatement('DELETE FROM `core_menu_under` WHERE `default_name` = ?', ['_CONTEST']);
            }
        }

         // Step 1: Controlla se esiste il record
        $countUseLabel = $connection->fetchOne('SELECT COUNT(*) FROM `core_setting` WHERE `param_name` = ?', ['use_course_label']);

        // Step 2: Cancella il campo se esiste
        if ($countUseLabel > 0) {
            $connection->executeStatement('DELETE FROM `core_setting` WHERE `param_name` = ?', ['use_course_label']);
        }

         // Step 1: Controlla se esiste il record
         $countTbGames = $connection->fetchOne('SELECT COUNT(*) FROM `learning_middlearea` WHERE `obj_index` = ?', ['tb_games']);

         // Step 2: Cancella il campo se esiste
         if ($countTbGames > 0) {
             $connection->executeStatement('DELETE FROM `learning_middlearea` WHERE `obj_index` = ?', ['tb_games']);
         }

         // Step 1: Controlla se esiste il record
         $countNews = $connection->fetchOne('SELECT COUNT(*) FROM `learning_middlearea` WHERE `obj_index` = ?', ['news']);

         // Step 2: Cancella il campo se esiste
         if ($countNews > 0) {
             $connection->executeStatement('DELETE FROM `learning_middlearea` WHERE `obj_index` = ?', ['news']);
         }

         // Step 1: Controlla se esiste il record
         $countNewsInternal = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu` WHERE `name` = ?', ['_NEWS_INTERNAL']);

         // Step 2: Cancella il campo se esiste
         if ($countNewsInternal > 0) {
             $connection->executeStatement('DELETE FROM `core_menu` WHERE `name` = ?', ['_NEWS_INTERNAL']);
             $countNewsInternalUnder = $connection->fetchOne('SELECT COUNT(*) FROM `core_menu_under` WHERE `default_name` = ?', ['_NEWS_INTERNAL']);
            if ($countNewsInternalUnder > 0) {
                $connection->executeStatement('DELETE FROM `core_menu_under` WHERE `default_name` = ?', ['_NEWS_INTERNAL']);
            }
         }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
