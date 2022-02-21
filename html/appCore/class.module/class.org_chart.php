<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package admin-core
 * @subpackage user
 */
 
require_once(dirname(__FILE__).'/class.definition.php');

class Module_Org_chart extends Module {
	
	function loadBody() {
		
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		orgDispatch( $GLOBALS['op'], false, $this );
	}
	
}

?>
