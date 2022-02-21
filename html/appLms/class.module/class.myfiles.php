<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_MyFiles extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		myfilesDispatch($GLOBALS['op']);
	}
	
	function getAllToken() {
		
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
}

?>