<?php

include('bootstrap.php');
require('../config.php');
include_once(_base_."/db/lib.docebodb.php");

sql_query("SET NAMES 'utf8'");
sql_query("SET CHARACTER SET 'utf8'");

$enabled_step = 4;
$current_step = Get::gReq('cur_step', DOTY_INT);
$upg_step = Get::gReq('upg_step', DOTY_INT);

if ($_SESSION['start_version'] < 3000 || $_SESSION['start_version'] >= 4000 ) {
	echo 'error: version (' . $_SESSION['start_version'] . ') not supported for upgrade: too new (v4)';
	die();
}

if ( $current_step != $enabled_step ) {
	echo 'error: procedure must be called from upgrade step ' . $enabled_step . ' only!!';
	die();
}

switch($upg_step) {
	case "1": { // --- Upgrade db structure --------------------------------------
		$fn = _upgrader_.'/data/sql/pre_upgrade.sql';
		importSqlFile($fn);
	} break;
	case "2": { // --- Upgrade learning_module content ---------------------------
		updateLearningModule();
	} break;
	case "3": { // --- Upgrade some db data --------------------------------------
		$fn = _upgrader_.'/data/sql/upgrade01.sql';
		importSqlFile($fn);

		$re = sql_query("SELECT idCourse, idUser, COUNT(*)
		FROM learning_courseuser
		GROUP BY idCourse, idUser
		HAVING COUNT(*) >= 2");

		while(list($idc, $idu, $occurency) = sql_fetch_row($re)) {

			$query = "DELETE FROM learning_courseuser WHERE idCourse = ".$idc." AND idUser = ".$idu." LIMIT ".($occurency-1);
			if(!sql_query($query)) {

				$GLOBALS['debug'] .= $query.' - '.sql_error();
			}
		}

		$re = sql_query("SELECT id_category, lang_code, text_name, text_desc
			FROM learning_competence_category_text
			WHERE 1");

		while(list($id_cat, $lang_code, $name, $description) = sql_fetch_row($re))
			sql_query("INSERT INTO learning_competence_category_lang
				VALUES (".$id_category.", '".$lang_code."', '".$name."', '".$description."')");

		$re = sql_query("SELECT id_category, lang_code, text_name, text_desc
			FROM learning_competence_text
			WHERE 1");

		while(list($id_cat, $lang_code, $name, $description) = sql_fetch_row($re))
			sql_query("INSERT INTO learning_competence_lang
				VALUES (".$id_category.", '".$lang_code."', '".$name."', '".$description."')");

	} break;
	case "4": { // --- Upgrade trees (ileft / iright) ----------------------------
		$GLOBALS['tree_st'] = '';
		$tables = array(
			'core_org_chart_tree' => 'idOrg',
			'learning_category' => 'idCategory',
		);
		foreach ($tables as $tab=>$p_key) {
			populate($tab, $p_key);
		}
	} break;
	case "5": { // --- Upgrade settings table ------------------------------------
		updateSettings();
	} break;
	case "6": { // --- Adding god admins and all users roles ---------------------
		addUpgraderRoles();
	} break;
	case "7": { // --- Remove old photo ------------------------------------------
		$query =	"SELECT photo"
					." FROM core_user"
					." WHERE avatar <> ''"
					." AND photo <> ''";

		$result = sql_query($query);

		while(list($photo) = sql_fetch_row($result)) {
			@unlink('../files/doceboCore/photo/'.$photo);
		}

		// create missining upload folders
		mkdir('../files/doceboLms/label');

	} break;
	case "8": { // --- Kb --------------------------------------------------------
		kbUpgrade();
	} break;
	case "9": { // --- Post upgrade queries --------------------------------------
		$fn = _upgrader_.'/data/sql/post_upgrade.sql';
		importSqlFile($fn);
		$_SESSION['start_version'] = 4040;
	} break;
}

$GLOBALS['debug'] = 'Execution step ' .$current_step . '.' . $upg_step . '<br/>Result: '
					. ( $GLOBALS['debug'] == '' ? 'OK' : $GLOBALS['debug'] );

echo $GLOBALS['debug'];



// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------




function populate($table_name, $field_id) {

	$search_query = "
	SELECT ".$field_id.", idParent, path, lev, iLeft, iRight
	FROM ".$table_name."
	ORDER BY path";
	$q = sql_query($search_query);
	if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

	if(!$q) return false;

	$table = array();
	$GLOBALS['tree_st'] = array(
		0 =>  array(
			'id' => 0,
			'id_parent' => 0,
			'path' => '/root/',
			'sons' => array(),
			'left' => 0,
			'right' => 0,
			'iLeft' => 1,
			'iRight' => sql_num_rows($q) * 2
		)
	);
	while(list($id, $idParent, $path, $deep, $il, $ir) = sql_fetch_row($q)) {

		$GLOBALS['tree_st'][$id] = array(
			'id' => $id,
			'id_parent' => $idParent,
			'path' => $path,
			'sons' => array(),
			'left' => 0,
			'right' => 0,
			'iLeft' => $il,
			'iRight' => $ir
		);

		if(isset($GLOBALS['tree_st'][$idParent]) && $id != 0) {
			$GLOBALS['tree_st'][$idParent]['sons'][$id] = $id;
		}
		$table[$deep][$id] = end(explode("/", $path));
	}

	$GLOBALS['count'] = 1;

	navigate(0);
	if($table_name == 'core_org_chart_tree') {
		// we need to update also idst_oc and idst_ocd
		$idst_oc = array();
		$qtxt ="SELECT idst, groupid FROM core_group WHERE groupid LIKE '/oc%' ";
		$q = sql_query($qtxt);
		while($row=sql_fetch_object($q)) {

			$idst_oc[$row->groupid] = $row->idst;
		}
	}
	foreach($GLOBALS['tree_st'] as $id => $node) {
		$qtxt ="
		UPDATE ".$table_name."
		SET iLeft = '".$node['left']."', iRight = '".$node['right']."'"
		.( $table_name == 'core_org_chart_tree' ? ", idst_oc = '".$idst_oc['/oc_'.$node['id']]."', idst_ocd = '".$idst_oc['/ocd_'.$node['id']]."' " : "" )
		."WHERE ".$field_id." = ".$node['id']."";

		$q2 =sql_query($qtxt);
		if (!$q2) { $GLOBALS['debug'].=sql_error()."\n"; }
	}
}


function navigate($nodeid) {

	$GLOBALS['tree_st'][$nodeid]['left'] = $GLOBALS['count'];
	$GLOBALS['count']++;

	if(empty($GLOBALS['tree_st'][$nodeid]['sons'])) {

		$GLOBALS['tree_st'][$nodeid]['right'] = $GLOBALS['count'];
		$GLOBALS['count']++;
		return;

	} else {
		foreach($GLOBALS['tree_st'][$nodeid]['sons'] as $id) {
			navigate($id);
		}

		$GLOBALS['tree_st'][$nodeid]['right'] = $GLOBALS['count'];
		$GLOBALS['count']++;
	}

}



// -----------------------------------------------------------------------------


function updateLearningModule() {

	$fn = _upgrader_."/data/sql/learning_module_new.sql";
	importSqlFile($fn);

	$fields ="t1.module_name, t1.default_op, t1.default_name, t1.token_associated,
		t1.file_name, t1.class_name, t1.module_info, t1.mvc_path, t2.idModule as old_id";
	$qtxt ="SELECT ".$fields." FROM learning_module_new as t1
		LEFT JOIN learning_module as t2 ON
		(t1.module_name=t2.module_name AND
		t1.default_name = t2.default_name)
		WHERE t2.module_name IS NULL OR t1.mvc_path != '' OR t1.module_info != ''";
	$q =sql_query($qtxt);
	if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

	if ($q) {
		while($row=sql_fetch_assoc($q)) {

			if ($row['old_id'] > 0) { // update (t1.mvc_path != '')
				$qtxt ="UPDATE learning_module SET module_name = '".$row['module_name']."',
					default_op = '".$row['default_op']."', default_name = '".$row['default_name']."',
					token_associated = '".$row['token_associated']."',
					file_name = '".$row['file_name']."',class_name = '".$row['class_name']."',
					module_info = '".$row['module_info']."',mvc_path = '".$row['mvc_path']."'
					WHERE learning_module.idModule ='".$row['old_id']."' LIMIT 1";
				$q2 =sql_query($qtxt);
				if (!$q2) { $GLOBALS['debug'].=sql_error()."\n"; }
			}
			else { // insert missing
				$qtxt ="INSERT INTO learning_module (module_name, default_op,
					default_name, token_associated, file_name, class_name, module_info,
					mvc_path) VALUES
					('".$row['module_name']."', '".$row['default_op']."',
					'".$row['default_name']."', '".$row['token_associated']."',
					'".$row['file_name']."', '".$row['class_name']."',
					'".$row['module_info']."', '".$row['mvc_path']."');";
				$q2 =sql_query($qtxt);
				if (!$q2) { $GLOBALS['debug'].=sql_error()."\n"; }
			}
		}
	}

	$qtxt ="DROP TABLE IF EXISTS `learning_module_new`;";
	sql_query($qtxt);
}


// -----------------------------------------------------------------------------


function addUpgraderRoles() {
	require_once(_lib_.'/lib/lib.role.php');

	$godadmin =getGroupIdst('/framework/level/godadmin');
	$oc0 =getGroupIdst('/oc_0');

	$fn = _upgrader_."/data/role/rolelist_godadmin.txt";
	$roles=explode("\n", file_get_contents($fn));
	addRoles($roles, $godadmin);

	$fn = _upgrader_."/data/role/rolelist_oc0.txt";
	$roles=explode("\n", file_get_contents($fn));
	addRoles($roles, $oc0);

	addMissingRoles();

}

function addMissingRoles() {
	require_once(_lib_.'/installer/lib.role.php');

	$role_nogroup = array(
					'/lms/course/public/pusermanagement/view',
					'/lms/course/public/pusermanagement/add',
					'/lms/course/public/pusermanagement/mod',
					'/lms/course/public/pusermanagement/del',
					'/lms/course/public/pusermanagement/approve_waiting_user',
					'/lms/course/public/pcourse/view',
					'/lms/course/public/pcourse/add',
					'/lms/course/public/pcourse/mod',
					'/lms/course/public/pcourse/del',
					'/lms/course/public/pcourse/moderate',
					'/lms/course/public/pcourse/subscribe',
					'/lms/course/public/public_report_admin/view',
					'/lms/course/public/public_newsletter_admin/view',
					'/lms/course/private/quest_bank/mod',
					'/lms/course/private/quest_bank/view',
					'/lms/course/private/reservation/mod',
					'/lms/course/private/reservation/view'
					);
	addRoles($role_nogroup);


	$role_group = array(
 					'/lms/course/private/coursecharts/view',
					'/lms/course/private/coursestats/view'
					);
	addRoles($role_group);

	$role_group = array(
					'/lms/course/private/presence/view'
					);
	addRoles($role_group);

	// group '/framework/level/godadmin'
	$groupId = getGroupIdst('/framework/level/godadmin');
	$role_godadmin = array(
					'/lms/course/public/pcertificate/view',
					'/lms/course/public/pcertificate/mod'
					);
	addRoles($role_godadmin, $groupId);


}

// -----------------------------------------------------------------------------


function updateSettings() {
	$fn = _upgrader_."/data/sql/core_setting_default.sql";
	importSqlFile($fn);

	$new_setting	= getSettingsArr('core_setting_default');

	// Unset Old settings
	unset($core_cfg['core_version']);
	unset($learning_cfg['lms_version']);

	$core_cfg		= getSettingsArr('core_setting');
	$learning_cfg	= getSettingsArr('learning_setting');
	$conference_cfg = getSettingsArr('conference_setting');
	$old_cfg		= array_merge($core_cfg, $learning_cfg, $conference_cfg);

	// Update the platform url
	$https=(isset($_SERVER["HTTPS"]) ? $_SERVER["HTTPS"] : FALSE);
	$base_url=($https ? "https://" : "http://").$_SERVER["HTTP_HOST"].dirname($_SERVER['PHP_SELF'])."/";
	$base_url=preg_replace("/upgrade\\/$/", "", $base_url);
	$default_cfg['url']['param_value']=$base_url;


	// empty the core_setting
	$qtxt = "TRUNCATE TABLE core_setting";
	$q=sql_query($qtxt);
	if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

	// Store config (we'll keep only the core_setting table)
	foreach($new_setting as $key=>$val) {
		$fields = array();
		foreach ($val as $fk => $fv) {

			if($fk == 'param_value') $fields[] = $fk."='".( isset($old_cfg[$fk]) ? $old_cfg[$fk][$fv] : $fv )."'";
			else $fields[] = $fk."='".$fv."'";
		}
		$fields_qtxt =implode(', ', $fields);
		$qtxt ="INSERT INTO core_setting SET ".$fields_qtxt;
		$q=sql_query($qtxt);
		if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }
	}

	$qtxt ="DROP TABLE IF EXISTS `core_setting_default`;";
	sql_query($qtxt);
}


function getSettingsArr($table) {
	$res = array();

	$qtxt = "SELECT * FROM ".$table." ORDER BY param_name";
	$q=sql_query($qtxt);
	if (!$q) { $GLOBALS['debug'].=sql_error()."\n"; }

	if ($q) {
		while($row=sql_fetch_assoc($q)) {
			$key = $row['param_name'];
			$res[$key] = $row;
		}
	}

	return $res;
}


function updateConfVal($param_name, & $from_arr, & $to_arr) {

	if (!isset($to_arr[$param_name]) && (isset($from_arr[$param_name]))) {
		$to_arr[$param_name]=$from_arr[$param_name];
	}
	else if (isset($to_arr[$param_name])) {
		$to_arr[$param_name]['param_value']=$from_arr[$param_name]['param_value'];
	}

}

function kbUpgrade() {

	$qtxt = "INSERT INTO learning_kb_res (r_name, original_name, r_item_id, r_type, r_env, r_env_parent_id)"
		."SELECT title as title1, title as title2, idResource, objectType, 'course_lo', idCourse FROM learning_organization WHERE objectType <> '' ";
	$q=sql_query($qtxt);
}


// -----------------------------------------------------------------------------


?>