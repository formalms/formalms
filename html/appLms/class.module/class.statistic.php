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

class Module_Statistic extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/statistic/statistic.php');
		statisticDispatch($GLOBALS['op']);
	}

	function getAllToken($op) {
			return array( 	'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'), 
					'view_all' => array( 	'code' => 'view_all',
								'name' => '_VIEW_ALL',
								'image' => 'standard/moduser.png') );
	}

	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, ''),
			2 => $this->selectPerm($op, ''),
			3 => $this->selectPerm($op, ''),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view'),
			6 => $this->selectPerm($op, 'view,view_all'),
			7 => $this->selectPerm($op, 'view,view_all')
		);
	}	
}

?>