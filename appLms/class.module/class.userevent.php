<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class Module_UserEvent extends LmsModule {
	
	function loadBody() {
		require_once($GLOBALS['where_framework'].'/modules/event_manager/event_manager.php');
		eventDispatch($GLOBALS['op']);
	}
	
	function useExtraMenu() {
		return false;
	}
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			'mod' => array( 	'code' => 'mod',
								'name' => '_SAVE',
								'image' => 'standard/edit.png')
		);
	}
	
}

?>
