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

class Module_Course extends LmsModule {
	
	function beforeLoad() {
		switch($GLOBALS['op']) {
			case "mycourses" : 
			case "unregistercourse" : {
				if (isset($_SESSION['idCourse'])) {
				
					TrackUser::closeSessionCourseTrack();
					unset($_SESSION['idCourse']);
					unset($_SESSION['idEdition']);
				}
				if(isset($_SESSION['cp_assessment_effect'])) unset($_SESSION['cp_assessment_effect']);
			}
		}	
	}
	
	function loadBody() {
		
		switch($GLOBALS['op']) {
			case 'showresults': {
				$id_course = Get::req('id_course', DOTY_INT, false);
				$_SESSION['idCourse'] = $id_course;
				Util::jump_to('index.php?modname=organization&op=showresults&idcourse='.$id_course);
			};break;
			case "mycourses" : 
			case "unregistercourse" : {
				
				require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/course.php');
				
				require_once(_base_.'/lib/lib.urlmanager.php');
				$url =& UrlManager::getInstance('course');
				$url->setStdQuery('r='._lms_home_);
				
				mycourses($url);
			};break;
			case "donwloadmaterials":
				downloadMaterials();
			break;
			default: {
				
				require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/infocourse.php');
				infocourseDispatch($GLOBALS['op']);
			};break;
		}
	}
	
	function getAllToken($op) {
		
		switch($op) {
			case "infocourse" : {
				
				return array( 
					'view' => array( 	'code' => 'view_info',
										'name' => '_VIEW',
										'image' => 'standard/view.png'), 
					'mod' => array( 	'code' => 'mod',
										'name' => '_MOD',
										'image' => 'standard/edit.png')
				);
			};break;
			default : {
				
				return array( 
					'view' => array( 	'code' => 'view',
										'name' => '_VIEW',
										'image' => 'standard/view.png')
				);
			} 
		}
	}


	function getPermissionsForMenu($op) {
		return array(
			1 => $this->selectPerm($op, 'view'),
			2 => $this->selectPerm($op, 'view'),
			3 => $this->selectPerm($op, 'view'),
			4 => $this->selectPerm($op, 'view'),
			5 => $this->selectPerm($op, 'view,mod'),
			6 => $this->selectPerm($op, 'view,mod'),
			7 => $this->selectPerm($op, 'view,mod')
		);
	}

}

?>