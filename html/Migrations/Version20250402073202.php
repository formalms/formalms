<?php

declare(strict_types=1);

namespace Formalms\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402073202 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $settingsToAdd = [
            [
                'param_name' => 'sso_secret',
                'param_value' => '',
                'value_type' => 'text',
                'max_size' => 255,
                'pack' => '0',
                'regroup' => 9,
                'sequence' => 6,
                'param_load' => 1,
                'hide_in_modify' => 0,
                'extra_info' => ''
            ],
            [
                'param_name' => 'sso_token',
                'param_value' => 'off',
                'value_type' => 'enum',
                'max_size' => 3,
                'pack' => '0',
                'regroup' => 9,
                'sequence' => 5,
                'param_load' => 1,
                'hide_in_modify' => 0,
                'extra_info' => ''
            ]
        ];

        foreach ($settingsToAdd as $setting) {
            // Check if setting already exists
            $exists = $this->connection->fetchOne(
                'SELECT COUNT(*) FROM core_setting WHERE param_name = ?',
                [$setting['param_name']]
            );

            if (!$exists) {
                // Insert only if not exists
                $this->addSql("
                INSERT INTO core_setting 
                (param_name, param_value, value_type, max_size, pack, regroup, sequence, param_load, hide_in_modify, extra_info)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                    $setting['param_name'],
                    $setting['param_value'],
                    $setting['value_type'],
                    $setting['max_size'],
                    $setting['pack'],
                    $setting['regroup'],
                    $setting['sequence'],
                    $setting['param_load'],
                    $setting['hide_in_modify'],
                    $setting['extra_info']
                ]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
