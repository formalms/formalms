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

class CoursepathLmsController extends LmsController
{
	public $name = 'coursepath';

	public $ustatus = array();
	public $cstatus = array();

	public $path_course = '';

	protected $model;

	public function isTabActive($tab_name) {
		return true;
	}

	public function init() {
		YuiLib::load('base,tabview');

		require_once(_lms_.'/lib/lib.course.php');
		require_once(_lms_.'/lib/lib.subscribe.php');
		require_once(_lms_.'/lib/lib.levels.php');

		$this->cstatus = array(
			CST_PREPARATION => '_CST_PREPARATION',
			CST_AVAILABLE 	=> '_CST_AVAILABLE',
			CST_EFFECTIVE 	=> '_CST_CONFIRMED',
			CST_CONCLUDED 	=> '_CST_CONCLUDED',
			CST_CANCELLED 	=> '_CST_CANCELLED',
		);

		$this->ustatus = array(
			_CUS_CONFIRMED 		=> '_T_USER_STATUS_CONFIRMED',
			_CUS_SUBSCRIBED 	=> '_T_USER_STATUS_SUBS',
			_CUS_BEGIN 			=> '_T_USER_STATUS_BEGIN',
			_CUS_END 			=> '_T_USER_STATUS_END'
		);
		
		$this->path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';

		$this->model = new CoursepathLms();
	}

	public function show()
	{
            
            $this->render('_tabs_block', array());      
	}

	public function all()
	{
        $filter_text = Get::req('filter_text', DOTY_STRING, "");
        $filter_year = Get::req('filter_year', DOTY_INT, "");
        $filter_status = Get::req('filter_status', DOTY_STRING, "");
		
		$conditions = '';
		if (!empty($filter_text)) {
			$conditions[] = "cp.path_name LIKE '%".addslashes($filter_text)."%'";
		}
        
        if ($filter_year != 0) {
            $conditions[] = "(cpu.date_assign >= '".$filter_year."-00-00 00:00:00' AND cpu.date_assign <= '".$filter_year."-12-31 23:59:59')";
        }    
		
		$user_coursepath = $this->model->getCoursepath(Docebo::user()->getIdSt(), $conditions, $filter_status);
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'type' => 'all',
												'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}

}