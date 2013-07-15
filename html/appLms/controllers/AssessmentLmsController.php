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

class AssessmentLmsController extends LmsController {

	public $name = 'assessment';

	protected $_default_action = 'show';

	public $ustatus = array();

	public $cstatus = array();

	public $levels = array();

	public $path_course = '';

	public $info = array();

	public function isTabActive($tab_name) {

		switch($tab_name) {
			case "new" : {
				if(!isset($this->info['assessment'][0]) && !isset($this->info['assessment'][1])) return false;
			};break;
			case "completed" : {
				if(!isset($this->info['assessment'][2])) return false;
			};break;
		}
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
			//_CUS_RESERVED 		=> '_T_USER_STATUS_RESERVED',
			//_CUS_WAITING_LIST 	=> '_T_USER_STATUS_WLIST',
			_CUS_CONFIRMED 		=> '_T_USER_STATUS_CONFIRMED',

			_CUS_SUBSCRIBED 	=> '_T_USER_STATUS_SUBS',
			_CUS_BEGIN 			=> '_T_USER_STATUS_BEGIN',
			_CUS_END 			=> '_T_USER_STATUS_END'
		);
		$this->levels = CourseLevel::getLevels();
		$this->path_course = $GLOBALS['where_files_relative'].'/appLms/'.Get::sett('pathcourse').'/';
		
		$upd = new UpdatesLms();
		$this->info = $upd->courseUpdates();
	}

	public function showTask() {

		$this->render('_tabs', array());
	}

	public function newTask() {

		$model = new AssessmentLms();
		$courselist = $model->findAll(array(
			'cu.iduser = :id_user',
			'c.course_type = ":course_type"'
		), array(
			':id_user' => Docebo::user()->getId(),
			':course_type' => 'assessment'
		));

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist
		));
	}

	public function completedTask() {

		$model = new AssessmentLms();
		$courselist = $model->findAll(array(
			'cu.iduser = :id_user',
			'cu.status = :status',
			'c.course_type = ":course_type"'
		), array(
			':id_user' => Docebo::user()->getId(),
			':status' => _CUS_END,
			':course_type' => 'assessment'
		));

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist
		));
	}

}
