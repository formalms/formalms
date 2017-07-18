<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

require_once(_base_.'/lib/lib.utils.php');

$page_url = getPopupBaseUrl();

if ((isset($_GET["op"])) && ($_GET["op"] != ""))
	$op=substr($_GET["op"], 0, 60);
else
	$op="main";

// ---------- Popup Menu ----------------------

$menu_label 	= array();
$menu_url 		= array();

if(canAccessPersonalMedia()) {
	$menu_label["personal"]=$lang->def("_PERSONAL_MEDIA");
	$menu_url["personal"]=$page_url."&amp;op=personal";
}

// --------------------------------------------------------------------------------------------

define("_USER_FPATH_INTERNAL", "/common/users/");
define("_USER_FPATH", $GLOBALS["where_files_relative"]._USER_FPATH_INTERNAL);

define("_FPATH", $GLOBALS["where_files_relative"]._FPATH_INTERNAL);

define("_PPATH", $GLOBALS["where_files_relative"]._PPATH_INTERNAL);


switch ($op) {

	case "main": {
		show_personal_media($out, $lang);
		//show_main($out, $lang);
	} break;

	case "personal": {
		show_personal_media($out, $lang);
	} break;

	case "addpersonal": {
		add_personal_media($out, $lang);
	} break;

	case "inspersonal": {
		ins_personal_media();
	} break;

	case "delpersonal": {
		del_personal_media($out, $lang);
	} break;

	case "select": {
		select_media($out, $lang);
	} break;


	default: {
		show_personal_media($out, $lang);
		//show_main($out, $lang);
	} break;
}

// --------------------------------------------------------------------------------------------

function canAccessPersonalMedia() {
	$level_id = Docebo::user()->getUserLevelId();
	if(Docebo::user()->isAnonymous()) return false;

	if ( (Get::sett("htmledit_image_godadmin") && $level_id == ADMIN_GROUP_GODADMIN) ||
		((Get::sett("htmledit_image_admin")) && ($level_id == ADMIN_GROUP_ADMIN )  ) ||
		((Get::sett("htmledit_image_user")) && ($level_id == ADMIN_GROUP_USER)) ) {

		return true;
	} else {
		return false;
	}
}



function show_main(& $out, & $lang) {

    if (canAccessPersonalMedia() )
		$out->add($lang->def("_POPUP_MEDIA_INTRO"));
	else
		$out->add("<span style=\"color: #FF0000;\">".$lang->def("_POPUP_MEDIA_NOACCESS")."</span>\n");

}



function show_personal_media(& $out, & $lang) {

	if (!canAccessPersonalMedia())
		die("You can't access!");

	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.mimetype.php');
	require_once(_base_.'/lib/lib.multimedia.php');

	$url 		= getPopupBaseUrl();
	$tab 		= new Table();
	$user_id 	= (int)Docebo::user()->getIdSt();

	//if(Get::sett('hteditor') == 'tinymce') {
		
		$GLOBALS['page']->add('<script type="text/javascript">'
		.'var FileBrowserDialogue = {
		    mySubmit : function (url) {
		        var URL = url;
				
				
		        var win = top.tinymce.activeEditor.windowManager.getParams().window;
				var input = top.tinymce.activeEditor.windowManager.getParams().input;

		        // insert information now
		        win.document.getElementById(input).value = URL;
				
				// simulate the onchange event to let tinymce load the dimension of the image
				var element=win.document.getElementById(input);
				if ("createEvent" in document) {
				    var evt = document.createEvent("HTMLEvents");
				    evt.initEvent("change", false, true);
				    element.dispatchEvent(evt);
				}
				else{
				    element.fireEvent("onchange");
				}
				
		        // close popup window
				top.tinymce.activeEditor.windowManager.close();
				
		    }
		}
		'

		.'</script>', 'page_head');
	//}

	$head = array($lang->def("_PREVIEW"), $lang->def("_TYPE"), $lang->def("_FILENAME"));
	$head[] = "<img src=\"".getPathImage()."standard/download.png\" alt=\"".$lang->def("_ATTACHMENT")."\" title=\"".$lang->def("_ATTACHMENT")."\" />";
	$head[] = "<img src=\"".getPathImage()."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" title=\"".$lang->def("_DEL")."\" />";
	$head_type = array('preview80', 'image', '', 'image', 'image');

	$tab->setColsStyle($head_type);
	$tab->addHead($head, '');

	$path =(strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' ).'/';
	$path.=$GLOBALS["where_files_relative"];
	

	$qtxt = "
	SELECT *
	FROM ".$GLOBALS["prefix_fw"]."_user_file
	WHERE user_idst='".$user_id."'";
	$q = sql_query($qtxt);

	if (($q) && (sql_num_rows($q) > 0)) {
		while($row = sql_fetch_array($q)) {
			$site_url="http://".$_SERVER['HTTP_HOST'].$path.'/common/users/';
			$rowcnt = array();

			if (!empty($row["media_url"])) {
				$rowcnt[]="&nbsp;";
				
			}
			else {
				$file = _USER_FPATH.rawurlencode($row["real_fname"]);
				$rowcnt[] = "<div style=\"text-align: center;\">"
					.'<a href="#" onclick="javascript:window.open(\''.$file.'\',\'\');return false;">'
					."<img height=\"120\" src=\"".$file."\" alt=\"".$row["fname"]."\" title=\"".$row["fname"]."\" /></a></div>";
			}

			$img = "<img src=\"".getPathImage('fw').mimeDetect($file)."\" alt=\"".$row["fname"]."\" title=\" ";
			$img .= $row["fname"]."\" />\n";
			$rowcnt[] = $img;

			$rowcnt[]=$row["fname"];

			if (!empty($row["media_url"])) {
				$type=getMediaType($row["media_url"]);
				$site_url=$row["media_url"];
			}
			else {
				$type=getMediaType($row["fname"]);
			}

			$sel_url =$url."&amp;op=select&amp;from=personal&amp;type=".$type."&amp;item_id=".$row["id"];
			$img = "<img src=\"".getPathImage()."standard/download.png\" alt=\"".$lang->def("_ATTACHMENT")."\" title=\"".$lang->def("_ATTACHMENT")."\" />\n";
			$rowcnt[] = '<a href="'.$sel_url.'" '
				.( Get::sett('hteditor') == 'tinymce'
					? 'onclick="FileBrowserDialogue.mySubmit(\''.$site_url.$row["real_fname"].'\'); return false;"' : '' )
				.'>'
				.$img."</a>\n";

			$img = "<img src=\"".getPathImage('fw')."standard/delete.png\" alt=\"".$lang->def("_DEL")."\" title=\"".$lang->def("_DEL")."\" />\n";
			$rowcnt[] = "<a href=\"".$url."&amp;op=delpersonal&amp;item_id=".$row["id"]."\">".$img."</a>\n";

			$tab->addBody($rowcnt);
		}
	}
	$url = getPopupBaseUrl()."&amp;op=addpersonal";
	$tab->addActionAdd("<a href=\"".$url."\">".$lang->def("_ADD")."</a>");

	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "invalid_ext" : $GLOBALS['page']->add($lang->def('_INVALID_EXTENSION'));break;
			case "upload_err" 	: $GLOBALS['page']->add($lang->def('_ERROR_UPLOAD'));break;
			case "upload_ok" 	: $GLOBALS['page']->add($lang->def('_OPERATION_SUCCESSFUL'));break;
		}
	}
    $GLOBALS['page']->add("<hr>");
	$GLOBALS['page']->add($tab->getTable());
}

function add_personal_media(& $out, & $lang) {

	if (!canAccessPersonalMedia())
		die("You can't access!");

	require_once(_base_."/lib/lib.form.php");

	$url 	= getPopupBaseUrl()."&amp;op=inspersonal";
	$form 	= new Form();

	$GLOBALS['page']->add(
 		$form->openForm("popup_form", $url, false, false, "multipart/form-data")

		.$form->openElementSpace()
		.$form->getFilefield($lang->def("_FILENAME"), "file", "file")
		.$form->getTextfield($lang->def("_MEDIA_URL"), "media_url", "media_url", 255)
		.$form->closeElementSpace()

		.$form->openButtonSpace()
		.$form->getButton('save', 'save', $lang->def('_SAVE'))
		.$form->getReset('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()

		.$form->closeForm()
	);
}


function ins_personal_media() {

	if (!canAccessPersonalMedia())
		die("You can't access!");

	include_once(_base_.'/lib/lib.upload.php');
	include_once(_base_.'/lib/lib.multimedia.php');

	$url = getPopupBaseUrl()."&amp;op=personal";
	$user_id 		= Docebo::user()->getIdSt();

	$is_streaming =FALSE;
	if ((isset($_FILES["file"]["name"])) && (!empty($_FILES["file"]["name"]))) {
		$fname 			= $_FILES["file"]["name"];
		$size 			= $_FILES["file"]["size"];
		$tmp_fname 		= $_FILES["file"]["tmp_name"];
		$real_fname 	= $user_id.'_'.mt_rand(0,100).'_'.time().'_'.$fname;
	}
	else {

		$media_url =$_POST["media_url"];
		$fname="";
		$real_fname="";

		if (!empty($media_url)) {
			$is_streaming =TRUE;
			if (isYouTube($media_url)) {
				$fname =str_replace("http://www.", "", strtolower($media_url));
			}
			else {
				$fname=basename($media_url);
				$fname=(strpos($fname, "?") !== FALSE ? preg_replace("/(\?.*)/", "", $fname) : $fname);
			}
		}
	}

	if (!$is_streaming) {
        
        $valid_ext = explode(",", Get::sett('file_upload_whitelist',''));
		$ext = strtolower(end(explode(".", $fname)));
		if (!in_array($ext, $valid_ext))
			Util::jump_to($url.'&result=invalid_ext');

		sl_open_fileoperations();
		$f1 = sl_upload($tmp_fname, _USER_FPATH_INTERNAL.$real_fname);
		sl_close_fileoperations();
		if(!$f1) {
			// upload error
			Util::jump_to($url.'&result=upload_err');
		}
	}

	$qtxt = "INSERT INTO ".$GLOBALS["prefix_fw"]."_user_file ";
	$qtxt .= " ( user_idst, type, fname, real_fname, media_url, size, uldate ) VALUES ";
	$qtxt .= " ('".$user_id."', 'image', '".$fname."', '".addslashes($real_fname)."', '".$media_url."', '".$size."', NOW())";
	$q = sql_query($qtxt);

	Util::jump_to($url.'&result='.( $q ? 'upload_ok' : 'upload_err' ));
}



function del_personal_media(& $out, & $lang) {

	include_once(_base_.'/lib/lib.upload.php');
	include_once(_base_."/lib/lib.form.php");

	if (isset($_POST["canc_del"])) {
		Util::jump_to(getPopupBaseUrl()."&amp;op=personal");
	}
	else if (isset($_POST["conf_del"])) {

		$id=(int)$_POST["id"];

		$user_id=Docebo::user()->getIdSt();

		$qtxt ="SELECT real_fname FROM ".$GLOBALS["prefix_fw"]."_user_file ";
		$qtxt.="WHERE id='".$id."' AND user_idst='".$user_id."' AND type='image'";
		$q=sql_query($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$row=sql_fetch_array($q);
			$real_fname=$row["real_fname"];

			//@sl_unlink(_USER_FPATH.$real_fname);

			$qtxt="DELETE FROM ".$GLOBALS["prefix_fw"]."_user_file WHERE id='$id';";
			$q=sql_query($qtxt);
		}

		Util::jump_to(getPopupBaseUrl()."&amp;op=personal");
	}
	else {

		//load info
		$id=(int)importVar("item_id");
		$user_id=Docebo::user()->getIdSt();
		list($fname) = sql_fetch_row(sql_query("
		SELECT fname
		FROM ".$GLOBALS["prefix_fw"]."_user_file
		WHERE id='".$id."' AND user_idst='".$user_id."'"));

		$GLOBALS['page']->add("<div class=\"std_block\">\n");

		$form=new Form();

		$GLOBALS['page']->add($form->openForm("popup_form", getPopupBaseUrl()."&amp;op=delpersonal"));

		$GLOBALS['page']->add($form->getHidden("id", "id", $id));

		$GLOBALS['page']->add(getDeleteUi(
		$lang->def('_AREYOUSURE'),
			'<span class="text_bold">'.$lang->def('_FILENAME').' :</span> '.$fname.'<br />',
			false,
			'conf_del',
			'canc_del'));

		$GLOBALS['page']->add($form->closeForm());
		$GLOBALS['page']->add("</div>\n");
	}

}



function select_media(& $out, & $lang) {

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.multimedia.php');

	$form=new Form();

	$from=importVar("from");
	$item_id=(int)importVar("item_id");

	$src="";
	$title="";
	$path="";

	switch($from) {

		case "personal": {

			if (!canAccessPersonalMedia())
				die("You can't access!");

			$user_id=Docebo::user()->getIdSt();
			$path=_USER_FPATH_INTERNAL;
			$relative_path=_USER_FPATH;
			$preview_path=_USER_FPATH;

			$qtxt ="SELECT real_fname, media_url FROM ".$GLOBALS["prefix_fw"]."_user_file ";
			$qtxt.="WHERE id='".$item_id."' AND user_idst='".$user_id."' AND type='image'";
			$q=sql_query($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {
				$row=sql_fetch_array($q);
				if (!empty($row["media_url"])) {
					$src=$row["media_url"];
				}
				else {
					$src=$row["real_fname"];
				}
			}

		} break;

	}


	$res="";
	$url=getPopupBaseUrl()."&amp;op=main";

	if (!empty($row["media_url"])) {
		$media_url =$row["media_url"];
		$popup_file_path =$media_url;
	}
	else {
		$popup_file_path =$path.$src;
	}

	addMediaPopupJS($popup_file_path);

	// TODO: remove inline style
	if (file_exists($preview_path.$src)) {
		$style="width: 100px; padding: 2px; border: 1px solid #AAAAAA; margin-bottom: 0.4em;";
		$res.="<img style=\"".$style."\" src=\"".$preview_path.$src."\" alt=\"".$title."\" title=\"".$title."\" />\n";
	}
	else {
		$media_type =getMediaType($src);
		$style="width: 64px; padding: 2px; border: 1px solid #AAAAAA; margin-bottom: 0.4em;";
		$res.="<img style=\"".$style."\" src=\"".getPathImage('fw')."media/".$media_type.".png\" alt=\"".$title."\" title=\"".$title."\" />\n";
	}


 	$res.=$form->openForm("popup_form", $url, false, false, '', "onSubmit=\"insItem();\" onReset=\"closePopup();\"");

 	$res.=$form->openElementSpace();


	$type=getPopupSelType();

	switch ($type) {

		case "image": {
			$res.=$form->getTextfield($lang->def("_ALT_TXT"), "alt_text", "alt_text", 255, $title);
			$res.=$form->getTextfield($lang->def("_TITLE"), "title_text", "title_text", 255, $title);


		} break;

		case "flash": {

			$flash_info=getSwfInfoArray($relative_path.$src);
			$res.=$form->getTextfield($lang->def("_WIDTH"), "width", "width", 4, $flash_info["width"]);
			$res.=$form->getTextfield($lang->def("_HEIGHT"), "height", "height", 4, $flash_info["height"]);

			$res.=$form->getTextfield($lang->def("_BGCOLOR"), "bgcolor", "border", 7, "#FFF");

		} break;

		case "audio":
		case "video":
		case "streaming": {

			require_once(_base_.'/lib/lib.json.php');
			$json = new Services_JSON();
			if (!empty($media_url)) {
				$code =getStreamingEmbed($media_url, FALSE, $src);
			}
			else {
				$cut_from =strlen($GLOBALS["where_files_relative"]);
				$base_relative_path =$GLOBALS["base_where_files_relative"].substr($relative_path, $cut_from);
				$current_pl = Get::cur_plat();
				$site_file_path =(getPLSetting($current_pl, "url").$base_relative_path);
				$code =getEmbedPlay($site_file_path, $src, FALSE, FALSE, FALSE, FALSE, FALSE, FALSE, TRUE);
			}
			$code ='<div>'.$code.'</div>';
			$core_url =getPLSetting("framework", "url");
			$bad_path =$GLOBALS["where_framework_relative"]."/addons/players/";
			$good_path =$core_url."addons/players/";
			$code =str_replace($bad_path, $good_path, $code);
			$content = array("code" => $code);
			$embed_code = $json->encode($content);
			$res.=$form->getHidden("embed_code", "embed_code", rawurlencode($embed_code));
			$res.=$src;
		} break;

	}


 	$res.=$form->closeElementSpace();

	$res.=$form->openButtonSpace();
	$res.=$form->getButton('apply', 'apply', $lang->def('_SAVE'));
	$res.=$form->getReset('undo', 'undo', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();

	$res.=$form->closeForm();

	$out->add($res);
}



function addMediaPopupJS($src) {

	$res="";

	$sn = Get::cur_plat();

	$path =(strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' ).'/';
	$path.=$GLOBALS["where_files_relative"]; //."/";




	$site_url="http://".$_SERVER['HTTP_HOST'].$path;
	$src=($site_url.$src);



	switch ($GLOBALS["popup"]["editor"]) {

		case "fck": { // ---------------------------------------- Fck Editor --------
			$res.=addFckPopupJS($src);
		} break;

		case "xinha": { // -------------------------------------- Xinha -------------
			$res.=addXinhaPopupJS($src);
		} break;

		case "widgeditor": { // --------------------------------- widgEditor --------
			$res.=addWidgPopupJS($src);
		} break;

	}

	$GLOBALS["page"]->add($res, "page_head");
}


function getPopupCommonCode() {
	$res="";

	$res.="function GetE( elementId )\n";
	$res.="{\n";
	$res.="return document.getElementById( elementId )  ;\n";
	$res.="}\n";

	$res.="function closePopup() {\n";
	$res.="window.close();\n";
	$res.="}\n";

	return $res;
}


function addXinhaPopupJS($src) {

	$relative_path =$GLOBALS['where_framework_relative'];
	$relative_path.=( substr($GLOBALS['where_framework_relative'], -1) == '/' ? '' : '/' );

	$res ="<script type=\"text/javascript\" src=\"".$relative_path;
	$res.="addons/xinha/popups/popup.js\"></script>\n";

	$res.= "<script type=\"text/javascript\"><!--\n";
	// ---------------------------------------------------------------------------

	$type=getPopupSelType();

	switch ($type) {

		case "image": {
			$field_list="['title_text', 'alt_text']";
		} break;

		case "flash": {
			$field_list="['bgcolor', 'width', 'height']";
		} break;

		case "audio":
		case "video":
		case "streaming": {
			$field_list="['embed_code']";
		} break;

	}

	$res.= <<<JS_END

		function insItem() {
		// pass data back to the calling window
		var fields = $field_list;
		var param = new Object();
		for (var i in fields) {
			var id = fields[i];
			var el = document.getElementById(id);
			param[id] = el.value;
		}
		param['url']='$src';
		param['type']='$type';
		__dlg_close(param);
		return false;
	}
JS_END;


	// ---------------------------------------------------------------------------
	$res.=getPopupCommonCode();

	$res.="--></script>\n";

	return $res;

}


function addWidgPopupJS($src) {

	$res = "<script type=\"text/javascript\"><!--\n";

	// ---------------------------------------------------------------------------

	$res.="function insItem() {\n";

	$res.="var theToolbar 		= window.opener.widg.theToolbar;\n";
	$res.="var theWidgEditor 	= window.opener.widg.theWidgEditor;\n";
	$res.="var theIframe 		= window.opener.widg.theIframe;\n";

	$type=getPopupSelType();

	switch ($type) {

		case "image": {
			$res.=<<<JS_END

			var theTitle 	= GetE('title_text').value;
			var theAlt 		= GetE('alt_text').value;

			var theSelection = null;
			var theRange = null;

			/* IE selections */
			if (theIframe.contentWindow.document.selection)
			{
				/* Escape quotes in alt text */
				theAlt = theAlt.replace(/"/g, "'");

				theSelection = theIframe.contentWindow.document.selection;
				theRange = theSelection.createRange();
				theRange.collapse(false);
				theRange.pasteHTML("<img title=" + theTitle + " alt=" + theAlt + " src=" + theImage + " />");

			}
			/* Mozilla selections */
			else
			{
				try
				{
					theSelection = theIframe.contentWindow.getSelection();
				}
				catch (e)
				{
					return false;
				}

				if (theSelection.rangeCount > 0) {
					theRange = theSelection.getRangeAt(0);
				} else {
					theRange = theWidgEditor.theIframe.contentWindow.document.createRange();
					theRange.setStart(theWidgEditor.theIframe.contentWindow.document.body, 1);
					theSelection.addRange(theRange);
				}

				var theImageNode = theIframe.contentWindow.document.createElement("img");

				theImageNode.src = "$src";
				theImageNode.alt = theAlt;
				theImageNode.title = theTitle;

				theRange.insertNode(theImageNode);

			}
JS_END;
		} break;

		case "audio":
		case "video":
		case "streaming": {

			addAjaxJs(); // req. by our friend json..

			$res.=<<<JS_END

			var embed_code=GetE('embed_code').value;

			var theSelection = null;
			var theRange = null;

			/* IE selections */
			if (theIframe.contentWindow.document.selection)
			{
				/* Escape quotes in alt text */
				theAlt = theAlt.replace(/"/g, "'");

				theSelection = theIframe.contentWindow.document.selection;
				theRange = theSelection.createRange();
				theRange.collapse(false);
				theRange.pasteHTML(embed_code);

			}
			/* Mozilla selections */
			else
			{
				try
				{
					theSelection = theIframe.contentWindow.getSelection();
				}
				catch (e)
				{
					return false;
				}

				if (theSelection.rangeCount > 0) {
					theRange = theSelection.getRangeAt(0);
				} else {
					theRange = theWidgEditor.theIframe.contentWindow.document.createRange();
					theRange.setStart(theWidgEditor.theIframe.contentWindow.document.body, 1);
					theSelection.addRange(theRange);
				}

				var parsed = unescape(embed_code);
				parsed = parsed.evalJSON(true);

				var oItem = theIframe.contentWindow.document.createElement("div");
				oItem.innerHTML=parsed.code;
				theRange.insertNode(oItem);

			}
JS_END;

		} break;

		case "flash": {

			$res.=<<<JS_END

			var itemWidth=GetE('width').value;
			var itemHeight=GetE('height').value;
			var itemBgCol=GetE('bgcolor').value;

			var theSelection = null;
			var theRange = null;

			/* IE selections */
			if (theIframe.contentWindow.document.selection)
			{
				/* Escape quotes in alt text */
				theAlt = theAlt.replace(/"/g, "'");

				theSelection = theIframe.contentWindow.document.selection;
				theRange = theSelection.createRange();
				theRange.collapse(false);
				theRange.pasteHTML("<img title=" + theTitle + " alt=" + theAlt + " src=" + theImage + " />");

			}
			/* Mozilla selections */
			else
			{
				try
				{
					theSelection = theIframe.contentWindow.getSelection();
				}
				catch (e)
				{
					return false;
				}

				if (theSelection.rangeCount > 0) {
					theRange = theSelection.getRangeAt(0);
				} else {
					theRange = theWidgEditor.theIframe.contentWindow.document.createRange();
					theRange.setStart(theWidgEditor.theIframe.contentWindow.document.body, 1);
					theSelection.addRange(theRange);
				}

				var oItem = theIframe.contentWindow.document.createElement("embed");

				oItem.src = "$src";
				oItem.bgcolor = itemBgCol;
				oItem.width = itemWidth;
				oItem.height = itemHeight;
				oItem.type="application/x-shockwave-flash";

				theRange.insertNode(oItem);

			}
JS_END;

		} break;
	}


	$res.="window.close();\n";
	$res.="}\n";

	$res.="\n";

	// ---------------------------------------------------------------------------

	$res.=getPopupCommonCode();

	$res.="--></script>\n";

	return $res;
}


function addFckPopupJS($src) {

	$res="";

	$res.="<script type=\"text/javascript\"><!--\n";

	// ---------------------------------------------------------------------------

	$res.="function insItem() {\n";


	$type=getPopupSelType();

	switch ($type) {

		case "image": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var titleTxt = GetE('title_text').value;\n";
			$res.="var altTxt = GetE('alt_text').value;\n";

			$res.="// Get the selected item (if available).\n";
			$res.="var oItem = FCK.Selection.GetSelectedElement() ;\n";

			$res.="var bHasItem = ( oItem != null ) ;\n";

			$res.="if ( bHasItem ) {\n";
			$res.="FCK.Selection.Delete() ;\n";
			$res.="}\n";

			$res.="oItem = FCK.CreateElement( 'IMG' ) ;\n";

			$res.="oItem.src=\"".$src."\" ;\n";
			$res.="oItem.title=titleTxt ;\n";
			$res.="oItem.alt=altTxt ;\n";
		} break;

		case "flash": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var itemWidth = GetE('width').value;\n";
			$res.="var itemHeight = GetE('height').value;\n";
			$res.="var itemBgCol = GetE('bgcolor').value;\n";

			$res.="// Get the selected item (if available).\n";
			$res.="var oItem = FCK.Selection.GetSelectedElement() ;\n";

			$res.="var bHasItem = ( oItem != null ) ;\n";

			$res.="if ( bHasItem ) {\n";
			$res.="FCK.Selection.Delete() ;\n";
			$res.="}\n";

			$res.="oItem = FCK.CreateElement( 'embed' ) ;\n";

			$res.="oItem.src=\"".$src."\" ;\n";
			$res.="oItem.bgcolor=itemBgCol ;\n";
			$res.="oItem.width=itemWidth ;\n";
			$res.="oItem.height=itemHeight ;\n";
			$res.="oItem.type=\"application/x-shockwave-flash\" ;\n";

			$res.="FCK.OnAfterSetHTML() ;\n";

		} break;

		case "audio":
		case "video":
		case "streaming": {

			addAjaxJs(); // req. by our friend json..

			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var html_code = GetE('embed_code').value;\n";

			$res.='var parsed = unescape(html_code);'."\n";
			$res.='parsed = parsed.evalJSON(true);'."\n";

			$res.="FCK.InsertHtml(parsed.code);\n";
			$res.="FCK.OnAfterSetHTML() ;\n";

		} break;

	}

	$res.="window.close();\n";
	$res.="}\n\n";

	// ---------------------------------------------------------------------------

	$res.=getPopupCommonCode();

	$res.="--></script>\n";

	return $res;
}


function getPopupSelType() {
	$res="image";

	if ((isset($_GET["type"])) && (!empty($_GET["type"])))
		$res=$_GET["type"];

	return $res;
}


?>
