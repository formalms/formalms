<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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
		
		$this->path_course = $GLOBALS['where_files_relative'].'/doceboLms/'.Get::sett('pathcourse').'/';

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

	public function startPath()
	{
		$user_coursepath = $this->model->getUserStartedCoursepath(Docebo::user()->getIdSt());
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}

	public function endPath()
	{
		$user_coursepath = $this->model->getUserFinishedCoursepath(Docebo::user()->getIdSt());
		$coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

		if(count($user_coursepath) > 0)
			$this->render('coursepath', array(	'user_coursepath' => $user_coursepath,
												'coursepath_courses' => $coursepath_courses));
		else
			echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
	}
}