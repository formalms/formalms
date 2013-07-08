<?php defined("IN_DOCEBO") or die("You can't access this file directly");

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class Module_Tags extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/tags/tags.php');
		tags_dispatch($GLOBALS['op']);
	}
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'), 
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png')
		);
	}
	
}

?>