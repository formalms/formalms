<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Wiki extends LmsModule {

	function loadBody() {
		require_once($GLOBALS['where_lms'].'/modules/wiki/wiki.php');
		wikiDispatch($GLOBALS['op']);
	}

	function useExtraMenu() {
		return false;
	}

	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'edit' => ['code' => 'edit',
								'name' => '_MOD_WIKI',
								'image' => 'standard/edit.png'],
			'admin' => ['code' => 'admin',
								'name' => '_ADMIN_WIKI',
								'image' => 'standard/property.png']
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,edit'),
			6 => $this->selectPerm($op, 'view,edit'),
			7 => $this->selectPerm($op, 'view,edit,admin')
        ];
	}

}

?>
