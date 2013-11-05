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

function regional_settings() {
	checkPerm('view');
	/*funAdminAccess('lang','OP');
	$newPerm = funAdminAccess('addlang', 'NEW', true);
	$modPerm = funAdminAccess('modlang', 'MOD', true);
	$remPerm = funAdminAccess('dellang', 'REM', true);*/
	$write_perm=true;
	$mod_perm=true;
	$rem_perm=true;


	require_once(_base_."/lib/lib.table.php");

	$out=& $GLOBALS['page'];
	$lang=& DoceboLanguage::createInstance('admin_regset', 'framework');
	$regset=new RegionalSettingsManager();

	$out->setWorkingZone("content");

	$out->add(getTitleArea($lang->def("_REGIONAL_SETTINGS"), "regset"));
	$out->add("<div class=\"std_block\">\n");

	$ini=importVar("ini", true, 0);

	$arr=$regset->getAllRegions();


	$table=new Table(Get::sett('visuItem'));
	$out->add($table->OpenTable(""));

	$head=array($lang->def("_REGION_CODE"), $lang->def("_DESCRIPTION"),
		'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def("_MOD").'" title="'.$lang->def("_MOD").'" />',
		'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def("_DEL").'" title="'.$lang->def("_DEL").'" />');
	$head_type=array('', '', 'img', 'img');


	$out->add($table->WriteHeader($head, $head_type));

	$tot=(count($arr) < ($ini+Get::sett('visuItem'))) ? count($arr) : $ini+Get::sett('visuItem');
	for($i=$ini; $i<$tot; $i++ ) {
		$rowcnt=array();
		$rowcnt[]=$arr[$i];
		$rowcnt[]=$regset->getRegionInfo($arr[$i], "description");
		if ($mod_perm) {
			$btn ="<a href=\"index.php?modname=regional_settings&amp;op=editregion&amp;id=".$arr[$i]."\">";
			$btn.="<img src=\"".getPathImage()."standard/edit.png\" ";
			$btn.="alt=\"".$lang->def("_MOD")."\" title=\"".$lang->def("_MOD")." ".$arr[$i]."\" />";
			$btn.="</a>\n";
			$rowcnt[]=$btn;
		}
		else
			$rowcnt[]="&nbsp;";

		if (($rem_perm) && (!$regset->getRegionInfo($arr[$i], "default"))) {
			$btn ="<a href=\"\">";
			$btn.="<img src=\"".getPathImage()."standard/delete.png\" ";
			$btn.="alt=\"".$lang->def("_DEL")."\" title=\"".$lang->def("_DEL")." ".$arr[$i]."\" />";
			$btn.="</a>\n";
			$rowcnt[]=$btn;
		}
		else
			$rowcnt[]="&nbsp;";

		$out->add($table->writeRow($rowcnt));
	}

	if($write_perm) {
		$out->add($table->WriteAddRow('<a href="index.php?modname=regional_settings&amp;op=addregion">
		 <img src="'.getPathImage().'standard/add.png" title="'.$lang->def( '_ADD' ).'" alt="'.$lang->def( '_ADD' ).'" /> '.
		 $lang->def( '_ADD' ).'</a>'));
	}

	$out->add($table->CloseTable());

	$out->add($table->WriteNavBar('',
								'index.php?modname=regional_settings&amp;op=regset&amp;ini=',
								$ini,
								count($arr)));


	$out->add("</div>\n");

}

function regset_editregion($region_id=FALSE) {
	checkPerm('view');
	// [TODO] check write permissions
	require_once(_base_.'/lib/lib.form.php');

	$out=& $GLOBALS['page'];
	$lang=& DoceboLanguage::createInstance('admin_regset', 'framework');
	$form=new Form();

	$out->setWorkingZone("content");

	$out->add(getTitleArea($lang->def("_REGIONAL_SETTINGS"), "regset"));
	$out->add("<div class=\"std_block\">\n");


	if ($region_id == "")
		$region_id=FALSE;

	$data=array();

	if ($region_id === FALSE) {  // Add
		$out->add($form->openForm("regset_form", "index.php?modname=regional_settings&amp;op=insnew"));
		$submit_lbl=$lang->def("_INSERT");
		$data["region_id"]=$lang->def("_REGION_CODE");
		$data["region_desc"]=$lang->def("_DESCRIPTION");
		$data["browsercode"]=$lang->def("_BROWSER_CODE");
	}
	else {  // Edit
		$out->add($form->openForm("regset_form", "index.php?modname=regional_settings&amp;op=updregion"));

		$regset=new RegionalSettingsManager();
		$data=$regset->getRegionSettings($region_id);
		$data["region_id"]=$region_id;
		$data["region_desc"]=$regset->getRegionInfo($region_id, "description");
		$data["browsercode"]=$regset->getRegionInfo($region_id, "browsercode");

		$submit_lbl=$lang->def("_MOD");
	}


	$out->add($form->openElementSpace());


	$out->add($form->getTextfield($lang->def("_REGION_CODE"), "region_id", "region_id", 100, $data["region_id"]));
	$out->add($form->getTextfield($lang->def("_DESCRIPTION"), "region_desc", "region_desc", 255, $data["region_desc"]));
	$out->add($form->getTextfield($lang->def("_BROWSER_CODE"), "browsercode", "browsercode", 255, $data["browsercode"]));

	displaySettingFields($out, $lang, $form, $data);


	if ($region_id !== FALSE)
		$out->add($form->getHidden("old_region_id", "old_region_id", $region_id));

	$out->add($form->closeElementSpace());
	$out->add($form->openButtonSpace());
	$out->add($form->getButton('save', 'save', $submit_lbl));
	$out->add($form->getButton('undo', 'undo', $lang->def('_UNDO')));
	$out->add($form->closeButtonSpace());
	//"<br /><br /><input class=\"button\" type=\"submit\" value=\"".$submit_lbl."\" />\n");
	$out->add($form->closeForm());

	$out->add("</div>\n");

}


function regset_insnew() {
	checkPerm('view');
// [TODO] check write permissions

	$regset=new RegionalSettingsManager();
	$regset->addNewRegion($_POST);

	Util::jump_to("index.php?modname=regional_settings&op=regset");
}


function regset_updregion() {
	checkPerm('view');
// [TODO] check mod. permissions

	$regset=new RegionalSettingsManager();
	$regset->updateRegion($_POST);

	Util::jump_to("index.php?modname=regional_settings&op=regset");
}



function displaySettingFields(& $out, & $lang, & $form, $data) {
	checkPerm('view');
	$date_format_arr=array();
	$date_format_arr["d_m_Y"]=$lang->def("_DATE_FORMAT_DMY_LONG");
	$date_format_arr["m_d_Y"]=$lang->def("_DATE_FORMAT_MDY_LONG");
	$date_format_arr["Y_m_d"]=$lang->def("_DATE_FORMAT_YMD_LONG");
	$date_format_arr["d_m_y"]=$lang->def("_DATE_FORMAT_DMY_SHORT");
	$date_format_arr["m_d_y"]=$lang->def("_DATE_FORMAT_MDY_SHORT");
	if (isset($data["date_format"]))
		$date_format_sel=$data["date_format"];
	else
		$date_format_sel=NULL;

	$out->add($form->getDropdown($lang->def("_DATE_FORMAT"), "date_format", "date_format", $date_format_arr, $date_format_sel));
	// [TODO] ..if not in array and not null
	// date_format_array then custom = value -> fill the custom input field and select custom in dropdown

	// ------------------------------------------------------------------------

	$date_sep_arr=array();
	$date_sep_arr["-"]="-";
	$date_sep_arr["/"]="/";
	if (isset($data["date_sep"]))
		$date_sep_sel=$data["date_sep"];
	else
		$date_sep_sel=NULL;

	$out->add($form->getDropdown($lang->def("_DATE_SEP"), "date_sep", "date_sep", $date_sep_arr, $date_sep_sel));

	// ------------------------------------------------------------------------

	$time_format_arr=array();
	$time_format_arr["H_i"]=$lang->def("_TIME_FORMAT_24");
	$time_format_arr["h_i.a"]=$lang->def("_TIME_FORMAT_12");
	$time_format_arr["H_i_s"]=$lang->def("_TIME_FORMAT_24_SEC");
	$time_format_arr["h_i_s.a"]=$lang->def("_TIME_FORMAT_12_SEC");
	if (isset($data["date_sep"]))
		$time_format_sel=$data["time_format"];
	else
		$time_format_sel=NULL;

	$out->add($form->getDropdown($lang->def("_TIME_FORMAT"), "time_format", "time_format", $time_format_arr, $time_format_sel));
	// [TODO] custom as for date .. custom will override also the separator setting

	// ------------------------------------------------------------------------

}



function regsetDispatch( $op ) {
	
	switch($op) {
		
		case "regset": {
			regional_settings();
		} break;
		
		case "addregion": {
			regset_editregion();
		} break;
		
		case "insnew": {
			if (isset($_POST["undo"]))
				regional_settings();
			else
				regset_insnew();
		} break;
		
		case "editregion": {
			regset_editregion($_GET["id"]);
		} break;
		
		case "updregion": {
			if (isset($_POST["undo"]))
				regional_settings();
			else
				regset_updregion();
		} break;
	}
}


?>
