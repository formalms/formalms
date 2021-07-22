<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

Class EditionAlms extends Model
{
	var $acl_man;
	public $edition_man;
	public $course_man;

	var $id_course;
	var $id_edition;

	public function __construct($id_course = 0, $id_edition = 0)
	{
		require_once(_lms_.'/lib/lib.edition.php');
		require_once(_lms_.'/lib/lib.course.php');

		$this->edition_man = new EditionManager();

		$this->course_man = new Man_Course();

		$this->acl_man =& Docebo::user()->getAclManager();

		$this->id_course = $id_course;
		$this->id_edition = $id_edition;
	}

	public function getPerm()
	{
		return array('view' => 'standard/view.png');
	}

	public function getIdCourse()
	{
		return $this->id_course;
	}

	public function getIdEdition()
	{
		return $this->id_edition;
	}

	public function getEditionNumber()
	{
		return $this->edition_man->getEditionNumber($this->id_course);
	}

	public function loadEdition($start_index, $results, $sort, $dir)
	{
		return $this->edition_man->getEdition($this->id_course, $start_index, $results, $sort, $dir);
	}

	public function getStatusForDropdown()
	{
		return $this->edition_man->getStatusForDropdown();
	}

	protected function getEditionInfoFromPost()
	{
		$res = array(	'code' => Get::req('code', DOTY_MIXED, ''),
						'name' => Get::req('name', DOTY_MIXED, ''),
						'description' => Get::req('description', DOTY_MIXED, ''),
						'status' => Get::req('status', DOTY_INT, 0),
						'max_par' => Get::req('max_par', DOTY_INT, 0),
						'min_par' => Get::req('min_par', DOTY_INT, 0),
						'price' => Get::req('price', DOTY_MIXED, ''),
						'date_begin' => Get::req('date_begin', DOTY_MIXED, ''),
						'date_end' => Get::req('date_end', DOTY_MIXED, ''),
						'overbooking' => Get::req('overbooking', DOTY_INT, 0),
						'can_subscribe' => Get::req('can_subscribe', DOTY_INT, 0),
						'sub_date_begin' => Get::req('sub_date_begin', DOTY_MIXED, ''),
						'sub_date_end' => Get::req('sub_date_end', DOTY_MIXED, ''));

		return $res;
	}

	public function addEdition()
	{
		$edition_info = $this->getEditionInfoFromPost();

		return $this->edition_man->insertEdition($this->id_course, $edition_info['code'], $edition_info['name'], $edition_info['description'], $edition_info['status'], $edition_info['max_par'], $edition_info['min_par'], $edition_info['price'], $edition_info['date_begin'], $edition_info['date_end'], $edition_info['overbooking'], $edition_info['can_subscribe'], $edition_info['sub_date_begin'], $edition_info['sub_date_end']);
	}

	public function getEditionInfo($id_edition)
	{
		return $this->edition_man->getEditionInfo($id_edition);
	}

	public function modEdition()
	{
		$edition_info = $this->getEditionInfoFromPost();

		return $this->edition_man->modEdition($this->id_edition, $edition_info['code'], $edition_info['name'], $edition_info['description'], $edition_info['status'], $edition_info['max_par'], $edition_info['min_par'], $edition_info['price'], $edition_info['date_begin'], $edition_info['date_end'], $edition_info['overbooking'], $edition_info['can_subscribe'], $edition_info['sub_date_begin'], $edition_info['sub_date_end']);
	}

	public function delEdition()
	{
		return $this->edition_man->delEdition($this->id_edition);
	}

	public function getEditionIdFromCourse($id_course)
	{
		$query =	"SELECT id_edition"
					." FROM %lms_course_editions"
					." WHERE id_course = ".(int)$id_course;

		$result = sql_query($query);

		$res = array();

		while(list($id_edition) = sql_fetch_row($result))
			$res[$id_edition] = $id_edition;

		return $res;
	}
}
?>