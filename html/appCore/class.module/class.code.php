<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package admin-core
 * @subpackage group_code
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Code extends Module {

	function loadBody() {
		global $op, $modname, $prefix;
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		codeDispatch($op);
	}
	
	function getAllToken($op)
	{
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']];
	}
	
}

?>