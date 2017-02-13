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
require_once(_base_.'/lib/lib.json.php');

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

require_once(_base_.'/lib/lib.domxml.php');

$xml_answer = false;

$xml_answer = (isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : false);

//Test di richiesta
/*$xml_answer =	'<?xml version="1.0" encoding="utf-8"?'.'>'
						.'<ews>'
						.'<errorcode>0</errorcode>'
						.'<errormessage></errormessage>'
						.'<sessions>'
						.'<session sid="1" roomid="1" uid="1039" role="2" date="2008-09-30 11:00:13" duration="3565"/>'
						.'</sessions>'
						.'</ews>';*/

if($xml_answer === false)
	aout('<?xml version="1.0" encoding="UTF-8"?><ews><errorcode>1</errorcode><errormessage>No data found</errormessage></ews>');

$dom_answer = new DoceboDOMDocument();
$dom_answer->loadXML( trim($xml_answer) );

$dlist_code = $dom_answer->getElementsByTagName('errorcode');
$dlist_msg = $dom_answer->getElementsByTagName('errormessage');
$dnode_code = $dlist_code->item(0);
$dnode_msg = $dlist_msg->item(0);

$e_code = $dnode_code->textContent;
$e_msg = $dnode_msg->textContent;

if($e_code == 0)
{
	require_once($GLOBALS['where_scs'].'/lib/lib.teleskill.php');
	
	$teleskill = new Teleskill_Management();
	
	$teleskill->clearRoomLog($roomid);
	
	$dlist_sessions = $dom_answer->getElementsByTagName('session');
	
	$dlist_sessions = $dlist_sessions->item(0);
	
	if((int)$dlist_sessions->getAttribute('uid') != 0 && $dlist_sessions->getAttribute('uid') !== '')
	{
		$gmt = date('P', fromDatetimeToTimestamp($dlist_sessions->getAttribute('date')));
		$gmt_split = explode(':', $gmt);
		$gmt_offset = (int)$gmt_split[0];
		
		$query_control =	"SELECT COUNT(*)"
							." FROM ".$GLOBALS['prefix_scs']."_teleskill_log"
							." WHERE roomid = '".$dlist_sessions->getAttribute('roomid')."'"
							." AND idUser = '".(int)$dlist_sessions->getAttribute('uid')."'";
		
		list($control) = sql_fetch_row(sql_query($query_control));
		
		if($control)
		{
			$query =	"UPDATE ".$GLOBALS['prefix_scs']."_teleskill_log"
						." SET role = '".$dlist_sessions->getAttribute('role')."',"
						." duration = (duration + '".$dlist_sessions->getAttribute('duration')."'),"
						." access = (access + ".($dlist_sessions->getAttribute('duration') == 0 ? 1 : 0).")"
						." WHERE roomid = '".$dlist_sessions->getAttribute('roomid')."'"
						." AND idUser = '".(int)$dlist_sessions->getAttribute('uid')."'";
			
			$result = sql_query($query);
		}
		else
		{
			$query =	"INSERT INTO ".$GLOBALS['prefix_scs']."_teleskill_log (roomid, idUser, role, `date`, duration, access)"
						." VALUES ('".$dlist_sessions->getAttribute('roomid')."',
						'".(int)$dlist_sessions->getAttribute('uid')."',
						'".$dlist_sessions->getAttribute('role')."',
						'".date('Y-m-d H:i:s', fromDatetimeToTimestamp($dlist_sessions->getAttribute('date')) + $gmt_offset * 3600)."',
						'".$dlist_sessions->getAttribute('duration')."',
						'".($dlist_sessions->getAttribute('duration') == 0 ? 1 : 0)."')";
			
			$result = sql_query($query);
		}
		
		if($result)
			aout('<?xml version="1.0" encoding="UTF-8"?><ews><errorcode>0</errorcode><errormessage></errormessage></ews>');
		else
			aout('<?xml version="1.0" encoding="UTF-8"?><ews><errorcode>1</errorcode><errormessage>Error during insertion in db</errormessage></ews>');
	}
	else
		aout('<?xml version="1.0" encoding="UTF-8"?><ews><errorcode>1</errorcode><errormessage>No data found</errormessage></ews>');
}
else
	aout('<?xml version="1.0" encoding="UTF-8"?><ews><errorcode>1</errorcode><errormessage>No data found</errormessage></ews>');
// =====================================================================================

// close database connection

sql_close($GLOBALS['dbConn']);

ob_clean();
print($GLOBALS['operation_result']);
ob_end_flush();

?>