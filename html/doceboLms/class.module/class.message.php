<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class Module_Message extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/message/message.php');
		messageDispatch($GLOBALS['op']);
	}
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			/*'send_upper' => array( 	'code' => 'send_upper',
								'name' => '_SEND_UPPER',
								'image' => 'message/send_upper.gif'), */
			'send_all' => array( 	'code' => 'send_all',
								'name' => '_SEND_ALL',
								'image' => 'message/send.gif')
		);
	}
}

?>