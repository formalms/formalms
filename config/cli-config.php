<?php

use Doctrine\ORM\Tools\Console\ConsoleRunner;

define('IN_FORMA',true);


// replace with file to your own project bootstrap
require __DIR__ . '/../html/vendor/autoload.php';

require __DIR__ . '/../html/config.php';

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\HelperSet;


$paths = [__DIR__ . "/../html/src/Entity"];
$isDevMode = true;

// the connection configuration
$dbParams = [
    'driver' => 'pdo_mysql',
    'user' => $cfg['db_user'],
    'password' => $cfg['db_pass'],
    'dbname' => $cfg['db_name'],
    'host' => $cfg['db_host']
];

$config = \Doctrine\ORM\ORMSetup::createAnnotationMetadataConfiguration($paths, $isDevMode);
$entityManager = \Doctrine\ORM\EntityManager::create($dbParams, $config);

return new HelperSet([
    'em' => new EntityManagerHelper($entityManager),
    'db' => new Symfony\Component\Console\Helper\($entityManager->getConnection()),

]);

new Doctrine\DBAL\Tools\Console\ConnectionProvider\SingleConnectionProvider()