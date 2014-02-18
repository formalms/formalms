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

class Module_Stats extends LmsModule {
	
	function loadBody() {
		$GLOBALS['page']->setWorkingZone( 'page_head' );
		
		switch($GLOBALS['op']) {
			case "statuser" : {
				$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />'."\n" );
				//$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_scormplayer.css" rel="stylesheet" type="text/css" />'."\n" );
			};break;
			case "statitem":
			case "statcourse":
			case "statoneuser":
			case "statoneuseroneitem": {
				$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />'."\n" );
				$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/report/style_report_general.css" rel="stylesheet" type="text/css" />'."\n" );
				//$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_organizations.css" rel="stylesheet" type="text/css" />'."\n" );				
				//$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_scormplayer.css" rel="stylesheet" type="text/css" />'."\n" );
			}
		}		
		require(_lms_.'/modules/stats/stats.php');
	}
	
	function getAllToken($op) {
		
		if($op == 'statuser') {
			return array( 'view_user' => array( 	'code' => 'view_user',
								'name' => '_VIEW',
								'image' => 'standard/view.png') );
		} else {
			
			return array( 'view_course' => array( 	'code' => 'view_course',
								'name' => '_VIEW',
								'image' => 'standard/view.png') );
		}
		
	}

	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, 'view_user,view_course'),
			2 => $this->selectPerm($op, 'view_user,view_course'),
			3 => $this->selectPerm($op, 'view_user,view_course'),
			4 => $this->selectPerm($op, 'view_user,view_course'),
			5 => $this->selectPerm($op, 'view_user,view_course'),
			6 => $this->selectPerm($op, 'view_user,view_course'),
			7 => $this->selectPerm($op, 'view_user,view_course')
		);
	}
	
}



?>
