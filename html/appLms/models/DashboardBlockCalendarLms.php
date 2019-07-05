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

	public function getElearningCalendar($month = null)
	{

		if (null === $month) {
			//$month = date('m');
			$month = 5;
		}

		$startDate = date('Y-' . $month . '-01');
		$endDate = date('Y-' . $month . '-' . date('t', strtotime($startDate)));

		return $this->findCourses($startDate, $endDate, self::COURSE_TYPE_ELEARNING);
	}

	public function getClassroomCalendar($month = null)
	{

		if (null === $month) {
			//$month = date('m');
			$month = 5;
		}

		$startDate = date('Y-' . $month . '-01');
		$endDate = date('Y-' . $month . '-' . date('t', strtotime($startDate)));

		return $this->findCourses($startDate, $endDate, self::COURSE_TYPE_CLASSROOM);
	}

	public function getReservationCalendar($month = null)
	{

		if (null === $month) {
			//$month = date('m');
			$month = 5;
		}

		$startDate = date('Y-' . $month . '-01');
		$endDate = date('Y-' . $month . '-' . date('t', strtotime($startDate)));

		return $this->findReservations($startDate, $endDate);
	}

	private function findCourses($startDate, $endDate, $courseType)
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


		$query = 'SELECT c.*, cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting'
			. ' FROM %lms_course AS c '
			. ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
			. ' WHERE cu.iduser = ' . Docebo::user()->getId()
			. ' AND c.course_type = "' . $courseType . '"'
			. ' AND ( c.date_begin BETWEEN CAST("' . $startDate . '" AS DATE) AND CAST("' . $endDate . '" AS DATE) OR c.date_begin = 0000-00-00)';


		$query .= $exclude_pathcourse
			. " ORDER BY c.date_begin";

		$rs = $db->query($query);

		$result = array();
		while ($data = $db->fetch_assoc($rs)) {

			$result[] = $this->getCalendarDataFromCourse($data, $startDate, $endDate);
		}

		return $result;
	}

	private function findReservations($startDate, $endDate)
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


		$query = 'SELECT c.*, cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting'
			. ' FROM %lms_course AS c '
			. ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
			. ' WHERE cu.iduser = ' . Docebo::user()->getId()
			. ' AND ( c.date_begin BETWEEN CAST("' . $startDate . '" AS DATE) AND CAST("' . $endDate . '" AS DATE) OR c.date_begin = 0000-00-00)';


		$query .= $exclude_pathcourse
			. " ORDER BY c.date_begin";

		$rs = $db->query($query);

		$result = array();
		while ($data = $db->fetch_assoc($rs)) {

			$result[] = $this->getCalendarDataFromReservation($data, $startDate, $endDate);
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

	private function getCalendarDataFromCourse($course, $startDate, $endDate)
	{
		$status_list = [
			0 => Lang::t('_CST_PREPARATION', 'course'),
			1 => Lang::t('_CST_AVAILABLE', 'course'),
			2 => Lang::t('_CST_CONFIRMED', 'course'),
			3 => Lang::t('_CST_CONCLUDED', 'course'),
			4 => Lang::t('_CST_CANCELLED', 'course')
		];

		$dateBegin = $course['date_begin'];
		if ($dateBegin === '0000-00-00') {
			$dateBegin = $startDate;
		}


		$dateEnd = $course['date_end'];
		if ($dateEnd === '0000-00-00') {
			$dateEnd = $endDate;
		}

		$hourBebing = $course['hour_begin'];
		$hourBebingString = '';
		if ($hourBebing === '-1') {
			$hourBebing = '00:00:00';
		} else {
			$hourBebing .= ':00';
			$hourBebingString = $course['hour_begin'];
		}
		$hourEnd = $course['hour_end'];
		$hourEndString = '';
		if ($hourEnd === '-1') {
			$hourEnd = '23:59:59';
		} else {
			$hourEnd .= ':00';
			$hourEndString = $course['hour_end'];
		}

		$calendarData = [
			'title' => $course['name'],
			'start' => $dateBegin . 'T' . $hourBebing,
			'end' => $dateEnd . 'T' . $hourEnd,
			'type' => $course['course_type'],
			'status' => $status_list[(int)$course['status']],
			'description' => $course['box_description'],
			'hours' => $hourBebingString . ' ' . $hourEndString,
		];

		return $calendarData;
	}

	private function getCalendarDataFromReservation($reservation, $reservationCourse, $startDate, $endDate)
	{
		$dateBegin = $reservation['date_begin'];
		if ($dateBegin === '0000-00-00') {
			$dateBegin = $startDate;
		}


		$dateEnd = $reservation['date_end'];
		if ($dateEnd === '0000-00-00') {
			$dateEnd = $endDate;
		}

		$hourBebing = $reservation['hour_begin'];
		$hourBebingString = '';
		if ($hourBebing === '-1') {
			$hourBebing = '00:00:00';
		} else {
			$hourBebing .= ':00';
			$hourBebingString = $reservation['hour_begin'];
		}
		$hourEnd = $reservation['hour_end'];
		$hourEndString = '';
		if ($hourEnd === '-1') {
			$hourEnd = '23:59:59';
		} else {
			$hourEnd .= ':00';
			$hourEndString = $reservation['hour_end'];
		}

		$calendarData = [
			'title' => $reservation['name'],
			'start' => $dateBegin . 'T' . $hourBebing,
			'end' => $dateEnd . 'T' . $hourEnd,
			'type' => $reservation['course_type'],
			'status' => true,
			'description' => $reservation['box_description'],
			'hours' => $hourBebingString . ' ' . $hourEndString,
		];

		$calendarData['course'] = $this->getCalendarDataFromCourse($reservationCourse);

		return $calendarData;
	}
}
