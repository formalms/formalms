<?php

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

class EditionAlmsController extends AlmsController {
	public $name = 'classroom';

	protected $json;
	protected $acl_man;

	protected $data;

	protected $permissions;

	protected $base_link_course;
	protected $base_link_edition;
	protected $base_link_subscription;

	public function init()
	{
		checkPerm('view', false, 'course', 'lms');
		require_once(_base_.'/lib/lib.json.php');

		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();

		$this->base_link_course = 'alms/course';
		$this->base_link_edition = 'alms/edition';
		$this->base_link_subscription = 'alms/subscription';

		$this->permissions = array(
			'view'			=> checkPerm('view', true, 'course', 'lms'),
			'add'				=> checkPerm('add', true, 'course', 'lms'),
			'mod'				=> checkPerm('mod', true, 'course', 'lms'),
			'del'				=> checkPerm('del', true, 'course', 'lms'),
			'moderate'	=> checkPerm('moderate', true, 'course', 'lms'),
			'subscribe'	=> checkPerm('subscribe', true, 'course', 'lms')
		);
	}

	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
		}
		return $message;
	}
        
	protected function _getBackLink() {
		if ($this->id_edition != 0) {
			return getBackUi('index.php?r='.$this->base_link_edition.'/show&id_course=' . $this->id_course, Lang::t('_BACK', 'standard'));
		} elseif ($this->id_date != 0) {
			return getBackUi('index.php?r='.$this->base_link_classroom.'/classroom&id_course=' . $this->id_course, Lang::t('_BACK', 'standard'));
		} else {
			return getBackUi('index.php?r='.$this->base_link_course.'/show', Lang::t('_BACK', 'standard'));
		}
	}

	protected function show()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$model = new EditionAlms($id_course);
		$this->render('show', array(
			'back_link' => $this->_getBackLink(),
			'model' => $model,
			'permissions' => $this->permissions,
			'base_link_course' => $this->base_link_course,
			'base_link_edition' => $this->base_link_edition
		));
	}

	protected function geteditionlist()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		//Datatable info
		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'userid');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');

		$model = new EditionAlms($id_course);

		$total_edition = $model->getEditionNumber();
		$array_edition = $model->loadEdition($start_index, $results, $sort, $dir);

		$result = array(	'totalRecords' => $total_edition,
							'startIndex' => $start_index,
							'sort' => $sort,
							'dir' => $dir,
							'rowsPerPage' => $results,
							'results' => count($array_edition),
							'records' => $array_edition);

		$this->data = $this->json->encode($result);

		echo $this->data;
	}

	public function add()
	{
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		require_once(_lms_.'/lib/lib.course.php');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$course_info = Man_Course::getCourseInfo($id_course);

		$model = new EditionAlms($id_course);

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse());
		elseif(isset($_POST['ins']))
		{
			if($model->addEdition())
				Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse().'&result=ok');
			Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse().'&result=err_ins');
		}
		else
			$this->render('add', array(
					'model' => $model,
					'course_info' => $course_info,
					'base_link_course' => $this->base_link_course,
					'base_link_edition' => $this->base_link_edition
			));
	}

	public function edit()
	{
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		$model = new EditionAlms($id_course, $id_edition);

		$edition_info = $model->getEditionInfo($id_edition);

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse());
		elseif(isset($_POST['mod']))
		{
			if($model->modEdition())
				Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse().'&result=ok');
			Util::jump_to('index.php?r='.$this->base_link_edition.'/show&id_course='.$model->getIdCourse().'&result=err_mod');
		}
		else
			$this->render('edit', array(
					'model' => $model,
					'edition_info' => $edition_info,
					'base_link_course' => $this->base_link_course,
					'base_link_edition' => $this->base_link_edition
			));
	}

	public function del()
	{
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		if(Get::cfg('demo_mode'))
			die('Cannot del course during demo mode.');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		$model = new EditionAlms($id_course, $id_edition);

		$res = array('success' => $model->delEdition());

		$this->data = $this->json->encode($res);

		echo $this->data;
	}
}
?>