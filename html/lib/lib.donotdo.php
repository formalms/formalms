<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/**
 * This file contains function that tells in wich cases the
 * system should avoid to perform some core operations like
 * cleaning the HTML or replacing the site base url with the
 * {site_base_url} tag...
 */

define("_sep_", '--');

$GLOBALS['clean_html'] = array();
$GLOBALS['clean_html']["framework"._sep_."configuration"._sep_."config"._sep_."google_stat_code"] = 1;

$GLOBALS['clean_url'] = array();
$GLOBALS['clean_url']["framework"._sep_."configuration"._sep_."config"._sep_."url"] = 1;

/**
 * @param array 	$list array with the list of cases in wich we can skip
 *                          a certain operation for a specified field
 *                          The array format is platform ($sep) module name
 *                          ($sep) op ($sep) field name
 * @param string $field_name the name of the specified field to check
 *
 * @return bool true if we can skip the operation.
 */
function checkSkipList(&$list, $field_name, $name_modname="modname", $name_op="op") {

	$res = FALSE;
	$platform = Get::cur_plat();
	$modname = Get::req($name_modname, DOTY_ALPHANUM, "");
	$op = Get::req($name_op, DOTY_ALPHANUM, "");

	if(isset($list[$platform._sep_.$modname._sep_.$op._sep_.$field_name])) {

		$res =TRUE;
	}

	return $res;
}



function dontCleanHtml($field_name, $req_admin=TRUE) {
	$res =FALSE;

	if(Docebo::user()->isAnonymous())
			return $res;
	$level_id = Docebo::user()->getUserLevelId();

	if($level_id == ADMIN_GROUP_GODADMIN) return true;

	if (($req_admin) && ($level_id != ADMIN_GROUP_GODADMIN) && ($level_id != ADMIN_GROUP_ADMIN)) {
		return $res;
	}
	$platform 	= Get::cur_plat();
	$modname 	= Get::req('modname', DOTY_ALPHANUM, "");
	$op 		= Get::req('op', DOTY_ALPHANUM, "");

	$res = false;
	if(isset($GLOBALS['clean_html'][$platform._sep_.$modname._sep_.$op._sep_.$field_name])) {
		$res = TRUE;
	}
	return $res;
}


function dontReplaceBaseUrl($field_name, $req_admin=TRUE) {
	$res =FALSE;

	if(Docebo::user()->isAnonymous())
			return $res;
	$level_id = Docebo::user()->getUserLevelId();

	if (($req_admin) && ($level_id != ADMIN_GROUP_GODADMIN) && ($level_id != ADMIN_GROUP_ADMIN)) {
		return $res;
	}

	$platform 	= Get::cur_plat();
	$modname 	= Get::req('modname', DOTY_ALPHANUM, "");
	$op 		= Get::req('op', DOTY_ALPHANUM, "");

	$res = false;
	if(isset($GLOBALS['clean_url'][$platform._sep_.$modname._sep_.$op._sep_.$field_name])) {
		$res = TRUE;
	}
	return $res;
}


?>