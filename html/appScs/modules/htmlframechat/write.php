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

define("IN_FORMA", true);

if(isset($_REQUEST['GLOBALS'])) die('GLOBALS overwrite attempt detected');
@error_reporting(E_COMPILE_ERROR|E_ERROR|E_CORE_ERROR);

require_once(dirname(__FILE__)."/header.php");
// -------------------------------------------------------------------

$script ="<script type=\"text/javascript\">\n";
$script.="<!--\n";
$script.="function addEmo(code)\n";
$script.="{\n";
$script.="	document.forms[0].msgtxt.value=document.forms[0].msgtxt.value+' '+code;\n";
$script.="	document.forms[0].msgtxt.focus();\n";
$script.="}\n";
$script.="//-->\n";
$script.="</script>\n";

$out->add($script, "page_head");

$op=importVar('op');
if (empty($op))
	$op="write";

switch ($op) {
	case "write": {
		showWriteForm($out, $lang);
	} break;

	case "send": {
		if (isset($_POST["savechat"]))
			saveChatMsg();
		else
			sendChatMsg();
	} break;
}

// -------------------------------------------------------------------
require_once(dirname(__FILE__)."/footer.php");
// -------------------------------------------------------------------




function showWriteForm(& $out, & $lang) {
	require_once(_base_.'/lib/lib.form.php');

	$form=new Form();

	$res="";
	$res .= 
		$form->openForm('msg_form', getPopupBaseUrl().'&amp;op=send')
		.'<div class="msg_form">';

	
	$res.='<label for="msgtxt">'.$lang->def("_MSGTXT").'</label>'
		.$form->getInputTextfield('msgtext', 'msgtxt', 'msgtxt', '', strip_tags($lang->def("_MSGTXT")), 1000, '' );
	
	$res.=$form->getButton('send', 'send', $lang->def("_SEND"), 'button_send');
	$res.=$form->getButton('savechat', 'savechat', $lang->def("_SAVE"), 'button_save');
	
	$res .= '</div>'
		.$form->closeForm();

	$res.="<script type=\"text/javascript\">\n";
	$res.="document.forms[0].msgtxt.focus();";
	$res.="</script>\n";

	$res.=$GLOBALS["chat_emo"]->emoticonList();

	$out->add($res);
}


?>