<?php

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

if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');

define("IN_FORMA", true);

$path_to_root = '../..';

// prepare refer ------------------------------------------------------------------

require_once(dirname(__FILE__).'/'.$path_to_root.'/config.php');
require_once($GLOBALS['where_config'].'/config.php');



if ($GLOBALS["where_kms_relative"] != false)
	$GLOBALS["where_kms_relative"]=$path_to_root.'/'.$GLOBALS["where_kms_relative"];

if ($GLOBALS["where_lms_relative"] != false)
	$GLOBALS["where_lms_relative"]=$path_to_root.'/'.$GLOBALS["where_lms_relative"];

if ($GLOBALS["where_framework_relative"] != false)
	$GLOBALS["where_framework_relative"]=$path_to_root.'/'.$GLOBALS["where_framework_relative"];

if ($GLOBALS["where_files_relative"] != false) {
	$GLOBALS["where_files_relative"]=$path_to_root.'/'.$GLOBALS["where_files_relative"];
}

ob_start();

// load lms setting ------------------------------------------------------------------

session_name("docebo_session");
session_start();

// load regional setting --------------------------------------------------------------

// load current user from session -----------------------------------------------------
require_once(_base_.'/lib/lib.user.php');
$GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromSession('public_area');

//require_once(_i18n_.'/lib.lang.php');
require_once(_base_.'/lib/lib.template.php');
require_once(_base_.'/lib/lib.utils.php');

// security check --------------------------------------------------------------------

chkInput($_GET);
chkInput($_POST);
chkInput($_COOKIE);

$GLOBALS['operation_result'] = '';
function aout($string) { $GLOBALS['operation_result'] .= $string; }

// here all the specific code ==========================================================

$op = importVar('op');

switch ($op) {
	case "getmaxroom":
		$room_type=importVar('room_type');
		$maxp=Get::sett($room_type."_max_participant");
		aout($maxp);
		break;

	default:
		aout(0);
		break;
}

// =====================================================================================

// close database connection

sql_close($GLOBALS['dbConn']);

ob_clean();
print($GLOBALS['operation_result']);
ob_end_flush();

?>