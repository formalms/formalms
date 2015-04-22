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

class ElearningLmsController extends LmsController {

	public $name = 'elearning';

	public $ustatus = array();
	public $cstatus = array();

	public $levels = array();

	public $path_course = '';

	protected $_default_action = 'show';

	public $info = array();

	public function isTabActive($tab_name) {

		switch($tab_name) {
			case "new" : {
				if(!isset($this->info['elearning'][0])) return false;
			};break;
			case "inprogress" : {
				if(!isset($this->info['elearning'][1])) return false;
			};break;
			case "completed" : {
				if(!isset($this->info['elearning'][2])) return false;
			};break;
		}
		return true;
	}

	public function init() {

		YuiLib::load('base,tabview');

		if(!isset($_SESSION['id_common_label']))
			$_SESSION['id_common_label'] = -1;

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
			_CUS_WAITING_LIST 	=> '_WAITING_USERS',
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

	public function fieldsTask() {
		$level = Docebo::user()->getUserLevelId();
		if (Get::sett('request_mandatory_fields_compilation', 'off') == 'on' && $level != ADMIN_GROUP_GODADMIN) {
			require_once(_adm_.'/lib/lib.field.php');
			$fl = new FieldList();
			$idst_user = Docebo::user()->getIdSt();
			$res = $fl->storeFieldsForUser($idst_user);
		}
		Util::jump_to('index.php?r=elearning/show');
	}

	public function showTask() {

		$model = new ElearningLms();

		if(Get::sett('on_usercourse_empty') === 'on')
		{
			$conditions_t = array(
				'cu.iduser = :id_user'
			);

			$params_t = array(
				':id_user' => (int)Docebo::user()->getId()
			);

			$cp_courses = $model->getUserCoursePathCourses(Docebo::user()->getIdst());
			if (!empty($cp_courses))
			{
				$conditions_t[] = "cu.idCourse NOT IN (".implode(",", $cp_courses).")";
			}

			$courselist_t = $model->findAll($conditions_t, $params_t);

			if(empty($courselist_t))
				Util::jump_to('index.php?r=lms/catalog/show&sop=unregistercourse');
		}

		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$block_list = array();
		//if($ma->currentCanAccessObj('user_details_short')) $block_list['user_details_short'] = true;
		if($ma->currentCanAccessObj('user_details_full')) $block_list['user_details_full'] = true;
		if($ma->currentCanAccessObj('credits')) $block_list['credits'] = true;
		if($ma->currentCanAccessObj('news')) $block_list['news'] = true;
		$tb_label = $ma->currentCanAccessObj('tb_label');
		if(!$tb_label)
			$_SESSION['id_common_label'] = 0;
		else
		{
			$id_common_label = Get::req('id_common_label', DOTY_INT, -1);

			if($id_common_label >= 0)
				$_SESSION['id_common_label'] = $id_common_label;
			elseif($id_common_label == -2)
				$_SESSION['id_common_label'] = -1;

			$block_list['labels'] = true;
		}

		if($tb_label && $_SESSION['id_common_label'] == -1)
		{
			require_once(_lms_.'/admin/models/LabelAlms.php');
			$label_model = new LabelAlms();

			$user_label = $label_model->getLabelForUser(Docebo::user()->getId());

			$this->render('_labels',array(	'block_list' => $block_list,
											'label' => $user_label));
		}
		else
		{
			if(!empty($block_list))
				$this->render('_tabs_block', array('block_list' => $block_list));
			else
				$this->render('_tabs', array());
		}
		
		// add feedback:
		// - feedback_type: [err|inf] display error feedback or info feedback
		// - feedback_code: translation code of message
		// - feedback_extra: extrainfo concat at end message
		$feedback_code=Get::req('feedback_code', DOTY_STRING, "");
		$feedback_type=Get::req('feedback_type', DOTY_STRING, "");
		$feedback_extra=Get::req('feedback_extra', DOTY_STRING, "");
		switch($feedback_type){
			case "err":
				$msg = Lang::t($feedback_code, 'login')." ".$feedback_extra;
				UIFeedback::error($msg);
				break;
			case "inf":
				$msg = Lang::t($feedback_code, 'login')." ".$feedback_extra;
				UIFeedback::info($msg);
				break;
		}		
	}

	public function newTask() {
		$model = new ElearningLms();

		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_year = Get::req('filter_year', DOTY_INT, 0);

		$conditions = array(
			'cu.iduser = :id_user',
			'cu.status = :status'
		);

		$params = array(
			':id_user' => (int)Docebo::user()->getId(),
			':status' => _CUS_SUBSCRIBED
		);

		if (!empty($filter_text)) {
			$conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
			$params[':keyword'] = $filter_text;
		}

		if (!empty($filter_year)) {
			$conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
			$params[':year'] = $filter_year;
		}

		$courselist = $model->findAll($conditions, $params);

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}

		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist,
			'use_label' => $ma->currentCanAccessObj('tb_label'),
			'keyword' => $filter_text
		));
	}




	public function inprogress() {
		$model = new ElearningLms();

		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_year = Get::req('filter_year', DOTY_INT, 0);

		$conditions = array(
			'cu.iduser = :id_user',
			'cu.status = :status'
		);

		$params = array(
			':id_user' => (int)Docebo::user()->getId(),
			':status' => _CUS_BEGIN
		);

		if (!empty($filter_text)) {
			$conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
			$params[':keyword'] = $filter_year;
		}

		if (!empty($filter_year)) {
			$conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
			$params[':year'] = $filter_text;
		}

		$courselist = $model->findAll($conditions, $params);

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}
		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist,
			'use_label' => $ma->currentCanAccessObj('tb_label'),
			'keyword' => $filter_text
		));
	}




	public function completed() {
		$model = new ElearningLms();

		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_year = Get::req('filter_year', DOTY_INT, 0);

		$conditions = array(
			'cu.iduser = :id_user',
			'cu.status = :status'
		);

		$params = array(
			':id_user' => (int)Docebo::user()->getId(),
			':status' => _CUS_END
		);

		if (!empty($filter_text)) {
			$conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
			$params[':keyword'] = $filter_text;
		}

		if (!empty($filter_year)) {
			$conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
			$params[':year'] = $filter_year;
		}

		$courselist = $model->findAll($conditions, $params);

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}
		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist,
			'use_label' => $ma->currentCanAccessObj('tb_label'),
			'keyword' => $filter_text
		));
	}




	public function allTask() {
		$model = new ElearningLms();

		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_year = Get::req('filter_year', DOTY_INT, 0);

		$conditions = array(
			'cu.iduser = :id_user',
			'cu.status <> :status'
		);

		$params = array(
			':id_user' => (int)Docebo::user()->getId(),
			':status' => _CUS_END
		);

		if (!empty($filter_text)) {
			$conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
			$params[':keyword'] = $filter_text;
		}

		if (!empty($filter_year)) {
			$conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
			$params[':year'] = $filter_year;
		}

//		$cp_courses = $model->getUserCoursePathCourses( Docebo::user()->getIdst() );
//		if (!empty($cp_courses)) {
//			$conditions[] = "cu.idCourse NOT IN (".implode(",", $cp_courses).")";
//		}

		$courselist = $model->findAll($conditions, $params);

		//check courses accessibility
		$keys = array_keys($courselist);
		for ($i=0; $i<count($keys); $i++) {
			$courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
		}

		require_once(_lms_.'/lib/lib.middlearea.php');
		$ma = new Man_MiddleArea();
		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist,
			'use_label' => $ma->currentCanAccessObj('tb_label'),
			'keyword' => $filter_text
		));
	}

	/**
	 * This implies the skill gap analysis :| well, a first implementation will be done based on
	 * required over acquired skill and proposing courses that will give, the required competences.
	 * If this implementation will require too much time i will wait for more information and pospone the implementation
	 */
	public function suggested() {

		$competence_needed = Docebo::user()->requiredCompetences();

		$model = new ElearningLms();
		$courselist = $model->findAll(array(
			'cu.iduser = :id_user',
			'comp.id_competence IN (:competence_list)'
		), array(
			':id_user' => Docebo::user()->getId(),
			':competence_list' => $competence_needed
		), array('LEFT JOIN %lms_competence AS comp ON ( .... ) '));

		$this->render('courselist', array(
			'path_course' => $this->path_course,
			'courselist' => $courselist
		));
	}

	/**
	 * The action of self-unsubscription from a course (if enabled for the course),
	 * available in the course box of the courses list
	 */
	public function self_unsubscribe() {
		$id_user = Docebo::user()->idst;//Get::req('id_user', DOTY_INT, Docebo::user()->idst);
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		$cmodel = new CourseAlms();
		$cinfo = $cmodel->getCourseModDetails($id_course);

		//index.php?r=elearning/show
		$back = Get::req('back', DOTY_STRING, "");
		if ($back != "") {
			$parts = explode('/', $back);
			$length = count($parts);
			if ($length > 0) {
				$parts[$length -1] = 'show';
				$back = implode('/', $parts);
			}
		}
		$jump_url = 'index.php?r='.($back ? $back : 'lms/elearning/show');

		if ($cinfo['auto_unsubscribe'] == 0) {
			//no self unsubscribe possible for this course
			Util::jump_to($jump_url.'&res=err_unsub');
		}

		$date_ok = TRUE;
		if ($cinfo['unsubscribe_date_limit'] != "" && $cinfo['unsubscribe_date_limit'] != "0000-00-00 00:00:00") {
			if ($cinfo['unsubscribe_date_limit'] < date("Y-m-d H:i:s")) {
				//self unsubscribing is no more allowed, go back to courselist page
				Util::jump_to($jump_url.'&res=err_unsub');
			}
		}

		$smodel = new SubscriptionAlms();
		$param = '';

		if ($cinfo['auto_unsubscribe'] == 1) {
			//moderated self unsubscribe
			$res = $smodel->setUnsubscribeRequest($id_user, $id_course, $id_edition, $id_date);
			$param .= $res ? '&res=ok_unsub' : '&res=err_unsub';
		}

		if ($cinfo['auto_unsubscribe'] == 2) {
			//directly unsubscribe user
			$res = $smodel->unsubscribeUser($id_user, $id_course, $id_edition, $id_date);
			$param .= $res ? '&res=ok_unsub' : '&res=err_unsub';
		}

		Util::jump_to($jump_url);
	}

}
