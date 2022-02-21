<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_MyGroup extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/mygroup/mygroup.php');
		mygroupDispatch($GLOBALS['op']);
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