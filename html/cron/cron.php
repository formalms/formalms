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

if (isset($_REQUEST['GLOBALS'])) {
    exit('GLOBALS overwrite attempt detected');
}

if (!defined('IN_FORMA')) {
    define('IN_FORMA', true);
}

$path_to_root = '..';

// prepare refer ------------------------------------------------------------------

require_once dirname(__FILE__) . '/' . $path_to_root . '/config.php';
require_once dirname(__FILE__) . '/' . $path_to_root . '/appLms/config.php';

ob_start();

@sql_query("SET NAMES '" . $GLOBALS['db_conn_names'] . "'", $GLOBALS['dbConn']);
@sql_query("SET CHARACTER SET '" . $GLOBALS['db_conn_char_set'] . "'", $GLOBALS['dbConn']);
@sql_query("SET SQL_MODE = 'NO_AUTO_CREATE_USER'", $GLOBALS['dbConn']);

// load lms setting ------------------------------------------------------------------

//session_name('docebo_session');
//session_start();

// load regional setting --------------------------------------------------------------

// load current user from session -----------------------------------------------------
require_once _base_ . '/lib/lib.user.php';
$GLOBALS['current_user'] = &FormaUser::createFormaUserFromSession('public_area');

//require_once(_i18n_.'/lib.lang.php');
require_once _base_ . '/lib/lib.template.php';
require_once _base_ . '/lib/lib.utils.php';

// security check --------------------------------------------------------------------

chkInput($_GET);
chkInput($_POST);
chkInput($_COOKIE);

$GLOBALS['operation_result'] = '';

function aout($string)
{
    $GLOBALS['operation_result'] .= $string;
}

// here all the specific code ==========================================================

// =====================================================================================

// close database connection

sql_close($GLOBALS['dbConn']);

ob_clean();
echo $GLOBALS['operation_result'];
ob_end_flush();
