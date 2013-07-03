<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package admin-library
 * @subpackage module
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.istance.php 831 2006-11-27 21:58:49Z fabio $
 */

/**
 * create a istance of a specified class of a module
 * automaticaly include the file that contains the class of the module
 *
 * @param string	$module_name 	the name og the module to istance
 * @param string 	$class_name 	the name of the class relative to the module, if not passed is
 *									extracted from the $module_name
 *
 * @return mixed 	the class istance
 */
function &createModule($module_name, $class_name = NULL) {
	$module_name = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $module_name);
	if(!isset($_SESSION['current_action_platform']))
		$_SESSION['current_action_platform'] = 'framework';
	
	switch($_SESSION['current_action_platform']) {
		case "framework" : {
			$where = _adm_;
			$def_class_name = 'Module';
		};break;
		case "lms" : {
			$where = _lms_.'/admin';
			$def_class_name = 'Module';
		};break;
		case "cms" : {
			$where = _cms_.'/admin';
			$def_class_name = 'Module';
		};break;
		case "scs" : {
			$where = _scs_.'/admin';
			$def_class_name = 'Module';
		};break;
	}

	if($module_name == 'item' || $module_name == 'scorm') {
		
		$def_class_name = 'Module';
		$where = _lms_;
		require_once($where.'/class.module/class.definition.php');
	}
	
	if(file_exists($where.'/class.module/class.'.$module_name.'.php')) {

		require_once($where.'/class.module/class.'.$module_name.'.php');
		if( $class_name === NULL ) $class_name = $def_class_name.'_'.ucfirst($module_name);
	}
	elseif(file_exists(_adm_.'/class.module/class.'.$module_name.'.php')) {

		require_once(_adm_.'/class.module/class.'.$module_name.'.php');
		if( $class_name === NULL ) $class_name = $def_class_name.'_'.ucfirst($module_name);
	} else {

		require_once($where.'/class.module/class.definition.php');
		$class_name = $def_class_name;
	}
	
	$module_cfg = new $class_name();
	
	return $module_cfg;
}


function &createLmsModule($module_name) {
	$module_name = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $module_name);
	include_once(_lms_.'/class.module/class.definition.php');
	if(file_exists(_lms_.'/class.module/class.'.$module_name.'.php')) {

		include_once(_lms_.'/class.module/class.'.$module_name.'.php');
		$class_name = 'Module_'.ucfirst($module_name);
	} else {
		$class_name = 'LmsModule';
	}
	$module_cfg = new $class_name();
	return $module_cfg;
}

?>