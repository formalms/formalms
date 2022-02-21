<?php defined("IN_FORMA") or die("You can't access this file directly");



class Module_Tags extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/tags/tags.php');
		tags_dispatch($GLOBALS['op']);
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png']
        ];
	}
	
}

?>