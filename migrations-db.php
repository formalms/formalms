<?php
use Doctrine\DBAL\DriverManager;
define('IN_FORMA', true);
require '/app/html/config.php';
return DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'user' => $cfg['db_user'],
    'password' => $cfg['db_pass'],
    'dbname' => $cfg['db_name'],
    'host' => $cfg['db_host']
]);