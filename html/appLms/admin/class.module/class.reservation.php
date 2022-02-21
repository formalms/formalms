<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png'],
        ];
	}
}

?>