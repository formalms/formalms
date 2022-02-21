<?php



class Module_Calendar extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		drawCalendar();
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'personal' => ['code' => 'personal',
								'name' => '_PERSONAL',
								'image' => 'standard/identity.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png']
        ];
	}

	function getPermissionsForMenu($op) {
		return [
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,personal'),
			6 => $this->selectPerm($op, 'view,personal,mod'),
			7 => $this->selectPerm($op, 'view,personal,mod')
        ];
	}
	
}

?>