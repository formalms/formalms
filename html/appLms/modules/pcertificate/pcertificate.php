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

define('IS_META', 0);

if(Docebo::user()->isAnonymous()) die("You can't access");

function list_element_certificate() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	
	$mod_perm		= checkPerm('mod', true);
	$id_certificate = importVar('id_certificate', true);
	
	// create a language istance for module admin_certificate
	$lang 		=& DoceboLanguage::createInstance('certificate', 'lms');
	$out 		=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$form		= new Form();
	
	$page_title = array(
		'index.php?modname=pcertificate&amp;op=certificate' => $lang->def('_TITLE_CERTIFICATE'),
		$lang->def('_STRUCTURE_CERTIFICATE')
	);
	
	$out->add(getTitleArea($page_title, 'certificate')
			.'<div class="std_block">'
			.getBackUi( 'index.php?modname=pcertificate&amp;op=certificate', $lang->def('_BACK') )
	);
	
	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
			case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
		}
	}
	
	$query_structure = "
	SELECT cert_structure, orientation, bgimage
	FROM ".$GLOBALS['prefix_lms']."_certificate 
	WHERE id_certificate = '".(int)$id_certificate."'";
	
	list($structure, $orientation, $bgimage) = sql_fetch_row(sql_query($query_structure));
	
	$out->add('<div class="std_block">'	);
	
	$out->add( getInfoUi($lang->def('_CERTIFICATE_WARNING')) );
	
	$out->add($form->openForm('structure_certificate', 'index.php?modname=pcertificate&amp;op=savecertificate', false, false, 'multipart/form-data'));
	$out->add($form->openElementSpace()
						
		.$form->getTextarea ($lang->def('_STRUCTURE_CERTIFICATE'), 'structure', 'structure', $structure) 
		.'<p><b>'.$lang->def('_ORIENTATION').'</b></p>'
		.$form->getRadio($lang->def('_PORTRAIT'), 'portrait', 'orientation', 'P', ($orientation == 'P'))
		.$form->getRadio($lang->def('_LANDSCAPE'), 'landscape', 'orientation', 'L', ($orientation == 'L'))
		
		.$form->getExtendedFilefield(	$lang->def('_BACK_IMAGE'), 
										'bgimage', 
										'bgimage', 
										$bgimage)
		
		.$form->closeElementSpace()
		.$form->openButtonSpace()
		.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
		.$form->getButton('save_structure', 'save_structure', ($lang->def('_SAVE') ) )
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm());

	
	$tb = new Table(0, $lang->def('_TAG_LIST_CAPTION'), $lang->def('_TAG_LIST_SUMMARY'));
	
	$tb->setColsStyle(array('', ''));
	$tb->addHead(array($lang->def('_TAG_CODE'), $lang->def('_TAG_DESCRIPTION')));
	
	//search query of certificates tag
	$query_format_tag = "
	SELECT file_name, class_name 
	FROM ".$GLOBALS['prefix_lms']."_certificate_tags ";
	$re_certificate_tags = sql_query($query_format_tag);
	while(list($file_name, $class_name) = sql_fetch_row($re_certificate_tags)) {
		
		if(file_exists($GLOBALS['where_lms'].'/lib/certificate/'.$file_name)) {
			
			require_once($GLOBALS['where_lms'].'/lib/certificate/'.$file_name);
			$instance = new $class_name(0, 0);
			$this_subs = $instance->getSubstitutionTags();
			foreach($this_subs as $tag => $description) {
			
				$tb->addBody(array($tag, $description));
			} // end foreach
		} // end if
	}	
	$out->add($tb->getTable());
	
	$out->add('</div>');
}

function manageCertificateFile($new_file_id, $old_file, $path, $delete_old, $is_image = false) {
	require_once(_base_.'/lib/lib.upload.php');
	$arr_new_file = ( isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false );
	$return = array(	'filename' => $old_file,
						'new_size' => 0,
						'old_size' => 0,
						'error' => false,
						'quota_exceeded' => false);
	sl_open_fileoperations();
	if(($delete_old || $arr_new_file !== false) && $old_file != '')
		sl_unlink($path.$old_file);// the flag for file delete is checked or a new file was uploaded ---------------------
	
	if(!empty($arr_new_file))
	{
		// if present load the new file --------------------------------------------------------
		$filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];
		
		if(!sl_upload($arr_new_file['tmp_name'], $path.$filename))
			return false;
		else
			return $filename;
	}
	sl_close_fileoperations();
	return '';
}

function view_report_certificate()
{
	checkPerm('view');

	require_once(_lms_.'/lib/lib.certificate.php');
	require_once(_lms_.'/lib/lib.course.php');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$deletion = importVar('deletion', true, 0);

	if ($deletion)
		switch ($deletion)
		{
			case 1:	$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));	break;
			case 2:	$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));	break;
			case 3:	$out->add(getErrorUi($lang->def('_OPERATION_FAILURE'))); break;
		}

	$certificate = new Certificate();

	$id_certificate = importVar('id_certificate', true, 0);
	$id_course = importVar('id_course', true, 0);

	$selection = importVar('selection', false, array()); //all possible selected values
	$selected = importVar('selected', false, array()); //effectively selected values with checkbox
	$sel = importVar('old_selection', false, ''); //selected values from previous table pages
	if ($sel != '') $total_selection = explode(',', $sel); else $total_selection = array();

	//update total selection
	foreach ($selection as $key=>$val)
	{
		if (in_array($val, $selected))
		{
			if (!in_array($val, $total_selection))
				$total_selection[] = $val;
		}
		else
		{
			$index = array_search($val, $total_selection);
			if ($index !== false)
				array_splice($total_selection, $index, 1);
		}
	}

	$search_filter = importVar('filter', false, '');
	$only_released = importVar('only_released', true, 0);

	//which command?
	if (importVar('search_button', false, false) !== false)
	{
	}
	if (importVar('reset_button', false, false) !== false)
	{
		$search_filter = '';
		$only_released = 0;
	}
	if (importVar('print_button', false, false) !== false)
	{
	}

	$numtablerows = $GLOBALS['framework']['visuItem'];

	$tb = new Table($numtablerows, $lang->def('_CERTIFICATE_VIEW_CAPTION'), $lang->def('_CERTIFICATE_VIEW_CAPTION'));
	$tb->initNavBar('ini', 'button');
	$ini = $tb->getSelectedElement();

	$tca = $GLOBALS['prefix_lms']."_certificate_assign as ca";
	$tcc = $GLOBALS['prefix_lms']."_certificate_course as cc";
	$tcu = $GLOBALS['prefix_lms']."_courseuser as cu";
	$tu = $GLOBALS['prefix_fw']."_user as u";

	$where = "";
	if ($search_filter != '') $where .= " AND (u.userid LIKE '%".$search_filter."%' OR u.lastname LIKE '%".$search_filter."%' OR u.firstname LIKE '%".$search_filter."%') ";
	if ($only_released > 0) $where = " AND ca.on_date ".($only_released==1 ? "IS NOT" : "IS")." NULL ";//$where .= " AND ".$aval_status." ".($only_released==1 ? "<" : ">=")." cu.status ";

	list($aval_status) = sql_fetch_row(sql_query("SELECT available_for_status FROM ".$tcc." "
		." WHERE id_certificate='".(int)$id_certificate."' AND id_course='".(int)$id_course."'"));

	switch($aval_status)
	{
		case AVS_ASSIGN_FOR_ALL_STATUS 		: { $aval_status = " 1 "; };break;
		case AVS_ASSIGN_FOR_STATUS_INCOURSE : { $aval_status = " cu.status = "._CUS_BEGIN." "; };break;
		case AVS_ASSIGN_FOR_STATUS_COMPLETED : { $aval_status = " cu.status = "._CUS_END." "; };break;
	}

	list($totalrows) = sql_fetch_row(sql_query("SELECT COUNT(*) "
		." FROM ( ".$tu." JOIN ".$tcu." ON (u.idst = cu.idUser) ) LEFT JOIN ".$tca." "
		." ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser ) "
		." WHERE (ca.id_certificate='".(int)$id_certificate."' OR ca.id_certificate IS NULL) AND ".$aval_status." "
		." AND cu.idCourse='".(int)$id_course."' ".$where));

	$query = "SELECT u.userid, u.firstname, u.lastname, cu.date_complete, ca.on_date, cu.idUser as id_user, cu.status "
		." FROM ( ".$tu." JOIN ".$tcu." ON (u.idst = cu.idUser) ) LEFT JOIN ".$tca." "
		." ON ( ca.id_course = cu.idCourse AND ca.id_user=cu.idUser ) "
		." WHERE (ca.id_certificate='".(int)$id_certificate."' OR ca.id_certificate IS NULL) AND ".$aval_status." "
		." AND cu.idCourse='".(int)$id_course."' ".$where
		." ORDER BY u.userid LIMIT ".$ini.", ".$numtablerows;

	$res = sql_query($query);

	$from = Get::req('from', DOTY_MIXED, '');

	$back_ui = getBackUi('index.php?r=lms/pcourse/certificate&amp;id_course='.(int)$id_course, $lang->def('_BACK'));

	$out->add(getTitleArea(array(
			'index.php?r=lms/pcourse/certificate&amp;id_course='.(int)$id_course => $lang->def('_CERTIFICATE_ASSIGN_STATUS', 'course'),
			$lang->def('_CERTIFICATE_REPORT_COURSE_CERT')), 'certificate'));

	$out->add('<div class="std_block">'.$back_ui);

	$numrows = sql_num_rows($res);
	$downloadables = array();

	if ($numrows > 0)
	{
		$clang =& DoceboLanguage::CreateInstance('course', 'lms');

		$type_h = array('image', '', '', '', '', '', '', 'image', 'image');
		$cont_h = array(
						'',
						$lang->def('_USERNAME'),
						$lang->def('_LASTNAME'),
						$lang->def('_FIRSTNAME'),
						$clang->def('_STATUS'),
						$lang->def('_DATE'),
						$lang->def('_RELASE_DATE'),
						Get::img('course/certificate.png', Lang::t('_TITLE_VIEW_CERT', 'certificate')),
						Get::img('standard/delete.png', Lang::t('_DEL', 'certificate'))
		);

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		$acl_man =& $GLOBALS['current_user']->getAclManager();
		$arr_status = array(
			_CUS_CONFIRMED 		=> $clang->def('_USER_STATUS_CONFIRMED'),//, 'subscribe', 'lms'),
			_CUS_SUBSCRIBED 	=> $clang->def('_USER_STATUS_SUBS'),//, 'subscribe', 'lms'),//_USER_STATUS_SUBS(?)
			_CUS_BEGIN 			=> $clang->def('_USER_STATUS_BEGIN'),//, 'subscribe', 'lms'),
			_CUS_END 			=> $clang->def('_USER_STATUS_END'),//, 'subscribe', 'lms'),
			_CUS_SUSPEND 		=> $clang->def('_USER_STATUS_SUSPEND')//, 'subscribe', 'lms')
		);

		//foreach($report_info as $info_report)
		while ($info = sql_fetch_assoc($res))
		{
			$cont = array();

			$can_assign = (bool)($info['on_date']=='');
			$input_id = $info['id_user'];

			$sel_cell_content = '';
			$label_open = '';
			$label_close = '';
			if($can_assign)
			{
				$input = '<input type="hidden" id="selection_'.$input_id.'" name="selection['.$input_id.']" value="'.$input_id.'"/> ';
				if(in_array($input_id, $total_selection)) $checked = ' checked="checked"'; else $checked = '';
				$sel_cell_content .= $input.'<input type="checkbox" id="selected_'.$input_id.'" name="selected['.$input_id.']" value="'.$input_id.'"'.$checked.'/>';

				$label_open = '<label for="selected_'.$input_id.'">';
				$label_close = '</label>';
			}

			$userid = $acl_man->relativeId($info['userid']);

			$cont[] = $sel_cell_content;
			$cont[] = $label_open.($search_filter!='' ? highlightText($userid, $search_filter) : $userid).$label_close;
			$cont[] = $label_open.($search_filter!='' ? highlightText($info['lastname'], $search_filter) : $info['lastname']).$label_close;
			$cont[] = $label_open.($search_filter!='' ? highlightText($info['firstname'], $search_filter) : $info['firstname']).$label_close;
			$cont[] = $arr_status[$info['status']];

			$cont[] = $info['date_complete'];
			$cont[] = $info['on_date'];

			$url = 'index.php?modname=pcertificate&amp;certificate_id='.$id_certificate.'&amp;course_id='.$id_course.'&amp;user_id='.$info['id_user'];

			$dl_url = $url."&amp;op=send_certificate";
			if($can_assign) $downloadables[] = 'dl_single_'.$input_id;
			$cont[] = '<a href="'.($can_assign ? "javascript:;" : $dl_url).'" id="dl_single_'.$input_id.'">'
				.Get::img('course/certificate.png', Lang::t('_TITLE_VIEW_CERT', 'certificate'))
				.($can_assign ?  $lang->def('_GENERATE') : $lang->def('_DOWNLOAD')).'</a>';

			$cont[] = ($can_assign ? "" : '<a href="'.$url.'&amp;op=del_report_certificate">'
				.Get::img('standard/delete.png', Lang::t('_DEL', 'certificate')).'</a>');

			$tb->addBody($cont);
		}

		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=del_report_certificate]');

		$form = new Form();
		$form_url = "index.php?modname=pcertificate&amp;op=view_report_certificate&amp;id_certificate=".(int)$id_certificate."&amp;id_course=".(int)$id_course;
		$out->add($form->openForm("certificates_emission", $form_url));

		$out->add($form->getHidden('old_selection', 'old_selection', implode(',', $total_selection)));

		//search filter ...
		$release_options = array(
			$lang->def('_ALL') => 0,
			$lang->def('_ONLY_RELEASED') => 1,
			$lang->def('_ONLY_TO_RELEASE') => 2
		);
/*
		$out->add($form->getOpenFieldset($lang->def('_SEARCH_FILTER')));
		$out->add($form->getTextfield($lang->def('_FILTER'), "filter", "filter", 255, $search_filter));
		$out->add($form->getRadioSet($lang->def('_RELEASE_OPTIONS'), 'only_released', 'only_released', $release_options, $only_released).'<div class="no_float"></div>');
		$out->add($form->openButtonSpace());
		$out->add($form->getButton("search_button", "search_button", $lang->def('_SEARCH')));
		$out->add($form->getButton("reset_button", "reset_button", $lang->def('_UNDO')));
		$out->add($form->closeButtonSpace());
		$out->add($form->getCloseFieldset());
*/
		$print_button_1 = '<div><a id="print_selected_button_1" href="javascript:;">'
			.Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
			.Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
			.'</a></div>';
		$print_button_2 = '<div><a id="print_selected_button_2" href="javascript:;">'
			.Get::img('course/certificate.png', Lang::t('_GENERATE_ALL_SELECTED', 'certificate'))
			.Lang::t('_GENERATE_ALL_SELECTED', 'certificate')
			.'</a></div>';
		cout('<div class="quick_search_form">'

			.Form::getInputTextfield( "_FILTER", "filter", "filter", $search_filter, '', 255, '' ) //TO DO: value from $_SESSION
			.Form::getButton( "search_button", "search_button", Lang::t('_SEARCH', 'standard'), "search_b")
			.Form::getButton( "reset_button", "reset_button", Lang::t('_RESET', 'standard'), "reset_b")
			
			.'<br />'
			.'<br />'
			.$lang->def('_RELEASE_OPTIONS').': '
			.Form::getInputRadio('only_released_0', 'only_released', '0', ($only_released == 0), '')
				.' <label class="label_normal" for="only_released_0">'.Lang::t('_ALL', 'certificate').'</label>'
				.'&nbsp;&nbsp;&nbsp;&nbsp;'

			.Form::getInputRadio('only_released_1', 'only_released', '1', ($only_released == 1), '')
				.' <label class="label_normal" for="only_released_1">'.Lang::t('_ONLY_RELEASED', 'certificate').'</label>'
				.'&nbsp;&nbsp;&nbsp;&nbsp;'
			
			.Form::getInputRadio('only_released_2', 'only_released', '2', ($only_released == 2), '')
				.' <label class="label_normal" for="only_released_2">'.Lang::t('_ONLY_TO_RELEASE', 'certificate').'</label>'

			.Form::closeForm()
			.'</div>'
		, 'content');
		$navbar = $tb->getNavBar($ini, $totalrows);
		$out->add($print_button_1.'<br />'.$navbar.$tb->getTable().$navbar.'<br />'.$print_button_2);

		$out->add($form->closeForm());
	}
	else
		$out->add($lang->def('_NO_USER_FOR_CERTIFICATE'));

	$out->add($back_ui.'</div>');

	addCss('style_menu', 'lms');
	Util::get_js(Get::rel_path('lms').'/modules/pcertificate/pcertificate.js', true, true);
	$script = 'var ajax_url="ajax.server.php?plf=lms&mn=pcertificate"; var _STOP="'.$lang->def('_STOP').'"; '
		.'var glob_id_certificate = '.(int)$id_certificate.', glob_id_course = '.(int)$id_course.';'
		.'var single_list = ['.(count($downloadables) ? '"'.implode('","', $downloadables).'"' : '').']; '
		.'var reload_url = "'.str_replace('&amp;', '&', (isset($form_url) ? $form_url : '')).'", _ERROR_PARSE = "'.$lang->def('_OPERATION_FAILURE').'", _SUCCESS = "'.$lang->def('_OPERATION_SUCCESSFUL').'";';
	$out->add('<script type="text/javascript">'.$script.'</script>', 'page_head');
}

function del_report_certificate()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.certificate.php');

	$certificate = new Certificate();
	$form = new Form();

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$id_certificate = importVar('certificate_id', true, 0);
	$id_course = importVar('course_id', true, 0);
	$id_user = importVar('user_id', true, 0);

	$certificate_info = array();
	$certificate_info = $certificate->getCertificateInfo($id_certificate);

	$c_infos = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
	$certificate_info = current($c_infos);
	if(Get::req('confirm_del_report_certificate', DOTY_INT, 0) == 1 || (isset($_GET['confirm']) && $_GET['confirm'] == 1))
	{
		require_once(_base_.'/lib/lib.upload.php');

		$path = '/appLms/certificate/';
		$deletion_result = true;
		if($certificate_info[CERT_NAME] != '')
			$deletion_result = sl_unlink($path.$certificate_info[ASSIGN_CERT_FILE]);

		if($deletion_result)
		{
			$deletion_result = $certificate->delCertificateForUserInCourse($id_certificate, $id_user, $id_course);
			if($deletion_result)
				Util::jump_to('index.php?modname=pcertificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&deletion=1');
			else
				Util::jump_to('index.php?modname=pcertificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&deletion=2');
		}
		else
			Util::jump_to('index.php?modname=pcertificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course.'&deletion=3');
	}
	elseif(isset($_POST['undo_del_report_certificate']))
		Util::jump_to('index.php?modname=pcertificate&amp;op=view_report_certificate&id_certificate='.$id_certificate.'&id_course='.$id_course);
	else
	{
		$GLOBALS['page']->add(
			getTitleArea($lang->def('_VIEW_REPORT_DELETION'), 'certificate')
			.'<div class="std_block">'
			.$form->openForm('del_certificate', 'index.php?modname=pcertificate&amp;op=del_report_certificate&amp;certificate_id='.$id_certificate.'&amp;course_id='.$id_course.'&amp;user_id='.$id_user)
			.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_NAME').' : </span>'.$certificate_info[$id_certificate][CERT_NAME].'<br />'
								.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$certificate_info[$id_certificate][CERT_DESCR],
							false,
							'confirm_del_report_certificate',
							'undo_del_report_certificate'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function send_certificate() {
	
	require_once(_lms_.'/lib/lib.certificate.php');
	require_once(_base_.'/lib/lib.download.php');

	$id_certificate = importVar('certificate_id', true, 0);
	$id_course 		= importVar('course_id', true, 0);
	$id_user 		= importVar('user_id', true, 0);

	$certificate = new Certificate();

	$report_info = array();
	$report_info = $certificate->getInfoForCourseCertificate($id_course, $id_certificate, $id_user);
	$info_report = current($report_info);

	$file = $info_report[ASSIGN_CERT_FILE];

	//recognize mime type
	$expFileName = explode('.', $file);
	$totPart = count($expFileName) - 1;

	//send file
	sendFile('/appLms/certificate/', $file, $expFileName[$totPart]);
}

function print_certificate() {

	require_once(_lms_.'/lib/lib.certificate.php');
	require_once(_base_.'/lib/lib.download.php' );

	$id_certificate = importVar('certificate_id', true, 0);
	$id_course 		= importVar('course_id', true, 0);
	$id_user 		= importVar('user_id', true, 0);

	$cert = new Certificate();

	$subs = $cert->getSubstitutionArray($id_user, $id_course);
	$cert->send_certificate($id_certificate, $id_user, $id_course, $subs, true);
}

function preview() {
	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.certificate.php');

	$id_certificate = importVar('id_certificate', true, 0);

	$cert = new Certificate();
	$cert->send_preview_certificate($id_certificate, array());
}

function pcertificateDispatch($op)
{
	if(isset($_POST['undo'])) $op = 'certificate';
	if(isset($_POST['undo_report'])) $op = 'report_certificate';
	if(isset($_POST['certificate_course_selection'])) $op = 'view_report_certificate';
	if(isset($_POST['certificate_course_selection_back'])) $op = 'report_certificate';
	switch($op) {
		case "elemcertificate":
			list_element_certificate();
		break;
		case "view_report_certificate":
			view_report_certificate();
		break;
		case "del_report_certificate":
			del_report_certificate();
		break;
		case "send_certificate":
			send_certificate();
		break;
		case "print_certificate":
			print_certificate();
		break;
		case "preview":
			preview();
		break;
	}
}
?>