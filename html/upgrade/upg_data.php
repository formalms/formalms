<?php

// start buffer
ob_start();

include('bootstrap.php');
require('../config.php');
include_once(_base_."/db/lib.docebodb.php");

sql_query("SET NAMES 'utf8'");
sql_query("SET CHARACTER SET 'utf8'");

$enabled_step = 5;
$current_step = Get::gReq('cur_step', DOTY_INT);
$upg_step = Get::gReq('upg_step', DOTY_INT);

// allowed err codes
$allowed_err_codes = array();
array_push($allowed_err_codes, 1060); // ER_DUP_FIELDNAME
array_push($allowed_err_codes, 1068); // ER_MULTIPLE_PRI_KEY
array_push($allowed_err_codes, 1091); // ER_CANT_DROP_FIELD_OR_KEY

if ($_SESSION['start_version'] >= 3000 && $_SESSION['start_version'] < 4000) {
	echo 'error: version (' . $_SESSION['start_version'] . ') not supported for upgrade: too old (v3)';
	die();
}

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

if ($_SESSION['upgrade_ok']) {
	$current_ver =$to_upgrade_arr[$upg_step-1];
	if ($current_ver != $last_ver) {
		$formalms_version =$GLOBALS['cfg']['versions'][$current_ver];
	}
	else {
		$formalms_version =$GLOBALS['cfg']['versions'][$GLOBALS['cfg']['endversion']];
	}
	$upgrade_msg .= " <br/>" . "Upgrading to version: ".$formalms_version;

	// --- pre upgrade -----------------------------------------------------------
	$fn =_upgrader_.'/data/upg_data/'.$current_ver.'_pre.php';
	if (file_exists($fn)) {
		$GLOBALS['debug'] .=  " <br/>" . "Source pre-upgrade file: " . $fn ;
		require($fn);
		$func ='preUpgrade'.$current_ver;
		if (function_exists($func)) {
			$GLOBALS['debug'] .=  " <br/>" . "Execute pre-upgrade func: " . $func ;
			$res =$func();
			if (!$res) { $_SESSION['upgrade_ok']=false; }
		}
	}


	if ($_SESSION['upgrade_ok']) {
		// --- sql upgrade -----------------------------------------------------------
		$fn =_upgrader_.'/data/upg_data/'.$current_ver.'_db.sql';
		if (file_exists($fn)) {
			$GLOBALS['debug'] .=  " <br/>" . "Upgrade db with file: " . $fn ;
			$res =importSqlFile($fn, $allowed_err_codes);
			if (!$res['ok']) {
				$_SESSION['upgrade_ok']=false;
			}
		}
	}

	if ($_SESSION['upgrade_ok']) {
		// --- post upgrade ----------------------------------------------------------
		$fn =_upgrader_.'/data/upg_data/'.$current_ver.'_post.php';
		if (file_exists($fn)) {
			$GLOBALS['debug'] .=  " <br/>" . "Source post-upgrade file: " . $fn ;
			require($fn);
			$func ='postUpgrade'.$current_ver;
			if (function_exists($func)) {
			$GLOBALS['debug'] .=  " <br/>" . "Execute post-upgrade func: " . $func ;
				$res =$func();
				if (!$res) {
					$_SESSION['upgrade_ok']=false;
				}
			}
		}
	}


	if ($_SESSION['upgrade_ok']) {
		// --- roles -----------------------------------------------------------------
		require_once(_lib_.'/installer/lib.role.php');
		$fn =_upgrader_.'/data/upg_data/'.$current_ver.'_role.php';
		if (file_exists($fn)) {
			$GLOBALS['debug'] .=  " <br/>" . "Source role-upgrade file: " . $fn ;
			require($fn);
			$func ='upgradeUsersRoles'.$current_ver;
			if (function_exists($func)) {
			$GLOBALS['debug'] .=  " <br/>" . "Execute role-upgrade func: " . $func ;
				$role_list =$func();
				if (!empty($role_list)) {
					$role_list_arr =explode("\n", $role_list);
					$oc0 =getGroupIdst('/oc_0'); // all users
					addRoles($roles, $oc0);
				}
			}
			$func ='upgradeGodAdminRoles'.$current_ver;
			if (function_exists($func)) {
			$GLOBALS['debug'] .=  " <br/>" . "Execute role-upgrade func: " . $func ;
				$role_list =$func();
				if (!empty($role_list)) {
					$role_list_arr =explode("\n", $role_list);
					$godadmin =getGroupIdst('/framework/level/godadmin'); // god admin
					addRoles($roles, $godadmin);
				}
			}
		}
	}

}


// Save version number if upgrade was successfull:
if ($_SESSION['upgrade_ok']) {
	$qtxt ="UPDATE core_setting SET param_value = '".$formalms_version."' WHERE param_name = 'core_version' ";
	$q =sql_query($qtxt);
        
	//MODIFICA TEMPORANEA reset del template di default a STANDARD in futuro controllo del templates
	$qtxt ="UPDATE core_setting SET param_value = 'standard' WHERE param_name = 'defaultTemplate' ";
	$q =sql_query($qtxt);
        
}





$GLOBALS['debug'] = $upgrade_msg
					. '<br/>' . 'Result: ' . ( $_SESSION['upgrade_ok'] ? 'OK ' : 'ERROR !!! ' )
					. '<br/>' . $GLOBALS['debug']
					.'<br>------' ;


//echo $GLOBALS['debug'];

if ( $_SESSION['upgrade_ok'] ) {
		$res =array('res'=>'ok', 'msg' => $GLOBALS['debug']);
} else {
		$res =array('res'=>'Error', 'msg' => $GLOBALS['debug']);
}

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

/**/
require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();
echo $json->encode($res);
session_write_close();

// flush buffer
ob_end_flush();

die();


// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------


?>