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

/**
 * This function must return always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug']
 */


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
require_once('bootstrap.php');
require_once('../config.php');
include_once(_base_."/db/lib.docebodb.php");
function upgradeConfig10400($config) {

	$config_sts = 1 ;	// no change required

    $sts = 0;
    if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_google($config);
 		if ( $sts != 1 )  $config_sts = $sts;
   }

	return array($config_sts,$config);
}


// bug/new feature #3988
function _update_cfg_google($_config) {


	$sts = 0;

	if ( strpos($_config, "cfg['use_google_login_oauth2']") !== false ) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {
		$todate = date('Ymd');
		$disdate = '20150420';	// google openid dismiss date 2015 apr 20

		if ( check_google_login() && $todate < $disdate ) {
			$sts = 2;	// changed
			$_config = $_config . '
/**
 * Social Google Options
 * -------------------------------------------------------------------------
 * use_google_login_oauth2: boolean to set whether to enable or not the oauth2 login in google connection instead of openid (default is TRUE)
 */
$cfg[\'use_google_login_oauth2\'] = false;
';

		} else {
			// google connect not active, no need to change config
			$sts = 1;	// no change required
		}
	}

	return array($sts,$_config);

}


function check_google_login() {

//include('./bootstrap.php');
require('../config.php');

	$query = "SELECT param_value FROM core_setting WHERE param_name = 'social_google_active'";
	$active = sql_fetch_row(sql_query($query));
	$query = "SELECT param_value FROM core_setting WHERE param_name = 'social_google_client_id'";
	$gc_id = sql_fetch_row(sql_query($query));

	// google login is active and use openid
    $isactive = ( $active[0] == "on" && empty($gc_id[0]) );

	return($isactive);
}
