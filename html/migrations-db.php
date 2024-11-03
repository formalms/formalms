<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Driver\PDO\PDOException;

if (file_exists(__DIR__ . '/config.php')) {
    require __DIR__ . '/config.php';
}
else {

    if(file_exists(sys_get_temp_dir(). '/config.php')) {
        require sys_get_temp_dir(). '/config.php';
    }
    
}

\FormaLms\db\DbConn::getInstance(null,$cfg);

try 
{
    return DriverManager::getConnection([
        'driver' => 'pdo_mysql',
        'user' => $cfg['db_user'],
        'password' => $cfg['db_pass'],
        'dbname' => $cfg['db_name'],
        'host' => $cfg['db_host']
    ]);
} catch(\PDOException $pdoException) {

}
catch (Exception $e) {
  
}

