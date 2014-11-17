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
 * @version  $Id: class.report.php 573 2006-08-23 09:38:54Z fabio $
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Report extends LmsAdminModule {
	
	
	function loadBody() {
		
		if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/admin/modules/'.$this->module_name.'/'.$this->module_name.'.php') && Get::cfg('enable_customscripts', false) == true ){
			require_once(_base_ . '/customscripts/'._folder_lms_.'/admin/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		} else {
			require_once(_base_ . '/'._folder_lms_.'/admin/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		}

		reportDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return array( 
			'view' => array( 'code' => 'view',
							'name' => '_VIEW',
							'image' => 'standard/view.png'),
			
			'mod' => array( 'code' => 'mod',
							'name' => '_MOD',
							'image' => 'standard/edit.png')
		);
	}
}

?>