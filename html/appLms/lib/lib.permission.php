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

function funAccess($functionname, $mode, $returnValue = false, $custom_mod_name = false) {
	
	return true;
	checkPerm($mode, $returnValue, $custom_mod_name);
}


function checkPerm($mode, $return_value = false, $use_mod_name = false, $is_public = false) {
	
	if($use_mod_name != false) $mod_name = $use_mod_name;
	else $mod_name = $GLOBALS['modname'];
	
	switch($mode) {
		case "OP" :
		case "view" : $suff = 'view';break;
		case "NEW" :
		case "add" : $suff = 'add';break;
		case "MOD" :
		case "mod" : $suff = 'mod';break;
		case "REM" :
		case "del" : $suff = 'del';break;
		default:  $suff = $mode;
	}
	$role = '/'.Get::cur_plat().'/'
		.( isset($_SESSION['idCourse']) && $is_public == false ? 'course/private/'.$_SESSION['idCourse'].'/' : 'course/public/' )
		.$mod_name.'/'.$suff;
	if(!$return_value && isset($_SESSION['idCourse'])) {
		require_once(_lms_.'/lib/lib.track_user.php');
		TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], $mod_name, $suff);
	}
	
	if(Docebo::user()->matchUserRole($role)) {
		
		return true;
	} else {
		
		if($return_value) { return false; }
		else { die("You can't access".$role); }
	}
}

function checkPermForCourse($mode, $id_course, $return_value = false, $use_mod_name = false) {
	
	if($use_mod_name != false) $mod_name = $use_mod_name;
	else $mod_name = $GLOBALS['modname'];
	
	switch($mode) {
		case "OP" :
		case "view" : $suff = 'view';break;
		case "NEW" :
		case "add" : $suff = 'add';break;
		case "MOD" :
		case "mod" : $suff = 'mod';break;
		case "REM" :
		case "del" : $suff = 'del';break;
		default:  $suff = $mode;
	}
	
	$role = '/'.Get::cur_plat().'/course/private/'.$id_course.'/'.$mod_name.'/'.$suff;
	
	if(!$return_value && isset($_SESSION['idCourse'])) {
		
		TrackUser::setActionTrack(getLogUserId(), $_SESSION['idCourse'], $mod_name, $suff);
	}
	
	if(Docebo::user()->matchUserRole($role)) {
		
		return true;
	} else {
		
		if($return_value) return false;
		else die("You can't access");
	}
}

function checkRole($roleid, $return_value = true) {

	if(Docebo::user()->matchUserRole($roleid)) return true;
	if($return_value) return false;
	else die("You can't access");
}
?>
