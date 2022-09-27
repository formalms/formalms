<?php

define('IN_FORMA',true);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

// replace with file to your own project bootstrap
require __DIR__ . '/../html/vendor/autoload.php';

require __DIR__ . '/../html/config.php';

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\HelperSet;

$paths = [__DIR__ . "/../html/Entity"];
$isDevMode = true;

// the connection configuration
$dbParams = [
    'driver' => 'pdo_mysql',
    'user' => $cfg['db_user'],
    'password' => $cfg['db_pass'],
    'dbname' => $cfg['db_name'],
    'host' => $cfg['db_host']
];

//$cache = new \Doctrine\Common\Cache\ArrayCache();
//
//$reader = new \Doctrine\Common\Annotations\AnnotationReader();
//$driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver($reader, $paths);

$config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
#$config->setMetadataCacheImpl( $cache );
#$config->setQueryCacheImpl( $cache );
#$config->setMetadataDriverImpl( $driver );

$entityManager = EntityManager::create($dbParams, $config);

$platform = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

#return new HelperSet([
#    'em' => new EntityManagerHelper($entityManager),
#    'db' => new ConnectionHelper($entityManager->getConnection()),
#]);

// replace with file to your own project bootstrap
//require_once 'bootstrap.php';



return ConsoleRunner::createHelperSet($entityManager);
