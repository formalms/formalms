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
 * Class DashboardBlockCoursesLms.
 */
class DashboardBlockCoursesLms extends DashboardBlockLms
{
    public const COURSE_TYPE_ALL = 'all';
    public const COURSE_TYPE_ELEARNING = 'elearning';
    public const COURSE_TYPE_CLASSROOM = 'classroom';
    public const SORT_ORDER = [1 => ' cu.date_inscr DESC', 2 => 'cu.date_inscr ASC', 3 => 'cu.idCourse DESC', 4 => 'cu.idCourse ASC'];

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
        return self::ALLOWED_TYPES;
    }

    public function getForm()
    {
        $form = parent::getForm();

        array_push(
            $form,
            DashboardBlockForm::getFormItem($this, 'max_courses_number', DashboardBlockForm::FORM_TYPE_NUMBER, false),
            DashboardBlockForm::getFormItem($this, 'course_type', DashboardBlockForm::FORM_TYPE_SELECT, false,
                [
                    'all' => Lang::t('_VIEW_ALL', 'STANDARD'),
                    'elearning' => Lang::t('_COURSE_TYPE_ELEARNING', 'COURSE'),  // ORDER -> CLOSEST DATE END
                    'classroom' => Lang::t('_CLASSROOM'),
                ]
            ),
            DashboardBlockForm::getFormItem($this, 'show_button', DashboardBlockForm::FORM_TYPE_CHECKBOX, false, [1 => Lang::t('_SHOW_BUTTON', 'dashboardsetting')]),
        );

        return $form;
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
        $courses = [];

        $limit = array_key_exists('max_courses_number', $this->data) ? $this->data['max_courses_number'] : 0;
        $conditions = ['ID_USER' => 'cu.iduser =' . (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId()];
        $conditions['COURSE_STATUS'] = '(c.status in  (1,2))'; // only available, confirmed
        $conditions['USER_ENROLLMENT_STATUS'] = '(cu.status in (0,1))'; // only enrolled and in progress

        $queries = [];
        switch ($this->data['course_type']) {
            case self::COURSE_TYPE_CLASSROOM:
                $conditions['COUSE_TYPE'] = "(c.course_type = '" . self::COURSE_TYPE_CLASSROOM . "')";
                $queries[] = $this->geClassroomQuery($conditions, $limit);
                break;
            case self::COURSE_TYPE_ELEARNING:
                $conditions['COUSE_TYPE'] = "(c.course_type = '" . self::COURSE_TYPE_ELEARNING . "')";
                $queries[] = $this->getElearningQuery($conditions, $limit);
                break;
            case self::COURSE_TYPE_ALL:
            default:
                $conditions['COUSE_TYPE'] = "(c.course_type = '" . self::COURSE_TYPE_ELEARNING . "')";
                $queries[] = $this->getElearningQuery($conditions, $limit);

                $conditions['COUSE_TYPE'] = "(c.course_type = '" . self::COURSE_TYPE_CLASSROOM . "')";
                $queries[] = $this->geClassroomQuery($conditions, $limit);
                break;
        }

        foreach ($queries as $query) {
            $queryResult = $this->db->query($query);
            foreach ($queryResult as $course) {
                $courseData = $this->getDataFromCourse($course);

                $courses[$course['course_id']] ??= $courseData; // getting just first date for classroom courses
            }
        }

        usort($courses, static function ($a, $b) {
            switch ($a['type']) {
                case self::COURSE_TYPE_ELEARNING:
                    switch ($b['type']) {
                        case self::COURSE_TYPE_ELEARNING:
                            return $a['endDate'] > $b['endDate'];
                        case self::COURSE_TYPE_CLASSROOM:
                            return $a['endDate'] > $b['startDate'];
                        default:
                    }
                    break;
                case self::COURSE_TYPE_CLASSROOM:
                    switch ($b['type']) {
                        case self::COURSE_TYPE_ELEARNING:
                            return $a['startDate'] > $b['endDate'];
                        case self::COURSE_TYPE_CLASSROOM:
                            return $a['startDate'] > $b['startDate'];
                        default:
                    }
                    break;
                default:
            }

            return $a['startDate'] > $b['startDate'];
        });

        return array_slice($courses, 0, $limit);
    }

    private function getElearningQuery($conditions, $limit = 0)
    {
        $midnight = new DateTime('midnight');
        $midnight = date_format($midnight, 'Y-m-d H:i:s');

        $conditions[] = "(c.date_end >= '$midnight')";

        $excludePathcourse = $this->excludePathCourse();

        $query = 'SELECT c.idCourse course_id, c.name course_name,  c.idCategory course_category_id, c.status course_status, c.description,  
                          c.course_type, c.date_end course_date_end, c.hour_end course_hour_end, c.date_begin course_date_begin, 
                          c.hour_begin course_hour_begin, c.box_description course_box_description, 
                          c.img_course course_img_course, cu.status user_status, cu.level user_level, cu.date_inscr user_date_inscr'
            . ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
            . ' WHERE ' . implode(' AND ', $conditions)
            . $excludePathcourse
            . '	GROUP BY course_id'
            . ' ORDER BY  c.date_end ASC';

        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }

        return $query;
    }

    private function geClassroomQuery($conditions, $limit = 0)
    {
        $midnight = new DateTime('midnight');
        $midnight = date_format($midnight, 'Y-m-d H:i:s');

        $conditions[] = "(cdd.date_begin >= '$midnight')";
        $conditions[] = '(cd.status = 0)';

        $excludePathcourse = $this->excludePathCourse();

        $query = 'SELECT c.idCourse course_id, c.name course_name, c.idCategory course_category_id, c.status course_status,
                          c.course_type, c.box_description course_box_description, c.img_course course_img_course, c.description, cd.name edition, 
                          cdd.date_begin course_date_begin, cdd.date_end course_date_end,
                          DATE_FORMAT(cdd.date_begin,"%H:%i") course_hour_begin, DATE_FORMAT(cdd.date_end,"%H:%i") course_hour_end'
            . ' FROM %lms_course AS c'
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse)'
            . ' JOIN %lms_course_date AS cd ON (c.idCourse = cd.id_Course)'
            . ' JOIN %lms_course_date_user AS cdu on (cdu.id_date = cd.id_date)'
            . ' JOIN %lms_course_date_day AS cdd ON (cd.id_date = cdd.id_date)'
            . ' WHERE ' . implode(' AND ', $conditions)
            . $excludePathcourse
            . '	GROUP BY course_id'
            . ' ORDER BY cdd.date_begin ASC';

        if ($limit > 0) {
            $query .= " LIMIT $limit";
        }

        return $query;
    }

    private function excludePathCourse()
    {
        // exclude course belonging to pathcourse in which the user is enrolled as a student
        $exclude_pathcourse = '';
        if (FormaLms\lib\Get::sett('on_path_in_mycourses') == 'off') {
            $id_user = (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId();
            $learning_path_enroll = $this->getUserCoursePathCourses($id_user);
            if (count($learning_path_enroll) >= 1) {
                $exclude_path_course = 'select idCourse from learning_courseuser where idUser=' . $id_user . ' and level <= 3 '
                    . ' and idCourse in (' . implode(',', $learning_path_enroll) . ')';
                $rs = $this->db->query($exclude_path_course);
                foreach ($rs as $data) {
                    $excl[] = $data['idCourse'];
                }
                $exclude_pathcourse = ' and c.idCourse not in (' . implode(',', $excl) . ' )';
            }
        }

        return $exclude_pathcourse;
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
            $courseDateData = [
                'id' => $date['date_id'],
                'code' => $date['date_code'],
                'name' => $date['date_name'],
                'description' => $date['date_description'],
                'status' => $date['date_status'],
                'startDate' => $date['date_start_date'],
                'endDate' => $date['date_end_date'],
                'showStartDate' => false,
                'showEndDate' => false,
            ];

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

            $now = new DateTime();
            $startDate = new DateTime($course['date_start_date']);
            $endDate = new DateTime($course['date_end_date']);

            if ($startDate >= $now) {
                $courseDateData['showStartDate'] = true;
            }
            if ($endDate >= $now) {
                $courseDateData['showEndDate'] = true;
            }

            $courseDateData['startDateString'] = $startDateString;
            $courseDateData['endDateString'] = $endDateString;

            $dates[] = $courseDateData;
        }

        return $dates;
    }

    protected function getDataFromCourse($course)
    {
        $courseData = parent::getDataFromCourse($course);

        $courseData['showStartDate'] = false;
        $courseData['showEndDate'] = false;

        $now = new DateTime();
        $startDate = new DateTime($course['course_date_begin']);
        $endDate = new DateTime($course['course_date_end']);

        if ($startDate >= $now) {
            $courseData['showStartDate'] = true;
        }
        if ($endDate >= $now) {
            $courseData['showEndDate'] = true;
        }

        $hours = '';
        if (!empty($courseData['hourBegin'])) {
            $hours .= (new DateTime($courseData['hourBegin']))->format('H:i');
        }
        if (!empty($courseData['hourEnd'])) {
            if (!empty($hours)) {
                $hours .= ' - ';
            }
            $hours .= (new DateTime($courseData['hourEnd']))->format('H:i');
        }
        $courseData['hours'] = $hours;

        return $courseData;
    }
}
