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
 * @package admin-core
 * @subpackage event
 * @version  $Id: class.event_manager.php 220 2006-04-09 14:55:58Z fabio $
 * @author   Emanuele Sandri <esandri@docebo.com>
 */

require_once(_base_.'/lib/lib.event.php' );
require_once($GLOBALS['where_framework'].'/class.module/class.definition.php');

class Module_Event_Manager extends Module {
	
	function useExtraMenu() {
		return true;
	}
	
	function loadExtraMenu() {
		loadAdminModuleLanguage($this->module_name);
	}

	function loadBody() {
		require_once($GLOBALS['where_framework'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		eventDispatch( $GLOBALS['op'] );
	}
	
	// Function for permission managment
	function getAllToken($op) {
		return array( 
			'view' => array( 	'code' => 'view_event_manager',
								'name' => '_VIEW',
								'image' => 'standard/view.png')
					);
		$op = $op;
	}

}

?>
