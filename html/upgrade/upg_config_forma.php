<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
\ ======================================================================== */

// start buffer
ob_start();


include('bootstrap.php');
require('../config.php');

$enabled_step = 3;
$current_step = Get::gReq('cur_step', DOTY_INT);
//$upg_step = Get::gReq('upg_step', DOTY_INT);

//if ($_SESSION['start_version'] >= 3000 && $_SESSION['start_version'] < 4000) {
//	echo 'error: version (' . $_SESSION['start_version'] . ') not supported for upgrade: too old (v3)';
//	die();
//}

// Collapse the upgrade from docebo 3.6xx into this procedure

if ( $current_step != $enabled_step ) {
	echo 'error: procedure must be called from upgrade step ' . $enabled_step . ' only!!';
	die();
}


if (!empty($_SESSION['to_upgrade_arr'])) {
	$to_upgrade_arr =$_SESSION['to_upgrade_arr'];
}
else {
	$to_upgrade_arr =getToUpgradeArray();
}

$last_ver =getVersionIntNumber($GLOBALS['cfg']['endversion']);

$fn_config = _base_.'/config.php';

$config ='';
// read current config file
if (file_exists($fn_config)) {
	$handle = fopen($fn_config, "r");
	$config = fread($handle, filesize($fn_config));
	fclose($handle);
}

$_SESSION['upgrade_ok'] = true;
$config_changed = false;

if ($_SESSION['upgrade_ok']) {
	$GLOBALS['debug'] .="<br/>" . "Upgrading config ";

	// for all upgrade step required
	foreach ($to_upgrade_arr as $upg_step => $current_ver) {

		$msg_version =$GLOBALS['cfg']['versions'][$current_ver];

		$GLOBALS['debug'] .=" <br/>" . "Upgrading config to version: ".$msg_version;

		// --- config upgrade -----------------------------------------------------------
		$fn =_upgrader_.'/data/upg_conf/'.$current_ver.'_conf.php';

		if (file_exists($fn)) {
			$GLOBALS['debug'] .=  " <br/>" . "Source upgrade config file: " . $fn ;
			require($fn);
			$func ='upgradeConfig'.$current_ver;
			if (function_exists($func)) {
				$GLOBALS['debug'] .=  " <br/>" . "Execute upgrade config func: " . $func ;
				list($sts, $config) = $func($config);
				if ( $sts <= 0 ) {
					$_SESSION['upgrade_ok']=false;
					break;
				} else if ( $sts > 1 ) { // made change in config
					$config_changed = true ;
				}
			}
		}
	}
}
else {
  $sts = 0;
}

$config_saved = false;
if ($_SESSION['upgrade_ok']) {
	if ( $config_changed ) {
		$dwnl = Get::req('dwnl', DOTY_ALPHANUM, '0');
		if ( $dwnl == 1 ) {
			// download new configuration
			downloadConfig($config);
		} else {
			$fn_new =_base_.'/config.php';
			$config_saved =saveConfig($fn_new, $config);
			$GLOBALS['debug'] .="<br/>" . "Save new config file: " .$fn_new ;
		}
	} else  {
		$GLOBALS['debug'] .="<br/>" . "NO CHANGE required to config file";
	}
} else {
		$GLOBALS['debug'] .="<br/>" . "Upgrade error!!";
}

$GLOBALS['debug'] = ""
					//. '<br/>' . 'Result: ' . ( $_SESSION['upgrade_ok'] ? 'OK ' : 'ERROR !!! ' )
					. '<br/> ----<br>' . $GLOBALS['debug']
					.'<br> -----' ;
if ( ! $_SESSION['upgrade_ok']) {
		$res =array('res'=>'Error', 'msg' => $GLOBALS['debug']);
} else if ( ! $config_changed ) {
		$res =array('res'=>'not_change', 'msg' => $GLOBALS['debug']);
} else if ( $config_saved ) {
		$res =array('res'=>'saved', 'msg' => $GLOBALS['debug']);
} else if ( ! $config_saved ) {
		$res =array('res'=>'not_saved', 'msg' => $GLOBALS['debug']);
} else {
		$res =array('res'=>'Error', 'msg' => $GLOBALS['debug']);
}

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

/* */
require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();
echo $json->encode($res);
session_write_close();

// flush buffer
ob_end_flush();

die();
/* */

// -----------------------------------------------------------------------------
// local function
// -----------------------------------------------------------------------------


function saveConfig($fn, $config) {
	$saved =file_put_contents($fn, $config);
	if ( ! $saved ) {
		$GLOBALS['debug'] .= " <br/>" . "Error saving config: file read-only or not accesisble: " . $fn ;
	}
	return($saved);
}

function downloadConfig($config) {

	header('Content-type: text/plain');
	header('Content-Disposition: attachment; filename="config.php"');

	echo $config;
	die();
}

?>