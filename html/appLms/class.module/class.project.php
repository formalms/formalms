<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Project extends LmsModule {
	
	function loadBody() {
		require_once($GLOBALS['where_lms'].'/modules/project/project.php');
		projectDispatch($GLOBALS['op']);
	}
	
	function useExtraMenu() {
		return false;
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'add' => ['code' => 'add',
								'name' => '_ALT',
								'image' => 'standard/add.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'],
			'del' => ['code' => 'del',
								'name' => '_DEL',
								'image' => 'standard/delete.png']
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,mod'),
			6 => $this->selectPerm($op, 'view,add,mod,del'),
			7 => $this->selectPerm($op, 'view,add,mod,del')
        ];
	}
	
}

?>