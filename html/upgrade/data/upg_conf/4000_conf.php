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

// Upgrade procedure from Docebo 3.6.x series
// Source and converted from old procedure

/**
 * This function must always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug']
 */


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeConfig4000($config) {

	$config_sts = 0 ;

	if(isset($GLOBALS['cfg']['db_type'])) {
		// config already upgraded
		$config_sts = 1;
	} else {
		// config  upgraded
		$config_sts = 2;

		require_once(_base_.'/config.php');
		$_SESSION['db_info']["db_host"]=$GLOBALS['dbhost'];
		$_SESSION['db_info']["db_user"]=$GLOBALS['dbuname'];
		$_SESSION['db_info']["db_pass"]=$GLOBALS['dbpass'];
		$_SESSION['db_info']["db_name"]=$GLOBALS['dbname'];

		if ($GLOBALS['uploadType'] == 'fs') {
			$_SESSION['upload_method']='http';
		} else {
			$_SESSION['upload_method']='ftp';
		}
		$_SESSION['ul_info']["ftp_host"]=$GLOBALS['ftphost'];
		$_SESSION['ul_info']["ftp_port"]=$GLOBALS['ftpport'];
		$_SESSION['ul_info']["ftp_user"]=$GLOBALS['ftpuser'];
		$_SESSION['ul_info']["ftp_pass"]=$GLOBALS['ftppass'];
		$_SESSION['ul_info']["ftp_path"]=$GLOBALS['ftppath'];

		$fn = _upgrader_.'/data/config_template.php';

		// generateConfig from install/lib/lib.php
		$config = generateConfig($fn);

	}

	return array($config_sts,$config);
}
