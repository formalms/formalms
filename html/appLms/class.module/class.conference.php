<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package 
 * @subpackage 
 * @category 
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5
 * 
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 ) 
 */

class Module_Conference extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		dispatchConference($GLOBALS['op']);
	}
	
	function getAllToken($op = '') {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_SCHEDULE',
								'image' => 'standard/edit.png']
        ];
	}


	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,mod'),
			6 => $this->selectPerm($op, 'view,mod'),
			7 => $this->selectPerm($op, 'view,mod')
        ];
	}
	
}

?>