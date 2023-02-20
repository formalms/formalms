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

ob_start();

include 'bootstrap.php';
require '../config.php';
include_once _base_ . '/db/lib.docebodb.php';

sql_query("SET NAMES 'utf8'");
sql_query("SET CHARACTER SET 'utf8'");
//TODO NO_Strict_MODE: to be confirmed
sql_query("SET SQL_MODE = 'NO_AUTO_CREATE_USER'");

$enabled_step = 5;
$current_step = FormaLms\lib\Get::gReq('cur_step', DOTY_INT);
$upg_step = FormaLms\lib\Get::gReq('upg_step', DOTY_INT);
$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
$startVersion = $session->get('start_version');
// allowed err codes
$allowed_err_codes = [];
array_push($allowed_err_codes, 1060); // ER_DUP_FIELDNAME
array_push($allowed_err_codes, 1068); // ER_MULTIPLE_PRI_KEY
array_push($allowed_err_codes, 1091); // ER_CANT_DROP_FIELD_OR_KEY

if ($startVersion >= 3000 && $startVersion < 4000) {
    echo 'error: version (' . $startVersion . ') not supported for upgrade: too old (v3)';
    exit();
}

if ($current_step != $enabled_step) {
    echo 'error: procedure must be called from upgrade step ' . $enabled_step . ' only!!';
    exit();
}

if (!empty($session->get('to_upgrade_arr'))) {
    $to_upgrade_arr = $session->get('to_upgrade_arr');
} else {
    $to_upgrade_arr = getToUpgradeArray();
}

$last_ver = (int) $GLOBALS['cfg']['endversion'];

if ($session->get('upgrade_ok')) {
    $current_ver = $to_upgrade_arr[$upg_step - 1];
    if ($current_ver != $last_ver) {
        $formalms_version = $GLOBALS['cfg']['versions'][$current_ver];
    } else {
        $formalms_version = $GLOBALS['cfg']['versions'][$GLOBALS['cfg']['endversion']];
    }
    $upgrade_msg .= ' <br/>' . 'Upgrading to version: ' . $formalms_version;

    // --- pre upgrade -----------------------------------------------------------
    $fn = _upgrader_ . '/data/upg_data/' . $GLOBALS['cfg']['detailversions'][$current_ver]['pre'];

    if (file_exists($fn) && !is_dir($fn)) {
        $GLOBALS['debug'] .= ' <br/>' . 'Source pre-upgrade file: ' . $fn;
        require $fn;
        $func = 'preUpgrade' . $current_ver;
        if (function_exists($func)) {
            $GLOBALS['debug'] .= ' <br/>' . 'Execute pre-upgrade func: ' . $func;
            $res = $func();
            if (!$res) {
                $session->set('upgrade_ok', false);
                $session->save();
            }
        }
    }

    if ($session->get('upgrade_ok')) {
        // --- sql upgrade -----------------------------------------------------------
        $fn = _upgrader_ . '/data/upg_data/' . $GLOBALS['cfg']['detailversions'][$current_ver]['mysql'];
        if (file_exists($fn) && !is_dir($fn)) {
            $GLOBALS['debug'] .= ' <br/>' . 'Upgrade db with file: ' . $fn;
            $res = importSqlFile($fn, $allowed_err_codes);
            if (!$res['ok']) {
                $session->set('upgrade_ok', false);
                $session->save();
            }
        }
    }

    if ($session->get('upgrade_ok')) {
        // --- post upgrade ----------------------------------------------------------
        $fn = _upgrader_ . '/data/upg_data/' . $GLOBALS['cfg']['detailversions'][$current_ver]['post'];
        if (file_exists($fn) && !is_dir($fn)) {
            $GLOBALS['debug'] .= ' <br/>' . 'Source post-upgrade file: ' . $fn;
            require $fn;
            $func = 'postUpgrade' . $current_ver;
            if (function_exists($func)) {
                $GLOBALS['debug'] .= ' <br/>' . 'Execute post-upgrade func: ' . $func;
                $res = $func();
                if (!$res) {
                    $session->set('upgrade_ok', false);
                    $session->save();
                }
            }
        }
    }

    if ($session->get('upgrade_ok')) {
        // --- roles -----------------------------------------------------------------
        require_once _lib_ . '/installer/lib.role.php';
        $fn = _upgrader_ . '/data/upg_data/' . $GLOBALS['cfg']['detailversions'][$current_ver]['role'];
        if (file_exists($fn) && !is_dir($fn)) {
            $GLOBALS['debug'] .= ' <br/>' . 'Source role-upgrade file: ' . $fn;
            require $fn;
            $func = 'upgradeUsersRoles' . $current_ver;
            if (function_exists($func)) {
                $GLOBALS['debug'] .= ' <br/>' . 'Execute role-upgrade func: ' . $func;
                $role_list = $func();
                if (!empty($role_list)) {
                    $role_list_arr = explode("\n", $role_list);
                    $oc0 = getGroupIdst('/oc_0'); // all users
                    addRoles($roles, $oc0);
                }
            }
            $func = 'upgradeGodAdminRoles' . $current_ver;
            if (function_exists($func)) {
                $GLOBALS['debug'] .= ' <br/>' . 'Execute role-upgrade func: ' . $func;
                $role_list = $func();
                if (!empty($role_list)) {
                    $role_list_arr = explode("\n", $role_list);
                    $godadmin = getGroupIdst('/framework/level/godadmin'); // god admin
                    addRoles($roles, $godadmin);
                }
            }
        }
    }
}

// Save version number if upgrade was successfull:
if ($session->get('upgrade_ok')) {
    $qtxt = "UPDATE core_setting SET param_value = '" . $formalms_version . "' WHERE param_name = 'core_version' ";
    $q = sql_query($qtxt);

    //MODIFICA TEMPORANEA reset del template di default a STANDARD in futuro controllo del templates
    $qtxt = "UPDATE core_setting SET param_value = 'standard' WHERE param_name = 'defaultTemplate' ";
    $q = sql_query($qtxt);
}

$GLOBALS['debug'] = $upgrade_msg
                    . '<br/>' . 'Result: ' . ($session->get('upgrade_ok') ? 'OK ' : 'ERROR !!! ')
                    . '<br/>' . $GLOBALS['debug']
                    . '<br>------';

//echo $GLOBALS['debug'];

if ($session->get('upgrade_ok')) {
    $res = ['res' => 'ok', 'msg' => $GLOBALS['debug']];
} else {
    $res = ['res' => 'Error', 'msg' => $GLOBALS['debug']];
}

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

require_once _base_ . '/lib/lib.json.php';
$json = new Services_JSON();
echo $json->encode($res);
//session_write_close();

// flush buffer
ob_end_flush();

exit();

// -----------------------------------------------------------------------------
// -----------------------------------------------------------------------------
