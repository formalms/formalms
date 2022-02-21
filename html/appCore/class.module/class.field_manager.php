<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package admin-core
 * @subpackage field
 */
 
require_once(dirname(__FILE__).'/class.definition.php');

class Module_Field_Manager extends Module {
	
	function loadBody() {
		
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			
			'add' => ['code' => 'add',
								'name' => '_ADD',
								'image' => 'standard/add.png'],
			
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'],
			
			'del' => ['code' => 'del',
								'name' => '_DEL',
								'image' => 'standard/delete.png']
        ];
	}
}

?>
