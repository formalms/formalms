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
 * @package  DoceboLms
 * @subpackage  menu managment
 * @version  $Id: lib.manmenu_course.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course menu managment
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

function getAllCustom() {
	
	$query = "
	SELECT idCustom, title 
	FROM ".$GLOBALS['prefix_lms']."_menucustom 
	ORDER BY title";
	$re_custom = sql_query($query);
	
	$customs = array();
	while(list($id, $name) = sql_fetch_row($re_custom)) {
		$customs[$id] = $name;
	}
	return $customs;
}

function getModuleNextSeq($id_main) {
	
	$query_seq = "
	SELECT MAX(sequence)
	FROM ".$GLOBALS['prefix_lms']."_menucourse_under 
	WHERE idMain = '".$id_main."'";
	list($seq) = sql_fetch_row(sql_query($query_seq));
	return ($seq + 1);
}

/**
 * Find the idst of the group of a course that represent the level
 * @param 	string 	$id_course 	the id of the course
 * 
 * @return 	array	[lv] => idst, [lv] => idst
 */
function &getCourseLevelSt($id_course) {
	
	$map 		= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& Docebo::user()->getAclManager();
	
	// find all the group created for this menu custom for permission management
	foreach($levels as $lv => $name_level) {
		
		$group_info = $acl_man->getGroup(FALSE, '/lms/course/'.$id_course.'/subscribed/'.$lv);
		$map[$lv] 	= $group_info[ACL_INFO_IDST];
	}
	return $map;
}

/**
 * Return the association from token_code and idst
 * @param 	string 	$module_name 	the module name
 * @param 	array 	$all_token 		all the token_code of the module
 * @param 	array 	$flip 			if true flip the returned array
 * 
 * @return 	array	[token_code] => idst, [token_code] => idst			$flip = false
 * 					[idst] => token_code, [idst] => token_code			$flip = true
 */
function &getModuleRoleSt($module_name, $all_token, $flip = false) {
	
	$map 		= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& Docebo::user()->getAclManager();
	
	// find the idst of all the role of the selected module
	foreach($all_token as $token) {
		
		$code 		= $token['code'];
		$role_info 	= $acl_man->getRole(FALSE, '/lms/course/private/'.$_SESSION['idCourse'].'/'.$module_name.'/'.$code);
		if($role_info === FALSE) {
			$id_role = $acl_man->registerRole('/lms/course/private/'.$_SESSION['idCourse'].'/'.$module_name.'/'.$code, '');
		} else {
			$id_role = $role_info[ACL_INFO_IDST];
		}
		if($flip === false) $map[$code]	= $id_role;
		else $map[$id_role] = $code;
	}
	return $map;
}

/**
 * Return the actual permission of a module
 * @param 	array 	$group_idst 	[lv] => idst_level, [lv] => idst_level
 * @param 	array 	$idst_cast 		cast the idst only on these [idst] => xxx, [idst] => xxx
 * 
 * @return 	array	[lv] => ( [idst] => 1, [idst] => 2, ...)
 */
function &getAllModulesPermissionSt($group_idst, $idst_cast = false) {
	
	$old_perm 	= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& Docebo::user()->getAclManager();
	
	// find all the roles associated to the main groups
	foreach($levels as $lv => $name_level) {
		
		$lv_perm = $acl_man->getRolesContainer($group_idst[$lv], true);
		if($idst_cast === false) {
			
			$old_perm[$lv] = $lv_perm;
		} else {
			
			$i = 0;
			$old_perm[$lv] = array();
			foreach($lv_perm as $idst => $v) {
				
				if(isset($idst_cast[$idst])) $old_perm[$lv][$idst] = 1;
			}
		}
	}
	return $old_perm;
}

/**
 * Convert the array with the permission of a module from token_code to idst
 * @param 	array 	$map_idst_roles	[lv] => ( [token_code] => 1, [token_code] => 1)
 * @param 	array 	$token			[token_code] => idst, [token_code] => idst
 *
 * @return 	array	an array where the token_code of the first array is substituded with 
 *					the idst indicated in the second array
 */
function &fromTokenToSt(&$tokens, &$map_idst) {
	
	$new_perm 	= array();
	$levels 	= CourseLevel::getLevels();
	// convert all the permission from token code to idst
	foreach($levels as $lv => $name_level) {
		
		if(is_array($tokens[$lv])) {
			foreach($tokens[$lv] as $token_code => $v) {
				
				$new_perm[$lv][$map_idst[$token_code]] = 1;
			}
		}
	}
	return $new_perm;
	
}

/**
 * Convert the array with the permission of a module from idst to token_code
 * @param 	array 	$map_idst_roles	[lv] => ( [idst] => 0, [idst] => 1)
 * @param 	array 	$token			[idst] => token_code, [idst] => token_code
 *
 * @return 	array	an array where the idst of the first array is substituded with 
 *					the token_code indicated in the second array
 */
function &fromStToToken(&$map_idst_roles, &$token) {
	
	$convert 	= array();
	$levels 	= CourseLevel::getLevels();
	
	foreach($levels as $lv => $name_level) {
		
		if(is_array($map_idst_roles[$lv])) {
			foreach($map_idst_roles[$lv] as $idst => $v) {
				
				$convert[$lv][$token[$idst]] = $v;
			}
		}
	}
	return $convert;
}

?>