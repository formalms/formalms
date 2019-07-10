<?php


defined("IN_FORMA") or die('Direct access is forbidden.');

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


/**
 * Class DashboardBlockCoursesLms
 */
class DashboardBlockCoursesLms extends DashboardBlockLms
{
	const MAX_COURSES = 3;
	const COURSE_TYPE_LIMIT = 3;

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_MEDIUM);
	}

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();

		$data['courses'] = $this->getCourses();
		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath(): string
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile(): string
	{
		return $this->viewFile;
	}

	public function getLink(): string
	{
		return '#';
	}

	public function getRegisteredActions(): array
	{
		return [];
	}

	private function getCourses()
	{

		$conditions = [
			'cu.iduser = :id_user'
		];

		$params = [
			':id_user' => (int) Docebo::user()->getId()
		];

		// course status : all status, new, completed, in progress
		$conditions[] = '(c.status <> 3 )';

		$elearningConditions = $conditions;
		$elearningConditions[] = "c.course_type = ':course_type'";


		$elearningParams = $params;
		$elearningParams[':course_type'] = 'elearning';

		$courselist = [];//$this->findAll($elearningConditions, $elearningParams, self::COURSE_TYPE_LIMIT);

		if (count($courselist) < self::COURSE_TYPE_LIMIT) {

			$classRoomConditions = $conditions;
			$classRoomConditions[] = "c.course_type = ':course_type'";


			$classRoomParams = $params;
			$classRoomParams[':course_type'] = 'classroom';

			$classRoomCourseList = $this->findAll($classRoomConditions, $classRoomParams, self::MAX_COURSES - count($courselist));

			foreach ($classRoomCourseList as $id => $course) {
				$courselist[$id] = $course;
			}
		}

		return $courselist;
	}

	private function findAll($conditions, $params, $limit = 0, $offset = 0)
	{
		$db = DbConn::getInstance();

		// exclude course belonging to pathcourse in which the user is enrolled as a student
		$learning_path_enroll = $this->getUserCoursePathCourses($params[':id_user']);
		$exclude_pathcourse = '';
		if (count($learning_path_enroll) > 1 && Get::sett('on_path_in_mycourses') == 'off') {
			$exclude_path_course = "select idCourse from learning_courseuser where idUser=" . $params[':id_user'] . " and level <= 3 and idCourse in (" . implode(',', $learning_path_enroll) . ")";
			$rs = $db->query($exclude_path_course);
			while ($d = $db->fetch_assoc($rs)) {
				$excl[] = $d['idCourse'];
			}
			$exclude_pathcourse = " and c.idCourse not in (" . implode(',', $excl) . " )";
		}


		$query = "SELECT c.*, c.status AS course_status, cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting"
			. " FROM %lms_course AS c "
			. " JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse)  "
			. " WHERE " . $this->compileWhere($conditions, $params)
			. $exclude_pathcourse
			. " ORDER BY c.idCourse";

		if ($limit > 0) {
			$query .= " LIMIT $limit";
		}
		if ($offset > 0) {
			$query .= " OFFSET $offset";
		}


		$rs = $db->query($query);

		$result = [];
		while ($course = $db->fetch_assoc($rs)) {

			$result[$course['idCourse']] = $this->getDataFromCourse($course);
		}

		return $result;
	}

	private function getUserCoursePathCourses($id_user)
	{
		require_once(_lms_ . '/lib/lib.coursepath.php');
		$cp_man = new Coursepath_Manager();
		$output = array();
		$cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
		if (!empty($cp_list)) {
			$cp_list = array_keys($cp_list);
			$output = $cp_man->getAllCourses($cp_list);
		}
		return $output;
	}

	private function compileWhere($conditions, $params)
	{

		if (!is_array($conditions)) return "1";

		$where = array();
		$find = array_keys($params);
		foreach ($conditions as $key => $value) {

			$where[] = str_replace($find, $params, $value);
		}
		return implode(" AND ", $where);
	}
}
