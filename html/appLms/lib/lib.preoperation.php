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

$request = \Forma\lib\Request\RequestManager::getInstance()->getRequest();

$session = $request->getSession();

// access granted only if user is logged in
if (Docebo::user()->isAnonymous()) { // !isset($_GET['no_redirect']) && !isset($_POST['no_redirect']) XXX: redirection???
    // save requested page in session to call it after login
    $loginRedirect = $_SERVER[REQUEST_URI];

    // redirect to index
    Util::jump_to(Forma\lib\Get::rel_path('base') . '/index.php?login_redirect=' . $loginRedirect);
}



// get maintenence setting
$query = ' SELECT param_value FROM %adm_setting'
        . " WHERE param_name = 'maintenance'"
        . ' ORDER BY pack, sequence';
$maintenance = $db->fetch_row($db->query($query))[0];

// handling maintenece
if ($maintenance == 'on' && Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
    // only god admins can access maintenence - logout the user
    Util::jump_to(Forma\lib\Get::rel_path('base') . '/index.php?r=' . _logout_);
}

// handling access from multiple sessions
if (Forma\lib\Get::sett('stop_concurrent_user') == 'on' && $session->has('idCourse')) {
    // two user logged at the same time
    if (!TrackUser::checkSession(getLogUserId())) {
        TrackUser::resetUserSession(getLogUserId());
        Util::jump_to(Forma\lib\Get::rel_path('base') . '/index.php?r=' . _stopconcurrency_);
    }
}

if ($session->has('must_renew_pwd') && $session->get('must_renew_pwd') == 1) {
    // handling required password renewal

    $GLOBALS['modname'] = '';
    $GLOBALS['op'] = '';
    $GLOBALS['req'] = 'lms/profile/renewalpwd';
} elseif ($session->has('request_mandatory_fields_compilation') && $session->get('request_mandatory_fields_compilation') == 1 && $GLOBALS['req'] != 'precompile/set') {
    // handling required mandatory fields compilation

    $GLOBALS['modname'] = '';
    $GLOBALS['op'] = '';
    $GLOBALS['req'] = 'lms/precompile/show';
} elseif ($GLOBALS['modname'] == '' && $GLOBALS['op'] == '' && $GLOBALS['req'] == '') {
    // setting default action

    // if course is in session, enter the course
    if (!$session->isEmpty('idCourse')) {
        // TODO: in corso
        if ($session->has('sel_module_id') && $session->get('sel_module_id') != 0) {
            $query = ' SELECT module_name, default_op, mvc_path'
                    . ' FROM %lms_module'
                    . ' WHERE idModule = ' . (int) $session->get('sel_module_id');
            list($modname, $op, $mvc_path) = sql_fetch_row(sql_query($query));
            if ($mvc_path !== '') {
                $GLOBALS['req'] = $mvc_path;
            }
            $GLOBALS['modname'] = $modname;
            $GLOBALS['op'] = $op;
        }
    } else {
        // select default home page
        $GLOBALS['req'] = Forma\lib\Get::home_page_req();
    }
}

$next_action = Forma\lib\Get::req('act', DOTY_STRING, false);
if ($next_action != false && Forma\lib\Get::sett('sco_direct_play', 'off') == 'on') {
    $id_course = Forma\lib\Get::req('id_course', DOTY_INT, 0);
    $id_item = Forma\lib\Get::req('id_item', DOTY_INT, '');
    $chapter = Forma\lib\Get::req('chapter', DOTY_MIXED, false);
    if ($id_course) {
        // if we have a id_course setted we will log the user into the course,
        // if no specific action are required we will redirect the user into the first page
        // otherwise we will continue to another option
        require_once _lms_ . '/lib/lib.course.php';
        logIntoCourse($id_course, ($next_action == false || $next_action == 'none' ? true : false));

        // specific action required
        switch ($next_action) {
            case 'playsco':
                $linkto = 'index.php?modname=organization&op=custom_playitem&id_item=' . $id_item . '&start_from_chapter=' . $chapter . '&collapse_menu=1';
                Util::jump_to($linkto);
                break;
        }
    }
}

//operation that is needed before loading grafiphs element, menu and so on
switch ($GLOBALS['op']) {
    case 'aula':
        require_once _lms_ . '/lib/lib.course.php';
        $idCourse =Forma\lib\Get::req('idCourse',DOTY_ALPHANUM);
        if (!logIntoCourse($idCourse, true)) {
            $session->set('current_main_menu','1');
            $session->set('sel_module_id','1');
            $GLOBALS['modname'] = 'middlearea';
            $GLOBALS['op'] = 'show';
        }
        break;
    //registering menu information
    case 'unregistercourse':
        //if a course is selected the selection is deleted
        if ($session->has('idCourse')) {
            TrackUser::closeSessionCourseTrack();

            $session->remove('idCourse');
            $session->remove('idEdition');
        }
        if ($session->has('test_assessment')) {
            $session->remove('test_assessment');
        }
        if ($session->has('direct_play')) {
            $session->remove('direct_play');
        }
        if ($session->has('cp_assessment_effect')) {
            $session->remove('cp_assessment_effect');
        }

        $session->set('current_main_menu','1');
        $session->set('sel_module_id','1');
        $session->set('is_ghost',true);

        $session->save();
        $GLOBALS['modname'] = 'middlearea';
        $GLOBALS['op'] = 'show';

        break;
    case 'selectMain':
        $idMain =Forma\lib\Get::req('idMain');
        $session->set('current_main_menu',$idMain);
        $session->save();
        $firstPage = firstPage($idMain);

        if ($firstPage['modulename'] != '') {
            Util::jump_to('index.php?modname=' . $firstPage['modulename'] . '&op=' . $firstPage['op'] . '&sel_module=' . $firstPage['idModule']);
        }
        break;
    //change language for register user
    case 'registerconfirm':
        $language =Forma\lib\Get::pReq('language',DOTY_STRING);

        Lang::set($language);
        break;
    case 'registerme':
        $randomCode = Forma\lib\Get::req('random_code',DOTY_STRING);
        list($language_reg) = sql_fetch_row(sql_query(' SELECT language FROM ' . $GLOBALS['prefix_lms'] . "_user_temp  WHERE random_code = '" . $randomCode . "'"));
        if ($language_reg !== '') {
            Lang::set($language_reg);
        }
        break;
    default:

        break;
}

// special operation
$sop = importVar('sop', false, '');
if ($sop) {
    if (is_array($sop)) {
        $sop = key($sop);
    }
    switch ($sop) {
        case 'setcourse':
            $id_c = Forma\lib\Get::req('sop_idc', DOTY_INT, 0);

            if ($session->has('idCourse') && $session->get('idCourse') != $id_c) {
                TrackUser::closeSessionCourseTrack();
                $session->remove('idCourse');
                $session->remove('idEdition');

                require_once _lms_ . '/lib/lib.course.php';
                logIntoCourse($id_c, false);
            } elseif (!$session->has('idCourse') || empty($session->get('idCourse'))) {
                require_once _lms_ . '/lib/lib.course.php';
                logIntoCourse($id_c, false);
            }
            if ($session->has('cp_assessment_effect')) {
                $session->remove('cp_assessment_effect');
            }
            $session->save();
            break;
        case 'resetselmodule':
            $session->remove('sel_module_id');
            $session->save();
            break;
        case 'unregistercourse':
            if ($session->has('idCourse')) {
                TrackUser::closeSessionCourseTrack();
                $session->remove('idCourse');
                $session->remove('idEdition');

            }
            if ($session->has('cp_assessment_effect')) {
                $session->remove('cp_assessment_effect');
            }
            $session->save();
            break;
        case 'changelang':
            Lang::set(Forma\lib\Get::req('new_lang', DOTY_MIXED));
            $session->set('changed_lang', true);
            $session->save();
            break;
        default:
            break;
    }
}

// istance the course description class
if ($session->has('idCourse') && !isset($GLOBALS['course_descriptor'])) {
    require_once _lms_ . '/lib/lib.course.php';
    $GLOBALS['course_descriptor'] = new DoceboCourse($session->get('idCourse'));
}
