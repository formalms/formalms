<?php

include('bootstrap.php');
include_once(_lib_.'/loggers/lib.logger.php');
require_once _base_.'/db/lib.docebodb.php';
set_time_limit(0);


DbConn::getInstance(false,array(
    'db_type'=>$_SESSION['db_info']['db_type'],
    'db_host'=>$_SESSION['db_info']['db_host'],
    'db_user'=>$_SESSION['db_info']['db_user'],
    'db_pass'=>$_SESSION['db_info']['db_pass']
));

sql_query("CREATE DATABASE IF NOT EXISTS ".$_SESSION['db_info']['db_name']);
sql_select_db($_SESSION['db_info']['db_name']);
sql_query("SET NAMES 'utf8'");
sql_query("SET CHARACTER SET 'utf8'");

$sq = 'ALTER DATABASE `' . $_SESSION['db_info']['db_name'] . "` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci";
sql_query($sq);

$result = TRUE;

// -- Finding mysql version -------------------
$qtxt = "SELECT VERSION()";
$q = sql_query($qtxt);
list($version) = sql_fetch_row($q);
$match = array();
preg_match("/^\\d+\\.\\d+/", $version, $match);
$sql_ver = $match[0] * 100;
// --------------------------------------------

$platform_arr = $_SESSION['platform_arr'];
foreach ($platform_arr as $platform_code => $platform_folder) {

	$fn = _installer_ . "/data/sql/" . $platform_code . ".sql";

	if (file_exists($fn)) {
		$res = importSqlFile($fn);
		$text .= $res['log'];

		if (!$res['ok']) {
			$result = false;
		}
	}
}
$jres = array();
if ($result) {

	$lang_install = $_SESSION["lang_install"];
	$lang_arr = Lang::getLanguageList("language");

	foreach ($lang_arr as $language) {
		if (!in_array($language, $lang_install)) {

			$qtxt = "DELETE FROM core_lang_language WHERE lang_code='" . $language . "'";
			$q = sql_query($qtxt);
		}
	}

	// Create the admin user
	registerAdminUser();
	// Store settings
	storeSettings();
	// Add roles
	addInstallerRoles();

	$jres['result'] = true;
	$jres['text'] = false;

} else {

	$jres['result'] = false;
	$jres['text'] = $text;
}

require_once(_base_.'/lib/lib.json.php');
$json = new Services_JSON();
ob_clean();
echo $json->encode($jres);

sql_close($db);
session_write_close();

// -------------------------------------------------------------------------------


function registerAdminUser() {

	// ----------- Registering admin user ---------------------------------

	$qtxt = "SELECT * FROM core_user WHERE userid='/" . $_SESSION['adm_info']["userid"] . "'";
	$q = sql_query($qtxt);

	if (($q) && (sql_num_rows($q) > 0)) { // Did the user refreshed the page?
		// You never know..
		$qtxt = "UPDATE core_user SET firstname='" . $_SESSION['adm_info']["firstname"] . "',
			lastname='" . $_SESSION['adm_info']["lastname"] . "',
			pass='" . md5($_SESSION['adm_info']["pass"]) . "' ";
		$qtxt.="WHERE userid='/" . $_SESSION['adm_info']["userid"] . "'";
		$q = sql_query($qtxt);
	} else { // Let's create the admin user..
		$qtxt = "INSERT INTO core_st (idst) VALUES(NULL)";
		$q = sql_query($qtxt);
		$user_idst = sql_insert_id();

		$qtxt = "SELECT groupid, idst FROM core_group WHERE groupid='/framework/level/godadmin' ";
		$qtxt.="OR groupid='/oc_0'";
		$q = sql_query($qtxt);

		$godadmin = 0;
		$oc_0 = 0;
		$res = array();
		if (($q) && (sql_num_rows($q) > 0)) {
			while ($row = sql_fetch_array($q)) {
				$res[$row["groupid"]] = $row["idst"];
			}
			$godadmin = $res["/framework/level/godadmin"];
			$oc_0 = $res["/oc_0"];
		}

		$qtxt = "INSERT INTO core_group_members (idst, idstMember) VALUES('" . $oc_0 . "', '" . $user_idst . "')";
		$q = sql_query($qtxt);
		$qtxt = "INSERT INTO core_group_members (idst, idstMember) VALUES('" . $godadmin . "', '" . $user_idst . "')";
		$q = sql_query($qtxt);

		$qtxt = "INSERT INTO core_user (idst, userid, firstname, lastname, pass, email) ";
		$qtxt.="VALUES ('" . $user_idst . "', '/" . $_SESSION['adm_info']["userid"] . "',
			'" . $_SESSION['adm_info']["firstname"] . "', '" . $_SESSION['adm_info']["lastname"] . "',
			'" . md5($_SESSION['adm_info']["pass"]) . "', '" . $_SESSION['adm_info']["email"] . "')";
		$q = sql_query($qtxt);
	}
}

function storeSettings() {
	require_once(_adm_.'/versions.php');

	$url = $_SESSION["site_url"];
	$qtxt = "UPDATE core_setting SET param_value='" . $url . "' ";
	$qtxt.="WHERE param_name='url'";
	$q = sql_query($qtxt);
	
	$qtxt = "UPDATE core_setting SET param_value='" . $_SESSION['sel_lang'] . "' ";
	$qtxt.="WHERE param_name='default_language'";
	$q = sql_query($qtxt);

	$qtxt = "INSERT INTO `core_setting` ";
	$qtxt.=" (`param_name`, `param_value`, `value_type`, `max_size`, `pack`, `regroup`, `sequence`, `param_load`, `hide_in_modify`, `extra_info`) ";
	$qtxt.=" VALUES ";
	$qtxt.=" ('core_version', '" . _file_version_ . "', 'string', 255, '0', 1, 0, 1, 1, '') ";
	$q = sql_query($qtxt);
	
}


function addInstallerRoles() {
	require_once(_lib_.'/installer/lib.role.php');

	$godadmin =getGroupIdst('/framework/level/godadmin');
	$oc0 =getGroupIdst('/oc_0');

	$fn = _installer_."/data/role/rolelist_godadmin.txt";
	$roles=explode("\n", file_get_contents($fn));
	addRoles($roles, $godadmin);

	$fn = _installer_."/data/role/rolelist_oc0.txt";
	$roles=explode("\n", file_get_contents($fn));
	addRoles($roles, $oc0);
}