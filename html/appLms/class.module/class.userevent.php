<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_UserEvent extends LmsModule {
	
	function loadBody() {
		require_once($GLOBALS['where_framework'].'/modules/event_manager/event_manager.php');
		eventDispatch($GLOBALS['op']);
	}
	
	function useExtraMenu() {
		return false;
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_SAVE',
								'image' => 'standard/edit.png']
        ];
	}
	
}

?>
