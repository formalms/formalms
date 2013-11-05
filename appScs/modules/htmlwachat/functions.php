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
 * @package  DoceboSCS
 * @version  $Id: functions.php 113 2006-03-08 18:08:42Z ema $
 */

 
function getWriteBox(& $out, & $lang) {
	require_once(_base_.'/lib/lib.form.php');	 
	$res="";
	
	if (isset($_POST["refreshrate"]))
		$_SESSION["refreshrate"]=(int)$_POST["refreshrate"];
	
	$form=new Form();	
	
	$res .= 
		$form->openForm('refresh_form', getPopupBaseUrl().'&amp;op=refresh')
		.'<div class="refresh_form">';
	
	$res.='<label for="refreshrate">'.$lang->def("_AUTOREFRESH").'</label>'
		.$form->getInputTextfield('refreshtext', 'refreshrate', 'refreshrate', $_SESSION["refreshrate"], strip_tags($lang->def("_AUTOREFRESH")), 1000, '' );
	
	$res.=$lang->def("_SECONDS")."\n";
		
	$res.=$form->getButton('refresh', 'refresh', $lang->def("_REFRESH"), 'button_refresh');
	
	$res .= '</div>'
		.$form->closeForm();	
	
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
	$res.="document.forms[1].msgtxt.focus();";
	$res.="</script>\n";

	$res.= '<div class="emoticons_container"><b>';
	$res.= $lang->def("_EMOTICONS")."</b>:&nbsp;\n";	
	$res.=$GLOBALS["chat_emo"]->emoticonList();
	$res.= '</div>';	
	
	if ($_SESSION["refreshrate"] > 0) {
		$ref_meta ="<meta http-equiv=refresh content=\"".$_SESSION["refreshrate"]."; url=";
		$ref_meta.=getPopupBaseUrl()."&amp;op=refresh\" />\n";
		$out->add($ref_meta, "page_head");		
	}
	
	return $res;
}


function setRoom(& $out, & $lang) {

	$ri=importVar("ri");

	if ($ri > 0) {
		$_SESSION["chat_room_id"]=$ri;
	}

}


// ----------------------------------------------------------------------------
 
class HtmlChatEmoticons_WAChat extends HtmlChatEmoticons {
 
	function getChatEmoticonCode($code) {
	
		$res ="<b>".$code."</b>";
	
		return $res;
	}
	
	function emoticonList($ext="gif") {
	
		$res="";
		$emo_arr=$this->getEmoticonArr();
		foreach($emo_arr as $name=>$code) {
			//$res.="<a href=\"#\" onclick=\"javascript:addEmo('".$code."')\">";
			$res.=$this->getChatEmoticonCode($code);
			$res.="&nbsp;&nbsp;\n";
			//$res.="</a>&nbsp;&nbsp;\n";
		}
	
		/*
		http://groups.google.it/group/it.comp.lang.javascript/browse_thread/thread/86e45294e6f75886/46828e7f1b88f59d?q=text+cursore&rnum=3&hl=it#46828e7f1b88f59d
		*/
	
		return $res;
	}	

}

?>