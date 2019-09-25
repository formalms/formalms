<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

class ElearningLmsController extends LmsController
{

    public $name = 'elearning';

    public $ustatus = [];
    public $cstatus = [];

    public $levels = [];

    public $path_course = '';

    protected $_default_action = 'show';

    public $info = [];


    public function init()
    {

        YuiLib::load('base,tabview');

        if (!isset($_SESSION['id_common_label']))
            $_SESSION['id_common_label'] = -1;

        require_once(_lms_ . '/lib/lib.course.php');
        require_once(_lms_ . '/lib/lib.subscribe.php');
        require_once(_lms_ . '/lib/lib.levels.php');

        $this->cstatus = [
            CST_PREPARATION => '_CST_PREPARATION',
            CST_AVAILABLE => '_CST_AVAILABLE',
            CST_EFFECTIVE => '_CST_CONFIRMED',
            CST_CONCLUDED => '_CST_CONCLUDED',
            CST_CANCELLED => '_CST_CANCELLED',
        ];

        $this->ustatus = [
            //_CUS_RESERVED 		=> '_T_USER_STATUS_RESERVED',
            _CUS_WAITING_LIST => '_WAITING_USERS',
            _CUS_CONFIRMED => '_T_USER_STATUS_CONFIRMED',

            _CUS_SUBSCRIBED => '_T_USER_STATUS_SUBS',
            _CUS_BEGIN => '_T_USER_STATUS_BEGIN',
            _CUS_END => '_T_USER_STATUS_END'
        ];
        $this->levels = CourseLevel::getLevels();
        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . Get::sett('pathcourse') . '/';

        $upd = new UpdatesLms();
        $this->info = $upd->courseUpdates();
    }

    public function fieldsTask()
    {
        $level = Docebo::user()->getUserLevelId();
        if (Get::sett('request_mandatory_fields_compilation', 'on') === 'on' && $level !== ADMIN_GROUP_GODADMIN) {
            require_once(_adm_ . '/lib/lib.field.php');
            $fl = new FieldList();
            $idst_user = Docebo::user()->getIdSt();
            $res = $fl->storeFieldsForUser($idst_user);
        }
        Util::jump_to('index.php?r=elearning/show');
    }

    public function showTask()
    {


        $model = new ElearningLms();

        // update behavior for on_usercourse_empty: applies only after login
        if (Get::sett('on_usercourse_empty') === 'on' && !$_SESSION['logged_in']) {
            $conditions_t = [
                'cu.iduser = :id_user'
            ];

            $params_t = [
                ':id_user' => (int)Docebo::user()->getId()
            ];

            $cp_courses = $model->getUserCoursePathCourses(Docebo::user()->getIdst());
            if (!empty($cp_courses)) {
                $conditions_t[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
            }

            $courselist_t = $model->findAll($conditions_t, $params_t);

            if (empty($courselist_t))
                Util::jump_to('index.php?r=lms/catalog/show&op=unregistercourse');
        }

        $block_list = [];        
        $tb_label = (Get::sett('use_course_label', false) == 'off'? false: true);
        if (!$tb_label) {
            $_SESSION['id_common_label'] = 0;
        } else {
            $id_common_label = Get::req('id_common_label', DOTY_INT, -1);
            $_SESSION['id_common_label'] = $id_common_label;
            $block_list['labels'] = true;
        }
        


        if ($tb_label) {
            require_once(_lms_ . '/admin/models/LabelAlms.php');
            $label_model = new LabelAlms();
            $user_label = $label_model->getLabelForUser(Docebo::user()->getId());
            $this->render('_tabs_block', ['block_list' => $block_list, 'use_label' => $tb_label, 'label' => $user_label, 'current_label' => $id_common_label]);                
        } else {
            $this->render('_tabs_block', ['block_list' => $block_list, 'use_label' => $tb_label]);
        }

        // add feedback:
        // - feedback_type: [err|inf] display error feedback or info feedback
        // - feedback_code: translation code of message
        // - feedback_extra: extrainfo concat at end message
        $feedback_code = Get::req('feedback_code', DOTY_STRING, '');
        $feedback_type = Get::req('feedback_type', DOTY_STRING, '');
        $feedback_extra = Get::req('feedback_extra', DOTY_STRING, '');
        switch ($feedback_type) {
            case 'err':
                $msg = Lang::t($feedback_code, 'login') . ' ' . $feedback_extra;
                UIFeedback::error($msg);
                break;
            case 'inf':
                $msg = Lang::t($feedback_code, 'login') . ' ' . $feedback_extra;
                UIFeedback::info($msg);
                break;
        }
    }

    public function newTask()
    {
        $model = new ElearningLms();

        $filter_text = Get::req('filter_text', DOTY_STRING, '');
        $filter_year = Get::req('filter_year', DOTY_INT, 0);
        $filter_type = Get::req('filter_type', DOTY_STRING, '');
        $filter_cat = Get::req('filter_cat', DOTY_STRING, '');


        $conditions = [
            'cu.iduser = :id_user',
            'cu.status = :status'
        ];

        $params = [
            ':id_user' => (int)Docebo::user()->getId(),
            ':status' => _CUS_SUBSCRIBED
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
            $params[':year'] = $filter_year;
        }


        if (!empty($filter_cat)) {
            $conditions[] = '(c.idCategory in (' . $filter_cat . ') )';
        }

        // filtro per tipo corso elearning
        if (empty($filter_type) || $filter_type === 'elearning' || $filter_type === 'all') {
            $courselist = $model->findAll($conditions, $params);
            $filter_type = empty($filter_type) ? 'elearning' : $filter_type;
        }

        //check courses accessibility
        foreach ($courselist as $key => $courseListItem) {
            $courselist[$key]['can_enter'] = Man_Course::canEnterCourse($courselist[$key]);
            $courselist[$key]['course_type'] = 'elearning';
        }


        // CLASSROOM
        $modelClassroom = new ClassroomLms();

        $filter_text = Get::req('filter_text', DOTY_STRING, '');
        $filter_year = Get::req('filter_year', DOTY_INT, 0);

        $conditions = [
            'cu.iduser = :id_user',
            'cu.status = :status'
        ];

        $params = [
            ':id_user' => (int)Docebo::user()->getId(),
            ':status' => _CUS_SUBSCRIBED
        ];

        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }


        if (!empty($filter_cat)) {
            $conditions[] = '(c.idCategory in (' . $filter_cat . ') )';
        }

        if (!empty($filter_year)) {
            $clist = $modelClassroom->getUserCoursesByYear(Docebo::user()->getId(), $filter_year);
            if ($clist !== false) {
                $conditions[] = 'cu.idCourse IN (' . implode(',', $clist) . ')';
            }
        }

        $cp_courses = $modelClassroom->getUserCoursePathCourses(Docebo::user()->getIdst());
        if (!empty($cp_courses)) {
            $conditions[] = 'cu.idCourse NOT IN (' . implode(',', $cp_courses) . ')';
        }

        if ($filter_type === 'classroom' || $filter_type === 'all') {
            $courselistClassroom = $modelClassroom->findAll($conditions, $params);
        }


        //check courses accessibility
        $keys = [];
        foreach ($courselistClassroom as $key => $courselistClassroomItem) {
            $courselistClassroom[$key]['can_enter'] = Man_Course::canEnterCourse($courselistClassroom[$key]);
            $keys[] = $key;
        }
        // fine classroom


        require_once(_lms_ . '/lib/lib.middlearea.php');
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'use_label' => $ma->currentCanAccessObj('tb_label'),
            'keyword' => $filter_text,
            'display_info' => $this->_getClassDisplayInfo($keys),
            'courselistClassroom' => $courselistClassroom,
            'course_state' => "new_task",
            'filter_type' => $filter_type
        ]);
    }


                
    


    public function allTask()
    {

        // ELEARNING
        $model = new ElearningLms();

        $filter_text = Get::req('filter_text', DOTY_STRING, '');
        $filter_type = '' . Get::req('filter_type', DOTY_STRING, '');
        $filter_cat = Get::req('filter_cat', DOTY_STRING, '');
        $filter_year = Get::req('filter_year', DOTY_STRING, 0);
        $filter_status = Get::req('filter_status', DOTY_STRING, '');


        $conditions = [
            'cu.iduser = :id_user'
        ];

        $params = [
            ':id_user' => (int)Docebo::user()->getId()
        ];


        if (!empty($filter_text)) {
            $conditions[] = "(c.code LIKE '%:keyword%' OR c.name LIKE '%:keyword%')";
            $params[':keyword'] = $filter_text;
        }

        if (!empty($filter_year)) {
            $str_cond_year = '';
            $conditions[] = "(cu.date_inscr >= ':year-00-00 00:00:00' AND cu.date_inscr <= ':year-12-31 23:59:59')";
            $params[':year'] = $filter_year;
        }

        if (!empty($filter_cat) && $filter_cat != '0') {
            $conditions[] = "(c.idCategory in (:filter_category) )";
            $arr_cat = explode(',', $filter_cat);
			$arr_cat = array_map(
				function($value) { return (int)$value; },
				$arr_cat
            );
            $arr_cat = array_unique($arr_cat);
            $params[':filter_category'] = implode(",", $arr_cat);
        }

        // course status : all status, new, completed, in progress
        if ($filter_status !== '' && $filter_status !== 'all') {
            $arr_status = explode(',', $filter_status);
			$arr_status = array_map(
				function($value) { return (int)$value; },
				$arr_status
            );
            $arr_status = array_unique($arr_status);
            $conditions[] = '(cu.status in (' . implode(",", $arr_status) . ') )';
        }
        else if ($filter_status == 'all') {
            $conditions[] = '(c.status <> 3 )';
        }

        // course type: elearning, all, classroom 
        if ($filter_type != 'all') {
            $conditions[] = "c.course_type = ':course_type'";
            $params[':course_type'] = $filter_type;
        }

        $courselist = $model->findAll($conditions, $params);


        //check courses accessibility
        $keys = array_keys($courselist);
        for ($i = 0; $i < count($keys); $i++) {
            $courselist[$keys[$i]]['can_enter'] = Man_Course::canEnterCourse($courselist[$keys[$i]]);
        }


        require_once(_lms_ . '/lib/lib.middlearea.php');
        $ma = new Man_MiddleArea();
        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist,
            'keyword' => $filter_text,
            'ustatus' => $this->ustatus,
            'levels' => $this->levels,
            'display_info' => $this->_getClassDisplayInfo($keys),
            'stato_corso' => 'all_task',
            'filter_type' => $filter_type,
            'current_user' => $params[':id_user']
        ]);
    }
    
    public function allLabelTask(){
        require_once(_lms_ . '/admin/models/LabelAlms.php');
        $label_model = new LabelAlms();
        $user_label = $label_model->getLabelForUser(Docebo::user()->getId());
        $ret ="";
        foreach($user_label as $id_common_label => $label_info) {
           $url = "index.php?r=elearning/show&amp;id_common_label=".$id_common_label;
           $ret .=    '<div class="label_container">'
                        .'<a class="no_decoration" href="'.$url.'">'
                            .'<span class="label_image_cont">'
                                .'<img class="label_image" src="'.($label_info['image'] !== '' ? $GLOBALS['where_files_relative'].'/appLms/label/'.$label_info['image'] : Get::tmpl_path('base').'images/course/label_image.png').'" />'
                            .'</span>'
                            .'<span class="label_info_con">'
                                .'<span class="label_title">'.$label_info['title'].'</span>'
                                .($label_info['description'] !== '' ? '<br /><span id="label_description_'.$id_common_label.'" class="label_description" title="'.html_entity_decode($label_info['description']).'">'.$label_info['description'].'</span>' : '')
                            .'</span>'
                        .'</a>'
                    .'</div>';
        }
        echo $ret;                
    }

    /**
     * This implies the skill gap analysis :| well, a first implementation will be done based on
     * required over acquired skill and proposing courses that will give, the required competences.
     * If this implementation will require too much time i will wait for more information and pospone the implementation
     */
    public function suggested()
    {

        $competence_needed = Docebo::user()->requiredCompetences();

        $model = new ElearningLms();
        $courselist = $model->findAll([
            'cu.iduser = :id_user',
            'comp.id_competence IN (:competence_list)'
        ], [
            ':id_user' => Docebo::user()->getId(),
            ':competence_list' => $competence_needed
        ], ['LEFT JOIN %lms_competence AS comp ON ( .... ) ']);

        $this->render('courselist', [
            'path_course' => $this->path_course,
            'courselist' => $courselist
        ]);
    }

    /**
     * The action of self-unsubscription from a course (if enabled for the course),
     * available in the course box of the courses list
     */
    public function self_unsubscribe()
    {
        $id_user = Docebo::user()->idst;//Get::req('id_user', DOTY_INT, Docebo::user()->idst);
        $id_course = Get::req('id_course', DOTY_INT, 0);
        $id_edition = Get::req('id_edition', DOTY_INT, 0);
        $id_date = Get::req('id_date', DOTY_INT, 0);

        $cmodel = new CourseAlms();
        $cinfo = $cmodel->getCourseModDetails($id_course);

        //index.php?r=elearning/show
        $back = Get::req('back', DOTY_STRING, '');
        if ($back !== '') {
            $parts = explode('/', $back);
            $length = count($parts);
            if ($length > 0) {
                $parts[$length - 1] = 'show';
                $back = implode('/', $parts);
            }
        }
        $jump_url = 'index.php?r=' . ($back ? $back : 'lms/elearning/show');

        if ($cinfo['auto_unsubscribe'] == 0) {
            //no self unsubscribe possible for this course
            Util::jump_to($jump_url . '&res=err_unsub');
        }

        $date_ok = TRUE;
        if ($cinfo['unsubscribe_date_limit'] !== '' && $cinfo['unsubscribe_date_limit'] !== '0000-00-00 00:00:00' && $cinfo['unsubscribe_date_limit'] !== NULL) {
            if ($cinfo['unsubscribe_date_limit'] < date('Y-m-d H:i:s')) {
                //self unsubscribing is no more allowed, go back to courselist page
                Util::jump_to($jump_url . '&res=err_unsub');
            }
        }

        $smodel = new SubscriptionAlms();
        $param = '';

        if ($cinfo['auto_unsubscribe'] == 1) {
            //moderated self unsubscribe
            $res = $smodel->setUnsubscribeRequest($id_user, $id_course, $id_edition, $id_date);
            $param .= $res ? '&res=ok_unsub' : '&res=err_unsub';
        }

        if ($cinfo['auto_unsubscribe'] == 2) {
            //directly unsubscribe user
            $res = $smodel->unsubscribeUser($id_user, $id_course, $id_edition, $id_date);
            $param .= $res ? '&res=ok_unsub' : '&res=err_unsub';
        }

        Util::jump_to($jump_url);
    }


    protected function _getClassDisplayInfo($courses)
    {
        $model = new ClassroomLms();
        $class_info = $model->getUserEditionsInfo(Docebo::user()->getIdst(), $courses);
        if (empty ($class_info)) return [];

        $dm = new DateManager();
        $status_arr = $dm->getStatusForDropdown();

        $output = [];
        /** @var int $id_course @var array $classrooms */
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

                if (!property_exists($output[$id_course][$id_classroom], 'start_date')) $output[$id_course][$id_classroom]->start_date = $classroom->date_begin;
                if (!property_exists($output[$id_course][$id_classroom], 'end_date')) $output[$id_course][$id_classroom]->end_date = $classroom->date_end;
                if ($classroom->date_end > $output[$id_course][$id_classroom]->end_date) $output[$id_course][$id_classroom]->end_date = $classroom->date_end;
                if ($classroom->date_begin < $output[$id_course][$id_classroom]->start_date) $output[$id_course][$id_classroom]->start_date = $classroom->date_begin;
            }
        }


        return $output;
    }


}
