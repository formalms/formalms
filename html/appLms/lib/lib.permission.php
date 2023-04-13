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

function checkPerm($mode, $return_value = false, $use_mod_name = false, $is_public = false)
{
    if ($use_mod_name != false) {
        $mod_name = $use_mod_name;
    } else {
        $mod_name = $GLOBALS['modname'];
    }

    switch ($mode) {
        case 'OP' :
        case 'view' : $suff = 'view'; break;
        case 'NEW' :
        case 'add' : $suff = 'add'; break;
        case 'MOD' :
        case 'mod' : $suff = 'mod'; break;
        case 'REM' :
        case 'del' : $suff = 'del'; break;
        default:  $suff = $mode;
    }

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $idCourse = ($session->has('idCourse') && !empty($session->get('idCourse'))) ? $session->get('idCourse') : null;

    $role = '/' . FormaLms\lib\Get::cur_plat() . '/'
        . ($idCourse && $is_public == false ? 'course/private/' . $idCourse . '/' : 'course/public/')
        . $mod_name . '/' . $suff;
    if (!$return_value && $idCourse) {
        require_once _lms_ . '/lib/lib.track_user.php';
        TrackUser::setActionTrack(getLogUserId(), $idCourse, $mod_name, $suff);
    }

    if (Forma::user()->matchUserRole($role)) {
        return true;
    } elseif ($return_value) {
        return false;
    } else {
        exit("You can't access" . $role);
    }
}

function checkPermForCourse($mode, $id_course, $return_value = false, $use_mod_name = false)
{
    if ($use_mod_name != false) {
        $mod_name = $use_mod_name;
    } else {
        $mod_name = $GLOBALS['modname'];
    }

    switch ($mode) {
        case 'OP' :
        case 'view' : $suff = 'view'; break;
        case 'NEW' :
        case 'add' : $suff = 'add'; break;
        case 'MOD' :
        case 'mod' : $suff = 'mod'; break;
        case 'REM' :
        case 'del' : $suff = 'del'; break;
        default:  $suff = $mode;
    }

    $role = '/' . FormaLms\lib\Get::cur_plat() . '/course/private/' . $id_course . '/' . $mod_name . '/' . $suff;

    if (!$return_value && isset($id_course)) {
        TrackUser::setActionTrack(getLogUserId(), $id_course, $mod_name, $suff);
    }

    if (Forma::user()->matchUserRole($role)) {
        return true;
    } else {
        if ($return_value) {
            return false;
        } else {
            exit("You can't access");
        }
    }
}

function checkRole($roleid, $return_value = true)
{
    if (Forma::user()->matchUserRole($roleid)) {
        return true;
    }
    if ($return_value) {
        return false;
    } else {
        exit("You can't access");
    }
}
