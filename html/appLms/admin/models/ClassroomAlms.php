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

defined('IN_FORMA') or exit('Direct access is forbidden');

class ClassroomAlms extends Model
{
    protected $db;
    protected $acl_man;
    public $classroom_man;
    public $course_man;

    protected $id_course;
    protected $id_date;

    public function __construct($id_course = 0, $id_date = 0)
    {
        parent::__construct();
        require_once _lms_ . '/lib/lib.date.php';
        require_once _lms_ . '/lib/lib.course.php';

        $this->id_course = $id_course;
        $this->id_date = $id_date;
        $this->db = DbConn::getInstance();
        $this->classroom_man = new DateManager();
        $this->course_man = new Man_Course();
        $this->acl_man = &Docebo::user()->getAclManager();
        parent::__construct();
    }

    public function getPerm()
    {
        return ['view' => 'standard/view.png'];
    }

    public function getClassroomsNumber($filter = false)
    {
        $categories = false;
        $filter_text = false;
        $filter_waiting = false;
        if ($filter) {
            if (isset($filter['id_category'])) {
                if (isset($filter['descendants'])) {
                    $categories = $this->getCategoryDescendants($filter['id_category']);
                } else {
                    $categories = $filter['id_category'];
                }
            }
            if (isset($filter['text'])) {
                $filter_text = $filter['text'];
            }

            if (isset($filter['waiting']) && $filter['waiting'] == 1) {
                $filter_waiting = true;
            }
        }

        return $this->course_man->getClassroomsNumber($categories, $filter_text, $filter_waiting);
    }

    public function loadCourse($start_index, $results, $sort, $dir, $filter = false)
    {
        $categories = false;
        $filter_text = false;
        $filter_waiting = false;
        if ($filter) {
            if (isset($filter['id_category'])) {
                if (isset($filter['descendants']) && $filter['descendants'] != 0) {
                    $categories = $this->getCategoryDescendants($filter['id_category']);
                } else {
                    $categories = $filter['id_category'];
                }
            }
            if (isset($filter['text']) && $filter['text'] !== '') {
                $filter_text = $filter['text'];
            }

            if (isset($filter['waiting']) && $filter['waiting'] == 1) {
                $filter_waiting = true;
            }
        }

        return $this->course_man->getClassrooms($start_index, $results, $sort, $dir, $categories, $filter_text, $filter_waiting);
    }

    public function getCategoryDescendants($id_category)
    {
        $output = [];

        if ($id_category != 0) {
            $query = 'SELECT iLeft, iRight FROM %lms_category WHERE idCategory=' . (int)$id_category;
            $res = $this->db->query($query);
            list($left, $right) = $this->db->fetch_row($res);

            $query = 'SELECT idCategory FROM %lms_category WHERE iLeft>=' . $left . ' AND iRight<=' . $right;
            $res = $this->db->query($query);
            while (list($id_cat) = $this->db->fetch_row($res)) {
                $output[] = $id_cat;
            }
        } else {
            $output[] = 0;

            $query = 'SELECT idCategory FROM %lms_category';
            $res = $this->db->query($query);
            while (list($id_cat) = $this->db->fetch_row($res)) {
                $output[] = $id_cat;
            }
        }

        return $output;
    }

    public function getIdCourse()
    {
        return $this->id_course;
    }

    public function getIdDate()
    {
        return $this->id_date;
    }

    public function getCourseEditionNumber()
    {
        return $this->classroom_man->getDateNumberNoLimit($this->id_course);
    }

    public function loadCourseEdition($start_index, $results, $sort, $dir)
    {
        return $this->classroom_man->getCourseEdition($this->id_course, $start_index, $results, $sort, $dir);
    }

    public function getCourseInfo()
    {
        return $this->course_man->getCourseInfo($this->id_course);
    }

    public function getStatusForDropdown()
    {
        return $this->classroom_man->getStatusForDropdown();
    }

    public function getTestTypeForDropdown()
    {
        return $this->classroom_man->getTestTypeForDropdown();
    }

    public function getClassroomForDropdown()
    {
        return $this->classroom_man->getClassroomForDropdown();
    }

    public function getDateInfoFromPost()
    {
        $res = [];

        $res['customFields'] = [];
        $res['code'] = FormaLms\lib\Get::req('code', DOTY_MIXED, '');
        $res['name'] = FormaLms\lib\Get::req('name', DOTY_MIXED, '');
        $res['max_par'] = FormaLms\lib\Get::req('max_par', DOTY_INT, 0);
        $res['price'] = FormaLms\lib\Get::req('price', DOTY_MIXED, '');
        $res['overbooking'] = FormaLms\lib\Get::req('overbooking', DOTY_INT, 0);
        $res['test'] = FormaLms\lib\Get::req('test', DOTY_INT, 0);
        $res['status'] = FormaLms\lib\Get::req('status', DOTY_INT, 0);
        $res['date_selected'] = FormaLms\lib\Get::req('date_selected', DOTY_MIXED, '');
        $res['mediumTime'] = FormaLms\lib\Get::req('mediumTime', DOTY_INT, 0);
        $res['description'] = FormaLms\lib\Get::req('description', DOTY_MIXED, '');
        $res['sub_start_date'] = FormaLms\lib\Get::req('sub_start_date', DOTY_MIXED, '');
        $res['sub_end_date'] = FormaLms\lib\Get::req('sub_end_date', DOTY_MIXED, '');
        $res['unsubscribe_date_limit'] = FormaLms\lib\Get::req('unsubscribe_date_limit', DOTY_MIXED, '');
        $res['customFields'] = array_replace($res['customFields'], FormaLms\lib\Get::req('textfield', DOTY_MIXED, []));
        $res['customFields'] = array_replace($res['customFields'], FormaLms\lib\Get::req('dropdown', DOTY_MIXED, []));
        $array_day = [];

        if ($res['date_selected'] !== '') {
            $array_day = explode(',', $res['date_selected']);
        }

        $res['array_day'] = $array_day;

        return $res;
    }

    public function getDayTable($array_day, $id_date = 0)
    {
        require_once _base_ . '/lib/lib.table.php';

        $tb = new Table(0, Lang::t('_DETAILS', 'course'), Lang::t('_DETAILS', 'course'));

        $cont_h = [Lang::t('_DAY', 'course'),
            Lang::t('_HOUR_BEGIN', 'course'),
            Lang::t('_PAUSE_BEGIN', 'course'),
            Lang::t('_PAUSE_END', 'course'),
            Lang::t('_HOUR_END', 'course'),
            Lang::t('_CLASSROOM', 'course'),];

        $type_h = ['align_center', 'align_center', 'align_center', 'align_center'];

        $classroom_array = $this->classroom_man->getClassroomForDropdown();

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $days = [];
        if ((int)$id_date > 0) {
            $days = $this->classroom_man->getDateDayForControl($id_date);
        }

        $arrayLenght = count($array_day);
        for ($i = 0; $i < $arrayLenght; ++$i) {
            if (isset($days[$array_day[$i]])) {
                $b_hours = $days[$array_day[$i]]['b_hours'];
                $b_minutes = $days[$array_day[$i]]['b_minutes'];
                $pb_hours = $days[$array_day[$i]]['pb_hours'];
                $pb_minutes = $days[$array_day[$i]]['pb_minutes'];
                $pe_hours = $days[$array_day[$i]]['pe_hours'];
                $pe_minutes = $days[$array_day[$i]]['pe_minutes'];
                $e_hours = $days[$array_day[$i]]['e_hours'];
                $e_minutes = $days[$array_day[$i]]['e_minutes'];
                $classroom = $days[$array_day[$i]]['classroom'];
            } else {
                $b_hours = false;
                $b_minutes = false;
                $pb_hours = false;
                $pb_minutes = false;
                $pe_hours = false;
                $pe_minutes = false;
                $e_hours = false;
                $e_minutes = false;
                $classroom = 0;
            }

            $classroom_array_checked = [];
            $occupied = $this->getOccupiedClassrooms($array_day[$i]);
            foreach ($classroom_array as $key => $value) {
                $classroom_array_checked[$key] = (in_array($key, $occupied) && $key != 0 ? '* ' : '') . $value;
            }

            $tb->addBody([Format::date($array_day[$i], 'date'),
                Form::getInputDropdown('', 'b_hours_' . $i, 'b_hours_' . $i, $this->classroom_man->getHours(), $b_hours, false) . ' : ' . Form::getInputDropdown('', 'b_minutes_' . $i, 'b_minutes_' . $i, $this->classroom_man->getMinutes(), $b_minutes, false),
                Form::getInputDropdown('', 'pb_hours_' . $i, 'pb_hours_' . $i, $this->classroom_man->getHours(), $pb_hours, false) . ' : ' . Form::getInputDropdown('', 'pb_minutes_' . $i, 'pb_minutes_' . $i, $this->classroom_man->getMinutes(), $pb_minutes, false),
                Form::getInputDropdown('', 'pe_hours_' . $i, 'pe_hours_' . $i, $this->classroom_man->getHours(), $pe_hours, false) . ' : ' . Form::getInputDropdown('', 'pe_minutes_' . $i, 'pe_minutes_' . $i, $this->classroom_man->getMinutes(), $pe_minutes, false),
                Form::getInputDropdown('', 'e_hours_' . $i, 'e_hours_' . $i, $this->classroom_man->getHours(), $e_hours, false) . ' : ' . Form::getInputDropdown('', 'e_minutes_' . $i, 'e_minutes_' . $i, $this->classroom_man->getMinutes(), $e_minutes, false),
                Form::getInputDropdown('', 'classroom_' . $i, 'classroom_' . $i, $classroom_array_checked, $classroom, false),
            ]);
        }
        if (count($array_day) > 1) {
            $tb->addBody([
                Lang::t('_SET', 'course'),
                Form::getInputDropdown('', 'b_hours', 'b_hours', $this->classroom_man->getHours(), '00', false) . ' : ' . Form::getInputDropdown('', 'b_minutes', 'b_minutes', $this->classroom_man->getMinutes(), '00', false),
                Form::getInputDropdown('', 'pb_hours', 'pb_hours', $this->classroom_man->getHours(), '00', false) . ' : ' . Form::getInputDropdown('', 'pb_minutes', 'pb_minutes', $this->classroom_man->getMinutes(), '00', false),
                Form::getInputDropdown('', 'pe_hours', 'pe_hours', $this->classroom_man->getHours(), '00', false) . ' : ' . Form::getInputDropdown('', 'pe_minutes', 'pe_minutes', $this->classroom_man->getMinutes(), '00', false),
                Form::getInputDropdown('', 'e_hours', 'e_hours', $this->classroom_man->getHours(), '00', false) . ' : ' . Form::getInputDropdown('', 'e_minutes', 'e_minutes', $this->classroom_man->getMinutes(), '00', false),
                Form::getInputDropdown('', 'classroom', 'classroom', $classroom_array, 0, false),
            ]);
        }

        $table = '<script type="text/javascript">'
            . 'var num_day = ' . count($array_day) . ';'
            . 'YAHOO.util.Event.on("b_hours", "change", changeBeginHours);'
            . 'YAHOO.util.Event.on("b_minutes", "change", changeBeginMinutes);'
            . 'YAHOO.util.Event.on("pb_hours", "change", changePBeginHours);'
            . 'YAHOO.util.Event.on("pb_minutes", "change", changePBeginMinutes);'
            . 'YAHOO.util.Event.on("pe_hours", "change", changePEndHours);'
            . 'YAHOO.util.Event.on("pe_minutes", "change", changePEndMinutes);'
            . 'YAHOO.util.Event.on("e_hours", "change", changeEndHours);'
            . 'YAHOO.util.Event.on("e_minutes", "change", changeEndMinutes);'
            . 'YAHOO.util.Event.on("classroom", "change", changeClassroom);'
            . '</script>'
            . $tb->getTable();

        return $table;
    }

    public function saveNewDate()
    {
        $date_info = $this->getDateInfoFromPost();

        $array_day_tmp = !empty($date_info['date_selected']) ? explode(',', $date_info['date_selected']) : [];
        $array_day = [];

        $countDays = count($array_day_tmp);
        for ($i = 0; $i < $countDays; ++$i) {
            $array_day[$i]['date_begin'] = $array_day_tmp[$i] . ' ' . $_POST['b_hours_' . $i] . ':' . $_POST['b_minutes_' . $i] . ':00';
            $array_day[$i]['pause_begin'] = $array_day_tmp[$i] . ' ' . $_POST['pb_hours_' . $i] . ':' . $_POST['pb_minutes_' . $i] . ':00';
            $array_day[$i]['pause_end'] = $array_day_tmp[$i] . ' ' . $_POST['pe_hours_' . $i] . ':' . $_POST['pe_minutes_' . $i] . ':00';
            $array_day[$i]['date_end'] = $array_day_tmp[$i] . ' ' . $_POST['e_hours_' . $i] . ':' . $_POST['e_minutes_' . $i] . ':00';
            $array_day[$i]['classroom'] = $_POST['classroom_' . $i];
        }

        $sub_start_date = trim($date_info['sub_start_date']);
        $sub_end_date = trim($date_info['sub_end_date']);
        $unsubscribe_date_limit = trim($date_info['unsubscribe_date_limit']);

        $sub_start_date = (!empty($sub_start_date) ? Format::dateDb($sub_start_date, 'date') : '0000-00-00') . ' 00:00:00';
        $sub_end_date = (!empty($sub_end_date) ? Format::dateDb($sub_end_date, 'date') : '0000-00-00') . ' 00:00:00';
        $unsubscribe_date_limit = (!empty($unsubscribe_date_limit) ? Format::dateDb($unsubscribe_date_limit, 'date') : '0000-00-00') . ' 00:00:00';

        $id_date = $this->classroom_man->insDate($this->id_course, $date_info['code'], $date_info['name'], $date_info['description'], $date_info['mediumTime'], $date_info['max_par'], $date_info['price'], $date_info['overbooking'], $date_info['status'], $date_info['test'],
            $sub_start_date, $sub_end_date, $unsubscribe_date_limit);

        if (isset($date_info['customFields'])) {
            foreach ($date_info['customFields'] as $idField => $customEntry) {
                $this->classroom_man->addCustomFieldValue($id_date, $idField, $customEntry);
            }
        }

        if ($id_date) {
            if ($countDays > 0) {
                return $this->classroom_man->updateDateDay($id_date, $array_day);
            }

            return $id_date;
        }

        return false;
    }

    public function getDateInfo()
    {
        if (isset($_POST['back'])) {
            $date_info = [];
        } else {
            $date_info = $this->classroom_man->getDateInfo($this->id_date);
        }

        return $date_info;
    }

    public function getCustomFieldsValue($idDate, $idField)
    {
        return $this->classroom_man->getCustomFieldValue($idDate, $idField);
    }

    public function getDateDay($idDate = null)
    {
        return $this->classroom_man->getDateDay($idDate ?? $this->id_date);
    }

    public function getAllDateDay($idDate = null)
    {
        return $this->classroom_man->getAllDateDay($idDate ?? $this->id_date);
    }

    public function getDateString()
    {
        $array_day = $this->getDateDay();

        $date_string = '';
        $start_mounth = '';

        $first = true;

        $countDays = count($array_day);
        for ($i = 0; $i < $countDays; ++$i) {
            if ($first) {
                $first = false;
                $start_mounth = substr($array_day[$i]['date_begin'], 5, 2) . '/' . substr($array_day[$i]['date_begin'], 0, 4);
                $date_string .= substr($array_day[$i]['date_begin'], 5, 2) . '/' . substr($array_day[$i]['date_begin'], 8, 2) . '/' . substr($array_day[$i]['date_begin'], 0, 4);
            } else {
                $date_string .= ',' . substr($array_day[$i]['date_begin'], 5, 2) . '/' . substr($array_day[$i]['date_begin'], 8, 2) . '/' . substr($array_day[$i]['date_begin'], 0, 4);
            }
        }

        return $date_string;
    }

    public function updateDate()
    {
        $date_info = $this->getDateInfoFromPost();

        $array_day_tmp = !empty($date_info['date_selected']) ? explode(',', $date_info['date_selected']) : [];
        $array_day = [];

        $countDays = count($array_day_tmp);
        for ($i = 0; $i < $countDays; ++$i) {
            $array_day[$i]['date_begin'] = $array_day_tmp[$i] . ' ' . $_POST['b_hours_' . $i] . ':' . $_POST['b_minutes_' . $i] . ':00';
            $array_day[$i]['pause_begin'] = $array_day_tmp[$i] . ' ' . $_POST['pb_hours_' . $i] . ':' . $_POST['pb_minutes_' . $i] . ':00';
            $array_day[$i]['pause_end'] = $array_day_tmp[$i] . ' ' . $_POST['pe_hours_' . $i] . ':' . $_POST['pe_minutes_' . $i] . ':00';
            $array_day[$i]['date_end'] = $array_day_tmp[$i] . ' ' . $_POST['e_hours_' . $i] . ':' . $_POST['e_minutes_' . $i] . ':00';
            $array_day[$i]['classroom'] = $_POST['classroom_' . $i];
        }

        if (isset($date_info['customFields'])) {
            foreach ($date_info['customFields'] as $idField => $customEntry) {
                $this->classroom_man->addCustomFieldValue($this->id_date, $idField, $customEntry);
            }
        }

        $res = $this->classroom_man->upDate($this->id_date, $date_info['code'], $date_info['name'], $date_info['description'], $date_info['mediumTime'], $date_info['max_par'], $date_info['price'], $date_info['overbooking'], $date_info['status'], $date_info['test'], Format::dateDb($date_info['sub_start_date'], 'date') . ' 00:00:00', Format::dateDb($date_info['sub_end_date'], 'date') . ' 00:00:00', Format::dateDb($date_info['unsubscribe_date_limit'], 'date') . ' 00:00:00');

        if ($res) {
            if ($countDays > 0) {
                return $this->classroom_man->updateDateDay($this->id_date, $array_day);
            }

            return true;
        }

        return false;
    }

    public function removeDateDay($days)
    {
        return $this->classroom_man->removeDateDay($this->id_date, $days);
    }

    public function updateDateDays($days)
    {
        return $this->classroom_man->updateDateDay($this->id_date, $days);
    }

    public function delClassroom($customFields = [])
    {
        return $this->classroom_man->delDate($this->id_date, $customFields);
    }

    public function delCourse()
    {
        $classroom = $this->classroom_man->getDateIdForCourse($this->id_course);

        foreach ($classroom as $id_date) {
            if (!$this->classroom_man->delDate($id_date)) {
                return false;
            }
        }

        require_once _lms_ . '/admin/modules/course/course.php';

        return removeCourse($this->id_course);
    }

    public function getTestType()
    {
        return $this->classroom_man->getTestType($this->id_date);
    }

    public function getPresenceTable()
    {
        $user = $this->classroom_man->getUserForPresence($this->id_date, $this->id_course);
        $day = $this->getDateDay($this->id_date);
        $test_type = $this->getTestType();
        $user_presence = $this->classroom_man->getUserPresenceForDate($this->id_date);

        $tb = new Table(0, Lang::t('_ATTENDANCE', 'course'), Lang::t('_ATTENDANCE', 'course'));

        $cont_h = [Lang::t('_USERNAME', 'course'),
            Lang::t('_FULLNAME', 'course'),];

        $type_h = ['', ''];

        foreach ($day as $id_day => $day_info) {
            $cont_h[] = Format::date($day_info['date_begin'], 'date') . '<br />'
                . '<a href="javascript:;" onClick="checkAllDay(' . $id_day . ')">' . FormaLms\lib\Get::img('standard/checkall.png', Lang::t('_CHECK_ALL_DAY', 'presence')) . '</a>'
                . ' '
                . '<a href="javascript:;" onClick="unCheckAllDay(' . $id_day . ')">' . FormaLms\lib\Get::img('standard/uncheckall.png', Lang::t('_UNCHECK_ALL_DAY', 'presence')) . '</a>';
            $type_h[] = 'img-cell';
        }

        $cont_h[] = '';
        $type_h[] = 'img-cell';

        if ($test_type == _DATE_TEST_TYPE_PAPER) {
            $cont_h[] = Lang::t('_SCORE', 'course');
            $type_h[] = 'img-cell';
        }

        $cont_h[] = Lang::t('_NOTES', 'course');
        $type_h[] = 'img-cell';

        $tb->setColsStyle($type_h);
        $tb->addHead($cont_h);

        $array_user_id = [];

        foreach ($user as $id_user => $user_info) {
            reset($day);

            $array_user_id[] = $id_user;

            $cont = [];

            $cont[] = $user_info['userid'];
            $cont[] = $user_info['lastname'] . ' ' . $user_info['firstname'];

            foreach ($day as $id_day => $day_info) {
                if (isset($user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]) && $user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]['presence'] == 1) {
                    $presence = true;
                } elseif (isset($user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]) && $user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]['presence'] == 0) {
                    $presence = false;
                } else {
                    $presence = false;
                }

                $cont[] = Form::getInputCheckbox('date_' . $id_day . '_' . $id_user, 'date_' . $id_day . '_' . $id_user, 1, $presence, false);
            }

            $cont[] = '<a href="javascript:;" onClick="checkAllUser(' . $id_user . ')">' . FormaLms\lib\Get::img('standard/checkall.png', Lang::t('_CHECK_ALL_USER', 'presence')) . '</a>'
                . '<br />'
                . '<a href="javascript:;" onClick="unCheckAllUser(' . $id_user . ')">' . FormaLms\lib\Get::img('standard/uncheckall.png', Lang::t('_UNCHECK_ALL_USER', 'presence')) . '</a>';

            if ($test_type == _DATE_TEST_TYPE_PAPER) {
                if (isset($user_presence[$id_user]['0000-00-00']) && $user_presence[$id_user]['0000-00-00']['presence'] == 1) {
                    $passed = true;
                } else {
                    $passed = false;
                }

                //$cont[] = Form::getTextfield('', 'score_'.$id_user, 'score_'.$id_user, 255, (isset($user_presence[$id_user]['0000-00-00']['score']) ? $user_presence[$id_user]['0000-00-00']['score'] : '0'));
                $cont[] = Form::getInputTextfield('', 'score_' . $id_user, 'score_' . $id_user, (isset($user_presence[$id_user]['0000-00-00']['score']) ? $user_presence[$id_user]['0000-00-00']['score'] : '0'), Lang::t('_SCORE', 'course'), 255, '');
            }

            //$cont[] = Form::getSimpleTextarea('', 'note_'.$id_user, 'note_'.$id_user, (isset($user_presence[$id_user]['0000-00-00']['note']) ? $user_presence[$id_user]['0000-00-00']['note'] : ''), false, false, false, 2);
            $cont[] = Form::getInputTextarea('note_' . $id_user, 'note_' . $id_user, (isset($user_presence[$id_user]['0000-00-00']['note']) ? $user_presence[$id_user]['0000-00-00']['note'] : ''), '', 5, 22);
            $tb->addBody($cont);
        }

        return $tb->getTable();
    }

    public function savePresence()
    {
        $score_min = FormaLms\lib\Get::req('score_min', DOTY_INT, 0);

        $user = $this->classroom_man->getUserForPresence($this->id_date);
        $days = $this->getDateDay($this->id_date);
        $test_type = $this->classroom_man->getTestType($this->id_date);

        foreach ($user as $id_user => $user_info) {
            $user[$id_user]['score'] = FormaLms\lib\Get::req('score_' . $id_user, DOTY_INT, 0);
            $user[$id_user]['note'] = FormaLms\lib\Get::req('note_' . $id_user, DOTY_MIXED, '');
            $user[$id_user]['day_presence'] = [];


            foreach ($days as $index => $day) {
                $requestString = 'date_' . $day['id'] . '_' . $id_user;

                $user[$id_user]['day_presence'][$day['id']] = FormaLms\lib\Get::req($requestString, DOTY_INT, 0);
            }
        }

        return $this->classroom_man->insDatePresence($this->id_course, $this->id_date, $user, $days, $score_min);
    }

    /**
     * Check if the days and classroom selection is available: return the intersecation
     * and if availability is ok the result will be an empty array.
     *
     * @param <type> $info
     *
     * @return array
     */
    public function checkDateAvailability($info)
    {
        $output = [];
        if (!empty($info)) {
            //get class occupation
            $classrooms = [];
            foreach ($info as $day) {
                if ($day['classroom'] > 0 && !in_array($day['classroom'], $classrooms)) {
                    $classrooms[] = $day['classroom'];
                }
            }

            if (!empty($classrooms)) {
                $query = 'SELECT * FROM %lms_course_date_day WHERE classroom IN (' . implode(',', $classrooms) . ')';
                $res = sql_query($query);
                while ($obj = sql_fetch_object($res)) {
                }
            }
        }

        return $output;
    }

    /**
     * Check if at any date the classrooms are occupied.
     *
     * @param <type> $date
     *
     * @return array
     */
    public function getOccupiedClassrooms($date)
    {
        if (!$date) {
            return false;
        }
        if (!is_string($date) || strlen($date) < 10) {
            return false;
        }
        $date = substr($date, 0, 10);
        $output = [];
        $query = 'SELECT DISTINCT(classroom) FROM %lms_course_date_day '
            . " WHERE date_begin <= '" . $date . " 23:59:59' AND date_end >= '" . $date . " 00:00:00'";
        $res = sql_query($query);
        while (list($id_classroom) = sql_fetch_row($res)) {
            $output[] = $id_classroom;
        }

        return $output;
    }

    public function sendCalendarToAllSubscribers()
    {
        $subscriptionModel = new SubscriptionAlms($this->id_course, false, $this->id_date);

        $users = $subscriptionModel->loadUser();

        $calendarMailer = new CalendarMailer();
        foreach ($users as $user) {
            $user = Docebo::user()->getAclManager()->getUserMappedData(Docebo::user()->getAclManager()->getUser($user['id_user'], false));

            $calendar = CalendarManager::getCalendarDataContainerForDateDays((int)$this->id_course, (int)$this->id_date, (int)$user['idst']);

            $calendarMailer->sendCalendarToUser($calendar, $user);
        }
    }
}
