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

define('_REP_KEY_NAME',     'name');
define('_REP_KEY_CREATOR',  'creator');
define('_REP_KEY_CREATION', 'creation');
define('_REP_KEY_PUBLIC',   'public');
define('_REP_KEY_OPEN',     'open');
define('_REP_KEY_MOD',      'mod');
define('_REP_KEY_SCHED',    'sched');
define('_REP_KEY_REM',      'rem');

require_once(_lms_.'/lib/lib.report.php');

function reportList() {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang =& DoceboLanguage::createInstance('report', 'framework');
	$_SESSION['report_tempdata'] = array();
	$can_mod = checkPerm('mod', true);
	$acl_man = Docebo::aclm();
	$public_admin_mod = true;
	
	$query = "SELECT t1.*, t2.userid 
	FROM %lms_report_filter as t1 
		LEFT JOIN %adm_user as t2 ON t1.author=t2.idst
	WHERE t1.is_public = 1 OR t1.author = ".Docebo::user()->getId();
	
	$tb = new Table();
	$tb->initNavBar('ini', 'button');
	$col_type = array('','align_center','align_center','image');
	$col_content = array(
		$lang->def('_NAME'),
		$lang->def('_TAB_REP_CREATOR', 'report', 'framework'),
		$lang->def('_CREATION_DATE'),
		'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('REPORT_SHOW_RESULTS').'" title="'.$lang->def('REPORT_SHOW_RESULTS').'" />',	
	);
	if ($public_admin_mod && $can_mod) {
		$col_type[] = 'image';
		$col_content[] = '<img src="'.getPathImage().'standard/edit.png"  alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'"/>';
		$col_type[] = 'image';
		$col_content[] = '<img src="'.getPathImage().'standard/delete.png"  alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'"/>';
	}
	$tb->setColsStyle($col_type);
	$tb->addHead($col_content);

	$res = sql_query($query);
	if ($res) {
		
		while($row = sql_fetch_assoc($res)) {
			$id = $row['id_filter'];
			$opn_link = 
				'<a href="index.php?modname=public_report_admin&amp;op=view_report&amp;idrep='.$id.'" '.
				' title="'.$lang->def('REPORT_SHOW_RESULTS', 'report', 'framework').'">'.
				'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('REPORT_SHOW_RESULTS', 'report', 'framework').'" />'.
				'</a>';
			$tb_content = array(
				($row['author'] == 0 ? $lang->def($row['filter_name']) : $row['filter_name']),
				($row['author'] == 0 ? '<div class="align_center">-</div>' : $acl_man->relativeId($row['userid'])),
				Format::date($row['creation_date']),
				$opn_link
			);
			if ($public_admin_mod && $can_mod) {
				if ($row['author'] == Docebo::user()->getId())
					$tb_content[] =
						'<a href="index.php?modname=public_report_admin&amp;op=modify_name&amp;modid='.$id.'" '.
						' title="'.$lang->def('_MOD', 'report', 'framework').'">'.
						'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD', 'report', 'framework').'" />'.
						'</a>';
				else
					$tb_content[] = '';

				if ($row['author'] == Docebo::user()->getId())
					$tb_content[] =
						'<a href="index.php?modname=public_report_admin&amp;op=del_public_report&amp;idrep='.$id.'" '.
						' title="'.$lang->def('_DEL', 'report', 'framework').'">'.
						'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL', 'report', 'framework').'" />'.
						'</a>';
				else
					$tb_content[] = '';
			}
			$tb->addBody($tb_content);		
		}
	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=del_public_report]');

	if ($public_admin_mod && $can_mod) {
	
		$tb->addActionAdd('<a href="index.php?modname=public_report_admin&amp;op=create_name">'.
			'<img src="'.getPathImage().'standard/add.png" title="'.$lang->def('_NEW').'" /> '.
			$lang->def('_NEW').'</a>');
	}
	cout(
		getTitleArea($lang->def('_REPORT'))
		.'<div class="std_block">'
		.$tb->getTable()
		.'</div>'
	, 'content');
}

function viewReport() {
	checkPerm('view');
	
	$idrep = Get::req('idrep', DOTY_INT, 0);
	
	$out = &$GLOBALS['page'];
	$out->setWorkingZone('content');
	
	load_filter($idrep, true);
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.download.php');
	
	$lang =& DoceboLanguage::createInstance('report', 'framework');

	//$obj_report = openreport($idrep);
	if ($idrep!=false && $idrep>0) {
		
		$id_report = $idrep;
	} else {
		
		$id_report = $_SESSION['report_tempdata']['id_report'];
		if ($id_report!=false && $idrep>0)
		load_filter($idrep, true, false);
	}
	$query_report = "
	SELECT r.class_name, r.file_name, r.report_name, f.filter_name, f.filter_data, f.author
	FROM %lms_report AS r
		JOIN %lms_report_filter AS f
		ON ( r.id_report = f.id_report )
	WHERE f.id_filter = '".$idrep."'";
	$re_report = sql_query($query_report);

	list($class_name, $file_name, $report_name, $filter_name, $report_data) = sql_fetch_row($re_report);
	
	if(sql_num_rows($re_report) == 0) {
		reportlist();
		return;
	}
	$report_data = unserialize($report_data);
	
	require_once(_lms_.'/admin/modules/report/'.$file_name);
	$obj_report = new $class_name( $id_report );

	$obj_report->back_url = 'index.php?modname=public_report_admin&op=reportlist';
	$obj_report->jump_url = 'index.php?modname=public_report_admin&op=view_report&idrep='.$idrep;
	$start_url = 'index.php?modname=public_report_admin&op=reportlist';

	$temp = Get::req('dl', DOTY_STRING, false);
	if ($temp) {
		list($filter_name) = mysql_fetch_row(mysql_query("SELECT filter_name FROM ".$GLOBALS['prefix_lms']."_report_filter WHERE id_filter = '".$idrep."'"));
		$filename = 'report_'.$filter_name.'_'.date("d_m_Y");
		switch ($temp) {
			case 'htm': { sendStrAsFile($obj_report->getHTML(false, NULL), $filename.'.html'); } break;
			case 'csv': { sendStrAsFile($obj_report->getCSV(false, NULL), $filename.'.csv'); } break;
			case 'xls': { sendStrAsFile($obj_report->getXLS(false, NULL), $filename.'.xls'); } break;
		}
	}

	$report_info = $lang->def('_SHOW_REPORT_INFO', 'report', 'framework').getReportNameById($idrep);
	
	$GLOBALS['page']->add(
  	getTitleArea(	$lang->def('REPORT_SHOW_RESULTS', 'report', 'framework'), 'report', $lang->def('_REPORT_PRINTTABLE', 'report', 'framework'))
  					.'<div class="std_block">', 'content');
  	
	if (Get::req('no_show_repdownload', DOTY_INT, 0) <= 0) {
		$GLOBALS['page']->add(	getBackUi($obj_report->back_url, $lang->def('_BACK', 'report', 'framework'), 'content')
  				.getInfoUi($report_info), 'content');
  			
		$export_url = 'index.php?modname=public_report_admin&amp;op=view_report&amp;idrep='.$idrep;
		$GLOBALS['page']->add(
				'<p class="export_list">'.
				'<a class="export_htm" href="'.$export_url.'&amp;dl=htm">'.$lang->def('_EXPORT_HTML', 'report', 'framework').'</a>&nbsp;'.
				'<a class="export_csv" href="'.$export_url.'&amp;dl=csv">'.$lang->def('_EXPORT_CSV', 'report', 'framework').'</a>&nbsp;'.
				'<a class="export_xls" href="'.$export_url.'&amp;dl=xls">'.$lang->def('_EXPORT_XLS', 'report', 'framework').'</a>'.
				'</p><br />', 'content');
	}
	
	$GLOBALS['page']->add(Form::openForm('report_form', $obj_report->jump_url), 'content');
	// css -----------------------------------------------------------
	$GLOBALS['page']->add(	"\n".'<link href="'.getPathTemplate('lms').'style/report/style_report_user.css" rel="stylesheet" type="text/css" />'."\n", 'page_head');

	$obj_report->show_results($report_data['columns_filter_category'], $report_data);
	
	$out->add(Form::closeForm(), 'content');
	$out->add('</div>', 'content');
}

function load_filter($id, $tempdata=false, $update=false)
{
	
	if ($id==false) return;

	require_once(_lms_.'/lib/lib.report.php');
	
	$row = sql_fetch_assoc(sql_query("SELECT * FROM %lms_report_filter WHERE id_filter=$id"));
	$temp = unserialize($row['filter_data']);
	if ($tempdata) $_SESSION['report_tempdata'] = $temp;
	$_SESSION['report'] = $temp;

	$_SESSION['report_saved'] = true;
	$_SESSION['report_saved_data'] = array('id' => $id, 'name' => getReportNameById($id));

	if ($update) $_SESSION['report_update'] = $id;
	else $_SESSION['report_update'] = false;
}

function openreport($idrep = false) {
	
	if ($idrep!=false && $idrep>0) {
		
		$id_report = $idrep;
	} else {
		
		$id_report = $_SESSION['report_tempdata']['id_report'];
		if ($id_report!=false && $idrep>0)
		load_filter($idrep, true, false);
	}
	$query_report = "
	SELECT r.class_name, r.file_name, r.report_name, f.filter_name, f.filter_data, f.author
	FROM %lms_report AS r
		JOIN %lms_report_filter AS f
		ON ( r.id_report = f.id_report )
	WHERE f.id_filter = '".$idrep."'";
	$re_report = sql_query($query_report);

	list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);
	
	if(sql_num_rows($re_report) == 0) {
		reportlist();
		return;
	}
	require_once(_lms_.'/admin/modules/report/'.$file_name);
	$obj_report = new $class_name( $id_report );

	return $obj_report;
}


//------------------------------------------------------------------------------
//---------- public admin mod --------------------------------------------------
//------------------------------------------------------------------------------

function get_update_info() {
	$output = '';
	$lang =& DoceboLanguage::createInstance('report');
	if (isset($_SESSION['report_update'])) {
		$ref =& $_SESSION['report_update'];
		if (is_int($ref) && $ref>0) {
			$output .= $lang->def('_REPORT_MODIFYING').getReportNameById($_SESSION['report_update']);
		}
	}
	return $output;
}


function mod_report_name() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	//require_once($GLOBALS['where_framework'].'/lib/lib.json.php');
	require_once($GLOBALS['where_lms'].'/admin/modules/report/class.report.php'); //reportbox class
	require_once($GLOBALS['where_lms'].'/lib/lib.report.php');
	//require_once('report_categories.php');
	load_categories();

	$lang =& DoceboLanguage::createInstance('report');

	$idrep = Get::req('modid', DOTY_INT, false);
	//if (!idrep) Util::jump_to(initial page ... )

	$page_title = getTitleArea(array(
			'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
			$lang->def('_REPORT_MOD_NAME')
		), 'report'/*, $lang->def('_ALT_REPORT')*/);
	$GLOBALS['page']->add($page_title.'<div class="std_block">', 'content');

	$info = get_update_info();
	if($info) $GLOBALS['page']->add( getInfoUi($info), 'content');

	$box = new ReportBox('report_modify_name');

	$box->title = $lang->def('_REPORT_MOD_NAME');
	$box->description = $lang->def('_REPORT_MODNAME_DESC');

	$box->body =
	Form::openForm('repcat_form', 'index.php?modname=public_report_admin&amp;op=modify_rows&amp;modid='.$idrep).
	Form::getHidden('mod_name', 'mod_name', 1);
	
	//$json = new Services_JSON();
	//$rdata =& getFilterData($idrep);
	//Form::getHidden('report_filter_data', 'report_filter_data', $json->encode($rdata));

	$box->body .= Form::getTextField(
		$lang->def('_MOD_REPORT_NAME'), //$label_name,
		'report_name',
		'report_name',
		'200', getReportNameById($idrep));

	$box->body .=
	//Form::closeElementSpace().
	Form::openButtonSpace().
	Form::getButton( '', '', $lang->def('_FORWARD'), false).
	Form::closeButtonSpace().
	Form::closeForm();

	$GLOBALS['page']->add($box->get(), 'content');

	/*$lang->def('_REPORT_SCHEDMAN');$lang->def('_REPORT_SCHEDMAN_DESC');*/

	$GLOBALS['page']->add( '</div>', 'content');
}


function mod_report_rows() {
	checkPerm('mod');

	$lang =& DoceboLanguage::createInstance('report', 'framework');
	$ref =& $_SESSION['report_tempdata'];

	$idrep = Get::req('modid', DOTY_INT, false);

	if (Get::req('mod_name', DOTY_INT, 0)==1) {
		$ref['report_name'] = Get::req('report_name', DOTY_STRING, false);
	}
	
	$obj_report = openreport($idrep);

	$obj_report->back_url = 'index.php?modname=public_report_admin&op=modify_name&modid='.$idrep;
	$obj_report->jump_url = 'index.php?modname=public_report_admin&op=modify_rows&modid='.$idrep;
	$obj_report->next_url = 'index.php?modname=public_report_admin&op=modify_cols&modid='.$idrep;

	$page_title = getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=report&op=modify_name&modid='.$idrep => $lang->def('_REPORT_MOD_NAME'),
			$lang->def('_REPORT_MOD_ROWS')
		), 'report'/*, $lang->def('_ALT_REPORT')*/);

	/*$info = get_update_info();
	if($info) getInfoUi($info) );*/

	if ($obj_report->usestandardtitle_rows) {
		$GLOBALS['page']->add($page_title.'<div class="std_block">', 'content');//.getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content'));
		$info = get_update_info();
		if($info) $GLOBALS['page']->add( getInfoUi($info), 'content');
		//$GLOBALS['page']->add(Form::openForm('user_report_rows_courses_mod', $obj_report->jump_url));
	} else {
		//this is just used to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_rows_filter();

	if ($obj_report->usestandardtitle_rows) {
		//$GLOBALS['page']->add(Form::closeForm());
		$GLOBALS['page']->add('</div>', 'content'); //close title area
	}
}


function mod_report_cols() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$ref =& $_SESSION['report_tempdata']['columns_filter_category'];
	if (isset($_POST['columns_filter']))
	$ref = $_POST['columns_filter'];

	$idrep = Get::req('modid', DOTY_INT, false);
	$lang =& DoceboLanguage::createInstance('report');

	$obj_report = openreport($idrep);
	$obj_report->back_url = 'index.php?modname=public_report_admin&op=modify_rows&modid='.$idrep;
	$obj_report->jump_url = 'index.php?modname=public_report_admin&op=modify_cols&modid='.$idrep;
	$obj_report->next_url = 'index.php?modname=public_report_admin&op=modify_save&modid='.$idrep;

	//page title
	$page_title = getTitleArea(array(
		  'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
		  'index.php?modname=public_report_admin&op=modify_name&modid='.$idrep => $lang->def('_REPORT_MOD_NAME'),
			'index.php?modname=public_report_admin&op=modify_rows&modid='.$idrep => $lang->def('_REPORT_MOD_ROWS'),
			$lang->def('_REPORT_MOD_COLUMNS')
		))
	.'<div class="std_block">';

	/*$info = get_update_info();
	if($info) $GLOBALS['page']->add( getInfoUi($info) );*/

	if($obj_report->useStandardTitle_Columns()) {
		$GLOBALS['page']->add($page_title, 'content');
		$info = get_update_info();
		if($info) $GLOBALS['page']->add( getInfoUi($info), 'content');
		$GLOBALS['page']->add(Form::openForm('user_report_columns_courses_mod', str_replace('&', '&amp;', $obj_report->jump_url)), 'content');
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$query = "SELECT filter_data FROM ".$GLOBALS['prefix_lms']."_report_filter WHERE id_filter='".(int)$idrep."'";
	list($old_data_serialized) = mysql_fetch_row(mysql_query($query));
	$old_data = unserialize($old_data_serialized);
	$category = $old_data['columns_filter_category'];
	$_SESSION['report_tempdata']['columns_filter_category'] = $category;
	unset($old_data);
	unset($old_data_serialized);

	$output = $obj_report->get_columns_filter($category);//($_SESSION['report_tempdata']['columns_filter_category']);
	$GLOBALS['page']->add($output, 'content');

	if ($obj_report->useStandardTitle_Columns()) {
		$GLOBALS['page']->add(Form::openButtonSpace(), 'content');
		$GLOBALS['page']->add(
			Form::getBreakRow()
			.Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
			.Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
			.Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
			.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report')), 'content'
		);
		$GLOBALS['page']->add(Form::closeButtonSpace(), 'content');
		$GLOBALS['page']->add(Form::closeForm(), 'content');
		$GLOBALS['page']->add('</div>', 'content'); //close std_block div
	}
}


function mod_report_save() {
	
}

/*
function getFilterData($idrep) {
	if (!isset($GLOBALS['report_filter_data'])) {
		$req_data = Get::req('report_filter_data', false, false);
		if ($req_data != false) {
			require_once($GLOBALS['where_framework'].'/lib/lib.json.php');
			$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
			$GLOBALS['report_filter_data'] = $json->decode($req_data);
		} else {
			$query = "SELECT filter_data FROM ".$GLOBALS['prefix_lms']."_report_filter WHERE id_filter='".(int)$idrep."'";
			$res = mysql_query($query);
			if ($res && mysql_num_rows($res)>0) {
				list($data) = mysql_fetch_row($res);
				$GLOBALS['report_filter_data'] = unserialize($data);
			} else {
				//...
			}
		}
		return $GLOBALS['report_filter_data'];
	}
}
*/

//------------------------------------------------------------------------------
//---------- report creation ---------------------------------------------------
//------------------------------------------------------------------------------


function create_report_name() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/admin/modules/report/class.report.php'); //reportbox class
	require_once($GLOBALS['where_lms'].'/lib/lib.report.php');
	require_once(_base_.'/lib/lib.form.php');
	
	load_categories();
	$lang =& DoceboLanguage::createInstance('report');

	$step_index = 0;
	//$GLOBALS['page']->add( get_page_title($step_index) );
	$_SESSION['report_tempdata'] = array();

	$page_title = getTitleArea(array(
			'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
			$lang->def('_NEW')
		), 'report');
	$GLOBALS['page']->add($page_title.'<div class="std_block">', 'content');

	$error = Get::req('err', DOTY_STRING, false);
	switch ($error) {
		case 'noname': {
			$GLOBALS['page']->add( getErrorUi($lang->def('_REPORT_NONAME')) );
		} break;
	}

	$temp = array();
	foreach ($GLOBALS['report_categories'] as $key=>$value) {
		$temp[$key] = $lang->def($value );
	}

	$GLOBALS['page']->add(	Form::openForm('repcat_form', 'index.php?modname=public_report_admin&amp;op=create_rows').
							Form::getHidden('set_category', 'set_category', 1).
							Form::getTextField($lang->def('_NAME'),'report_name','report_name','200').
							Form::getDropDown($lang->def('_SELECT_REPORT_CATEGORY'), '', 'id_report', $temp).
							Form::openButtonSpace().
							Form::getButton( '', 'cat_forward', $lang->def('_NEXT'), false).
							Form::getButton( '', 'cat_undo', $lang->def('_UNDO'), false).
							Form::closeButtonSpace().
							Form::closeForm(), 'content');

	/*$lang->def('_REPORT_SCHEDMAN');$lang->def('_REPORT_SCHEDMAN_DESC');*/

	$GLOBALS['page']->add( '</div>', 'content');
}


function create_report_rows() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	if (Get::req('cat_undo', DOTY_MIXED, false)) Util::jump_to('index.php?modname=public_report_admin&op=reportlist');

	$lang =& DoceboLanguage::createInstance('report');
	$ref =& $_SESSION['report_tempdata'];

	if (Get::req('set_category', DOTY_INT, 0)==1) {
		if (Get::req('report_name', DOTY_STRING, '')=='') Util::jump_to('index.php?modname=report&op=report_category&err=noname');
		$ref['id_report'] = Get::req('id_report', DOTY_ALPHANUM, false);
		$ref['report_name'] = Get::req('report_name', DOTY_STRING, false);
	}

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=public_report_admin&op=create_name';
	$obj_report->jump_url = 'index.php?modname=public_report_admin&op=create_rows';
	$obj_report->next_url = 'index.php?modname=public_report_admin&op=create_type';

	$page_title = getTitleArea(array(
		'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
	  'index.php?modname=public_report_admin&amp;op=create_name' => $lang->def('_NEW'),//$obj_report->report_name,
			$lang->def('_REPORT_SEL_ROWS')
		), 'report'/*, $lang->def('_ALT_REPORT')*/);

	if ($obj_report->usestandardtitle_rows) {
		$GLOBALS['page']->add($page_title.'<div class="std_block">', 'content');//.getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content'));
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_rows_filter();

	if ($obj_report->usestandardtitle_rows) {
		$GLOBALS['page']->add('</div>', 'content'); //close title area
	}
}


function create_report_type() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('report', 'framework');
	$obj_report = openreport();
	$temp = $obj_report->get_columns_categories();
	$GLOBALS['page']->add(getTitleArea(array(
			'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=public_report_admin&amp;op=create_name' => $lang->def('_NEW'),//$obj_report->report_name,
			'index.php?modname=public_report_admin&amp;op=create_rows' => $lang->def('_REPORT_SEL_ROWS'),
				$lang->def('_REPORT_SEL_COLUMNS')
			))
		.'<div class="std_block">', 'content');

	$GLOBALS['page']->add(Form::openForm('choose_category_form','index.php?modname=public_report_admin&amp;op=create_cols'), 'content');
	
	$i = 1;
	foreach ($temp as $key=>$value) {
		$GLOBALS['page']->add(Form::getRadio( $i.') '.$value, 'sel_columns_'.$key, 'columns_filter', $key, ($i==1)), 'content');
		$i++;
	}
	$GLOBALS['page']->add(	Form::openButtonSpace().
							Form::getButton( '', '', $lang->def('_CONFIRM'), false).
							Form::closeButtonSpace().
							Form::closeForm(), 'content');
}


function create_report_cols() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$ref =& $_SESSION['report_tempdata']['columns_filter_category'];
	if (isset($_POST['columns_filter']))
	$ref = $_POST['columns_filter'];

	$lang =& DoceboLanguage::createInstance('report');

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=public_report_admin&op=create_type';
	$obj_report->jump_url = 'index.php?modname=public_report_admin&op=create_cols';
	$obj_report->next_url = 'index.php?modname=public_report_admin&op=create_save';

	//page title
	$page_title = getTitleArea(array(
			'index.php?modname=public_report_admin&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=public_report_admin&amp;op=create_name' => $lang->def('_NEW'),
			'index.php?modname=public_report_admin&amp;op=create_rows' => $lang->def('_REPORT_SEL_ROWS'),
			'index.php?modname=public_report_admin&amp;op=create_type' => $lang->def('_REPORT_SEL_COLUMNS'),
			$lang->def('_REPORT_COLUMNS')
		))
	.'<div class="std_block">';
	//.  	getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content');

	if($obj_report->useStandardTitle_Columns()) {
		$GLOBALS['page']->add($page_title, 'content');
		$GLOBALS['page']->add(Form::openForm('report_columns_form', str_replace('&', '&amp;', $obj_report->jump_url)), 'content');
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$output = $obj_report->get_columns_filter($_SESSION['report_tempdata']['columns_filter_category']);
	$GLOBALS['page']->add($output, 'content');

	if ($obj_report->useStandardTitle_Columns()) {
		$GLOBALS['page']->add(
			Form::openButtonSpace()
			.Form::getBreakRow()
			.Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
			.Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
			.Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
			.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report'))
			.Form::closeButtonSpace()
			.Form::closeForm(), 'content');
		$GLOBALS['page']->add('</div>', 'content'); //close std_block div
	}
}


function common_report_save() {
	checkPerm('mod');

	$ref =& $_SESSION['report_tempdata'];
	$report_id = $ref['id_report'];
	$report_name = $ref['report_name'];
	$nosave = Get::req('nosave', DOTY_INT, 0);
	$show = Get::req('show', DOTY_INT, 0);
	$idrep = Get::req('modid', DOTY_INT, false);

	if ($nosave>0) {
		Util::jump_to('index.php?modname=public_report_admin&op=view_report&nosave=1'.($idrep ? '&modid='.$idrep : ''));
	}

	if (isset($_SESSION['report_update'])  || $idrep) {
		$save_ok = report_update($idrep, $report_name, $ref, true);
		if ($show) {
			Util::jump_to('index.php?modname=public_report_admin&op=view_report&idrep='.$idrep);
		} else {
			Util::jump_to('index.php?modname=public_report_admin&op=reportlist&modrep='.($save_ok ? 'true' : 'false'));
		}
	} else {
		$save_ok = report_save($report_id, $report_name, $ref, true);
		if ($show) {
			Util::jump_to('index.php?modname=public_report_admin&op=view_report&idrep='.$save_ok);
		} else {
			Util::jump_to('index.php?modname=public_report_admin&op=reportlist&saverep='.($save_ok ? 'true' : 'false'));
		}
	}
}


//------------------------------------------------------------------------------



function delete_public_report() {
	require_once($GLOBALS['where_lms'].'/lib/lib.report.php');
	$id_filter = Get::req('idrep', DOTY_INT, -1);
	if ($id_filter <= 0) $result = false;
	else $result = report_delete_filter($id_filter);
	Util::jump_to('index.php?modname=public_report_admin&op=reportlist');
}



function publicReportAdminDispatch($op) {
	
	switch($op) {
		case "reportlist" : {
			reportList();
		};break;
		case "view_report" : {
			viewReport();
		};break;

	//---- report modifying options ----------------------------------------------
		case "modify_name" : {
			mod_report_name();
		} break;

		case "modify_rows": {
			mod_report_rows();
		} break;

		case "modify_cols": {
			mod_report_cols();
		} break;

		case "modify_save": {
			common_report_save();
		} break;

	//---- report creating options -----------------------------------------------
		case "create_name": {
			create_report_name();
		} break;

		case "create_rows": {
			create_report_rows();
		} break;

		case "create_type": {
			create_report_type();
		} break;

		case "create_cols": {
			create_report_cols();
		} break;

		case "create_save": {
			common_report_save();
		} break;

	//----------------------------------------------------------------------------
		case "del_public_report": {
			delete_public_report();
		}

	}
	
}
?>