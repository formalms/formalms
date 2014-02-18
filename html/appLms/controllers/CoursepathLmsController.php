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
		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$block_list = array();
		//if($ma->currentCanAccessObj('user_details_short')) $block_list['user_details_short'] = true;
		if($ma->currentCanAccessObj('user_details_full')) $block_list['user_details_full'] = true;
		if($ma->currentCanAccessObj('credits')) $block_list['credits'] = true;
		if($ma->currentCanAccessObj('news')) $block_list['news'] = true;

		if(!empty($block_list))
			$this->render('_tabs_block', array('block_list' => $block_list));
		else
			$this->render('_tabs', array());
	}

	public function all()
	{
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		
		$conditions = '';
		if (!empty($filter_text)) {
			$conditions = "AND cp.path_name LIKE '%".addslashes($filter_text)."%'";
		}
		
		$user_coursepath = $this->model->getAllCoursepath(Docebo::user()->getIdSt(), $conditions);
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'type' => 'all',
												'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}
	
	public function startPath()
	{
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		
		$conditions = '';
		if (!empty($filter_text)) {
			$conditions = "AND cp.path_name LIKE '%".addslashes($filter_text)."%'";
		}

		$user_coursepath = $this->model->getUserStartedCoursepath(Docebo::user()->getIdSt(), $conditions);
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'type' => 'itinere',
												'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}

	public function endPath()
	{
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		
		$conditions = '';
		if (!empty($filter_text)) {
			$conditions = "AND cp.path_name LIKE '%".addslashes($filter_text)."%'";
		}

		$user_coursepath = $this->model->getUserFinishedCoursepath(Docebo::user()->getIdSt(), $conditions);
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'type' => 'completed',
												'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}
}