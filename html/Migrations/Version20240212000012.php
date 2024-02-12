<?php


declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240212000012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adding new security tab in configuration and relatives options';
    }

    public function up(Schema $schema): void
    {

        $this->addSql("update core_setting set regroup = 14 where  regroup = 3 and pack = 'password'");
        $this->addSql("update core_setting set regroup = 14, pack='security' where param_name in ('max_log_attempt', 'register_deleted_user', 'save_log_attempt')");
        $this->addSql("INSERT INTO core_setting 
                            (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info) 
                            VALUES 
                        ('pass_min_uppercase', 0, 'int', 1,'password', 14, 11, 1, 0, ''),
                        ('pass_min_lowercase', 0, 'int', 1,'password', 14, 12, 1, 0, ''),
                        ('pass_min_digit', 0, 'int', 1,'password', 14, 13, 1, 0, ''),
                        ('pass_special_char', 0, 'int', 1,'password', 14, 14, 1, 0, ''),
                        ('pass_send_remind', 'off', 'enum', 3, 'password', 14, 15, 1, 0, '');
                    ");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("update core_setting set regroup = 3 where  regroup = 14 and pack = 'password'");
        $this->addSql("update core_setting set regroup = 3, pack='0' where param_name in ('max_log_attempt', 'register_deleted_user', 'save_log_attempt')");
        $this->addSql("DELETE FROM core_setting where param_name in ('pass_min_uppercase', 'pass_min_lowercase', 'pass_min_digit', 'pass_special_char', 'pass_send_remind')");
    }
}
