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

class Module_Test extends LmsModule {
	
	function hideLateralMenu() {
		
		if(isset($_SESSION['test_assessment'])) return true;
		if(isset($_SESSION['direct_play'])) return true;
		return false;
	}
	
	function loadBody() {
		//EFFECTS: include module language and module main file
		
		switch($GLOBALS['op']) {
			case "play" : {
				$idTest = importVar('id_test', true, 0);
				$id_param = importVar('id_param', true, 0);
				$back_url = importVar('back_url');
				$test_type = importVar('test_type', false, 'test');
				
				$object_poll = createLO( $test_type, $idTest );
				$object_poll->play( $idTest, $id_param, Util::unserialize(urldecode($back_url)) );
			};break;
			default : {
				parent::loadBody();
			}
		}
	}
}

?>