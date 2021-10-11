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
    const COURSE_TYPE_ELEARNING = 'elearning';
    const COURSE_TYPE_CLASSROOM = 'classroom';

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

        $data['courses'] = $this->getCourses();
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

    private function getCourses()
    {

        $conditions = [
            'cu.iduser = :id_user'
        ];

        $params = [
            ':id_user' => (int)Docebo::user()->getId()
        ];

        // course status : all status, new, completed, in progress
        $conditions[] = '(c.status <> 3)';

        $midnight = new DateTime('midnight');
        $midnight = date_format($midnight, 'Y-m-d H:i:s');

        $elearningConditions = $conditions;
        $elearningConditions[] = "c.course_type = ':course_type'";
        $elearningConditions[] = "c.date_end >= '$midnight'";
        $elearningConditions[] = "c.date_end != '0000-00-00'";

        $elearningParams = $params;
        $elearningParams[':course_type'] = 'elearning';

        $courselist = $this->findAll($elearningConditions, $elearningParams, self::COURSE_TYPE_LIMIT);

        if (count($courselist) < self::COURSE_TYPE_LIMIT || count($courselist) < self::MAX_COURSES) {

            $classRoomConditions = $conditions;
            $classRoomConditions[] = "c.course_type = ':course_type'";

            $classRoomParams = $params;
            $classRoomParams[':course_type'] = 'classroom';

            $classRoomCourseList = $this->findAll($classRoomConditions, $classRoomParams, 0);

            foreach ($classRoomCourseList as $id => $course) {
                switch ($course['type']){
                    case self::COURSE_TYPE_CLASSROOM:
                        $q = sql_query("
		            	SELECT date_begin, date_end FROM %lms_course_date_day cdd 
		            	INNER JOIN %lms_course_date cd ON cdd.id_date = cd.id_date 
						INNER JOIN %lms_course_date_user cdu ON cdd.id_date = cdu.id_date
		            	WHERE cd.id_course = $id
		            	AND cdu.id_user = " . Docebo::user()->getId() . "
		            	AND date_begin >= '$midnight'
		            	AND cdd.deleted = 0
		            	ORDER BY date_begin ASC
	            	");
                        foreach ($q as $row){
                            if (!$row['date_begin'] || !$row['date_end']) {
                                break;
                            }

                            $course['startDateString'] = $course['startDate'] = date("d-m-Y", strtotime($row['date_begin']));
                            $course['endDateString'] = $course['endDate'] = date("H:i", strtotime($row['date_end'])) . ' ' . date("H:i", strtotime($row['date_end']));

                            if (isset($course['dates'])) {
                                unset($course['dates']);
                            }

                            $courselist[] = $course;
                        }
                        break;
                    case self::COURSE_TYPE_ELEARNING:
                    default:
                        $courselist[] = $course;
                        break;
                }
            }
        }

        // Order by startDate
        usort($courselist, function ($element1, $element2) {
            $datetime1 = strtotime($element1['startDate']);
            $datetime2 = strtotime($element2['startDate']);
            return $datetime1 - $datetime2;
        });

        // Limit to self::COURSE_TYPE_LIMIT
        $i = 0;
        foreach ($courselist as $cl) {
            if ($i >= self::COURSE_TYPE_LIMIT) {
                break;
            }
            $res[] = $cl;
            $i++;
        }

        return $res;
    }

    private function findAll($conditions, $params, $limit = 0, $offset = 0)
    {
        // exclude course belonging to pathcourse in which the user is enrolled as a student
        $learning_path_enroll = $this->getUserCoursePathCourses($params[':id_user']);
        $exclude_pathcourse = '';
        if (count($learning_path_enroll) > 1 && Get::sett('on_path_in_mycourses') == 'off') {
            $exclude_path_course = "select idCourse from learning_courseuser where idUser=" . $params[':id_user'] . " and level <= 3 and idCourse in (" . implode(',', $learning_path_enroll) . ")";
            $rs = $this->db->query($exclude_path_course);
            while ($d = $this->db->fetch_assoc($rs)) {
                $excl[] = $d['idCourse'];
            }
            $exclude_pathcourse = " and c.idCourse not in (" . implode(',', $excl) . " )";
        }

        $query = 'SELECT c.idCourse AS course_id, c.idCategory AS course_category_id, c.name AS course_name, c.status AS course_status, c.date_begin AS course_date_begin, c.date_end AS course_date_end, c.hour_begin AS course_hour_begin, c.hour_end AS course_hour_end, c.course_type AS course_type, c.box_description AS course_box_description, c.img_course AS course_img_course '
            . ' ,cu.status AS user_status, cu.level AS user_level, cu.date_inscr AS user_date_inscr, cu.date_first_access AS user_date_first_access, cu.date_complete AS user_date_complete, cu.waiting AS user_waiting '
            . ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
            . ' WHERE ' . $this->compileWhere($conditions, $params)
            . $exclude_pathcourse
            . ' ORDER BY c.idCourse';

        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }
        if ($offset > 0) {
            $query .= " OFFSET $offset";
        }

        $rs = $this->db->query($query);

        $result = [];
        foreach ($rs as $course){

            $courseData = $this->getDataFromCourse($course);

            if ($courseData['type'] === 'classroom') {

                $dates = $this->getDatesForCourse($course);

                $courseData['dates'] = $dates;
            }

            $result[$course['course_id']] = $courseData;
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

    private function getDatesForCourse($course)
    {
        $query = 'SELECT cd.id_date AS date_id ,cd.code AS date_code ,cd.name AS date_name ,cd.description AS date_description ,cd.status AS date_status ,cd.sub_start_date AS date_start_date ,cd.sub_end_date AS date_end_date'
            . ' FROM %lms_course_date AS cd '
            . ' WHERE cd.id_course = ' . $course['course_id']
            . ' AND cd.status <>3 '
            . ' AND cd.sub_end_date <> \'0000-00-00 00:00:00\' '
            . ' AND cd.sub_start_date <> \'0000-00-00 00:00:00\' '
            . ' ORDER BY cd.id_date';

        $rs = $this->db->query($query);

        $dates = [];
        foreach ($rs as $date) {

            if ($date['date_start_date'] !== '0000-00-00 00:00:00') {
                $startDate = new DateTime($date['date_start_date']);
                $startDateString = $startDate->format('d/m/Y');
            } else {
                $startDateString = '';
            }

            if ($date['date_end_date'] !== '0000-00-00 00:00:00') {
                $endDate = new DateTime($date['date_end_date']);
                $endDateString = $endDate->format('d/m/Y');
            } else {
                $endDateString = '';
            }

            $dates[] = [
                'id' => $date['date_id'],
                'code' => $date['date_code'],
                'name' => $date['date_name'],
                'description' => $date['date_description'],
                'status' => $date['date_status'],
                'startDate' => $date['date_start_date'],
                'endDate' => $date['date_end_date'],
                'startDateString' => $startDateString,
                'endDateString' => $endDateString,
            ];
        }
        return $dates;
    }
}
