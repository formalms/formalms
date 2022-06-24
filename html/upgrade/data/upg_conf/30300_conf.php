<?php

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

// if this file is not needed for a specific version,
// just don't create it.

//require_once('bootstrap.php');
//require_once('../config.php');

/**
 * This function must always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug'].
 */

// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeConfig30300($config)
{
    $config_sts = 0;	// error

    if (strpos($config, "cfg['session']") !== false) {
        // config already upgraded
        $config_sts = 1;	// no change required
    } else {
        $config_sts = 2;	// changed

        $sessionConfig = "//\$cfg['session']['handler'] = \FormaLms\lib\Session\SessionManager::FILESYSTEM; //filesystem | memcached | redis | pdo | mongodb";
        $sessionConfig .= "//\$cfg['session']['url'] = ''; // dsn pattern url to session server";
        $sessionConfig .= "//\$cfg['session']['timeout'] = (float)'2.5';";
        $sessionConfig .= "//\$cfg['session']['lifetime'] = (int) 3600; //session lifetime";
        $sessionConfig .= "//\$cfg['session']['prefix'] = 'core_sessions'; //session prefix or session table name in case of pdo";
        $sessionConfig .= "//\$cfg['session']['name'] = \$cfg['db_name']; //db name";
        $sessionConfig .= "//\$cfg['session']['port'] = 3306;  // process port session handler";
        $sessionConfig .= "//\$cfg['session']['host'] = \$cfg['db_host']; //host";
        $sessionConfig .= "//\$cfg['session']['authentication'] = true; //true | false";
        $sessionConfig .= "//\$cfg['session']['user'] = \$cfg['db_user']; // authentication user session handler";
        $sessionConfig .= "//\$cfg['session']['pass'] = \$cfg['db_pass']; // authentication psw session handler";
        $sessionConfig .= "//\$cfg['session']['options'] = []; // other options key value array to pass based on selected handler";

        $config = $config . $sessionConfig;
    }

    return [$config_sts, $config];
}
