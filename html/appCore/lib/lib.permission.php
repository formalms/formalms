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

/**
 * @author 		Fabio Pirovano <fabio@docebo.com>
 *
 * @version 	$Id:$
 */
function checkPerm($token, $return_value = false, $use_custom_name = false, $use_custom_platform = false)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if ($use_custom_name !== false) {
        $mod_name = $use_custom_name;
    } else {
        $mod_name = $GLOBALS['modname'];
    }

    if ($use_custom_platform !== false) {
        $platform_name = $use_custom_platform;
    } else {
        $platform_name = $session->get('current_action_platform');
    }

    switch ($token) {
        case 'OP' : $suff = 'view'; break;
        case 'NEW' : $suff = 'add'; break;
        case 'MOD' : $suff = 'mod'; break;
        case 'REM' : $suff = 'del'; break;
        default:  $suff = $token;
    }

    $role = '/'
            . ($platform_name != '' ? $platform_name . '/' : '')
            . 'admin/'
            . ($mod_name != '' ? $mod_name . '/' : '')
            . $suff;

    // we return true if the user is a godadmin requesting a permission in the framework platform
    if ((\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_GODADMIN) &&
        (strpos($role, '/admin/') !== false || $platform_name == 'framework')) {
        return true;
    }

    // if alredy asked
    if (isset($GLOBALS['role_asked'][$role])) {
        if ($GLOBALS['role_asked'][$role]) {
            return true;
        } elseif ($return_value) {
            return false;
        } else {
            exit("You can't access");
        }
    }

    if (\FormaLms\lib\FormaUser::getCurrentUser()->matchUserRole($role)) {
        $GLOBALS['role_asked'][$role] = true;

        return true;
    } else {
        $GLOBALS['role_asked'][$role] = false;
        if ($return_value) {
            return false;
        } else {
            exit("You can't access");
        }
    }
}

function checkRole($roleid, $return_value = true)
{
    if (\FormaLms\lib\FormaUser::getCurrentUser()->matchUserRole($roleid)) {
        return true;
    }
    if ($return_value) {
        return false;
    } else {
        exit("You can't access");
    }
}
