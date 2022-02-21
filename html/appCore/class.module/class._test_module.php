<?php defined("IN_FORMA") or die('Direct access is forbidden.');



require_once(dirname(__FILE__).'/class.definition.php');


class Module__Test_Module extends Module {
	
	function loadBody() {
		global $op, $modname, $prefix;
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		dispatch( $op );
		
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
	
}

?>