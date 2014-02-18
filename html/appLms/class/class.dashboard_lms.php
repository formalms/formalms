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

require_once($GLOBALS['where_framework'].'/class/class.dashboard.php');

class Dashboard_Lms extends Dashboard {
	
	function Dashboard_Lms() {
	
	}
	
	function getBoxContent() {
		
		$html = '';
		
		if(!checkPerm('view', true, 'course', 'lms')) return $html;
		
		require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');
		
		$course_man = new AdminCourseManagment();
		$course_stats = $course_man->getCoursesStats();
		
		$lang =& DoceboLanguage::createInstance('dashboard', 'framework');
		$html = array();
		$html[] = '<h2 class="inline">'.$lang->def('_COURSES_PANEL').'</h2>'
			.'<p>'
				.$lang->def('_TOTAL_COURSE').': <b>'.$course_stats['total'].'</b>;<br />'
				.$lang->def('_ACTIVE_COURSE').': <b>'.$course_stats['active'].'</b>;'
			.'</p><p>'
				.$lang->def('_ACTIVE_SEVEN_COURSE').': <b>'.$course_stats['active_seven'].'</b>;<br />'
				.$lang->def('_DEACTIVE_SEVEN_COURSE').': <b>'.$course_stats['deactive_seven'].'</b>;'
			.'</p><p>'
				.$lang->def('_TOTAL_SUBSCRIPTION').': <b>'.$course_stats['user_subscription'].'</b>;<br />'
				.( checkPerm('moderate', true, 'course', 'lms')
					? $lang->def('_WAITING_SUBSCRIPTION').': <b>'.$course_stats['user_waiting'].'</b>;'
					: '' )
			.'</p>';
		
		return $html;
	}
	
}

?>