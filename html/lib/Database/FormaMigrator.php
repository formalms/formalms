<?php

namespace FormaLms\lib\Database;
/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

use Doctrine\DBAL\DriverManager;


use Doctrine\DBAL\Driver\PDO\PDOException;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\Migrations\Exception\MetadataStorageError;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;


class FormaMigrator
{
    private static ?FormaMigrator $instance = null;

    protected $connection;

    protected $dependencyFactory;

    protected YamlFile $configFile;

    public const ADMITTABLE_COMMANDS = ['list', 'migrate', 'status', 'current', 'uptodate'];

    /**
     * @return FormaMigrator
     */
    public static function getInstance(): FormaMigrator
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct()
    {

        $this->configFile = new YamlFile(_base_ . '/migrations.yaml');

    }

    public function setup()
    {

        $cfg = [];
        if (file_exists(_base_ . '/config.php')) {
            require _base_ . '/config.php';
        } else {
            if (file_exists(sys_get_temp_dir() . '/config.php')) {
                require sys_get_temp_dir() . '/config.php';
            }

        }

        try {
            $this->connection = DriverManager::getConnection([
                'user' => $cfg['db_user'],
                'password' => $cfg['db_pass'],
                'dbname' => $cfg['db_name'],
                'host' => $cfg['db_host'],
                'driver' => 'pdo_mysql',
            ]);
            $this->dependencyFactory = DependencyFactory::fromConnection(
                $this->configFile,
                new ExistingConnection($this->connection)
            );
        } catch (PDOException $pdoException) {

        } catch (Exception $e) {

        }

        $this->syncMetadata();

    }

    public function executeCommand(string $command, array $args = [])
    {
        $this->setup();
        if (!in_array($command, self::ADMITTABLE_COMMANDS)) {
            throw new Exception("Not Implement Command");
        }

        if (PHP_VERSION_ID < 80000) {
            return $this->$command(extract($args));
        }
        return $this->$command(...$args);
    }

    private function migrate($debug = false, $test = false)
    {

        $response['success'] = true;
        $writeSqlFile = _base_ . "/files/logs/migration" . floor(microtime(true) * 1000) . ".sql";
        $arguments = [];
        if ($debug) {
            $arguments['--write-sql'] = $writeSqlFile;
        }

        if ($test) {
            $arguments['--dry-run'] = true;
        }

        $command = new MigrateCommand($this->dependencyFactory);

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        $output = new BufferedOutput();


        $exitcode = $command->run($input, $output);


        if ($exitcode > 0) {
            $response['success'] = false;
            $response['message'] = $output->fetch();
            //throw new Exception($output->fetch());
        }

        $response['message'] = $output->fetch();
        if ($test) {
            $response['message'] = $writeSqlFile;
        }

        return $response;
    }

    private function syncMetadata()
    {

        $command = new SyncMetadataCommand($this->dependencyFactory);

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $output = new BufferedOutput();


        $exitcode = $command->run($input, $output);


        return $output->fetch();
    }

    private function uptodate()
    {


        $command = new UpToDateCommand($this->dependencyFactory);

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        try {
            $exitcode = $command->run($input, $output);
        } catch (MetadataStorageError $e) {

            return '[ERROR]';
        }
        //if ($exitcode > 0) {
        //    throw new Exception($output->fetch());
        //}

        return $output->fetch();

    }


    private function list()
    {

        $command = new ListCommand($this->dependencyFactory);

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        $exitcode = $command->run($input, $output);
        if ($exitcode > 0) {
            throw new Exception($output->fetch());
        }

        return $output->fetch();

    }

    private function current()
    {

        $command = new CurrentCommand($this->dependencyFactory);

        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        $exitcode = $command->run($input, $output);
        if ($exitcode > 0) {
            throw new Exception($output->fetch());
        }

        return $output->fetch();

    }

    private function status()
    {

        $command = new StatusCommand($this->dependencyFactory);
        $arguments['--show-versions'] = true;
        $input = new ArrayInput([]);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        $exitcode = $command->run($input, $output);
        if ($exitcode > 0) {
            throw new Exception($output->fetch());
        }

        return $output->fetch();

    }

    /**
     * @return \Doctrine\Migrations\Configuration\Configuration
     */
    public function getConfiguration()
    {

        return $this->configFile->getConfiguration();
    }


    public function getMigrationTableSettings()
    {

        return $this->configFile->getConfiguration()->getMetaDataStorageConfiguration();
    }

}

