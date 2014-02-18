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

require_once(dirname(__FILE__)."/header.php");
// -------------------------------------------------------------------


checkLogin();

$last_msg_id=(int)importVar("lmi");
$getnew=haveNewMsg($last_msg_id);

$script ="<script type=\"text/javascript\">\n";
$script.="<!--\n";
$script.="function refreshPage()\n";
$script.="{\n";
//$script.="	document.location.href='".getPopupBaseUrl()."&lmi=".$last_msg_id."';\n";
$script.="window.location.reload( false );\n";
$script.="}\n";
$script.="function resetLmi()\n";
$script.="{\n";
//$script.="	document.location.href='".getPopupBaseUrl()."&lmi=0';\n";
$script.="	";
$script.="	var write = parent.chatText.document.getElementById(\"write_here\"); write.innerHTML = ''; ";
$script.="}\n";
$script.="window.setTimeout('refreshPage()',2000);\n";
//$script.="parent.chatText.setTimeout('refreshPage()',1000);\n";
//$script.="parent.chatText.document.write('refreshPage');\n";

/*if (($last_msg_id > 0) && (count($txt_arr) > 0)) {
	foreach ($txt_arr as $key=>$val) {
		$script.="parent.chatText.appendMsg('".addslashes($val["text"])."');\n";
	}
}*/
/*
if ($getnew)
	$script.="parent.chatText.reloadMsg();";
*/
$script.="//-->\n";
$script.="</script>\n";

$out->add($script, "page_head");

//-- debug: --// $out->add(date("H:i:s", time()), "content");

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/footer.php");
// -------------------------------------------------------------------



?>