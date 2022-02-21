<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @version  $Id: class.room.php 113 2006-03-08 18:08:42Z ema $
 * @category Configuration
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Room extends ScsAdminModule {
	
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/'.$this->module_name.'/'.$this->module_name.'.php');
		roomDispatch($GLOBALS['op']);
	}	
	
	// Function for permission managment
	
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'],
			'mod' => ['code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png']
        ];
	}
}

?>
