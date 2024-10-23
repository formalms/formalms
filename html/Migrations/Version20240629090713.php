<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240629090713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
         // this up() migration is auto-generated, please modify it to your needs
         $connection = $this->connection;

         // Step 1: Controlla se esiste il record
         $countLdap = $connection->fetchOne('SELECT COUNT(*) FROM `core_setting` WHERE `param_name` LIKE  ?', ['ldap%']);
 
         // Step 2: Cancella il campo se esiste
         if ($countLdap > 0) {
             $connection->executeStatement('DELETE FROM `core_setting` WHERE `param_name` LIKE  ?', ['ldap%']);
             
         }
         $countLdapSSo = $connection->fetchOne('SELECT COUNT(*) FROM `core_setting` WHERE `param_name` LIKE  ?', ['sso%']);
 
         // Step 2: Cancella il campo se esiste
         if ($countLdapSSo > 0) {
             $connection->executeStatement('DELETE FROM `core_setting` WHERE `param_name` LIKE  ?', ['sso%']);
             
         }

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
