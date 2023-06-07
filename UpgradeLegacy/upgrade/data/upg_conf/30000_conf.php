<?php

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
function upgradeConfig30000($config)
{
    $config_sts = 0;	// error

    if (strpos($config, "cfg['trezero']") !== false) {
        // config already upgraded
        $config_sts = 1;	// no change required
    } else {
        $config_sts = 2;	// changed

        $config = $config . '
/**
 * Other params trezero
 */
//$cfg[\'trezero\'] = \'aggiornato a trezero\';		//define if different from php.ini setting
';
    }

    return [$config_sts, $config];
}
