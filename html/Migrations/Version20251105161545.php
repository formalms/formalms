<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251105161545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql("DELETE t1 FROM learning_certificate_assign t1
                            JOIN learning_certificate_assign t2 
                            ON t1.id_certificate = t2.id_certificate AND t1.id_course = t2.id_course AND  t1.id_user = t2.id_user AND t1.id > t2.id");
        $this->addSql('CREATE UNIQUE INDEX uniqueCertKey ON learning_certificate_assign (id_certificate, id_course, id_user)');
   
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('DROP INDEX uniqueCertKey ON learning_certificate_assign');
 }
}
