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
function upgradeConfig10000($config) {

	$config_sts = 0 ;

	if ( strpos($config, 'IN_FORMA') !== false ) {
		// config already upgraded
		$config_sts = 1;
	} else {
		// config  upgraded
		$config_sts = 2;

		// change the definition of "IN_XXX" behavior
		$config=str_replace('defined("IN_DOCEBO")', 'defined("IN_FORMA")', $config);

		// change the license
		$old_license='!/\*.*\s.*\sDOCEBO.*\s.*\s.*\s.*\s.*\s.*= \*/!';

		$new_license='
/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
';

		$config=preg_replace($old_license, $new_license, $config);

	}

	return array($config_sts,$config);
}
