<?php


defined("IN_FORMA") or die('Direct access is forbidden.');




/**
 * Class DashboardBlockAnnouncementsLms
 */
class DashboardBlockAnnouncementsLms extends DashboardBlockLms
{

	public function __construct($jsonConfig)
	{
		parent::__construct($jsonConfig);
	}

	public function parseConfig($jsonConfig)
	{
		$this->parseBaseConfig($jsonConfig);
	}

	public function getAvailableTypesForBlock()
	{
		return [
			DashboardBlockLms::TYPE_1COL,
			DashboardBlockLms::TYPE_2COL,
			DashboardBlockLms::TYPE_3COL,
			DashboardBlockLms::TYPE_4COL
		];
	}

	public function getViewData()
	{
		$data = $this->getCommonViewData();
		$data['courseAdvices'] = $this->getCourseAdvices();

		return $data;
	}

	/**
	 * @return string
	 */
	public function getViewPath()
	{
		return $this->viewPath;
	}

	/**
	 * @return string
	 */
	public function getViewFile()
	{
		return $this->viewFile;
	}

	public function getLink()
	{
		return '#';
	}

	public function getRegisteredActions()
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
		// exclude course belonging to pathcourse in which the user is enrolled as a student
		$learning_path_enroll = $this->getUserCoursePathCourses(Docebo::user()->getId());
		$exclude_pathcourse = '';
		if (count($learning_path_enroll) > 1 && Get::sett('on_path_in_mycourses') == 'off') {
			$exclude_path_course = "select idCourse from learning_courseuser where idUser=" . Docebo::user()->getId() . " and level <= 3 and idCourse in (" . implode(',', $learning_path_enroll) . ")";
			$rs = $this->db->query($exclude_path_course);
			while ($d = $this->db->fetch_assoc($rs)) {
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


		$rs = $this->db->query($query);

		$result = [];
		while ($data = $this->db->fetch_assoc($rs)) {
			$result[] = $data['idCourse'];
		}

		return $result;
	}

	private function getUserCoursePathCourses($id_user)
	{
		require_once(_lms_ . '/lib/lib.coursepath.php');
		$cp_man = new Coursepath_Manager();
		$output = [];
		$cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
		if (!empty($cp_list)) {
			$cp_list = array_keys($cp_list);
			$output = $cp_man->getAllCourses($cp_list);
		}
		return $output;
	}

	private function getAdvicesForCourses($courses, $limit = 0, $offset = 0)
	{
		$query = "SELECT a.idAdvice, a.title, a.description, a.important, a.author, a.posted, a.idCourse, c.name AS courseName 
					FROM " . $GLOBALS['prefix_lms'] . "_advice AS a
					INNER JOIN " . $GLOBALS['prefix_lms'] . "_course AS c ON c.idCourse = a.idCourse
					WHERE a.idCourse IN (" . implode(',', $courses) . ")
					ORDER BY a.posted DESC ";

		if ($limit > 0) {
			$query .= " LIMIT $limit";
		}
		if ($offset > 0) {
			$query .= " OFFSET $offset";
		}

		$rs = $this->db->query($query);

		while ($data = $this->db->fetch_assoc($rs)) {
			$result[] = $this->getAdviceData($data);
		}

		return $result;
	}

	private function getAdviceData($advice)
	{

		$date = new DateTime($advice['posted']);

		$adviceData = [
			'idAdvice' => $advice['idAdvice'],
			'title' => $advice['title'],
			'description' => html_entity_decode(strip_tags($advice['description'])),
			'important' => $advice['important'],
			'author' => $advice['author'],
			'posted' => $advice['posted'],
			'idCourse' => $advice['idCourse'],
			'courseName' => $advice['courseName'],
			'date' => $date->format('d/m/Y')
		];

		return $adviceData;
	}
}
