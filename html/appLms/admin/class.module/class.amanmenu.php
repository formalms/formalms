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
 * @package  DoceboLms
 * @version  $Id: class.manmenu.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course menu managment
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

require_once(dirname(__FILE__).'/class.definition.php');

class Module_AManmenu extends LmsAdminModule {
	
	function loadBody() {

		include(dirname(__FILE__).'/../modules/amanmenu/amanmenu.php');
		manmenuDispatch($GLOBALS['op']);
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view',
								'name' => '_VIEW',
								'image' => 'standard/view.png'), 
			'mod' => array( 	'code' => 'mod',
								'name' => '_MOD',
								'image' => 'standard/edit.png')
		);
	}
}

?>
