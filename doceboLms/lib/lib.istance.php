<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2004 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */
/**
 * @package 	library
 * @subpackage	lms
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.istance.php 573 2006-08-23 09:38:54Z fabio $
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
function createModule($module_name, $class_name = NULL) {
	$module_name = preg_replace('/[^a-zA-Z0-9\-\_]+/', '', $module_name);
	if(file_exists(dirname(__FILE__).'/../class.module/class.'.$module_name.'.php')) {
		
		include_once(dirname(__FILE__).'/../class.module/class.'.$module_name.'.php');
		if( $class_name === NULL ) $class_name = 'Module_'.ucfirst($module_name); 
	} else {

		include_once(dirname(__FILE__).'/../class.module/class.definition.php');
		$class_name = 'LmsModule';
	}
	$module_cfg = new $class_name();
	return $module_cfg;
}

?>