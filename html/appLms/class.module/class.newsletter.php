<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Newsletter extends LmsModule {

	function loadBody() {
		require_once($GLOBALS['where_lms'].'/modules/newsletter/newsletter.php');
	}

	function useExtraMenu() {
		return false;
	}

	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'view_all' => ['code' => 'view_all',
						'name' => '_VIEW_ALL',
						'image' => 'standard/moduser.png'],
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view'),
			6 => $this->selectPerm($op, 'view'),
			7 => $this->selectPerm($op, 'view')
        ];
	}

}

?>
