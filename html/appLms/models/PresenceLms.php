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

class PresenceLms extends Model
{
	protected $cache;
	protected $classroom_model;
	protected $acl_man;
	protected $id_course;
	protected $id_date;

	public function  __construct($id_course, $id_date)
	{
		$this->id_course = $id_course;
		$this->id_date = $id_date;
		require_once(_lms_.'/admin/models/ClassroomAlms.php');
		$this->classroom_model = new ClassroomAlms($this->id_course, $this->id_date);
		$this->cache = array();
		$this->acl_man = $GLOBALS["current_user"]->getAclManager();
	}

	public function getPerm()
	{
		return array(
			'view' => 'standard/view.png'
		);
	}

	public function setIdDate($id_date)
	{
		$this->id_date = $id_date;
		$this->classroom_model = new ClassroomAlms($this->id_course, $this->id_date);
	}

	public function getIdDate()
	{
		return $this->id_date;
	}

	public function getUserDateForCourse($id_user)
	{
		if($id_user == $this->acl_man->getAnonymousId())
			return array();

		$query =	"SELECT id_date"
					." FROM %lms_course_date_user"
					." WHERE id_user = ".$id_user
					." AND id_date IN"
					." ("
					." SELECT id_date"
					." FROM %lms_course_date"
					." WHERE id_course = ".$this->id_course
					.")";

		$result = sql_query($query);
		$res = array();

		while(list($id_date) = sql_fetch_row($result))
			$res[] = $id_date;

		return $res;
	}

	public function getDateInfoForPublicPresence($array_date)
	{
		$query =	"SELECT dt.*, MIN(dy.date_begin) AS date_begin, MAX(dy.date_end) AS date_end, dy.pause_begin, dy.pause_end, COUNT(dy.id_day) as num_day, COUNT(DISTINCT du.id_user) as user_subscribed"
					." FROM %lms_course_date as dt"
					." JOIN %lms_course_date_day as dy ON dy.id_date = dt.id_date"
					." LEFT JOIN %lms_course_date_user as du ON du.id_date = dt.id_date"
					." WHERE dt.id_date IN (".implode(',',$array_date).")"
					." GROUP BY dt.id_date"
					." ORDER BY date_begin DESC";

		$result = sql_query($query);

		$res = array();

		while($row = sql_fetch_assoc($result))
		{
			$row['classroom'] = $this->getDateClassrooms($row['id_date']);
			$res[] = $row;
		}

		return $res;
	}

	protected function getDateClassrooms($id_date)
	{
		$query =	"SELECT DISTINCT classroom"
					." FROM %lms_course_date_day"
					." WHERE id_date = ".$id_date;

		$result = sql_query($query);
		$array_classroom = array();

		while(list($id_classroom) = sql_fetch_row($result))
			$array_classroom[$id_classroom] = $id_classroom;

		$res = '';
		$first = true;

		if(isset($array_classroom[0]))
		{
			$first = false;
			$res .= Lang::t('_NOT_ASSIGNED', 'admin_date');
		}

		$query =	"SELECT name"
					." FROM %lms_classroom"
					." WHERE idClassroom IN (".implode(',', $array_classroom).")"
					." ORDER BY name";

		$result = sql_query($query);

		while(list($name) = sql_fetch_row($result))
			if($first)
			{
				$first = false;
				$res .= $name;
			}
			else
				$res .= ', '.$name;

		return $res;
	}

	public function getPresenceTable()
	{
		return $this->classroom_model->getPresenceTable();
	}

	public function getTestType()
	{
		return $this->classroom_model->getTestType();
	}

	public function savePresence()
	{
		return $this->classroom_model->savePresence();
	}
}
?>