<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_MyCompetences extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/mycompetences/mycompetences.php');
		mycompetencesDispatch($GLOBALS['op']);
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