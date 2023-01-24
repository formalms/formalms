<?php

use Doctrine\DBAL\DriverManager;

define('IN_FORMA', true);
if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}
else {

    require sys_get_temp_dir(). '/config.php';
}

DbConn::getInstance(null,$cfg);

return DriverManager::getConnection([
    'driver' => 'pdo_mysql',
    'user' => $cfg['db_user'],
    'password' => $cfg['db_pass'],
    'dbname' => $cfg['db_name'],
    'host' => $cfg['db_host']
]);