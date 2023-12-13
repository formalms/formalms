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

class CoursepathLmsController extends LmsController
{
    public $name = 'coursepath';

    public $ustatus = [];
    public $cstatus = [];

    public $path_course = '';

    protected $model;

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {
        YuiLib::load('base,tabview');

        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.subscribe.php';
        require_once _lms_ . '/lib/lib.levels.php';

        $this->cstatus = [
            CST_PREPARATION => '_CST_PREPARATION',
            CST_AVAILABLE => '_CST_AVAILABLE',
            CST_EFFECTIVE => '_CST_CONFIRMED',
            CST_CONCLUDED => '_CST_CONCLUDED',
            CST_CANCELLED => '_CST_CANCELLED',
        ];

        $this->ustatus = [
            _CUS_CONFIRMED => '_T_USER_STATUS_CONFIRMED',
            _CUS_SUBSCRIBED => '_T_USER_STATUS_SUBS',
            _CUS_BEGIN => '_T_USER_STATUS_BEGIN',
            _CUS_END => '_T_USER_STATUS_END',
        ];

        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';

        $this->model = new CoursepathLms();
    }

    public function show()
    {
        $this->render('_tabs_block', []);
    }

    public function all()
    {
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $filter_year = FormaLms\lib\Get::req('filter_year', DOTY_INT, '');
        $filter_status = FormaLms\lib\Get::req('filter_status', DOTY_STRING, '');

        $conditions = '';
        if (!empty($filter_text)) {
            $conditions[] = "cp.path_name LIKE '%" . addslashes($filter_text) . "%'";
        }

        if ($filter_year != 0) {
            $conditions[] = "(cpu.date_assign >= '" . $filter_year . "-00-00 00:00:00' AND cpu.date_assign <= '" . $filter_year . "-12-31 23:59:59')";
        }

        $user_coursepath = $this->model->getCoursepath(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), $conditions, $filter_status);
        if (count($user_coursepath) > 0) {
            $coursepath_courses = $this->parseDetailsCourse($this->model->getCoursepathCourseDetails(array_keys($user_coursepath)));
            $path_details = $coursepath_courses['path_details'];
            $starting_courses = $coursepath_courses['starting_course'];
            $this->render('coursepathlist', ['type' => 'all', 'user_coursepath' => $user_coursepath,
                                                       'coursepath_courses' => $path_details,'starting_courses'  =>  $starting_courses ]);
        } else {
            echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
        }
    }

    private function parseDetailsCourse($result){
        $res = [];
        $starting_course = [];
        while ($row = sql_fetch_assoc($result)) {
            $can_enter = Man_Course::canEnterCourse($row, (int)$row['id_path']);
            $row['course_unlocked'] = ($can_enter['can'] == true && $can_enter['reason'] != 'prerequisites');
            $row['can_enter'] = $can_enter['can'];
            if ($row['course_type'] === 'elearning') {
                $row['course_type'] = Lang::t('_COURSE_TYPE_ELEARNING', 'course');
            } else {
                $row['course_type'] =  Lang::t('_CLASSROOM_COURSE', 'cart');
            }
            if ($row['user_status'] == _CUS_END) {
                $row['ico_style'] = 'subs_actv';
                $row['ico_text'] = Lang::t('_COURSE_COMPLETED', 'coursepath');
            } elseif (!$row['course_unlocked']) {
                $row['ico_style'] = 'subs_locked';
                $row['ico_text'] = Lang::t('_COURSE_LOCKED', 'coursepath');
            } else {
                $row['ico_style']  = 'subs_noac';
                $row['ico_text'] = Lang::t('_COURSE_ACTIVE', 'coursepath');
            }
            if ($row['can_enter'] &&  $row['user_status'] != _CUS_END &&  $starting_course[$row['id_path']] == null) {
                $starting_course[$row['id_path']] = ['name'=>$row['name'], 'idCourse'=>$row['idCourse'], 'course_unlocked'=>$row['course_unlocked']];
            }
            $res[$row['id_path']][$row['idCourse']] = $row;
        }
        return ['path_details'=>$res, 'starting_course'=>$starting_course];
    }
}
