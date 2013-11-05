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

if(Docebo::user()->isAnonymous()) die('You can\'t access!');

addCss("style_wiki", "fw");
addCss("style_wiki_lms", "lms");
require_once($GLOBALS["where_lms"]."/lib/lib.wiki.php");


function &cwaSetup() {
	$res =new LmsWikiAdmin();
	$res->urlManagerSetup("modname=wiki&op=main");
	$res->setCourseId($_SESSION["idCourse"]);
	return $res;
}


function &cwpSetup($wiki_id) {
	$res = new LmsWikiPublic($wiki_id);
	$res->setInternalPerm("edit", checkPerm('edit', true));
	return $res;
}


function wikiMain() {
	checkPerm('view');
	$can_mod = checkPerm('edit', true);
	$can_admin = checkPerm('admin', true);

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$res ="";

	$cwa =& cwaSetup();
	$wiki_list =$cwa->getCourseWikiList();

	if ((count($wiki_list["list"]) == 1) && (!$can_admin)) {
		$um =& UrlManager::getInstance();
		$where = implode(",", $wiki_list["list"]);
		$url =$um->getUrl("op=show&wiki_id=".$where);
		Util::jump_to($url);
		return TRUE;
	}

	$title=$cwa->lang->def("_WIKI");
	$res.=$cwa->titleArea($title);
	$res.=$cwa->getHead($can_mod, TRUE, TRUE, TRUE, TRUE);


	if ((count($wiki_list["list"]) == 0) && (!$can_admin)) {
		$res.=getInfoUi($cwa->lang->def("_NO_WIKI_AVAILABLE"));
	}
	else {
		//$res.=$cwa->getWikiListTable(0);
		$res.=$cwa->getCourseWikiTable($can_admin, $wiki_list);
	}


	$res.=$cwa->getFooter(TRUE);
	$out->add($res);
}


function &getLmsWikiUrlManager($wiki_id) {
	require_once(_base_.'/lib/lib.urlmanager.php');

	$um =& UrlManager::getInstance();
	$um->setStdQuery("modname=wiki&op=show&wiki_id=".$wiki_id);

	return $um;
}


function addeditWiki($id=0) {
	checkPerm('view');
	$can_mod =checkPerm('edit', true);
	$can_admin =checkPerm('admin', true);

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$res ="";

	$cwa =& cwaSetup();

	if ($id > 0) {
		$info=$cwa->wikiManager->getWikiInfo($id);
		$title_label=$cwa->lang->def("_MOD").": ".$info["title"];
	}
	else {
		$title_label=$cwa->lang->def("_ADD_WIKI");
	}

	$um=& UrlManager::getInstance();
	$back_url=$um->getUrl();
	$title[$back_url]=$cwa->lang->def("_WIKI");
	$title[]=$title_label;
	$res.=$cwa->titleArea($title);
	$res.=$cwa->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);
	$res.=$cwa->backUi();

	$res.=$cwa->addeditWiki($id);

	$res.=$cwa->backUi();
	$res.=$cwa->getFooter();
	$out->add($res);
}


function saveWiki() {
	checkPerm('edit');
	$cwa =& cwaSetup();
	$cwa->saveWiki();
}



function deleteWiki() {
	checkPerm('edit');
	$res="";
	
	$can_mod = checkPerm('edit', true);
	
	if ((isset($_GET["wiki_id"])) && ($_GET["wiki_id"] > 0))
		$wiki_id=(int)$_GET["wiki_id"];
	else
		return FALSE;

	$cwa =& cwaSetup();
	$cwa->requireLmsWikiOwner($wiki_id);

	$delete_ui_code=$cwa->deleteWiki($wiki_id);

	if (!empty($delete_ui_code)) {

		$info=$cwa->wikiManager->getWikiInfo($wiki_id);
		$title_label=$cwa->lang->def("_DEL").": ".$info["title"];

		$out=& $GLOBALS["page"];
		$out->setWorkingZone("content");

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl();
		$title[$back_url]=$cwa->lang->def("_WIKI");
		$title[]=$title_label;
		$res.=$cwa->titleArea($title);
		$res.=$cwa->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);
		$res.=$cwa->backUi();

		$course_id =$cwa->getCourseId();
		if ($cwa->wikiManager->isWikiUsedByOthers($course_id, $wiki_id)) {
			$res.=getInfoUI($cwa->lang->def("_WIKI_USED_BY_OTHER_COURSES"));
		}

		$res.=$delete_ui_code;

		$res.=$cwa->getFooter();
		$out->add($res);
	}
}


function showWikiPerm() {
	checkPerm('edit');
	$res="";
	
	$can_mod = checkPerm('edit', true);
	
	if ((isset($_GET["wiki_id"])) && ($_GET["wiki_id"] > 0))
		$wiki_id=(int)$_GET["wiki_id"];
	else
		return FALSE;

	$cwa =& cwaSetup();

	$page_content=$cwa->showWikiPerm($wiki_id);

	if ($page_content !== FALSE) {

		$out=& $GLOBALS["page"];
		$out->setWorkingZone("content");

		$info=$cwa->wikiManager->getWikiInfo($wiki_id);
		$title_label=$cwa->lang->def("_ALT_SETPERM").": ".$info["title"];

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl();
		$title[$back_url]=$cwa->lang->def("_WIKI");
		$title[]=$title_label;
		$res.=$cwa->titleArea($title);
		$res.=$cwa->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);

		$res.=$page_content;

		$res.=$cwa->getFooter();
		$out->add($res);
	}
}


function doneWikiPerm() {
	checkPerm('edit');

	Docebo::user()->loadUserSectionST('/lms/course/private/');
	Docebo::user()->SaveInSession();

	$cwa =& cwaSetup();
	$um=& UrlManager::getInstance();
	$url=$um->getUrl();
	Util::jump_to($url);
}


function selectWiki() {
	checkPerm('edit');
	$res="";
	
	$can_mod = checkPerm('edit', true);
	
	$cwa =& cwaSetup();

	$page_content =$cwa->selectLmsWiki();

	if ($page_content !== FALSE) {

		$out=& $GLOBALS["page"];
		$out->setWorkingZone("content");

		$title_label=$cwa->lang->def("_SELECT_WIKI");

		$um=& UrlManager::getInstance();
		$back_url=$um->getUrl();
		$title[$back_url]=$cwa->lang->def("_WIKI");
		$title[]=$title_label;
		$res.=$cwa->titleArea($title);
		$res.=$cwa->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);

		$res.=$page_content;

		$res.=$cwa->getFooter();
		$out->add($res);
	}
}


// --------------------------------------------------------


function showWiki() {
	checkPerm('view');
	
	$can_mod = checkPerm('edit', true);
	$can_admin = checkPerm('admin', true);
	
	$lang =& DoceboLanguage::createInstance('profile', 'framework');

	if ((!isset($_GET["wiki_id"])) || ($_GET["wiki_id"] < 1))
		return FALSE;

	$out =& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$res = "";

	$wiki_id = $_GET["wiki_id"];
	$um =& getLmsWikiUrlManager($wiki_id);
	$cwp =& cwpSetup($wiki_id);

	$title = $cwp->lang->def("_WIKI");
	$res .= $cwp->titleArea($title);
	
	$res .= getBackUi('index.php?modname=wiki&op=main', '<< '.$lang->def('_BACK').'');//'<a href="index.php?modname=wiki&op=main">Indietro</a></p>';
	
	$res .= $cwp->getHead($can_mod, TRUE, TRUE, TRUE, TRUE);

	$res .= $cwp->getPageContent();

	$res .= $cwp->getFooter(TRUE);
	
	$out->add($res);
}


function editWikiPage() {
	checkPerm('edit');

	if ((!isset($_GET["wiki_id"])) || ($_GET["wiki_id"] < 1))
		return FALSE;
	
	$can_mod = checkPerm('edit', true);
	
	$wiki_id =$_GET["wiki_id"];
	$um =& getLmsWikiUrlManager($wiki_id);
	$cwp =& cwpSetup($wiki_id);
	//$cwp->checkWikiPerm($wiki_id, "edit");

	$page_code=$cwp->editWikiPage();

	if (!empty($page_code)) {
		$out=& $GLOBALS["page"];
		$out->setWorkingZone("content");
		$res ="";

		$title =$cwp->lang->def("_WIKI");
		$res.=$cwp->titleArea($title);
		$res.=$cwp->getHead($can_mod, FALSE, FALSE, FALSE, TRUE);
		
		$res.=$page_code;
		
		$res.=$cwp->getFooter();
		$out->add($res);
	}
}


function wikiMap() {
	checkPerm('edit');
	
	if ((!isset($_GET["wiki_id"])) || ($_GET["wiki_id"] < 1))
		return FALSE;
	
	$pi = Get::req('pi', DOTY_MIXED, '');
	$lang = Get::req('lang', DOTY_MIXED, '');
	$page = Get::req('page', DOTY_MIXED, '');
	$wiki_id =$_GET["wiki_id"];
	
	if(isset($_POST['del_selected']))
		Util::jump_to('index.php?mn=wiki&pi='.$pi.'&op=map&lang='.$lang.'&page='.$page.'&result='.(int)wikiPageDeleting($wiki_id));
	
	$can_mod = checkPerm('edit', true);
	
	if(isset($_POST['export_pdf']) || isset($_POST['export_all_pdf']))
		wikiPdfExport($wiki_id, isset($_POST['export_all_pdf']));
	
	$um =& getLmsWikiUrlManager($wiki_id);
	$cwp =& cwpSetup($wiki_id);
	//$cwp->checkWikiPerm($wiki_id, "view");

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	
	if(isset($_GET['result']) && $_GET['result'])
		$out->add(getResultUi($cwp->lang->def("_OPERATION_SUCCESSFUL")));
	elseif(isset($_GET['result']))
		$out->add(getErrorUi($cwp->lang->def("_OPERATION_FAILURE")));
	
	$title=$cwp->lang->def("_WIKI");
	$res ="";
	$res.=$cwp->titleArea($title);
	$res.=$cwp->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);

	$res.=$cwp->wikiMap();
	
	$res .= '<br />'
			.Form::getButton('export_pdf', 'export_pdf', $cwp->lang->def('_EXPORT_WIKI'))
			.Form::getButton('export_all_pdf', 'export_all_pdf', $cwp->lang->def('_EXPORT_HTML'));

	$res.=$cwp->getFooter();

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$out->add($res);
}

function wikiPdfExport($wiki_id, $all)
{
	$cwp =& cwpSetup($wiki_id);
	
	$query =	"SELECT title"
				." FROM ".$GLOBALS['prefix_fw']."_wiki"
				." WHERE wiki_id = '".$wiki_id."'";
	
	list($wiki_title) = sql_fetch_row(sql_query($query));
	
	$pages_selected = $_POST['page'];
	
	$page_code_array = array();
	
	$query =	"SELECT page_code"
				." FROM ".$GLOBALS['prefix_fw']."_wiki_page";
	
	if($all)
		$query .= " WHERE wiki_id = '".$wiki_id."'";
	else
		$query .= " WHERE page_id IN (".implode(',',$pages_selected).")";
	
	$result = sql_query($query);
	
	while(list($page_code) = sql_fetch_row($result))
		$page_code_array[] = $page_code;
	
	$query =	"SELECT p.page_id, p.page_code, i.title, r.content"
				." FROM ".$GLOBALS['prefix_fw']."_wiki_page as p"
				." JOIN ".$GLOBALS['prefix_fw']."_wiki_page_info as i ON p.page_id = i.page_id"
				." JOIN ".$GLOBALS['prefix_fw']."_wiki_revision as r ON p.page_id = r.page_id";
	if($all)
		$query .= " WHERE p.wiki_id = '".$wiki_id."'";
	else
		$query .= " WHERE p.page_id IN (".implode(',',$pages_selected).")";
	
	$query .=	" AND i.version = r.version"
				." AND i.language = '".$cwp->getWikiLanguage($wiki_id)."'"
				." AND r.language = '".$cwp->getWikiLanguage($wiki_id)."'"
				." ORDER BY p.page_path";
	
	$html = '';
	
	$result = sql_query($query);
	
	while(list($page_id, $page_code, $title, $content) = sql_fetch_row($result))
	{
		$content = str_replace('{site_base_url}', getSiteBaseUrl(), $content);

		$html .=	'<div style="page-break-after:always">'
					.'<a name="'.$page_code.'"><h2>'.htmlentities($title, ENT_COMPAT, 'UTF-8').'</h2></a>'
					.$cwp->parseWikiLinks($content, true, $page_code_array)
					.'</div>';
	}
	
	$html =	'<html><head>'
				.'<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'
				.'<style>'
				.'body {font-family: helvetica;'
				.'margin: 30px 30px 30px 30px;}'
				.'</style>'
				.'</hedad><body>'
				.$html
				.'</body></html>';
	
	require_once(_base_.'/lib/lib.download.php' );
	
	sendStrAsFile($html, getCleanTitle($wiki_title, 60).'.html', 'utf-8');
}

function wikiPageDeleting($wiki_id)
{
	if ((int)$wiki_id < 1)
		return FALSE;
	
	cwpGlobalSetup($wiki_id);
	$cwp=& $GLOBALS["core_wiki_public"];
	
	$pages_selected = $_POST['page'];
	
	return $cwp->deleteWikiPage($wiki_id, $pages_selected);
}

function wikiPageHistory() {
	checkPerm('edit');

	if ((!isset($_GET["wiki_id"])) || ($_GET["wiki_id"] < 1))
		return FALSE;
	
	$can_mod = checkPerm('edit', true);
	
	$wiki_id =$_GET["wiki_id"];
	$um =& getLmsWikiUrlManager($wiki_id);
	$cwp =& cwpSetup($wiki_id);
	//$cwp->checkWikiPerm($wiki_id, "edit");

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");

	$title=$cwp->lang->def("_WIKI");
	$res ="";
	$res.=$cwp->titleArea($title);
	$res.=$cwp->getHead($can_mod, TRUE, FALSE, FALSE, TRUE);

	$res.=$cwp->wikiPageHistory();

	$res.=$cwp->getFooter();

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$out->add($res);
}


function wikiSearch() {
	checkPerm('edit');

	if ((!isset($_GET["wiki_id"])) || ($_GET["wiki_id"] < 1))
		return FALSE;
	
	$can_mod = checkPerm('edit', true);
	
	$wiki_id =$_GET["wiki_id"];
	$um =& getLmsWikiUrlManager($wiki_id);
	$cwp =& cwpSetup($wiki_id);
	//$cwp->checkWikiPerm($wiki_id, "view");

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");

	$title=$cwp->lang->def("_WIKI");
	$res ="";
	$res.=$cwp->titleArea($title);
	$res.=$cwp->getHead($can_mod, FALSE, FALSE, TRUE, TRUE);


	$res.=$cwp->getFoundPages();


	$res.=$cwp->getFooter(FALSE);
	$out->add($res);
}



// --------------------------------------------------------


function wikiDispatch($op) {

	switch($op) {
		case "main": {
			wikiMain();
		} break;
		case "addwiki" : {
			addeditWiki();
		} break;
		case "editwiki" : {
			addeditWiki((int)$_GET["wiki_id"]);
		} break;
		case "savewiki" : {
			if (!isset($_POST["undo"]))
				saveWiki();
			else
				wikiMain();
		} break;
		case "delwiki" : {
			deleteWiki();
		} break;
		case "setperm" : {
			showWikiPerm();
		} break;
		case "doneperm" : {
			doneWikiPerm();
		} break;
		case "selectwiki": {
			selectWiki();
		} break;

		case "show": {
			showWiki();
		} break;
		case "edit": {
			editWikiPage();
		} break;
		case "map": {
			wikiMap();
		} break;
		case "history": {
			wikiPageHistory();
		} break;
		case "search": {
			wikiSearch();
		} break;
	}

}


?>