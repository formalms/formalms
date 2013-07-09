<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @author Giovanni Derks
 * @version $Id:$
 *
 */

if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');

if(!defined("IN_DOCEBO")) define("IN_DOCEBO", true);

$path_to_root = '../../..';

// prepare refer ------------------------------------------------------------------

require_once(dirname(__FILE__).'/'.$path_to_root.'/config.php');
require_once(dirname(__FILE__).'/'.$path_to_root.'/doceboLms/config.php');

ob_start();

// connect to database -------------------------------------------------------------------

$GLOBALS['dbConn'] = mysql_connect($GLOBALS['dbhost'], $GLOBALS['dbuname'], $GLOBALS['dbpass']);
if( !$GLOBALS['dbConn'] )
	die( "Can't connect to db. Check configurations" );

if( !mysql_select_db($dbname, $GLOBALS['dbConn']) )
	die( "Database not found. Check configurations" );

@sql_query("SET NAMES '".$GLOBALS['db_conn_names']."'", $GLOBALS['dbConn']);
@sql_query("SET CHARACTER SET '".$GLOBALS['db_conn_char_set']."'", $GLOBALS['dbConn']);

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

function aout($string)
{
	$GLOBALS['operation_result'] .= $string;
}

// here all the specific code ==========================================================

$op = importVar('op');

switch($op) {
	case "extendedquestdetail" : {
		$lang =& DoceboLanguage::createInstance( 'coursereport', 'lms');
		
		$id_test = importVar('id_test', true, 0);
		$id_quest = importVar('id_quest', true, 0);
		
		$result = array('id_quest' => $id_quest);
		
		$query_track =	"SELECT idTrack"
						." FROM ".$GLOBALS['prefix_lms']."_testtrack"
						." WHERE idTest = '".$id_test."'";
		
		$result_track = sql_query($query_track);
		
		while(list($id_track) = sql_fetch_row($result_track))
		{
			$query_track_answer =	"SELECT more_info"
									." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
									." WHERE idTrack = '".$id_track."'"
									." AND idQuest = '".$id_quest."'";
			
			$result_track_answer = sql_query($query_track_answer);
			
			while(list($more_info) = sql_fetch_row($result_track_answer))
				$result['records'][] = $more_info;
		}
  
		require_once(_base_.'/lib/lib.json.php');
		
		$json = new Services_JSON();
		$output = $json->encode($result);
  		aout($output);
	};break;
	
	case "fileuploaddetail" : {
		$lang =& DoceboLanguage::createInstance( 'coursereport', 'lms');
		
		$id_test = importVar('id_test', true, 0);
		$id_quest = importVar('id_quest', true, 0);
		
		$result = array('id_quest' => $id_quest);
		
		$path = '/doceboLms/'.Get::sett('pathtest');
		
		$query_track =	"SELECT idTrack"
						." FROM ".$GLOBALS['prefix_lms']."_testtrack"
						." WHERE idTest = '".$id_test."'";
		
		$result_track = sql_query($query_track);
		
		while(list($id_track) = sql_fetch_row($result_track))
		{
			$query_track_answer =	"SELECT more_info"
									." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
									." WHERE idTrack = '".$id_track."'"
									." AND idQuest = '".$id_quest."'";
			
			$result_track_answer = sql_query($query_track_answer);
			
			while(list($more_info) = sql_fetch_row($result_track_answer))
			{
				$link = '<a href="index.php?modname=question&amp;op=quest_download&amp;type_quest=upload'
						.'&amp;id_quest='.$id_quest.'&amp;id_track='.$id_track.'">'
						.$more_info.'</a>';
				
				if ($more_info != '')
					$result['records'][] = $link;//'<a href="'.$GLOBALS['where_files_relative'].$path.$more_info.'">'.$more_info.'</a>';
			}
		}
  
		require_once(_base_.'/lib/lib.json.php');
		
		$json = new Services_JSON();
		$output = $json->encode($result);
  		aout($output);
	};break;
}

// =====================================================================================

// close database connection

mysql_close($GLOBALS['dbConn']);

ob_clean();
echo $GLOBALS['operation_result'];
ob_end_flush();

?>
