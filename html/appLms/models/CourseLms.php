<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class CourseLms extends Model
{
    protected $_t_order = false;

    protected $idCourse;

    public function __construct($idCourse = false)
    {
        parent::__construct();
        $this->idCourse = $idCourse;
    }

    /**
     * @throws CourseIdNotSetException
     */
    private function checkIdCourseOrThrow()
    {
        if (empty($this->idCourse)) {
            throw new CourseIdNotSetException();
        }
    }

    /**
     * This function return the correct order to use when you wish to diplay the a
     * course list for the user.
     *
     * @param <array> $t_name the table name to use as a prefix for the field, if false is passed no prefix will e used
     *                            we need a prefix for the course user rows and a prefix for the course table
     *                            array('u', 'c')
     *
     * @return string the order to use in a ORDER BY clausole
     */
    protected function _resolveOrder($t_name = ['', ''])
    {
        // read order for the course from database
        if ($this->_t_order == false) {
            $t_order = FormaLms\lib\Get::sett('tablist_mycourses', false);
            if ($t_order != false) {
                $arr_order_course = explode(',', $t_order);
                $arr_temp = [];
                foreach ($arr_order_course as $key => $value) {
                    switch ($value) {
                        case 'status':
                            $arr_temp[] = ' ?u.status ';
                            break;
                        case 'code':
                            $arr_temp[] = ' ?c.code ';
                            break;
                        case 'name':
                            $arr_temp[] = ' ?c.name ';
                            break;
                    }
                }
                $t_order = implode(', ', $arr_temp);
            } else {
                $t_order = '?u.status, ?c.name';
            }
            // save a class copy of the resolved list
            $this->_t_order = $t_order;
        }
        foreach ($t_name as $key => $value) {
            if ($value != '') {
                $t_name[$key] = $value . '.';
            }
        }

        return str_replace(['?u.', '?c.'], $t_name, $this->_t_order);
    }

    public function compileWhere($conditions, $params)
    {
        if (!is_array($conditions)) {
            return '1';
        }

        $where = [];
        $find = array_keys($params);
        foreach ($conditions as $key => $value) {
            $where[] = str_replace($find, $params, $value);
        }

        return implode(' AND ', $where);
    }

    public function findAll($conditions, $params)
    {
        $commonLabel = $this->session->get('id_common_label');
        $db = \FormaLms\db\DbConn::getInstance();
        $queryResult = $db->query(
            'SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.difficult, c.status AS course_status, c.course_edition, '
            . '	c.max_num_subscribe, c.create_date, '
            . '	c.direct_play, c.img_othermaterial, c.course_demo, c.use_logo_in_courselist, c.img_course, c.lang_code, '
            . '	c.course_vote, '
            . '	c.date_begin, c.date_end, c.valid_time, c.show_result, c.userStatusOp, c.auto_unsubscribe, c.unsubscribe_date_limit, '

            . '	cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting'

            . ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
            . ' WHERE ' . $this->compileWhere($conditions, $params)
            . ($commonLabel > 0 ? " AND c.idCourse IN (SELECT id_course FROM %lms_label_course WHERE id_common_label = '" . $commonLabel . "')" : '')
            . ' ORDER BY ' . $this->_resolveOrder(['cu', 'c'])
        );

        $result = [];
        $courses = [];
        foreach ($queryResult as $data) {
            $data['enrolled'] = 0;
            $data['numof_waiting'] = 0;
            $data['first_lo_type'] = false;
            $courses[] = $data['idCourse'];
            $result[$data['idCourse']] = $data;
        }

        if (!empty($courses)) {
            // find subscriptions
            $enrolledResponse = $db->query(
                'SELECT c.idCourse, COUNT(*) as numof_associated, SUM(waiting) as numof_waiting'
                . ' FROM %lms_course AS c '
                . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
                . ' WHERE c.idCourse IN (' . implode(',', $courses) . ') '
                . ' GROUP BY c.idCourse'
            );
            foreach ($enrolledResponse as $data) {
                $result[$data['idCourse']]['enrolled'] = $data['numof_associated'] - $data['numof_waiting'];
                $result[$data['idCourse']]['numof_waiting'] = $data['numof_waiting'];
            }

            // find first LO type
            $firstLearningObjectResponse = $db->query(
                'SELECT o.idOrg, o.idCourse, o.objectType FROM %lms_organization AS o '
                . " WHERE o.objectType != '' AND o.idCourse IN (" . implode(',', $courses) . ') '
                . ' GROUP BY o.idCourse ORDER BY o.path'
            );
            foreach ($firstLearningObjectResponse as $data) {
                $result[$data['idCourse']]['first_lo_type'] = $data['objectType'];
            }
        }

        return $result;
    }

    public static function getCourseParsedData($course)
    {
        $path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';
        $levels = CourseLevel::getTranslatedLevels();
        $infoEnroll = self::getInfoEnroll($course['idCourse'], \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());

        $parsedData = $course;

        $parsedData['name'] = strip_tags($parsedData['name']); // this for course boxes
        $parsedData['escaped_name'] = Util::purge($parsedData['name']); // and this for javascript calls

        if ($parsedData['use_logo_in_courselist']) {
            $parsedData['img_course'] = $parsedData['img_course'] && is_file($path_course . $parsedData['img_course']) ? $path_course . $parsedData['img_course'] : FormaLms\lib\Get::tmpl_path() . 'images/course/course_nologo.png';
        } else {
            $parsedData['img_course'] = FormaLms\lib\Get::tmpl_path() . 'images/course/course_nologo.png';
        }

        if (array_key_exists('nameCategory', $parsedData) && strlen($parsedData['nameCategory']) > 1) {
            $parsedData['nameCategory'] = substr($parsedData['nameCategory'], strripos($parsedData['nameCategory'], '/') + 1);
        }

        $parsedData['level_icon'] = array_key_exists('level', $parsedData) ? $parsedData['level'] : false;
        if ($parsedData['level_icon']) {
            $parsedData['level_text'] = array_key_exists($parsedData['level'], $parsedData) ? $levels[$parsedData['level']] : '';

        }

        //LRZ:  if validity day is setting
        //$date_first_access = fromDatetimeToTimestamp(self::getDateFirstAccess($course['idCourse'], \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt()));
        //if ($parsedData['valid_time'] > 0 && $date_first_access > 0) {
        //    $time_expired = $date_first_access + ($parsedData['valid_time'] * 24 * 3600);
        //    $parsedData['dateClosing_year'] = date('Y', $time_expired);
        //    $parsedData['dateClosing_month'] = Lang::t('_MONTH_' . substr('0' . date('m', $time_expired), -2), 'standard');
        //    $parsedData['dateClosing_day'] = date('d', $time_expired);
        //}
    

        $parsedData['is_enrolled'] = !empty($infoEnroll);
        if ($parsedData['is_enrolled']) {
            $parsedData['level'] = $infoEnroll['level'];
        }

        if ($parsedData['is_enrolled']) {
            $parsedData['canEnter'] = Man_Course::canEnterCourse($parsedData)['can'];
        } else {
            $parsedData['canEnter'] = false;
        }


        // se l'utente è in attesa dsi approvazione ne sovrascrivo i permessi derivati dalla sessione
        if($infoEnroll['waiting']) {
            $parsedData['canEnter'] = false;
        }

        if ($parsedData['date_begin'] !== null) {
            //se la data di inizio è superiore ad oggi
            if(new DateTime($parsedData['date_begin']) > new DateTime()) {
                $parsedData['canEnter'] = false;

            }
        }


        if ($parsedData['date_end'] !== null) {
            $date_closing = getdate(strtotime(Format::date($parsedData['date_end'], 'date')));
            if ($date_closing['year'] > 0) {
                $parsedData['dateClosing_year'] = $date_closing['year'];
                $parsedData['dateClosing_month'] = Lang::t('_MONTH_' . substr('0' . $date_closing['mon'], -2), 'standard');
                $parsedData['dateClosing_day'] = $date_closing['mday'];
            }

            $hour_end = ($parsedData['hour_end'] == -1) ? '23:59' : $parsedData['hour_end'];
            
           
            if(new DateTime($parsedData['date_end'] . ' ' . $hour_end .':59') < new DateTime()) {
                $parsedData['canEnter'] = false;

                //anche nel caso di semplice iscrizione metto un flag fake per impedire iscrizione
                $parsedData['subscribe_method'] = -1;
            }
        }


        $parsedData['editions'] = false;
        $parsedData['course_full'] = false;
        $parsedData['in_cart'] = false;
        $parsedData['waiting'] = array_key_exists('waiting', $infoEnroll) ? ($infoEnroll['waiting'] || $infoEnroll['status'] == 4) : false; // 4 = overbooked
        switch ($parsedData['course_type']) {
            case 'elearning':
                if (!empty($infoEnroll)) {
                    $parsedData['level'] = $infoEnroll['level'];
                    if (!$infoEnroll['waiting'] && $parsedData['canEnter']) {
                        $learningObject = self::getInfoLastLearningObject($parsedData['idCourse']);
                        if (array_key_exists('objectType', $learningObject) && $learningObject['objectType'] === 'scormorg' && $parsedData['level'] <= 3 && $parsedData['direct_play'] === 1) {
                            $parsedData['useLightBox'] = true;
                        } else {
                            $parsedData['useLightBox'] = false;
                        }
                        $parsedData['rel'] = $parsedData['useLightBox'] ? 'lightbox' : '';
                    }
                } else {
                    if ($parsedData['max_num_subscribe'] > 0) {
                        $parsedData['course_full'] = self::enrolledStudent($parsedData['idCourse']) >= $parsedData['max_num_subscribe'];
                    }
                }
                break;
            case 'classroom':
                $d = new DateManager();
                $parsedData['edition_exists'] = (count($d->getAvailableDate($parsedData['idCourse'])) > 0);
                if ($parsedData['is_enrolled']) {
                    $parsedData['editions'] = self::getAllClassDisplayInfo($parsedData['idCourse'], $parsedData);
                } else {
                    $parsedData['editions'] = (new CatalogLms())->courseSelectionInfo($parsedData['idCourse']);
                }
                break;
            default:
                break;
        }

        $parsedData['userCanUnsubscribe'] = self::userCanUnsubscribe($parsedData);

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if (!$parsedData['course_full'] && isset($parsedData['selling'])) {
            $parsedData['in_cart'] = ($session->has('lms_cart') && isset($session->get('lms_cart')[$parsedData['idCourse']]));
        }

        $parsedData['show_options'] = $parsedData['course_demo'] ||
            ($parsedData['userCanUnsubscribe'] && $parsedData['is_enrolled']);

        $parsedData['courseBoxEnabled'] = false;

        //se l'utente è superadmin sovrascrive quanto presente per vincoli di accesso al corso
        $userLevel = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
        if ($userLevel == ADMIN_GROUP_GODADMIN) {
            $parsedData['canEnter'] = true;
            $parsedData['subscribe_method'] = 2;
        }

        return $parsedData;
    }

    private static function getDateFirstAccess($id_course, $id_user)
    {
        $query = 'select date_first_access from learning_courseuser where idCourse=' . $id_course . ' and idUser=' . $id_user;

        list($date_first_access) = sql_fetch_row(sql_query($query));

        return $date_first_access;
    }

    // if in my courses, I am enrolled, so I need to unenroll if option enabled
    public static function isBoxEnabledForElearningAndClassroomInElearning($course)
    {
        return true;
    }

    public static function isBoxEnabledForElearningInCatalogue($course)
    {
        if ($course['is_enrolled']) {  // if enrolled always show enabled (I need to unenroll myself if option enabled)
            $courseBoxEnabled = true;
        } else {
            if ($course['course_full']) {
                if ($course['allow_overbooking']) {
                    $courseBoxEnabled = true;
                } else {
                    $courseBoxEnabled = false;
                }
            } else {
                if ((int)$course['selling'] === 0) {
                    switch ((int)$course['subscribe_method']) {
                        case 1:
                        case 2:
                            $courseBoxEnabled = true;
                            break;
                        case 0:
                        default:
                            $courseBoxEnabled = false;
                            break;
                    }
                } else {
                    $courseBoxEnabled = true;
                }
            }
        }

        return $courseBoxEnabled;
    }

    public static function isBoxEnabledForClassroomInCatalogue($course)
    {
        if ($course['edition_exists'] || $course['is_enrolled']) { // if enrolled always show enabled (I need to unenroll myself if option enabled)
            if ($course['is_enrolled']) {
                $courseBoxEnabled = true;
            } else {
                if ((int)$course['selling'] === 0) {
                    switch ((int)$course['subscribe_method']) {
                        case 1:
                        case 2:
                            $courseBoxEnabled = true;
                            break;
                        default:
                            $courseBoxEnabled = false;
                            break;
                    }
                } else {
                    $courseBoxEnabled = true;
                }
            }
        } else {
            $courseBoxEnabled = false;
        }

        return $courseBoxEnabled;
    }

    public static function enrolledStudent($idCourse)
    {
        $query = 'SELECT COUNT(*)'
            . ' FROM %lms_courseuser'
            . " WHERE idCourse = '" . $idCourse . "'";

        list($enrolled) = sql_fetch_row(sql_query($query));

        return $enrolled;
    }

    public static function getAllClassDisplayInfo($id_course, &$course_array)
    {
        require_once _lms_ . '/lib/lib.date.php';
        $dm = new DateManager();
        $cl = new ClassroomLms();
        $course_editions = $cl->getUserEditionsInfo(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), $id_course);
        $out = [];
        $course_array['next_lesson'] = '-';
        $next_lesson_array = [];
        $currentDate = new DateTime();

        if (array_key_exists($id_course, $course_editions)) {
            // user can be enrolled in more than one edition (as a teacher or crazy student....)
            foreach ($course_editions[$id_course] as $id_date => $obj_data) {
                // skip if course if over or not available
                try {
                    $end_course = new DateTime(Format::date($obj_data->date_max, 'datetime'));
                } catch (Exception $e) {
                    $end_course = clone $currentDate;
                }
                if (((int)$obj_data->status === 0) && ($end_course > $currentDate)) {
                    $out[$id_date]['code'] = $obj_data->code;
                    $out[$id_date]['name'] = $obj_data->name;
                    $out[$id_date]['date_begin'] = $obj_data->date_min;
                    $out[$id_date]['date_end'] = $obj_data->date_max;
                    $out[$id_date]['unsubscribe_date_limit'] = $obj_data->unsubscribe_date_limit;
                    $array_day = $dm->getDateDayDateDetails($obj_data->id_date);

                    foreach ($array_day as $id => $day) {
                        $out[$id_date]['days'][$id]['classroom'] = $day['classroom'];
                        $out[$id_date]['days'][$id]['day'] = Format::date($day['date_begin'], 'date');
                        $out[$id_date]['days'][$id]['begin'] = Format::date($day['date_begin'], 'time');
                        $out[$id_date]['days'][$id]['end'] = Format::date($day['date_end'], 'time');
                        $out[$id_date]['days'][$id]['full_date'] = $day['date_begin'];

                        try {
                            $nextLesson = new DateTime(Format::date($day['date_begin'], 'datetime'));
                        } catch (Exception $e) {
                            $nextLesson = '';
                        }
                        $next_lesson_array[$id_date . ',' . $id] = $nextLesson;
                    }
                }
            }
        }


        // calculating what's next lession will be; safe mode in case of more editions with different days
        if (count($next_lesson_array) > 0) {
            asort($next_lesson_array);
            foreach ($next_lesson_array as $k => $v) {
                if ($v > $currentDate) {
                    $j = explode(',', $k);
                    $course_array['next_lesson'] = $out[$j[0]]['days'][$j[1]]['day'] . ' ' . $out[$j[0]]['days'][$j[1]]['begin'];
                    break;
                }
            }
        }

        return $out;
    }

    public function courseSelectionInfo($id_course)
    {
        $query = 'SELECT name, selling, prize'
            . ' FROM %lms_course'
            . ' WHERE idCourse = ' . (int)$id_course;

        list($course_name, $selling, $price) = sql_fetch_row(sql_query($query));
        $classrooms = $this->classroom_man->getCourseDate($id_course, false);
        $classroom_not_confirmed = $this->classroom_man->getNotConfirmetDateForCourse($id_course);
        // cutting not confirmed classrooms
        $available_classrooms = array_diff_key($classrooms, $classroom_not_confirmed);
        $full_classrooms = $this->classroom_man->getFullDateForCourse($id_course);
        $overbooking_classrooms = $this->classroom_man->getOverbookingDateForCourse($id_course);
        foreach ($available_classrooms as $id_date => $classroom_info) {
            $available_classrooms[$id_date]['in_cart'] = ($this->session->has($id_course) && isset($this->session->get($id_course)['classroom'][$id_date]));
            $available_classrooms[$id_date]['selling'] = $selling;
            $available_classrooms[$id_date]['price'] = $price;
            $available_classrooms[$id_date]['days'] = $this->classroom_man->getDateDayDateDetails($id_date);
            $available_classrooms[$id_date]['full'] = isset($full_classrooms[$id_date]);
            $available_classrooms[$id_date]['overbooking'] = isset($overbooking_classrooms[$id_date]);
        }
        $teachers = array_intersect_key($this->course_man->getClassroomTeachers($id_course), $available_classrooms);

        return compact('available_classrooms', 'teachers', 'course_name');
    }

    public static function getInfoEnroll($idCourse, $idUser)
    {
        $responseData = [];
        $query = 'SELECT status, waiting, level'
            . ' FROM %lms_courseuser'
            . ' WHERE idCourse = ' . $idCourse
            . ' AND idUser = ' . $idUser;
        $result = \FormaLms\lib\Forma::db()->query($query);

        if (\FormaLms\lib\Forma::db()->affected_rows() > 0) {
            $responseData = \FormaLms\lib\Forma::db()->fetch_assoc($result);
        }

        return $responseData;
    }

    public static function getInfoLastLearningObject($idCourse)
    {
        $responseData = [];

        $query = "SELECT idOrg, idCourse, objectType FROM learning_organization WHERE objectType != '' AND idCourse  = $idCourse ORDER BY path limit 1";

        $result = \FormaLms\lib\Forma::db()->query($query);

        if (\FormaLms\lib\Forma::db()->affected_rows() > 0) {
            $responseData = \FormaLms\lib\Forma::db()->fetch_assoc($result);
        }

        return $responseData;
    }

    public static function userCanUnsubscribe(&$course)
    {
        $now = new DateTime();
        $defaultTrueDate = new DateTime('2999-01-01');

        if ($course['course_type'] == 'classroom') {
            if ((int)$course['auto_unsubscribe'] === 2) {
                $editionKey = array_key_first($course['editions']);

                if (array_key_exists('unsubscribe_date_limit', $course['editions'][$editionKey])) {
                    $unsub_date_limit = $course['editions'][$editionKey]['unsubscribe_date_limit'];
                    $unsub_date_limit = DateTime::createFromFormat('Y-m-d H:i:s', $unsub_date_limit);
                } else {
                    $unsub_date_limit = $defaultTrueDate;
                }
                $edition_not_started = true;
                $days = array_key_exists('days', $course['editions'][$editionKey]) ? $course['editions'][$editionKey]['days'] : [];
                foreach ($days as $k => $day) {
                    $next_day = $day['full_date'];
                    $next_day = DateTime::createFromFormat('Y-m-d H:i:s', $next_day);
                    $edition_not_started = $edition_not_started && ($now < $next_day);
                    if (!$edition_not_started) {
                        break;
                    }
                }

                return $now < $unsub_date_limit && $edition_not_started;
            } else {
                return false;
            }
        } else {
            // if course date end, cannot unenroll
       
            if ($course['date_end'] && $now > DateTime::createFromFormat('Y-m-d', $course['date_end'])) {
                return false;
            }

            $courseUnsubscribeDateLimit = (null !== $course['unsubscribe_date_limit'] ? DateTime::createFromFormat('Y-m-d H:i:s', $course['unsubscribe_date_limit']) : $defaultTrueDate);
            if (((int)$course['auto_unsubscribe'] === 2 || (int)$course['auto_unsubscribe'] === 1) && ($now < $courseUnsubscribeDateLimit)) {
                return true;
            }

            return false;
        }
    }

    /**
     * @throws CourseIdNotSetException
     */
    public function isHtmlFront(): bool
    {
        $this->checkIdCourseOrThrow();

        $sql_exist = 'select count(id_course) as exist from learning_htmlfront where id_course=' . $this->idCourse;
        $qres = sql_query($sql_exist);
        list($exist) = sql_fetch_row($qres);

        if ((int)$exist === 1) {
            return true;
        }

        return false;
    }

    /**
     * @throws CourseIdNotSetException
     */
    public function getHtmlFront(): string
    {
        $this->checkIdCourseOrThrow();

        $query = "SELECT textof FROM %lms_htmlfront WHERE id_course = '$this->idCourse'";
        $result = \FormaLms\lib\Forma::db()->query($query);

        foreach (\FormaLms\lib\Forma::db()->fetch_assoc($result) as $item) {
            return (string)$item['textof'];
        }

        return '';
    }

    /**
     * @param $html
     *
     * @throws CourseIdNotSetException
     */
    public function saveHtmlFront($html): bool
    {
        $this->checkIdCourseOrThrow();

        if ($this->isHtmlFront()) {
            $query = "UPDATE %lms_htmlfront SET textof = '" . addslashes($html) . "' WHERE id_course = $this->idCourse";
        } else {
            $query = "INSERT INTO %lms_htmlfront ( id_course, textof) VALUES ($this->idCourse,'" . addslashes($html) . "')";
        }

        $result = \FormaLms\lib\Forma::db()->query($query);

        if ($result === false) {
            return false;
        }

        return true;
    }

    public function deleteHtmlFront()
    {
        $this->checkIdCourseOrThrow();

        $query = "DELETE FROM %lms_htmlfront WHERE id_course = $this->idCourse";

        $result = \FormaLms\lib\Forma::db()->query($query);

        if ($result === false) {
            return false;
        }

        return true;
    }

    // get info partecipat
    // course_type
    // level
    public function getInfoPartecipant($idCourse)
    {
        $query = 'select course_type, level from 
            learning_course lc, learning_courseuser lcu
            where lc.idCourse=lcu.idCourse
            and lc.idCourse=' . $idCourse . ' and idUser=' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();

        list($course_type, $level) = sql_fetch_row(sql_query($query));

        $out = [];
        $out['course_type'] = $course_type;
        $out['level'] = $level;

        return $out;
    }

    public static function getIdUserOfLevelDate($id_course, $level, $id_date)
    {
        $users = [];

        if ($level == 7) {
            $query_courseuser = 'SELECT cdu.id_user 
                FROM %lms_course_date_user AS cdu, %lms_courseuser lcu
                where id_date=' . $id_date . ' and lcu.idCourse=' . $id_course . ' and id_user=idUser and level=' . $level . ' 
                UNION
                SELECT cdu.id_user
                 FROM %lms_course_date_user AS cdu, %lms_courseuser lcu
                where lcu.idCourse=' . $id_course . ' and id_user=idUser and level=' . $level;
        } else {
            $query_courseuser = 'SELECT cdu.id_user 
                FROM %lms_course_date_user AS cdu, %lms_courseuser lcu
                where id_date=' . $id_date . ' and lcu.idCourse=' . $id_course . ' and id_user=idUser and level=' . $level;
        }

        $courseuserResult = sql_query($query_courseuser);
        foreach ($courseuserResult as $item) {
            $users[$item['id_user']] = $item['id_user'];
        }

        return $users;
    }

    public static function getInfoDate($idDate)
    {
        $query = 'select code, name from %lms_course_date where id_date=' . $idDate;

        list($code, $name) = sql_fetch_row(sql_query($query));

        $out = [];
        $out['code'] = $code;
        $out['name'] = $name;

        return $out;
    }

    public static function getMyDateCourse($idCourse)
    {
        $query = 'select lcd.id_date from %lms_course_date lcd, %lms_course_date_user lcdu
            where 
            lcd.id_date = lcdu.id_date
            and id_user = ' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . ' and lcd.id_course=' . $idCourse;

        list($id_date) = sql_fetch_row(sql_query($query));

        return $id_date;
    }

    public function setCourseDateCompleted($idUser)
    {
        $query = 'UPDATE %lms_courseuser '
            . " SET date_complete = '" . date('Y-m-d H:i:s') . "'"
            . ' WHERE idCourse = ' . $this->idCourse
            . ' AND id_user = ' . $idUser;

        return sql_query($query);
    }
}
