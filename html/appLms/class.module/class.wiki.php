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

class Module_Wiki extends LmsModule {

	function loadBody() {
		require_once($GLOBALS['where_lms'].'/modules/wiki/wiki.php');
		wikiDispatch($GLOBALS['op']);
	}

	function useExtraMenu() {
		return false;
	}

	function getAllToken($op) {
		return array(
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			'edit' => array( 	'code' => 'edit',
								'name' => '_MOD_WIKI',
								'image' => 'standard/edit.png'),
			'admin' => array( 	'code' => 'admin',
								'name' => '_ADMIN_WIKI',
								'image' => 'standard/property.png')
		);
	}

	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,edit'),
			6 => $this->selectPerm($op, 'view,edit'),
			7 => $this->selectPerm($op, 'view,edit,admin')
		);
	}

}

?>
