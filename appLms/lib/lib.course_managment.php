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
 * @package DoceboLms
 * @subpackage Course managment
 * @author Fabio Pirovano <fabio [at] docebo-com>
 * @version  $Id: lib.course_managment.php 573 2006-08-23 09:38:54Z fabio $
 */

 /**
  * This class is mindede to be userd for course managment and include the function for load a course 
  * selector that includes 'course', 'coursepath' and 'catalogue' 
  * Other function is the copy of a course menu and the retriving of info about it
  */
class Course_Manager {
	
	var $ref_link = '';
	
	var $show_filter = true;
	
	var $tab = NULL;
	
	/* Selector instances */
	var $course_selector = NULL;
	
	var $coursepath_selector = NULL;
	
	var $catalogue_selector = NULL;
	
	/* What to show */
	var $show_course_selector = true;
	
	var $show_coursepath_selector = true;
	
	var $show_catalogue_selector = true;
	
	/* Save Status */
	var $status_course_selector = NULL;
	
	var $status_coursepath_selector = NULL;
	
	var $status_catalogue_selector = NULL;
	
	/**
	 * Class constructor
	 */
	function Course_Manager() {
		
		require_once(_base_.'/lib/lib.tab.php');
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
		
		$this->show_filter 			= true;
		$this->course_selector 		= new Selector_Course();
		$this->coursepath_selector 	= new Selector_CoursePath();
		$this->catalogue_selector 	= new Selector_Catalogue();
	}
	
	function setLink($link) {
		
		$this->ref_link = $link;
	}
	
	function resetCourseSelection($new_selection) {
		
		$this->course_selector->resetSelection($new_selection);
	}
	
	function resetCoursePathSelection($new_selection) {
		
		$this->coursepath_selector->resetSelection($new_selection);
	}
	
	function resetCatalogueSelection($new_selection) {
		
		$this->catalogue_selector->resetSelection($new_selection);
	}
	
	function getCourseSelection($array_state) {
		
		$old_tab = importVar('old_tab');
		if($old_tab == 'tab_course') {
			$this->course_selector->parseForState($_POST);
		} else {
			$this->status_course_selector = urldecode(importVar('sel_course_selected'));
			$this->course_selector->loadStatus( $this->status_course_selector );
		}
		return $this->course_selector->getSelection();
	}
	
	function loadCourseSelector($noprint = false) {
		
		return $this->course_selector->loadCourseSelector($noprint);
	}
	
	function getCoursePathSelection($array_state) {
		
		$old_tab = importVar('old_tab');
		if($old_tab == 'tab_coursepath') {
			$this->coursepath_selector->parseForState($_POST);
		} else {
			$this->status_coursepath_selector = urldecode(importVar('sel_coursepath_selected'));
			$this->coursepath_selector->loadStatus( $this->status_coursepath_selector );
		}
		return $this->coursepath_selector->getSelection();
	}
	
	function loadCoursePathSelector($noprint = false) {
		
		return $this->coursepath_selector->loadCoursePathSelector($noprint);
	}
	
	function getCatalogueSelection($array_state) {
		
		$old_tab = importVar('old_tab');
		if($old_tab == 'tab_catalogue') {
			$this->catalogue_selector->parseForState($_POST);
		} else {
			$this->status_catalogue_selector = urldecode(importVar('sel_catalogue_selected'));
			$this->catalogue_selector->loadStatus( $this->status_catalogue_selector );
		}
		return $this->catalogue_selector->getSelection();
	}
	
	function loadCatalogueSelector($noprint = false) {
		
		return $this->catalogue_selector->loadCatalogueSelector($noprint);
	}
	
	function _loadCourseStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_course';
		$my_var = 'sel_course_selected';
		$out = '';
		
		if($old_tab != $my_tab && $new_tab == $my_tab) {
			
			if(isset($_POST[$my_var])) {
				$this->status_course_selector = urldecode(importVar($my_var));
				$this->course_selector->loadStatus( $this->status_course_selector );
			} else $this->course_selector->parseForState($_POST);
		} else {
			$this->course_selector->parseForState($_POST);
		}
	}
	
	function _loadCoursePathStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_coursepath';
		$my_var = 'sel_coursepath_selected';
		$out = '';
		
		if($old_tab != $my_tab && $new_tab == $my_tab) {
			
			if(isset($_POST[$my_var])) {
				$this->status_coursepath_selector = urldecode(importVar($my_var));
				$this->coursepath_selector->loadStatus( $this->status_coursepath_selector );
			} else $this->coursepath_selector->parseForState($_POST);
		} else {
			$this->coursepath_selector->parseForState($_POST);
		}
	}
	
	function _loadCatalogueStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_catalogue';
		$my_var = 'sel_catalogue_selected';
		$out = '';
		
		if($old_tab != $my_tab && $new_tab == $my_tab) {
			
			if(isset($_POST[$my_var])) {
				$this->status_catalogue_selector = urldecode(importVar($my_var));
				$this->catalogue_selector->loadStatus( $this->status_catalogue_selector );
			} else $this->catalogue_selector->parseForState($_POST);
		} else {
			$this->catalogue_selector->parseForState($_POST);
		}
	}
	
	function _saveCourseStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_course';
		$my_var = 'sel_course_selected';
		$out = '';
		if($this->show_course_selector !== false) {
			
			if($old_tab == $my_tab && $new_tab != $my_tab) {
				$this->status_course_selector = urlencode($this->course_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_course_selector);
			} elseif(isset($_POST[$my_var])) {
				$out = Form::getHidden($my_var, $my_var, $_POST[$my_var]);
			} elseif($old_tab == '') {
				$this->status_course_selector = urlencode($this->course_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_course_selector);
			}
		}
		return $out."\n";
	}
	
	function _saveCoursePathStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_coursepath';
		$my_var = 'sel_coursepath_selected';
		$out = '';
		if($this->show_coursepath_selector !== false) {
			
			if($old_tab == $my_tab && $new_tab != $my_tab) {
				$this->status_coursepath_selector = urlencode($this->coursepath_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_coursepath_selector);
			} elseif(isset($_POST[$my_var])) {
				
				$out = Form::getHidden($my_var, $my_var, $_POST[$my_var]);
			} elseif($old_tab == '') {
				$this->status_coursepath_selector = urlencode($this->coursepath_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_coursepath_selector);
			}
		}
		return $out."\n";
	}
	
	function _saveCatalogueStatus($new_tab, $old_tab) {
		
		$my_tab = 'tab_catalogue';
		$my_var = 'sel_catalogue_selected';
		$out = '';
		if($this->show_catalogue_selector !== false) {
			
			if($old_tab == $my_tab && $new_tab != $my_tab) {
				$this->status_catalogue_selector = urlencode($this->catalogue_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_catalogue_selector);
			} elseif(isset($_POST[$my_var])) {
				$out = Form::getHidden($my_var, $my_var, $_POST[$my_var]);
			} elseif($old_tab == '') {
				$this->status_catalogue_selector = urlencode($this->catalogue_selector->getStatus());
				$out = Form::getHidden($my_var, $my_var, $this->status_catalogue_selector);
			}
		}
		return $out."\n";
	}
	
	function loadSelector($show_tabs = true, $noprint = false) {
		
		$this->tab = new TabView('course_management', $this->ref_link);
		$lang =& DoceboLanguage::createInstance('course_selector', 'lms');
		
		// overwrite show status looking for permission
		if(!checkPerm('view', true, 'course' ,'lms')) 		$this->show_course_selector 		= false;
		if(!checkPerm('view', true, 'coursepath' ,'lms')) 	$this->show_coursepath_selector 	= false;
		if(!checkPerm('view', true, 'catalogue' ,'lms')) 	$this->show_catalogue_selector		= false;
		
		// previous tab
		$old_tab 		= importVar('old_tab');
		$active_tab 	= importVar('old_tab', false, 'tab_course');
		// now display tab
		if(isset($_POST['tabelem_tab_course_status'])) 			$active_tab = 'tab_course';
		elseif(isset($_POST['tabelem_tab_coursepath_status'])) 	$active_tab = 'tab_coursepath';
		elseif(isset($_POST['tabelem_tab_catalogue_status'])) 	$active_tab = 'tab_catalogue';
		
		// istance selector
		if($show_tabs)
		{
			if($this->show_course_selector !== false) {
				$course_tab 		= new TabElemDefault(	'tab_course', 
															$lang->def('_SEL_COURSE'), 
															getPathImage('lms').'area_title/course.gif');
				$this->tab->addTab($course_tab);
			}
			if($this->show_coursepath_selector !== false) {
				
				$coursepath_tab 	= new TabElemDefault(	'tab_coursepath', 
															$lang->def('_COURSEPATH'), 
															getPathImage('lms').'area_title/coursepath.gif');
				$this->tab->addTab($coursepath_tab);
			}
			if($this->show_catalogue_selector !== false) {
				$catalogue_tab 		= new TabElemDefault(	'tab_catalogue', 
															$lang->def('_CATALOGUE'), 
															getPathImage('lms').'area_title/catalogue.gif');
				
				$this->tab->addTab($catalogue_tab);
			}
		}
		$this->tab->setActiveTab($active_tab);
		
		$output = $this->tab->printTabView_Begin('', false);
		
		if($old_tab != $active_tab) {
			switch($old_tab) {
				case "tab_course": {
					$this->course_selector->parseForState($_POST);
				};break;
				case "tab_coursepath": {
					$this->coursepath_selector->parseForState($_POST);
				};break;
				case "tab_catalogue": {
					$this->catalogue_selector->parseForState($_POST);
				};break;
			}
		}
		switch($active_tab) {
			case "tab_course": {
				$this->_loadCourseStatus($active_tab, $old_tab);
				$output .= $this->loadCourseSelector($noprint);
			};break;
			case "tab_coursepath": {
				$this->_loadCoursePathStatus($active_tab, $old_tab);
				$output .= $this->loadCoursePathSelector($noprint);
			};break;
			case "tab_catalogue": {
				$this->_loadCatalogueStatus($active_tab, $old_tab);
				$output .= $this->loadCatalogueSelector($noprint);
			};break;
		}
		$output .= $this->_saveCourseStatus($active_tab, $old_tab)
			.$this->_saveCoursePathStatus($active_tab, $old_tab)
			.$this->_saveCatalogueStatus($active_tab, $old_tab)
			.Form::getHidden('old_tab', 'old_tab', $active_tab);

		$output .= $this->tab->printTabView_End();

		if($noprint) return $output; else cout($output, 'content');
	}
}


class AdminCourseManagment {
	
	var $course 		= array();
	var $coursepath 	= array();
	var $catalogues 	= array();
	
	function Admin_Course() {}
	
	function _executeQuery($text_query) {
		
		$rs = sql_query($text_query);
		return $rs;
	}
	
	function &getUserCourses($id_user) {
		
		$courses = array();
		$query_course = "
		SELECT id_entry 
		FROM ".$GLOBALS['prefix_fw']."_admin_course 
		WHERE type_of_entry = 'course' AND idst_user = '".$id_user."'";
		$re_course = $this->_executeQuery($query_course);
		while(list($id) = sql_fetch_row($re_course)) {
			
			$courses[$id] = $id;
		}
		return $courses;
	}
	
	function &getUserPathCourses($id_user) {
		
		$coursepaths = array();
		$query_coursepath = "
		SELECT id_entry 
		FROM ".$GLOBALS['prefix_fw']."_admin_course 
		WHERE type_of_entry = 'coursepath' AND idst_user = '".$id_user."'";
		$re_coursepath = $this->_executeQuery($query_coursepath);
		while(list($id) = sql_fetch_row($re_coursepath)) {
			
			$coursepaths[$id] = $id;
		}
		return $coursepaths;
	}
	
	function &getUserCatalogues($id_user) {
		
		$catalogues = array();
		$query_catalogue = "
		SELECT id_entry 
		FROM ".$GLOBALS['prefix_fw']."_admin_course 
		WHERE type_of_entry = 'catalogue' AND idst_user = '".$id_user."'";
		$re_catalogue = $this->_executeQuery($query_catalogue);
		while(list($id) = sql_fetch_row($re_catalogue)) {
			
			$catalogues[$id] = $id;
		}
		return $catalogues;
	}
	
	function &getUserAllCourses($id_user) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		
		$courses 		= $this->getUserCourses($id_user);
		$coursepath 	= $this->getUserPathCourses($id_user);
		$catalogues 	= $this->getUserCatalogues($id_user);
		
		$man_cataloge 				= new AdminCatalogue();
		$course_from_catalogue 		= $man_cataloge->getAllCourses($catalogues);
		$coursepath_from_catalogue 	= $man_cataloge->getAllCoursePaths($catalogues);
		$coursepath = array_merge($coursepath, $coursepath_from_catalogue);
		
		$man_coursepath 				= new CoursePath_Manager();
		$course_from_coursepath 		= $man_coursepath->getAllCourses($coursepath);
		
		$courses = array_merge($courses, $course_from_catalogue, $course_from_coursepath);
		return $courses;
	}
	
	function &getUserAllCoursePaths($id_user) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		
		$coursepath 	=& $this->getUserPathCourses($id_user);
		$catalogues 	=& $this->getUserCatalogues($id_user);
		
		$man_cataloge 				= new AdminCatalogue();
		$coursepath_from_catalogue 	=& $man_cataloge->getAllCoursePaths($catalogues);
		$coursepath = array_merge($coursepath, $coursepath_from_catalogue);
		return $coursepath;
	}
	
	function getCoursesStats($manual_filter = false) {

		$course_stats = array();
		$course_stats['total'] = 0;
		$course_stats['active'] = 0;
		$course_stats['active_seven'] = 0;
		$course_stats['deactive_seven'] = 0;
		$course_stats['user_subscription'] = 0;
		$course_stats['user_waiting'] = 0;
		
		$query_course =" SELECT COUNT(*) FROM %lms_course ";
		$where = " WHERE 1 ";

		if ($manual_filter !== false) {
			if (!empty($manual_filter)) {
				$where .= " AND idCourse IN ( ".implode(',', $manual_filter)." )";
			} else {
				return $course_stats;
			}
		} else {
			//automatic filter, based on current user
			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

				$course_array =& $this->getUserAllCourses( getLogUserId() );
				if(empty($course_array)) {

					if(Get::sett('on_catalogue_empty') == 'on') return $course_stats;
				} else {
					$where .= " AND idCourse IN ( ".implode(',', $course_array)." )";
				}
			}
		}
		
		list($course_stats['total']) = sql_fetch_row(sql_query($query_course.$where));
		list($course_stats['active']) = sql_fetch_row(sql_query($query_course
			.$where." AND status = '1'"));
		
		list($course_stats['active_seven']) = sql_fetch_row(sql_query($query_course
			.$where." AND date_begin > '".date("Y-m-d H:i:s")."' AND date_begin < '".date("Y-m-d", time() + 7*24*3600)." 23:59:59'"));
		list($course_stats['deactive_seven']) = sql_fetch_row(sql_query($query_course
			.$where." AND date_end > '".date("Y-m-d H:i:s")."' AND date_end < '".date("Y-m-d", time() + 7*24*3600)." 23:59:59'"));
		
		$query_subscribe = "SELECT `waiting`, COUNT(*) FROM %lms_courseuser ";
		$re_query = sql_query($query_subscribe
			.$where
			." GROUP BY `waiting`");
		
		while(list($wait_stat, $number) = sql_fetch_row($re_query)) {;
			if($wait_stat == 0) $course_stats['user_subscription'] = $number;
			else $course_stats['user_waiting'] = $number;
		}
		
		return $course_stats;
	}
}

?>