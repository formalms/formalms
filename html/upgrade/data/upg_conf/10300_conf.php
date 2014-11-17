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
 * This function must return always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug']
 */


// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeConfig10300($config) {

	$config_sts = 1 ;	// no change required

    $sts = 0;
    if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_smtp($config);
 		if ( $sts != 1 )  $config_sts = $sts;
   }
   if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_keepalive($config);
		if ( $sts != 1 )  $config_sts = $sts;
    }
    if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_customscripts($config);
 		if ( $sts != 1 )  $config_sts = $sts;
   }
    if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_plugin($config);
		if ( $sts != 1 )  $config_sts = $sts;
    }
    if ( $config_sts > 0 ) {
		list($sts, $config) = _update_cfg_tplengine($config);
 		if ( $sts != 1 )  $config_sts = $sts;
   }

	return array($config_sts,$config);
}

// bug #3213
function _update_cfg_smtp($_config) {

	$sts = 0;
	if ( strpos($_config, "cfg['smtp_port']") !== false &&
		 strpos($_config, "cfg['smtp_secure']") !== false	) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {

		$re_smtp = "/(^.*cfg\['smtp_user'\])/m";

		$new_params=
"//\$cfg['smtp_port'] = '';			// Options: '' (default port) , port number
//\$cfg['smtp_secure'] = '';			// Options: '', 'ssl', 'tls'
$1";

		if ( preg_match($re_smtp, $_config) ) {
			$sts = 2;	// changed
			$_config=preg_replace($re_smtp, $new_params, $_config);
		} else {
			$sts = 1;	// no change required
		}

	}

	return array($sts,$_config);
}


// bug #3116
function _update_cfg_keepalive($_config) {

	$sts = 0;
	if ( strpos($_config, "cfg['keepalivetmo']") !== false ) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {
		$sts = 2;	// changed

		$_config = $_config . '
//$cfg[\'keepalivetmo\'] = \'\';			// timeout for keepalive, must be < session lifetime, 0 to disable keepalive
';

	}

	return array($sts,$_config);
}

// new feature #3628
function _update_cfg_customscripts($_config) {

	$sts = 0;
	if ( strpos($_config, "cfg['enable_customscripts']") !== false ) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {
		$sts = 2;	// changed

		$_config = $_config . '
//$cfg[\'enable_customscripts\'] = false;	// enable custom scripts processing;  accepted value: true , false ; default false
';

	}

	return array($sts,$_config);
}

// new feature #3632
function _update_cfg_plugin($_config) {

	$sts = 0;
	if ( strpos($_config, "cfg['enable_plugins']") !== false ) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {
		$sts = 2;	// changed

		$_config = $_config . '
//$cfg[\'enable_plugins\']       = false; // enable plugins processing;  accepted value: true , false ; default false
';

	}

	return array($sts,$_config);
}

// new feature #3629
function _update_cfg_tplengine($_config) {

	$sts = 0;
	if ( strpos($_config, "cfg['template_engine']") !== false ) {
		// config already upgraded
		$sts = 1;	// no change required
	} else {
		$sts = 2;	// changed

		$_config = $_config . '
/**
 * Template engine custom param
 * -------------------------------------------------------------------------
 * add all template_engine enabled (if exists)
 * array value=file extension
 * template_engine available: twig
 */
//$cfg[\'template_engine\'][\'twig\'] = array(\'ext\' => \'.twig\');
';

	}

	return array($sts,$_config);
}


