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

if (Docebo::user()->isAnonymous()) {
    exit("You can't access");
}

require_once _lms_ . '/lib/lib.levels.php';
require_once _lms_ . '/lib/lib.course.php';

function mycourses(&$url)
{
    checkPerm('view');
    require_once _base_ . '/lib/lib.user_profile.php';
    $lang = &DoceboLanguage::createInstance('catalogue');

    require_once _lms_ . '/lib/lib.middlearea.php';
    $ma = new Man_MiddleArea();

    $course_stats = userCourseList($url, $ma->currentCanAccessObj('lo_tab'));

    $access_career = $ma->currentCanAccessObj('career');
    $access_news = $ma->currentCanAccessObj('news');
    $access_search_form = $ma->currentCanAccessObj('search_form');
    $access_user_details_full = $ma->currentCanAccessObj('user_details_full');
    $access_user_details_short = $ma->currentCanAccessObj('user_details_short');

    $onecol = (!$access_career && !$access_news && !$access_user_details_full && !$access_user_details_short);

    require_once _adm_ . '/lib/lib.myfriends.php';
    $friends = new MyFriends(getLogUserId());
    $pendent = count($friends->getPendentRequest());

    $GLOBALS['page']->addStart(''
        . '<div id="mycourse_top">'
        . ($onecol ? '' : '<div class="mycourse_left">'), 'content');

    // user_details_short ------------------------------------------------------------------------

    if ($access_user_details_short) {
        $profile = new UserProfile(getLogUserId());
        $profile->init('profile', 'framework', 'index.php?' . FormaLms\lib\Get::home_page_query(), 'ap');
        $GLOBALS['page']->addStart($profile->userIdMailProfile('normal', false, false), 'content');
    }
    // user_details_full ------------------------------------------------------------------------
    if ($access_user_details_full) {
        $profile = new UserProfile(getLogUserId());
        $profile->init('profile', 'framework', 'index.php?' . FormaLms\lib\Get::home_page_query(), 'ap');
        $GLOBALS['page']->addStart($profile->homeUserProfile('normal', false, false), 'content');
    }
    // career ------------------------------------------------------------------------
    if ($access_career) {
        $base_url = 'index.php?' . FormaLms\lib\Get::home_page_query() . '&amp;filter=';
        $end = 0;
        if (isset($course_stats['with_ustatus'][_CUS_END]) && $course_stats['with_ustatus'][_CUS_END] != 0) {
            $end = $course_stats['with_ustatus'][_CUS_END];
        }

        $GLOBALS['page']->addStart(''
            . '<div class="course_stat">'
            . '<table summary="">'
            . '<caption>' . $lang->def('_CAREER') . '</caption>'

            . '<tr><th scope="row">' . $lang->def('_TOTAL_COURSE') . ' :</th><td><a href="' . $base_url . 'nothing">' . ($course_stats['total'] - $end) . '</a></td></tr>'

            . (isset($course_stats['with_ustatus'][_CUS_END]) && $course_stats['with_ustatus'][_CUS_END] != 0
                ? '<tr><th scope="row">' . $lang->def('_COURSE_END') . ' :</th><td><a href="' . $base_url . 'end">' . $course_stats['with_ustatus'][_CUS_END] . '</a></td></tr>'
                : '')

            . (isset($course_stats['expiring']) && $course_stats['expiring'] != 0
                ? '<tr><th scope="row">' . $lang->def('_COURSE_EXPIRING') . ' :</th><td><a href="' . $base_url . 'expiring">' . $course_stats['expiring'] . '</a></td></tr>'
                : ''), 'content');

        if (count($course_stats['with_ulevel']) > 1) {
            require_once _lms_ . '/lib/lib.levels.php';

            $lvl = CourseLevel::getTranslatedLevels();

            foreach ($course_stats['with_ulevel'] as $lvl_num => $quantity) {
                $GLOBALS['page']->addStart(''
                    . '<tr><th scope="row">' . str_replace('[level]', $lvl[$lvl_num], $lang->def('_COURSE_AS')) . ' :</th><td><a href="' . $base_url . 'level&amp;filter_on=' . $lvl_num . '">' . $quantity . '</a></td></tr>', 'content');
            } //end foreach
        }

        require_once _lms_ . '/lib/lib.aggregated_certificate.php';
        $aggrCertLib = new AggregatedCertificate();

        $query = 'SELECT c.idAssociation, m.idCertificate'
                    . ' FROM ' . $GLOBALS['prefix_lms'] . $aggrCertLib->table_cert_meta_association_courses . ' as c'
                    . ' JOIN ' . $GLOBALS['prefix_lms'] . $aggrCertLib->table_cert_meta_association . ' AS m ON c.idAssociation = m.idAssociation'
                    . " WHERE c.idUser = '" . getLogUserId() . "'"
                    . ' GROUP BY c.idAssociation'
                    . ' ORDER BY m.title, m.description';

        $result = sql_query($query);

        $num_meta_cert = sql_num_rows($result);

        while (list($id_meta, $id_certificate) = sql_fetch_row($result)) {
            $query_released = 'SELECT on_date'
                                . ' FROM ' . $GLOBALS['prefix_lms'] . $aggrCertLib->table_assign_agg_cert
                                . " WHERE idUser = '" . getLogUserId() . "'"
                                . " AND idCertificate = '" . $id_certificate . "'";

            $result_released = sql_query($query_released);

            $query = 'SELECT user_release'
                        . ' FROM %lms_certificate'
                        . " WHERE id_certificate = '" . $id_certificate . "'";

            list($user_release) = sql_fetch_row(sql_query($query));

            if (sql_num_rows($result_released)) {
            } elseif ($user_release == 0) {
                --$num_meta_cert;
            } else {
                /* $query =	"SELECT idCourse"
                             ." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
                             ." WHERE idUser = '".getLogUserId()."'"
                             ." AND idMetaCertificate = '".$id_meta."'";

                 $result_int = sql_query($query);*/

                $assocArr = $aggrCertLib->getAssociationLink($id_meta, AggregatedCertificate::AGGREGATE_CERTIFICATE_TYPE_COURSE, getLogUserId());

                $control = true;

                foreach ($assocArr as $assoc) {
                    $query = 'SELECT COUNT(*)'
                                . ' FROM %lms_courseuser'
                                . " WHERE idCourse = '" . $assoc . "'"
                                . " AND idUser = '" . getLogUserId() . "'"
                                . " AND status = '" . _CUS_END . "'";

                    list($number) = sql_fetch_row(sql_query($query));

                    if (!$number) {
                        $control = false;
                    }
                }

                if (!$control) {
                    --$num_meta_cert;
                }
            }
        }

        $tot_cert = $num_meta_cert + $course_stats['cert_relesable'];

        $GLOBALS['page']->addStart(''
            . (isset($course_stats['cert_relesable']) && $tot_cert != 0
                ? '<tr><th scope="row">' . $lang->def('_CERT_RELESABLE') . ' :</th><td><a href="index.php?r=lms/mycertificate/show">' . $tot_cert . '</a></td></tr>'
                : '')

            . ($pendent != 0
                ? '<tr><th scope="row">' . $lang->def('_FRIEND_PENDENT') . ' :</th><td><a href="index.php?modname=myfriends&amp;op=myfriends">' . $pendent . '</a></td></tr>'
                : '')

            . '</table>'
            . '</div>', 'content');
    }

    // career ------------------------------------------------------------------------
    if ($access_search_form) {
        $year_array = [0 => $lang->def('_ALL_YEAR')];
        $query_year = 'SELECT DISTINCT create_date' .
            ' FROM %lms_course';

        $result = sql_query($query_year);
        while (list($year) = sql_fetch_row($result)) {
            $year_array[$year[0] . $year[1] . $year[2] . $year[3]] = $year[0] . $year[1] . $year[2] . $year[3];
        }
        if (isset($year_array['0000'])) {
            unset($year_array['0000']);
        }

        $GLOBALS['page']->addStart(''
            . '<div class="course_search">'
            . '<h2>' . $lang->def('_SEARCH') . '</h2>'
            . Form::openForm('course_filter', 'index.php?modname=course&amp;op=mycourses')

            . '<p>'
                . Form::getLabel('search', $lang->def('_WORD'))
            . '</p>'
            . Form::getInputTextfield('textfield_nowh', 'search', 'search', importVar('search'), $lang->def('_WORD'), '255', '') . '<br/>'

            . '<p>'
                . Form::getLabel('year', $lang->def('_YEAR'))
            . '</p>'
            . Form::getInputDropdown('dropdown_nowh', 'year', 'year', $year_array, importVar('year'), '')

            . Form::getButton('apply_filter', 'apply_filter', $lang->def('_SEARCH'))
            . Form::closeForm()
            . '</div>', 'content');
    }
    // news ------------------------------------------------------------------------

    if ($access_news) {
        $GLOBALS['page']->addStart(''
            . '<div class="course_news">'
            . '<h2>' . $lang->def('_NEWS') . '</h2>', 'content');

        $user_level = Docebo::user()->getUserLevelId();

        $user_assigned = Docebo::user()->getArrSt();

        $query_news = '
		SELECT idNews, publish_date, title, short_desc, important, viewer
		FROM ' . $GLOBALS['prefix_lms'] . "_news_internal
		WHERE language = '" . getLanguage() . "'
		ORDER BY important DESC, publish_date DESC ";
        $re_news = sql_query($query_news);

        $displayed = 0;
        while (list($id_news, $publish_date, $title, $short_desc, $impo, $viewer) = sql_fetch_row($re_news)) {
            $viewer = (is_string($viewer) && $viewer != false ? unserialize($viewer) : []);
            $intersect = array_intersect($user_assigned, $viewer);
            if (!empty($intersect) || empty($viewer)) {
                $GLOBALS['page']->addStart(
                    '<h3>' . $title . '</h3>'
                    . '<div class="news_textof">'
                    . '<span class="news_data">' . Format::date($publish_date, 'date') . ' - </span>'
                        . $short_desc
                    . '</div>', 'content');
                ++$displayed;
            }
        } // end news display
        if (!$displayed) {
            $GLOBALS['page']->addStart($lang->def('_NO_CONTENT'), 'content');
        }

        $GLOBALS['page']->addStart(''
            . '</div>', 'content');
    }
    if (!$onecol) {
        $GLOBALS['page']->addStart(''
            . '</div>', 'content');
        $GLOBALS['page']->addStart(''
            . '<div id="mycourse_right">', 'content');
    }

    // ------------------------------------------------------------------------
    if (!$onecol) {
        $GLOBALS['page']->addEnd(''
            . '</div>'
            . '<div class="nofloat"></div>', 'content');
    }

    $GLOBALS['page']->addEnd(''
        . '</div>', 'content');

    if ($ma->currentCanAccessObj('lo_tab')) {
        $current_tab = importVar('current_tab', false, 'lo_plan');

        $GLOBALS['page']->addStart(
            '<div class="lo_tab">'

            . '<h1>'
                . $lang->def('_WELCOME') . ': '
                . '<span>' . Docebo::user()->getUserName() . '</span>'
            . '</h1>'

            . '<ul class="flat_tab">'

            . ($course_stats['with_ustatus'][_CUS_END] != $course_stats['total'] ?
                '<li ' . ($current_tab == 'lo_plan' ? 'class="now_selected"' : '') . '>'
                . '<a href="index.php?modname=course&amp;op=mycourses&amp;current_tab=lo_plan"><span>' . $lang->def('_COURSE') . '</span></a></li>'
                : ''
            )
            . ($course_stats['with_ustatus'][_CUS_END] != 0 ?
                '<li ' . ($current_tab == 'lo_history' ? 'class="now_selected"' : '') . '>'
                    . '<a href="index.php?modname=course&amp;op=mycourses&amp;current_tab=lo_history"><span>' . $lang->def('_COMPLETED') . '</span></a></li>'
                : ''
            )
            . ($course_stats['with_wstatus'][_CUS_RESERVED] != 0 || $course_stats['with_wstatus'][_CUS_WAITING_LIST] != 0 ?
                '<li ' . ($current_tab == 'lo_waiting' ? 'class="now_selected"' : '') . '>'
                    . '<a href="index.php?modname=course&amp;op=mycourses&amp;current_tab=lo_waiting"><span>' . $lang->def('_LO_WAITING') . '</span></a></li>'
                : ''
            )
            . '</ul>'
            . '</div>', 'content');
    } else {
        $GLOBALS['page']->addStart(
            '<div class="lo_tab">'
            . '<h1 class="no_tab">'
                . $lang->def('_WELCOME') . ': '
                . '<span>' . Docebo::user()->getUserName() . '</span>'
            . '</h1>'
            . '</div>', 'content');
    }
}

function userCourseList(&$url, $use_tab = true, $page_add = true)
{
    YuiLib::load([
        'animation' => 'animation-min.js',
        'dragdrop' => 'dragdrop-min.js',
        'button' => 'button-min.js',
        'container' => 'container-min.js',
        'my_window' => 'windows.js',
    ], [
        'container/assets/skins/sam' => 'container.css',
        'button/assets/skins/sam' => 'button.css',
    ]);

    if ($page_add) {
        addJs($GLOBALS['where_lms_relative'] . '/modules/coursecatalogue/', 'ajax.coursecatalogue.js');
    }

    require_once _base_ . '/lib/lib.form.php';
    require_once Forma::inc(_lib_ . '/lib.user_profile.php');
    require_once _base_ . '/lib/lib.navbar.php';
    require_once _lms_ . '/lib/lib.preassessment.php';
    require_once _lms_ . '/lib/lib.catalogue.php';
    require_once _lms_ . '/lib/lib.course.php';
    require_once _lms_ . '/lib/lib.coursereport.php';
    require_once _lms_ . '/lib/lib.coursepath.php';
    require_once _adm_ . '/lib/lib.ajax_comment.php';
    require_once _lms_ . '/lib/lib.classroom.php';

    // pre-loading coursepath ------------------------------------------------------------------
    $path_man = new CoursePath_Manager();

    // search for the coursepath ----------------------------------------------------------
    $user_coursepath = $path_man->getUserSubscriptionsInfo(getLogUserId(), true);
    $coursepath = $path_man->getCoursepathAllInfo(array_keys($user_coursepath));

    if (!empty($coursepath)) {
        // find structures of the course path ---------------------------------------------
        $path_courses = $path_man->getPathStructure(array_keys($coursepath));
        $cp_info = $path_man->getAllCoursesInfo($path_courses['all_paths']);
        /*echo "<pre>\n\n";
        print_r($cp_info);
        echo "\n\n</pre>";*/
    }

    // ------------------------------------------------------------------------
    $course_stats = [
        'total' => 0,
        'u_can_enter' => 0,
        'with_status' => [
            CST_AVAILABLE => 0,
            CST_EFFECTIVE => 0,
            CST_CONCLUDED => 0,
            CST_CANCELLED => 0,
        ],
        'with_ustatus' => [
            _CUS_SUBSCRIBED => 0,
            _CUS_BEGIN => 0,
            _CUS_END => 0,
            _CUS_SUSPEND => 0,
        ],
        'with_wstatus' => [
            _CUS_WAITING_LIST => 0,
        ],
        'with_ulevel' => [],
        'expiring' => 0,
        'cert_relesable' => 0,
    ];
    // ------------------------------------------------------------------------

    $filter = importVar('filter', false, 'total');

    if ($filter == 'level') {
        $filter_level = importVar('filter_on', true, 0);
    }

    $current_tab = importVar('current_tab', false, 'lo_plan');

    if ($use_tab && $page_add) {
        addCss('style_tab');

        $lo_plan = importVar('lo_plan', false, 0);
        $lo_history = importVar('lo_history', false, 0);

        if ($lo_plan != 0) {
            $current_tab = 'lo_plan';
        }
        if ($lo_history != 0) {
            $current_tab = 'lo_history';
        }
    }

    if ($use_tab && $page_add) {
        addCss('style_tab');

        $lo_plan = importVar('lo_plan', false, 0);
        $lo_history = importVar('lo_history', false, 0);

        if ($lo_plan != 0) {
            $current_tab = 'lo_plan';
        }
        if ($lo_history != 0) {
            $current_tab = 'lo_history';
        }
    }

    require_once Forma::inc(_lms_ . '/lib/lib.certificate.php');
    $cert = new Certificate();

    $released = $cert->certificateReleased(getLogUserId());
    $available_cert = $cert->certificateForCourses(false, false);

    // cahce classroom -----------------------------------------------------------------
    $classroom_man = new ClassroomManager();
    $classrooms = $classroom_man->getClassroomNameList();

    $lang = &DoceboLanguage::createInstance('catalogue');
    $lang_c = &DoceboLanguage::createInstance('course');

    $man_course = new Man_Course();

    $subcourse_list = sql_query(' SELECT u.idCourse, u.edition_id, u.level, u.date_inscr, u.date_first_access, u.date_complete, u.status AS user_status, u.waiting, u.edition_id  FROM %lms_courseuser AS u'
        . " WHERE idUser = '" . getLogUserId() . "'");

    $subscription = [];
    foreach ($subcourse_list as $cinfo) {
        $subscription['course'][$cinfo['idCourse']] = $cinfo;
        if ($cinfo['edition_id'] != 0) {
            $subscription['edition'][$cinfo['idCourse']][$cinfo['edition_id']] = $cinfo;
        }
    }
    // searching courses ---------------------------------------------------------------
    $select_course = ''
    . ' SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.lang_code, c.difficult, '
    . '	c.subscribe_method, c.date_begin, c.date_end, c.max_num_subscribe, '
    . '	c.selling, c.prize, c.create_date, c.status, c.course_edition, '
    . '	c.classrooms, c.img_othermaterial, c.course_demo, c.course_vote, '
    . '	c.can_subscribe, c.sub_start_date, c.sub_end_date, c.valid_time, c.userStatusOp, c.show_result, u.status AS user_status, u.level '

    . ', c.use_logo_in_courselist, c.img_course, c.direct_play ';

    $from_course = ' FROM %lms_course AS c '
    . '	 JOIN %lms_courseuser AS u on c.idCourse = u.idCourse';

    $where_course = ' c.idCourse = u.idCourse '
        . " AND u.idUser = '" . getLogUserId() . "' "
        . " AND ( c.status <> '" . CST_PREPARATION . "' OR u.level > 3 )"
        . " AND c.course_type <> 'assessment' ";

    $selected_year = 0;
    $selected_search = '';

    if (isset($_POST['apply_filter'])) {
        if ($_POST['year']) {
            $where_course .= " AND c.create_date BETWEEN '" . $_POST['year'] . "-01-01 00:00:00' AND '" . $_POST['year'] . "-12-31 23:59:59'";
            $selected_year = $_POST['year'];
        }

        if ($_POST['search'] !== '') {
            $where_course .= " AND c.name LIKE '%" . $_POST['search'] . "%'";
            $selected_search = $_POST['search'];
        }
    }

    $group_by_course = ' GROUP BY c.idCourse ';
    $order_course = ' ORDER BY ';

    $tablist = FormaLms\lib\Get::sett('tablist_mycourses', '');
    if ($tablist != '') {
        $arr_order_course = explode(',', $tablist);
        $arr_temp = [];
        foreach ($arr_order_course as $key => $value) {
            switch ($value) {
                case 'status': $arr_temp[] = ' u.status '; break;
                case 'code': $arr_temp[] = ' c.code '; break;
                case 'name': $arr_temp[] = ' c.name '; break;
            }
        }
        $order_course = $order_course . implode(', ', $arr_temp);
    }
    if ($order_course == ' ORDER BY ') { //default without parameter
        $order_course .= ' u.status, c.name ';
    }
    // apply search filter --------------------------------------------------------------

    $all_lang = Docebo::langManager()->getAllLangCode();

    $query = $select_course
        . $from_course
        . ' WHERE ' . $where_course
        . $group_by_course
        . $order_course;

    $re_course = sql_query($query);

    // retrive editions ----------------------------------------------------------------

    $select_edition = ' SELECT e.* ';
    $from_edition = ' FROM %lms_course_editions AS e '
        . ' JOIN %lms_courseuser AS cu ON e.id_edition = cu.edition_id';
    $where_edition = " WHERE e.status <> '" . CST_PREPARATION . "' AND cu.idUser ='" . getLogUserId() . "'";

    $query = $select_edition
        . $from_edition
        . $where_edition;

    $re_edition = sql_query($query);

    // --------------------------------------------------------------------------------

    $editions = [];
    if ($re_edition) {
        foreach ($re_edition as $edition_elem) {
            $edition_elem['classrooms'] = (isset($classrooms[$edition_elem['classrooms']]) ? $classrooms[$edition_elem['classrooms']] : '');
            $editions[$edition_elem['id_course']][$edition_elem['id_course']] = $edition_elem;
        }
    }

    $man_courseuser = new Man_CourseUser();
    $ax_comm = new AjaxComment('course', 'lms');
    $comment_count = $ax_comm->getResourceCommentCount();
    $user_score = $man_courseuser->getUserCourseScored(getLogUserId());

    // -----------------------------------------------------------------------------

    $needed_info_for = [];
    if (!empty($subscription['course'])) {
        $id_course_list = array_keys($subscription['course']);
        // find last access to the courses ---------------------------------------------------------------------
        require_once _lms_ . '/lib/lib.track_user.php';
        $last_access_courses = TrackUser::getLastAccessToCourse(getLogUserId());

        // retrive unreaded advice -----------------------------------------------------------------------------
        require_once _lms_ . '/lib/lib.advice.php';
        $advices = Man_Advice::getCountUnreaded(getLogUserId(), $id_course_list, $last_access_courses);

        // retrive unreaded forum messages ---------------------------------------------------------------------
        require_once _lms_ . '/lib/lib.forum.php';
        $forums = Man_Forum::getCountUnreaded(getLogUserId(), $id_course_list, $last_access_courses);

        // retrive new lesson ----------------------------------------------------------------------------------
        require_once _lms_ . '/lib/lib.orgchart.php';
        $org_chart = OrganizationManagement::getCountUnreaded(getLogUserId(), $id_course_list, $last_access_courses);

        if (!empty($path_courses['all_items'])) {
            $needed_info_for = array_diff($path_courses['all_items'], $id_course_list);
        }

        $first_is_scorm = OrganizationManagement::objectFilter($id_course_list, 'scormorg');

        $enroll_list = sql_query('SELECT u.idCourse, u.edition_id, COUNT(*) as number '
            . ' FROM %lms_courseuser AS u'
            . ' WHERE u.idCourse IN (' . implode(',', $id_course_list) . ') '
            . " AND u.level = '3'"
            . " AND u.status IN ('" . _CUS_CONFIRMED . "', '" . _CUS_SUBSCRIBED . "', '" . _CUS_BEGIN . "', '" . _CUS_END . "', '" . _CUS_SUSPEND . "', '" . _CUS_WAITING_LIST . "')"
            . " AND u.absent = '0'"
            . " AND u.idUser = '" . getLogUserId() . "'"
            . ' GROUP BY u.idCourse, u.edition_id ');

        $enrolled = [];
        foreach ($enroll_list as $cinfo) {
            $enrolled[$cinfo['idCourse']][$cinfo['edition_id']] = $cinfo['number'];
        }
    }

    // search pre-assessment -----------------------------------------------------------

    $select_assess = ''
    . ' SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.lang_code, c.difficult, '
    . '	c.subscribe_method, c.date_begin, c.date_end, c.max_num_subscribe, '
    . '	c.selling, c.prize, c.create_date, c.status AS course_status, c.course_edition, '
    . '	c.classrooms, c.img_othermaterial, c.course_demo, c.course_vote, '
    . '	c.can_subscribe, c.sub_start_date, c.sub_end_date, c.valid_time, c.userStatusOp, '
    . '	u.level, u.date_inscr, u.date_first_access, u.date_complete, u.status AS user_status, u.waiting, c.advance, u.waiting ';

    $from_assess = ' FROM %lms_course AS c '
    . '	 JOIN %lms_courseuser AS u on c.idCourse = u.idCourse';

    $where_assess = ' c.idCourse = u.idCourse '
        . " AND u.idUser = '" . getLogUserId() . "' "
        . " AND c.course_type = 'assessment' "
        . " AND  ( c.status <> '" . CST_PREPARATION . "' OR u.level > 3 ) "
        . ($filter == 'level' ? " AND level = '" . $filter_level . "'" : '');
    //." AND ( u.status <> '"._CUS_END."' OR u.level > 3 ) ";

    $query = $select_assess
        . $from_assess
        . ' WHERE ' . $where_assess
        . ' ORDER BY c.name ';

    $preass_list = sql_query($query);

    // pre assessment list ---------------------------------------------------------------------------------------
    $i = 0;
    if (sql_num_rows($preass_list) && $current_tab == 'lo_plan') {
        if ($page_add) {
            $GLOBALS['page']->add(
                '<div id="mycourse_asses">'
                . '<h1>' . $lang_c->def('_ASSESSMENT_LIST') . '</h1>', 'content');
        }
        foreach ($preass_list as $cinfo) {
            $cinfo['user_score'] = (isset($user_score[$cinfo['idCourse']]) ? $user_score[$cinfo['idCourse']] : null);

            if (isset($comment_count[$cinfo['idCourse']])) {
                $cinfo['comment_count'] = $comment_count[$cinfo['idCourse']];
            }
            if ($page_add) {
                $GLOBALS['page']->add(dashmyassess($url,
                                                $lang_c,
                                                $cinfo,
                                                $i++), 'content');
            }
        }
        if ($page_add) {
            $GLOBALS['page']->add('</div>', 'content');
        }
    }

    // page intest ------------------------------------------------------------
    require_once _lms_ . '/lib/lib.levels.php';
    $lvl = CourseLevel::getTranslatedLevels();

    $title = $lang->def('_COURSE_LIST');
    switch ($filter) {
        case 'access': 	 $title = $lang->def('_COURSE_YOU_CAN_ACCESS'); break;
        case 'expiring': 	 $title = $lang->def('_COURSE_EXPIRING'); break;
        case 'subscribed':  $title = $lang->def('_COURSE_SUBSCRIBED'); break;
        case 'begin': 		 $title = $lang->def('_COURSE_BEGIN'); break;
        case 'end': 		 $title = $lang->def('_COURSE_END'); break;
        case 'level': 		 $title = str_replace('[level]', $lvl[$_GET['filter_on']], $lang->def('_COURSE_AS')); break;
    }
    if ($page_add) {
        $GLOBALS['page']->add(
            '<div id="mycourse_list">'
            . '<h1>' . $title . '</h1>', 'content');
    }

    $i = 0;
    $direct_play = false;

    foreach ($re_course as $cinfo) {
        $access = Man_Course::canEnterCourse($cinfo);

        if ($cinfo['direct_play'] == 1) {
            $direct_play = true;
        }

        ++$course_stats['total'];
        if ($cinfo['user_status'] == _CUS_WAITING_LIST) {
            ++$course_stats['with_wstatus'][$cinfo['user_status']];
        } elseif ($access['can']) {
            ++$course_stats['u_can_enter'];
        }

        if (isset($course_stats['with_ustatus'][$cinfo['user_status']])) {
            ++$course_stats['with_ustatus'][$cinfo['user_status']];
        } else {
            $course_stats['with_ustatus'][$cinfo['user_status']] = 1;
        }

        if (!isset($course_stats['with_ulevel'][$cinfo['level']])) {
            $course_stats['with_ulevel'][$cinfo['level']] = 1;
        } else {
            ++$course_stats['with_ulevel'][$cinfo['level']];
        }

        if (isset($available_cert[$cinfo['idCourse']])) {
            foreach ($available_cert[$cinfo['idCourse']] as $id_cert => $certificate) {
                if (!isset($released[$id_cert]) && $cert->canRelease($certificate[CERT_AV_STATUS], $cinfo['user_status'])) {
                    if ($cert->certificateAvailableForUser($id_cert, $cinfo['idCourse'], Docebo::user()->getIdst())) {
                        ++$course_stats['cert_relesable'];
                    }
                }
            }
        }
    }

    if (sql_num_rows($re_course)) {
        sql_data_seek($re_course, 0);
    }

    foreach ($re_course as $cinfo) {
        $cinfo['edition_list'] = (isset($editions[$cinfo['idCourse']]) ? $editions[$cinfo['idCourse']] : []);
        $cinfo['user_score'] = (isset($user_score[$cinfo['idCourse']]) ? $user_score[$cinfo['idCourse']] : null);
        $cinfo['enrolled'] = (isset($enrolled[$cinfo['idCourse']]) ? $enrolled[$cinfo['idCourse']] : false);

        if (isset($comment_count[$cinfo['idCourse']])) {
            $cinfo['comment_count'] = $comment_count[$cinfo['idCourse']];
        }

        $cinfo['classrooms'] = (isset($classrooms[$cinfo['classrooms']]) ? $classrooms[$cinfo['classrooms']] : '');

        // advertising --------------------------------------------------------------------------------------------
        $cinfo['to_read']['advice'] = (isset($advices[$cinfo['idCourse']]) ? $advices[$cinfo['idCourse']] : 0);
        $cinfo['to_read']['forum'] = (isset($forums[$cinfo['idCourse']]) ? $forums[$cinfo['idCourse']] : 0);
        $cinfo['to_read']['lobj'] = (isset($org_chart[$cinfo['idCourse']]) ? $org_chart[$cinfo['idCourse']] : 0);

        // 10 days in the future
        $range = time() + (10 * 24 * 60 * 60);
        $expiring = false;
        if ($cinfo['date_end'] != '0000-00-00') {
            $time_end = fromDatetimeToTimestamp($cinfo['date_end']);
            if ($range > $time_end) {
                $expiring = true;
                ++$course_stats['expiring'];
            }
        }
        if ($cinfo['valid_time'] != '0' && $cinfo['valid_time'] != '' && $cinfo['date_first_access'] != '') {
            $time_first_access = fromDatetimeToTimestamp($cinfo['date_first_access']);
            if ($range > ($time_first_access + ($cinfo['valid_time'] * 24 * 3600))) {
                $expiring = true;
                ++$course_stats['expiring'];
            }
        }
        if (isset($first_is_scorm[$cinfo['idCourse']])) {
            $cinfo['first_is_scorm'] = $first_is_scorm[$cinfo['idCourse']];
        } else {
            $cinfo['first_is_scorm'] = false;
        }

        $access = Man_Course::canEnterCourse($cinfo);

        // --------------------------------------------------------------------

        if (!isset($path_courses['all_items'][$cinfo['idCourse']])) {
            // the course is not related with a coursepath, so i can print it --------------
            if ($filter == 'level') {
                if ($subscription['course'][$cinfo['idCourse']]['level'] == $filter_level) {
                    $dash = dashmycourse($url,
                                        $lang_c,
                                        $subscription,
                                        $cinfo,
                                        $i++);
                } else {
                    $dash = '';
                }
            } else {
                $dash = dashmycourse($url,
                                        $lang_c,
                                        $subscription,
                                        $cinfo,
                                        $i++);
            }

            if ($use_tab == true) {
                if ($course_stats['with_ustatus'][_CUS_END] == $course_stats['total']) {
                    $current_tab = 'lo_history';
                }

                if ($current_tab == 'lo_history') {
                    if ($cinfo['user_status'] == _CUS_END && $page_add) {
                        $GLOBALS['page']->add($dash, 'content');
                    }
                } else {
                    if (($cinfo['user_status'] != _CUS_END || $cinfo['level'] >= 4) && $page_add) {
                        $GLOBALS['page']->add($dash, 'content');
                    }
                }
            } else {
                switch ($filter) {
                    case 'access' :
                        if ($access['can'] && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    case 'expiring' :
                        if ($expiring && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    case 'subscribed' :
                        if ($cinfo['user_status'] == _CUS_SUBSCRIBED && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    case 'begin' :
                        if ($cinfo['user_status'] == _CUS_BEGIN && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    case 'end' :
                        if ($cinfo['user_status'] == _CUS_END && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    case 'level' :
                        if ($_GET['filter_on'] == $cinfo['level'] && $page_add) {
                            $GLOBALS['page']->add($dash, 'content');
                        }
                     break;
                    default: if ($page_add) {
                        $GLOBALS['page']->add($dash, 'content');
                    }
                }
            }
        } else {
            // the course is related with a coursepath, so i must wait to print it ----------
            //$cinfo['prerequisites'] = $path_courses['all_items'][$cinfo['idCourse']]; // <- useless?

            //$courses =array($cinfo['idCourse']=>$cinfo);
            $prere = $cp_info[$cinfo['idCourse']]['prerequisites'];
            if ($path_man->checkPrerequisites($prere, $subscription)) {
                $cinfo['prerequisites_satisfied'] = true;
            } else {
                $cinfo['prerequisites_satisfied'] = false;
            }

            if ($filter == 'level') {
                if ($subscription['course'][$cinfo['idCourse']]['level'] == $filter_level) {
                    $course_cache[$cinfo['idCourse']] = dashmycourse($url,
                                                                        $lang_c,
                                                                        $subscription,
                                                                        $cinfo,
                                                                        1,
                                                                        true,
                                                                        4);
                } else {
                    $course_cache[$cinfo['idCourse']] = '';
                }
            } else {
                $course_cache[$cinfo['idCourse']] = dashmycourse($url,
                                                                    $lang_c,
                                                                    $subscription,
                                                                    $cinfo,
                                                                    1,
                                                                    true,
                                                                    4);
            }
        } // end else-if -------------------------------------------------------
    } //  end while ------------------------------------------------------------

    if ($direct_play && $page_add) {
        $GLOBALS['page']->add(''
        . '	<link href="' . getPathTemplate() . '/style/shadowbox.css" rel="stylesheet" type="text/css" />'

        . '<script type="text/javascript" src="' . $GLOBALS['where_framework_relative'] . '/addons/shadowbox/shadowbox-yui.js"></script>' . "\n"
        . '<script type="text/javascript" src="' . $GLOBALS['where_framework_relative'] . '/addons/shadowbox/shadowbox.js"></script>' . "\n", 'page_head');

        $GLOBALS['page']->add('<script type="text/javascript">
	
		YAHOO.util.Event.onDOMReady(function() {
			var options = { listenOverlay:false, overlayOpacity:"0.8", 
				loadingImage:"' . getPathImage('lms') . 'standard/loading.gif", overlayBgImage:"' . getPathImage('lms') . 'standard/overlay-85.png", 
				text: {close: "' . Lang::t('_CLOSE') . '", cancel: "' . Lang::t('_UNDO') . '", loading:"' . Lang::t('_LOADING') . '" },
				onOpen: function (gallery) { window.onbeforeunload = function() { return "' . Lang::t('_CONFIRM_EXIT', 'organization', 'lms') . '"; } }
		    }; 
			Shadowbox.init(options); 
			Shadowbox.close = function() { 
				window.frames[\'shadowbox_content\'].uiPlayer.closePlayer(true, window);
			}
		});
		</script>');
    }

    if ($course_stats['total'] == 0 && $page_add) {
        $GLOBALS['page']->add(''
            . '<b>' . $lang->def('_NO_COURSE') . '</b> ' . '<br />', 'content');
    }
    if ($page_add) {
        $GLOBALS['page']->add('</div>', 'content');
    }

    // Coursepath --------------------------------------------------------------

    if (!empty($coursepath)) {
        // find structures of the course path ----------------------------------
        $path_slot = $path_man->getPathSlot(array_keys($coursepath));

        // coursepath list -----------------------------------------------------

        if ($page_add) {
            $GLOBALS['page']->add(
                '<div id="mycoursepath_list">'
                . '<h1>' . $lang->def('_COURSEPATH_LIST') . '</h1>', 'content');
        }

        $i = 0;

        // find course basilar information -------------------------------------
        if (!empty($needed_info_for)) {
            $course_info = $man_course->getAllCourses(false, false, $needed_info_for);
        } else {
            $course_info = [];
        }

        foreach ($coursepath as $id_path => $path) {
            $html = '<div class="coursepath_container coursepath_container_simple">';

            $html .= '<h2>'

                . ($path[COURSEPATH_CODE] != '' ? '[' . $path[COURSEPATH_CODE] . '] ' : '')
                . $path[COURSEPATH_NAME]

                . '</h2>';
            if (!isset($path_courses[$id_path]) || empty($path_courses[$id_path])) {
                $html .= $lang->def('_NO_COURSE_ASSIGNED_TO_COURSEPATH') . '<br />';
            } else {
                // display the slots
                foreach ($path_slot[$id_path]  as $id_slot => $slot_info) {
                    if ($id_slot == 0) {
                        $html .= '<h3>' . $lang->def('_MANDATORY') . '</h3>';
                        if (!empty($path_courses[$id_path][$id_slot])) {
                            $html .= '<ul class="coursepath_mainslot">';
                        }
                    } else {
                        if ($slot_info['min_selection'] > 0 && $slot_info['max_selection'] > 0) {
                            $title = str_replace(['[min_selection]', '[max_selection]'],
                                                    [$slot_info['min_selection'], $slot_info['max_selection']],
                                                    $lang->def('_COURSE_PATH_SLOT_MIN_MAX'));
                        } elseif ($slot_info['max_selection'] > 0) {
                            $title = str_replace('[max_selection]',
                                                    $slot_info['max_selection'],
                                                    $lang->def('_COURSE_PATH_SLOT_MAX'));
                        } else {
                            $title = $lang->def('_COURSE_PATH_SLOT');
                        }
                        $html .= '<h3>' . $title . '</h3>';
                        if (!empty($path_courses[$id_path][$id_slot])) {
                            $html .= '<ul class="coursepath_otherslot">';
                        }
                    }

                    foreach ($path_courses[$id_path][$id_slot] as $id => $v) {
                        if (isset($course_cache[$id])) {
                            $html .= '<li>' . $course_cache[$id] . '</li>';
                        } else {
                            $html .= '<li>' . dashAcourse($id, 4) . '</li>';
                        }
                    }

                    if (!empty($path_courses[$id_path][$id_slot])) {
                        $html .= '</ul>';
                    }
                }
                $html .= '</div>';
                if ($page_add) {
                    $GLOBALS['page']->add($html, 'content');
                }
            }
            if ($page_add) {
                $GLOBALS['page']->add('</div>', 'content');
            }
        }
        if ($course_stats['cert_relesable'] < 0) {
            $course_stats['cert_relesable'] = 0;
        }
    }

    return $course_stats;
}

function dashmyassess(&$url, $lang, $cinfo, $index)
{
    $html = '<div class="course_container' . ($index == 0 ? ' course_container_first' : '') . '">'
        . '<h2>';

    $access = Man_Course::canEnterCourse($cinfo);
    if ($access['can']) {
        $html .= ''
                //.( $cinfo['lang_code'] ? '<img src="'.getPathImage('cms').'language/'.$cinfo['lang_code'].'.png" alt="'.$cinfo['lang_code'].'" /> ' : '' )
                . '<a class="course_enter" href="index.php?modname=course&amp;op=aula&amp;idCourse=' . $cinfo['idCourse'] . '" '
                . 'title="' . $lang->def('_ENTER') . '">'
                . (trim($cinfo['code']) != '' ? '[' . $cinfo['code'] . '] ' : '') . $cinfo['name']
                . '</a>';
    } else {
        $html .= ''
            . '<img id="locked_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'course/lock.gif" alt="' . $lang->def('_NOENTER') . '" />'
            //.( $cinfo['lang_code'] ? '<img src="'.getPathImage('cms').'language/'.$cinfo['lang_code'].'.png" alt="'.$cinfo['lang_code'].'" /> ' : '' )
            . (trim($cinfo['code']) != '' ? '[' . $cinfo['code'] . '] ' : '') . $cinfo['name'];
    }
    $html .= '</h2>';

    if (trim($cinfo['description']) == '') {
        $html .= '';
    } elseif (strpos($cinfo['description'], '<p') === false) {
        $html .= '<p class="course_description">' . $cinfo['description'] . '</p>';
    } else {
        $html .= '<div class="course_description">' . $cinfo['description'] . '</div>';
    }

    $html .= '</div>';

    return $html;
}

function dashmycourse(&$url, $lang, &$subscription, $cinfo, $index)
{
    require_once _lms_ . '/lib/lib.levels.php';
    $lvl = CourseLevel::getTranslatedLevels();

    $arr_status = [     _CUS_WAITING_LIST => $lang->def('_WAITING_USERS'),
                        _CUS_CONFIRMED => $lang->def('_T_USER_STATUS_CONFIRMED'),

                        _CUS_SUBSCRIBED => $lang->def('_T_USER_STATUS_SUBS'),
                        _CUS_BEGIN => $lang->def('_T_USER_STATUS_BEGIN'),
                        _CUS_END => $lang->def('_T_USER_STATUS_END'), ];

    DoceboLanguage::createInstance('course', 'lms');

    $arr_coursestatus = [
        CST_PREPARATION => $lang->def('_CST_PREPARATION', 'course', 'lms'),
        CST_AVAILABLE => $lang->def('_CST_AVAILABLE', 'course', 'lms'),
        CST_EFFECTIVE => $lang->def('_CST_CONFIRMED', 'course', 'lms'),
        CST_CONCLUDED => $lang->def('_CST_CONCLUDED', 'course', 'lms'),
        CST_CANCELLED => $lang->def('_CST_CANCELLED', 'course', 'lms'), ];

    $course_type = $cinfo['course_type'];
    $there_material = [];

    if ($cinfo['img_othermaterial'] != '') {
        $there_material[] = '&id_course=' . $cinfo['idCourse'];
    }

    // course title -----------------------------------------------------
    $cinfo = array_merge($cinfo, $subscription['course'][$cinfo['idCourse']]);

    // -----------------------------------------------------------------
    $courseuser_st = -10;
    $html = '';
    $intest = '';
    if (!isset($subscription['edition'][$cinfo['idCourse']])) {
        $access = Man_Course::canEnterCourse($cinfo);

        $lb_param = '';
        if ($cinfo['first_is_scorm'][0] != '' && $cinfo['first_is_scorm'][0] != '0') {
            $lb_param .= ';width=' . $cinfo['first_is_scorm'][0] . '';
        }

        if ($cinfo['first_is_scorm'][1] != '' && $cinfo['first_is_scorm'][1] != '0') {
            $lb_param .= ';height=' . $cinfo['first_is_scorm'][1] . '';
        }

        $intest .= '<h2>';
        if ($access['can']) {
            $intest .= ''
                    . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                    . '<a class="course_enter" href="index.php?modname=course&amp;op=aula&amp;idCourse=' . $cinfo['idCourse'] . '" '
                    . ($cinfo['direct_play'] == 1 && $cinfo['level'] <= 3 && $cinfo['first_is_scorm']
                        ? ' rel="shadowbox' . $lb_param . '" title="' . $cinfo['name'] . '"'
                        : ' title="' . $lang->def('_ENTER') . '"')
                    . '>'

                    //.( trim($cinfo['code']) != '' ? '['.$cinfo['code'].'] ' : '' )
                    . $cinfo['name']

                    . '</a>';
        } else {
            $intest .= '<img class="image_lock" id="locked_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'course/lock.gif" alt="' . $lang->def('_NOENTER') . '" />'
                . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                //.( trim($cinfo['code']) != '' ? '['.$cinfo['code'].'] ' : '' )
                . $cinfo['name']

                . '';
        }
        $intest .= '</h2>';
        // not editon
        //if($cinfo['level'] >= 4) {

        $intest .= '<p class="course_support_info">' .
                str_replace(['[course_type]', '[create_date]', '[enrolled]', '[course_status]'],
                                [$course_type,
                                        createDateDistance($cinfo['create_date'], 'catalogue', true),
                                        (isset($cinfo['enrolled'][0]) ? $cinfo['enrolled'][0] : 0),
                                        $arr_coursestatus[$cinfo['course_status']], ],
                                $lang->def('_COURSE_INTRO')) .

                ($cinfo['date_begin'] != '0000-00-00' || $cinfo['date_end'] != '0000-00-00'
                    ?
                        str_replace(['[date_begin]', '[date_end]'],
                                    [Format::date($cinfo['date_begin'], 'date'),
                                            Format::date($cinfo['date_end'], 'date'), ],
                                    $lang->def('_COURSE_DATE'))
                    : '') .
                '</p>';
        //}

        if ($cinfo['classrooms'] != '') {
            $intest .= '<p class="course_support_info">' .
                    str_replace(['[classrooms_name]', '[classrooms_location]'],
                                    [$cinfo['classrooms']['classroom'], $cinfo['classrooms']['location']],
                                    $lang->def('_IN_THE_CLASSROOM'))
                    . '</p>';
        }

        $intest .= '<p class="course_support_info">'
            . $arr_status[$cinfo['user_status']]
            . ($cinfo['level'] >= 4
                ? str_replace('[level]', $lvl[$cinfo['level']], $lang->def('_USER_LVL'))
            . ' ' . (trim($cinfo['code']) != '' ? '<br />[' . $cinfo['code'] . '] ' : '')
                : ''
            )
            . '</p>';
        if (isset($access['expiring_in']) && $access['expiring_in'] != false && $access['expiring_in'] < 30) {
            $intest .= '<p class="course_support_info">' .
                    str_replace('[expiring_in]', $access['expiring_in'], $lang->def('_EXPIRING_IN'))
                    . '</p>';
        }

        if ($cinfo['show_result'] && $cinfo['user_status'] > _CUS_SUBSCRIBED && $cinfo['user_status'] < _CUS_SUSPEND) {
            $intest .= '<p class="course_support_info show_results">'
                        . '<a href="index.php?modname=course&amp;op=aula&amp;idCourse=' . $cinfo['idCourse'] . '&amp;showresult=1">' . $lang->def('_SHOW_RESULTS') . '</a>'
                        . '</p>';
        }

        $courseuser_st = $cinfo['user_status'];
    } elseif (count($subscription['edition'][$cinfo['idCourse']]) == 1) {
        // edition unique--------------------------------------------------------------------------------

        $ed_id = key($subscription['edition'][$cinfo['idCourse']]);
        $sub_info = current($subscription['edition'][$cinfo['idCourse']]);
        $ed_info = &$cinfo['edition_list'][$ed_id];

        $cinfo['date_begin'] = $ed_info['date_begin'];
        $cinfo['date_end'] = $ed_info['date_end'];
        $access = Man_Course::canEnterCourse($cinfo);

        if ($ed_info['date_begin'] == '0000-00-00') {
            $ed_info['date_begin'] = '';
        }
        if ($ed_info['date_end'] == '0000-00-00') {
            $ed_info['date_end'] = '';
        }

        $intest = '<h2>';
        if ($access['can']) {
            $intest .= '<a class="course_enter" href="index.php?modname=course&amp;op=aula&amp;idCourse=' . $cinfo['idCourse'] . '&amp;id_e=' . $ed_id . '" '
                    . 'title="' . $lang->def('_ENTER') . '">'
                    . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                    //.( trim($ed_info['code']) != '' ? '['.$ed_info['code'].'] ' : '' )
                    . $ed_info['name']

                    . ($ed_info['date_begin'] != '' || $ed_info['date_end'] != ''
                        ? ' <span>( '
                            . Format::date($ed_info['date_begin'], 'date')
                            . ' ' . Format::date($ed_info['date_end'], 'date')
                            . ' )</span> '
                        : '')

                    . '</a>';
        } else {
            $intest .= '<img id="locked_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'course/lock.gif" alt="' . $lang->def('_NOENTER') . '" />'
                . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                . (trim($ed_info['code']) != '' ? '[' . $ed_info['code'] . '] ' : '') . $ed_info['name']

                    . ($ed_info['date_begin'] != '' || $ed_info['date_end'] != ''
                        ? ' <span>( '
                            . Format::date($ed_info['date_begin'], 'date')
                            . ' ' . Format::date($ed_info['date_end'], 'date')
                            . ' )</span> '
                        : '');
        }
        $intest .= '</h2>';
        //if($sub_info['level'] >= 4) {

        $intest .= '<p class="course_support_info">'
                . str_replace(['[course_type]', '[create_date]', '[enrolled]', '[course_status]'],
                                [$ed_info['edition_type'],
                                        createDateDistance($cinfo['create_date'], 'catalogue', true),
                                        $cinfo['enrolled'][$ed_id],
                                        $arr_coursestatus[$ed_info['status']], ],
                                $lang->def('_COURSE_INTRO'))
                . '</p>';
        //}
        if ($ed_info['classrooms'] != '') {
            $intest .= '<p class="course_support_info">' .
                    str_replace(['[classrooms_name]', '[classrooms_location]'],
                                    [$ed_info['classrooms']['classroom'], $ed_info['classrooms']['location']],
                                    $lang->def('_IN_THE_CLASSROOM'))
                    . '</p>';
        }
        $intest .= '<p class="course_support_info">'
            . $arr_status[$sub_info['user_status']]
            . str_replace('[level]', $lvl[$sub_info['level']], $lang->def('_USER_LVL'))
            . ' ' . (trim($cinfo['code']) != '' && $sub_info['level'] >= 4 ? '<br />[' . $cinfo['code'] . '] ' : '')
            . '</p>';
        if ($access['expiring_in'] != false && $access['expiring_in'] < 30) {
            $intest .= '<p class="course_support_info">' .
                    str_replace('[expiring_in]', $access['expiring_in'], $lang->def('_EXPIRING_IN'))
                    . '</p>';
        }

        $courseuser_st = $sub_info['user_status'];
    } else {
        // more than one edition ------------------------------------------------------------------------

        foreach ($subscription['edition'][$cinfo['idCourse']] as $ed_id => $sub_info) {
            $ed_info = &$cinfo['edition_list'][$ed_id];

            $cinfo['date_begin'] = $ed_info['date_begin'];
            $cinfo['date_end'] = $ed_info['date_end'];
            $access = Man_Course::canEnterCourse($cinfo);

            if ($ed_info['date_begin'] == '0000-00-00') {
                $ed_info['date_begin'] = '';
            }
            if ($ed_info['date_end'] == '0000-00-00') {
                $ed_info['date_end'] = '';
            }

            $intest .= '<h2>';
            if ($access['can']) {
                $intest .= '<a class="course_enter" href="index.php?modname=course&amp;op=aula&amp;idCourse=' . $cinfo['idCourse'] . '&amp;id_e=' . $ed_id . '" '
                        . 'title="' . $lang->def('_ENTER') . '">'
                        . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                        //.( trim($ed_info['code']) != '' ? '['.$ed_info['code'].'] ' : '' )
                        . $ed_info['name']

                        . ($ed_info['date_begin'] != '' || $ed_info['date_end'] != ''
                            ? ' <span>( '
                                . Format::date($ed_info['date_begin'], 'date')
                                . ' ' . Format::date($ed_info['date_end'], 'date')
                                . ' )</span> '
                            : '')

                        . '</a>';
            } else {
                $intest .= '<img id="locked_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'course/lock.gif" alt="' . $lang->def('_NOENTER') . '" />'
                    . ($cinfo['lang_code'] && $cinfo['lang_code'] != 'none' ? '<img src="' . getPathImage('cms') . 'language/' . $cinfo['lang_code'] . '.png" alt="' . $cinfo['lang_code'] . '" /> ' : '')
                    . (trim($ed_info['code']) != '' ? '[' . $ed_info['code'] . '] ' : '') . $ed_info['name']

                        . ($ed_info['date_begin'] != '' || $ed_info['date_end'] != ''
                            ? ' <span>( '
                                . Format::date($ed_info['date_begin'], 'date')
                                . ' ' . Format::date($ed_info['date_end'], 'date')
                                . ' )</span> '
                            : '');
            }
            $intest .= '</h2>';

            //if($sub_info['level'] >= 4) {

            $intest .= '<p class="course_support_info">'
                    . str_replace(['[course_type]', '[create_date]', '[enrolled]', '[course_status]'],
                                    [$ed_info['edition_type'],
                                            createDateDistance($cinfo['create_date'], 'catalogue', true),
                                            $cinfo['enrolled'][$ed_id],
                                            $arr_coursestatus[$ed_info['status']], ],
                                    $lang->def('_COURSE_INTRO'))
                    . '</p>';
            //}

            if ($ed_info['classrooms'] != '') {
                $intest .= '<p class="course_support_info">' .
                        str_replace(['[classrooms_name]', '[classrooms_location]'],
                                        [$ed_info['classrooms']['classroom'], $ed_info['classrooms']['location']],
                                        $lang->def('_IN_THE_CLASSROOM'))
                        . '</p>';
            }
            $intest .= '<p class="course_support_info">'
                . $arr_status[$sub_info['user_status']]
                . str_replace('[level]', $lvl[$sub_info['level']], $lang->def('_USER_LVL'))

                . (trim($cinfo['code']) != '' && $sub_info['level'] >= 4 ? '<br />[' . $cinfo['code'] . '] ' : '')
                . '</p>';
        }

        if ($access['expiring_in'] != false && $access['expiring_in'] < 30) {
            $intest .= '<p class="course_support_info">' .
                    str_replace('[expiring_in]', $access['expiring_in'], $lang->def('_EXPIRING_IN'))
                    . '</p>';
        }
        $courseuser_st = ($courseuser_st < $sub_info['user_status'] ? $sub_info['user_status'] : $courseuser_st);
    }
    // -----------------------------------------------------------------------------------------

    $html = '<div class="course_container'
        . (FormaLms\lib\Get::sett('use_social_courselist') == 'on' ? ' double_height' : '')
        . ($index == 0 ? ' course_container_first' : '')

        . ($courseuser_st == _CUS_SUBSCRIBED ? ' cc_inprogress' : '')
        . ($courseuser_st == _CUS_BEGIN ? ' cc_begin' : '')

        . '">';

    if (($cinfo['use_logo_in_courselist'] == '1' && $cinfo['img_course'] != '')
        || FormaLms\lib\Get::sett('use_social_courselist') == 'on') {
        $html .= '<div class="course_info_container">';
    }

    if ($cinfo['use_logo_in_courselist'] == '1' && $cinfo['img_course'] != '') {
        $html .= '<ul class="course_score"><li>';
        $html .= '<img height="70" src="' . $GLOBALS['where_files_relative'] . '/doceboLms/' . FormaLms\lib\Get::sett('pathcourse')
                . $cinfo['img_course'] . '" alt="' . $lang->def('_COURSE_LOGO') . '" />';
        $html .= '</li></ul>';
    }

    $html .= $intest;

    $li = '';
    $li .= '<li class="advice_to_read">'
        . '<img src="' . getPathImage('lms') . 'coursecatalogue/' . ($cinfo['to_read']['advice'] != 0 ? 'adv_advice' : 'no_advice') . '.jpg" '
        . ' title="' . str_replace('[advice]', $cinfo['to_read']['advice'], $lang->def('_ADVERT_ADVICE')) . '" '
        . ' alt="' . str_replace('[advice]', $cinfo['to_read']['advice'], $lang->def('_ADVERT_ADVICE')) . '" />'
        . '</li>';

    $li .= '<li class="forum_to_read">'
        . '<img src="' . getPathImage('lms') . 'coursecatalogue/' . ($cinfo['to_read']['forum'] != 0 ? 'adv_forum' : 'no_forum') . '.jpg" '
        . ' title="' . str_replace('[forum]', $cinfo['to_read']['forum'], $lang->def('_ADVERT_FORUM')) . '" '
        . ' alt="' . str_replace('[forum]', $cinfo['to_read']['forum'], $lang->def('_ADVERT_FORUM')) . '" />'
        . '</li>';

    $li .= '<li class="lobj_to_read">'
        . '<img src="' . getPathImage('lms') . 'coursecatalogue/' . ($cinfo['to_read']['lobj'] != 0 ? 'adv_lobj' : 'no_lobj') . '.jpg" '
        . ' title="' . str_replace('[lobj]', $cinfo['to_read']['lobj'], $lang->def('_ADVERT_LOBJ')) . '" '
        . ' alt="' . str_replace('[lobj]', $cinfo['to_read']['lobj'], $lang->def('_ADVERT_LOBJ')) . '" />'
        . '</li>';

    if ($li != '') {
        $html .= '<ul class="course_advertising">' . $li . '</ul>';
    }

    // course related extra option -------------------------------------------------------------
    if (FormaLms\lib\Get::sett('use_social_courselist') == 'on' || !empty($there_material)) {
        $li = '';
        if (FormaLms\lib\Get::sett('use_social_courselist') == 'on') {
            $li .= '<li class="course_comment">'
                    . '<a href="javascript:;" onclick="openComment(\'' . $cinfo['idCourse'] . '\'); return false;">'
                    . '<span>' . $lang->def('_COMMENTS') . ' ('
                    . (isset($cinfo['comment_count']) ? $cinfo['comment_count'] : '0') . ')</span></a>'
                    . '</li>';
        }
        // the course material -----------------------------------------------------------------
        if (!empty($there_material)) {
            if (count($there_material) == 1) {
                // direct download of material -------------------------------------------------
                $li .= '<li class="course_materials">'
                    . '<a href="' . $url->getUrl('op=donwloadmaterials' . array_pop($there_material)) . '">'
                    . '<span>' . $lang->def('_MATERIALS') . '</span></a>'
                    . '</li>';
            } else {
                // popup download of material --------------------------------------------------
                $li .= '<li class="course_materials">'
                    . '<a href="javascript:;" onclick="openWindowWithAction(\'' . $cinfo['idCourse'] . '\', \'course_materials\'); return false;">'
                    . '<span>' . $lang->def('_MATERIALS') . '</span></a>'
                    . '</li>';
            }
        }

        if ($li != '') {
            $html .= '<ul class="course_related_actions">' . $li . '</ul>';
        }
    }
    /*if ($cinfo['direct_play'] == 1) {
        $html .= '<p class="showresults">'
            .'<a href="index.php?modname=course&amp;op=showresults&amp;id_course='.$cinfo['idCourse'].'">'
            .'<span>'.$lang->def('_SHOW_RESULTS').'</span></a>'
            .'</p>';
    }*/

    // score and subscribe action ------------------------------------------------------------

    if (FormaLms\lib\Get::sett('use_social_courselist') == 'on') {
        $html .= '<ul class="course_score">';

        $html .= '<li class="current_score"><span>' . $lang->def('_SCORE') . '</span><br />'
            . '<strong id="course_score_' . $cinfo['idCourse'] . '">' . $cinfo['course_vote'] . '</strong></li>';
        if ($cinfo['waiting'] == 0) {
            $html .= '<li class="score_it">'
                    . '<a class="good" href="javascript:;" '
                        . 'onclick="course_vote(\'' . $cinfo['idCourse'] . '\', \'good\'); return false;" '
                        . 'title="' . $lang->def('_VOTE_GOOD_TITLE') . '">'

                        . '<img id="score_image_good_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'coursecatalogue/good'
                            . ($cinfo['user_score'] == '1' ? '_grey' : '')
                            . '.png" alt="' . $lang->def('_VOTE_GOOD_ALT') . ' : ' . strip_tags($cinfo['name']) . '" />'
                    . '</a> '
                    . '<a class="bad" href="javascript:;" '
                        . 'onclick="course_vote(\'' . $cinfo['idCourse'] . '\', \'bad\'); return false;" '
                        . 'title="' . $lang->def('_VOTE_BAD_TITLE') . '">'

                        . '<img id="score_image_bad_' . $cinfo['idCourse'] . '" src="' . getPathImage() . 'coursecatalogue/bad'
                            . ($cinfo['user_score'] == '-1' ? '_grey' : '')
                            . '.png" alt="' . $lang->def('_VOTE_BAD_ALT') . ' : ' . strip_tags($cinfo['name']) . '" />'
                    . '</a>'
                . '</li>';
        } else {
            $li .= '<li class="score_it" id="score_action_' . $cinfo['idCourse'] . '">'
                    . '<img src="' . getPathImage() . 'coursecatalogue/good_grey.png" alt="' . $lang->def('_VOTE_GOOD_ALT') . ' : ' . strip_tags($cinfo['name']) . '" /> '
                    . '<img src="' . getPathImage() . 'coursecatalogue/bad_grey.png" alt="' . $lang->def('_VOTE_BAD_ALT') . ' : ' . strip_tags($cinfo['name']) . '" />'
                . '</li>';
        }
        $html .= '</ul>';
    }

    if (($cinfo['use_logo_in_courselist'] == '1' && $cinfo['img_course'] != '')
        || FormaLms\lib\Get::sett('use_social_courselist') == 'on') {
        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

function dashAcourse($id_course, $h_number)
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _base_ . '/lib/lib.user_profile.php';
    require_once _base_ . '/lib/lib.navbar.php';
    require_once _lms_ . '/lib/lib.preassessment.php';
    require_once _lms_ . '/lib/lib.catalogue.php';
    require_once _lms_ . '/lib/lib.coursepath.php';
    require_once _lms_ . '/lib/lib.course.php';
    require_once _lms_ . '/modules/coursecatalogue/lib.coursecatalogue.php';
    $lang = &DoceboLanguage::createInstance('standard', 'framework');
    $lang->setGlobal();
    $lang = &DoceboLanguage::createInstance('course', 'lms');

    $normal_subs = 1;

    $man_course = new DoceboCourse($id_course);
    $cinfo = $man_course->getAllInfo();

    $man_courseuser = new Man_CourseUser();
    $usercourses = &$man_courseuser->getUserSubscriptionsInfo(getLogUserId(), false);

    $select_edition = ' SELECT * ';
    $from_edition = ' FROM %lms_course_edition';
    $where_edition = " WHERE idCourse = '" . $id_course . "' ";
    $order_edition = ' ORDER BY date_begin ';
    $re_edition = sql_query($select_edition . $from_edition . $where_edition . $order_edition);
    $editions = [];

    if ($re_edition) {
        while ($edition_elem = sql_fetch_assoc($re_edition)) {
            $edition_elem['waiting'] = 0;
            $edition_elem['user_count'] = 0;
            $edition_elem['theacher_list'] = getSubscribed($edition_elem['idCourse'], false, 6, true, $edition_elem['idCourseEdition']);
            $editions[$edition_elem['idCourse']][$edition_elem['idCourseEdition']] = $edition_elem;
        }
    }

    $select_ed_count = 'SELECT idCourse, edition_id, sum(waiting) as waiting, COUNT(*) as user_count ';
    $from_ed_count = 'FROM %lms_courseuser ';
    $where_ed_count = "WHERE edition_id <> 0 AND idCourse = '" . $id_course . "'";
    $group_ed_count = 'GROUP BY edition_id ';
    $re_ed_count = sql_query($select_ed_count . $from_ed_count . $where_ed_count . $group_ed_count);
    if ($re_ed_count) {
        while ($ed_count_elem = sql_fetch_assoc($re_ed_count)) {
            $editions[$ed_count_elem['idCourse']][$ed_count_elem['edition_id']]['waiting'] = $ed_count_elem['waiting'];
            $editions[$ed_count_elem['idCourse']][$ed_count_elem['edition_id']]['user_count'] = $ed_count_elem['user_count'];
        }
    }

    $cinfo['theacher_list'] = getSubscribed($cinfo['idCourse'], false, 6, true);
    $cinfo['edition_list'] = (isset($editions[$cinfo['idCourse']]) ? $editions[$cinfo['idCourse']] : []);
    $cinfo['edition_available'] = count($cinfo['edition_list']);
    $cinfo['user_score'] = (isset($user_score[$cinfo['idCourse']]) ? $user_score[$cinfo['idCourse']] : null);

    require_once _base_ . '/lib/lib.urlmanager.php';
    $url = &UrlManager::getInstance('catalogue');
    $url->setStdQuery(FormaLms\lib\Get::home_page_query());
    if ($normal_subs == 0) {
        $cinfo['can_subscribe'] = 0;
    }
    $html = dashcourse($url, $lang, $cinfo, (isset($usercourses[$cinfo['idCourse']]) ? $usercourses[$cinfo['idCourse']] : false), 0, $h_number);

    return $html;
}

function downloadMaterials()
{
    require_once _lms_ . '/lib/lib.course.php';
    require_once _base_ . '/lib/lib.multimedia.php';
    $lang = DoceboLanguage::createInstance('course', 'lms');

    $id_course = importVar('id_course', true, 0);
    $edition_id = importVar('edition_id', true, 0);

    if ($id_course != 0) {
        $man_course = new DoceboCourse($id_course);
        $file = $man_course->getValue('img_othermaterial');
    }
    if ($edition_id != 0) {
        $select_edition = ' SELECT img_othermaterial ';
        $from_edition = ' FROM %lms_course_edition';
        $where_edition = " WHERE idCourseEdition = '" . $edition_id . "' ";

        list($file) = sql_fetch_row(sql_query($select_edition . $from_edition . $where_edition));
    }
    require_once _base_ . '/lib/lib.download.php';
    $ext = end(explode('.', $file));
    sendFile('/doceboLms/' . FormaLms\lib\Get::sett('pathcourse'), $file, $ext);
}
