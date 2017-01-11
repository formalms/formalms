<?php //defined("IN_FORMA") or die('Direct access is forbidden.');

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

@error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);

define("IN_FORMA", true);
define("_REFRESH_RATE","2");


define("_deeppath_", '../../../');
require(dirname(__FILE__).'/../../../base.php');

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

$path_to_root =substr(_deeppath_, -1);


if ($GLOBALS["where_lms_relative"] != false)
	$GLOBALS["where_lms_relative"]=$path_to_root.'/'.$GLOBALS["where_lms_relative"];

if ($GLOBALS["where_framework_relative"] != false)
	$GLOBALS["where_framework_relative"]=$path_to_root.'/'.$GLOBALS["where_framework_relative"];

if ($GLOBALS["where_files_relative"] != false)
	$GLOBALS["where_files_relative_popup"]=$path_to_root.'/'.$GLOBALS["where_files_relative"];


if ((isset($_GET["sn"])) && ($_GET["sn"] != "")) {
	switch($_GET["sn"]) {
		case 'lms': {
			define('LMS', true);
		} break;
		case 'framework': {
			define('CORE', true);
		} break;
	}
}

	//Get::cur_plat()=$_GET["sn"];
/*else
	Get::cur_plat()="framework";*/


/*Start buffer************************************************************/

ob_start();

/*Start database connection***********************************************/
/*
$GLOBALS['dbConn'] = sql_connect($GLOBALS['dbhost'], $GLOBALS['dbuname'], $GLOBALS['dbpass']);
if( !$GLOBALS['dbConn'] )
	die( "Can't connect to db. Check configurations" );

if( !sql_select_db($dbname, $GLOBALS['dbConn']) )
	die( "Database not found. Check configurations" );

@sql_query("SET NAMES '".$GLOBALS['db_conn_names']."'", $GLOBALS['dbConn']);
@sql_query("SET CHARACTER SET '".$GLOBALS['db_conn_char_set']."'", $GLOBALS['dbConn']);
*/

/*Start session***********************************************************/

//cookie lifetime ( valid until browser closed )
//session_set_cookie_params( 0 );
//session lifetime ( max inactivity time )
//ini_set('session.gc_maxlifetime', $GLOBALS['ttlSession']);

switch (Get::cur_plat()) {

	case "lms":
	case "kms": {
		$sn = "docebo_session";
		$user_session = 'public_area';
	} break;

	case "framework":
	default: {
		if(Get::sett('common_admin_session') == 'on') {

			$sn = "docebo_session";
			$user_session = 'public_area';
		} else {

			$sn = "docebo_core";
			$user_session = 'admin_area';
		}
	} break;
}
/* session_name($sn);
session_start();

// load regional setting

// load current user from session
require_once(_base_.'/lib/lib.user.php');
$GLOBALS['current_user'] =& DoceboUser::createDoceboUserFromSession($user_session); */

// Utils and so on
//require_once($GLOBALS['where_framework'].'/lib/lib.php');

// load standard language module and put it global
$glang =& DoceboLanguage::createInstance( 'standard', 'framework');
$glang->setGlobal();

/*require_once(_base_.'/lib/lib.platform.php');*/

// create instance of StdPageWriter
StdPageWriter::createInstance();

//require_once($GLOBALS['where_framework'].'/lib/lib.preoperation.php');


$GLOBALS["template_path"]=Get::tmpl_path().'style/';
$GLOBALS["img_path"]=Get::tmpl_path().'images/chat/';


$GLOBALS['page']->add(
		'<link href="'.$GLOBALS["template_path"].'chat.css" rel="stylesheet" type="text/css" />'."\n",
		'page_head');

$out=& $GLOBALS["page"];
$out->setWorkingZone("content");
$lang=& DoceboLanguage::createInstance('htmlframechat', 'scs');

require_once($GLOBALS["where_scs"].'/lib/lib.html_chat_common.php');
require_once(dirname(__FILE__).'/functions.php');

$GLOBALS["chat_emo"]=new HtmlChatEmoticons_FrameChat();

?>