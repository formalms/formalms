<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package admin-core
 * @subpackage regional_setting
 */
 
require_once(dirname(__FILE__).'/class.definition.php');

class Module_Regional_settings extends Module {
	
	function loadBody() {
		
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		regsetDispatch( $GLOBALS['op'] );
		
	}
	
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
	
}

?>
