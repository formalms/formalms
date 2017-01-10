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

$menu_label["main"]=$lang->def("_LINK");
$menu_url["main"]=$page_url."&amp;op=main";


if(isEditorInWiki()) {
	$menu_label["wiki_new"]=$lang->def("_WIKI_PAGE_NEW");
	$menu_url["wiki_new"]=$page_url."&amp;op=wiki_new";
	$menu_label["wiki_sel"]=$lang->def("_WIKI_PAGE_SEL");
	$menu_url["wiki_sel"]=$page_url."&amp;op=wiki_sel";
}

// ---------------------------------------------

drawMenu($menu_label, $menu_url, $op);

// --------------------------------------------------------------------------------------------

$GLOBALS['page']->add(
		'<link href="'.$GLOBALS["where_framework_relative"].'/templates/standard/style/base-old-treeview.css" rel="stylesheet" type="text/css" />'."\n",
		'page_head');

$GLOBALS['page']->add(
		 '<script type="text/javascript"> window.resizeTo(640, 480); </script>',
		 'page_head');

// --------------------------------------------------------------------------------------------

define("_USER_FPATH_INTERNAL", "/common/users/");
define("_USER_FPATH", $GLOBALS["where_files_relative"]._USER_FPATH_INTERNAL);

define("_FPATH", $GLOBALS["where_files_relative"]._FPATH_INTERNAL);

define("_PPATH", $GLOBALS["where_files_relative"]._PPATH_INTERNAL);


switch ($op) {

	case "main": {
		show_main($out, $lang);
	} break;

	case "wiki_new": {
		showWikiNew($out, $lang);
	} break;

	case "wiki_sel": {
		showWikiSelect($out, $lang);
	} break;

}

// --------------------------------------------------------------------------------------------





function show_main(& $out, & $lang) {

	require_once(_base_."/lib/lib.form.php");

	$form=new Form();

	$from=importVar("from");
	$item_id=(int)importVar("item_id");


	$res="";

	addLinkPopupJS();

	$base_url =getPopupBaseUrl();
	$on_submit ="onSubmit=\"insItem();\" onReset=\"closePopup();\"";
 	$res.=$form->openForm("popup_form", $base_url, false, false, '', $on_submit);

 	$res.=$form->openElementSpace();


	$type=getPopupSelType();

	$url="http://";
	$title="";
	$code="";

	$res.=$form->getTextfield($lang->def("_TEXTOF"), "code", "code", 255, $code);
	$res.=$form->getTextfield($lang->def("_URL"), "url", "url", 255, $url);
	$res.=$form->getTextfield($lang->def("_TITLE"), "title", "title", 255, $title);

 	$res.=$form->closeElementSpace();

	$res.=$form->openButtonSpace();
	$res.=$form->getButton('apply', 'apply', $lang->def('_SAVE'));
	$res.=$form->getReset('undo', 'undo', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();

	$res.=$form->closeForm();

	$out->add($res);
	addLinkPopupAfterJS();
}



function showWikiNew(& $out, & $lang) {

	require_once(_base_."/lib/lib.form.php");

	$form=new Form();

	$from=importVar("from");
	$item_id=(int)importVar("item_id");

	$title="";


	$res="";
	$url=getPopupBaseUrl()."&amp;op=wiki_new";

	addLinkPopupJS();


 	$res.=$form->openForm("popup_form", $url, false, false, '', "onSubmit=\"insItem();\" onReset=\"closePopup();\"");

 	$res.=$form->openElementSpace();


	$type=getPopupSelType();


	$res.=$form->getTextfield($lang->def("_PAGE_TITLE"), "title", "title", 255);
	//$res.=$form->getTextfield($lang->def("_EXTENDED_TITLE"), "ext_title", "ext_title", 255);

 	$res.=$form->closeElementSpace();

	$res.=$form->openButtonSpace();
	$res.=$form->getButton('apply', 'apply', $lang->def('_SAVE'));
	$res.=$form->getReset('undo', 'undo', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();

	$res.=$form->closeForm();

	$out->add($res);
}



function showWikiSelect(& $out, & $lang) {

	require_once(_base_."/lib/lib.form.php");
	require_once(_base_.'/lib/lib.urlmanager.php');
	require_once($GLOBALS["where_framework"]."/lib/lib.wiki.php");

	$um =& UrlManager::getInstance();
	$um->setStdQuery("sn=".Get::cur_plat()."&op=wiki_sel");
	//$um->setBaseUrl($_SERVER["script_name"]);

	$wiki_id=getEditorWikiId();
	$cwp =new CoreWikiPublic($wiki_id);

	$form=new Form();

	$from=importVar("from");
	$item_id=(int)importVar("item_id");

	$title="";


	$res="";


	$url=getPopupBaseUrl()."&amp;op=wiki_sel";

	addLinkPopupJS();


 	$res.=$form->openForm("popup_form", $url, false, false, '', "onReset=\"closePopup();\"");

 	$res.=$form->openElementSpace();


	$type=getPopupSelType();

/*
	$res.=$form->getTextfield($lang->def("_PAGE_TITLE"), "title", "title", 255);
	$res.=$form->getTextfield($lang->def("_EXTENDED_TITLE"), "ext_title", "ext_title", 255);
	*/

	$wiki_lang=$cwp->getWikiLanguage();

	// TODO: change getLanguage() with the current wiki language;
	// try to pass arguments in a better way, like using GET.. [?]
	$wiki_page_db=new TreeDb_WikiDb($cwp->wikiManager->getWikiPageTable(), $cwp->wikiManager->getWikiPageInfoTable(),  $wiki_id, $wiki_lang);
	$treeView=new TreeView_WikiView($wiki_page_db, 'wiki_tree');

	$treeView->hideAction();
	$treeView->parsePositionData($_POST, $_POST, $_POST);
	$folder_id=$treeView->getSelectedFolderId();
	$folder_name=$treeView->getFolderPrintName($wiki_page_db->getFolderById($folder_id));

	$res.=$treeView->autoLoad();


	if ($folder_id > 0) {

		$other_param ='onClick="insItem();"';
		$page_id =& $folder_id;

		$page_info=$cwp->wikiManager->getPageInfo($wiki_id, $wiki_lang, FALSE, $page_id);
		$res.=$form->getHidden("title", "title", $page_info["title"]);
		$res.=$form->getHidden("page_code", "page_code", $page_info["page_code"]);

	}
	else {
		$other_param ='disabled="disabled"';
	}

 	$res.=$form->closeElementSpace();

	$res.=$form->openButtonSpace();
	$res.=$form->getButton('apply', 'apply', $lang->def('_SAVE'), FALSE, $other_param);
	$res.=$form->getReset('undo', 'undo', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();

	$res.=$form->closeForm();

	$out->add($res);
}


function addLinkPopupJS() {

	$res="";

	$sn = Get::cur_plat();
/*	if ($sn != "framework")
		$src=$GLOBALS[$sn]["url"].$GLOBALS["where_files_relative"]."/".$src;
	else
		$src=$GLOBALS["url"].$GLOBALS["where_files_relative"]."/".$src; */

	$path =(strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' ).'/';
	$path.=$GLOBALS["where_files_relative"]; //."/";

	//-- test : //
	//echo(cleanUrlPath("http://127.0.0.1:88/folder/folder/appCore//addons/./mod_media/../../../files/common/")); return 0;


	//$site_url="http://".$_SERVER['HTTP_HOST'].$path;
	//$src=cleanUrlPath($site_url.$src);

//	$src=str_replace("//", "/", $src);

	switch ($GLOBALS["popup"]["editor"]) {

		case "fck": { // ---------------------------------------- Fck Editor --------
			$res.=addFckPopupJS();
		} break;

		case "xinha": { // -------------------------------------- Xinha -------------
			$res.=addXinhaPopupJS();
		} break;

		case "widgeditor": { // --------------------------------- widgEditor --------
			$res.=addWidgPopupJS();
		} break;

	}

	$GLOBALS["page"]->add($res, "page_head");
}


function addLinkPopupAfterJS() {
	$res="";
	$res.= "<script type=\"text/javascript\"><!--\n";

	switch ($GLOBALS["popup"]["editor"]) {

		case "fck": { // ---------------------------------------- Fck Editor --------
			$res.=addFckLinkPopupAfterJS();
		} break;

		case "xinha": { // -------------------------------------- Xinha -------------
			$res.=addXinhaLinkPopupAfterJS();
		} break;

		case "widgeditor": { // --------------------------------- widgEditor --------
			$res.=addWidgLinkPopupAfterJS();
		} break;

	}

	$res.="--></script>\n";
	$GLOBALS["page"]->add($res, "content");
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

	$res.=<<<JS_END

	function get_browser_obj() {
		var Browser = {
			IE:     !!(window.attachEvent && !window.opera),
			Opera:  !!window.opera,
			WebKit: navigator.userAgent.indexOf('AppleWebKit/') > -1,
			Gecko:  navigator.userAgent.indexOf('Gecko') > -1 && navigator.userAgent.indexOf('KHTML') == -1
		}

		return Browser;
	}

	function get_link_attrib(str) {

		var my_exp = /<a\s*([^>]*)/i;

		matches = my_exp.exec(str);

		if (matches && matches[1] != null) {
			return matches[1];
		}
		else {
			return false;
		}
	}

	function get_link_content(str) {

		var my_exp = /<a.*?>(.*)<\/a>/i;

		matches = my_exp.exec(str);

		if (matches && matches[1] != null) {
			return matches[1];
		}
		else {
			return false;
		}
	}

	function parse_attrib_string(str) {
		//alert(str);
		var attrib = new Array();
		var my_exp = /\s*(.*?)\="(.*?)"\s*/gi;

		while ((matches = my_exp.exec(str)) != null) {
			attr_name = matches[1];
			attr_val = matches[2];
			//alert(attr_name+'->'+attr_val);
			attrib[attr_name.toLowerCase()] = attr_val;
		}

		//alert(attrib.href);
		return attrib;
	}
JS_END;
	$res.="\n";

	return $res;
}


function addXinhaPopupJS() {

	$relative_path =$GLOBALS['where_framework_relative'];
	$relative_path.=( substr($GLOBALS['where_framework_relative'], -1) == '/' ? '' : '/' );

	$res ="<script type=\"text/javascript\" src=\"".$relative_path;
	$res.="addons/xinha/popups/popup.js\"></script>\n";

	$res.= "<script type=\"text/javascript\"><!--\n";

	// ---------------------------------------------------------------------------

	$type=getPopupSelType();

	switch ($type) {

		case "link": {
			$field_list="['url', 'title', 'code']";
		} break;

		case "wiki_new": {
			$field_list="['title']";
		} break;

		case "wiki_sel": {
			$field_list="['title', 'page_code']";
		} break;

	}

	$res.= <<<JS_END

		function insItem() {
		// pass data back to the calling window
		var fields = $field_list;
		var param = new Object();
		for(i=0 ; i < fields.length; i++) {
			var id = fields[i];
			var el = document.getElementById(id);
			param[id] = el.value;
		}
		param['type']='$type';
		__dlg_close(param);
		return false;
	}
JS_END;
/*
	function insItem() {
  var required = {
    "f_url": i18n("You must enter the URL")
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  // pass data back to the calling window
  var fields = ["title_text", "alt_text", "border"];
  var param = new Object();
  for (var i in fields) {
    var id = fields[i];
    var el = document.getElementById(id);
    param[id] = el.value;
  }
  __dlg_close(param);
  return false; */

	// ---------------------------------------------------------------------------
	$res.=getPopupCommonCode();

	$res.="--></script>\n";

	return $res;

}


function addWidgPopupJS() {

	$res = "<script type=\"text/javascript\"><!--\n";

	// ---------------------------------------------------------------------------

	$res.="function insItem() {\n";

	$res.="var theToolbar 		= window.opener.widg.theToolbar;\n";
	$res.="var theWidgEditor 	= window.opener.widg.theWidgEditor;\n";
	$res.="var theIframe 		= window.opener.widg.theIframe;\n";

	$type=getPopupSelType();

	switch ($type) {

		case "link": {
			$res.=<<<JS_END
			var theCode 	= GetE('code').value;
			var theHref 	= GetE('url').value;
			var theTitle 	= GetE('title').value;
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
				theRange.pasteHTML("<a href=" + theHref + " title=" + theTitle + ">" + theCode + "</a>");

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

					// Deletes the actual selection contents.
					for ( var i = 0 ; i < theSelection.rangeCount ; i++ )
					{
						theSelection.getRangeAt(i).deleteContents() ;
					}

					theRange = theSelection.getRangeAt(0);
				} else {
					theRange = theWidgEditor.theIframe.contentWindow.document.createRange();
					theRange.setStart(theWidgEditor.theIframe.contentWindow.document.body, 1);
					theSelection.addRange(theRange);
				}

				var theLinkNode = theIframe.contentWindow.document.createElement("a");

				theLinkNode.innerHTML = theCode;
				theLinkNode.href = theHref;
				theLinkNode.title = theTitle;

				theRange.insertNode(theLinkNode);

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


function addFckPopupJS() {

	$res="";

	$res.="<script type=\"text/javascript\"><!--\n";

	// ---------------------------------------------------------------------------

	$res.="function insItem() {\n";


	$type=getPopupSelType();

	switch ($type) {

		case "link": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var url = GetE('url').value;\n";
			$res.="var title = GetE('title').value;\n";
			$res.="var code = GetE('code').value;\n";

			$res.="// Get the selected item (if available).\n";
			$res.="var oItem = FCK.Selection.GetSelectedElement() ;\n";

			$res.="var bHasItem = ( oItem != null ) ;\n";

			$res.="if ( bHasItem ) {\n";
			// $res.="FCK.Selection.Delete() ;\n";
			$res.="}\n";

			$res.="oItem = FCK.CreateElement( 'A' ) ;\n";

			$res.="oItem.href=url ;\n";
			$res.="oItem.innerHTML=code ;\n";
			$res.="oItem.title=title ;\n";
			//$res.="oItem.alt=altTxt ;\n";
		} break;

		case "wiki_new": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var title = GetE('title').value;\n";

			//$res.="// Get the selected item (if available).\n";
			//$res.="var oItem = FCK.Selection.GetSelectedElement() ;\n";

			$res.="FCK.InsertHtml('[['+title+']]');\n";
			$res.="FCK.OnAfterSetHTML() ;\n";
		} break;


		case "wiki_sel": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.="var FCKLang		= oEditor.FCKLang ;\n";
			$res.="var FCKConfig	= oEditor.FCKConfig ;\n";

			$res.="var title = GetE('title').value;\n";
			$res.="var page_code = GetE('page_code').value;\n";

			//$res.="// Get the selected item (if available).\n";
			//$res.="var oItem = FCK.Selection.GetSelectedElement() ;\n";

			$res.="FCK.InsertHtml('[['+page_code+'|'+title+']]');\n";
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


function addXinhaLinkPopupAfterJS() {
	$res ="";

	$type=getPopupSelType();

	switch ($type) {

		case "link": {
			$res.='var code_field = GetE("code");'."\n";
			$res.='var url_field = GetE("url");'."\n";
			$res.='var title_field = GetE("title");'."\n";
			$res.="var text = window.opener.old_text;\n";
			$res.="var attrib = get_link_attrib(text);\n";
			$res.="\n";
			$res.=<<<JS_END
				if (attrib != false) {
					var attrib_arr = parse_attrib_string(attrib);
				}
				else {
					var attrib_arr = new Array();
				}
				if (attrib_arr.href != null) {
					var link_content = get_link_content(text);
					code_field.value = link_content;
					url_field.value = attrib_arr.href;
					if (attrib_arr.title != null) {
						title_field.value = attrib_arr.title;
					}
				}
				else {
					code_field.value = text;
				}
JS_END;
			$res.="\n";
		} break;

	}

	return $res;
}


function addFckLinkPopupAfterJS() {
	$res ="";

	$type=getPopupSelType();

	switch ($type) {

		case "link": {
			$res.="dialogArguments = window.opener.FCKLastDialogInfo ;\n";
			$res.="var oEditor		= dialogArguments.Editor ;\n";
			$res.="var FCK			= oEditor.FCK ;\n";
			$res.='var code_field = GetE("code");'."\n";
			$res.='var url_field = GetE("url");'."\n";
			$res.='var title_field = GetE("title");'."\n";
			$res.=<<<JS_END
				browser = get_browser_obj();
				if (browser.IE) {
					var oRange = FCK.EditorDocument.selection.createRange() ;
					text = oRange.text;
				}
				else {
					var text = FCK.EditorWindow.getSelection();
				}

				var oLink = FCK.Selection.MoveToAncestorNode( 'A' ) ;
				if ( oLink ) {
					FCK.Selection.SelectNode( oLink ) ;

					var sHRef = oLink.getAttribute( '_fcksavedurl' ) ;
						if ( sHRef == null )
							sHRef = oLink.getAttribute( 'href' , 2 ) + '' ;

					var sTitle = oLink.getAttribute( 'title' ) ;

					if ( sHRef != null ) url_field.value = sHRef;
					if ( sTitle != null ) title_field.value = sTitle;
				}
				code_field.value = text;
JS_END;
			$res.="\n";
		} break;

	}

	return $res;
}


function addWidgLinkPopupAfterJS() {
	$res ="";

	$type=getPopupSelType();

	$res.="var theToolbar 		= window.opener.widg.theToolbar;\n";
	$res.="var theWidgEditor 	= window.opener.widg.theWidgEditor;\n";
	$res.="var theIframe 		= window.opener.widg.theIframe;\n";
	$res.='var code_field = GetE("code");'."\n";
	$res.='var url_field = GetE("url");'."\n";
	$res.='var title_field = GetE("title");'."\n";

	switch ($type) {

		case "link": {
			$res.=<<<JS_END
				browser = get_browser_obj();
				if (browser.IE) {
					text = theIframe.contentWindow.document.selection;
				}
				else {
					var text = theIframe.contentWindow.getSelection();
				}

				code_field.value = text;
				url_field.value = text.anchorNode.parentNode.getAttribute('href');
				title_field.value = text.anchorNode.parentNode.getAttribute('title');
JS_END;
			$res.="\n";
		} break;

	}

	return $res;
}


function getPopupSelType() {
	$res="link";

	if ((isset($_GET["op"])) && (!empty($_GET["op"]))) {
		switch($_GET["op"]) {

			case "main": {
				$res ="link";
			} break;

			case "wiki_new": {
				$res ="wiki_new";
			} break;

			case "wiki_sel": {
				$res ="wiki_sel";
			} break;

		}
	}

	return $res;
}


function isEditorInWiki() {

	$wiki_id=getEditorWikiId();

	$res=($wiki_id > 0 ? TRUE : FALSE);

	return $res;
}


function getEditorWikiId() {
	$wiki_id=0;

	if (isset($_SESSION["editor_in_wiki"]))
		$wiki_id=$_SESSION["editor_in_wiki"];

	if (isset($_GET["wiki_id"]))
		$wiki_id=$_GET["wiki_id"];

	return $wiki_id;
}


?>
