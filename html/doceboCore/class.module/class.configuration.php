<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package admin-core
 * @subpackage configuration
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Configuration extends Module {
	
	function loadBody() {
		
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		configurationDispatch( $GLOBALS['op'] );
	}
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png')
		);
	}
}

?>
