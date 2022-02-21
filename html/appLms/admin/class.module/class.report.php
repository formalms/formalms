<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package  DoceboLms
 * @version  $Id: class.report.php 573 2006-08-23 09:38:54Z fabio $
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Report extends LmsAdminModule {
	
	
	function loadBody() {
		
		require_once(Forma::inc(_base_ . '/'._folder_lms_.'/admin/modules/'.$this->module_name.'/'.$this->module_name.'.php'));

		reportDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
							'name' => '_VIEW',
							'image' => 'standard/view.png'],
			
			'mod' => ['code' => 'mod',
							'name' => '_MOD',
							'image' => 'standard/edit.png'],
			
			'schedule' => ['code' => 'schedule',
							'name' => '_SCHEDULE',
							'image' => 'standard/schedule.png'],
        ];
	}
}

?>