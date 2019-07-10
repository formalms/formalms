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
 * Class DashboardBlockCourseAdviceLms
 */
class DashboardBlockCourseAdviceLms extends DashboardBlockLms
{

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_MEDIUM);
	}

	public function getViewData(): array
	{
		$data = $this->getCommonViewData();
		$data['courseAdvices'] = $this->getCourseAdvices();

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

	private function getCourseAdvices()
	{
		$courseAdvices = [];

		$course = $this->findEnrolledCourses();

		if (count($course) > 0) {
			$courseAdvices = $this->getAdvicesForCourses($course, 3);
		}

		return $courseAdvices;
	}

	private function findEnrolledCourses()
	{
		$db = DbConn::getInstance();

		// exclude course belonging to pathcourse in which the user is enrolled as a student
		$learning_path_enroll = $this->getUserCoursePathCourses(Docebo::user()->getId());
		$exclude_pathcourse = '';
		if (count($learning_path_enroll) > 1 && Get::sett('on_path_in_mycourses') == 'off') {
			$exclude_path_course = "select idCourse from learning_courseuser where idUser=" . Docebo::user()->getId() . " and level <= 3 and idCourse in (" . implode(',', $learning_path_enroll) . ")";
			$rs = $db->query($exclude_path_course);
			while ($d = $db->fetch_assoc($rs)) {
				$excl[] = $d['idCourse'];
			}
			$exclude_pathcourse = " and c.idCourse not in (" . implode(',', $excl) . " )";
		}


		$query = "SELECT c.idCourse"
			. " FROM %lms_course AS c "
			. " JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse)  "
			. " WHERE cu.iduser = " . Docebo::user()->getId() . ' '
			. $exclude_pathcourse
			. " ORDER BY c.idCourse";


		$rs = $db->query($query);

		$result = array();
		while ($data = $db->fetch_assoc($rs)) {
			$result[] = $data['idCourse'];
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

	private function getAdvicesForCourses($courses, $limit = 0, $offset = 0)
	{
		$db = DbConn::getInstance();

		$query = "SELECT idAdvice, title, description, important, author, posted 
					FROM " . $GLOBALS['prefix_lms'] . "_advice
					WHERE idCourse IN (" . implode(',', $courses) . ")
					ORDER BY posted DESC ";

		if ($limit > 0) {
			$query .= " LIMIT $limit";
		}
		if ($offset > 0) {
			$query .= " OFFSET $offset";
		}

		$rs = $db->query($query);

		while ($data = $db->fetch_assoc($rs)) {
			$result[] = $this->getAdviceData($data);
		}

		return $result;
	}

	private function getAdviceData($advice){

		$date = new DateTime($advice['posted']);

		$adviceData = [
			'idAdvice' => $advice['idAdvice'],
			'title' => $advice['title'],
			'description' => $advice['description'],
			'important' => $advice['important'],
			'author' => $advice['author'],
			'posted' => $advice['posted'],
			'date' => $date->format('d/m/Y')
		];

		return $adviceData;
	}
}
