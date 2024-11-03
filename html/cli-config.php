<?php
if (!defined('IN_FORMA')){
    define('IN_FORMA',true);
}

use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\YamlFile;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;

use Symfony\Component\Console\Helper\HelperSet;
// replace with file to your own project bootstrap
require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/config.php';

$paths = [__DIR__ . "/Entity"];
$isDevMode = true;
$config = new YamlFile(__DIR__ . '/migrations.yaml');
// the connection configuration
$dbParams = [
    'driver' => 'pdo_mysql',
    'user' => $cfg['db_user'],
    'password' => $cfg['db_pass'],
    'dbname' => $cfg['db_name'],
    'host' => $cfg['db_host']
];


$ORMconfig = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, null, null, false);


$entityManager = EntityManager::create($dbParams, $ORMconfig);

$platform = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));


return ConsoleRunner::createHelperSet($entityManager);
