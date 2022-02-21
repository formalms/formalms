<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package admin-core
 * @subpackage newsletter
 */
require_once(dirname(__FILE__).'/class.definition.php');

class Module_Newsletter extends Module {

	function useExtraMenu() {
		return true;
	}

	function loadExtraMenu() {
		loadAdminModuleLanguage($this->module_name);

	}

	function loadBody() {
		global $op, $modname, $prefix;
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
	}

}

?>
