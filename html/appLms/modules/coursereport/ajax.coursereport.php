<?php
/*
@error_reporting(E_ALL);
@ini_set('display_errors', true);
@ini_set('html_errors', true);
@ini_set('error_reporting', E_ALL);
*/

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

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @author Giovanni Derks
 * @version $Id:$
 *
 */

/*
if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');

if(!defined("IN_FORMA")) define("IN_FORMA", true);
*/

$path_to_root = '../../..';

// prepare refer ------------------------------------------------------------------

/*
require_once(dirname(__FILE__).'/'.$path_to_root.'/config.php');
require_once(dirname(__FILE__).'/'.$path_to_root.'/base.php');
*/
//require_once(dirname(__FILE__).'/'.$path_to_root.'/appLms/config.php');


ob_start();

/*

// connect to database -------------------------------------------------------------------
$GLOBALS['dbConn'] = mysql_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass']);
if( !$GLOBALS['dbConn'] )
	die( "Can't connect to db. Check configurations" );

if( !mysql_select_db($cfg['db_name'], $GLOBALS['dbConn']) )
	die( "Database not found. Check configurations" );
*/

//@sql_query("SET NAMES '".$GLOBALS['db_conn_names']."'", $GLOBALS['dbConn']);
//@sql_query("SET CHARACTER SET '".$GLOBALS['db_conn_char_set']."'", $GLOBALS['dbConn']);

// load lms setting ------------------------------------------------------------------
/*
session_name("docebo_session");
session_start();
*/

// load regional setting --------------------------------------------------------------

// load current user from session -----------------------------------------------------

//require_once(_i18n_.'/lib.lang.php');
//require_once(_base_.'/lib/lib.template.php');
//require_once(_base_.'/lib/lib.utils.php');

// security check --------------------------------------------------------------------

//chkInput($_GET);
//chkInput($_POST);
//chkInput($_COOKIE);

/*
$GLOBALS['operation_result'] = '';
function aout($string)
{
	$GLOBALS['operation_result'] .= $string;
}
*/

// here all the specific code ==========================================================

$op = $_GET['op'];
$GLOBALS['prefix_lms'] = 'learning';

switch($op) {
	case "extendedquestdetail" : {

		$id_test = $_GET['id_test'];
		$id_quest = $_GET['id_quest'];

		$result = array('id_quest' => $id_quest);

		$query_track =	"SELECT idTrack"
						." FROM ".$GLOBALS['prefix_lms']."_testtrack"
						." WHERE idTest = '".$id_test."'";

		$result_track = mysql_query($query_track);

		while(list($id_track) = mysql_fetch_row($result_track))
		{
			$query_track_answer =	"SELECT more_info"
									." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
									." WHERE idTrack = '".$id_track."'"
									." AND idQuest = '".$id_quest."'";

			$result_track_answer = mysql_query($query_track_answer);

			while(list($more_info) = mysql_fetch_row($result_track_answer))
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

		$path = '/appLms/'.Get::sett('pathtest');

		$query_track =	"SELECT idTrack"
						." FROM ".$GLOBALS['prefix_lms']."_testtrack"
						." WHERE idTest = '".$id_test."'";

		$result_track = mysql_query($query_track);

		while(list($id_track) = mysql_fetch_row($result_track))
		{
			$query_track_answer =	"SELECT more_info"
									." FROM ".$GLOBALS['prefix_lms']."_testtrack_answer"
									." WHERE idTrack = '".$id_track."'"
									." AND idQuest = '".$id_quest."'";

			$result_track_answer = mysql_query($query_track_answer);

			while(list($more_info) = mysql_fetch_row($result_track_answer))
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

/* mysql_close($GLOBALS['dbConn']); */

ob_clean();
echo $GLOBALS['operation_result'];
ob_end_flush();

?>
