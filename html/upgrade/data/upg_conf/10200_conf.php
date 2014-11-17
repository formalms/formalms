<?php //if (!defined('IN_FORMA')) { die('You can\'t access!'); }

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
\ ======================================================================== */

// if this file is not needed for a specific version,
// just don't create it.

//require_once('bootstrap.php');
//require_once('../config.php');

/**
 * This function must always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug']
 */


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeConfig10200($config) {

	$config_sts = 0 ;	// error

	if ( strpos($config, "cfg['timezone']") !== false ) {
		// config already upgraded
		$config_sts = 1;	// no change required
	} else {
		$config_sts = 2;	// changed

		$config = $config . '
/**
 * Other params
 * -------------------------------------------------------------------------
 * timezone     = default site timezone , if not specified get default from php.ini date.timezone
 *                for valid timezone see http://www.php.net/manual/en/timezones.php
 * set_mysql_tz = set mysql timezone same as php timezone ,  valid value
 *                true = set ,  false = (default) not set
 */
//$cfg[\'timezone\'] = \'Europe/Rome\';		//define if different from php.ini setting
//$cfg[\'set_mysql_tz\'] = false;			//set mysql timezone same as php timezone , default false
';

	}

	return array($config_sts,$config);
}
