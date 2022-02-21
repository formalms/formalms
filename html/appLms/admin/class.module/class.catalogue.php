<?php defined("IN_FORMA") or die('Direct access is forbidden.');



/**
 * @package  DoceboLms
 * @version  $Id: class.catalogue.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course managment
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_Catalogue extends LmsAdminModule {
	
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/catalogue/catalogue.php');
		catalogueDispatch($GLOBALS['op']);
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
			'subscribe' => ['code' => 'associate',
								'name' => '_ASSIGN_USERS',
								'image' => 'standard/groups.gif']
        ];
	}
}

?>
