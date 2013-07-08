<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package doceboLms
 * @subpackage reservation 
 * @author Marco Valloni
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Reservation extends LmsAdminModule {
		
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/reservation/reservation.php');
		reservationDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'),
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'),
		);
	}
}

?>