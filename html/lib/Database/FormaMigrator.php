<?php
namespace FormaLms\lib\Database;
/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;


class FormaMigrator
{
    private static ?FormaMigrator $instance = null;

    protected $connection;

    protected $dependencyFactory;

    public static function getInstance()
    {
        if (self::$instance === null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function __construct() {

        if (file_exists(_base_ . '/config.php')) {
            require _base_. '/config.php';
        }
        else {
            
            require sys_get_temp_dir(). '/config.php';

        }
        
        $this->connection = DriverManager::getConnection([
            'user' => $cfg['db_user'],
            'password' => $cfg['db_pass'],
            'dbname' => $cfg['db_name'],
            'host' => $cfg['db_host'],
            'driver' => 'pdo_mysql',
        ]);

        $config = new YamlFile(_base_.'/migrations.yaml');

        $this->dependencyFactory = DependencyFactory::fromConnection(
            $config,
            new ExistingConnection($this->connection)
        );
       
    }

    public function migrate($debug = false, $test = false) {
    
        $writeSqlFile = _base_. "/files/logs/migration" . floor(microtime(true) * 1000) .".sql";
        $arguments = [];
        if($debug) {
            $arguments['--write-sql'] = $writeSqlFile;
        }

        if($test) {
            $arguments['--dry-run'] = true;
        }
   
        $command = new MigrateCommand($this->dependencyFactory);

        $input = new ArrayInput($arguments);
        $input->setInteractive(false);

        $output = new BufferedOutput();

        $exitcode = $command->run($input, $output);
        if ($exitcode > 0) {
            throw new Exception($output->fetch());
        }

        $result = $output->fetch();
        if($test) {
            $result = $writeSqlFile;
        }
        
        return $result;
    }


}

