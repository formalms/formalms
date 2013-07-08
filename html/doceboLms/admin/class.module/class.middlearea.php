<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package  DoceboLms
 * @version  $Id: class.catalogue.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course managment
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_MiddleArea extends LmsAdminModule {
	
	function loadBody() {
		
		require_once(dirname(__FILE__).'/../modules/middlearea/middlearea.php');
		MiddleAreaDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png')
		);
	}
}

?>