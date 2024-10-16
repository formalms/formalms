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

use UsermanagementAdm;

class CourseAlms extends Model
{
    protected $acl_man;
    public $course_man;
    public $classroom_man;
    public $edition_man;

    protected $id_course;
    protected $id_date;
    protected $id_edition;

    public const boxDescrMaxLimit = 140;

    public function __construct($id_course = 0, $id_date = 0, $id_edition = 0)
    {
        require_once _lms_ . '/lib/lib.date.php';
        require_once _lms_ . '/lib/lib.edition.php';
        require_once _lms_ . '/lib/lib.course.php';

        $this->id_course = $id_course;
        $this->id_date = $id_date;
        $this->id_edition = $id_edition;

        $this->course_man = new Man_Course();
        $this->classroom_man = new DateManager();
        $this->edition_man = new EditionManager();

        $this->acl_man = &Docebo::user()->getAclManager();
        parent::__construct();
    }

    public function getPerm()
    {
        return [
            'view' => 'standard/view.png',
            'add' => 'standard/add.png',
            'mod' => 'standard/edit.png',
            'del' => 'standard/rem.png',
            'moderate' => '',
            'subscribe' => '',
        ];
    }

    public function getUserInOverbooking($idCourse)
    {
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $acl_man = &Docebo::user()->getAclManager();

            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
        }

        // skipping those that are both in ovebooking and waiting (admin approval course + overbooking) otherwise they are couted twice

        $query = 'select COUNT(cu.idUser) as num_overbooking'
            . ' FROM %lms_course AS c'
            . ' LEFT JOIN %lms_courseuser AS cu ON c.idCourse = cu.idCourse and cu.idCourse=' . $idCourse
            . ($userlevelid != ADMIN_GROUP_GODADMIN
                ? (!empty($admin_users) ? ' AND cu.idUser IN (' . implode(',', $admin_users) . ')' : ' AND cu.idUser IN (0)')
                : '')
            . " WHERE c.course_type <> 'assessment' and cu.status=4 and cu.waiting = 0";

        $res = sql_query($query);
        list($num_overbooking) = sql_fetch_row($res);

        return $num_overbooking;
    }

    public function getFirstOverbooked()
    {
        $query = 'select idUser'
            . ' FROM %lms_courseuser '
            . ' WHERE status=4 AND idCourse = ' . $this->id_course
            . ' ORDER BY date_inscr ASC LIMIT 1';

        $res = sql_query($query);
        list($overbooked_user) = sql_fetch_row($res);

        return $overbooked_user;
    }

    public function getUserInWaiting($idCourse)
    {
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $acl_man = &Docebo::user()->getAclManager();

            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
        }

        $query = 'select COUNT(cu.idUser) as num_waiting'
            . ' FROM %lms_course AS c'
            . ' LEFT JOIN %lms_courseuser AS cu ON c.idCourse = cu.idCourse and cu.idCourse=' . $idCourse
            . ($userlevelid != ADMIN_GROUP_GODADMIN
                ? (!empty($admin_users) ? ' AND cu.idUser IN (' . implode(',', $admin_users) . ')' : ' AND cu.idUser IN (0)')
                : '')
            . " WHERE c.course_type <> 'assessment' and cu.status=-2 ";

        $res = sql_query($query);
        list($num_waiting) = sql_fetch_row($res);

        return $num_waiting;
    }

    public function getUserInCourse($idCourse)
    {
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $acl_man = &Docebo::user()->getAclManager();

            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
        }

        $query = 'select COUNT(cu.idUser) as num_users'
            . ' FROM %lms_course AS c'
            . ' LEFT JOIN %lms_courseuser AS cu ON c.idCourse = cu.idCourse and cu.idCourse=' . $idCourse
            . ($userlevelid != ADMIN_GROUP_GODADMIN
                ? (!empty($admin_users) ? ' AND cu.idUser IN (' . implode(',', $admin_users) . ')' : ' AND cu.idUser IN (0)')
                : '')
            . " WHERE c.course_type <> 'assessment' ";

        $res = sql_query($query);
        list($num_overbooking) = sql_fetch_row($res);

        return $num_overbooking;
    }

    public function getCourseNumber($filter = false)
    {
        $query = 'SELECT COUNT(*)'
            . ' FROM %lms_course'
            . " WHERE course_type <> 'assessment'";

        if ($filter) {
            if (isset($filter['id_category'])) {
                if (isset($filter['descendants']) && $filter['descendants']) {
                    $query .= ' AND idCategory IN (' . implode(',', $this->getCategoryDescendants($filter['id_category'])) . ')';
                } else {
                    $query .= ' AND idCategory = ' . (int) $filter['id_category'];
                }
            }
            if (isset($filter['text']) && $filter['text'] !== '') {
                $query .= " AND( name LIKE '%" . $filter['text'] . "%'"
                    . " OR code LIKE '%" . $filter['text'] . "%')";
            }

            if (isset($filter['waiting']) && $filter['waiting']) {
                $query_course = 'SELECT idCourse'
                    . ' FROM %lms_courseuser'
                    . ' WHERE waiting = 1';

                $result = sql_query($query);
                $id_course_filter = [0 => 0];

                while (list($id_course_tmp) = sql_fetch_row($result)) {
                    $id_course_filter[$id_course_tmp] = $id_course_tmp;
                }

                $query .= ' AND idCourse IN (' . implode(',', $id_course_filter) . ')';
            }

            if (isset($filter['classroom']) && $filter['classroom']) {
                $query .= " AND course_type = 'classroom'";
            }

            if (isset($filter['idCourse']) && $filter['idCourse']) {
                $query .= ' AND idCourse = ' . $filter['idCourse'];
            }
        }

        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();

            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            $all_courses = false;
            if (isset($admin_courses['course'][0])) {
                $all_courses = true;
            } elseif (isset($admin_courses['course'][-1])) {
                require_once _lms_ . '/lib/lib.catalogue.php';
                $cat_man = new Catalogue_Manager();

                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if (count($user_catalogue) > 0) {
                    $courses = [0];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat, true);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    foreach ($courses as $id_course) {
                        if ($id_course != 0) {
                            $admin_courses['course'][$id_course] = $id_course;
                        }
                    }
                } elseif (FormaLms\lib\Get::sett('on_catalogue_empty', 'off') == 'on') {
                    $all_courses = true;
                }
            } else {
                $array_courses = [];
                $array_courses = array_merge($array_courses, $admin_courses['course']);

                if (!empty($admin_courses['coursepath'])) {
                    require_once _lms_ . '/lib/lib.coursepath.php';
                    $path_man = new CoursePath_Manager();
                    $coursepath_course = &$path_man->getAllCourses($admin_courses['coursepath']);
                    $array_courses = array_merge($array_courses, $coursepath_course);
                }
                if (!empty($admin_courses['catalogue'])) {
                    require_once _lms_ . '/lib/lib.catalogue.php';
                    $cat_man = new Catalogue_Manager();
                    foreach ($admin_courses['catalogue'] as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat, true);
                        $array_courses = array_merge($array_courses, $catalogue_course);
                    }
                }
                $admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
            }

            if (!$all_courses) {
                if (empty($admin_courses['course'])) {
                    $query .= ' AND 0 ';
                } else {
                    $query .= ' AND idCourse IN (' . implode(',', $admin_courses['course']) . ') ';
                }
            }
        }

        list($res) = sql_fetch_row(sql_query($query));

        return $res;
    }

    public function getCategoryDescendants($id_category)
    {
        $output = [];

        if ($id_category != 0) {
            $query = 'SELECT iLeft, iRight FROM %lms_category WHERE idCategory=' . (int) $id_category;
            $res = sql_query($query);
            list($left, $right) = sql_fetch_row($res);

            $query = 'SELECT idCategory FROM %lms_category WHERE iLeft>=' . $left . ' AND iRight<=' . $right;
            $res = sql_query($query);
            while (list($id_cat) = sql_fetch_row($res)) {
                $output[] = $id_cat;
            }
        } else {
            $output[] = 0;

            $query = 'SELECT idCategory FROM %lms_category';
            $res = sql_query($query);
            while (list($id_cat) = sql_fetch_row($res)) {
                $output[] = $id_cat;
            }
        }

        return $output;
    }

    public function loadCourse($start_index, $results, $sort, $dir, $filter = false)
    {
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $acl_man = &Docebo::user()->getAclManager();

            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
        }

        $query = 'SELECT c.*, COUNT(cu.idUser) as subscriptions, SUM(cu.waiting) as pending'
            . ' FROM %lms_course AS c'
            . ' LEFT JOIN %lms_courseuser AS cu ON c.idCourse = cu.idCourse'
            . ($userlevelid != ADMIN_GROUP_GODADMIN
                ? (!empty($admin_users) ? ' AND cu.idUser IN (' . implode(',', $admin_users) . ')' : ' AND cu.idUser IN (0)')
                : '')
            . " WHERE c.course_type <> 'assessment'";

        if ($filter) {
            if (isset($filter['id_category'])) {
                if (isset($filter['descendants']) && $filter['descendants']) {
                    $query .= ' AND c.idCategory IN (' . implode(',', $this->getCategoryDescendants($filter['id_category'])) . ')';
                } else {
                    $query .= ' AND c.idCategory = ' . (int) $filter['id_category'];
                }
            }
            if (isset($filter['text']) && $filter['text'] !== '') {
                $query .= " AND( c.name LIKE '%" . $filter['text'] . "%'"
                    . " OR c.code LIKE '%" . $filter['text'] . "%')";
            }

            if (isset($filter['waiting']) && $filter['waiting']) {
                $query_course = 'SELECT idCourse'
                    . ' FROM %lms_courseuser'
                    . ' WHERE waiting = 1';

                $result = sql_query($query_course);
                $id_course_filter = [0 => 0];

                while (list($id_course_tmp) = sql_fetch_row($result)) {
                    $id_course_filter[$id_course_tmp] = $id_course_tmp;
                }

                $query .= ' AND c.idCourse IN (' . implode(',', $id_course_filter) . ')';
            }

            if (isset($filter['classroom']) && $filter['classroom']) {
                $query .= " AND course_type = 'classroom'";
            }

            if (isset($filter['idCourse']) && $filter['idCourse']) {
                $query .= ' AND c.idCourse = ' . $filter['idCourse'];
            }
        }

        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            $all_courses = false;
            if (isset($admin_courses['course'][0])) {
                $all_courses = true;
            } elseif (isset($admin_courses['course'][-1])) {
                require_once _lms_ . '/lib/lib.catalogue.php';
                $cat_man = new Catalogue_Manager();

                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if (count($user_catalogue) > 0) {
                    $courses = [0];

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat, true);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    foreach ($courses as $id_course) {
                        if ($id_course != 0) {
                            $admin_courses['course'][$id_course] = $id_course;
                        }
                    }
                } elseif (FormaLms\lib\Get::sett('on_catalogue_empty', 'off') == 'on') {
                    $all_courses = true;
                }
            } else {
                $array_courses = [];
                $array_courses = array_merge($array_courses, $admin_courses['course']);

                if (!empty($admin_courses['coursepath'])) {
                    require_once _lms_ . '/lib/lib.coursepath.php';
                    $path_man = new CoursePath_Manager();
                    $coursepath_course = &$path_man->getAllCourses($admin_courses['coursepath']);
                    $array_courses = array_merge($array_courses, $coursepath_course);
                }
                if (!empty($admin_courses['catalogue'])) {
                    require_once _lms_ . '/lib/lib.catalogue.php';
                    $cat_man = new Catalogue_Manager();
                    foreach ($admin_courses['catalogue'] as $id_cat) {
                        $catalogue_course = &$cat_man->getCatalogueCourse($id_cat, true);
                        $array_courses = array_merge($array_courses, $catalogue_course);
                    }
                }
                $admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
            }

            if (!$all_courses) {
                if (empty($admin_courses['course'])) {
                    $query .= ' AND 0 ';
                } else {
                    $query .= ' AND c.idCourse IN (' . implode(',', $admin_courses['course']) . ') ';
                }
            }
        }

        $query .= ' GROUP BY c.idCourse'
            . ' ORDER BY ' . $sort . ' ' . $dir;

        if ((int) $results > 0) {
            $query .= ' LIMIT ' . (int) $start_index . ', ' . (int) $results;
        }

        return sql_query($query);
    }

    public function getCourseModDetails($id_course = false)
    {
        if ($id_course === false) {
            return [
                'autoregistration_code' => '',
                'code' => '',
                'name' => '',
                'lang_code' => getLanguage(),
                'difficult' => 'medium',
                'course_type' => 'classroom', //'elearning',
                'status' => CST_EFFECTIVE,
                'course_edition' => 0,
                'description' => '',
                'box_description' => '',
                'can_subscribe' => 1,
                'sub_start_date' => '',
                'sub_end_date' => '',
                'show_rules' => 0,
                'credits' => 0,
                'show_progress' => 1,
                'show_time' => 1,
                'show_who_online' => 1,
                'show_extra_info' => 0,
                'level_show_user' => 0,
                'subscribe_method' => 2,
                'selling' => 0,
                'prize' => '',
                'advance' => '',
                'permCloseLO' => 0,
                'userStatusOp' => (1 << _CUS_SUSPEND),
                'direct_play' => 0,
                'date_begin' => '',
                'date_end' => '',
                'hour_begin' => '-1',
                'hour_end' => '-1',
                'valid_time' => '0',
                'mediumTime' => '0',
                'sendCalendar' => '0',
                'calendarId' => '',
                'min_num_subscribe' => '0',
                'max_num_subscribe' => '0',
                'allow_overbooking' => '',
                'course_quota' => '',
                'show_result' => '0',
                'linkSponsor' => 'http://',
                'use_logo_in_courselist' => '1',
                'img_material' => '',
                'img_course' => '',
                'img_othermaterial' => '',
                'imgSponsor' => '',
                'course_demo' => '',
                'auto_unsubscribe' => '0',
                'unsubscribe_date_limit' => '',
            ];
        } else {
            $query_course = " SELECT * FROM %lms_course WHERE idCourse = '" . (int) $id_course . "'";

            $course = sql_fetch_assoc(sql_query($query_course));
            if ($course) {
                $course['date_begin'] = Format::date($course['date_begin'], 'date');
                $course['date_end'] = Format::date($course['date_end'], 'date');
                $course['sub_start_date'] = Format::date($course['sub_start_date'], 'date');
                $course['sub_end_date'] = Format::date($course['sub_end_date'], 'date');
            }

            return $course;
        }
    }

    public function insCourse($data_params = null)
    {
        if (is_null($data_params)) {
            // Backward compatibility.
            $data_params = $_POST;
        }

        require_once _base_ . '/lib/lib.upload.php';
        require_once _base_ . '/lib/lib.multimedia.php';
        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.manmenu.php';

        $array_lang = Docebo::langManager()->getAllLangCode();
        $array_lang[] = 'none';

        $id_custom = $data_params['selected_menu'];

        // calc quota limit
        $quota = $data_params['course_quota'];
        if (isset($data_params['inherit_quota'])) {
            $quota = FormaLms\lib\Get::sett('course_quota');
            $data_params['course_quota'] = COURSE_QUOTA_INHERIT;
        }

        $quota = $quota * 1024 * 1024;

        $path = FormaLms\lib\Get::sett('pathcourse');
        $path = '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . (substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

        if ($data_params['course_name'] == '') {
            $data_params['course_name'] = Lang::t('_NO_NAME', 'course');
        }

        // restriction on course status ------------------------------------------
        $user_status = 0;
        if (isset($data_params['user_status'])) {
            foreach ($data_params['user_status'] as $status => $v) {
                $user_status |= (1 << $status);
            }
        }

        // level that will be showed in the course --------------------------------
        $show_level = 0;
        if (isset($data_params['course_show_level'])) {
            foreach ($data_params['course_show_level'] as $lv => $v) {
                $show_level |= (1 << $lv);
            }
        }

        // save the file uploaded -------------------------------------------------
        $file_sponsor = '';
        $file_logo = '';
        $file_material = '';
        $file_othermaterial = '';
        $file_demo = '';

        $error = false;
        $quota_exceeded = false;
        $total_file_size = 0;

        $boxDescription = $data_params['course_box_descr'];

        if (strlen($boxDescription) > self::boxDescrMaxLimit) {
            $res['err'] = '_err_course_box_descr_max_limit';

            return $res;
        }

        if (is_array($_FILES) && !empty($_FILES)) {
            sl_open_fileoperations();
        }
        // load user material ---------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_user_material',
            '',
            $path,
            ($quota != 0 ? $quota - $total_file_size : false),
            false
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_material = $arr_file['filename'];
        $total_file_size = $total_file_size + $arr_file['new_size'];

        // course otheruser material -------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_otheruser_material',
            '',
            $path,
            ($quota != 0 ? $quota - $total_file_size : false),
            false
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_othermaterial = $arr_file['filename'];
        $total_file_size = $total_file_size + $arr_file['new_size'];

        // course demo-----------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_demo',
            '',
            $path,
            ($quota != 0 ? $quota - $total_file_size : false),
            false
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_demo = $arr_file['filename'];
        $total_file_size = $total_file_size + $arr_file['new_size'];

        // course sponsor---------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_sponsor_logo',
            '',
            $path,
            ($quota != 0 ? $quota - $total_file_size : false),
            false,
            true
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_sponsor = $arr_file['filename'];
        $total_file_size = $total_file_size + $arr_file['new_size'];

        // course logo-----------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_logo',
            '',
            $path,
            ($quota != 0 ? $quota - $total_file_size : false),
            false,
            true
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_logo = $arr_file['filename'];
        $total_file_size = $total_file_size + $arr_file['new_size'];

        // ----------------------------------------------------------------------------------------------
        sl_close_fileoperations();

        if ($data_params['can_subscribe'] == '2') {
            $sub_start_date = Format::dateDb($data_params['sub_start_date'], 'date');
            $sub_end_date = Format::dateDb($data_params['sub_end_date'], 'date');
        }

        $date_begin = Format::dateDb($data_params['course_date_begin'], 'date');
        $date_end = Format::dateDb($data_params['course_date_end'], 'date');

        // insert the course in database -----------------------------------------------------------
        $hour_begin = '-1';
        $hour_end = '-1';
        if ($data_params['hour_begin']['hour'] != '-1') {
            $hour_begin = (strlen($data_params['hour_begin']['hour']) == 1 ? '0' . $data_params['hour_begin']['hour'] : $data_params['hour_begin']['hour']);
            if ($data_params['hour_begin']['quarter'] == '-1') {
                $hour_begin .= ':00';
            } else {
                $hour_begin .= ':' . $data_params['hour_begin']['quarter'];
            }
        }

        if ($data_params['hour_end']['hour'] != '-1') {
            $hour_end = (strlen($data_params['hour_end']['hour']) == 1 ? '0' . $data_params['hour_end']['hour'] : $data_params['hour_end']['hour']);
            if ($data_params['hour_end']['quarter'] == '-1') {
                $hour_end .= ':00';
            } else {
                $hour_end .= ':' . $data_params['hour_end']['quarter'];
            }
        }

        $data = Events::trigger('lms.course.creating', ['parameters' => $data_params]);
        $data_params = $data['parameters'];

        $query_course = "
        INSERT INTO %lms_course
        SET idCategory          = '" . (isset($data_params['idCategory']) ? $data_params['idCategory'] : 0) . "',
            CODE                = '" . $data_params['course_code'] . "',
            NAME                = '" . $data_params['course_name'] . "',
            description         = '" . $data_params['course_descr'] . "',
            box_description         = '" . $data_params['course_box_descr'] . "',
            lang_code           = '" . $array_lang[$data_params['course_lang']] . "',
            STATUS              = '" . (int) $data_params['course_status'] . "',
            level_show_user     = '" . $show_level . "',
            subscribe_method    = '" . (int) $data_params['course_subs'] . "',
            credits             = '" . $data_params['credits'] . "',

            create_date         = '" . date('Y-m-d H:i:s') . "',

            linkSponsor         = '" . $data_params['course_sponsor_link'] . "',
            imgSponsor          = '" . $file_sponsor . "',
            img_course          = '" . $file_logo . "',
            img_material        = '" . $file_material . "',
            img_othermaterial   = '" . $file_othermaterial . "',
            course_demo         = '" . $file_demo . "',

            mediumTime          = '" . $data_params['course_medium_time'] . "',
            sendCalendar        = '" . (isset($data_params['send_calendar']) ? 1 : 0) . "',
            calendarId          = '" . CalendarManager::generateUniqueCalendarId() . "',
            permCloseLO         = '" . $data_params['course_em'] . "',
            userStatusOp        = '" . $user_status . "',
            difficult           = '" . $data_params['course_difficult'] . "',

            show_progress       = '" . (isset($data_params['course_progress']) ? 1 : 0) . "',
            show_time           = '" . (isset($data_params['course_time']) ? 1 : 0) . "',

            show_who_online     = '" . $data_params['show_who_online'] . "',

            show_extra_info     = '" . (isset($data_params['course_advanced']) ? 1 : 0) . "',
            show_rules          = '" . (int) $data_params['course_show_rules'] . "',

            direct_play         = '" . (isset($data_params['direct_play']) ? 1 : 0) . "',

            date_begin          = '" . $date_begin . "',
            date_end            = '" . $date_end . "',
            hour_begin          = '" . $hour_begin . "',
            hour_end            = '" . $hour_end . "',

            valid_time          = '" . (int) $data_params['course_day_of'] . "',

            min_num_subscribe   = '" . (int) $data_params['min_num_subscribe'] . "',
            max_num_subscribe   = '" . (int) $data_params['max_num_subscribe'] . "',
            selling             = '" . (isset($data_params['course_sell']) ? '1' : '0') . "',
            prize               = '" . $data_params['course_prize'] . "',

            course_type         = '" . $data_params['course_type'] . "',

            course_edition      = '" . (isset($data_params['course_edition']) && $data_params['course_edition'] == 1 ? 1 : 0) . "',

            course_quota        = '" . $data_params['course_quota'] . "',
            used_space          = '" . $total_file_size . "',
            allow_overbooking   = '" . (isset($data_params['allow_overbooking']) ? 1 : 0) . "',
            can_subscribe       = '" . (int) $data_params['can_subscribe'] . "',
            sub_start_date      = " . ($data_params['can_subscribe'] == '2' ? "'" . $sub_start_date . "'" : 'NULL') . ',
            sub_end_date        = ' . ($data_params['can_subscribe'] == '2' ? "'" . $sub_end_date . "'" : 'NULL') . ",

            advance             = '" . $data_params['advance'] . "',
            show_result         = '" . (isset($data_params['show_result']) ? 1 : 0) . "',

            use_logo_in_courselist = '" . (isset($data_params['use_logo_in_courselist']) ? '1' : '0') . "',

            auto_unsubscribe = '" . (int) $data_params['auto_unsubscribe'] . "',
            unsubscribe_date_limit = " . (isset($data_params['use_unsubscribe_date_limit']) && $data_params['use_unsubscribe_date_limit'] > 0 ? "'" . Format::dateDb($data_params['unsubscribe_date_limit'], 'date') . "'" : 'NULL') . '';

        if (isset($data_params['random_course_autoregistration_code'])) {
            $control = 1;
            $str = '';

            while ($control) {
                for ($i = 0; $i < 10; ++$i) {
                    $seed = mt_rand(0, 10);
                    if ($seed > 5) {
                        $str .= mt_rand(0, 9);
                    } else {
                        $str .= chr(mt_rand(65, 90));
                    }
                }

                $control_query = 'SELECT COUNT(*)' .
                    ' %lms_course' .
                    " WHERE autoregistration_code = '" . $str . "'";

                $control_result = sql_query($control_query);
                list($result) = sql_fetch_row($control_result);
                $control = $result;
            }

            $query_course .= ", autoregistration_code = '" . $str . "'";
        } else {
            $query_course .= ", autoregistration_code = '" . $data_params['course_autoregistration_code'] . "'";
        }

        if (!sql_query($query_course)) {
            // course save failed, delete uploaded file
            if ($file_sponsor != '') {
                sl_unlink($path . $file_sponsor);
            }
            if ($file_logo != '') {
                sl_unlink($path . $file_logo);
            }
            if ($file_material != '') {
                sl_unlink($path . $file_material);
            }
            if ($file_othermaterial != '') {
                sl_unlink($path . $file_othermaterial);
            }
            if ($file_demo != '') {
                sl_unlink($path . $file_demo);
            }

            return ['err' => '_err_course'];
        }

        // recover the id of the course inserted --------------------------------------------
        list($id_course) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));

        require_once _lms_ . '/admin/models/LabelAlms.php';
        $label_model = new LabelAlms();

        $label = $data_params['label'];

        $label_model->associateLabelToCourse($label, $id_course);

        // add this corse to the pool of course visible by the user that have create it -----
        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $adminManager->addAdminCourse($id_course, Docebo::user()->getIdSt());
        }

        //if the scs exist create a room ----------------------------------------------------
        if ($GLOBALS['where_scs'] !== false) {
            require_once _scs_ . '/lib/lib.room.php';

            $rules = [
                'room_name' => $data_params['course_name'],
                'room_type' => 'course',
                'id_source' => $id_course,
            ];
            $re = insertRoom($rules);
        }
        $course_idst = &DoceboCourse::createCourseLevel($id_course);

        // create the course menu -----------------------------------------------------------
        if (!createCourseMenuFromCustom($id_custom, $id_course, $course_idst)) {
            return ['err' => '_err_coursemenu'];
        }

        $res = [];

        if ($quota_exceeded) {
            $res['limit_reach'] = 1;
        }

        if ($error) {
            $res['err'] = '_err_course';
        } else {
            // Salvataggio CustomField
            require_once _adm_ . '/lib/lib.customfield.php';
            $extra_field = new CustomFieldList();
            $extra_field->setFieldArea('COURSE');
            $extra_field->storeFieldsForObj($id_course);

            //AUTO SUBSCRIPTION
            if (isset($data_params['auto_subscription']) && $data_params['auto_subscription'] == 1) {
                $userId = Docebo::user()->getIdSt();

                if (!$this->autoUserRegister($userId, $id_course)) {
                    exit('Error during autosubscription');
                }
            }
            $res['res'] = '_ok_course';
        }

        $course = new DoceboCourse($id_course);
        Events::trigger('lms.course.created', ['id_course' => $id_course, 'course' => $course, 'parameters' => $data_params]);

        return $res;
    }

    public function upCourse($id_course = null, $data_params = null)
    {
        if (is_null($data_params)) {
            // Backward compatibility.
            $data_params = $_POST;
        }

        if (is_null($id_course)) {
            // Backward compatibility.
            $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        }

        require_once _base_ . '/lib/lib.upload.php';
        require_once _base_ . '/lib/lib.multimedia.php';
        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.manmenu.php';

        $array_lang = Docebo::langManager()->getAllLangCode();
        $array_lang[] = 'none';

        $acl_man = &Docebo::user()->getAclManager();

        require_once _lms_ . '/admin/models/LabelAlms.php';
        $label_model = new LabelAlms();

        $label = $data_params['label'];

        $label_model->associateLabelToCourse($label, $id_course);

        // calc quota limit
        $quota = $data_params['course_quota'];
        if (isset($data_params['inherit_quota'])) {
            $quota = FormaLms\lib\Get::sett('course_quota');
            $data_params['course_quota'] = COURSE_QUOTA_INHERIT;
        }
        $quota = $quota * 1024 * 1024;

        $course_man = new DoceboCourse($id_course);
        $used = $course_man->getUsedSpace();

        if ($data_params['course_name'] == '') {
            $data_params['course_name'] = Lang::t('_NO_NAME', 'course', 'lms');
        }

        $boxDescription = $data_params['course_box_descr'];

        if (strlen($boxDescription) > self::boxDescrMaxLimit) {
            $res['err'] = '_err_course_box_descr_max_limit';

            return $res;
        }

        // restriction on course status ------------------------------------------
        $user_status = 0;
        if (isset($data_params['user_status'])) {
            foreach ($data_params['user_status'] as $status => $val) {
                $user_status |= (1 << $status);
            }
        }

        // level that will be showed in the course --------------------------------
        $show_level = 0;
        if (isset($data_params['course_show_level'])) {
            foreach ($data_params['course_show_level'] as $lv => $val) {
                $show_level |= (1 << $lv);
            }
        }

        // save the file uploaded -------------------------------------------------

        $error = false;
        $quota_exceeded = false;

        $path = FormaLms\lib\Get::sett('pathcourse');
        $path = '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . (substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

        $old_file_size = 0;
        if ((is_array($_FILES) && !empty($_FILES)) || (is_array($data_params['file_to_del']))) {
            sl_open_fileoperations();
        }

        // load user material ---------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_user_material',
            $data_params['old_course_user_material'],
            $path,
            ($quota != 0 ? $quota - $used : false),
            isset($data_params['file_to_del']['course_user_material'])
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_material = $arr_file['filename'];
        $used = $used + ($arr_file['new_size'] - $arr_file['old_size']);
        $old_file_size += $arr_file['old_size'];

        // course otheruser material -------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_otheruser_material',
            $data_params['old_course_otheruser_material'],
            $path,
            ($quota != 0 ? $quota - $used : false),
            isset($data_params['file_to_del']['course_otheruser_material'])
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_othermaterial = $arr_file['filename'];
        $used = $used + ($arr_file['new_size'] - $arr_file['old_size']);
        $old_file_size += $arr_file['old_size'];

        // course demo-----------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_demo',
            $data_params['old_course_demo'],
            $path,
            ($quota != 0 ? $quota - $used : false),
            isset($data_params['file_to_del']['course_demo'])
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_demo = $arr_file['filename'];
        $used = $used + ($arr_file['new_size'] - $arr_file['old_size']);
        $old_file_size += $arr_file['old_size'];

        // course sponsor---------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_sponsor_logo',
            $data_params['old_course_sponsor_logo'],
            $path,
            ($quota != 0 ? $quota - $used : false),
            isset($data_params['file_to_del']['course_sponsor_logo']),
            true
        );
        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_sponsor = $arr_file['filename'];
        $used = $used + ($arr_file['new_size'] - $arr_file['old_size']);
        $old_file_size += $arr_file['old_size'];

        // course logo-----------------------------------------------------------------------------------
        $arr_file = $this->manageCourseFile(
            'course_logo',
            $data_params['old_course_logo'],
            $path,
            ($quota != 0 ? $quota - $used : false),
            isset($data_params['file_to_del']['course_logo']),
            true,
            640,
            170
        );

        $error |= $arr_file['error'];
        $quota_exceeded |= $arr_file['quota_exceeded'];
        $file_logo = $arr_file['filename'];
        $used = $used + ($arr_file['new_size'] - $arr_file['old_size']);
        $old_file_size += $arr_file['old_size'];
        // ----------------------------------------------------------------------------------------------
        sl_close_fileoperations();

        $date_begin = Format::dateDb($data_params['course_date_begin'], 'date');
        $date_end = Format::dateDb($data_params['course_date_end'], 'date');

        if ($data_params['can_subscribe'] == '2') {
            $sub_start_date = Format::dateDb($data_params['sub_start_date'], 'date');
            $sub_end_date = Format::dateDb($data_params['sub_end_date'], 'date');
        }

        $hour_begin = '-1';
        $hour_end = '-1';
        if ($data_params['hour_begin']['hour'] != '-1') {
            $hour_begin = (strlen($data_params['hour_begin']['hour']) == 1 ? '0' . $data_params['hour_begin']['hour'] : $data_params['hour_begin']['hour']);
            if ($data_params['hour_begin']['quarter'] == '-1') {
                $hour_begin .= ':00';
            } else {
                $hour_begin .= ':' . $data_params['hour_begin']['quarter'];
            }
        }

        if ($data_params['hour_end']['hour'] != '-1') {
            $hour_end = (strlen($data_params['hour_end']['hour']) == 1 ? '0' . $data_params['hour_end']['hour'] : $data_params['hour_end']['hour']);
            if ($data_params['hour_end']['quarter'] == '-1') {
                $hour_end .= ':00';
            } else {
                $hour_end .= ':' . $data_params['hour_end']['quarter'];
            }
        }

        $data = Events::trigger('lms.course.updating', ['id_course' => $id_course, 'old_course' => $course_man, 'parameters' => $data_params]);
        $data_params = $data['parameters'];

        // update database ----------------------------------------------------
        $query_course = "
        UPDATE %lms_course
        SET code                = '" . $data_params['course_code'] . "',
            name                = '" . $data_params['course_name'] . "',
            idCategory          = '" . (int) $data_params['idCategory'] . "',
            description         = '" . $data_params['course_descr'] . "',
            box_description         = '" . $data_params['course_box_descr'] . "',
            lang_code           = '" . $array_lang[$data_params['course_lang']] . "',
            status              = '" . (int) $data_params['course_status'] . "',
            level_show_user     = '" . $show_level . "',
            subscribe_method    = '" . (int) $data_params['course_subs'] . "',
            idCategory          = '" . (int) $data_params['idCategory'] . "',
            credits             = '" . $data_params['credits'] . "',

            linkSponsor         = '" . $data_params['course_sponsor_link'] . "',

            imgSponsor          = '" . $file_sponsor . "',
            img_course          = '" . $file_logo . "',
            img_material        = '" . $file_material . "',
            img_othermaterial   = '" . $file_othermaterial . "',
            course_demo         = '" . $file_demo . "',

            mediumTime          = '" . $data_params['course_medium_time'] . "',
            sendCalendar        = '" . (isset($data_params['send_calendar']) ? 1 : 0) . "',
            permCloseLO         = '" . $data_params['course_em'] . "',
            userStatusOp        = '" . $user_status . "',
            difficult           = '" . $data_params['course_difficult'] . "',

            show_progress       = '" . (isset($data_params['course_progress']) ? 1 : 0) . "',
            show_time           = '" . (isset($data_params['course_time']) ? 1 : 0) . "',

            show_who_online     = '" . $data_params['show_who_online'] . "',

            show_extra_info     = '" . (isset($data_params['course_advanced']) ? 1 : 0) . "',
            show_rules          = '" . (int) $data_params['course_show_rules'] . "',

            direct_play         = '" . (isset($data_params['direct_play']) ? 1 : 0) . "',

            date_begin          = '" . $date_begin . "',
            date_end            = '" . $date_end . "',
            hour_begin          = '" . $hour_begin . "',
            hour_end            = '" . $hour_end . "',

            valid_time          = '" . (int) $data_params['course_day_of'] . "',

            min_num_subscribe   = '" . (int) $data_params['min_num_subscribe'] . "',
            max_num_subscribe   = '" . (int) $data_params['max_num_subscribe'] . "',

            course_type         = '" . $data_params['course_type'] . "',
            point_to_all        = '" . (isset($data_params['point_to_all']) ? $data_params['point_to_all'] : 0) . "',
            course_edition      = '" . (isset($data_params['course_edition']) ? $data_params['course_edition'] : 0) . "',
            selling             = '" . (isset($data_params['course_sell']) ? 1 : 0) . "',
            prize               = '" . (isset($data_params['course_prize']) ? $data_params['course_prize'] : 0) . "',
            policy_point        = '" . $data_params['policy_point'] . "',

            course_quota        = '" . $data_params['course_quota'] . "',

            allow_overbooking   = '" . (isset($data_params['allow_overbooking']) ? 1 : 0) . "',
            can_subscribe       = '" . (int) $data_params['can_subscribe'] . "',
            sub_start_date      = " . ($data_params['can_subscribe'] == '2' ? "'" . $sub_start_date . "'" : 'NULL') . ',
            sub_end_date        = ' . ($data_params['can_subscribe'] == '2' ? "'" . $sub_end_date . "'" : 'NULL') . ",

            advance             = '" . $data_params['advance'] . "',
            show_result         = '" . (isset($data_params['show_result']) ? 1 : 0) . "',


            use_logo_in_courselist = '" . (isset($data_params['use_logo_in_courselist']) ? '1' : '0') . "',

            auto_unsubscribe = '" . (int) $data_params['auto_unsubscribe'] . "',
            unsubscribe_date_limit = " . (isset($data_params['use_unsubscribe_date_limit']) && $data_params['use_unsubscribe_date_limit'] > 0 ? "'" . Format::dateDb($data_params['unsubscribe_date_limit'], 'date') . "'" : 'NULL') . '';

        if (isset($data_params['random_course_autoregistration_code'])) {
            $control = 1;
            $str = '';

            while ($control) {
                for ($i = 0; $i < 10; ++$i) {
                    $seed = mt_rand(0, 10);
                    if ($seed > 5) {
                        $str .= mt_rand(0, 9);
                    } else {
                        $str .= chr(mt_rand(65, 90));
                    }
                }

                $control_query = 'SELECT COUNT(*)' .
                    ' %lms_course' .
                    " WHERE autoregistration_code = '" . $str . "'" .
                    " AND idCourse <> '" . $id_course . "'";

                $control_result = sql_query($control_query);
                list($result) = sql_fetch_row($control_result);
                $control = $result;
            }

            $query_course .= ", autoregistration_code = '" . $str . "'";
        } else {
            $query_course .= ", autoregistration_code = '" . $data_params['course_autoregistration_code'] . "'";
        }

        $query_course .= " WHERE idCourse = '" . $id_course . "'";

        if (!sql_query($query_course)) {
            if ($file_sponsor != '') {
                sl_unlink($path . $file_sponsor);
            }
            if ($file_logo != '') {
                sl_unlink($path . $file_logo);
            }
            if ($file_material != '') {
                sl_unlink($path . $file_material);
            }
            if ($file_othermaterial != '') {
                sl_unlink($path . $file_othermaterial);
            }
            if ($file_demo != '') {
                sl_unlink($path . $file_demo);
            }

            $course_man->subFileToUsedSpace(false, $old_file_size);

            return ['err' => '_err_course'];
        }

        // cascade modify on all the edition of the course
        if (isset($data_params['cascade_on_ed']) && $id_course > 0) {
            $cinfo = $this->getInfo($id_course);
            $has_editions = $cinfo['course_edition'] > 0;
            $has_classrooms = $cinfo['course_type'] == 'classroom';

            if ($has_editions) {
                $query_editon = 'UPDATE %lms_course_editions '
                    . " SET code = '" . $data_params['course_code'] . "', "
                    . " name = '" . $data_params['course_name'] . "', "
                    . " description  = '" . $data_params['course_descr'] . "' "
                    . " WHERE id_course = '" . $id_course . "' ";
                sql_query($query_editon);
            }

            if ($has_classrooms) {
                $query_editon = 'UPDATE %lms_course_date '
                    . " SET code = '" . $data_params['course_code'] . "', "
                    . " name = '" . $data_params['course_name'] . "', "
                    . " description  = '" . $data_params['course_descr'] . "' "
                    . " WHERE id_course = '" . $id_course . "' ";
                sql_query($query_editon);
            }
        }

        $res = [];

        if ($quota_exceeded) {
            $res['limit_reach'] = 1;
        }

        // Salvataggio CustomField
        require_once _adm_ . '/lib/lib.customfield.php';
        $extra_field = new CustomFieldList();
        $extra_field->setFieldArea('COURSE');
        $extra_field->storeFieldsForObj($id_course);

        //AUTO SUBSCRIPTION
        $userId = Docebo::user()->getIdSt();
        $userSubscribed = $this->isUserSubscribedInCourse($userId, $id_course);
        if (intval($userSubscribed[0]) <= 0) {
            if (isset($data_params['auto_subscription']) && $data_params['auto_subscription'] == 1) {
                if (!$this->autoUserRegister($userId, $id_course)) {
                    exit('Error during autosubscription');
                }
            }
        }

        $res['res'] = '_ok_course';

        $new_course = new DoceboCourse($id_course);
        Events::trigger('lms.course.updated', ['id_course' => $id_course, 'old_course' => $course_man, 'new_course' => $new_course]);

        return $res;
    }

    public function manageCourseFile($new_file_id, $old_file, $path, $quota_available, $delete_old, $is_image = false, $width = 300, $height = 300)
    {
        $arr_new_file = (isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false);
        $return = [
            'filename' => $old_file,
            'new_size' => 0,
            'old_size' => 0,
            'error' => false,
            'quota_exceeded' => false,
        ];

        if (($delete_old || $arr_new_file !== false) && $old_file != '') {
            // the flag for file delete is checked or a new file was uploaded ---------------------
            $return['old_size'] = FormaLms\lib\Get::file_size(_files_ . $path . $old_file);
            if ($quota_available !== false) {
                $quota_available -= $return['old_size'];
            }
            sl_unlink($path . $old_file);
            $return['filename'] = '';
        }

        if (!empty($arr_new_file)) {
            // if present load the new file --------------------------------------------------------
            $filename = $new_file_id . '_' . mt_rand(0, 100) . '_' . time() . '_' . str_replace(' ', '_', $arr_new_file['name']);
            if ($is_image) {
                $re = createImageFromTmp(
                    $arr_new_file['tmp_name'],
                    $path . $filename,
                    $arr_new_file['name'],
                    $width,
                    $height,
                    true
                );

                if ($re < 0) {
                    $return['error'] = true;
                } else {
                    // after resize check size ------------------------------------------------------------
                    $size = FormaLms\lib\Get::file_size(_files_ . $path . $filename);
                    if ($quota_available != 0 && $size > $quota_available) {
                        $return['quota_exceeded'] = true;
                        sl_unlink($path . $filename);
                    } else {
                        $return['new_size'] = $size;
                        $return['filename'] = $filename;
                    }
                }
            } else {
                // check if the filesize don't exceed the quota ----------------------------------------
                $size = FormaLms\lib\Get::file_size($arr_new_file['tmp_name']);

                if ($quota_available != 0 && $size > $quota_available) {
                    $return['quota_exceeded'] = true;
                } else {
                    // save file ---------------------------------------------------------------------------
                    if (!sl_upload($arr_new_file['tmp_name'], $path . $filename)) {
                        $return['error'] = true;
                    } else {
                        $return['new_size'] = $size;
                        $return['filename'] = $filename;
                    }
                }
            }
        }

        return $return;
    }

    public function delCourse($id_course)
    {
        if ((int) $id_course <= 0) {
            return false;
        }

        require_once _lms_ . '/lib/lib.course.php';
        require_once _base_ . '/lib/lib.upload.php';

        $course_man = new Man_Course();

        $course = new DoceboCourse($id_course);
        if (!$course->getAllInfo()) {
            return false;
        }

        Events::trigger('lms.course.deleting', ['id_course' => $id_course, 'course' => $course]);

        //remove course subscribed------------------------------------------

        $levels = &$course_man->getCourseIdstGroupLevel($id_course);
        foreach ($levels as $lv => $idst) {
            Docebo::aclm()->deleteGroup($idst);
        }

        $alluser = getIDGroupAlluser($id_course);
        Docebo::aclm()->deleteGroup($alluser);
        $course_man->removeCourseRole($id_course);
        $course_man->removeCourseMenu($id_course);

        $query = "DELETE FROM %lms_courseuser WHERE idCourse = '" . (int) $id_course . "'";
        $qres = sql_query($query);
        if (!$qres) {
            return false;
        }

        //--- remove course data ---------------------------------------------------

        $query_course = "SELECT imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_type
            FROM %lms_course
            WHERE idCourse = '" . (int) $id_course . "'";
        $qres = sql_query($query_course);
        list($file_sponsor, $file_logo, $file_material, $file_othermaterial, $file_demo, $course_type, $course_edition) = sql_fetch_row($qres);

        require_once _base_ . '/lib/lib.upload.php';

        $path = '/appLms/' . FormaLms\lib\Get::sett('pathcourse');
        if (substr($path, -1) != '/' && substr($path, -1) != '\\') {
            $path .= '/';
        }
        sl_open_fileoperations();
        if ($file_sponsor != '') {
            sl_unlink($path . $file_sponsor);
        }
        if ($file_logo != '') {
            sl_unlink($path . $file_logo);
        }
        if ($file_material != '') {
            sl_unlink($path . $file_material);
        }
        if ($file_othermaterial != '') {
            sl_unlink($path . $file_othermaterial);
        }
        if ($file_demo != '') {
            sl_unlink($path . $file_demo);
        }
        sl_close_fileoperations();

        //if the scs exist delete course rooms
        if (_scs_ !== false) {
            require_once _scs_ . '/lib/lib.room.php';
            $re = deleteRoom(false, 'course', $id_course);
        }

        //--- delete classroom or editions -----------------------------------------
        if ($course_type == 'classroom') {
            require_once _lms_ . '/admin/models/ClassroomAlms.php';

            $classroom_model = new ClassroomAlms($id_course);

            $classroom = $classroom_model->classroom_man->getDateIdForCourse($id_course);

            foreach ($classroom as $id_date) {
                if (!$classroom_model->classroom_man->delDate($id_date)) {
                    return false;
                }
            }
        } elseif ($course_edition == 1) {
            require_once _lms_ . '/admin/model/EditionAlms.php';
            $edition_model = new EditionAlms($id_course);

            $editions = $edition_model->classroom_man->getEditionIdFromCourse($id_course);

            foreach ($editions as $id_edition) {
                if (!$edition_model->edition_man->delEdition($id_edition)) {
                    return false;
                }
            }
        }
        //--- end classrooms or editions -------------------------------------------

        //--- clear LOs ------------------------------------------------------------

        require_once _lms_ . '/lib/lib.module.php';
        require_once _lms_ . '/lib/lib.param.php';
        require_once _lms_ . '/class.module/track.object.php';

        $arr_lo_param = [];
        $arr_lo_track = [];
        $arr_org_access = [];

        $query = 'SELECT * FROM %lms_organization WHERE idCourse = ' . (int) $id_course;
        $ores = sql_query($query);
        while ($obj = sql_fetch_object($ores)) {
            $deleted = true;
            if ($obj->idResource != 0 && $obj->objectType != '') {
                $lo = createLO($obj->objectType);
                $deleted = $lo->del($obj->idResource); //delete learning object
            }
            if ($deleted) {
                $arr_lo_track[] = $obj->idOrg;
                $arr_org_access[] = $obj->idOrg; //collect org access ids
                $arr_lo_param[] = $obj->idParam; //collect idParams ids
            }
        }

        //delete all organizations references for the course
        $query = 'DELETE FROM %lms_organization WHERE idCourse = ' . (int) $id_course;
        $res = sql_query($query);

        //delete LOs trackings
        if (!empty($arr_lo_track)) {
            $track_object = new Track_Object(false, 'course_lo');
            $track_object->delIdTrackFromCommon($arr_lo_track);
        }

        //delete org accesses
        if (!empty($arr_org_access)) {
            $query = 'DELETE FROM %lms_organization_access
                WHERE idOrgAccess IN (' . implode(',', $arr_org_access) . ')';
            $res = sql_query($query);
        }

        //delete lo params
        if (!empty($arr_lo_param)) {
            $query = 'DELETE FROM %lms_lo_param
                WHERE idParam IN (' . implode(',', $arr_lo_param) . ')';
        }

        //--- end LOs --------------------------------------------------------------

        //--- clear coursepath references ------------------------------------------
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cman = new CoursePath_Manager();
        $cman->deleteCourseFromCoursePaths($id_course);
        //--- end coursepath references --------------------------------------------

        //--- clear certificates assignments ---------------------------------------
        require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');
        $cman = new Certificate();
        $cman->deleteCourseCertificateAssignments($id_course);
        //--- end certificates assignments -----------------------------------------

        //--- clear labels ---------------------------------------------------------
        $lmodel = new LabelAlms();
        $lmodel->clearCourseLabel($id_course);
        //--- end labels -----------------------------------------------------------

        //--- clear advices --------------------------------------------------------
        require_once _lms_ . '/lib/lib.advice.php';
        $aman = new Man_Advice();
        $aman->deleteAllCourseAdvices($id_course);
        //--- end advices ----------------------------------------------------------

        //--- clear coursereports --------------------------------------------------
        require_once _lms_ . '/lib/lib.coursereport.php';
        $cman = new CourseReportManager($id_course);
        $cman->deleteAllReports($id_course);
        //--- end coursereports ----------------------------------------------------

        //--- clear competences ----------------------------------------------------
        $cmodel = new CompetencesAdm();
        $cmodel->deleteAllCourseCompetences($id_course);
        //--- end competences ------------------------------------------------------

        //remove customfield
        if (!sql_query('DELETE FROM ' . $GLOBALS['prefix_fw'] . "_customfield_entry WHERE id_field IN (SELECT id_field FROM core_customfield WHERE area_code = 'COURSE') AND id_obj = '" . $id_course . "'")) {
            return false;
        }

        //--- finally delete course from courses table -----------------------------
        if (!sql_query("DELETE FROM %lms_course WHERE idCourse = '" . $id_course . "'")) {
            return false;
        }

        Events::trigger('lms.course.deleted', ['id_course' => $id_course, 'course' => $course]);

        return true;
    }

    public function hasEditionsOrClassrooms($id_course)
    {
        if ($this->edition_man->getEditionNumber($id_course) > 0) {
            return true;
        }
        if ($this->classroom_man->getDateNumber($id_course, true) > 0) {
            return true;
        }

        return false;
    }

    public function getInfo($id_course = false, $id_edition = false, $id_date = false)
    {
        $_id_course = ($id_course ? $id_course : $this->id_course);
        $_id_edition = ($id_edition ? $id_edition : $this->id_edition);
        $_id_date = ($id_date ? $id_date : $this->id_date);

        if (!$_id_course) {
            return false;
        }
        if (!$_id_edition && !$_id_date) {
            return $this->course_man->getCourseInfo($_id_course);
        }
        if ($_id_edition > 0) {
            return $this->edition_man->getEditionInfo($_id_edition);
        }
        if ($_id_date > 0) {
            return $this->classroom_man->getDateInfo($_id_date);
        }

        return false;
    }

    public function getCategoryForDropdown()
    {
        $query = 'SELECT idCategory, path, lev'
            . ' FROM %lms_category'
            . ' ORDER BY path';

        $result = sql_query($query);
        $res = ['0' => 'root'];

        while (list($id_cat, $path, $level) = sql_fetch_row($result)) {
            $name = end(explode('/', $path));

            for ($i = 0; $i < $level; ++$i) {
                $name = '&nbsp;&nbsp;' . $name;
            }

            $res[$id_cat] = $name;
        }

        return $res;
    }

    public function getCategoryName($id_category)
    {
        if ($id_category == 0) {
            return 'root';
        }

        $query = 'SELECT path'
            . ' FROM %lms_category'
            . ' WHERE idCategory = ' . (int) $id_category;

        list($path) = sql_fetch_row(sql_query($query));

        return end(explode('/', $path));
    }

    public function getCourseWithCertificate()
    {
        $query = 'SELECT DISTINCT id_course'
            . ' FROM %lms_certificate_course';

        $result = sql_query($query);
        $res = [];

        while (list($id_course) = sql_fetch_row($result)) {
            $res[$id_course] = $id_course;
        }

        return $res;
    }

    public function getCourseWithCompetence()
    {
        $query = 'SELECT DISTINCT id_course'
            . ' FROM %lms_competence_course';

        $result = sql_query($query);
        $res = [];

        while (list($id_course) = sql_fetch_row($result)) {
            $res[$id_course] = $id_course;
        }

        return $res;
    }

    public function getCoursesStudentsNumber($courses)
    {
        if (is_numeric($courses)) {
            $courses = [(int) $courses];
        }
        if (!is_array($courses) || empty($courses)) {
            return false;
        }

        $usersFilterIds = $this->getAdminRelatedUserIds();
        $output = [];
        foreach ($courses as $idCourse) {
            $output[$idCourse] = 0;
        }

        $query = 'SELECT idCourse, COUNT(*) as count FROM %lms_courseuser '
            . ' WHERE idCourse IN (' . implode(',', $courses) . ') '
            . ' AND LEVEL = 3 AND waiting <= 0 and status >= 0 ';

        if (count($usersFilterIds)) {
            $query .= 'AND idUser in (' . implode(',', $usersFilterIds) . ')';
        }

        $query .= ' GROUP BY idCourse';

        $res = sql_query($query);

        foreach ($res as $row) {
            $output[$row['idCourse']] = (int) $row['count'];
        }

        $class_real_count = 'SELECT cu.idCourse as idCourse,COUNT(*) as count FROM %lms_courseuser AS cu 
                JOIN %lms_course_date AS cd JOIN %lms_course_date_user AS cdu '
            . ' ON (cd.id_date = cdu.id_date AND cd.id_course = cu.idCourse AND cu.idUser = cdu.id_user) '
            . ' WHERE cu.idCourse IN (' . implode(',', $courses) . ') AND cu.level = 3 and cu.status >= 0 and cu.status < 4 ';

        if (count($usersFilterIds)) {
            $class_real_count .= 'AND cu.idUser in (' . implode(',', $usersFilterIds) . ')';
        }

        $class_real_count .= 'GROUP BY cu.idCourse';

        $resClass = sql_query($class_real_count);
        foreach ($resClass as $row) {
            $output[$row['idCourse']] = (int) $row['count'];
        }

        return $output;
    }

    private function autoUserRegister($idMember, $idCourse)
    {
        $query = "SELECT idst FROM %adm_group WHERE groupid = ('/lms/course/" . $idCourse . "/subscribed/7')";
        $res = sql_query($query);

        $idst = sql_fetch_row($res);

        sql_query('START TRANSACTION');

        $query = "INSERT INTO %adm_group_members (idst, idstMember, filter) VALUES ('" . $idst[0] . "','" . $idMember . "','')";

        if (sql_query($query)) {
            $row = $this->isUserSubscribedInCourse($idMember, $idCourse);

            if ($row[0] == 0) {
                $query = "INSERT INTO %lms_courseuser (idUser, idCourse, LEVEL, waiting, subscribed_by, date_inscr) VALUES ('" . $idMember . "', '" . $idCourse . "', '7', '0', '" . $idMember . "', 'now()')";

                if (sql_query($query)) {
                    sql_query('COMMIT');

                    return true;
                }
            } else {
                sql_query('ROLLBACK');

                return false;
            }
        }

        return false;
    }

    public function isUserSubscribedInCourse($idMember, $idCourse)
    {
        $query = 'SELECT COUNT(*) FROM %lms_courseuser WHERE idUser = ' . $idMember . ' AND idCourse = ' . $idCourse;

        $res = sql_query($query);

        $row = sql_fetch_row($res);

        return $row;
    }

    public function getAdminRelatedUserIds()
    {
        $usermanagementAdm = new UsermanagementAdm();
        $usersFilterIds = [];
        $currentUser = Docebo::user();
        if (ADMIN_GROUP_ADMIN == $currentUser->getUserLevelId()) {
            $nodes = $usermanagementAdm->getAdminFolders($currentUser->getIdSt(), true);

            foreach ($nodes as $node) {
                $pagination['results'] = $usermanagementAdm->getTotalUsers($node, false);

                $usersFilterIds = array_merge($usersFilterIds, array_keys($usermanagementAdm->getUsersList($node, true, $pagination)));
            }
        }

        return $usersFilterIds;
    }

    public function getListTototalUserCertificate($id_course, $id_certificate, $cf)
    {
        require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');
        $regset = Format::instance();
        $usermanagementAdm = new UsermanagementAdm();
        $date_format = $regset->date_token;
        $users = [];

        $usersFilterIds = $this->getAdminRelatedUserIds();

        $query = "SELECT u.idst, u.userid, u.firstname, u.lastname,
                         DATE_FORMAT(cu.date_complete,'" . $date_format . "') as dateComplete, DATE_FORMAT(ca.on_date,'" . $date_format . "') onDate, cu.idUser as id_user,
                         cu.status , cu.idCourse, cc.id_certificate,
                         c.name as name_certificate"
            . ' FROM ( %adm_user as u JOIN %lms_courseuser as cu ON (u.idst = cu.idUser) ) '
            . ' JOIN %lms_certificate_course as cc ON cc.id_course = cu.idCourse '
            . ' JOIN %lms_certificate as c ON c.id_certificate = cc.id_certificate'
            . ' LEFT JOIN %lms_certificate_assign as ca ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser AND ca.id_certificate = cc.id_certificate ) '
            . ' LEFT JOIN (SELECT iduser, idcourse, SUM( (UNIX_TIMESTAMP( lastTime ) - UNIX_TIMESTAMP( enterTime ) ) ) elapsed from learning_tracksession group by iduser, idcourse) t_elapsed on t_elapsed.idcourse=cu.idCourse and cu.idUser = t_elapsed.idUser '
            . ' WHERE 1 '
            . ($id_certificate != 0 ? ' AND cc.id_certificate = ' . $id_certificate : '')
            . ' AND coalesce(elapsed,0) >= coalesce(cc.minutes_required,0)*60 '
            . " AND cu.idCourse='" . (int) $id_course . "'";

        if (count($usersFilterIds)) {
            $query .= 'AND u.idst in (' . implode(',', $usersFilterIds) . ')';
        }

        $res = sql_query($query);

        foreach ($res as $row) {
            $idst = $row['idst'];
            $userid = $row['userid'];
            $firstname = $row['firstname'];
            $lastname = $row['lastname'];
            $date_complete = $row['dateComplete'];
            $on_date = $row['onDate'];
            $id_user = $row['id_user'];
            $status = $row['status'];
            $id_course = $row['idCourse'];
            $id_certificate = $row['id_certificate'];
            $name_certificate = $row['name_certificate'];

            foreach ($cf as $i => $value) {
                $cf[$i] = '';
            }
            $url = 'index.php?modname=certificate&amp;certificate_id=' . $id_certificate . '&amp;course_id=' . $id_course . '&amp;user_id=' . $id_user . '&amp;of_platform=lms';
            if ($on_date != null) {
                $operation_url = $url . '&amp;op=send_certificate';
                $cell_down_gen = "<a href='" . $operation_url . "' class='ico-wt-sprite subs_pdf'>" . Lang::t('_DOWNLOAD', 'certificate') . '</a>';
                $cell_del_cert = FormaLms\lib\Get::sprite_link('subs_del', $url . '&op=del_report_certificate&from=' . FormaLms\lib\Get::req('from'), Lang::t('_DEL', 'certificate'));
            } else {
                $operation_url = $url . '&amp;op=print_certificate';
                $generate = 'javascript:print_certificate(' . $id_user . ',' . $id_course . ',' . $id_certificate . ')';
                $cell_down_gen = "<a href='" . $generate . "' class='ico-wt-sprite subs_pdf'>" . Lang::t('_GENERATE', 'certificate') . '</a>';
                $cell_del_cert = '';
            }

            $user1 = [
                'id_user' => $id_user, 'id_certificate' => $id_certificate, 'edition' => $this->getInfoClassroom($id_user, $id_course), 'username' => substr($userid, 1),
                'lastname' => $lastname, 'firstname' => $firstname,
            ];
            // getting custom fields values
            $cf_values = $usermanagementAdm->getCustomFieldUserValues((int) $id_user);
            $cf = array_replace($cf, $cf_values);
            $user2 = [];
            foreach ($cf as $key => $value) {
                $user2["cf_$key"] = $value;
            }
            $user3 = ['status' => $status, 'name_certificate' => $name_certificate, 'date_complete' => $date_complete, 'on_date' => $on_date, 'cell_down_gen' => $cell_down_gen, 'cell_del_cert' => $cell_del_cert];

            $users[] = array_merge($user1, $user2, $user3);
        }

        return $users;
    }

    protected function getInfoClassroom($id_user, $id_course)
    {
        $query = "SELECT cd.code, cd.name
                 FROM %lms_course_date AS cd
                 INNER JOIN %lms_course_date_user cdu ON cd.id_date = cdu.id_date
                 WHERE id_course = $id_course
                 AND cdu.id_user = $id_user
                 ORDER BY cd.id_date DESC LIMIT 1";
        // AND cdu.date_complete <> '0000-00-00 00:00:00'

        return sql_fetch_row(sql_query($query)) ?: '';
    }
}
