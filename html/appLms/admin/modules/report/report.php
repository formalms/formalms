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

if(Docebo::user()->isAnonymous()) die("You can't access");

require_once(_base_.'/lib/lib.form.php');
require_once(_lms_.'/lib/lib.report.php');
require_once(_lms_.'/admin/modules/report/report_schedule.php');


function _encode(&$data) { return serialize($data); } //{ return urlencode(Util::serialize($data)); }
function _decode(&$data) { return unserialize($data); } //{ return Util::unserialize(urldecode($data)); }

function unload_filter($temp=false) {
	$_SESSION['report']=array();
	if ($temp) $_SESSION['report_tempdata']=array();
	if (isset($_SESSION['report_update'])) unset($_SESSION['report_update']);

	$_SESSION['report_saved'] = false;
	$_SESSION['report_saved_data'] = array('id' => '', 'name' => '');
}

function load_filter($id, $tempdata=false, $update=false) {
	
	if ($id==false) return;
    checkReport($id);
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

function openreport($idrep=false) {
	$lang =& DoceboLanguage::createInstance('report');

	if ($idrep!=false && $idrep>0)
	$id_report = $idrep;
	else {
		$id_report = $_SESSION['report_tempdata']['id_report'];

		if ($id_report!=false && $idrep>0)
		load_filter($idrep, true, false);
	}
	$query_report = "
	 SELECT class_name, file_name, report_name
	 FROM %lms_report
	 WHERE id_report = '".$id_report."'";
	$re_report = sql_query($query_report);

	if(sql_num_rows($re_report) == 0) {
		reportlist();
		return;
	}
	list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);
    //when file name set use old style
    if ($file_name){
	if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/admin/modules/report/'.$file_name) && Get::cfg('enable_customscripts', false) == true ){
	    require_once(_base_ . '/customscripts/'._folder_lms_.'/admin/modules/report/'.$file_name);
	} else {
		require_once(_lms_.'/admin/modules/report/'.$file_name);
	}
	
	$obj_report = new $class_name( $id_report );
    }else{
        $pg = new PluginManager('Report');
        $obj_report = $pg->get_plugin(strtolower($class_name),array($id_report));
        }
	return $obj_report;
}

function get_update_info() {
	$output = '';
	/*$lang =& DoceboLanguage::createInstance('report');
	if (isset($_SESSION['report_update'])) {
		$ref =& $_SESSION['report_update'];
		if (is_int($ref) && $ref>0) {
			//$output .= $lang->def('_REPORT_MODIFYING').getReportNameById($_SESSION['report_update']);
		}
	}*/
	return $output;
}

//******************************************************************************

$lang =& DoceboLanguage::createInstance('report');

define('_REP_KEY_NAME',     'name');
define('_REP_KEY_CREATOR',  'creator');
define('_REP_KEY_CREATION', 'creation');
define('_REP_KEY_PUBLIC',   'public');
define('_REP_KEY_OPEN',     'open');
define('_REP_KEY_MOD',      'mod');
define('_REP_KEY_SCHED',    'sched');
define('_REP_KEY_REM',      'rem');

function get_report_table($url='') {
	checkPerm('view');
	$can_mod = checkPerm('mod'  , true);

	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');

	$acl_man =& Docebo::user()->getACLManager();
	$level = Docebo::user()->getUserLevelId(Docebo::user()->getIdst());
	
	$lang =& DoceboLanguage::createInstance('report');
	$output = '';

	$is_admin = ( ($level==ADMIN_GROUP_GODADMIN || $level==ADMIN_GROUP_ADMIN) ? true : false);

	if ($level==ADMIN_GROUP_GODADMIN || $can_mod) {//if ($can_mod) {
		cout('<script type="text/javascript">
		var _FAILURE = "error";
		var ajax_path = "'.Get::rel_path('lms').'/ajax.adm_server.php?mn=report&plf=lms";

		function public_report(o, id_rep) {
			o.disabled=true; //no more operations allowed on the checkbox while ajaxing

			var val_el=document.getElementById("enable_value_"+id_rep);
			var value=val_el.value;

			var data = "&op=public_rep&id="+id_rep+"&val="+value;
			var objAjax = YAHOO.util.Connect.asyncRequest("POST", ajax_path+data, {
			success:function(t) {
				var temp=o.src;
				if (value==1)	{ o.src=temp.replace("unpublish.png", "publish.png"); val_el.value=0; }
				if (value==0)	{ o.src=temp.replace("publish.png", "unpublish.png"); val_el.value=1; }
					o.disabled=false;
				},
			failure:function(t) {
					o.disabled=false;
					alert(_FAILURE); //...
				} });
			}

			function setReportFilter() {
				var el = document.createElement("INPUT");
				el.type = "hidden";
				el.name = "search";
				el.value = "1";
				var form = YAHOO.util.Dom.get("report_searchbox_form");
				if (form) {
					form.appendChild(el);
					form.submit();
				}
			}
		</script>', 'page_head');
	}


	//filter by author
	YuiLib::load();
	$current_user = $acl_man->getUser(Docebo::user()->getIdst(), false);

	//dropdown data arrays
	$authors = array(
		0 => '('.$lang->def('_ALL').')', //recycle text key
		$current_user[ACL_INFO_IDST] => $acl_man->relativeId($current_user[ACL_INFO_USERID])
	);
	$query = "SELECT u.idst, u.userid FROM %lms_report_filter as r JOIN %adm_user as u ON (r.author=u.idst) WHERE u.idst<>".Docebo::user()->getIdst()." ORDER BY u.userid";
	$res = sql_query($query);
	while ($row = sql_fetch_assoc($res)) { $authors[$row['idst']] = $acl_man->relativeId($row['userid']); }

	$arr_report_types = array(
		0 => '('.$lang->def('_ALL').')'
	);

	//initializa session variable for filters
	if (!isset($_SESSION['report_admin_filter'])) {
		$_SESSION['report_admin_filter'] = array(
			'author' => 0,//array_key_exists(Docebo::user()->getIdst(), $authors) ? Docebo::user()->getIdst() : 0,
			'name' => '',
			'type' => 0
		);
	}

	if (Get::req('search', DOTY_MIXED, false) !== false) {
		$_SESSION['report_admin_filter']['author'] = Get::req('filter_author', DOTY_INT, (int)$_SESSION['report_admin_filter']['author']);
		$_SESSION['report_admin_filter']['name'] = Get::req('filter_name', DOTY_STRING, $_SESSION['report_admin_filter']['name']);
		$_SESSION['report_admin_filter']['type'] = Get::req('filter_type', DOTY_INT, (int)$_SESSION['report_admin_filter']['type']);
	}

	if (Get::req('reset', DOTY_MIXED, false) !== false) {
		$_SESSION['report_admin_filter']['author'] = 0;
		$_SESSION['report_admin_filter']['name'] = '';
		$_SESSION['report_admin_filter']['type'] = 0;
	}

	$dropdown_onclick = 'onchange="javascript:setReportFilter();"';

	$output .= Form::openForm('report_searchbox_form', 'index.php?modname=report&op=reportlist&of_platform=lms', false, 'POST');
  $output .= Form::getHidden('op', 'op', 'reportlist');
  $output .= Form::getHidden('modname', 'modname', 'report');
	$output .= '<div class="quick_search_form">
			<div>
				<div class="simple_search_box" id="report_searchbox_simple_filter_options" style="display: block;">'
						.Form::getInputDropdown('dropdown', 'report_searchbox_filter_author', 'filter_author', $authors, $_SESSION['report_admin_filter']['author'], $dropdown_onclick)
						."&nbsp;&nbsp;&nbsp;"
						.Form::getInputTextfield("search_t", "report_searchbox_filter_name", "filter_name", $_SESSION['report_admin_filter']['name'], '', 255, '' )
						.Form::getButton("report_searchbox_filter_set", "search", Lang::t('_SEARCH', 'standard'), "search_b")
						.Form::getButton("report_searchbox_filter_reset", "reset", Lang::t('_RESET', 'standard'), "reset_b")
				.'</div>
			</div>
		</div>';
	$output .= Form::closeForm();

	//end filter

	//compose search query
	$qconds = array();
	$query = "SELECT t1.*, t2.userid FROM %lms_report_filter as t1 LEFT JOIN %adm_user as t2 ON t1.author=t2.idst ";
	switch ($level) {
		case ADMIN_GROUP_GODADMIN : {
			if ($_SESSION['report_admin_filter']['author'] > 0) $qconds[] = " t1.author = ".$_SESSION['report_admin_filter']['author']." ";
		};break;
		case ADMIN_GROUP_ADMIN :
		case ADMIN_GROUP_USER :
		default : {
			 if ($_SESSION['report_admin_filter']['author'] > 0) {
				 $qconds[] = " ( t1.author = ".$_SESSION['report_admin_filter']['author']." AND t1.is_public = 1 ) ";
			 } else {
				 $qconds[] = " ( t1.author = ".Docebo::user()->getIdst()." OR t1.is_public = 1 ) ";
			 }
		}break;
	}

	if (trim($_SESSION['report_admin_filter']['name']) != "") {
		$qconds[] = " t1.filter_name LIKE '%".$_SESSION['report_admin_filter']['name']."%' ";
	}

	if (trim($_SESSION['report_admin_filter']['type']) > 0) {
		//$qconds[] = " t1.filter_name LIKE '".$_SESSION['report_admin_filter']['name']."' ";
	}

	if (!empty($qconds)) {
		$query .= " WHERE ".implode(" AND ", $qconds);
	}

	//$_SESSION['report_admin_filter']['type']
	//end query

	$tb = new Table(Get::sett('visu_course'));
	$tb->initNavBar('ini', 'button');
	$col_type = array('','','align_center','image','image', 'img-cell', 'img-cell','image');//,'image','image');
	$col_content = array(
		$lang->def('_NAME'),
		$lang->def('_TAB_REP_CREATOR'),
		$lang->def('_CREATION_DATE'),
		$lang->def('_TAB_REP_PUBLIC'),
		'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('_VIEW').'" title="'.$lang->def('_VIEW').'" />',
		'<span class="ico-sprite subs_csv"><span>'.Lang::t('_EXPORT_CSV', 'report').'</span></span>',
		'<span class="ico-sprite subs_xls"><span>'.Lang::t('_EXPORT_XLS', 'report').'</span></span>',
		'<img src="'.getPathImage().'standard/wait_alarm.png" alt="'.$lang->def('_SCHEDULE').'" title="'.$lang->def('_SCHEDULE').'" />'/*,
		'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_REP_TITLE_MOD').'" title="'.$lang->def('_MOD').'" />',
		'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />'	*/
	);

	if ($level==ADMIN_GROUP_GODADMIN || $can_mod) {
		$col_type[]='image';
		$col_type[]='image';
		$col_content[]='<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />';
		$col_content[]='<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />';
	}

	$tb->setColsStyle($col_type);
	$tb->addHead($col_content);

	if ($res = sql_query($query)) {
		while ($row = sql_fetch_assoc($res)) {
			$id = $row['id_filter'];
			$opn_link =
				'<a href="index.php?modname=report&amp;op=show_results&amp;idrep='.$id.'" '. //'.$url.'&amp;action=open&amp;idrep='.$id.'" '.
				' title="'.$lang->def('_VIEW').'">'.
				'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('_VIEW').'" />'.
				'</a>';
			$sch_link =
			//'<a href="'.$url.'&amp;action=schedule&amp;idrep='.$id.'" '.
				'<a href="index.php?modname=report&amp;op=schedulelist&amp;idrep='.$id.'" '.
				' title="'.$lang->def('_SCHEDULE').'">'.
				'<img src="'.getPathImage().'standard/wait_alarm.png" alt="'.$lang->def('_SCHEDULE').'" />'.
				'</a>';
			$mod_link =
				'<a href="'.$url.'&amp;action=modify&amp;idrep='.$id.'" '.
				' title="'.$lang->def('_MOD').'">'.
				'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />'.
				'</a>';
			$rem_link =
				'<a href="'.$url.'&amp;action=delete&amp;idrep='.$id.'" '.
				' title="'.$lang->def('_DEL').' : '.($row['author'] == 0 ? $lang->def($row['filter_name']) : $row['filter_name']).'">'.
				'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />';//.
				'</a>';
			$can_public = ($can_mod ? true : ($level==ADMIN_GROUP_GODADMIN && $row['author']==Docebo::user()->getIdst() ? true : false));
			$public = '<image '.($can_public ? 'class="handover"' : '').' src="'.getPathImage('lms').'standard/'.
			($row['is_public']==1 ? '' : 'un').'publish.png'.'" '.
			($level==ADMIN_GROUP_GODADMIN || $can_mod ? 'onclick="public_report(this, '.$row['id_filter'].');" ' : '').' />'.
				'<input type="hidden" id="enable_value_'.$row['id_filter'].'" '.
				'value="'.($row['is_public']==1 ? '0' : '1').'" />';

			$export_url = 'index.php?modname=report&op=show_results&idrep='.(int)$id;
			$export_link_csv = '<a class="ico-sprite subs_csv" href="'.$export_url.'&dl=csv" title="'.Lang::t('_EXPORT_CSV', 'report').'"><span></span>'.Lang::t('_EXPORT_CSV', 'report').'</a>';
			$export_link_xls = '<a class="ico-sprite subs_xls" href="'.$export_url.'&dl=xls" title="'.Lang::t('_EXPORT_XLS', 'report').'"><span></span>'.Lang::t('_EXPORT_XLS', 'report').'</a>';

			$_name = ($row['author'] == 0 ? $lang->def($row['filter_name']) : $row['filter_name']);
			if (trim($_SESSION['report_admin_filter']['name']) != "") { $_name = Layout::highlight($_name, $_SESSION['report_admin_filter']['name']); }

			$tb_content = array(
				_REP_KEY_NAME     => $_name,
				_REP_KEY_CREATOR  => ($row['author'] == 0 ? '<div class="align_center">-</div>' : $acl_man->relativeId($row['userid'])),
				_REP_KEY_CREATION => Format::date($row['creation_date']),
				_REP_KEY_PUBLIC   => $public,//$row['report_name'],
				_REP_KEY_OPEN     => $opn_link,
				$export_link_csv,
				$export_link_xls,
				_REP_KEY_SCHED    => $sch_link/*,
				_REP_KEY_MOD    => $mod_link,
				_REP_KEY_REM    => $rem_link*/
			);
			if ($level==ADMIN_GROUP_GODADMIN || $can_mod) {
				if ($row['author']==Docebo::user()->getIdst() || $can_mod) {
					$tb_content[_REP_KEY_MOD] = $mod_link;
					$tb_content[_REP_KEY_REM] = $rem_link;
				} else {
					$tb_content[_REP_KEY_MOD] = '&nbsp;';
					$tb_content[_REP_KEY_REM] = '&nbsp;';
				}
			}
			$tb->addBody($tb_content);
		}
	}

	if ($level==ADMIN_GROUP_GODADMIN || $can_mod) {//if ($can_mod) {
		$tb->addActionAdd('
			<a href="index.php?modname=report&amp;op=report_category">'.
		'<img src="'.getPathImage().'standard/add.png" '.
			'title="'.$lang->def('_NEW').'" /> '.
			$lang->def('_NEW').'</a>');
	}

	$output .= $tb->getTable();

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delete]');

	return $output;
}

//step functions

function reportlist() {
	checkPerm('view');

	require_once(_lms_.'/admin/modules/report/class.report.php'); //reportbox class
	require_once(_lms_.'/admin/modules/report/report_schedule.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_lms_.'/lib/lib.report.php');

	if ($action = Get::req('action', DOTY_STRING, false)) {
		switch ($action) {
			case 'sched_rem': {
				report_delete_schedulation(Get::req('id_sched', DOTY_INT, false));
			} break;
		}
	}

	unload_filter(true);

	$lang =& DoceboLanguage::createInstance('report');

    $error = Get::req('err', DOTY_STRING, false);
    switch ($error) {
        case 'plugin': {
            cout( getErrorUi($lang->def('_ERROR_NOTEXISTS')) );
        } break;
    }

	cout(getTitleArea($lang->def('_REPORT'), 'report'));
	cout('<div class="std_block">');
	//cout(get_report_steplist($step_index));

	switch (Get::req('saverep', DOTY_STRING, false)) {
		case 'true'  : cout( getResultUi($lang->def('_SAVE_REPORT_OK')) ); break;
		case 'false' : cout( getErrorUi($lang->def('_SAVE_REPORT_FAIL')) ); break;
	}

	switch (Get::req('modrep', DOTY_STRING, false)) {
		case 'true'  : cout( getResultUi($lang->def('_OPERATION_SUCCESSFUL')) ); break;
		case 'false' : cout( getErrorUi($lang->def('_MOD_REPORT_FAIL')) ); break;
	}

	cout(get_report_table('index.php?modname=report&op=report_open_filter'));

	cout( '</div>', 'content' );//std_block div
}

function report_category() {
	checkPerm('mod');

	require_once(_lms_.'/admin/modules/report/class.report.php'); //reportbox class
	require_once(_lms_.'/lib/lib.report.php');
	//require_once('report_categories.php');
	load_categories();

	$lang =& DoceboLanguage::createInstance('report');

	$step_index = 0;
	cout(getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			$lang->def('_NEW')
		), 'report')
		.'<div class="std_block">');

	$error = Get::req('err', DOTY_STRING, false);
	switch ($error) {
		case 'noname': {
			cout( getErrorUi($lang->def('_ERROR_NONAME')) );
		} break;
	}

	$temp = array();
	foreach ($GLOBALS['report_categories'] as $key=>$value) {
		$temp[$key] = $lang->def($value );
	}

	cout( Form::openForm('repcat_form', 'index.php?modname=report&op=report_rows_filter').
		Form::getHidden('set_category', 'set_category', 1).
		Form::getTextField(
			$lang->def('_NAME'), //$label_name,
			'report_name',
			'report_name',
			'200').
		Form::getDropDown($lang->def('_SELECT_REPORT_CATEGORY'), '', 'id_report', $temp).

		Form::openButtonSpace().
		Form::getButton( 'cat_forward', 'cat_forward', $lang->def('_NEXT') ).
		Form::getButton( 'cat_undo', 'cat_undo', $lang->def('_UNDO') ).
		Form::closeButtonSpace().
		Form::closeForm()
	, 'content');

	cout( '</div>', 'content' );
}

function report_rows_filter() {
	checkPerm('mod');

	if (Get::req('cat_undo', DOTY_MIXED, false)) Util::jump_to('index.php?modname=report&op=reportlist');

	$lang =& DoceboLanguage::createInstance('report');
	$ref =& $_SESSION['report_tempdata'];

	if (Get::req('set_category', DOTY_INT, 0)==1) {
		if (Get::req('report_name', DOTY_STRING, '')=='') Util::jump_to('index.php?modname=report&op=report_category&err=noname');
		$ref['id_report'] = Get::req('id_report', DOTY_ALPHANUM, false);
		$ref['report_name'] = Get::req('report_name', DOTY_STRING, false);
	}

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=report&op=report_category';
	$obj_report->jump_url = 'index.php?modname=report&op=report_rows_filter';
	$obj_report->next_url = 'index.php?modname=report&op=report_sel_columns';

	$page_title = getTitleArea(array(
		'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
		'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'),
		$lang->def('_REPORT_SEL_ROWS')
	), 'report')
	.'<div class="std_block">';

	if ($obj_report->usestandardtitle_rows) {
		cout($page_title.'<div class="std_block">');
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_rows_filter();

	if ($obj_report->usestandardtitle_rows) {
		cout('</div>'); //close title area
	}
}

function report_sel_columns() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('report');
	$obj_report = openreport();
	$temp = $obj_report->get_columns_categories();

	cout(getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'),//$obj_report->report_name,
			'index.php?modname=report&amp;op=report_rows_filter' => $lang->def('_REPORT_SEL_ROWS'),
			$lang->def('_REPORT_SEL_COLUMNS')
		))
		.'<div class="std_block">'
		.Form::openForm('choose_category_form','index.php?modname=report&op=report_columns_filter&of_platform=lms')
	, 'content');
	$i = 1;
	foreach ($temp as $key=>$value) {

		cout( Form::getRadio( $i.') '.$value, 'sel_columns_'.$key, 'columns_filter', $key, ($i==1)), 'content');
		$i++;
	}
	cout( Form::openButtonSpace().
		Form::getButton( 'sel_rep_columns_button', false, $lang->def('_CONFIRM'), false, '', true, true).
		Form::closeButtonSpace().
		Form::closeForm().
		'</div>'
	, 'content');
}

function report_columns_filter() {
	checkPerm('mod');

	$ref =& $_SESSION['report_tempdata']['columns_filter_category'];
	if (isset($_POST['columns_filter']))
	$ref = $_POST['columns_filter'];

	$lang =& DoceboLanguage::createInstance('report');

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=report&op=report_sel_columns';
	$obj_report->jump_url = 'index.php?modname=report&op=report_columns_filter';
	$obj_report->next_url = 'index.php?modname=report&op=report_save';

	//page title
	$page_title = getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'),
			'index.php?modname=report&amp;op=report_rows_filter' => $lang->def('_REPORT_SEL_ROWS'),
			'index.php?modname=report&amp;op=report_sel_columns' => $lang->def('_REPORT_SEL_COLUMNS'),
			$lang->def('_REPORT_COLUMNS')
		))
	.'<div class="std_block">';
	//.  	getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content');

	if($obj_report->useStandardTitle_Columns()) {
		cout($page_title);
		cout(Form::openForm('report_columns_form', $obj_report->jump_url));
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_columns_filter($_SESSION['report_tempdata']['columns_filter_category']);

	if ($obj_report->useStandardTitle_Columns()) {
		cout(
			Form::openButtonSpace()
			.Form::getBreakRow()
			.Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
			.Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
			.Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
			.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report'))
			.Form::closeButtonSpace()
			.Form::closeForm());
		cout('</div>'); //close std_block div
	}
}

function report_save_filter() {
	checkPerm('mod');

	$ref =& $_SESSION['report_tempdata'];
	$report_id = $ref['id_report'];
	$report_name = $ref['report_name'];
	$nosave = Get::req('nosave', DOTY_INT, 0);
	$show = Get::req('show', DOTY_INT, 0);
	$idrep = Get::req('modid', DOTY_INT, false);

	if ($nosave>0) {
		Util::jump_to('index.php?modname=report&op=show_results&nosave=1'.($idrep ? '&modid='.$idrep : ''));
	}

	if (isset($_SESSION['report_update'])  || $idrep) {
		$save_ok = report_update($idrep, $report_name, $ref);
		if ($show) {
			Util::jump_to('index.php?modname=report&op=show_results&idrep='.$idrep);
		} else {
			Util::jump_to('index.php?modname=report&op=reportlist&modrep='.($save_ok ? 'true' : 'false'));
		}
	} else {
		$save_ok = report_save($report_id, $report_name, $ref);
		if ($show) {
			Util::jump_to('index.php?modname=report&op=show_results&idrep='.$save_ok);
		} else {
			Util::jump_to('index.php?modname=report&op=reportlist&saverep='.($save_ok ? 'true' : 'false'));
		}
	}
}

function setup_report_js() {

	YuiLib::load(array(
		'animation' 		=> 'animation-min.js',
		'dragdrop' 			=> 'dragdrop-min.js',
		'button' 			=> 'button-min.js',
		'container' 		=> 'container-min.js',
		'my_window' 		=> 'windows.js'
	), array(
		'container/assets/skins/sam' => 'container.css',
		'button/assets/skins/sam' => 'button.css'
	));
	Util::get_js(Get::rel_path('lms').'/admin/modules/report/ajax.report.js', true, true);
}

function report_show_results($idrep = false) {

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.download.php' );

	//import yui pop-up stuff
	setup_report_js();

	$lang			=& DoceboLanguage::createInstance('report');
	$start_url		= 'index.php?modname=report&op=reportlist';
	$download		= Get::req('dl', DOTY_STRING, false);
	$no_download	= Get::req('no_show_repdownload', DOTY_INT, 0);
	$nosave			= Get::req('nosave', DOTY_INT, 0);

	if($idrep == false) {
		//die( print_r($_SESSION['report_tempdata'], true ) );
		if (!isset($_SESSION['report_tempdata'])) $ref =& $_SESSION['report']; else $ref =& $_SESSION['report_tempdata'];
		$id_report = $ref['id_report'];
		$res = sql_query("SELECT class_name, file_name FROM %lms_report WHERE id_report=".$id_report." AND enabled=1");
		$author = 0;
		$filter_name = $ref['report_name'];
		//['columns_filter_category'] 
		if ($res && (sql_num_rows($res)>0)) {
			list($class_name, $file_name) = sql_fetch_row($res);
            if ($file_name) {
                if (file_exists(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name) && Get::cfg('enable_customscripts', false) == true) {
                    require_once(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name);
                } else {
                    require_once(_lms_ . '/admin/modules/report/' . $file_name);
                }
                $obj_report = new $class_name($idrep);
            } else {
                $pg = new PluginManager('Report');
                $obj_report = $pg->get_plugin(strtolower($class_name), array($idrep));
            }
		} else {
			reportlist();
		}
	
	} else {
        checkReport($idrep);
		/// find main class report filename and report info
		$query_report = "
		SELECT r.class_name, r.file_name, r.report_name, f.filter_name, f.filter_data, f.author
		FROM %lms_report AS r
			JOIN %lms_report_filter AS f
			ON ( r.id_report = f.id_report )
		WHERE f.id_filter = '".$idrep."'";
		$re_report = sql_query($query_report);

		if(sql_num_rows($re_report) == 0) {
			reportlist();
			return;
		}
		
		// create the report object
		list($class_name, $file_name, $report_name, $filter_name, $filter_data, $author) = sql_fetch_row($re_report);
            //when file name set use old style
        if ($file_name) {
            if (file_exists(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name) && Get::cfg('enable_customscripts', false) == true) {
                require_once(_base_ . '/customscripts/' . _folder_lms_ . '/admin/modules/report/' . $file_name);
            } else {
                require_once(_lms_ . '/admin/modules/report/' . $file_name);
            }
            $obj_report = new $class_name($idrep);
        } else {
            $pg = new PluginManager('Report');
            $obj_report = $pg->get_plugin(strtolower($class_name), array($idrep));
        }
    }

	$obj_report->back_url = $start_url;
	$obj_report->jump_url = 'index.php?modname=report&op=show_results&idrep='.$idrep;

	if($author == 0) $filter_name = ( $filter_name ? $lang->def($filter_name) : '' );

	$data = _decode( $filter_data ) ;

	if($download != false) {
		$export_filename = 'report_'.$filter_name.'_'.date("d_m_Y");
		switch ($download) {
			case 'htm': { sendStrAsFile($obj_report->getHTML($data['columns_filter_category'], $data), $export_filename.'.html'); };break;
			case 'csv': { sendStrAsFile($obj_report->getCSV($data['columns_filter_category'], $data), $export_filename.'.csv'); };break;
			case 'xls': { sendStrAsFile($obj_report->getXLS($data['columns_filter_category'], $data), $export_filename.'.xls'); };break;
		}
	}

	cout(getTitleArea(array($start_url => $lang->def('_REPORT'), $filter_name), 'report')
		.'<div class="std_block">'
		.getBackUi($start_url, $lang->def('_BACK_TO_LIST'), 'content'));

	if ($nosave > 0) {
		$mod_id = Get::req('modid', DOTY_INT, false);
		cout('<br/>'.getBackUi('index.php?modname=report&op=report_save'.($mod_id ? '&modid='.$mod_id : ''), $lang->def('_SAVE_AND_BACK_TO_LIST')));
	}
	if($no_download <= 0) {

		cout('<p class="export_list">'.
			'<a class="ico-wt-sprite subs_htm" href="'.$obj_report->jump_url.'&amp;dl=htm"><span>'.$lang->def('_EXPORT_HTML').'</span></a>&nbsp;'.
			'<a class="ico-wt-sprite subs_csv" href="'.$obj_report->jump_url.'&amp;dl=csv"><span>'.$lang->def('_EXPORT_CSV').'</span></a>&nbsp;'.
			'<a class="ico-wt-sprite subs_xls" href="'.$obj_report->jump_url.'&amp;dl=xls"><span>'.$lang->def('_EXPORT_XLS').'</span></a>'.
			'</p>'.
			//'<div class="nofloat"></div>'.
			'<br/>');
	}

	// css -----------------------------------------------------------
	cout('<link href="'.getPathTemplate('lms').'style/report/style_report_user.css" rel="stylesheet" type="text/css" />'."\n", 'page_head');
	// $_SESSION['report_tempdata']['columns_filter_category']

	$query_update = "UPDATE %lms_report_filter SET views = views+1 WHERE id_filter = '".$idrep."'";
	$re_update = sql_query($query_update);

	cout(Form::openForm('user_report_columns_courses', $obj_report->jump_url));
	cout($obj_report->show_results($data['columns_filter_category'], $data));
	cout(Form::closeForm().'</div>');

}

function report_open_filter() {
	require_once(_lms_.'/lib/lib.report.php');

	$url='index.php?modname=report&op=reportlist';
	$filter_id = Get::req('idrep', DOTY_INT, false);
	$action = Get::req('action', DOTY_STRING, '');
	if ( !$filter_id )  { Util::jump_to($url); return false; }

	switch ($action) {
		case 'schedule': {
			load_filter($filter_id,true);
			Util::jump_to('index.php?modname=report&op=report_schedule');
		} break;

		case 'open': {
			load_filter($filter_id, true);
			Util::jump_to('index.php?modname=report&op=show_results');
		} break;

		case 'modify': {
			load_filter($filter_id,true,true); //will load it after the Util::jump_to
			Util::jump_to('index.php?modname=report&op=modify_name&modid='.$filter_id);
		} break;

		case 'delete': {
			//delete filter from list and DB, than reload page
			if (report_delete_filter($filter_id)) {
				$success = '&fdel=1&idrep='.$filter_id;
			} else {
				$success = '&fdel=0&idrep='.$filter_id;
			}
			Util::jump_to($url.$success);
		} break;

		default: Util::jump_to($url);
	}

}

function schedulelist() {
	require_once(_lms_.'/admin/modules/report/report_schedule.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_lms_.'/lib/lib.report.php');
    
    //import yui pop-up stuff
	setup_report_js();

	if ($action = Get::req('action', DOTY_STRING, false)) {
		switch ($action) {
			case 'sched_rem': {
				report_delete_schedulation(Get::req('id_sched', DOTY_INT, false));
			} break;
		}
	}

	if (isset($_SESSION['schedule_tempdata'])) {
		unset($_SESSION['schedule_tempdata']);
	}
	if (isset($_SESSION['schedule_update'])) {
		unset($_SESSION['schedule_update']);
	}

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('report');

	$idrep = Get::req('idrep', DOTY_INT, false);
	cout(getTitleArea(array(
	  'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
				$lang->def('_SCHEDULE').'"<b>'.getReportNameById($idrep).'</b>"' ) ) );

	cout('<div class="std_block">');
	cout('<p><span class="glyphicon glyphicon-warning-sign"></span> '.$lang->def('_WARNING_REPORT').'<p><hr>');
	cout(get_schedulations_table($idrep));

	cout( '</div>', 'content' ); //std_block div
}

//******************************************************************************

function report_modify_name() {
	checkPerm('mod');

	require_once(_lms_.'/admin/modules/report/class.report.php'); //reportbox class
	require_once(_lms_.'/lib/lib.report.php');
	//require_once('report_categories.php');
	load_categories();

	$lang =& DoceboLanguage::createInstance('report');

	$idrep = Get::req('modid', DOTY_INT, false);
	//if (!idrep) Util::jump_to(initial page ... )

	$page_title = getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			$lang->def('_MOD')
		), 'report'/*, $lang->def('_ALT_REPORT')*/);
	cout($page_title.'<div class="std_block">');

	$info = get_update_info();
	//if($info) cout( getInfoUi($info) );

	$box = new ReportBox('report_modify_name');

	$box->title = $lang->def('_MOD');
	$box->description = false;

	$box->body =
	Form::openForm('repcat_form', 'index.php?modname=report&op=modify_rows&modid='.$idrep).
	Form::getHidden('mod_name', 'mod_name', 1);

	$box->body .= Form::getTextField(
		$lang->def('_NAME'), //$label_name,
		'report_name',
		'report_name',
		'200', getReportNameById($idrep));

	$box->body .=
	//Form::closeElementSpace().
	Form::openButtonSpace().
	Form::getButton( '', '', $lang->def('_NEXT')).
	Form::closeButtonSpace().
	Form::closeForm();

	cout($box->get());

	cout( '</div>', 'content' );
}

function report_modify_rows() {
	checkPerm('mod');

	$lang =& DoceboLanguage::createInstance('report');
	$ref =& $_SESSION['report_tempdata'];

	$idrep = Get::req('modid', DOTY_INT, false);

	if (Get::req('mod_name', DOTY_INT, 0)==1) {
		$ref['report_name'] = Get::req('report_name', DOTY_STRING, false);
	}

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=report&op=modify_name&modid='.$idrep;
	$obj_report->jump_url = 'index.php?modname=report&op=modify_rows&modid='.$idrep;
	$obj_report->next_url = 'index.php?modname=report&op=modify_cols&modid='.$idrep;

	$page_title = getTitleArea(array(
			'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
			'index.php?modname=report&op=modify_name&modid='.$idrep => $lang->def('_MOD'),
			$lang->def('_REPORT_MOD_ROWS')
		), 'report')
		.'<div class="std_block">';

	/*$info = get_update_info();
	if($info) getInfoUi($info) );*/

	if ($obj_report->usestandardtitle_rows) {
		cout($page_title.'<div class="std_block">');//.getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content'));
		$info = get_update_info();
		if($info) cout( getInfoUi($info) );
		//cout(Form::openForm('user_report_rows_courses_mod', $obj_report->jump_url));
	} else {
		//this is just used to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_rows_filter();

	if ($obj_report->usestandardtitle_rows) {
		//cout(Form::closeForm());
		cout('</div>'); //close title area
	}
}

function report_modify_columns() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$ref =& $_SESSION['report_tempdata']['columns_filter_category'];
	if (isset($_POST['columns_filter']))
	$ref = $_POST['columns_filter'];

	$idrep = Get::req('modid', DOTY_INT, false);
	$lang =& DoceboLanguage::createInstance('report');

	$obj_report = openreport();
	$obj_report->back_url = 'index.php?modname=report&op=modify_rows&modid='.$idrep;
	$obj_report->jump_url = 'index.php?modname=report&op=modify_cols&modid='.$idrep;
	$obj_report->next_url = 'index.php?modname=report&op=report_save&modid='.$idrep;

	//page title
	$page_title = getTitleArea(array(
		  'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
		  'index.php?modname=report&op=modify_name&modid='.$idrep => $lang->def('_MOD'),
	  'index.php?modname=report&op=modify_rows&modid='.$idrep => $lang->def('_REPORT_MOD_ROWS'),
			$lang->def('_REPORT_MOD_COLUMNS')
		))
	.'<div class="std_block">';

	/*$info = get_update_info();
	if($info) cout( getInfoUi($info) );*/

	if($obj_report->useStandardTitle_Columns()) {
		cout($page_title);
		$info = get_update_info();
		if($info) cout( getInfoUi($info) );
		cout(Form::openForm('user_report_columns_courses_mod', $obj_report->jump_url));
	} else {
		//this is used just to pass std title string to object functions, who may use it
		$obj_report->page_title = $page_title;
	}

	$obj_report->get_columns_filter($_SESSION['report_tempdata']['columns_filter_category']);

	if ($obj_report->useStandardTitle_Columns()) {
		cout(Form::openButtonSpace());
		cout(
			Form::getBreakRow()
			.Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
			.Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
			.Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
			.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report'))
		);
		cout(Form::closeButtonSpace());
		cout(Form::closeForm());
		cout('</div>'); //close std_block div
	}
}

// switch
function reportDispatch($op) {

	
	if(isset($_POST['save_showed'])) $op = 'report_schedule';

	switch($op) {
		case "reportlist" : {
			reportlist();
		};break;

		case "report_category" : {
			report_category();
		};break;

		case "report_rows_filter" : {
			report_rows_filter();
		};break;

		case "report_sel_columns" : {
			report_sel_columns();
		};break;

		case "report_columns_filter" : {
			report_columns_filter();
		};break;

		case "report_save" : {
			if (Get::req('nosave', DOTY_INT, 0)>0) {
				report_show_results(false);
			}
			report_save_filter();
		} break;

		case "show_results": {
			report_show_results(Get::req('idrep', DOTY_INT, false));
		} break;

		case "modify_name": {
			report_modify_name();
		} break;

		case "modify_rows": {
			report_modify_rows();
		} break;

		case "modify_cols": {
			report_modify_columns();
		} break;

		case "sched_mod": {
			require_once(_lms_.'/admin/modules/report/report_schedule.php');
			modify_schedulation();
		} break;

		case "report_open_filter": {
			report_open_filter();
		} break;

		case "report_schedule": {
			require_once(_lms_.'/admin/modules/report/report_schedule.php');
			schedule_report();
		} break;

		case "schedulelist": {
			schedulelist();
		} break;
	} // end switch

}

?>