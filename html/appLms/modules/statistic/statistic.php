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

require_once Forma::inc(_lib_ . '/formatable/include.php');

function outPageView($link)
{
    $lang = &DoceboLanguage::createInstance('statistic', 'lms');
    $for = importVar('for', false, 'week');
    $times = ['day', 'week', 'month', 'year'];
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');
    $labels = [];
    $series = [];

    $dateend = date('Y-m-d H:i:s');
    $walk = [];
    $chartData = [];
    switch ($for) {
        case 'day':
            $dateinit = date('Y-m-d H:i:s', time() - 24 * 3600);
            $start_num = substr($dateinit, 11, 2);
            $colums = 24;
            $select = ' HOUR(timeof) AS from_time ';
            $group_by = ' HOUR(timeof) ';

            for ($i = 1; $i <= $colums; ++$i) {
                $c = (($i + $start_num) % $colums);
                $chartData[$c] = [
                    'x_axis' => $c,
                    'y_axis' => 0,
                ];
            }
            break;
        case 'week':
            $dateinit = date('Y-m-d H:i:s', time() - 7 * 24 * 3600);
            $start_num = date('w', time() - 7 * 24 * 3600);
            $colums = 7;
            $select = ' DAYOFWEEK(timeof) AS from_time ';
            $group_by = ' DAYOFMONTH(timeof) ';
            for ($i = 1; $i <= $colums; ++$i) {
                $c = (($start_num + $i) % $colums) + 1;
                $walk_name[] = $lang->def('_WEEK_DAY_' . $c . '_SHORT');
                $chartData[$c] = [
                    'x_axis' => $lang->def('_WEEK_DAY_' . ($c - 1) . '_SHORT'),
                    'y_axis' => 0,
                ];
            }
            break;
        case 'month':
            echo $dateinit = date('Y-m-d H:i:s', time() - 30 * 24 * 3600);
            $y = date('Y', time() - 30 * 24 * 3600);
            $m = date('m', time() - 30 * 24 * 3600);
            $start_num = substr($dateinit, 8, 2) - 1;
            $colums = 31;
            $limit = cal_days_in_month(CAL_GREGORIAN, ($m), $y);
            $select = ' DAYOFMONTH(timeof) AS from_time ';
            $group_by = ' DAYOFMONTH(timeof) ';
            for ($i = 1; $i <= $colums; ++$i) {
                $c = (($start_num + $i) % $limit);
                if ($c == 0) {
                    $c = $limit;
                }
                $walk[] = $c;
                $chartData[$c] = [
                    'x_axis' => $c,
                    'y_axis' => 0,
                ];
            }
            break;
        case 'year':
            $dateinit = date('Y-m-d H:i:s', time() - 365 * 24 * 3600);
            $start_num = substr($dateinit, 5, 2);
            $colums = 12;
            $select = ' MONTH(timeof) AS from_time ';
            $group_by = ' MONTH(timeof) ';
            for ($i = 1; $i <= $colums; ++$i) {
                $c = (($start_num + $i) % $colums);
                if ($c == 0) {
                    $c = $colums;
                }
                $walk[] = $c;
                $chartData[$c] = [
                    'x_axis' => Lang::t('_MONTH_' . ($c < 10 ? '0' : '') . $c, 'standard'),
                    'y_axis' => 0,
                ];
            }
            break;
        default:
            break;
    }

    $view_all_perm = checkPerm('view_all', true);
    $course_man = new Man_Course();
    $course_user = $course_man->getIdUserOfLevel($idCourse);

    //apply sub admin filters, if needed
    if (!$view_all_perm && Docebo::user()->getUserLevelId() == '/framework/level/admin') {
        //filter users
        require_once _base_ . '/lib/lib.preference.php';
        $ctrlManager = new ControllerPreference();
        $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
        $course_user = array_intersect($course_user, $ctrl_users);
    }

    $query_stat = 'SELECT ' . $select . ', COUNT(*) as count 
	FROM %lms_trackingeneral 
	WHERE idCourse="' . $idCourse . '"';
    if (!$view_all_perm && Docebo::user()->getUserLevelId() == '/framework/level/admin') {
        $query_stat .= ' AND idUser IN (' . implode(',', $course_user) . ') ';
    }
    if ($_REQUEST['op'] == 'userdetails' && isset($_REQUEST['id'])) {
        $query_stat .= ' AND idUser = ' . $_REQUEST['id'];
    }
    $query_stat .= ' AND timeof >= "' . $dateinit . '" AND timeof <= "' . $dateend . '" 
	GROUP BY ' . $group_by . '
	ORDER BY timeof';
    $max = 0;
    $re_stat = sql_query($query_stat);

    foreach ($re_stat as $row) {
        $number = $row['count'];
        if ($number > $max) {
            $max = $number;
        }

        $chartData[$row['from_time']]['y_axis'] = $number;
    }
    Util::get_js(FormaLms\lib\Get::rel_path('base') . '/addons/jquery/chartist/chartist.min.js', true, true);
    Util::get_js(FormaLms\lib\Get::rel_path('base') . '/addons/jquery/chartist-plugin-pointlabels/chartist-plugin-pointlabels.min.js', true, true);
    Util::get_css(FormaLms\lib\Get::rel_path('base') . '/addons/jquery/chartist/chartist.min.css', true, true);

    cout('<canvas id="statistic_chart"></canvas>', 'content');

    foreach ($chartData as $row) {
        $labels[] = $row['x_axis'];
        $series[] = $row['y_axis'];
    }

    $dataset = [
        'label' => $lang->def('_PAGE_VIEW'),
        'data' => $series,
        'borderWidth' => 1,
        'backgroundColor' => [],
        'borderColor' => [],
        'pointHoverRadius' => 5,
        'hoverBackgroundColor' => [],
    ];

    $chartString = '<script type="text/javascript">$(document).ready(function () {';
    $chartString .= '
    var dataset = ' . \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($dataset, 'json') . ";
    var backgroundColor = getComputedStyle(document.documentElement).getPropertyValue('--color-2');
    var borderColor = getComputedStyle(document.documentElement).getPropertyValue('--color-2-600');
        
        dataset.data.forEach(function(number) {
           dataset.backgroundColor.push(backgroundColor);
           dataset.borderColor.push(borderColor);
           dataset.hoverBackgroundColor.push(borderColor);
});
        
    const statsChart = new window.frontend.modules.Chart($(\"#statistic_chart\"), {
    type: 'bar',
    data: {
        labels: " . \FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($labels, 'json') . ',
        datasets: [ dataset ]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});';

    $chartString .= '});</script>';
    cout($chartString, 'content');

    cout('<div class="align-center">'
        . '<ul class="link_list_inline">', 'content');    
    //while (list(, $value) = each($times))
    foreach((Array) $times as $key => $value) {
        if ($for == $value) {
            $GLOBALS['page']->add('<li><span>' . $lang->def('_FOR_' . $value) . '</span></li>', 'content');
        } else {
            $GLOBALS['page']->add('<li><a href="' . $link . '&amp;for=' . $value . '" '
                . 'title="' . $lang->def('_FOR_TITLE_' . $value) . '">' . $lang->def('_FOR_' . $value) . '</a></li>', 'content');
        }
    }
    $GLOBALS['page']->add('</ul>'
        . '</div>', 'content');
}

function statistic()
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.table.php';
    require_once _lms_ . '/lib/lib.course.php';
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');

    $view_all_perm = checkPerm('view_all', true);

    $lang = &DoceboLanguage::createInstance('statistic', 'lms');
    $acl_man = Docebo::user()->getAclManager();
    $course_man = new Man_Course();
    $course_user = $course_man->getIdUserOfLevel($idCourse);

    //apply sub admin filters, if needed
    if (!$view_all_perm && Docebo::user()->getUserLevelId() == '/framework/level/admin') {
        //filter users
        require_once _base_ . '/lib/lib.preference.php';
        $ctrlManager = new ControllerPreference();
        $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
        $course_user = array_intersect($course_user, $ctrl_users);
    }

    $users_list = &$acl_man->getUsers($course_user);
    $GLOBALS['page']->add(getTitleArea(lang::t('_STAT', 'menu_course')), 'content');

    $GLOBALS['page']->add(
        '<div class="std_block">' .
        '</br>' .
        '<a href="index.php?r=coursestats/exportUsageStatistics" class="ico-wt-sprite subs_csv" title="' . Lang::t('_EXPORT_CSV', 'report') . '">
		<span>' . Lang::t('_EXPORT_CSV', 'report') . '</span>
	</a>'
        . '</div>', 'content'
    );

    if (FormaLms\lib\Get::sett('tracking') == 'on') {
        $GLOBALS['page']->add('<div class="title">' . $lang->def('_PAGE_VIEW') . '</div>', 'content');
        outPageView('index.php?modname=statistic&amp;op=statistic');
    }
    $GLOBALS['page']->add('<br />', 'content');
    $tb = new Table(0, $lang->def('_USERS_LIST_CAPTION'), $lang->def('_USERS_LIST_SUMMARY'));

    $type_h = ['', '', ''];
    $cont_h = [
        $lang->def('_USERNAME'),
        $lang->def('_LASTNAME'),
        $lang->def('_FIRSTNAME'),
    ];
    $tb->setColsStyle($type_h);
    $tb->addHead($cont_h);
    foreach ($users_list as $user_info ) {
        $cont = [
            '<a href="index.php?modname=statistic&amp;op=userdetails&amp;id=' . $user_info[ACL_INFO_IDST] . '" '
            . 'title="' . $lang->def('_DETAILS') . ' : ' . $acl_man->relativeId($user_info[ACL_INFO_USERID]) . '">'
            . $acl_man->relativeId($user_info[ACL_INFO_USERID]) . '</a>',
            $user_info[ACL_INFO_LASTNAME],
            $user_info[ACL_INFO_FIRSTNAME],
        ];
        $tb->addBody($cont);
    }

    $GLOBALS['page']->add(getTable($tb, '_USERS_LIST_CAPTION', 'stats_users_list'), 'content');
}

function getTable($tb, $title = null, $id)
{
    $table_head = '';
    foreach ($tb->table_head as $row) {
        $table_head .= '<tr>';
        foreach ($row->cells as $cell) {
            $table_head .= '<th>' . $cell->abbr . '</th>';
        }
        $table_head .= '</tr>';
    }

    $table_body = '';
    foreach ($tb->table_body as $row) {
        $table_body .= '<tr>';
        foreach ($row->cells as $cell) {
            $table_body .= '<td>' . $cell->label . '</td>';
        }
        $table_body .= '</tr>';
    }

    return '
        <table class="table table-striped table-bordered display" style="width:100%" id="' . $id . '">
          <thead>
            <tr>
                <th colspan="6"><b>' . Lang::t($title, 'statistic') . '</b></th>
            </tr>' .
        $table_head
        . '</thead>
          <tbody>' .
        $table_body
        . '</tbody>
        </table>'

        . '<script>
        $(function() {
          var tableId = "#' . $id . '";

          $(tableId).FormaTable({
            processing: true,
            serverSide: false,
            pagingType: "full_numbers",
            scrollX: true,
            order: [[ 0, "asc" ]],
          });
        });
        </script>';
}

function userdetails()
{
    checkPerm('view');
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');
    require_once _base_ . '/lib/lib.table.php';

    $idst_user = importVar('id', true, 0);
    $ord = importVar('ord');
    $inv = importVar('inv', true, 0);
    $link = 'index.php?modname=statistic&amp;op=userdetails&amp;id=' . $idst_user . '';

    $lang = &DoceboLanguage::createInstance('statistic', 'lms');
    $acl_man = Docebo::user()->getAclManager();
    $user_info = &$acl_man->getUser($idst_user, false);

    $page_title = [
        'index.php?modname=statistic&amp;op=statistic' => $lang->def('_STATISTICS'),
        ($user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
            ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
            : $acl_man->relativeId($user_info[ACL_INFO_USERID])),
    ];

    // Find modulename -> name int his course
    require_once _lms_ . '/lib/lib.course.php';
    $course_man = new Man_Course();
    $mods_names = &$course_man->getModulesName($idCourse);

    // find total time in the course
    $query_time = '
	SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))
	FROM %lms_tracksession 
	WHERE idCourse = "' . (int) $idCourse . '" AND idUser = "' . $idst_user . '"';
    list($tot_time) = sql_fetch_row(sql_query($query_time));

    $query_track = '
	SELECT idEnter, enterTime, lastTime, (UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)) AS howm, 
		numOp, lastFunction, lastOp, session_id 
	FROM %lms_tracksession 
	WHERE idCourse = "' . (int) $idCourse . '" AND idUser = "' . $idst_user . '"
	ORDER BY ';

    $img_down = '<img src="' . getPathImage() . 'standard/ord_asc.png" alt="' . $lang->def('_ORD_ASC_TITLE') . '" '
        . 'title="' . $lang->def('_ORD_ASC_TITLE') . '" />';
    $img_up = '<img src="' . getPathImage() . 'standard/ord_desc.png" alt="' . $lang->def('_ORD_DESC_ALT') . '" '
        . 'title="' . $lang->def('_ORD_DESC_TITLE') . '" />';
    $image_hm = $image_nop = $image_sst = '';
    switch ($ord) {
        case 'hm':
            $query_track .= ' howm ' . ($inv ? '  ' : ' DESC ');
            $order_for = $lang->def('_HOW_MUCH_TIME');
            $image_hm = ($inv ? $img_down : $img_up);
            break;
        case 'nop':
            $query_track .= ' numOp ' . ($inv ? '  ' : 'DESC');
            $order_for = $lang->def('_NUMBER_OF_OP');
            $image_nop = ($inv ? $img_down : $img_up);
            break;
        default:
            $query_track .= ' enterTime ' . ($inv ? ' DESC ' : '');
            $order_for = $lang->def('_SESSION_STARTED');
            $image_sst = ($inv ? $img_down : $img_up);
            break;
    }
    //$query_track .= " LIMIT " . $ini . ", " . FormaLms\lib\Get::sett('visuItem');
    $re_tracks = sql_query($query_track);

    $GLOBALS['page']->add(
        getTitleArea($page_title, 'statistic')
        //. '<div class="std_block">'
        . getBackUi('index.php?modname=statistic&amp;op=statistic', $lang->def('_BACK')), 'content');

    $tb = new Table(0, $lang->def('_USERS_LIST_DETAILS_CAPTION'), $lang->def('_USERS_LIST_DETAILS_SUMMARY'));

    $type_h = ['', '', 'align_center', 'align_center', ''];
    $cont_h = [
        '<a href="' . $link . '&amp;ord=sst&amp;inv=' . ($ord == 'sst' && $inv ? '0' : '1') . '" title="' . $lang->def('_ORD_FOR_SST') . '">'
        . $image_sst . ' ' . $lang->def('_SESSION_STARTED') . '</a>',
        $lang->def('_LAST_ACTION_AT'),
        '<a href="' . $link . '&amp;ord=hm&amp;inv=' . ($ord == 'hm' && $inv ? '0' : '1') . '" title="' . $lang->def('_ORD_FOR_HM') . '">'
        . $image_hm . ' ' . $lang->def('_HOW_MUCH_TIME') . '</a>',
        '<a href="' . $link . '&amp;ord=nop&amp;inv=' . ($ord == 'nop' && $inv ? '0' : '1') . '" title="' . $lang->def('_ORD_FOR_NOP') . '">'
        . $image_nop . ' ' . $lang->def('_NUMBER_OF_OP') . '</a>',
        $lang->def('_LAST_OP'),
    ];
    if (FormaLms\lib\Get::sett('tracking') == 'on') {
        $cont_h[] = '<img src="' . getPathImage() . 'standard/view.png" title="' . $lang->def('_VIEW_SESSION_DETAILS') . '" '
            . 'alt="' . $lang->def('_VIEW_SESSION_DETAILS_ALT') . '" />';
        $type_h[] = 'image';

        outPageView($link);
    }
    $tb->setColsStyle($type_h);
    $tb->addHead($cont_h);
    $type_h[2] = 'align_right';
    $tb->setColsStyle($type_h);
    $total_sec = 0;
    $chartData = [];
    while (list($id_enter, $session_start_at, $last_action_at, $how, $num_op, $last_module, $last_op, $session_id) = sql_fetch_row($re_tracks)) {
        $hours = (int) ($how / 3600);
        $minutes = (int) (($how % 3600) / 60);
        $seconds = (int) ($how % 60);
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        $readable = $hours . 'h ' . $minutes . 'm ' . $seconds . 's ';
        $start = Format::date($session_start_at);
        $cont = [
            $start,
            Format::date($last_action_at, false, true),
            $readable,
            $num_op,
            '<span class="text_bold">' . (isset($mods_names[$last_module]) ? $mods_names[$last_module] : $last_module) . '</span> [' . $last_op . ']',
        ];
        if (FormaLms\lib\Get::sett('tracking') == 'on') {
            $cont[] = '<a href="index.php?modname=statistic&amp;op=sessiondetails&amp;id=' . $idst_user . '&amp;id_enter=' . $id_enter
                . '&amp;sid=' . $session_id . '" '
                . 'title="' . $lang->def('_VIEW_SESSION_DETAILS') . ' : ' . $start . '">'
                . '<img src="' . getPathImage() . 'standard/view.png" alt="' . $lang->def('_VIEW_SESSION_DETAILS_ALT') . ' : ' . $start . '" /></a>';
        }
        $tb->addBody($cont);
    }

    $hours = (int) ($tot_time / 3600);
    $minutes = (int) (($tot_time % 3600) / 60);
    $seconds = (int) ($tot_time % 60);
    if ($minutes < 10) {
        $minutes = '0' . $minutes;
    }
    if ($seconds < 10) {
        $seconds = '0' . $seconds;
    }

    cout(
        '<div>'
        . '<span class="text_bold">' . $lang->def('_USER_TOTAL_TIME') . ' : </span>' . $hours . 'h ' . $minutes . 'm ' . $seconds . 's '
        . getTable($tb, '_USERS_LIST_DETAILS_CAPTION', 'stats_user_details')
        . getBackUi('index.php?modname=statistic&amp;op=statistic', $lang->def('_BACK'))
        . '</div>', 'content');
}

function sessiondetails()
{
    checkPerm('view');
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = $session->get('idCourse');
    require_once _base_ . '/lib/lib.table.php';

    $idst_user = importVar('id', true, 0);
    $id_enter = importVar('id_enter', true, 0);
    $p_ini = importVar('p_ini');
    $link = 'index.php?modname=statistic&amp;op=sessiondetails&amp;id=' . $idst_user . '&amp;id_enter=' . $id_enter;

    $nav_bar = new NavBar('ini', FormaLms\lib\Get::sett('visuItem'), 0, 'link');
    $nav_bar->setLink($link . '&amp;p_ini=' . $p_ini);
    $ini = $nav_bar->getSelectedElement();

    $lang = &DoceboLanguage::createInstance('statistic', 'lms');
    $acl_man = Docebo::user()->getAclManager();
    $user_info = &$acl_man->getUser($idst_user, false);

    $query_track = '
	SELECT g.function, g.type, g.timeof, UNIX_TIMESTAMP(g.timeof) AS unix_time 
	FROM %lms_trackingeneral AS g
	WHERE g.idCourse = "' . $idCourse . '" AND g.idUser = "' . $idst_user . '" AND ' .
        ' ( g.idEnter = "' . $id_enter . '" OR (  g.idEnter = 0 AND g.session_id = "' . importVar('sid') . '" ) ) '
        . ' ORDER BY g.timeof 
	LIMIT ' . $ini . ', ' . FormaLms\lib\Get::sett('visuItem');
    $re_tracks = sql_query($query_track);

    $query_tot_track = '
	SELECT COUNT(*) 
	FROM %lms_trackingeneral 
	WHERE idCourse = "' . (int) $idCourse . '" AND idUser = "' . $idst_user . '" AND idEnter = "' . $id_enter . '"';
    list($tot_elem) = sql_fetch_row(sql_query($query_tot_track));
    $nav_bar->setElementTotal($tot_elem);

    // Find modulename -> name int his course
    require_once _lms_ . '/lib/lib.course.php';
    $course_man = new Man_Course();
    $mods_names = &$course_man->getModulesName($idCourse);

    $page_title = [
        'index.php?modname=statistic&amp;op=statistic' => $lang->def('_STATISTICS'),
        'index.php?modname=statistic&amp;op=userdetails&amp;id=' . $idst_user . '&amp;p_ini=' . $p_ini => (
        $user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
            ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
            : $acl_man->relativeId($user_info[ACL_INFO_USERID])),
        $lang->def('_VIEW_SESSION_DETAILS'),
    ];
    $GLOBALS['page']->add(
        getTitleArea($page_title, 'statistic')
        . '<div class="std_block">'
        . getBackUi('index.php?modname=statistic&amp;op=userdetails&amp;id=' . $idst_user . '&amp;p_ini=' . $p_ini, $lang->def('_BACK')), 'content');

    $tb = new Table(0, $lang->def('_VIEW_SESSION_DETAILS'), $lang->def('_VIEW_SESSION_DETAILS'));

    $type_h = ['', '', ''];
    $cont_h = [
        $lang->def('_DATE'),
        $lang->def('_TYPE_OF_OPERATION'),
        $lang->def('_TIME_IN'),
    ];
    $tb->setColsStyle($type_h);
    $tb->addHead($cont_h);
    $type_h[2] = 'align_right';
    $tb->setColsStyle($type_h);
    $total_sec = 0;
    $read_previous = false;
    while ($read = sql_fetch_assoc($re_tracks)) {
        if ($read_previous !== false) {
            $time_in = $read['unix_time'] - $read_previous['unix_time'];
            $hours = (int) ($time_in / 3600);
            $minutes = (int) (($time_in % 3600) / 60);
            $seconds = (int) ($time_in % 60);
            if ($minutes < 10) {
                $minutes = '0' . $minutes;
            }
            if ($seconds < 10) {
                $seconds = '0' . $seconds;
            }

            $readable = $hours . 'h ' . $minutes . 'm ' . $seconds . 's ';
            $cont = [
                Format::date($read_previous['timeof'], false, true),
                '<span class="text_bold">' . (isset($mods_names[$read_previous['function']]) ? $mods_names[$read_previous['function']] : $read_previous['function'])
                . '</span> [' . $read_previous['type'] . ']',
                $readable,
            ];
            $tb->addBody($cont);
        }
        $read_previous = $read;
    }
    $query_last_track = '
	SELECT g.function, g.type, g.timeof, UNIX_TIMESTAMP(g.timeof) AS unix_time 
	FROM %lms_trackingeneral AS g
	WHERE g.idCourse = "' . (int) $idCourse . '" AND g.idUser = "' . $idst_user . '" AND g.idEnter = "' . $id_enter . '" 
	LIMIT ' . ($ini + FormaLms\lib\Get::sett('visuItem')) . ', 1';
    $re_track = sql_query($query_last_track);
    if (sql_num_rows($re_track) > 0) {
        $read = sql_fetch_assoc($re_track);
        $time_in = $read['unix_time'] - $read_previous['unix_time'];
        $hours = (int) ($time_in / 3600);
        $minutes = (int) (($time_in % 3600) / 60);
        $seconds = (int) ($time_in % 60);
        if ($minutes < 10) {
            $minutes = '0' . $minutes;
        }
        if ($seconds < 10) {
            $seconds = '0' . $seconds;
        }

        $readable = $hours . 'h ' . $minutes . 'm ' . $seconds . 's ';
    } else {
        $readable = '';
    }

    $cont = [
        Format::date($read_previous['timeof']),
        '<span class="text_bold">' . (isset($mods_names[$read_previous['function']]) ? $mods_names[$read_previous['function']] : $read_previous['function'])
        . '</span> [' . $read_previous['type'] . ']',
        $readable,
    ];
    $tb->addBody($cont);
    $GLOBALS['page']->add(
        getTable($tb, '_VIEW_SESSION_DETAILS', 'stats_session_detail')
        . getBackUi('index.php?modname=statistic&amp;op=userdetails&amp;id=' . $idst_user . '&amp;p_ini=' . $p_ini, $lang->def('_BACK'))
        . '</div>', 'content');
}

function statisticDispatch($op)
{
    switch ($op) {
        case 'statistic':
            statistic();
            break;
        case 'userdetails':
            userdetails();
            break;
        case 'sessiondetails':
            sessiondetails();
            break;
    }
}
