<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Class DashboardBlockCalendarLms.
 */
class DashboardBlockCalendarLms extends DashboardBlockLms
{
    public const COURSE_TYPE_ELEARNING = 'elearning';
    public const COURSE_TYPE_CLASSROOM = 'classroom';

    public function parseConfig($jsonConfig)
    {
        $this->parseBaseConfig($jsonConfig);
    }

    public function getAvailableTypesForBlock()
    {
        return self::ALLOWED_TYPES;
    }

    public function getViewData()
    {
        return $this->getCommonViewData();
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
        return [
            'getElearningCalendar',
            'getClassroomCalendar',
            'getReservationCalendar',
        ];
    }

    private function getStartAndEndDatesFromRequest()
    {
        $month = Forma\lib\Get::pReq('month', DOTY_STRING, '');
        if (!empty($month)) {
            $startDate = date('Y-' . $month . '-01');
            $endDate = date('Y-' . $month . '-' . date('t', strtotime($startDate)));
        } else {
            $startDate = Forma\lib\Get::pReq('startDate', DOTY_STRING, null);
            $endDate = Forma\lib\Get::pReq('endDate', DOTY_STRING, null);
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    public function getElearningCalendar()
    {
        $dates = $this->getStartAndEndDatesFromRequest();
        if (!$dates) {
            return [];
        }

        return $this->findCourses($dates['startDate'], $dates['endDate'], self::COURSE_TYPE_ELEARNING);
    }

    public function getClassroomCalendar()
    {
        $dates = $this->getStartAndEndDatesFromRequest();
        if (!$dates) {
            return [];
        }

        return $this->findCourses($dates['startDate'], $dates['endDate'], self::COURSE_TYPE_CLASSROOM);
    }

    public function getReservationCalendar()
    {
        $dates = $this->getStartAndEndDatesFromRequest();
        if (!$dates) {
            return [];
        }

        return $this->findReservations($dates['startDate'], $dates['endDate']);
    }

    private function findCourses($startDate = null, $endDate = null, $courseType, $showCourseWithoutDates = false)
    {
        // exclude course belonging to pathcourse in which the user is enrolled as a student
        $learning_path_enroll = $this->getUserCoursePathCourses(Docebo::user()->getId());
        $exclude_pathcourse = '';
        if (count($learning_path_enroll) > 1 && Forma\lib\Get::sett('on_path_in_mycourses') == 'off') {
            $exclude_path_course = 'select idCourse from learning_courseuser where idUser=' . Docebo::user()->getId() . ' and level <= 3 and idCourse in (' . implode(',', $learning_path_enroll) . ')';
            $rs = $this->db->query($exclude_path_course);
            while ($d = $this->db->fetch_assoc($rs)) {
                $excl[] = $d['idCourse'];
            }
            $exclude_pathcourse = ' and c.idCourse not in (' . implode(',', $excl) . ' )';
        }

        switch ($courseType) {
            case self::COURSE_TYPE_CLASSROOM:
                $query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, cd.name AS course_name, cd.status AS course_status, cd.sub_start_date AS course_date_begin, cd.sub_end_date AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, '
                    . ' cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting';
                break;
            case self::COURSE_TYPE_ELEARNING:
            default:
                $query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, '
                    . ' cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting';
                break;
        }
        $query .= ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) ';

        switch ($courseType) {
            case self::COURSE_TYPE_CLASSROOM:
                $query .= ' JOIN %lms_course_date AS cd ON (c.idCourse = cd.id_course) 
                            JOIN %lms_course_date_day cdd ON cdd.id_date = cd.id_date ';
                            if (null !== $startDate && !empty($startDate) && null !== $endDate && !empty($endDate)) {
                                $query .= 'AND cdd.date_begin BETWEEN CAST( "' . $startDate . '" AS DATE ) AND CAST( "' . $endDate . '" AS DATE )';
                            }
                break;
            case self::COURSE_TYPE_ELEARNING:
            default:
                break;
        }

        $query .= ' WHERE cu.iduser = ' . Docebo::user()->getId()
            . ' AND c.course_type = "' . $courseType . '"';

        if (($courseType == self::COURSE_TYPE_ELEARNING) && null !== $startDate && !empty($startDate) && null !== $endDate && !empty($endDate)) {
            $query .= ' AND (( c.date_end BETWEEN CAST( "' . $startDate . '" AS DATE ) AND CAST( "' . $endDate . '" AS DATE ) ) 
                OR ( c.date_begin BETWEEN CAST( "' . $startDate . '" AS DATE ) AND CAST( "' . $endDate . '" AS DATE ) ) )';
        }

        if ($showCourseWithoutDates) {
            $query .= ' OR c.date_begin = 0000-00-00 OR c.date_end = 0000-00-00';
        } else {
            switch ($courseType) {
                case self::COURSE_TYPE_CLASSROOM:
                    // $query .= ' AND cd.sub_start_date != 0000-00-00 AND  cd.sub_end_date != 0000-00-00';
                    break;
                case self::COURSE_TYPE_ELEARNING:
                default:
                    $query .= ' AND c.date_begin != 0000-00-00 AND  c.date_end != 0000-00-00';
                    break;
            }
        }

        $query .= $exclude_pathcourse;
        $query .= '	GROUP BY course_id'; //serve per evitare duplicati nei risultati (x es calendario)

        switch ($courseType) {
            case self::COURSE_TYPE_CLASSROOM:
                $query .= ' ORDER BY cd.sub_start_date';
                break;
            case self::COURSE_TYPE_ELEARNING:
            default:
                $query .= ' ORDER BY c.date_begin';
                break;
        }

        $rs = $this->db->query($query);

        $result = [];
        foreach ($rs as $data) {
            $courseDates = $this->getDatasFromCourse($data);

            foreach ($courseDates as $courseDate) {
                $result[] = $courseDate;
            }
        }

        return $result;
    }

    private function findReservations($startDate, $endDate, $showCourseWithoutDates = false)
    {
        $db = DbConn::getInstance();

        // exclude course belonging to pathcourse in which the user is enrolled as a student
        $learning_path_enroll = $this->getUserCoursePathCourses(Docebo::user()->getId());
        $exclude_pathcourse = '';
        if (count($learning_path_enroll) > 1 && Forma\lib\Get::sett('on_path_in_mycourses') == 'off') {
            $exclude_path_course = 'select idCourse from learning_courseuser where idUser=' . Docebo::user()->getId() . ' and level <= 3 and idCourse in (' . implode(',', $learning_path_enroll) . ')';
            $rs = $this->db->query($exclude_path_course);
            while ($d = $this->db->fetch_assoc($rs)) {
                $excl[] = $d['idCourse'];
            }
            $exclude_pathcourse = ' and c.idCourse not in (' . implode(',', $excl) . ' )';
        }

        $query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, '
            . ' cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting, '
            . ' re.idEvent AS reservation_event_id, re.idLaboratory AS reservation_laboratory_id, re.idCategory AS reservation_category_id, re.title AS reservation_title, re.description AS reservation_description, re.date AS reservation_date, re.maxUser AS reservation_max_user, re.deadLine AS reservation_dead_line, re.fromTime AS reservation_from_time, re.toTime AS reservation_tiTime'
            . ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
            . ' JOIN %lms_reservation_events AS re ON (c.idCourse = re.idCourse) '
            . ' JOIN %lms_reservation_subscribed AS rs ON (cu.iduser = rs.idstUser) '
            . ' WHERE cu.iduser = ' . Docebo::user()->getId()
            . ' AND ( c.date_begin BETWEEN CAST("' . $startDate . '" AS DATE) AND CAST("' . $endDate . '" AS DATE)';

        if ($showCourseWithoutDates) {
            $query .= ' OR c.date_begin = 0000-00-00 OR c.date_end = 0000-00-00';
        } else {
            $query .= ' AND c.date_begin != 0000-00-00 AND c.date_end != 0000-00-00';
        }

        $query .= ')';

        $query .= $exclude_pathcourse . ' ORDER BY c.date_begin';

        $rs = $this->db->query($query);

        $result = [];
        while ($data = $this->db->fetch_assoc($rs)) {
            $reservationData = $this->getDataFromReservation($data);

            $result[] = $reservationData;
        }

        return $result;
    }

    private function getUserCoursePathCourses($id_user)
    {
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cp_man = new Coursepath_Manager();
        $output = [];
        $cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
        if (!empty($cp_list)) {
            $cp_list = array_keys($cp_list);
            $output = $cp_man->getAllCourses($cp_list);
        }

        return $output;
    }

    protected function getDatasFromCourse($course)
    {
        $dates = [];
        $courseData = $this->getDataFromCourse($course);

        if ($course['course_type'] == self::COURSE_TYPE_CLASSROOM) {
            $query = 'SELECT cr.name AS class, cl.location, cdd.date_begin, cdd.date_end, c.name 
                FROM %lms_course_date_day cdd 
                INNER JOIN %lms_course_date cd ON cdd.id_date = cd.id_date 
                INNER JOIN %lms_course_date_user cdu ON cd.id_date = cdu.id_date 
                INNER JOIN %lms_course c ON c.idCourse = cd.id_course 
                LEFT JOIN %lms_classroom cr ON cdd.classroom = cr.idClassroom
                LEFT JOIN %lms_class_location cl ON cr.location_id = cl.location_id
                WHERE cd.id_course = ' . $course['course_id'] . '
                AND cdu.id_user = ' . Docebo::user()->getId() . ' AND cdd.deleted = 0 ORDER BY cdd.date_begin';
            $result = $this->db->query($query
            );

            foreach ($result as $row) {
                $courseData['endDate'] = $courseData['startDate'] = $row['date_begin'];
                $courseData['hourBegin'] = substr(explode(' ', $row['date_begin'])[1], 0, 5);
                $courseData['hourEnd'] = substr(explode(' ', $row['date_end'])[1], 0, 5);
                $courseData['hours'] = $courseData['hourBegin'] . ' - ' . $courseData['hourEnd'];
                $courseData['description'] = $row['name'] . '<br>' . $row['location'] . ' - ' . $row['class'];

                $dates[] = $courseData;
            }
        } elseif ($course['course_date_begin'] !== $course['course_date_end']) {
            $dates[] = $courseData;
            $courseData = $this->getDataFromCourse($course, false);
            $dates[] = $courseData;
        } else {
            $dates[] = $courseData;
        }

        return $dates;
    }

    protected function getDataFromCourse($course, $status = true)
    {
        $courseData = parent::getDataFromCourse($course);

        if ($status) {
            $courseData['endDate'] = $courseData['startDate'];
            $courseData['endDateString'] = $courseData['startDateString'];
        } else {
            $courseData['startDate'] = $courseData['endDate'];
            $courseData['startDateString'] = $courseData['endDateString'];
        }

        $courseData['status'] = $status;

        return $courseData;
    }
}
