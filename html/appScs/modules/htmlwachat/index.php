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

require_once(dirname(__FILE__)."/header.php");
// -------------------------------------------------------------------

$script ="<script type=\"text/javascript\">\n";
$script.="<!--\n";
$script.="function addEmo(code)\n";
$script.="{\n";
$script.="	document.forms[1].msgtxt.value=document.forms[1].msgtxt.value+' '+code;\n";
$script.="	document.forms[1].msgtxt.focus();\n";
$script.="}\n";
$script.="//-->\n";
$script.="</script>\n";

$out->add($script, "page_head");

$op=importVar('op');
if (empty($op))
	$op="";

switch ($op) {

	case "send": {
		if (isset($_POST["savechat"]))
			saveChatMsg();
		else
			sendChatMsg();
	} break;
	
	case "setroom": {
		setRoom($out, $lang);
	} break;	
	
}
	
	
if (!isset($_SESSION["refreshrate"]))
	$_SESSION["refreshrate"]=0;
	

checkLogin(false); // Auto-reload is off in accessibility mode

//--debug:--// echo("<pre>"); print_r($_SESSION); echo("</pre>");


	
$out->add("\n<div class=\"chatText\">");
$out->add(getMsgBuffer($lang, 25));
$out->add("</div>\n");

$out->add(listUsers($out, $lang));
$out->add(listRooms($out, $lang));
$out->add("\n<div class=\"nofloat\">&nbsp;</div>\n");

$out->add(getWriteBox($out, $lang));

$backurl=getBackUrl();
if (!empty($backurl)) {
	$out->add("\n<noscript>\n");	
	$out->add("<a href=\"".$backurl."\">");
	$out->add($lang->def("_BACK")."</a>\n");
	$out->add("\n</noscript>\n");	
}

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/footer.php");
// -------------------------------------------------------------------




?>