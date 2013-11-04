<?php
Class EditionLmsController extends LmsController
{
	public $name = 'classroom';

	protected $json;
	protected $acl_man;

	protected $data;

	public function __construct($mvc_name)
	{
		parent::__construct($mvc_name);

		require_once(_base_.'/lib/lib.json.php');

		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
	}

	protected function show()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$model = new EditionLms($id_course);
		$this->render('show', array('model' => $model));
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

		$model = new EditionLms($id_course);

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
		require_once(_lms_.'/lib/lib.course.php');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);

		$course_info = Man_Course::getCourseInfo($id_course);

		$model = new EditionLms($id_course);

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse());
		elseif(isset($_POST['ins']))
		{
			if($model->addEdition())
				Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse().'&result=ok');
			Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse().'&result=err_ins');
		}
		else
			$this->render('add', array('model' => $model, 'course_info' => $course_info));
	}

	public function edit()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		$model = new EditionLms($id_course, $id_edition);

		$edition_info = $model->getEditionInfo($id_edition);

		if(isset($_POST['undo']))
			Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse());
		elseif(isset($_POST['mod']))
		{
			if($model->modEdition())
				Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse().'&result=ok');
			Util::jump_to('index.php?r=edition/show&id_course='.$model->getIdCourse().'&result=err_mod');
		}
		else
			$this->render('edit', array('model' => $model, 'edition_info' => $edition_info));
	}

	public function del()
	{
		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);

		$model = new EditionLms($id_course, $id_edition);

		$res = array('success' => $model->delEdition());

		$this->data = $this->json->encode($res);

		echo $this->data;
	}
}
?>