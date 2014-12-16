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
        if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/class.module/class.'.$module_name.'.php' ) && Get::cfg('enable_customscripts', false) == true ){
		
                include_once(_base_ . '/customscripts/'._folder_lms_.'/class.module/class.'.$module_name.'.php' );
                if( $class_name === NULL ) $class_name = 'Module_'.ucfirst($module_name);
        }else if(file_exists(dirname(__FILE__).'/../class.module/class.'.$module_name.'.php')) {
		
		include_once(dirname(__FILE__).'/../class.module/class.'.$module_name.'.php');
		if( $class_name === NULL ) $class_name = 'Module_'.ucfirst($module_name); 
	} else {

		include_once(dirname(__FILE__).'/../class.module/class.definition.php');
		$class_name = 'LmsModule';
	}

	if(Get::cfg('enable_plugins', false)){
		if(checkIfPlugin($module_name)=="plugin"){
			include_once( Get::rel_path('plugins').'/'.$module_name.'/class/class.'.$module_name.'.php' ); 
			$class_name = 'Module_'.ucfirst($module_name);  
		}
	}
    
	$module_cfg = new $class_name();
	return $module_cfg;
}



function checkIfPlugin($module_name){
        list($module_info) = sql_fetch_row(sql_query(    "SELECT module_info"
                                                        ." FROM learning_module"
                                                        ." WHERE module_name = '".$module_name."'"));

        return $module_info;
    
}



?>