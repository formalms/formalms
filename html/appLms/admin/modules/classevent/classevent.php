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

/**
 * @version  $Id:  $
 */
// ----------------------------------------------------------------------------
if(Docebo::user()->isAnonymous()) die("You can't access");

require_once($GLOBALS["where_lms"]."/lib/lib.classevent.php");


function classEventMain() {
	$res="";

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("classevent", "lms");


	$back_ui_url="index.php?modname=classevent&amp;op=main";

	$title_arr=array();
	$title_arr[$back_ui_url]=$lang->def("_CLASS_EVENT");
	$res.=getTitleArea($title_arr, "classevent");
	$res.="<div class=\"std_block\">\n";

	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");

	$tt=new TimeTable();


	$permissions=2;
	$size=(isset($_GET["size"]) ? $_GET["size"]:"max");

	addCss('windows');
	addCss('calendar_'.$size, "lms");
	//addAjaxJs();
	//addScriptaculousJs();
  YuiLib::load(
    array('my_window'=>'windows.js')
  );

	$GLOBALS['page']->add("\n".'<script type="text/javascript" src="'.$GLOBALS['where_lms_relative'].'/modules/calendar/calendar.js"></script>'."\n"
, 'page_head');

	$GLOBALS['page']->add("\n".'<script type="text/javascript" src="'.$GLOBALS['where_lms_relative'].'/modules/calendar/calendar_helper.js"></script>'."\n"
, 'page_head');
	
	require_once($GLOBALS["where_lms"]."/lib/lib.classroom.php");
	$cm=new ClassroomManager();
	$class_arr=$cm->getClassroomArray();

		
	$GLOBALS['page']->add('<script type="text/javascript">'
     //.' setup_url(\''.$GLOBALS['where_lms_relative'].'/modules/calendar/ajax.calendar.php?\',\'lms_classroom\',\'lms_classroom\'); '
     .' setup_url(\''.$GLOBALS['where_lms_relative'].'/ajax.server.php?mn=calendar&\',\'lms_classroom\',\'lms_classroom\'); '
	 .' setup_mode("edit",'.$permissions.',"'.Docebo::user()->getUserId().'"); '
     .'</script>', 'page_head');

	$width="90%";
	if ($size=="min") $width="200px";
	
	$res.="\n";
	$res.="<div class=\"std_block\" style=\"margin:auto;width:".$width.";\">\n";
	$res.="<p>".$lang->def("_SELECT_CLASSROOM").": <select name=\"classroom_selected\" id=\"classroom_selected\"  onChange=\"updateCalendar(cal.date);\"><option value=\"\">(".$lang->def("_SELECT_ALL").")</option>";
	while (list($key,$value)=each($class_arr)) {
		$res.="<option value=\"".htmlentities($key)."\">".htmlentities($value)."</option>";	
	}
	$res.="</select></p>";
	$res.="</div>\n";
	
	$res.="\n".'<div id="displayCalendar" style="margin: auto; clear: both; width:'.$width.'"></div>';


	$res.="</div>\n";
	$out->add($res);
	
	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=del]');
}


function addeditClassEvent($id=0) {
	$res="";

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("classevent", "lms");

	require_once(_base_.'/lib/lib.form.php');
	$form=new Form();
	$res="";

	$form_code="";
	$url="index.php?modname=classevent";

	if ($id == 0) {
		$form_code=$form->openForm("main_form", $url."&amp;op=save");
		$submit_lbl=$lang->def("_INSERT");
		$page_title=$lang->def("_ADD_ITEM");

		$location="";
	}
	else if ($id > 0) {
		$form_code=$form->openForm("main_form", $url."&amp;op=save");

		require_once($GLOBALS["where_lms"]."/admin/modules/classevent/lib.classevent.php");

		$clm=new ClassEventManager();
		$stored=$clm->getClassEventInfo($id);

		$location=$stored["location"];
		$submit_lbl=$lang->def("_EDIT");
		$page_title=$lang->def("_EDIT_ITEM").": ".$location;
	}


	$back_ui_url="index.php?modname=classevent&amp;op=main";

	$title_arr=array();
	$title_arr[$back_ui_url]=$lang->def("_CLASS_EVENT");
	$title_arr[]=$page_title;
	$res.=getTitleArea($title_arr, "classevent");
	$res.="<div class=\"std_block\">\n";
	$res.=getBackUi($back_ui_url, $lang->def( '_BACK' ));

	$res.=$form_code.$form->openElementSpace();

	$res.=$form->getTextfield($lang->def("_LOCATION"), "location", "location", 255, $location);

	$res.=$form->getHidden("id", "id", $id);


	$res.=$form->closeElementSpace();
	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $submit_lbl);
	$res.=$form->getButton('undo', 'undo', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();


	$res.=getBackUi($back_ui_url, $lang->def( '_BACK' ));
	$res.="</div>\n";
	$out->add($res);
}


function saveClassEvent() {
	require_once($GLOBALS["where_lms"]."/admin/modules/classevent/lib.classevent.php");

	$clm=new ClassEventManager();

	$clm->saveData($_POST);
	Util::jump_to("index.php?modname=classevent&op=main");
}


function deleteClassEvent() {
	$res="";

	include_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_lms"]."/admin/modules/classevent/lib.classevent.php");

	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("classevent", "lms");
	$clm=new ClassEventManager();

	$back_url="index.php?modname=classevent&op=main";


	if (isset($_POST["undo"])) {
		Util::jump_to($back_url);
	}
	else if (Get::req('conf_del', DOTY_INT, 0) == 1) {

		$clm->deleteClassEvent($_POST["id"]);

		Util::jump_to($back_url);
	}
	else {

		$id=(int)importVar("id");
		$stored=$clm->getClassEventInfo($id);
		$location=$stored["location"];

		$back_ui_url="index.php?modname=classevent&amp;op=main";

		$title_arr=array();
		$title_arr[$back_ui_url]=$lang->def("_CLASS_EVENT");
		$title_arr[]=$lang->def("_DEL").": ".$location;
		$res.=getTitleArea($title_arr, "classevent");
		$res.="<div class=\"std_block\">\n";
		$res.=getBackUi($back_ui_url, $lang->def( '_BACK' ));

		$form=new Form();

		$url="index.php?modname=classevent&amp;op=del";

		$res.=$form->openForm("main_form", $url);

		$res.=$form->getHidden("id", "id", $id);


		$res.=getDeleteUi(
		$lang->def('_AREYOUSURE'),
			'<span class="text_bold">'.$lang->def('_LOCATION').' :</span> '.$location.'<br />',
			false,
			'conf_del',
			'undo');

		$res.=$form->closeForm();
		$res.="</div>\n";
		$out->add($res);
	}
}


// ----------------------------------------------------------------------------

$op=(isset($_GET["op"]) ? $_GET["op"] : "main");

switch ($op) {

	case "main": {
			classEventMain();
	} break;

	case "add": {
		addeditClassEvent();
	} break;

	case "save": {
		if (!isset($_POST["undo"]))
			saveClassEvent();
		else
			classEventMain();
	} break;

	case "edit": {
		addeditClassEvent((int)$_GET["id"]);
	} break;

	case "del": {
		deleteClassEvent();
	} break;

}

?>