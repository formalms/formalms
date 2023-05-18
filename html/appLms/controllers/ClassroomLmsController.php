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

class ClassroomLmsController extends LmsController
{
    public $name = 'classroom';

    public $ustatus = [];
    public $cstatus = [];

    public $levels = [];

    public $path_course = '';

    protected $_default_action = 'show';

    public $info = [];

    public function isTabActive($tab_name)
    {
        switch ($tab_name) {
            case 'new':
                if (!isset($this->info['classroom'][0])) {
                    return false;
                }
             break;
            case 'inprogress':
                if (!isset($this->info['classroom'][1])) {
                    return false;
                }
             break;
            case 'completed':
                if (!isset($this->info['classroom'][2])) {
                    return false;
                }
             break;
        }

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
            //_CUS_RESERVED 		=> '_T_USER_STATUS_RESERVED',
            _CUS_WAITING_LIST => '_WAITING',
            _CUS_CONFIRMED => '_T_USER_STATUS_CONFIRMED',

            _CUS_SUBSCRIBED => '_T_USER_STATUS_SUBS',
            _CUS_BEGIN => '_T_USER_STATUS_BEGIN',
            _CUS_END => '_T_USER_STATUS_END',
        ];
        $this->levels = CourseLevel::getTranslatedLevels();
        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';

        $upd = new UpdatesLms();
        $this->info = $upd->courseUpdates();
    }

    public function show()
    {
        $model = new ClassroomLms();

        if (FormaLms\lib\Get::sett('on_usercourse_empty') === 'on') {
            $conditions_t = [
                'cu.iduser = :id_user',
            ];

            $params_t = [
                ':id_user' => (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ];

            $cp_courses = $model->getUserCoursePathCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());
            if (!empty($cp_courses)) {
                $conditions_t[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
            }

            $courselist_t = $model->findAll($conditions_t, $params_t);

            if (empty($courselist_t)) {
                Util::jump_to('index.php?r=lms/catalog/show&sop=unregistercourse');
            }
        }

        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $block_list = [];
        if ($ma->currentCanAccessObj('user_details_short')) {
            $block_list['user_details_short'] = true;
        }
        if ($ma->currentCanAccessObj('user_details_full')) {
            $block_list['user_details_full'] = true;
        }
        if ($ma->currentCanAccessObj('credits')) {
            $block_list['credits'] = true;
        }
        if ($ma->currentCanAccessObj('news')) {
            $block_list['news'] = true;
        }
        $tb_label = $ma->currentCanAccessObj('tb_label');
        if (!$tb_label) {
            $this->session->set('id_common_label', 0);
        } else {
            $id_common_label = FormaLms\lib\Get::req('id_common_label', DOTY_INT, -1);

            if ($id_common_label >= 0) {
                $this->session->set('id_common_label', $id_common_label);
            } elseif ($id_common_label == -2) {
                $this->session->set('id_common_label', -1);
            }

            $block_list['labels'] = true;
        }
        $this->session->save();

        if ($tb_label && $this->session->get('id_common_label') == -1) {
            require_once _lms_ . '/admin/models/LabelAlms.php';
            $label_model = new LabelAlms();

            $user_label = $label_model->getLabelForUser(\FormaLms\lib\FormaUser::getCurrentUser()->getId());

            $this->render('_labels', ['block_list' => $block_list,
                                            'label' => $user_label, ]);
        } else {
            if (!empty($block_list)) {
                $this->render('_tabs_block', ['block_list' => $block_list]);
            } else {
                $this->render('_tabs', []);
            }
        }
    }

    /**
     * Format class editions info data to be displayed.
     *
     * @param array $courses
     *
     * @return array
     */
    protected function _getClassDisplayInfo($courses)
    {
        $model = new ClassroomLms();
        $class_info = $model->getUserEditionsInfo(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst(), $courses);
        if (empty($class_info)) {
            return [];
        }

        $dm = new DateManager();
        $status_arr = $dm->getStatusForDropdown();

        $output = [];

        foreach ($class_info as $id_course => $classrooms) {
            $output[$id_course] = [];
            foreach ($classrooms as $id_classroom => $classroom) {
                if (!isset($output[$id_course][$id_classroom])) {
                    $output[$id_course][$id_classroom] = new stdClass();
                    $output[$id_course][$id_classroom]->code = $classroom->code;
                    $output[$id_course][$id_classroom]->name = $classroom->name;
                    $output[$id_course][$id_classroom]->location = $classroom->location;
                    $output[$id_course][$id_classroom]->enrolled = $classroom->enrolled;
                    $output[$id_course][$id_classroom]->status = $status_arr[$classroom->status];
                    $output[$id_course][$id_classroom]->date_min = $classroom->date_min;
                    $output[$id_course][$id_classroom]->date_max = $classroom->date_max;

                    if (property_exists($classroom, 'date_info')) {
                        $output[$id_course][$id_classroom]->date_info = $classroom->date_info; // (array)
                    } else {
                        $output[$id_course][$id_classroom]->date_info = false;
                    }
                }

                if (!property_exists($output[$id_course][$id_classroom], 'start_date')) {
                    $output[$id_course][$id_classroom]->start_date = $classroom->date_begin;
                }
                if (!property_exists($output[$id_course][$id_classroom], 'end_date')) {
                    $output[$id_course][$id_classroom]->end_date = $classroom->date_end;
                }
                if ($classroom->date_end > $output[$id_course][$id_classroom]->end_date) {
                    $output[$id_course][$id_classroom]->end_date = $classroom->date_end;
                }
                if ($classroom->date_begin < $output[$id_course][$id_classroom]->start_date) {
                    $output[$id_course][$id_classroom]->start_date = $classroom->date_begin;
                }
            }
        }

        return $output;
    }

    public function allTask()
    {
        $model = new ClassroomLms();

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $filter_year = FormaLms\lib\Get::req('filter_year', DOTY_INT, 0);

        $conditions = [
            'cu.iduser = :id_user',
            'cu.status <> :status',
        ];

        $params = [
            ':id_user' => (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ':status' => _CUS_END,
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $clist = $model->getUserCoursesByYear(\FormaLms\lib\FormaUser::getCurrentUser()->getId(), $filter_year);
            if ($clist !== false) {
                $conditions[] = 'cu.idCourse IN (' . implode(',', $clist) . ')';
            }
        }

        $cp_courses = $model->getUserCoursePathCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());
        if (!empty($cp_courses)) {
            $conditions[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
        }

        $courselist = $model->findAll($conditions, $params);

        //check courses accessibility
        $keys = array_keys($courselist);
        for ($i = 0; $i < count($keys); ++$i) {
            $courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
        }

        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'use_label' => $ma->currentCanAccessObj('tb_label'),
            'display_info' => $this->_getClassDisplayInfo($keys),
            'dm' => new DateManager(),
            'keyword' => $filter_text,
        ]);
    }

    public function newTask()
    {
        $model = new ClassroomLms();

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $filter_year = FormaLms\lib\Get::req('filter_year', DOTY_INT, 0);

        $conditions = [
            'cu.iduser = :id_user',
            'cu.status = :status',
        ];

        $params = [
            ':id_user' => (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ':status' => _CUS_SUBSCRIBED,
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $clist = $model->getUserCoursesByYear(\FormaLms\lib\FormaUser::getCurrentUser()->getId(), $filter_year);
            if ($clist !== false) {
                $conditions[] = 'cu.idCourse IN (' . implode(',', $clist) . ')';
            }
        }

        $cp_courses = $model->getUserCoursePathCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());
        if (!empty($cp_courses)) {
            $conditions[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
        }

        $courselist = $model->findAll($conditions, $params);

        //check courses accessibility
        $keys = array_keys($courselist);
        for ($i = 0; $i < count($keys); ++$i) {
            $courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
        }
        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'use_label' => $ma->currentCanAccessObj('tb_label'),
            'display_info' => $this->_getClassDisplayInfo($keys),
            'dm' => new DateManager(),
            'keyword' => $filter_text,
        ]);
    }

    public function inprogressTask()
    {
        $model = new ClassroomLms();

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $filter_year = FormaLms\lib\Get::req('filter_year', DOTY_INT, 0);

        $conditions = [
            'cu.iduser = :id_user',
            'cu.status = :status',
        ];

        $params = [
            ':id_user' => (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ':status' => _CUS_BEGIN,
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $clist = $model->getUserCoursesByYear(\FormaLms\lib\FormaUser::getCurrentUser()->getId(), $filter_year);
            if ($clist !== false) {
                $conditions[] = 'cu.idCourse IN (' . implode(',', $clist) . ')';
            }
        }

        $cp_courses = $model->getUserCoursePathCourses(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());
        if (!empty($cp_courses)) {
            $conditions[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
        }

        $courselist = $model->findAll($conditions, $params);

        //check courses accessibility
        $keys = array_keys($courselist);
        for ($i = 0; $i < count($keys); ++$i) {
            $courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
        }
        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'use_label' => $ma->currentCanAccessObj('tb_label'),
            'display_info' => $this->_getClassDisplayInfo($keys),
            'dm' => new DateManager(),
            'keyword' => $filter_text,
        ]);
    }

    public function completedTask()
    {
        $model = new ClassroomLms();

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');
        $filter_year = FormaLms\lib\Get::req('filter_year', DOTY_INT, 0);

        $conditions = [
            'cu.iduser = :id_user',
            'cu.status = :status',
        ];

        $params = [
            ':id_user' => (int) \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ':status' => _CUS_END,
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $clist = $model->getUserCoursesByYear(\FormaLms\lib\FormaUser::getCurrentUser()->getId(), $filter_year);
            if ($clist !== false) {
                $conditions[] = 'cu.idCourse IN (' . implode(',', $clist) . ')';
            }
        }

        $courselist = $model->findAll($conditions, $params);

        //check courses accessibility
        $keys = array_keys($courselist);
        for ($i = 0; $i < count($keys); ++$i) {
            $courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
        }
        require_once _lms_ . '/lib/lib.middlearea.php';
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'use_label' => $ma->currentCanAccessObj('tb_label'),
            'display_info' => $this->_getClassDisplayInfo($keys),
            'dm' => new DateManager(),
            'keyword' => $filter_text,
        ]);
    }

    /**
     * This implies the skill gap analysis :| well, a first implementation will be done based on
     * required over acquired skill and proposing courses that will give, the required competences.
     * If this implementation will require too much time i will wait for more information and pospone the implementation.
     */
    public function suggested()
    {
        $competence_needed = \FormaLms\lib\FormaUser::getCurrentUser()->requiredCompetences();

        $model = new ClassroomLms();
        $courselist = $model->findAll([
            'cu.iduser = :id_user',
            'comp.id_competence IN (:competence_list)',
        ], [
            ':id_user' => \FormaLms\lib\FormaUser::getCurrentUser()->getId(),
            ':competence_list' => $competence_needed,
        ], ['LEFT JOIN %lms_competence AS comp ON ( .... ) ']);

        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'dm' => new DateManager(),
        ]);
    }

    public function self_unsubscribe_dialog()
    {
        $dm = new DateManager();
        $id_course = FormaLms\lib\Get::gReq('id_course', DOTY_INT);
        $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();

        $edition_arr = $dm->getUserDateForCourse($id_user, $id_course);

        $info = $dm->getCourseEdition($id_course, false, false, false, false, $edition_arr);

        $body = $this->render('edition_list', [
            'id_course' => $id_course,
            'info' => $info,
            'smodel' => new SubscriptionAlms(),
        ], true);

        $res = [
            'success' => true,
            'header' => Lang::t('_UNSUBSCRIBE_REQUESTS', 'course'),
            'body' => $body,
        ];

        ob_start();

        $json = new Services_JSON();
        echo $json->encode($res);
    }
}
