<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * @package appLms
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