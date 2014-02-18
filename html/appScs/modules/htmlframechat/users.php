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

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/header.php");

/*$last_msg_id=(int)importVar("lmi");
$getnew=haveNewMsg($last_msg_id); */

//$script.="parent.chatText.setTimeout('refreshPage()',1000);\n";
//$script.="parent.chatText.document.write('refreshPage');\n";

/*if (($last_msg_id > 0) && (count($txt_arr) > 0)) {
	foreach ($txt_arr as $key=>$val) {
		$script.="parent.chatText.appendMsg('".addslashes($val["text"])."');\n";
	}
}*/
$script ="
	<script type=\"text/javascript\">
	<!--

		function refreshPage() {".
/*			document.location.href='".getPopupBaseUrl()."'; */			
		"window.location.reload( false );\n".
		"}

		window.setTimeout('refreshPage()',10000);

	//-->
	</script>";

$out->add($script, "page_head");
//$out->add(date("H:i:s", time()), "content");
$out->add(listUsers($out, $lang), 'content');

require_once(dirname(__FILE__)."/footer.php");
// -------------------------------------------------------------------


?>