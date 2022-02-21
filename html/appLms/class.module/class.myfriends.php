<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.1.0
 */

class Module_MyFriends extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		myfriendsDispatch($GLOBALS['op']);
	}
	
	function getAllToken() {
		return [
			'view' => ['code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png']
        ];
	}
	
}

?>