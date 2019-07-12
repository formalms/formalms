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
 * Class DashboardBlockCalendarLms
 */
class DashboardBlockCalendarLms extends DashboardBlockLms
{
	const COURSE_TYPE_ELEARNING = 'elearning';
	const COURSE_TYPE_CLASSROOM = 'classroom';

	public function __construct()
	{
		parent::__construct();
		$this->setEnabled(true);
		$this->setType(DashboardBlockLms::TYPE_BUTTON);
	}


	public function getViewData(): array
	{
		return $this->getCommonViewData();
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
		return [
			'getElearningCalendar',
			'getClassroomCalendar',
			'getReservationCalendar'
		];
	}

	private function getStartAndEndDatesFromRequest()
	{
		$month = Get::pReq('month', DOTY_STRING, '');
		if (!empty($month)) {
			$startDate = date('Y-' . $month . '-01');
			$endDate = date('Y-' . $month . '-' . date('t', strtotime($startDate)));
		} else {
			$startDate = Get::pReq('startDate', DOTY_STRING, '2000-01-01');
			$endDate = Get::pReq('endDate', DOTY_STRING, '2050-01-01');

			if (empty($startDate) || empty($endDate)) {
				return false;
			}
		}
		return [
			'startDate' => $startDate,
			'endDate' => $endDate
		];
	}

	public function getElearningCalendar()
	{
		$dates = $this->getStartAndEndDatesFromRequest();
		if(!$dates){
			return [];
		}

		return $this->findCourses($dates['startDate'], $dates['endDate'], self::COURSE_TYPE_ELEARNING);
	}

	public function getClassroomCalendar()
	{
		$dates = $this->getStartAndEndDatesFromRequest();
		if(!$dates){
			return [];
		}

		return $this->findCourses($dates['startDate'], $dates['endDate'], self::COURSE_TYPE_CLASSROOM);
	}

	public function getReservationCalendar()
	{
		$dates = $this->getStartAndEndDatesFromRequest();
		if(!$dates){
			return [];
		}

		return $this->findReservations($dates['startDate'], $dates['endDate']);
	}

	private function findCourses($startDate, $endDate, $courseType, $showCourseWithoutDates = false)
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

		$query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, '
			. ' cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting'
			. ' FROM %lms_course AS c '
			. ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
			. ' WHERE cu.iduser = ' . Docebo::user()->getId()
			. ' AND c.course_type = "' . $courseType . '"'
			. ' AND ( c.date_begin BETWEEN CAST("' . $startDate . '" AS DATE) AND CAST("' . $endDate . '" AS DATE)';

		if ($showCourseWithoutDates) {
			$query .= ' OR c.date_begin = 0000-00-00';
		}

		$query .= ')';

		$query .= $exclude_pathcourse . " ORDER BY c.date_begin";

		$rs = $db->query($query);

		$result = array();
		while ($data = $db->fetch_assoc($rs)) {

			$courseData = $this->getDataFromCourse($data);
			
			$result[] = $courseData;
		}

		return $result;
	}

	private function findReservations($startDate, $endDate, $showCourseWithoutDates = false)
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

		$query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, '
			. ' cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting, '
			. ' re.idEvent AS reservation_event_id, re.idLaboratory AAS reservation_laboratory_id, re.idCategory AS reservation_category_id, re.title AS reservation_title, re.description AS reservation_description, re.date AS reservation_date, re.maxUser AS reservation_max_user, re.deadLine AS reservation_dead_line, re.fromTime AS reservation_from_time, re.toTime AS reservation_tiTime'
			. ' FROM %lms_course AS c '
			. ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
			. ' JOIN %lms_reservation_events AS re ON (c.idCourse = re.idCourse) '
			. ' JOIN %lms_reservation_subscribed AS rs ON (cu.iduser = rs.idstUser) '
			. ' WHERE cu.iduser = ' . Docebo::user()->getId()
			. ' AND ( c.date_begin BETWEEN CAST("' . $startDate . '" AS DATE) AND CAST("' . $endDate . '" AS DATE)';

		if ($showCourseWithoutDates) {
			$query .= ' OR c.date_begin = 0000-00-00';
		}

		$query .= ')';

		$query .= $exclude_pathcourse . " ORDER BY c.date_begin";

		$rs = $db->query($query);

		$result = array();
		while ($data = $db->fetch_assoc($rs)) {

			$reservationData = $this->getDataFromReservation($data);

			$result[] = $reservationData;
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
}
