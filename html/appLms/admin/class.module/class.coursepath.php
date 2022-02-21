<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @version  $Id: class.coursepath.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course managment
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Coursepath extends LmsAdminModule {
	
	
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/'.$this->module_name.'/'.$this->module_name.'.php');
		coursepathDispatch($GLOBALS['op']);
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
			'subscribe' => ['code' => 'subscribe',
								'name' => '_SUBSCRIBE',
								'image' => 'subscribe/add_subscribe.gif'],
			'moderate' => ['code' => 'moderate',
								'name' => '_MODERATE',
								'image' => 'standard/moderate.gif'],
        ];
	}
}

?>