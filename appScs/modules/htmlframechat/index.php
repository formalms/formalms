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

error_reporting(E_ALL ^ E_NOTICE);
// check for remote file inclusion attempt -------------------------------
$list = array('GLOBALS', '_POST', '_GET', '_COOKIE', '_SESSION'); 
while(list(, $elem) = each($list)) {
		
	if(isset($_REQUEST[$elem])) die('Request overwrite attempt detected');
}


define("POPUP_MOD_NAME", "mod_chat");

// ----------- Popup Options ---------------
$use_room = ( isset($_GET["use_room"]) ? (int)$_GET["use_room"] : 1 );

if((isset($_GET["sn"])) && ($_GET["sn"] != "")) $sn=$_GET["sn"];
else $sn="framework";

if((isset($_GET["ri"])) && ($_GET["ri"] != "")) $setroom = "&amp;ri=".$_GET["ri"]."&amp;op=setroom";
else $setroom = "";

require_once(dirname(__FILE__).'/header.php');

$_SESSION["chat_room_id"] = ( isset($_GET["ri"]) ? (int)$_GET["ri"] : 0 );
// ------------------------------------------

$page = ''
.'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" '."\n"
.'	http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">'."\n"
.'<html xmlns="http://www.w3.org/1999/xhtml">'."\n"
.'<head>'."\n"
.'	<title>Forma Chat</title>'."\n"
.'</head>'."\n"
	.'<frameset rows="0%,80%,20%" border="0">'."\n"
		.'<frame src="check.php?sn='.$sn.'" id="chatCtl" name="chatCtl" />'."\n"
		.'<frameset cols="70%,30%" border="0">'."\n"
			.'<frame src="text.php?sn='.$sn.'" id="chatText" name="chatText" />'."\n";
if($use_room == 1) {
	
	// use room ---------------------------------------------
	$page .= '<frameset rows="50%,49%" border="0">'."\n"
				.'<frame src="users.php?sn='.$sn.'" id="chatUsers" name="chatUsers" />'."\n"
				.'<frame src="rooms.php?sn='.$sn.$setroom.'" />'."\n"
			.'</frameset>'."\n";
} else {
	
	// do room ----------------------------------------------
	$page .= '<frame src="users.php?sn='.$sn.'" id="chatUsers" name="chatUsers" />'."\n";
}
$page .= '</frameset>'."\n"
		.'<frame src="write.php?sn='.$sn.'" />'."\n"
	.'</frameset>'."\n"
.'</html>';

echo $page;

?>