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

/**
 * @package course management
 * @subpackage course catalogue
 * @category ajax server
 * @version $Id:$
 *
 */

define("LMS", true);
define("IN_FORMA", true);
//define("IS_AJAX", true);
define("_deeppath_", '../../../');
require(dirname(__FILE__).'/'._deeppath_.'base.php');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

/*
if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');
if(!defined("IN_FORMA")) define("IN_FORMA", true);

$path_to_root = '../..';

// prepare refer ------------------------------------------------------------------

require_once(dirname(__FILE__).'/'.$path_to_root.'/config.php');
require_once($GLOBALS['where_config'].'/config.php');

if ($GLOBALS["where_cms_relative"] != false)
	$GLOBALS["where_cms_relative"]=$path_to_root.'/'.$GLOBALS["where_cms_relative"];

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

require_once(_i18n_.'/lib.lang.php');
require_once(_base_.'/lib/lib.template.php');
require_once(_base_.'/lib/lib.utils.php');

// security check --------------------------------------------------------------------

chkInput($_GET);
chkInput($_POST);
chkInput($_COOKIE);

// language --- use organization module
$lang =& DoceboLanguage::createInstance('organization', 'lms');

*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN"    
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
	
	<!-- <link href="<?php echo getPathTemplate(); ?>style/style.css" rel="stylesheet" type="text/css" /> -->
	<link href="<?php echo getPathTemplate(); ?>style/lms-scormplayer.css" rel="stylesheet" type="text/css" />
	<?php if(!isset($playertemplate) || trim($playertemplate) == '') $playertemplate = 'default'; ?>
	<link href="<?php echo getPathTemplate().'player_scorm/'.$playertemplate; ?>/def_style.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		function msgPrereqNotSatisfied( text ) {
			var elem = document.getElementById('prerequisites');
			elem.appendChild(document.createTextNode(text))
			elem.style.visibility = 'visible';
		}
		// inform the player 
		window.onload = function() {
			parent.scormPlayer.blankPageLoaded();
		}
	</script>
	</head>
	<body>
		<div id="bodynav">
			<div id="prerequisites" style="visibility: hidden" >
				<b><?php echo Lang::t('_ORGLOCKEDTITLE', 'organization') ?></b>
			</div>
			<br />
			<div id="prevblocklink">
				<a id="prevsco" href="#" onClick="parent.playprevclick(); return false;">
				<!--	<img src="<?php echo getPathImage(); ?>scorm/bt_sx.png" alt="prev" /> -->
					<span id="prevlink">
					</span>
				</a>
			</div>
			<div id="nextblocklink">
				<a id="nextsco" href="#" onClick="parent.playnextclick(); return false;">
					<span id="nextlink">
					</span>
				<!--	<img src="<?php echo getPathImage(); ?>scorm/bt_dx.png" alt="next" /> -->
				</a>
			</div>
		</div>
		<script type="text/javascript">
			if(parent.prevExist()) {
				var prev = document.getElementById('prevlink');
				prev.innerHTML = parent.scormPlayer.getPrevScoName();
			} else {
				var prev = document.getElementById('prevblocklink');
				prev.style.visibility = 'hidden';
			}
			if(parent.nextExist()) {
				var next = document.getElementById('nextlink');
				next.innerHTML = parent.scormPlayer.getNextScoName();
			} else {
				var next = document.getElementById('nextblocklink');
				next.style.visibility = 'hidden';
			}
		</script>
	</body>
</html>
<?php

// close database connection

mysql_close($GLOBALS['dbConn']);

ob_end_flush();

?>