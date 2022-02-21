<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package  DoceboLms
 * @version  $Id: class.catalogue.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course managment
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_MiddleArea extends LmsAdminModule {
	
	function loadBody() {
		
		require_once(Forma::inc(_lms_ . '/admin/modules/middlearea/middlearea.php'));
		MiddleAreaDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	
	function getAllToken($op) {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
}

?>