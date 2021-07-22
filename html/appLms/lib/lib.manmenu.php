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
 * @version  $Id: lib.manmenu.php 813 2006-11-27 15:45:33Z fabio $
 * @category Course menu managment
 * @author	 Fabio Pirovano 
 */

function createCourseMenuFromCustom($id_custom, $id_course, $group_idst) {
	
	$acl_man =& Docebo::user()->getAclManager();
        
        $menucustom_course_upquery = 'UPDATE %lms_course SET id_menucustom='.$id_custom.' WHERE idCourse='.$id_course;
        sql_query($menucustom_course_upquery);
	
	$re_main = sql_query("
		SELECT idMain, sequence, name, image
		FROM %lms_menucustom_main
		WHERE idCustom = '".$id_custom."'"
	);
	
	$main_values = array();
	$query_ins_main = "
	INSERT INTO %lms_menucourse_main (idCourse, sequence, name, image ) VALUES";
	while(list($id_main, $seq, $name, $image) = sql_fetch_row($re_main)) {
		
		if(!sql_query($query_ins_main." ( '".$id_course."','".$seq."', '".addslashes($name)."', '".$image."')") ) {
			
			$map_main_id[$id_main] = false;
		} else {
			
			list($map_main_id[$id_main]) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
		}
	}
	
	// copy module ------------------------------------------
	$re_module = sql_query("
		SELECT idModule, idMain, sequence, my_name
		FROM ".$GLOBALS['prefix_lms']."_menucustom_under
		WHERE idCustom = '".$id_custom."'"
	);

	$module_values = array();
	$re = true;
	$query_ins_module = "INSERT INTO %lms_menucourse_under ( idCourse, idModule, idMain, sequence, my_name ) VALUES";
	while(list($id_module, $id_main, $seq, $my_name) = sql_fetch_row($re_module)) {
		
		if(isset($map_main_id[$id_main]) && ($map_main_id[$id_main] !== false)) {
			
			$re &= sql_query($query_ins_module
				." ('".$id_course."', '".$id_module."', '".$map_main_id[$id_main]."', '".$seq."', '".$my_name."')");
		}
	}
	//copy module permission
	$group_of_from 	=& getCustomLevelSt($id_custom);
	$perm_form 		=& createPermForCourse($group_of_from, $id_course);
	$levels  		=  CourseLevel::getLevels();
	
	foreach($levels as $lv => $name_level) {
		
		foreach($perm_form[$lv] as $idrole => $v) {
			
			if($group_idst[$lv] != 0 && $idrole != 0) {
				$acl_man->addToRole( $idrole, $group_idst[$lv] );
			}
		}
	}
	return $re;
}

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

function getAssociatedCustom($id_course) {
	/*$query = "
	SELECT mc.title 
	FROM ".$GLOBALS['prefix_lms']."_course AS c
	JOIN ".$GLOBALS['prefix_lms']."_menucustom AS mc
            ON c.id_menucustom = mc.idCustom
	WHERE idCourse=".$id_course;*/
	$query = "
	SELECT id_menucustom 
	FROM ".$GLOBALS['prefix_lms']."_course 
	WHERE idCourse=".$id_course;
	$re_asscustom = sql_query($query);
	
	list($id_menucustom) = sql_fetch_row($re_asscustom);
	return $id_menucustom;
}


/**
 * Return the ID of a custom menu searcing by a given menu name
 * @param string $name
 * @return integer 
 */
function getCustomMenuIdByName($name) {
	$res =false;
	
	$query = "
		SELECT idCustom 
		FROM %lms_menucustom
		WHERE title='".$name."' LIMIT 0,1";
	$re_custom =DbConn::getInstance()->query($query);
	
	if ($re_custom && DbConn::getInstance()->num_rows($re_custom)) {
		list($res) = DbConn::getInstance()->fetch_row($re_custom);
	}
	
	return $res;
}


function getIdCustomFromMain($id_main) {
	
	$query_custom = "
	SELECT idCustom 
	FROM ".$GLOBALS['prefix_lms']."_menucustom_main 
	WHERE idMain = '".$id_main."'";
	list($id_custom) = sql_fetch_row(sql_query($query_custom));
	return $id_custom;
}

function getModuleNextSeq($id_main) {
	
	$query_seq = "
	SELECT MAX(sequence)
	FROM ".$GLOBALS['prefix_lms']."_menucustom_under 
	WHERE idMain = '".$id_main."'";
	list($seq) = sql_fetch_row(sql_query($query_seq));
	return ($seq + 1);
}

function cleanTokenFromModule($module_tokens) {
	
	$cleaned = array();
	foreach($module_tokens as $v => $element) {
		$cleaned[] = $element['code'];
	}
	return $cleaned;
}

/**
 * Find the idst of the group of a menu custom that represent the level
 * @param 	string 	$id_custom 	the id of the ciustom menu
 * 
 * @return 	array	[lv] => idst, [lv] => idst
 */
function &getCustomLevelSt($id_custom) {
	
	$map 		= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& Docebo::user()->getAclManager();
	
	// find all the group created for this menu custom for permission management
	foreach($levels as $lv => $name_level) {
		
		$group_info = $acl_man->getGroup(FALSE, '/lms/custom/'.$id_custom.'/'.$lv);
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
		$role_info 	= $acl_man->getRole(FALSE, '/lms/course/private/'.$module_name.'/'.$code);
		
		//print_r($role_info);
		
		if($role_info === FALSE) {
			$id_role = $acl_man->registerRole('/lms/course/private/'.$module_name.'/'.$code, '');
		} else {
			$id_role = $role_info[ACL_INFO_IDST];
		}
		if($flip === false) $map[$code]	= $id_role;
		else $map[$id_role] = $code;
	}
	return $map;
}

function &createPermForCourse($group_idst, $id_course) {
	
	$base_perm = '/lms/course/private/';
	$map 		= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& Docebo::user()->getAclManager();
	$cut_at 	= strlen($base_perm);
	
	// find the idst of all the role of the selected module
	
	foreach($levels as $lv => $name_level) {
		
		$map[$lv] = array();
		$all_idst = $acl_man->getRolesContainer($group_idst[$lv], true);
		
		foreach($all_idst as $idst => $v) {
			
			$role_info 	= $acl_man->getRole($idst, false);
			
			if($role_info !== FALSE && strpos($role_info[ACL_INFO_ROLEID], $base_perm) !== false) {
				
				$role_suffix = substr($role_info[ACL_INFO_ROLEID], $cut_at );
				$new_role = '/lms/course/private/'.$id_course.'/'.$role_suffix;
				
				$new_role_info = $acl_man->getRole(false, $new_role);
				if($new_role_info === FALSE) {
					$id_role = $acl_man->registerRole($new_role, '');
				} else {
					$id_role = $new_role_info[0];
				}
				
				$map[$lv][$id_role]	= $role_suffix;
			}
			
		}
	}
	
	return $map;
}


function createModuleRoleForCourse($id_course, $module_name, $tokens) {
	
	$base_perm 	= '/lms/course/private/';
	$acl_man	=& Docebo::user()->getAclManager();
	
	$role_and_id = array();
	if(!is_array($tokens)) return $role_and_id;
	
	foreach($tokens as $token) {
	 	
	 	$new_role = '/lms/course/private/'.$id_course.'/'.$module_name.'/'.$token['code'];	
		$new_role_info = $acl_man->getRole(false, $new_role);
		if($new_role_info === FALSE) $id_role = $acl_man->registerRole($new_role, '');
		else $id_role = $new_role_info[0];
		
		$role_and_id[$token['code']] = $id_role;
	}
	
	return $role_and_id;
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
	
	//$map_idst[$lv] = $group_info[ACL_INFO_IDST];
	
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

 /************** DUPLICA CORSO *********/

 function &createPermForCoursebis($group_idst, $id_course, $id_principale) {
	$base_perm = '/lms/course/private/'.$id_principale.'/';
	$map 		= array();
	$levels 	= CourseLevel::getLevels();
	$acl_man	=& $GLOBALS['current_user']->getAclManager();
	$cut_at 	= strlen($base_perm);
	// find the idst of all the role of the selected module
	foreach($levels as $lv => $name_level) {
		$map[$lv] = array();
		$all_idst = $acl_man->getRolesContainer($group_idst[$lv], true);
		foreach($all_idst as $idst => $v) {
			$role_info 	= $acl_man->getRole($idst, false);
			if($role_info !== FALSE && strpos($role_info[ACL_INFO_ROLEID], $base_perm) !== false) {
				$role_suffix = substr($role_info[ACL_INFO_ROLEID], $cut_at );
				$new_role = '/lms/course/private/'.$id_course.'/'.$role_suffix;
				$new_role_info = $acl_man->getRole(false, $new_role);
				if($new_role_info === FALSE) {
					$id_role = $acl_man->registerRole($new_role, '');
				} else {
					$id_role = $new_role_info[0];
				}
				$map[$lv][$id_role]	= $role_suffix;
			}
		}
	}
	return $map;
}
 /****************/

?>