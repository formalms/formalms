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

class Module_Light_repo extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/light_repo/light_repo.php');
		lightrepoDispatch($GLOBALS['op']);
	}
	
	function getAllToken($op) {
		
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png' ),
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png' ),
                        'view_all' => array( 	'code' => 'view_all',
								'name' => '_VIEW_ALL',
								'image' => 'standard/moduser.png')
		);
	}

	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,mod'),
			6 => $this->selectPerm($op, 'view,mod,view_all'),
			7 => $this->selectPerm($op, 'view,mod,view_all')
		);
	}
}

?>