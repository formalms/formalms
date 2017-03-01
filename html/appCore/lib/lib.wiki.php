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

Class CoreWikiAdmin {

	var $lang=NULL;
	var $um=NULL;
	var	$table_style=FALSE;

	var $source_platform=NULL;
	var $wikiManager=NULL;


	function CoreWikiAdmin($source_platform) {
		$this->lang =& DoceboLanguage::createInstance('wiki', "framework");
		$this->source_platform =$source_platform;
		$this->wikiManager=new CoreWikiManager();
	}


	function getTableStyle() {
		return $this->table_style;
	}


	function setTableStyle($style) {
		$this->table_style=$style;
	}


	function getSourcePlatform() {
		return $this->source_platform;
	}

	function titleArea($text, $image = '', $alt_image = '') {
		$res="";

		$res=getTitleArea($text, $image = '', $alt_image = '');

		return $res;
	}


	function getHead() {
		$res="";
		$res.="<div class=\"std_block\">\n";
		return $res;
	}


	function getFooter() {
		$res="";
		$res.="</div>\n";
		return $res;
	}


	function backUi($url=FALSE) {
		$res="";
		$um=& UrlManager::getInstance();

		if ($url === FALSE)
			$url=$um->getUrl();

		$res.=getBackUi($url, $this->lang->def( '_BACK' ));
		return $res;
	}


	function urlManagerSetup($std_query) {
		require_once(_base_.'/lib/lib.urlmanager.php');

		$um=& UrlManager::getInstance();

		$um->setStdQuery($std_query);
	}


	function getWikiListTable($vis_item, $view_link=FALSE) {
		$res="";
		require_once(_base_.'/lib/lib.table.php');

		$table_caption=$this->lang->def("_WIKI");
		$table_summary=$this->lang->def("_TABLE_WIKI_SUM");

		$um=& UrlManager::getInstance();
		$tab=new Table($vis_item, $table_caption, $table_summary);

		if ($this->getTableStyle() !== FALSE)
			$tab->setTableStyle($this->getTableStyle());

		$head=array($this->lang->def("_TITLE"));


/*		$img ="<img src=\"".getPathImage('fw')."standard/export.gif\" alt=\"".$this->lang->def("_EXPORT")."\" ";
		$img.="title=\"".$this->lang->def("_EXPORT")."\" />";
		$head[]=$img; */
		$img ="<img src=\"".getPathImage('fw')."standard/moduser.png\" alt=\"".$this->lang->def("_ALT_SETPERM")."\" ";
		$img.="title=\"".$this->lang->def("_ALT_SETPERM")."\" />";
		$head[]=$img;

		if ($view_link !== FALSE) {
			$img ="<img src=\"".getPathImage('fw')."standard/modelem.png\" alt=\"".$this->lang->def("_ALT_MODITEMS")."\" ";
			$img.="title=\"".$this->lang->def("_ALT_MODITEMS")."\" />";
			$head[]=$img;
		}
		$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
		$img.="title=\"".$this->lang->def("_MOD")."\" />";
		$head[]=$img;
		$img ="<img src=\"".getPathImage('fw')."standard/delete.png\" alt=\"".$this->lang->def("_DEL")."\" ";
		$img.="title=\"".$this->lang->def("_DEL")."\" />";
		$head[]=$img;

		$head_type=array("", "image", "image", "image", "image");
		if ($view_link !== FALSE) {
			$head_type[]="image";
		}

		$tab->setColsStyle($head_type);
		$tab->addHead($head);

		$tab->initNavBar('ini', 'link');
		$tab->setLink($um->getUrl());

		$ini=$tab->getSelectedElement();

		$source_platform =$this->getSourcePlatform();
		$data_info=$this->wikiManager->getWikiList($ini, $vis_item, FALSE, $source_platform);
		$data_arr=$data_info["data_arr"];
		$db_tot=$data_info["data_tot"];

		$tot=count($data_arr);
		for($i=0; $i<$tot; $i++ ) {

			$id=$data_arr[$i]["wiki_id"];

			$rowcnt=array();
			$rowcnt[]=$data_arr[$i]["title"];


/*			$img ="<img src=\"".getPathImage('fw')."standard/export.gif\" alt=\"".$this->lang->def("_EXPORT")."\" ";
			$img.="title=\"".$this->lang->def("_EXPORT")."\" />";
			$url=$um->getUrl("op=exportcat&catid=".$id);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n"; */

			$img ="<img src=\"".getPathImage('fw')."standard/moduser.png\" alt=\"".$this->lang->def("_ALT_SETPERM")."\" ";
			$img.="title=\"".$this->lang->def("_ALT_SETPERM")."\" />";
			$url=$um->getUrl("op=setperm&wiki_id=".$id);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			if ($view_link !== FALSE) {
				$img ="<img src=\"".getPathImage('fw')."standard/modelem.png\" alt=\"".$this->lang->def("_ALT_MODITEMS")."\" ";
				$img.="title=\"".$this->lang->def("_ALT_MODITEMS")."\" />";
				$url=$um->getUrl("op=showcat&wiki_id=".$id);
				$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";
			}

			$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
			$img.="title=\"".$this->lang->def("_MOD")."\" />";
			$url=$um->getUrl("op=editwiki&wiki_id=".$id);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			$img ="<img src=\"".getPathImage('fw')."standard/delete.png\" alt=\"".$this->lang->def("_DEL")."\" ";
			$img.="title=\"".$this->lang->def("_DEL")."\" />";
			$url=$um->getUrl("op=delwiki&wiki_id=".$id."&conf_del=1");
			$rowcnt[]="<a href=\"".$url."\" title=\"".$this->lang->def('_DEL')." : ".$data_arr[$i]["title"]."\">".$img."</a>\n";

			$tab->addBody($rowcnt);
		}

		$url=$um->getUrl("op=addwiki");
		$add_box ="<a class=\"new_element_link_float\" href=\"".$url."\">".$this->lang->def('_ADD')."</a>\n";
		$tab->addActionAdd($add_box);

		$res =$tab->getTable();
		if ($tot > 0) {
			$res.=$tab->getNavBar($ini, $db_tot);
		}

		return $res;
	}


	function addeditWiki($id=0) {
		$res="";
		require_once(_base_."/lib/lib.form.php");

		$form=new Form();
		$form_code="";

		$um=& UrlManager::getInstance();
		$url=$um->getUrl("op=savewiki");

		$lang_arr=$this->wikiManager->getLanguageArr();

		if ($id == 0) {
			$todo="add";
			$form_code=$form->openForm("main_form", $url);
			$submit_lbl=$this->lang->def("_INSERT");

			$public =FALSE;
			$title="";
			$description="";
			$sel_lang=getLanguage();
			$other_lang=array();
		}
		else if ($id > 0) {
			$todo="edit";
			$form_code=$form->openForm("main_form", $url);
			$submit_lbl=$this->lang->def("_SAVE");

			$info=$this->wikiManager->getWikiInfo($id);

			$public=($info["public"] == 1 ? TRUE : FALSE);
			$title=$info["title"];
			$description=$info["description"];
			if (!empty($info["language"])) {
				$sel_lang=$info["language"];
			}
			else {
				$sel_lang=FALSE;
			}

			if (!empty($info["other_lang"])) {
				$other_lang=explode(",", $info["other_lang"]);
			}
			else {
				$other_lang=array();
			}
		}


		$res.=$form_code;
		$res.=$form->openElementSpace();

		$res.=$form->getTextfield($this->lang->def("_TITLE"), "title", "title", 255, $title);
		$res.=$form->getSimpleTextarea($this->lang->def("_DESCRIPTION"), "description", "description", $description);
		
		if(Get::cur_plat() !== 'framework')
			$res.=$form->getHidden("public", "public", 1);

		$res.=$form->getHidden("id", "id", $id);

		if ($todo == "add") {
			$res.=$form->getDropdown($this->lang->def("_WIKI_LANGUAGE"), "language", "language", $lang_arr, $sel_lang);
		}
		else if ($todo == "edit") {
			$res.=$form->getLineBox($this->lang->def("_WIKI_LANGUAGE").":", ucfirst($sel_lang));
			$res.=$form->getHidden("language", "language", $sel_lang);
			//$form->getDropdown($this->lang->def("_WIKI_LANGUAGE"), "language", "language", $lang_arr, $sel_lang);
		}

		$res.=$form->getOpenCombo($this->lang->def('_WIKI_OTHER_LANGUAGES'));

		foreach($lang_arr as $lang_code=>$label) {
			$to_show=TRUE;
			$name="other_lang[".$lang_code."]";
			$id="other_lang_".$lang_code;

			if (($todo == "edit") && ($lang_code == $sel_lang)) {
				$to_show=FALSE;
			}

			$checked=(in_array($lang_code, $other_lang) ? TRUE : FALSE);
			if ($to_show) {
				$res.=$form->getCheckBox($label, $id, $name, $lang_code, $checked);
			}
		}

		$res.=$form->getCloseCombo();

		$res.=$form->closeElementSpace();
		$res.=$form->openButtonSpace();
		$res.=$form->getButton('save', 'save', $submit_lbl);
		$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();

		return $res;
	}


	function saveWiki() {
		$um=& UrlManager::getInstance();

		$source_platform=$this->getSourcePlatform();
		$wiki_id=$this->wikiManager->saveWiki($_POST, $source_platform);

		$url=$um->getUrl();
		Util::jump_to($url);
	}


	function deleteWiki($wiki_id) {
		include_once(_base_."/lib/lib.form.php");

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl();


		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_del"]) || isset($_GET["confirm"])) {

			$this->wikiManager->deleteWiki($wiki_id);

			Util::jump_to($back_url);
		}
		else {

			$res="";
			$info=$this->wikiManager->getWikiInfo($wiki_id);
			$title=$info["title"];

			$form=new Form();

			$url=$um->getUrl("op=delwiki&wiki_id=".$wiki_id);
			$res.=$form->openForm("delete_form", $url);


			$res.=getDeleteUi(
			$this->lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$this->lang->def('_TITLE').' :</span> '.$title.'<br />',
				false,
				'conf_del',
				'undo');

			$res.=$form->closeForm();
			return $res;
		}
	}


	function showWikiPerm($wiki_id) {
		$res=FALSE;
		require_once(_adm_."/lib/lib.simplesel.php");

		$um=& UrlManager::getInstance();
		$ssel=new SimpleSelector(TRUE, $this->lang);

		$perm=array();

		$perm["view"]["img"]=getPathImage('fw')."standard/view.png";
		$perm["view"]["alt"]=$this->lang->def("_VIEW");
		$perm["edit"]["img"]=getPathImage('fw')."standard/edit.png";
		$perm["edit"]["alt"]=$this->lang->def("_MOD");

		$ssel->setPermList($perm);

		$url=$um->getUrl("op=setperm&wiki_id=".$wiki_id);
		$back_url=$um->getUrl("op=doneperm");
		$ssel->setLinks($url, $back_url);

		$op=$ssel->getOp();

		if (($op == "main") || ($op == "manual_init") )
			$saved_data=$this->wikiManager->loadWikiPerm($wiki_id);


		$page_body="";
		$full_page="";

		switch($op) {

			case "main": {
				$ssel->setSavedData($saved_data);
				$res=$ssel->loadSimpleSelector();
			} break;

			case "manual_init":{

				// Saving permissions of simple selector
				$save_info=$ssel->getSaveInfo();
				$this->wikiManager->saveWikiPerm($wiki_id, $save_info["selected"], $save_info["database"]);

				$ssel->setSavedData($saved_data);
				$ssel->loadManualSelector($this->lang->def("_ALT_SETPERM"));
			} break;
			case "manual": {
				$ssel->loadManualSelector($this->lang->def("_ALT_SETPERM"));
			} break;

			case "save_manual": {

				// Saving permissions of manual selector
				$save_info=$ssel->getSaveInfo();
				$this->wikiManager->saveWikiPerm($wiki_id, $save_info["selected"], $save_info["database"]);

				Util::jump_to(str_replace("&amp;", "&", $url));
			} break;

			case "save": {

				// Saving permissions of simple selector
				$save_info=$ssel->getSaveInfo();
				$this->wikiManager->saveWikiPerm($wiki_id, $save_info["selected"], $save_info["database"]);

				Util::jump_to(str_replace("&amp;", "&", $back_url));
			} break;

		}

		return $res;
	}


	function exportCategory($cat_id) {
		$cat_exported=array();


		$info=$this->wikiManager->getCategoryInfo($cat_id);
		$cat_title=$info["title"];

		$doc = new DoceboDOMDocument("1.0");
		$root=$doc->createElement("FAQCATEGORY");
		$doc->appendChild($root);

		$elem=$doc->createElement("DATE");
		$elemText=$doc->createTextNode(date("Y-m-d H:i:s"));
		$elem->appendChild($elemText);
		$root->appendChild($elem);

		$elem=$doc->createElement("TITLE");
		$elemText=$doc->createTextNode(urlencode($info["title"]));
		$elem->appendChild($elemText);
		$root->appendChild($elem);

		$elem=$doc->createElement("DESCRIPTION");
		$elemText=$doc->createTextNode(urlencode($info["description"]));
		$elem->appendChild($elemText);
		$root->appendChild($elem);

/*		$elem=$doc->createElement("AUTHOR");
		$elemText=$doc->createTextNode($info["author"]);
		$elem->appendChild($elemText);
		$root->appendChild($elem); */

		$items=$doc->createElement("CATEGORYITEMS");
		$root->appendChild($items);

		$data_info=$this->wikiManager->getCategoryItems($cat_id);
		$data_arr=$data_info["data_arr"];

		$tot=count($data_arr);
		for($i=0; $i<$tot; $i++ ) {

/*			$id=$data_arr[$i]["faq_id"];

			$elem=$doc->createElement("faq_id");
			$elemText=$doc->createTextNode($id);
			$elem->appendChild($elemText);
			$elem->setAttribute("id", $id);
			$items->appendChild($elem);
*/

			$id=$data_arr[$i]["faq_id"];

			$faq=$doc->createElement("faq");
			$faq->setAttribute("id", $id);
			$items->appendChild($faq);

			$elem=$doc->createElement("title");
			$elemText=$doc->createTextNode(urlencode($data_arr[$i]["title"]));
			$elem->appendChild($elemText);
			$faq->appendChild($elem);

			$elem=$doc->createElement("question");
			$elemText=$doc->createTextNode(urlencode($data_arr[$i]["question"]));
			$elem->appendChild($elemText);
			$faq->appendChild($elem);

			$elem=$doc->createElement("keyword");
			$elemText=$doc->createTextNode(urlencode($data_arr[$i]["keyword"]));
			$elem->appendChild($elemText);
			$faq->appendChild($elem);

			$elem=$doc->createElement("answer");
			$elemText=$doc->createTextNode(urlencode($data_arr[$i]["answer"]));
			$elem->appendChild($elemText);
			$faq->appendChild($elem);

		}

		$out=$doc->saveXML();

		$title=rawurlencode(str_replace(" ", "", $cat_title));
		$date=date("Ymd");
		$domain=preg_replace("/www/i", "", $_SERVER["SERVER_NAME"]);
		$domain=str_replace(".", "", $domain);
		$filename="faq_".$date."_".$title."_".$domain;
		$filename=substr($filename, 0, 200);

		//-- Debug: --//
		// echo $filename."<br /><br /><textarea rows=\"20\" cols=\"80\">".$out."</textarea>"; die();

		ob_end_clean();
		//Download file
		//send file length info
		header('Content-Length:'. strlen($out));
		//content type forcing dowlad
		header("Content-type: application/download\n");
		//cache control
		header("Cache-control: private");
		//sending creation time
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		//content type
		header('Content-Disposition: attachment; filename="'.$filename.'.xml"');
		//sending file
		echo $out;
		//and now exit
		exit();
	}


	function importCategory() {
		include_once(_base_."/lib/lib.form.php");

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl();


		if (isset($_POST["undo"])) { // -------------------------- Cancel ------- |
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_import"])) { // -------------- Import ------- |

			$tmp_fname=$_FILES["file"]["tmp_name"];
			$import_arr=$this->wikiManager->getImportArrFromXml($tmp_fname);

			$this->wikiManager->importNewCategory($import_arr);

			// TODO: add import into category if cat_id > 0

			Util::jump_to($back_url);
		}
		else { // ------------------------------------------------ Import Form -- |

			$res="";
			$form=new Form();

			$url="";
			$res.=$form->openForm("import_form", $url, FALSE, FALSE, "multipart/form-data");
			$res.=$form->openElementSpace();

			$res.=$form->getFilefield($this->lang->def("_FILE"), "file", "file");

			$res.=$form->closeElementSpace();
			$res.=$form->openButtonSpace();
			$res.=$form->getButton('conf_import', 'conf_import', $this->lang->def('_IMPORT'));
			$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
			$res.=$form->closeButtonSpace();
			$res.=$form->closeForm();

			return $res;
		}
	}


	function showCatItems($cat_id, $vis_item) {
		$res="";
		require_once(_base_.'/lib/lib.table.php');

		$table_caption=$this->lang->def("_TABLE_FAQ_CAP");
		$table_summary=$this->lang->def("_TABLE_FAQ_SUM");

		$um=& UrlManager::getInstance();
		$tab=new Table($vis_item, $table_caption, $table_summary);

		if ($this->getTableStyle() !== FALSE)
			$tab->setTableStyle($this->getTableStyle());

		$head=array($this->lang->def("_TITLE"));


		$img ="<img src=\"".getPathImage('fw')."standard/down.png\" alt=\"".$this->lang->def("_MOVE_DOWN")."\" ";
		$img.="title=\"".$this->lang->def("_MOVE_DOWN")."\" />";
		$head[]=$img;
		$img ="<img src=\"".getPathImage('fw')."standard/up.png\" alt=\"".$this->lang->def("_MOVE_UP")."\" ";
		$img.="title=\"".$this->lang->def("_MOVE_UP")."\" />";
		$head[]=$img;
		$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
		$img.="title=\"".$this->lang->def("_MOD")."\" />";
		$head[]=$img;
		$img ="<img src=\"".getPathImage('fw')."standard/delete.png\" alt=\"".$this->lang->def("_DEL")."\" ";
		$img.="title=\"".$this->lang->def("_DEL")."\" />";
		$head[]=$img;

		$head_type=array("", "image", "image", "image", "image");

		$tab->setColsStyle($head_type);
		$tab->addHead($head);

		$tab->initNavBar('ini', 'link');
		$tab->setLink($um->getUrl());

		$ini=$tab->getSelectedElement();


		$data_info=$this->wikiManager->getCategoryItems($cat_id, $ini, $vis_item);
		$data_arr=$data_info["data_arr"];
		$db_tot=$data_info["data_tot"];

		$tot=count($data_arr);
		for($i=0; $i<$tot; $i++ ) {

			$id=$data_arr[$i]["faq_id"];

			$rowcnt=array();
			$rowcnt[]=$data_arr[$i]["title"];


			if ($ini+$i < $db_tot-1) {
				$img ="<img src=\"".getPathImage('fw')."standard/down.png\" alt=\"".$this->lang->def("_MOVE_DOWN")."\" ";
				$img.="title=\"".$this->lang->def("_MOVE_DOWN")."\" />";
				$url=$um->getUrl("op=movefaqdown&catid=".$cat_id."&faqid=".$id);
				$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";
			}
			else {
				$rowcnt[]="&nbsp;";
			}

			if ($ini+$i > 0) {
				$img ="<img src=\"".getPathImage('fw')."standard/up.png\" alt=\"".$this->lang->def("_MOVE_UP")."\" ";
				$img.="title=\"".$this->lang->def("_MOVE_UP")."\" />";
				$url=$um->getUrl("op=movefaqup&catid=".$cat_id."&faqid=".$id);
				$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";
			}
			else {
				$rowcnt[]="&nbsp;";
			}

			$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
			$img.="title=\"".$this->lang->def("_MOD")."\" />";
			$url=$um->getUrl("op=editfaq&catid=".$cat_id."&faqid=".$id);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			$img ="<img src=\"".getPathImage('fw')."standard/delete.png\" alt=\"".$this->lang->def("_DEL")."\" ";
			$img.="title=\"".$this->lang->def("_DEL")."\" />";
			$url=$um->getUrl("op=deletefaq&catid=".$cat_id."&faqid=".$id);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			$tab->addBody($rowcnt);
		}

		$url=$um->getUrl("op=addfaq&catid=".$cat_id);
		$tab->addActionAdd("<a class=\"new_element_link\" href=\"".$url."\">".$this->lang->def('_ADD')."</a>\n");

		$res=$tab->getTable().$tab->getNavBar($ini, $db_tot);

		return $res;
	}


	function addeditFaq($cat_id, $id=0) {
		$res="";
		require_once(_base_."/lib/lib.form.php");

		$form=new Form();
		$form_code="";

		$um=& UrlManager::getInstance();
		$url=$um->getUrl("op=savefaq&catid=".$cat_id);

		if ($id == 0) {
			$form_code=$form->openForm("main_form", $url);
			$submit_lbl=$this->lang->def("_INSERT");

			$title="";
			$question="";
			$keyword="";
			$answer="";
		}
		else if ($id > 0) {
			$form_code=$form->openForm("main_form", $url);
			$submit_lbl=$this->lang->def("_SAVE");

			$info=$this->wikiManager->getFaqInfo($id);

			$title=$info["title"];
			$question=$info["question"];
			$keyword=$info["keyword"];
			$answer=$info["answer"];
		}


		$res.=$form_code;
		$res.=$form->openElementSpace();

		$res.=$form->getTextfield($this->lang->def("_TITLE"), "title", "title", 255, $title);
		$res.=$form->getTextfield($this->lang->def("_QUESTION"), "question", "question", 255, $question);
		$res.=$form->getSimpleTextarea($this->lang->def("_KEYWORDS"), "keyword", "keyword", $keyword);
		$res.=$form->getTextarea($this->lang->def("_ANSWER"), "answer", "answer", $answer);

		$res.=$form->getHidden("cat_id", "cat_id", $cat_id);
		$res.=$form->getHidden("id", "id", $id);


		$res.=$form->closeElementSpace();
		$res.=$form->openButtonSpace();
		$res.=$form->getButton('save', 'save', $submit_lbl);
		$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
		$res.=$form->closeButtonSpace();
		$res.=$form->closeForm();

		return $res;
	}


	function saveFaq($cat_id) {
		$um=& UrlManager::getInstance();

		$faq_id=$this->wikiManager->saveFaq($cat_id, $_POST);

		$url=$um->getUrl("op=showcat&catid=".$cat_id);
		Util::jump_to($url);
	}


	function deleteFaq($cat_id, $faq_id) {
		include_once(_base_."/lib/lib.form.php");

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl("op=showcat&catid=".$cat_id);


		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["conf_del"])) {

			$this->wikiManager->deleteFaq($faq_id);

			Util::jump_to($back_url);
		}
		else {

			$res="";
			$info=$this->wikiManager->getFaqInfo($faq_id);
			$title=$info["title"];

			$form=new Form();

			$url="";
			$res.=$form->openForm("delete_form", $url);

			$res.=$form->getHidden("faq_id", "faq_id", $faq_id);
			$res.=$form->getHidden("cat_id", "cat_id", $cat_id);

			$res.=getDeleteUi(
			$this->lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$this->lang->def('_TITLE').' :</span> '.$title.'<br />',
				false,
				'conf_del',
				'undo');

			$res.=$form->closeForm();
			return $res;
		}
	}


	function moveFaq($cat_id, $faq_id, $direction) {
		$um=& UrlManager::getInstance();

		$this->wikiManager->moveFaq($direction, $faq_id);

		$url=$um->getUrl("op=showcat&catid=".$cat_id);
		Util::jump_to($url);
	}


}






Class CoreWikiPublic {

	var $lang=NULL;
	var $wikiManager=NULL;
	var $wiki_id=0;
	var $wiki_language=FALSE;
	var $internal_perm =array();


	function CoreWikiPublic($wiki_id) {
		$this->wiki_id=(int)$wiki_id;
		$_SESSION["editor_in_wiki"]=$this->getWikiId();

		$this->lang =& DoceboLanguage::createInstance("wiki", "framework");
		$this->wikiManager=new CoreWikiManager();

		$this->wikiLangSetup();
	}


	function getTableStyle() {
		return $this->table_style;
	}


	function setTableStyle($style) {
		$this->table_style=$style;
	}


	function setInternalPerm($name, $value) {
		$this->internal_perm[$name] =(bool)$value;
	}


	function getInternalPerm($name) {
		if (!isset($this->internal_perm[$name])) {
			return FALSE;
		}
		else {
			return (bool)$this->internal_perm[$name];
		}
	}


	function titleArea($text, $image = '', $alt_image = '') {
		$res="";

        /*
		if (Get::cur_plat() == 'cms') {
			$res=getCmsTitleArea($text, $image = '', $alt_image = '');
		}
		else {
			$res=getTitleArea($text, $image = '', $alt_image = '');
		}
        */
        
        $res=getTitleArea($text, $image = '', $alt_image = '');
        
		return $res;
	}


	function getHead($toolbar=TRUE, $lang_flags=TRUE, $nav_bar=FALSE, $search_box=FALSE, $can_view=FALSE) {
		$res="";
		$res.="<div class=\"std_block\">\n";
		$res.=($search_box ? $this->getWikiSearchBox() : "");
		$res.=($toolbar ? $this->getWikiToolbar($can_view) : "");
		$res.='<div class="wiki_lang_flags">';
		$res.=($lang_flags ? $this->getWikiLangFlags() : "&nbsp;");
		$res.="</div>\n"; // wiki_lang_flags
		if ($nav_bar) {
			$res.="<div class=\"wiki_navbar wiki_navbar_head\">".$this->getNavigationBar()."</div>\n";
		}
		$res.="<div class=\"wiki_page_content\">\n";
		return $res;
	}


	function getFooter($nav_bar=FALSE) {
		$res="";
		$res.="<div class=\"nofloat\"></div>\n";
		$res.="</div>\n"; // wiki_page_content

		if ($nav_bar) {
			$res.="<div class=\"wiki_navbar wiki_navbar_bottom\">".$this->getNavigationBar()."</div>\n";
		}

		$res.="</div>\n"; // std_block
		return $res;
	}


	function backUi($url=FALSE) {
		$res="";
		$um=& UrlManager::getInstance();

		if ($url === FALSE)
			$url=$um->getUrl();

		$res.=getBackUi($url, $this->lang->def( '_BACK' ));
		return $res;
	}


	function getWikiId() {
		return (int)$this->wiki_id;
	}


	function getOp() {
		$um=& UrlManager::getInstance();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);

		$op=importVar("op");
		if (empty($op))
			$op="show";

		return $op;
	}


	function getPageCode() {
		$um=& UrlManager::getInstance();
		$wiki_id=$this->getWikiId();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);

		if ((isset($_GET["page"])) && (!empty($_GET["page"]))) {
			$page_code=substr($_GET["page"], 0, 255);
		}
		else {
			$page_info=$this->wikiManager->getRootPageInfo($wiki_id);
			$page_code=$page_info["page_code"];
		}

		return $page_code;
	}


	function wikiLangSetup() {
		require_once(_base_.'/lib/lib.urlmanager.php');
		
		$res=0;

		$um=& UrlManager::getInstance();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);


		$load_main=TRUE;
		$lang_info=$this->wikiManager->getWikiLangInfo($this->getWikiId());

		if ((isset($_GET["lang"])) && (!empty($_GET["lang"]))) {
			$res =substr($_GET["lang"], 0, 50);
		}
		else {
			$res =getLanguage();
		}

		// Check for valid language; else the main language will be loaded..
		if (($res == $lang_info["main_language"]) || (in_array($res, $lang_info["other_lang"]))) {
			$load_main=FALSE;
		}

		if (($load_main) && ($this->getWikiId() > 0)) {
			$res=$lang_info["main_language"];
		}

		$um->updateStdQuery("lang", $res);
		$this->setWikiLanguage($res);

		return $res;
	}


	function getWikiLanguage() {
		if ($this->wiki_language === FALSE) {
			$this->wikiLangSetup();
		}

		return $this->wiki_language;
	}


	function setWikiLanguage($lang) {
		$this->wiki_language=$lang;
	}


	function getWikiToolbar($can_view) {
		$res="";

		$um=& UrlManager::getInstance();
		$page_code=$this->getPageCode();
		$op=$this->getOp();

		$res.="<ul class=\"wiki_toolbar\">\n";

		$url_op="show";
		$label=$this->lang->def("_PAGE");
		$img ='<img src="'.getPathImage('fw').'wiki/img_view.png" ';
		$img.='alt="'.$label.'" title="'.$label.'" />';
		if($can_view)
		{
			if ($url_op != $op) {
				$url=$um->getUrl("op=".$url_op."&page=".$page_code);
				$res.="<li class=\"selected\">";
				$res.="<a href=\"".$url."\"><span>";
				$res.=$img.$label."</span></a>";
			}
			else {
				$res.="<li><div><span>";
				$res.=$img.$label."</span></div>";
			}
			$res.="</li>\n";
		}

		if ($this->getInternalPerm("edit")) {
			$url_op="edit";
			$label=$this->lang->def("_MOD");
			$img ='<img src="'.getPathImage('fw').'wiki/img_edit.png" ';
			$img.='alt="'.$label.'" title="'.$label.'" />';
			if ($url_op != $op) {
				$url=$um->getUrl("op=".$url_op."&page=".$page_code);
				$res.="<li class=\"selected\">";
				$res.="<a href=\"".$url."\"><span>";
				$res.=$img.$label."</span></a>";
			}
			else {
				$res.="<li><div><span>";
				$res.=$img.$label."</span></div>";
			}
			$res.="</li>\n";
		}

		if ($this->getInternalPerm("edit"))
		{
			$url_op="map";
			$label=$this->lang->def("_MAP");
			$img ='<img src="'.getPathImage('fw').'wiki/img_map.png" ';
			$img.='alt="'.$label.'" title="'.$label.'" />';
			if ($url_op != $op) {
				$url=$um->getUrl("op=".$url_op."&page=".$page_code);
				$res.="<li class=\"selected\">";
				$res.="<a href=\"".$url."\"><span>";
				$res.=$img.$label."</span></a>";
			}
			else {
				$res.="<li><div><span>";
				$res.=$img.$label."</span></div>";
			}
			$res.="</li>\n";
		}


		if ($this->getInternalPerm("edit")) {
			$url_op="history";
			$label=$this->lang->def("_REVISIONS");
			$img ='<img src="'.getPathImage('fw').'wiki/img_history.png" ';
			$img.='alt="'.$label.'" title="'.$label.'" />';
			if ($url_op != $op) {
				$url=$um->getUrl("op=".$url_op."&page=".$page_code);
				$res.="<li class=\"selected\">";
				$res.="<a href=\"".$url."\"><span>";
				$res.=$img.$label."</span></a>";
			}
			else {
				$res.="<li><div><span>";
				$res.=$img.$label."</span></div>";
			}
			$res.="</li>\n";
		}

		$res.="</ul>\n";

		return $res;
	}


	function getWikiLangFlags() {
		$res="";
		// TODO: what if user would like to customize html code of this and toolbar?
		// let's think about it for v4.

		$um=& UrlManager::getInstance();
		$wiki_id=$this->getWikiId();
		$lang_info=$this->wikiManager->getWikiLangInfo($wiki_id);
		$page_code=$this->getPageCode();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);

		$op=$this->getOp();

		$res.='<ul class="wiki_lang_flags">';
		$lang_code=$lang_info["main_language"];
		$img ='<img src="'.getPathImage('fw')."language/".$lang_code.'.png" ';
		$img.='alt="'.ucfirst($lang_code).'" title="'.ucfirst($lang_code).'" />';
		if ($this->getWikiLanguage() == $lang_code) {
			$res.='<li class="selected">'.$img."</li>\n";
		}
		else {
			$url=$um->getUrl("op=".$op."&lang=".$lang_code."&page=".$page_code);
			$res.='<li><a href="'.$url.'">'.$img."</a></li>\n";
		}

		foreach($lang_info["other_lang"] as $lang_code) {
			$img ='<img src="'.getPathImage('fw')."language/".$lang_code.'.png" ';
			$img.='alt="'.ucfirst($lang_code).'" title="'.ucfirst($lang_code).'" />';
			if ($this->getWikiLanguage() == $lang_code) {
				$res.='<li class="selected">'.$img."</li>\n";
			}
			else {
				$url=$um->getUrl("op=".$op."&lang=".$lang_code."&page=".$page_code);
				$res.='<li><a href="'.$url.'">'.$img."</a></li>\n";
			}
		}
		$res.="</ul>";

		return $res;
	}


	function getNavigationBar() {
		$res="";

		$um=& UrlManager::getInstance();
		$lang=$this->getWikiLanguage();
		$page_code=$this->getPageCode();
		$wiki_id=$this->getWikiId();
		$history_name="wiki_".$this->getWikiId()."_history";

		$key=$page_code."-".$lang;

		if ((!isset($_SESSION[$history_name])) || (!is_array($_SESSION[$history_name]))) {
			$_SESSION[$history_name]=array();
		}

		if (!in_array($key, $_SESSION[$history_name])) {
			$arr=array();
			$arr["language"]=$lang;
			$arr["page_code"]=$page_code;
			$page_info=$this->wikiManager->getPageInfo($wiki_id, $lang, $page_code);
			$arr["page_title"]=$page_info["title"];
			$_SESSION[$history_name][$key]=$arr;
		}


		if (count($_SESSION[$history_name]) > 10) {
			array_shift($_SESSION[$history_name]);
		}


		$i=1;
		$res.="<ul class=\"wiki_history\">";
		$res.='<li class="label">';
		$res.=$this->lang->def("_HISTORY").":</li>";
		foreach($_SESSION[$history_name] as $val) {

			if (($val["page_code"] != $page_code) || ($val["language"] != $lang)) {
				$res.='<li>';
				$url=$um->getUrl("lang=".$val["language"]."&page=".$val["page_code"]);
				$title =(!empty($val["page_title"]) ? $val["page_title"] : $val["page_code"]);
				$res.='<a href="'.$url.'" title="'.$title.'">'.$i."</a>";
			}
			else {
				$res.='<li class="selected"><div>';
				$res.=$i."</div>";
			}
			$res.="</li>";

			$i++;
		}
		$res.="</ul>\n";

		// ----------------------------------------------------------------------- //

		$nav_link=$this->wikiManager->getWikiNavLinks($wiki_id, $page_code);

		$res.='<ul class="wiki_nav_box">';

		$label=$this->lang->def("_WIKI_HOME");
		$title=$this->lang->def("_HOME");
		$img ='<img src="'.getPathImage('fw').'wiki/home.png" ';
		$img.='alt="'.$title.'" title="'.$title.'" />';
		$res.="<li>\n";
		if ($nav_link["up"] !== FALSE) {
			$url=$um->getUrl("page=".$nav_link["home"]["page_code"]);
			$res.='<a href="'.$url.'"><span>'.$img.$label.'</span></a>';
		}
		else {
			$res.='<div><span>'.$img.$label.'</span></div>';
		}
		$res.="</li>\n";

		$label=$this->lang->def("_WIKI_PREV");
		$title=$this->lang->def("_PREV");
		$img ='<img src="'.getPathImage('fw').'wiki/prev.png" ';
		$img.='alt="'.$title.'" title="'.$title.'" />';
		$res.="<li>\n";
		if ($nav_link["prev"] !== FALSE) {
			$url=$um->getUrl("page=".$nav_link["prev"]["page_code"]);
			$res.='<a href="'.$url.'"><span>'.$img.$label.'</span></a>';
		}
		else {
			$res.='<div><span>'.$img.$label.'</span></div>';
		}
		$res.="</li>\n";

		$label=$this->lang->def("_WIKI_LEVEL_UP");
		$title=$this->lang->def("_LEVEL_UP");
		$img ='<img src="'.getPathImage('fw').'wiki/up.png" ';
		$img.='alt="'.$title.'" title="'.$title.'" />';
		$res.="<li>\n";
		if ($nav_link["up"] !== FALSE) {
			$url=$um->getUrl("page=".$nav_link["up"]["page_code"]);
			$res.='<a href="'.$url.'"><span>'.$img.$label.'</span></a>';
		}
		else {
			$res.='<div><span>'.$img.$label.'</span></div>';
		}
		$res.="</li>\n";

		$label=$this->lang->def("_NEXT");
		$title=$this->lang->def("_NEXT");
		$img ='<img src="'.getPathImage('fw').'wiki/next.png" ';
		$img.='alt="'.$title.'" title="'.$title.'" />';
		$res.="<li>\n";
		if ($nav_link["next"] !== FALSE) {
			$url=$um->getUrl("page=".$nav_link["next"]["page_code"]);
			$res.='<a href="'.$url.'"><span>'.$img.$label.'</span></a>';
		}
		else {
			$res.='<div><span>'.$label.$img.'</span></div>';
		}
		$res.="</li>\n";

		$res.="</ul>\n"; // wiki_nav_box

		return $res;
	}


	function getWikiSearchBox() {
		$res="";

		require_once(_base_."/lib/lib.form.php");

		$form=new Form();
		$um=& UrlManager::getInstance();
		$page_code=$this->getPageCode();

		$res.='<div class="wiki_search_box">';

		$search_txt=(isset($_POST["search_txt"]) ? $_POST["search_txt"] : "");

		$url=$um->getUrl("op=search&page=".$page_code);
		$res.=$form->openForm('wiki_search', $url);
		$res.='<div class="wiki_search_line form_line_l">';
		$res.='<label for="search_txt">'.$this->lang->def('_SEARCH').':</label> ';
		$res.=$form->getInputTextfield('textfield_nowh', 'search_txt', 'search_txt',
		      $search_txt, $this->lang->def('_SEARCH'), 255, '' );

		$res.='<input class="button_nowh" type="submit" id="search_button" name="search_button" value="'.$this->lang->def('_SEARCH').'" />';
		$res.='</div>';
		$res.=$form->closeForm();

		$res.="</div>\n";
		return $res;
	}


	function checkWikiPerm($wiki_id, $perm, $return_res=FALSE) {
		$res=FALSE;

		$user=& $GLOBALS['current_user'];
		$acl=new DoceboACL();
		$role_id="/framework/wiki/".$wiki_id."/".$perm;

		if (($role_id != "") && ($acl->getRoleST($role_id) != FALSE))
			$res=$user->matchUserRole($role_id);

		if ($return_res) {
			return $res;
		}
		else if (!$res)
			die("You can't access!");
	}


	function setPageTempInfo($wiki_id, $page_code) {
		$um=& UrlManager::getInstance();
		$wiki_id=$this->getWikiId();
		$wiki_lang=$this->getWikiLanguage();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);

		if ((isset($_GET["parent"])) && (!empty($_GET["parent"]))) {
			$_SESSION["wiki_temp_info"][$wiki_id][$page_code]["parent_code"]=$_GET["parent"];
			$parent_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $_GET["parent"]);
			$_SESSION["wiki_temp_info"][$wiki_id][$page_code]["parent_info"]=$parent_info;
		}

		if ((isset($_GET["title"])) && (!empty($_GET["title"]))) {
			$title=rawurldecode($_GET["title"]);
			$_SESSION["wiki_temp_info"][$wiki_id][$page_code]["title"]=$title;
		}
	}


	function getPageTempInfo($wiki_id, $page_code) {
		$res=FALSE;
		/* if (isset($_SESSION["wiki_temp_info"][$wiki_id][$page_code]))
			print_r($_SESSION["wiki_temp_info"][$wiki_id][$page_code]); */
		if (isset($_SESSION["wiki_temp_info"][$wiki_id][$page_code]))
			$res=$_SESSION["wiki_temp_info"][$wiki_id][$page_code];

		return $res;
	}


	function unsetPageTempInfo($wiki_id, $page_code) {
		if (isset($_SESSION["wiki_temp_info"][$wiki_id][$page_code]))
			unset($_SESSION["wiki_temp_info"][$wiki_id][$page_code]);
	}


	function getPageContent() {
		require_once($GLOBALS["where_framework"]."/lib/lib.wiki_revision.php");
		$res="";

		$um=& UrlManager::getInstance();
		$page_code=$this->getPageCode();

		if (isset($_GET["mr_str"]))
			$um->loadOtherModRewriteParamFromVar($_GET["mr_str"]);

		$wiki_id=$this->getWikiId();
		$wiki_lang=$this->getWikiLanguage($wiki_id);
		$page_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $page_code);

		if (isset($_GET["tempinfo"]))
			$this->setPageTempInfo($wiki_id, $page_code);


		$page_id=$page_info["page_id"];
		$rev=new WikiRevisionManager(array($wiki_id, $page_id, $wiki_lang));

		if ((isset($_GET["version"])) && ($_GET["version"] > 0)) {
			$version=(int)$_GET["version"];
		}
		else {
			$version=$page_info["version"];
		}

		$res.='<div class="wiki_title">';
		$res.=$page_info["title"];
		$res.="</div>\n";

		$to_load=$rev->getLastRevision();
		$to_load=$rev->getRevision($version);
		$res.=$this->parseWikiLinks($to_load["content"]);

		return $res;
	}


	function editWikiPage() {
		require_once(_base_."/lib/lib.form.php");
		require_once($GLOBALS["where_framework"]."/lib/lib.wiki_revision.php");
		$res="";
		$lang =& DoceboLanguage::createInstance('wiki', 'framework');
		$um=& UrlManager::getInstance();
		$page_code=$this->getPageCode();

		$wiki_id=$this->getWikiId();
		//$root_info=$this->wikiManager->getRootPageInfo($wiki_id);
		//$back_url=$um->getUrl("page=".$root_info["page_code"]);
		$back_url=$um->getUrl("page=".$page_code);

		$wiki_lang=$this->getWikiLanguage();

		if (isset($_POST["undo"])) {
			Util::jump_to($back_url);
		}
		else if (isset($_POST["save"])) {


			$page_temp_info=$this->getPageTempInfo($wiki_id, $page_code);
			$this->wikiManager->savePage($wiki_id, $_POST, $wiki_lang, $page_code, $page_temp_info);

			Util::jump_to($back_url);
		}
		else {
			$page_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $page_code);

			if ($page_info === FALSE) {

				$page_temp_info=$this->getPageTempInfo($wiki_id, $page_code);
				if ($page_temp_info !== FALSE) {
					$title=$page_temp_info["title"];
				}
				else {
					$title=$page_code;
				}

				$content="";
				$page_id=0;
			}
			else {

				$version=$page_info["version"];
				$page_id=$page_info["page_id"];

				$rev=new WikiRevisionManager(array($wiki_id, $page_id, $wiki_lang));
				$last=$rev->getLastRevision();

				$title=$page_info["title"];
				$content=$last["content"];
			}

			$url=$um->getUrl("op=edit&page=".$page_code);

			$form=new Form();
			$res.=$form->openForm("main_form", $url);
			//$res.=$form->openElementSpace();

			$res.=$form->getTextfield($this->lang->def("_TITLE"), "title", "title", 255, $title);
			//$res.=$form->getTextfield($this->lang->def("_QUESTION"), "question", "question", 255, $question);
			//$res.=$form->getSimpleTextarea($this->lang->def("_KEYWORDS"), "keyword", "keyword", $keyword);
			$res.=$form->getTextarea($this->lang->def("_TEXTOF"), "wiki_content", "content", $content);

			$res.=$form->getHidden("page_id", "page_id", $page_id);

			//$res.=$form->closeElementSpace();
			$res.=$form->openButtonSpace();
			$res.=$form->getButton('save', 'save', $this->lang->def('_SAVE'));
			$res.=$form->getButton('undo', 'undo', $this->lang->def('_UNDO'));
			$res.=$form->closeButtonSpace();
			$res.=$form->closeForm();
			$res .= '<p>'.$lang->def('_GUIDE').'</p>';
			$res.=getInfoUi($this->lang->def('_WIKI_INSTRUCTION'));
		}

		return $res;
	}


	function wikiMap() {
		require_once(_base_."/lib/lib.form.php");
		$res="";

		$um=& UrlManager::getInstance();
		$wiki_id=$this->getWikiId();
		$page_code=$this->getPageCode();
		$wiki_lang=$this->getWikiLanguage();

		$url=$um->getUrl("op=map&page=".$page_code);

		$form=new Form();
		$res.=$form->openForm("main_form", $url);

		$wiki_page_db=new TreeDb_WikiDb($this->wikiManager->getWikiPageTable(), $this->wikiManager->getWikiPageInfoTable(),  $wiki_id, $wiki_lang);
		$treeView=new TreeView_WikiView($wiki_page_db, 'wiki_tree');

		$treeView->parsePositionData($_POST, $_POST, $_POST);
		$folder_id=$treeView->getSelectedFolderId();
		$folder_name=$treeView->getFolderPrintName($wiki_page_db->getFolderById($folder_id));

		$res.=$treeView->autoLoad();

		return $res;
	}


	function wikiPageHistory($vis_item=FALSE) {
		require_once(_base_.'/lib/lib.table.php');
		require_once($GLOBALS["where_framework"]."/lib/lib.wiki_revision.php");
		require_once(_base_."/lib/lib.form.php");
		$res="";

		$wiki_id=$this->getWikiId();
		$page_code=$this->getPageCode();
		$wiki_lang=$this->getWikiLanguage();
		$page_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $page_code);

		$wiki_lang=$this->getWikiLanguage();

		$form=new Form();
		$rev=new WikiRevisionManager(array($wiki_id, $page_info["page_id"], $wiki_lang));

		if ($vis_item === FALSE)
			$vis_item=Get::sett('visuItem');

		$table_caption=$this->lang->def("_HISTORY");
		$table_summary=$this->lang->def("_HISTORY");

		$um=& UrlManager::getInstance();
		$tab=new Table($vis_item, $table_caption, $table_summary);


		$head=array($this->lang->def("_VERSION"));

		$head[]=$this->lang->def("_AUTHOR");
		$head[]=$this->lang->def("_DATE");

		$img ="<img src=\"".getPathImage('fw')."wiki/show.png\" alt=\"".$this->lang->def("_ALT_VIEW_REVISION")."\" ";
		$img.="title=\"".$this->lang->def("_ALT_VIEW_REVISION")."\" />";
		$head[]="&nbsp;"; //$img;

		$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
		$img.="title=\"".$this->lang->def("_MOD")."\" />";
		$head[]="&nbsp;"; //$img;

		$head_type=array("", "", "", "image", "image");

		$tab->setColsStyle($head_type);
		$tab->addHead($head);

		$tab->initNavBar('ini', 'link');
		$tab->setLink($um->getUrl());

		$ini=$tab->getSelectedElement();

		$data_info=$rev->getRevisionList($ini, $vis_item);
		$data_arr=$data_info["data_arr"];

		$tot=count($data_arr);

		if ($tot != 0)
			$user_arr=$data_info["user"];

		$db_tot=$data_info["data_tot"];

		for($i=0; $i<$tot; $i++ ) {

			$rowcnt=array();

			$version=$data_arr[$i]["version"];
			//rc// $rowcnt[]=$form->getRadio("", "previous_ver_".$version, "previous_ver", $version);
			//rc// $rowcnt[]=$form->getRadio("", "current_ver_".$version, "current_ver", $version);;

			$rowcnt[]=$version;

			$user_idst=$data_arr[$i]["author"];
			$rowcnt[]=$user_arr[$user_idst];
			$rowcnt[]=Format::date($data_arr[$i]["rev_date"]);

			$img ="<img src=\"".getPathImage('fw')."wiki/show.png\" alt=\"".$this->lang->def("_ALT_VIEW_REVISION")."\" ";
			$img.="title=\"".$this->lang->def("_ALT_VIEW_REVISION")."\" />";
			$url=$um->getUrl("page=".$page_code."&version=".$version);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			$img ="<img src=\"".getPathImage('fw')."standard/edit.png\" alt=\"".$this->lang->def("_MOD")."\" ";
			$img.="title=\"".$this->lang->def("_MOD")."\" />";
			$url=$um->getUrl("op=edit&page=".$page_code."&version=".$version);
			$rowcnt[]="<a href=\"".$url."\">".$img."</a>\n";

			$tab->addBody($rowcnt);
		}


		$url=$um->getUrl("op=history&page=".$page_code);
		//rc// $res.=$form->openForm("main_form", $url);
		
		$tab->setLink($url);
		
		$res.=$tab->getTable().$tab->getNavBar($ini, $db_tot);

		//rc// $res.=$form->openButtonSpace();
		//rc// $res.=$form->getButton("compare", "compare", $this->lang->def("_COMPARE_SEL_VER"));
		//rc// $res.=$form->closeButtonSpace();
		//rc// $res.=$form->closeForm();

		return $res;
	}


	function getFoundPages() {
		require_once($GLOBALS["where_framework"]."/lib/lib.wiki_revision.php");

		// TODO: set some time limit before a search and the next one
		// TODO: see if this function can be optimized

		$res="";

		$wiki_id=$this->getWikiId();
		$page_code=$this->getPageCode();
		$wiki_lang=$this->getWikiLanguage();
		$page_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $page_code);

		$wiki_lang=$this->getWikiLanguage();

		$rev=new WikiRevisionManager(array($wiki_id, $page_info["page_id"], $wiki_lang));

		$search_txt=(isset($_POST["search_txt"]) ? $_POST["search_txt"] : "");

		if ((!empty($search_txt)) && (strlen($search_txt) > 2)) {

			$found=$rev->searchInLatestRevision("page_id", $search_txt);
			$content_found=$found["found"]; // print_r($found["found"]);
			$title_found=$this->wikiManager->searchByTitle($search_txt, $wiki_id, $wiki_lang);
			//echo "title: "; print_r($title_found);

			$found_all =array_unique(array_merge($content_found, $title_found)); //print_r($found_all);

			foreach($found_all as $page_id) {

				$page_info =$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, FALSE, $page_id);

				if (isset($found["cached"][$page_id])) {

					$res.=$this->getSearchResult($search_txt, $found["cached"][$page_id], $page_info);

				}
				else {

					$data =array();
					$rev->setDefaultKeys(array($wiki_id, $page_id, $wiki_lang));
					$data =$rev->getLastRevision();
					$res.=$this->getSearchResult($search_txt, $data, $page_info);

				}

			}

		}
		else {
			$res.=getInfoUI($this->lang->def("_PROVIDE_SEARCH_QUERY"));
		}

		return $res;
	}


	function getSearchResult($search_txt, $data, $page_info) {
		$res="";
		$um =& UrlManager::getInstance();

		$url =$um->getUrl("page=".$page_info["page_code"]);
		$res.='<div class="wiki_search_title">';
		$title =preg_replace("/(".$search_txt.")/", '<span class="filter_evidence">\\1</span>', $page_info["title"]);
		$res.='<a href="'.$url.'">'.$title."</a></div>\n";
		$res.='<p class="wiki_search_txt_preview">';
		$content =strip_tags($data["content"]);
		$content =preg_replace("/\[\[(.*?)\]\]/", "\$1", $content);
		$start =strpos($content, $search_txt);
		$start =($start > 150 ? $start-150 : 0);
		$res.=($start > 0 ? "..." : "");
		$preview =substr($content, $start, 300);
		$preview =preg_replace("/(".$search_txt.")/", '<span class="filter_evidence">\\1</span>', $preview);
		$res.=$preview;
		$res.=(strlen($content) > 300 ? "..." : "");
		$res.="</p>";

		return $res;
	}

	var $page_code;
	
	function parseWikiLinks($txt, $pdf = false, $page_code = array()) {
		$this->page_code = $page_code;
		
		if($pdf)
			$res=preg_replace("/\\[\\[(.*?)\\]\\]/ei",
		                  "\$this->replaceWikiLinkPdf('\\1'".',true'.")",
		                  $txt);
		else
		$res=preg_replace("/\\[\\[(.*?)\\]\\]/ei",
		                  "\$this->replaceWikiLink('\\1')",
		                  $txt);

		return $res;
	}

	function replaceWikiLinkPdf($found)
	{
		$wiki_id=$this->getWikiId();
		$wiki_lang=$this->getWikiLanguage();

		if (strpos($found, "|") !== FALSE) {
			$link_arr=explode("|", $found);
			$page=trim($link_arr[0]);
			$title=trim($link_arr[1]);
		}
		else {
			$page=$found;
			$title=$found;
		}
		
		$page_code=getCleanTitle($page, 60);
		
		if(in_array($page_code, $this->page_code))
			$res = '<a href="#'.$page_code.'">'.$title.'</a>';
		else
			$res = $title;
		
		return $res;
	}
	
	function replaceWikiLink($found) {

		$um=& UrlManager::getInstance();
		$wiki_id=$this->getWikiId();
		$wiki_lang=$this->getWikiLanguage();

		if (strpos($found, "|") !== FALSE) {
			$link_arr=explode("|", $found);
			$page=trim($link_arr[0]);
			$title=trim($link_arr[1]);
		}
		else {
			$page=$found;
			$title=$found;
		}

		$page_code=getCleanTitle($page, 60);

		$page_info=$this->wikiManager->getPageInfo($wiki_id, $wiki_lang, $page_code);
		if ($page_info === FALSE) {
			$class="wiki_new_link";
			$parent=$this->getPageCode();
			$parent_qry ="&tempinfo=1&parent=".$parent."&title=";
			$parent_qry.=rawurlencode(substr(strip_tags($title), 0, 255));
		}
		else {
			$class="wiki_link";
			$parent_qry="";
		}

		$url=$um->getUrl("page=".$page_code.$parent_qry);

		$res="<a class=\"".$class."\" href=\"".$url."\">".$title."</a>";
		
		return $res;
	}
	
	function deleteWikiPage($wiki_id, $array_page_id)
	{
		return $this->wikiManager->deleteWikiPage($wiki_id, $array_page_id);
	}


}






Class CoreWikiManager {

	var $prefix=NULL;
	var $dbconn=NULL;

	var $wiki_info=NULL;
	var $page_info=NULL;
	var $page_id_arr=array();

	var $_wikiNavLinks=FALSE;


	function CoreWikiManager($prefix=FALSE, $dbconn=NULL) {
		$this->prefix=($prefix !== false ? $prefix : $GLOBALS["prefix_fw"]);
		$this->dbconn=$dbconn;
	}


	function _executeQuery( $query ) {
		if ($this->dbconn === NULL)
			$rs =sql_query($query);
		else
			$rs =sql_query($query, $this->dbconn);
		
		return $rs;
	}


	function _executeInsert( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) )
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}


	function _getWikiTable() {
		return $this->prefix."_wiki";
	}


	function getWikiPageTable() {
		return $this->prefix."_wiki_page";
	}


	function getWikiPageInfoTable() {
		return $this->prefix."_wiki_page_info";
	}
	
	function getWikiPageRevisionTable() {
		return $this->prefix."_wiki_revision";
	}
	
	function deleteWikiPage($wiki_id, $array_page_id)
	{
		$query =	"DELETE FROM ".$this->getWikiPageRevisionTable()
					." WHERE page_id IN (".implode(',', $array_page_id).")";
		
		$result = sql_query($query);
		
		if(!$result)
			return $result;
		
		$query =	"DELETE FROM ".$this->getWikiPageInfoTable()
					." WHERE page_id IN (".implode(',', $array_page_id).")";
		
		$result = sql_query($query);
		
		if(!$result)
			return $result;
		
		foreach($array_page_id as $page_id)
			$this->correctSonsPath($wiki_id, $page_id, TRUE);
		
		reset($array_page_id);
		
		$query =	"DELETE FROM ".$this->getWikiPageTable()
					." WHERE page_id IN (".implode(',', $array_page_id).")";
		
		$result = sql_query($query);
		
		if(!$result)
			return $result;
		return $result;
	}
	
	function correctSonsPath($wiki_id, $page_id, $first = FALSE)
	{
		$query =	"SELECT parent_id, lev, page_path"
					." FROM ".$this->getWikiPageTable()
					." WHERE page_id = '".$page_id."'";
		
		list($parent_id, $parent_level, $first_page_path) = sql_fetch_row(sql_query($query));
		
		$query =	"SELECT page_path"
					." FROM ".$this->getWikiPageTable()
					." WHERE page_id = '".$parent_id."'";
		
		list($parent_path) = sql_fetch_row(sql_query($query));
		
		$query =	"SELECT page_id"
					." FROM ".$this->getWikiPageTable()
					." WHERE parent_id = '".$page_id."'";
		
		$result = sql_query($query);
		
		if(sql_num_rows($result))
			while(list($sons_id) = sql_fetch_row($result))
			{
				$ord = $this->getLastPageOrd($wiki_id, $parent_level + ($first ? o : 1))+1;
				$page_path = ($first ? $parent_path : $first_page_path)."/".leadingZero($ord, 8);
				
				$query =	"UPDATE ".$this->getWikiPageTable()
							." SET parent_id = '".($first ? $parent_id : $page_id)."',"
							." page_path = '".$page_path."',"
							." lev = lev - 1"
							." WHERE page_id = '".$sons_id."'";
				
				sql_query($query);
				
				$this->correctSonsPath($wiki_id, $sons_id);
			}
	}
	
	function getWikiList($ini=FALSE, $vis_item=FALSE, $where=FALSE, $source_platform=FALSE) {

		$data_info=array();
		$data_info["data_arr"]=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getWikiTable()." ";

		if (($source_platform !== FALSE) && (!empty($source_platform))) {
			$qtxt.="WHERE source_platform='".$source_platform."' ";
			$where_prefix ="AND";
		}
		else {
			$where_prefix ="WHERE";
		}

		if (($where !== FALSE) && (!empty($where))) {
			$qtxt.=$where_prefix." ".$where." ";
		}

		$qtxt.="ORDER BY title ";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.="LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_assoc($q)) {

				$id=$row["wiki_id"];
				$data_info["data_arr"][$i]=$row;
				$this->wiki_info[$id]=$row;

				$i++;
			}
		}

		return $data_info;
	}


	function saveWiki($data, $source_platform) {

		$wiki_id=(int)$data["id"];
		$public=(isset($data["public"]) ? 1 : 0);
		$title=$data["title"];
		$description=$data["description"];
		$language=$data["language"];

		if ((isset($data["other_lang"])) && (is_array($data["other_lang"]))) {
			$other_lang_arr=$data["other_lang"];
		}
		else {
			$other_lang_arr=array();
		}

		if (in_array($language, $other_lang_arr)) {
			unset($other_lang_arr[$language]);
		}

		$other_lang=implode(",", $other_lang_arr);


		if ($wiki_id < 1) {

			$field_list ="source_platform, public, title, description, language, other_lang, creation_date";
			$field_val ="'".$source_platform."', '".$public."', '".$title."', '".$description."', ";
			$field_val.="'".$language."', '".$other_lang."', NOW()";

			$qtxt="INSERT INTO ".$this->_getWikiTable()." (".$field_list.") VALUES(".$field_val.")";
			$res=$this->_executeInsert($qtxt);
		}
		else {

			$qtxt ="UPDATE ".$this->_getWikiTable()." SET ";
			$qtxt.="public='".$public."', title='".$title."', ";
			$qtxt.="description='".$description."', language='".$language."', ";
			$qtxt.="other_lang='".$other_lang."' ";
			$qtxt.="WHERE wiki_id='".$wiki_id."'";
			$q=$this->_executeQuery($qtxt);

			$res=$wiki_id;
		}

		return $res;
	}


	function loadWikiInfo($id) {
		$res=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getWikiTable()." ";
		$qtxt.="WHERE wiki_id='".(int)$id."'";
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$res=sql_fetch_assoc($q);
		}

		return $res;
	}


	function getWikiInfo($id) {

		if (!isset($this->wiki_info[$id])) {
			$info=$this->loadWikiInfo($id);
			$this->wiki_info[$id]=$info;
		}

		return $this->wiki_info[$id];
	}


	function getWikiLangInfo($wiki_id) {

		$wiki_info=$this->getWikiInfo($wiki_id);

		$res["main_language"]=$wiki_info["language"];

		if (!empty($wiki_info["other_lang"])) {
			$res["other_lang"]=explode(",", $wiki_info["other_lang"]);
		}
		else {
			$res["other_lang"]=array();
		}

		return $res;
	}


	function deleteWiki($wiki_id) {

		// Delete wiki
		$qtxt ="DELETE FROM ".$this->_getWikiTable()." ";
		$qtxt.="WHERE wiki_id='".(int)$wiki_id."' LIMIT 1";
		$q=$this->_executeQuery($qtxt);

		// Delete wiki pages
/*		$qtxt ="DELETE FROM ".$this->_getFaqTable()." ";
		$qtxt.="WHERE category_id='".(int)$cat_id."'";
		$q=$this->_executeQuery($qtxt); */

		// Delete wiki roles
		$role_id="/framework/wiki/".(int)$wiki_id."/";
		$acl_manager=Docebo::user()->getAclManager();
		$acl_manager->deleteRoleFromPath($role_id);
	}


	function getWikiPermList() {
		return array("view", "edit");
	}


	function loadWikiPerm($wiki_id) {
		$res=array();
		$pl=$this->getWikiPermList();
		$acl_manager=& Docebo::user()->getACLManager();

		foreach($pl as $key=>$val) {

			$role_id="/framework/wiki/".$wiki_id."/".$val;
			$role=$acl_manager->getRole(false, $role_id);

			if (!$role) {
				$res[$val]=array();
			}
			else {
				$idst=$role[ACL_INFO_IDST];
				$res[$val]=array_flip($acl_manager->getRoleMembers($idst));
			}
		}

		return $res;
	}


	function saveWikiPerm($wiki_id, $selected_items, $database_items) {

		$pl=$this->getWikiPermList();
		$acl_manager=& Docebo::user()->getACLManager();
		foreach($pl as $key=>$val) {
			if ((isset($selected_items[$val])) && (is_array($selected_items[$val]))) {

				$role_id="/framework/wiki/".$wiki_id."/".$val;
				$role=$acl_manager->getRole(false, $role_id);
				if (!$role)
					$idst=$acl_manager->registerRole($role_id, "");
				else
					$idst=$role[ACL_INFO_IDST];

				foreach($selected_items[$val] as $pk=>$pv) {
					if ((!isset($database_items[$val])) || (!is_array($database_items[$val])) ||
						(!in_array($pv, array_keys($database_items[$val])))) {
							$acl_manager->addToRole($idst, $pv);
					}
				}

				if ((isset($database_items[$val])) && (is_array($database_items[$val])))
					$to_rem=array_diff(array_keys($database_items[$val]), $selected_items[$val]);
				else
					$to_rem=array();
				foreach($to_rem  as $pk=>$pv) {
					$acl_manager->removeFromRole($idst, $pv);
				}

			}
		}
	}




	function getImportArrFromXml($filename) {
		$res=array();

		require_once(_base_.'/lib/lib.domxml.php');
		$xml_doc=new DoceboDOMDocument();

		if (!$xml_doc)
			return FALSE;

		if ($xml_doc->load($filename)) {

			$xpath=new DoceboDOMXPath($xml_doc);

			$cat_info=array();
			$category_node=$xpath->query('/FAQCATEGORY');

			for($i = 0; $i < $category_node->length; $i++) {

				$item=$category_node->item($i);
				$elem=$xpath->query('TITLE/text()', $item);
				$elemNode=$elem->item(0);
				$cat_info["title"]=urldecode($elemNode->textContent);

				$item=$category_node->item($i);
				$elem=$xpath->query('DESCRIPTION/text()', $item);
				$elemNode=$elem->item(0);
				$cat_info["description"]=urldecode($elemNode->textContent);


				$cat_items=$xpath->query('CATEGORYITEMS/faq', $item);

				$faq_list=array();
				$arr_id=0;
				for($iFaq = 0; $iFaq < $cat_items->length; $iFaq++) {

					$faq=$cat_items->item($iFaq);
					$elem=$xpath->query('title/text()', $faq);
					$elemNode=$elem->item(0);
					$faq_list[$arr_id]["title"]=urldecode($elemNode->textContent);

					$faq=$cat_items->item($iFaq);
					$elem=$xpath->query('question/text()', $faq);
					$elemNode=$elem->item(0);
					$faq_list[$arr_id]["question"]=urldecode($elemNode->textContent);

					$faq=$cat_items->item($iFaq);
					$elem=$xpath->query('keyword/text()', $faq);
					$elemNode=$elem->item(0);
					$faq_list[$arr_id]["keyword"]=urldecode($elemNode->textContent);

					$faq=$cat_items->item($iFaq);
					$elem=$xpath->query('answer/text()', $faq);
					$elemNode=$elem->item(0);
					$faq_list[$arr_id]["answer"]=urldecode($elemNode->textContent);

					$arr_id++;
				}
			}
		}
		else
			return FALSE;

		$res["cat_info"]=$cat_info;
		$res["faq_list"]=$faq_list;

		return $res;
	}


	function importNewCategory($import_arr) {

		$cat_data=array();
		$cat_data["id"]=0;
		$cat_data["title"]=addslashes($import_arr["cat_info"]["title"]);
		$cat_data["description"]=addslashes($import_arr["cat_info"]["description"]);

		$cat_id=$this->saveWiki($cat_data);

		$this->importCategoryItems($cat_id, $import_arr["faq_list"]);
	}


	function importCategoryItems($cat_id, $faq_list) {

		foreach ($faq_list as $faq) {

			$fat_data=array();

			$faq_data["id"]=0;
			$faq_data["title"]=addslashes($faq["title"]);
			$faq_data["question"]=addslashes($faq["question"]);
			$faq_data["keyword"]=addslashes($faq["keyword"]);
			$faq_data["answer"]=addslashes($faq["answer"]);

			$this->saveFaq($cat_id, $faq_data);
		}
	}


	function getCategoryItems($cat_id, $ini=FALSE, $vis_item=FALSE, $where=FALSE) {

		$data_info=array();
		$data_info["data_arr"]=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getFaqTable()." ";

		$qtxt.="WHERE category_id='".(int)$cat_id."' ";
		if (($where !== FALSE) && (!empty($where))) {
			$qtxt.="AND ".$where." ";
		}

		$qtxt.="ORDER BY ord ";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.="LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_array($q)) {

				$id=$row["faq_id"];
				$data_info["data_arr"][$i]=$row;
				$this->faq_info[$id]=$row;

				$i++;
			}
		}

		return $data_info;
	}


	function loadPageInfo($wiki_id, $language, $id) {
		$res=FALSE;

		if ($language !== FALSE) {
			$fields="t1.*, t2.title, t2.version, t2.last_update";
			$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." as t1 ";
			$qtxt.="LEFT JOIN ".$this->getWikiPageInfoTable()." as t2 ";
			$qtxt.="ON (t1.page_id = t2.page_id AND t2.language='".$language."') ";
			$qtxt.="WHERE t1.wiki_id='".(int)$wiki_id."' AND t1.page_id='".(int)$id."'";
		}
		else {
			$fields="*";
			$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." ";
			$qtxt.="WHERE wiki_id='".(int)$wiki_id."' AND page_id='".(int)$id."'";
		}
		$q=$this->_executeQuery($qtxt); //echo $qtxt;

		if (($q) && (sql_num_rows($q) > 0)) {

			$res=sql_fetch_assoc($q);

			if ($language === FALSE) {
				$res["title"]="";
				$res["version"]="0";
				$res["last_update"]="";
			}
		}

		return $res;
	}


	function getPageInfo($wiki_id, $language, $code=FALSE, $id=FALSE) {

		if (($code === FALSE) && ($id === FALSE))
			return array();
		else if ($id === FALSE)
			$id=$this->getPageId($wiki_id, $code);

		if ((!isset($this->page_info[$id])) || (!isset($this->page_info[$id]["title"]))) {
			$info=$this->loadPageInfo($wiki_id, $language, $id);
			if ($language !== FALSE) {
				// Cache result only if also the language is specified
				$this->page_info[$id]=$info;
			}
			return $info;
		}
		else {
			return $this->page_info[$id];
		}
	}


	function loadPageId($wiki_id, $code) {
		$res=FALSE;

		$fields="page_id";
		$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." ";
		$qtxt.="WHERE wiki_id='".(int)$wiki_id."' AND page_code='".$code."'";
		$q=$this->_executeQuery($qtxt); //echo $qtxt;

		if (($q) && (sql_num_rows($q) > 0)) {
			$row=sql_fetch_assoc($q);
			$res=$row["page_id"];
		}

		return $res;
	}


	function getPageId($wiki_id, $code) {

		$code =rawurldecode($code);

		if (!isset($this->page_id_arr[$wiki_id]))
			$this->page_id_arr[$wiki_id]=array();

		if (!isset($this->page_id_arr[$wiki_id][$code])) {
			$id=$this->loadPageId($wiki_id, $code);
			$this->page_id_arr[$wiki_id][$code]=$id;
		}

		return $this->page_id_arr[$wiki_id][$code];
	}


	function getRootPageInfo($wiki_id, $language=FALSE) {
		$res=array();

		if ($language !== FALSE) {
			$fields="t1.*, t2.title, t2.version, t2.last_update";
			$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." as t1 ";
			$qtxt.="LEFT JOIN ".$this->getWikiPageInfoTable()." as t2 ";
			$qtxt.="ON (t1.page_id = t2.page_id AND t2.language='".$language."') ";
			$qtxt.="WHERE t1.wiki_id='".(int)$wiki_id."' AND t1.lev='0'";
		}
		else {
			$fields="*";
			$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." ";
			$qtxt.="WHERE wiki_id='".(int)$wiki_id."' AND lev='0'";
		}
		$q=$this->_executeQuery($qtxt);

		if ($q) {
			if (sql_num_rows($q) > 0) {
				$res=sql_fetch_assoc($q);

				if ($language === FALSE) {
					$res["title"]="";
					$res["version"]="0";
					$res["last_update"]="";
				}
			}
			else {
				$id=$this->createRootPage($wiki_id);
				$res=$this->getPageInfo($wiki_id, $language, FALSE, $id);
			}
		}

		return $res;
	}


	function createRootPage($wiki_id) {
		$res=FALSE;

		$info=$this->getWikiInfo($wiki_id);
		$title=$info["title"];
		$page_code=getCleanTitle($title, 255);
		$path="/root";
		$lev=0;

		$lang_info=$this->getWikiLangInfo($wiki_id);
		$language=$lang_info["main_language"];

		// Adding the page
		$field_list ="page_code, page_path, wiki_id";
		$field_val ="'".$page_code."', '".$path."', '".(int)$wiki_id."'";

		$qtxt="INSERT INTO ".$this->getWikiPageTable()." (".$field_list.") VALUES (".$field_val.")";
		$res=$this->_executeInsert($qtxt);


		if ($res > 0) {

			// Adding other page information
			$field_list ="page_id, language, title, last_update, wiki_id";
			$field_val ="'".$res."', '".$language."', '".$title."', NOW(), '".(int)$wiki_id."'";

			$qtxt="INSERT INTO ".$this->getWikiPageInfoTable()." (".$field_list.") VALUES (".$field_val.")";
			$q=$this->_executeInsert($qtxt);
		}

		return $res;
	}


	function savePage($wiki_id, $data, $language, $page_code=FALSE, $page_temp_info=FALSE) {
		require_once($GLOBALS["where_framework"]."/lib/lib.wiki_revision.php");
		//require_once(_base_.'/lib/lib.utils.php');

		$page_id=(int)$data["page_id"];
		$title=$data["title"];
		$content=$data["content"];

		if ($page_id < 1) { // Add

			if (($page_temp_info !== FALSE) && ($page_code !== FALSE)) {

				$lev=$page_temp_info["parent_info"]["lev"]+1;
				$parent_id=$page_temp_info["parent_info"]["page_id"];
				$version=1;

				$ord=$this->getLastPageOrd($wiki_id, $lev)+1;
				$page_path=$page_temp_info["parent_info"]["page_path"]."/".leadingZero($ord, 8);

				// Creating page
				$field_list ="page_code, parent_id, page_path, lev, wiki_id";
				$field_val ="'".$page_code."', '".(int)$parent_id."', '".$page_path."', '".(int)$lev."', ";
				$field_val.="'".(int)$wiki_id."'";

				$qtxt="INSERT INTO ".$this->getWikiPageTable()." (".$field_list.") VALUES (".$field_val.")";
				$res=$this->_executeInsert($qtxt);

				// Adding revision (page text)
				$rev=new WikiRevisionManager(array($wiki_id, $res, $language));
				$revision_data=array("content"=>$content);
				$rev->addRevision($revision_data);

				// Adding other page information
				$this->createPageInfo($res, $language, $title, $version, $wiki_id);
			}
			else {
				$res=FALSE;
			}
		}
		else { // Update

			$rev=new WikiRevisionManager(array($wiki_id, $page_id, $language));
			$revision_data=array("content"=>$content);
			$version=$rev->addRevision($revision_data);

			$qtxt ="SELECT * FROM ".$this->getWikiPageInfoTable()." ";
			$qtxt.="WHERE page_id='".$page_id."' AND language='".$language."'";
			$q=$this->_executeQuery($qtxt);

			if (($q) && (sql_num_rows($q) > 0)) {

				$qtxt ="UPDATE ".$this->getWikiPageInfoTable()." SET title='".$title."', ";
				$qtxt.="version='".$version."', last_update=NOW() ";
				$qtxt.="WHERE page_id='".$page_id."' AND language='".$language."'";

				$q=$this->_executeQuery($qtxt);
			}
			else if (($q) && (sql_num_rows($q) == 0)) {
				$this->createPageInfo($page_id, $language, $title, $version, $wiki_id);
			}


			$res=$page_id;
		}

		return $res;
	}


	function createPageInfo($page_id, $language, $title, $version, $wiki_id) {
		$res=FALSE;

		$field_list ="page_id, language, title, version, last_update, wiki_id";
		$field_val ="'".$page_id."', '".$language."', '".$title."', '".$version."', NOW(), '".(int)$wiki_id."'";

		$qtxt="INSERT INTO ".$this->getWikiPageInfoTable()." (".$field_list.") VALUES (".$field_val.")";
		$res=$this->_executeInsert($qtxt);

		return $res;
	}


	function getLastPageOrd($wiki_id, $lev) {
		$res=0;
		//require_once(_base_.'/lib/lib.utils.php');
		$table=$this->getWikiPageTable();
		$where="wiki_id='".(int)$wiki_id."' AND lev='".(int)$lev."'";

		$qtxt ="SELECT page_path FROM ".$table." WHERE ";
		$qtxt.=$where." ORDER BY page_path DESC LIMIT 0,1";

		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$row=sql_fetch_assoc($q);
			$page_path=$row["page_path"];
			$last_ord=end(explode("/", $page_path));
			$res=(int)$last_ord;
		}

		return $res;
	}


	function moveWikiPage($direction, $page_id, $wiki_id, $lev) {
		//require_once(_base_.'/lib/lib.utils.php');

		$table=$this->getWikiPageTable();
		$where="wiki_id='".(int)$wiki_id."' AND lev='".(int)$lev."'";

		//utilMoveItem($direction, $table, "page_id", $page_id, "ord", $where);
	}


	function orderToPageName($order) {
		$len=8;

		$zero_to_add=$len-strlen($order);
		if ($zero_to_add > 0)
			$res=str_repeat("0", $zero_to_add).$order;
		else
			$res=$order;

		return $res;
	}


	function deleteFaq($faq_id) {

		// Delete faq
		$qtxt ="DELETE FROM ".$this->_getFaqTable()." ";
		$qtxt.="WHERE faq_id='".(int)$faq_id."' LIMIT 1";
		$q=$this->_executeQuery($qtxt);
	}


	function getLanguageArr($include_other=FALSE) {
		$res=array();

		$lang_arr=Docebo::langManager()->getAllLangCode();

		if ($include_other)
			$res["other"]= Lang::t("_OTHER_LANGUAGE", "wiki");
		foreach($lang_arr as $lang_code) {
			$res[$lang_code]=ucfirst($lang_code);
		}

		return $res;
	}


	function getWikiNavLinks($wiki_id, $page_code) {
		$res=array();

		if ($this->_wikiNavLinks !== FALSE)
			return $this->_wikiNavLinks;


		$info =$this->getPageInfo($wiki_id, FALSE, $page_code);
		if ($info !== FALSE) {
			$lev=(int)$info["lev"];
			$parent_id=(int)$info["parent_id"];

			$fields="*";
			$qtxt ="SELECT ".$fields." FROM ".$this->getWikiPageTable()." ";
			$qtxt.="WHERE ((lev='".$lev."' OR lev='".($lev+1)."'";
			$qtxt.=($lev > 0 ? " OR lev='".($lev-1)."'" : "")." OR lev='0') ";
			$qtxt.="AND wiki_id='".(int)$wiki_id."') ";
			$qtxt.=($parent_id > 0 ? "OR page_id='".$parent_id."' " : "");
			$qtxt.="ORDER BY page_path";

			$q=$this->_executeQuery($qtxt); //echo $qtxt;

			$found=FALSE;
			if (($q) && (sql_num_rows($q) > 0)) {
				while(($found == FALSE) && ($row=sql_fetch_assoc($q))) {

					if ($row["lev"] == 0) {
						$home=$row;
					}

					if ($row["page_id"] == $parent_id) {
						$up=$row;
					}

					if ($row["page_code"] == $page_code) {
						$found=TRUE;
						$next=sql_fetch_assoc($q);
					}
					else {
						$prev=$row;
					}
				}
			}

		}
		else {
			// Todo: add revious page info to $prev array if current page does not exists (not yet created)
		}

		if ((isset($prev)) && (is_array($prev)))
			$res["prev"]=$prev;
		else
			$res["prev"]=FALSE;

		if ((isset($row)) && (is_array($row)))
			$res["current"]=$row;
		else
			$res["current"]=FALSE;

		if ((isset($next)) && (is_array($next)))
			$res["next"]=$next;
		else
			$res["next"]=FALSE;

		if ((isset($up)) && (is_array($up)))
			$res["up"]=$up;
		else
			$res["up"]=FALSE;

		if ((isset($home)) && (is_array($home)))
			$res["home"]=$home;
		else
			$res["home"]=FALSE;


		//echo "<br ><pre>"; print_r($res); echo "</pre>";

		$this->_wikiNavLinks=$res;
		return $res;
	}


	function searchByTitle($search_txt, $wiki_id, $wiki_lang) {
		$res=array();

		$qtxt ="SELECT page_id FROM ".$this->getWikiPageInfoTable(). " ";
		$qtxt.="WHERE title LIKE '%".$search_txt."%' AND language='".$wiki_lang."' ";
		$qtxt.="AND wiki_id='".(int)$wiki_id."' ";
		$qtxt.="ORDER BY title";

		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {
				$res[]=$row["page_id"];
			}
		}

		return $res;
	}


}



require_once(_base_.'/lib/lib.treedb.php');
require_once(_base_.'/lib/lib.treeview.php');


class TreeDb_WikiDb extends TreeDb {

	var $wiki_id=0;
	var $info_table="";
	var $wiki_lang="";

	// Constructor of TreeDb_WikiDb class
	function TreeDb_WikiDb($table_name, $info_table, $wiki_id, $wiki_lang) {

		$this->wiki_id=$wiki_id;

		$this->table = $table_name;
		$this->info_table = $info_table;
		$this->wiki_lang = $wiki_lang;
		$this->fields = array(
			'id' => 'page_id',
			'idParent' => 'parent_id',
			'path' => 'page_path',
			'lev' => 'lev'
		);
	}

	function _getOtherTables() { return " LEFT JOIN ".$this->info_table;	}

	function _getJoinFilter($tname = FALSE) {
		$tname.=(!empty($tname) ? "." : "");

		$res =$tname."page_id = ".$this->info_table.".page_id AND ";
		$res.=$this->info_table.".language='".$this->wiki_lang."'";

		return $res;
	}

	function _getOtherFields($tname = FALSE) {
		$res=", ";
		$tname.=(!empty($tname) ? "." : "");

		$res.=$tname."page_code, ";
		$res.=$tname."wiki_id, ";
		$res.=$this->info_table.".title ";

		return $res;
	}

	function _getOtherValues() {
		$res=", ";
		$tname.=(!empty($tname) ? "." : "");

		$res.="'', ";
		$res.="'".$this->wiki_id."', ";
		$res.="'' ";

		return $res;
	}

	function _getOtherUpdates() {

	}


	function _getFilter($tname = FALSE) {

		$tname.=($tname !== FALSE ? "." : "");

		$result = " AND ".$tname."wiki_id='".$this->wiki_id."'";// AND ".$tname."lev > 0";
		//$result.=" AND ".$tname."page_path LIKE '/root/%' "; //AND ".$tname."lev > 0";
		return $result;
	}


	function &getRootFolder() {
		$folder = new Folder( $this, array( 0, 0, "/root", 0) );
		return $folder;
	}


	function addFolderById($idParent, $folderName) {

		parent::addFolderById($idParent, $folderName);
	}


	function addItem($idParent, $org_name) {

		$idReference = parent::addFolderById($idParent, $org_name);
		return $idReference;
	}


	function modifyItem( $arrData ) {

		$folder = $this->getFolderById( $arrData['idItem'] );
		$this->changeOtherData( $folder );
	}

}


define("FIELD_PAGE_CODE", 0);
define("FIELD_WIKI_ID", 1);
define("FIELD_TITLE", 2);

class TreeView_WikiView extends TreeView {

	var $can_add = false;
	var $can_mod = false;
	var $can_del = false;
	var $lang = false;
	var $show_action = true;

	var $hide_inline_action = true;

	function  TreeView_WikiView($tdb, $id, $rootname = 'root') {

		parent::TreeView($tdb, $id, $rootname);
		$this->can_add = true;
		$this->can_mod = true;
		$this->can_del = true;
	}


	function hideInlineAction() {

		$this->hide_inline_action = true;
	}


	function showInlineAction() {

		$this->hide_inline_action = false;
	}


	function showAction() {
		$this->show_action = true;
	}


	function hideAction() {
		$this->show_action = false;
	}


	function _getAddImage() 						{ return getPathImage('fw').'standard/add.png'; }
	function _getAddLabel() 						{ return Lang::t('_ADD', 'standard'); }
	function _getAddAlt() 							{ return Lang::t('_ADD', 'standard'); }
	function canAdd() 								{ return $this->can_add && !$this->hide_inline_action; }

	function _getRenameImage()						{ return getPathImage('fw').'standard/edit.png'; }
	function _getRenameLabel() 						{ return Lang::t('_MOD', 'standard'); }
	function canRename() 							{ return $this->isFolderSelected() && $this->can_mod; }
	function canInlineRename() 						{ return $this->can_mod && !$this->hide_inline_action; }
	function canInlineRenameItem( &$stack, $level ) { return (($level != 0) && $this->can_mod); }

	function _getMoveLabel() 						{ return Lang::t('_MOVE', 'standard'); }
	function canMove()								{ return $this->isFolderSelected() && $this->can_mod; }
	function canInlineMove() 						{ return $this->can_mod && !$this->hide_inline_action; }
	function canInlineMoveItem( &$stack, $level ) 	{ return (($level != 0) && $this->can_mod); }

	function _getDeleteLabel() 						{ return Lang::t('_DEL', 'standard'); }
	function canDelete() {
		$info = $this->getSelectedFolderData();
		return ($info['isLeaf'] == 1) && $this->isFolderSelected() && $this->can_del;
	}
	function canInlineDelete() 						{ return $this->can_del && !$this->hide_inline_action; }
	function canInlineDeleteItem( &$stack, $level ) {
		return ( ($stack[$level]['isLeaf'] == 1) && ($level != 0) && $this->can_del );
	}

	function _getMoveTargetLabel()					{ return Lang::t('_MOVE', 'standard').' : '; }
	function _getCancelLabel()						{ return Lang::t('_UNDO', 'standard'); }

	function _getOtherActions() {

		if( $this->isFolderSelected() ) {

			return array();
		}
		return array();
	}

	function getFolderPrintName( &$folder ) {
		if( $folder->id == 0 )
			return "wiki";//$this->rootname;
			else {
				$title=$folder->otherValues[FIELD_TITLE];
				//var_dump($title);

				if ($title !== NULL) {
					$res=$title;
				}
				else {
					$page_code=$folder->otherValues[FIELD_PAGE_CODE];
					$res=$page_code." (". Lang::t("_NOT_TRANSLATED", "wiki", "framework").")";
				}

				return $res;
			}
	}

	function extendedParsing( $arrayState, $arrayExpand, $arrayCompress ) {

		if(!isset($arrayState[$this->id])) return;

	}


	function getImage( &$stack, $currLev, $maxLev ) {
		$res=FALSE;

		if( $currLev == $maxLev ) {
			if (($currLev > 0) && ($stack[$maxLev]['isExpanded'])) {
				if (!$stack[$maxLev]['isLeaf'])
					$res=array("wiki_page", "wiki/page_open.png", "_PAGE");
			}
			else if (($currLev > 0) && (!$stack[$currLev]['isExpanded'])) {
				$res=array("wiki_page", "wiki/page.png", "_PAGE");
			}
		}

		if ($res === FALSE)
			return parent::getImage( $stack, $currLev, $maxLev );
		else
			return $res;
	}


	function autoLoad() {
		$res="";
		$op=(!empty($this->op) ? $this->op : "display");
		switch($op) {
			case 'newfolder': {
				$res=$this->loadNewFolder();
			} break;
			case "renamefolder": {
				$res=$this->loadRenameFolder();
			} break;
			case "movefolder": {
				$res=$this->loadMoveFolder();
			} break;
			case "deletefolder": {
				$res=$this->loadDeleteFolder();
			} break;

			default:
			case 'display': {
				$res =$this->load();
				//$res.=$this->loadActions();
			} break;
		}
		return $res;
	}

	function printElement(&$stack, $level) {
		if(isset($_POST['page']))
			$pages_selected = $_POST['page'];
		else
			$pages_selected = array();
		
		$tree = '<div class="TreeViewRowBase">';
		$id = ($stack[$level]['isExpanded'])?($this->_getCompressActionId()):($this->_getExpandActionId());
		$id .= $stack[$level]['folder']->id;
		for( $i = 0; $i <= $level; $i++ ) {
			list( $classImg, $imgFileName, $imgAlt ) = $this->getImage($stack,$i,$level);
			if( $i != ($level-1) || $stack[$level]['isLeaf'] ) {
				$tree .= '<img src="'.getPathImage('fw').$imgFileName.'" '
						.'class="'.$classImg.'" alt="'.$imgAlt.'" '
						.'title="'.$imgAlt.'" />';
			} else {
				$tree .= '<input type="submit" class="'.$classImg.'" value="'
					.'" name="'. $id .'" id="seq_'. $stack[$level]['idSeq'] .'img" />';
			}
		}
		if( $stack[$level]['folder']->id == $this->selectedFolder ) {
			$this->selectedFolderData = $stack[$level];
			$classStyle = 'TreeItemSelected';
		} else {
			$classStyle = 'TreeItem';
		}
		$tree .= $this->getPreFolderName($stack[$level]['folder']);
		$tree .= ($level ? ' <input type="checkbox" id="page_'.$stack[$level]['folder']->id.'" name="page[]" value="'.$stack[$level]['folder']->id.'" '.(in_array($stack[$level]['folder']->id, $pages_selected) ? 'checked="checked"' : '').'>' : '').''
			.'<input type="submit" class="'.$classStyle.'" value="'
			.$this->getFolderPrintName( $stack[$level]['folder'])
			.'" name="'
			. $this->_getSelectedId().$stack[$level]['folder']->id
			.'" id="seq_'. $stack[$level]['idSeq'] .'" '
			.$this->getFolderPrintOther($stack[$level]['folder'])
			.' />';
		$tree .= '</div>';
		$tree .= $this->printActions( $stack, $level );
		return $tree."\n";
	}

	function printActions( &$stack, $level ) {
		$res = '';

/*		if( $this->canInlineDelete() ) {
			if( $this->canInlineDeleteItem($stack, $level) )
				$tree .= '<input type="submit" class="TVActionDelete" value="" name="'
					.$this->_getOpDeleteFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getDeleteLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}
		if( $this->canInlineRename() ) {
			if( $this->canInlineRenameItem($stack, $level) )
				$tree .= '<input type="submit" class="TVActionRename" value="" name="'
					.$this->_getOpRenameFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getRenameLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}

		if( $this->canInlineMove() ) {
			if( $this->canInlineMoveItem($stack, $level) )
				$tree .= '<input type="submit" class="TVActionMove" value="" name="'
					.$this->_getOpMoveFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getMoveLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}*/

		if ($level > 0) {
			$um=& UrlManager::getInstance();
			$page_code=$stack[$level]['folder']->otherValues[FIELD_PAGE_CODE];
			$title =$this->lang->def("_ALT_GOTO_PAGE");

			$img ="<img class=\"tree_action\" src=\"".getPathImage('fw')."wiki/goto_page.gif\" alt=\"".$title."\" ";
			$img.="title=\"".$title."\" />";
			$url=$um->getUrl("page=".$page_code);
			$res.="<a href=\"".$url."\">".$img."</a>\n";
		}

		if($this->show_action === false)
			return "";
		else
			return $res;
	}

}


?>
