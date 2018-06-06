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

define('IS_META', 1);

/**
 * @package  DoceboLms
 * @version  $Id: meta_certificate.php,v 1
 * @author	 Marco Valloni <marco [at] docebo [dot] com>
 */

function metaCertificate()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$mod_perm	= checkPerm('mod', true);

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_META_CERTIFICATE_CAPTION'), $lang->def('_META_CERTIFICATE_SUMMARY'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink("index.php?modname=meta_certificate&amp;op=meta_certificate");
	$ini=$tb->getSelectedElement();

	$form = new Form();

	if (isset($_POST['toggle_filter']))
	{
		unset($_POST['filter_text']);
	}

	//search query of certificates
	$query_certificate = "
	SELECT id_certificate, code, name, description
	FROM ".$GLOBALS['prefix_lms']."_certificate"
	." WHERE meta = 1";

	if (isset($_POST['filter']))
	{
		if ($_POST['filter_text'] !== '')
			$query_certificate .=	" AND ( name LIKE '%".$_POST['filter_text']."%'"
									." OR code LIKE '%".$_POST['filter_text']."%' )";
	}
	$query_certificate .= " ORDER BY id_certificate
	LIMIT $ini,".Get::sett('visuItem');

	$query_certificate_tot = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_certificate";

	$re_certificate = sql_query($query_certificate);

	list($tot_certificate) = sql_fetch_row(sql_query($query_certificate_tot));

	$type_h = array('', '', '', 'image', 'image');

	$cont_h	= array($lang->def('_CODE'),
					$lang->def('_NAME'),
					$lang->def('_DESCRIPTION'));
	if($mod_perm) $cont_h[] = Lang::t('_TEMPLATE', 'certificate');

	$cont_h[] = '<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def( '_PREVIEW' ).'" />';

	if($mod_perm)
	{
		$cont_h[] =	Get::img('standard/moduser.png', Lang::t('_TITLE_ASSIGN_META_CERTIFICATE', 'certificate'));
		$type_h[] =	'image';

		$cont_h[] =	Get::sprite('subs_print', Lang::t('_TITLE_CREATE_META_CERTIFICATE', 'certificate'));
		$type_h[] =	'image';

		$cont_h[] =	'<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" '
					.'alt="'.$lang->def('_MOD').'" />';
		$type_h[] =	'image';

		$cont_h[] =	'<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" '
					.'alt="'.$lang->def('_DEL').'"" />';
		$type_h[] =	'image';

	}

	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	while(list($id_certificate, $code, $name, $descr) = sql_fetch_row($re_certificate))
	{
		$title = strip_tags($name);

		$cont = array(
			$code,
			$name,
			Util::cut($descr)
		);
		if($mod_perm) $cont[] = '<a href="index.php?modname=meta_certificate&amp;op=elemmetacertificate&amp;id_certificate='.$id_certificate.'" '
				.'title="'.Lang::t('_TEMPLATE', 'certificate').'">'
				.Lang::t('_TEMPLATE', 'certificate').'</a>';


		$cont[]	= '<a href="index.php?modname=meta_certificate&amp;op=preview&amp;id_certificate='.$id_certificate.'">'
			.'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('_PREVIEW').' : '.$title.'" /></a>';

		if($mod_perm) {

			$cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=assign&amp;id_certificate='.$id_certificate.'" '
						.'title="'.$lang->def('_TITLE_ASSIGN_META_CERTIFICATE').' : '.$name.'">'
						.Get::img('standard/moduser.png', Lang::t('_TITLE_ASSIGN_META_CERTIFICATE', 'certificate'))
						.'</a>';
			/*
			$cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=create&amp;id_certificate='.$id_certificate.'" '
						.'title="'.$lang->def('_TITLE_CREATE_META_CERTIFICATE').' : '.$name.'">'
						.Get::img('course/certificate.png', Lang::t('_TITLE_CREATE_META_CERTIFICATE', 'certificate'))
						.'</a>';*/
			$cont[] = Get::sprite_link('subs_print', 'index.php?modname=meta_certificate&amp;op=create&amp;id_certificate='.$id_certificate, Lang::t('_TITLE_CREATE_META_CERTIFICATE', 'certificate'));

			$cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=modmetacertificate&amp;id_certificate='.$id_certificate.'" '
						.'title="'.$lang->def('_MOD').' : '.$name.'">'
						.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$title.'" /></a>';

			$cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=delmetacertificate&amp;id_certificate='.$id_certificate.'" '
						.'title="'.$lang->def('_DEL').' : '.$name.'">'
						.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$title.'" /></a>';
		}

		$tb->addBody($cont);
	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delmetacertificate]');

	if($mod_perm)
	{
		$tb->addActionAdd(	'<a class="ico-wt-sprite subs_add" href="index.php?modname=meta_certificate&amp;op=addmetacertificate" title="'.$lang->def('_NEW_CERTIFICATE').'"><span>'
							.$lang->def('_NEW_CERTIFICATE').'</span></a>');
	}

	$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE'), 'certificate')
				.'<div class="std_block">'	);

	$out->add(	$form->openForm('certificate_filter', 'index.php?modname=meta_certificate&amp;op=meta_certificate')
		.'<div class="quick_search_form">
			<div>
				<div class="simple_search_box">'
					.Form::getInputTextfield("search_t", "filter_text", "filter_text", Get::req('filter_text', DOTY_MIXED, ''), '', 255, '' )
					.Form::getButton("filter", "filter", Lang::t('_SEARCH', 'standard'), "search_b")
					.Form::getButton("toggle_filter", "toggle_filter", Lang::t('_RESET', 'standard'), "reset_b")
				.'</div>
			</div>
		</div>'



			/*.$form->openElementSpace()
				.$form->getTextfield($lang->def('_NAME'), 'name_filter', 'name_filter', '255', (isset($_POST['name_filter']) && $_POST['name_filter']!== '' ? $_POST['name_filter'] : ''))
				.$form->getTextfield($lang->def('_CODE'), 'code_filter', 'code_filter', '255', (isset($_POST['code_filter']) && $_POST['code_filter']!== '' ? $_POST['code_filter'] : ''))
				.$form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('filter', 'filter', $lang->def('_FILTER'))
				.$form->getButton('toggle_filter', 'toggle_filter', $lang->def('_RESET'))
				.$form->closeButtonSpace()*/
				.$form->closeForm());

	if(isset($_GET['result']))
	{
		switch($_GET['result'])
		{
			case "ok":
				$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
			break;
			case "err":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
			case "err_del":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
		}
	}

	$out->add($tb->getTable().$tb->getNavBar($ini, $tot_certificate).'</div>');
}

function editMetaCertificate($load = false)
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$form = new Form();

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$id_certificate = importVar('id_certificate', true, 0);
	$all_languages 	= Docebo::langManager()->getAllLanguages();
	$languages = array();
	foreach($all_languages as $k => $v) { $languages[$v[0]] = $v[1]; }

	if($load) {

		$query_certificate = "
		SELECT code, name, base_language, description, user_release
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";
		list($code, $name, $base_language, $descr, $user_release) = sql_fetch_row(sql_query($query_certificate));
	} else {

		$code = '';
		$name 	= '';
		$descr 	= '';
		$user_release = 1;
		$base_language = getLanguage();
	}

	$page_title = array(
		'index.php?modname=meta_certificate&amp;op=meta_certificate' => $lang->def('_TITLE_META_CERTIFICATE'),
		( $load ? $lang->def('_MOD') : $lang->def('_NEW_CERTIFICATE') )
	);
	$out->add(getTitleArea($page_title, 'certificate')
			.'<div class="std_block">'
			.getBackUi( 'index.php?modname=meta_certificate&amp;op=meta_certificate', $lang->def('_BACK') )

			.$form->openForm('adviceform', 'index.php?modname=meta_certificate&amp;op=savemetacertificate')
	);
	if($load) {

		$out->add($form->getHidden('id_certificate', 'id_certificate', $id_certificate)
				.$form->getHidden('load', 'load', 1)	);
	}
	$out->add(
		$form->openElementSpace()
		.$form->getTextfield($lang->def('_CODE'), 'code', 'code', 255, $code)
		.$form->getTextfield($lang->def('_NAME'), 'name', 'name', 255, $name)

		.Form::getDropdown( $lang->def('_BASE_LANGUAGE'),
							'base_language',
							'base_language',
							$languages,
							$base_language)

		.$form->getCheckbox($lang->def('_USER_RELEASE'), 'user_release', 'user_release', '1', $user_release)

		.$form->getTextarea($lang->def('_DESCRIPTION'), 'descr', 'descr', $descr)
		.$form->closeElementSpace()
		.$form->openButtonSpace()
		.$form->getButton('certificate', 'certificate', ( $load ? $lang->def('_SAVE') : $lang->def('_INSERT') ) )
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm()
		.'</div>'
	);
}

function saveMetaCertificate()
{
	checkPerm('mod');

	$id_certificate = importVar('id_certificate', true, 0);
	$load = importVar('load', true, 0);

	$all_languages = Docebo::langManager()->getAllLangCode();
	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	if($_POST['name'] == '')
		$_POST['name'] = $lang->def('_NOTITLE');

	if(isset($_POST['structure_certificate']))
	{
		$path 	= '/appLms/certificate/';
		$path 	= $path.( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

		$bgimage = manageCertificateFile(	'bgimage',
											$_POST["old_bgimage"],
											$path,
											isset($_POST['file_to_del']['bgimage']) );

		if(!$bgimage)
			$bgimage = '';

		$query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_certificate
		SET	cert_structure = '".$_POST['structure']."',
			orientation = '".$_POST['orientation']."'
			". ( $bgimage != '' && !isset($_POST['file_to_del']['bgimage']) ? " , bgimage = '".$bgimage."'" : '' )."
		WHERE id_certificate = '".$id_certificate."'";

		if(!sql_query($query_insert)) Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=err');
		Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=ok');
	}
	if($load == 1)
	{
		$query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_certificate
		SET	code = '".$_POST['code']."',
			name = '".$_POST['name']."',
			base_language = '".$_POST['base_language']."',
			description = '".$_POST['descr']."',
			user_release = '".(isset($_POST['user_release']) ? 1 : 0)."'
		WHERE id_certificate = '".$id_certificate."'";

		if(!sql_query($query_insert)) Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=err');
		Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=ok');
	}
	else
	{
		$query_insert = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_certificate
		( code, name, base_language, description, meta, user_release ) VALUES
		( 	'".$_POST['code']."' ,
			'".$_POST['name']."' ,
		 	'".$_POST['base_language']."' ,
			'".$_POST['descr']."',
			'1',
			'".(isset($_POST['user_release']) ? 1 : 0)."'
		)";

		if(!sql_query($query_insert))
			Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=err');

		list($id_certificate) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			Util::jump_to('index.php?modname=meta_certificate&op=elemmetacertificate&id_certificate='.$id_certificate);
	}
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
	if(($delete_old || $arr_new_file !== false) && $old_file != '') {

		// the flag for file delete is checked or a new file was uploaded ---------------------
		sl_unlink($path.$old_file);
	}

	if(!empty($arr_new_file)) {

		// if present load the new file --------------------------------------------------------
		$filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];

		if(!sl_upload($arr_new_file['tmp_name'], $path.$filename)) {

			return false;
		}
		else return $filename;
	}
	sl_close_fileoperations();
	return '';
}

function list_element_meta_certificate()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$mod_perm		= checkPerm('mod', true);
	$id_certificate = importVar('id_certificate', true, 0);

	// create a language istance for module admin_certificate
	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$form = new Form();

	$page_title = array(
		'index.php?modname=meta_certificate&amp;op=meta_certificate' => $lang->def('_TITLE_META_CERTIFICATE'),
		$lang->def('_STRUCTURE_META_CERTIFICATE'));

	$out->add(getTitleArea($page_title, 'certificate')
			.'<div class="std_block">'
			.getBackUi( 'index.php?modname=meta_certificate&amp;op=meta_certificate', $lang->def('_BACK') )
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

	// $out->add( getInfoUi($lang->def('_CERTIFICATE_WARNING')) );

	$out->add($form->openForm('structure_certificate_form', 'index.php?modname=meta_certificate&amp;op=savemetacertificate', false, false, 'multipart/form-data'));
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
		.$form->getButton('structure_certificate', 'structure_certificate', ($lang->def('_SAVE') ) )
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
			$instance = new $class_name(0, 0, 1);
			$this_subs = $instance->getSubstitutionTags();
			foreach($this_subs as $tag => $description)
			{
				$tb->addBody(array($tag, $description));
			}
		}
	}

	$out->add($tb->getTable());

	$out->add('</div>');
}

function delMetaCertificate() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$id_certificate = importVar('id_certificate', true, 0);
	$lang 		=& DoceboLanguage::createInstance('certificate', 'lms');

	if(Get::req('confirm', DOTY_INT, 0) == 1) {

		$res = true;

		$query = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'";

		if(sql_query($query))
		{
			$query =	"SELECT idMetaCertificate"
						." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
						." WHERE idCertificate = '".$id_certificate."'";

			$result = sql_query($query);

			while(list($id_meta) = sql_fetch_row($result))
			{
				$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
							." WHERE idMetaCertificate = '".$id_meta."'";

				if(!sql_query($query))
					$res = false;

				$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
							." WHERE idMetaCertificate = '".$id_meta."'";

				if(!sql_query($query))
					$res = false;
			}
		}
		else
			Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=err_del');

		if(!$res)
			Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=err_del');
		else
			Util::jump_to('index.php?modname=meta_certificate&op=meta_certificate&result=ok');
	}
	else
	{
		list($name, $descr) = sql_fetch_row(sql_query("
		SELECT name, description
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'"));

		$form = new Form();
		$page_title = array(
			'index.php?modname=meta_certificate&amp;op=meta_certificate' => $lang->def('_TITLE_CERTIFICATE'),
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'certificate')
			.'<div class="std_block">'
			.$form->openForm('del_certificate', 'index.php?modname=meta_certificate&amp;op=delmetacertificate')
			.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
								.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr,
							false,
							'confirm',
							'undo'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function assignMetaCertificate()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$id_certificate = importVar('id_certificate', true, 0);

	$_SESSION['meta_certificate'] = array();

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_META_CERTIFICATE_ASSIGN_CAPTION'), $lang->def('_META_CERTIFICATE_ASSIGN_CAPTION'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink("index.php?modname=meta_certificate&amp;op=assign");
	$ini = $tb->getSelectedElement();

	$query =	"SELECT idMetaCertificate, title, description"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
				." WHERE idCertificate = '".$id_certificate."'";

	$result = sql_query($query);

	$type_h = array('', '', 'image', 'image', 'image', 'image');

	$cont_h	= array($lang->def('_NAME'),
					$lang->def('_DESCRIPTION'),
					'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def( '_DETAILS' ).'" title="'.$lang->def( '_DETAILS' ).'" />',
					'<img src="'.getPathImage().'standard/modelem.png" alt="'.$lang->def( '_MOD' ).'" title="'.$lang->def( '_MOD' ).'" />',
					'<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
					'<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'"" />');

	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	while(list($id_meta_certificate, $title, $description) = sql_fetch_row($result))
	{
		$tb->addBody(array(	stripslashes($title),
							stripslashes($description),
							'<a href="index.php?modname=meta_certificate&amp;op=viewdetails&amp;idmeta='.$id_meta_certificate.'&amp;id_certificate='.$id_certificate.'"><img src="'.getPathImage().'standard/view.png" alt="'.$lang->def( '_DETAILS' ).'" title="'.$lang->def( '_DETAILS' ).'" /></a>',
							'<a href="index.php?modname=meta_certificate&amp;op=modassignment&amp;idmeta='.$id_meta_certificate.'&amp;id_certificate='.$id_certificate.'"><img src="'.getPathImage().'standard/modelem.png" alt="'.$lang->def( '_MOD' ).'" title="'.$lang->def( '_MOD' ).'" /></a>',
							'<a href="index.php?modname=meta_certificate&amp;op=modassignmetacertificate&amp;idmeta='.$id_meta_certificate.'&amp;id_certificate='.$id_certificate.'"><img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>',
							'<a href="index.php?modname=meta_certificate&amp;op=delassignmetacertificate&amp;idmeta='.$id_meta_certificate.'&amp;id_certificate='.$id_certificate.'"><img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'"" /></a>'));
	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delassignmetacertificate]');

	$tb->addActionAdd(	'<a class="new_element_link" href="index.php?modname=meta_certificate&amp;op=new_assign&amp;id_certificate='.$id_certificate.'" title="'.$lang->def('_NEW_ASSING_META_CERTIFICATE').'">'
						.$lang->def('_NEW_ASSING_META_CERTIFICATE').'</a>');

	$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
				.'<div class="std_block">');

	if(isset($_GET['result']))
	{
		switch($_GET['result'])
		{
			case "ok":
				$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
			break;
			case "err":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
			case "err_del":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
			case "err_info":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
			case "err_mod_info":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
			case "error_mod_assign":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
		}
	}

	$out->add(	$tb->getTable()
				.$tb->getNavBar($ini, sql_num_rows($result))
				.getBackUi('index.php?modname=meta_certificate&amp;op=meta_certificate', $lang->def('_BACK'))
				.'</div>');
}

function newAssignMetaCertificate()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$id_certificate = importVar('id_certificate', true, 0);

	$step = Get::req('step', DOTY_INT, 0);

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$acl_man =& Docebo::user()->getAclManager();
	$aclManager = new DoceboACLManager();
	$user_select = new UserSelector();
	$form = new Form();
	$sel = new Course_Manager();
	$course_man = new Man_Course();

	if(isset($_POST['next']))
	{
		$step++;
		$_SESSION['meta_certificate']['name'] = $_POST['name'];
		$_SESSION['meta_certificate']['description'] = $_POST['description'];
	}

	if(isset($_POST['okselector']))
	{
		$user_selected 	= $user_select->getSelection($_POST);
		$_SESSION['meta_certificate']['users'] = $user_selected;
		$step++;
	}

	if(isset($_POST['import_filter']))
	{
		$_SESSION['meta_certificate']['course'] = $sel->getCourseSelection($_POST);
		$step++;
	}

	if(isset($_POST['insert']))
		$step++;

	if($step == 4)
	{
		$query_meta_certificate =	"INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta (idCertificate, title, description)"
									." VALUES ('".$id_certificate."', '".addslashes($_SESSION['meta_certificate']['name'])."', '".addslashes($_SESSION['meta_certificate']['description'])."')";

		if(sql_query($query_meta_certificate))
		{
			list($id_meta_certificate) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID() FROM ".$GLOBALS['prefix_lms']."_certificate_meta"));

			$array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['users']);

			$query_course =	"INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta_course (idMetaCertificate, idUser, idCourse)"
							." VALUES ";

			$array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['users']);

			$array_user = array_unique($array_user);

			$first = true;

			foreach($array_user as $id_user)
				foreach($_SESSION['meta_certificate']['course'] as $id_course)
					if(isset($_POST['_'.$id_user.'_'.$id_course.'_']))
						if ($first)
						{
							$query_course .= "('".$id_meta_certificate."', '".$id_user."', '".$id_course."')";
							$first = false;
						}
						else
							$query_course .= ", ('".$id_meta_certificate."', '".$id_user."', '".$id_course."')";

			$res = sql_query($query_course);

			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=ok');
		}
		else
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=error_info');
	}
	elseif($step == 3)
	{
		YuiLib::load();
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		$tb	= new Table(0, $lang->def('_META_CERTIFICATE_NEW_ASSIGN_CAPTION'), $lang->def('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
		$tb->setLink('index.php?modname=meta_certificate&amp;op=new_assign');

		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('new_assign_step_3', 'index.php?modname=meta_certificate&amp;op=new_assign')
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->getHidden('step', 'step', 3));

		$form_name = 'new_assign_step_3';

		$type_h = array('', '');
		$cont_h = array($lang->def('_FULLNAME'), $lang->def('_USERNAME'));

		foreach($_SESSION['meta_certificate']['course'] as $id_course)
		{
			$type_h[] = 'align_center';

			$course_info = $course_man->getCourseInfo($id_course);

			$cont_h[] = $course_info['code'].' - '.$course_info['name'];
		}

		$type_h[] = 'image';
		$cont_h[] = $lang->def('_CHECKALL');

		$type_h[] = 'image';
		$cont_h[] = $lang->def('_UNCHECKALL');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		reset($_SESSION['meta_certificate']['course']);

		$array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['users']);

		$array_user = array_unique($array_user);

		$query =	"SELECT idst"
					." FROM ".$GLOBALS['prefix_fw']."_user"
					." WHERE idst IN (".implode(',',$array_user).")"
					." ORDER BY lastname, firstname, userid";

		$result = sql_query($query);

		$array_user = array();

		while(list($id_user) = sql_fetch_row($result))
			$array_user[] = $id_user;

		foreach($array_user as $id_user)
		{
			$cont = array();

			$user_info = $acl_man->getUser($id_user, false);

			$cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

			$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

			foreach($_SESSION['meta_certificate']['course'] as $id_course)
			{
				if(isset($_POST['_'.$id_user.'_'.$id_course.'_']) || isset($_POST['select_all']))
					$checked = true;
				else
					$checked = false;

				$cont[] = $form->getCheckbox('', '_'.$id_user.'_'.$id_course.'_', '_'.$id_user.'_'.$id_course.'_', 1, $checked);
			}

			$cont[] =	'<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', true); return false;">'
						.$lang->def('_CHECKALL')
						.'</a>';
			$cont[] =	'<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', false); return false;">'
						.$lang->def('_UNCHECKALL')
						.'</a>';

			$tb->addBody($cont);
		}

		reset($_SESSION['meta_certificate']['course']);

		$cont = array();

		$cont[] = '';
		$cont[] = '';

		foreach($_SESSION['meta_certificate']['course'] as $id_course)
		{
			$cont[] =	'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', true); return false;">'
						.$lang->def('_CHECKALL')
						.'</a><br/>'
						.'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', false); return false;">'
						.$lang->def('_UNCHECKALL')
						.'</a>';
		}

		$cont[] = '';
		$cont[] = '';

		$tb->addBody($cont);

		$out->add(	$form->openElementSpace()
					.$tb->getTable()
					.$form->closeElementSpace()
					.$form->openButtonSpace()
					.$form->getButton('select_all', 'select_all', $lang->def('_SELECT_ALL'))
					.$form->getButton('insert', 'insert', $lang->def('_INSERT'))
					.$form->getButton('undo_assign', 'undo_assign', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm());

	}
	elseif($step == 2)
	{
		$sel->setLink('index.php?modname=meta_certificate&amp;op=new_assign');

		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('new_assign_step_2', 'index.php?modname=meta_certificate&amp;op=new_assign')
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->getHidden('step', 'step', 2));



		$sel->loadSelector();

		$out->add(	Form::getHidden('update_tempdata', 'update_tempdata', 1)
					.Form::openButtonSpace()
					.Form::getBreakRow()
			  		.Form::getButton('ok_filter', 'import_filter', $lang->def('_NEXT'))
			  		.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO'))
			  		.Form::closeButtonSpace()
					.Form::closeForm()
					.'</div>');
	}
	elseif($step == 1)
	{
		$user_select->show_orgchart_simple_selector = FALSE;
		$user_select->multi_choice = TRUE;

		$user_select->addFormInfo(	$form->getHidden('step', 'step', 1)
									.$form->getHidden('id_certificate', 'id_certificate', $id_certificate));

		$user_select->setPageTitle(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
									.'<div class="std_block">');

		$user_select->loadSelector('index.php?modname=meta_certificate&amp;op=new_assign',
				false,
				$lang->def('_USER_FOR_META_CERTIFICATE_ASSIGN'),
				true);
	}
	else
	{
		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('new_assign_step_0', 'index.php?modname=meta_certificate&amp;op=new_assign')
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->openElementSpace()
					.$form->getTextfield($lang->def('_NAME'), 'name', 'name', '255')
					.$form->getSimpleTextarea($lang->def('_DESCRIPTION'), 'description', 'description')
					.$form->closeElementSpace()
					.$form->openButtonSpace()
					.$form->getButton('next', 'next', $lang->def('_NEXT'))
					.$form->getButton('undo_assign', 'undo_assign', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm()
					.'</div>');
	}
}

function delAssignMetaCertificate()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$id_certificate = Get::req('id_certificate', DOTY_INT, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	if(Get::req('confirm', DOTY_INT, 0) == 1) {

		$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
					." WHERE idMetaCertificate = '".$id_meta."'";

		if(sql_query($query))
		{
			$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
						." WHERE idMetaCertificate = '".$id_meta."'";

			if(sql_query($query))
				Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=ok');
			else
				Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=err_del');
		}
		else
		Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=err_del');
	} else {

		list($name, $descr) = sql_fetch_row(sql_query("
		SELECT title, description
		FROM ".$GLOBALS['prefix_lms']."_certificate_meta
		WHERE idMetaCertificate = '".$id_meta."'"));

		$form = new Form();

		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_DELETING'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('del_certificate', 'index.php?modname=meta_certificate&amp;op=delassignmetacertificate')
					.$form->getHidden('idmeta', 'idmeta', $id_meta)
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.getDeleteUi(	$lang->def('_AREYOUSURE'),
									'<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
									.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr,
									false,
									'confirm',
									'undo_assign')
					.$form->closeForm()
					.'</div>');
	}
}

function modAssignMetaCertificate()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$id_certificate = Get::req('id_certificate', DOTY_INT, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$form = new Form();

	if(isset($_POST['confirm']))
	{
		$title = addslashes(Get::req('name', DOTY_MIXED, ''));
		$description = addslashes(Get::req('description', DOTY_MIXED, ''));


		$query =	"UPDATE ".$GLOBALS['prefix_lms']."_certificate_meta"
					." SET title = '".$title."',"
					." description = '".$description."'"
					." WHERE idMetaCertificate = '".$id_meta."'";

		$result = sql_query($query);

		if($result)
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=ok');
		else
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=err_mod_info');
	}
	else
	{
		list($title, $description) = sql_fetch_row(sql_query(	"SELECT title, description"
																	." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
																	." WHERE idMetaCertificate = '".$id_meta."'"));

		$out->add(	getTitleArea($lang->def('_MOD'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('del_certificate', 'index.php?modname=meta_certificate&amp;op=modassignmetacertificate')
					.$form->getHidden('idmeta', 'idmeta', $id_meta)
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->openElementSpace()
					.$form->getTextfield($lang->def('_NAME'), 'name', 'name', '255', $title)
					.$form->getSimpleTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
					.$form->closeElementSpace()
					.$form->openButtonSpace()
					.$form->getButton('confirm', 'confirm', $lang->def('_SAVE'))
					.$form->getButton('undo_assign', 'undo_assign', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm()
					.'</div>');
	}
}

function viewDetails()
{
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.table.php');

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$id_certificate = Get::req('id_certificate', DOTY_INT, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$lang =& DoceboLanguage::createInstance('course', 'lms');
	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$acl_man =& Docebo::user()->getAclManager();

	$course_man = new Man_Course();

	//Take user for the meta certificate
	$query =	"SELECT DISTINCT idUser"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
				." WHERE idMetaCertificate = '".$id_meta."'";

	$result = sql_query($query);

	$users = array();

	while(list($id_user) = sql_fetch_row($result))
		$users[$id_user] = $id_user;

	//Take courses for the meta certificate
	$query =	"SELECT DISTINCT idCourse"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
				." WHERE idMetaCertificate = '".$id_meta."'";

	$result = sql_query($query);

	$courses = array();

	while(list($id_course) = sql_fetch_row($result))
		$courses[$id_course] = $id_course;

	//Control assign for the meta certificate
	$query =	"SELECT idUser, idCourse"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
				." WHERE idMetaCertificate = '".$id_meta."'";

	$result = sql_query($query);

	$status = array();

	while(list($id_user, $id_course) = sql_fetch_row($result))
		$status[$id_user][$id_course] = 1;

	//Table creation
	$tb	= new Table(0, $lang->def('_META_CERTIFICATE_DETAILS_CAPTION'), $lang->def('_META_CERTIFICATE_DETAILS_CAPTION'));
	$tb->setLink('index.php?modname=meta_certificate&amp;op=viewdetails&amp;idmeta='.$id_meta.'&amp;id_certificate='.$id_certificate);

	$type_h = array('', '');
	$cont_h = array($lang->def('_FULLNAME'), $lang->def('_USERNAME'));

	foreach($courses as $id_course)
	{
		$type_h[] = 'align_center';

		$course_info = $course_man->getCourseInfo($id_course);

		$cont_h[] = $course_info['code'].' - '.$course_info['name'];
	}

	$type_h[] = 'align_center';

	$cont_h[] = $lang->def('_META_CERTIFICATE_PROGRESS');

	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	reset($courses);

	$query =	"SELECT idst"
				." FROM ".$GLOBALS['prefix_fw']."_user"
				." WHERE idst IN (".implode(',',$users).")"
				." ORDER BY userid";

	$result = sql_query($query);

	$array_user = array();

	while(list($id_user) = sql_fetch_row($result))
		$array_user[] = $id_user;

	foreach($array_user as $id_user)
	{
		$cont = array();

		$user_info = $acl_man->getUser($id_user, false);

		$cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

		$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

		$total_course_assigned = 0;
		$total_course_ended = 0;

		foreach($courses as $id_course)
		{
			if(!isset($status[$id_user][$id_course]))
				$cont[] = $lang->def('_NOT_ASSIGNED');
			else
			{
				$total_course_assigned++;

				$query =	"SELECT COUNT(*)"
							." FROM ".$GLOBALS['prefix_lms']."_courseuser"
							." WHERE idCourse = '".$id_course."'"
							." AND idUser = '".$id_user."'"
							." AND status = '"._CUS_END."'";

				list($control) = sql_fetch_row(sql_query($query));

				if($control)
				{
					$total_course_ended++;
					$cont[] = $lang->def('_END', 'course');
				}
				else
					$cont[] = $lang->def('_NOT_ENDED');
			}
		}

		$cont[] = $total_course_ended.' / '.$total_course_assigned;

		$tb->addBody($cont);
	}

	$out->add(	getTitleArea($lang->def('_DETAILS'), 'certificate')
				.'<div class="std_block">'
				.getBackUi('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate, $lang->def('_BACK'))
				.$tb->getTable()
				.getBackUi('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate, $lang->def('_BACK'))
				.'</div>');
}

function modAssignmentAssignMetaCertificate()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$id_certificate = importVar('id_certificate', true, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$step = Get::req('step', DOTY_INT, 0);

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$acl_man =& Docebo::user()->getAclManager();
	$aclManager = new DoceboACLManager();
	$user_select = new UserSelector();
	$form = new Form();
	$sel = new Course_Manager();
	$course_man = new Man_Course();

	if(isset($_POST['okselector']))
	{
		$user_selected 	= $user_select->getSelection($_POST);
		$_SESSION['meta_certificate']['users'] = $user_selected;
		$step++;
	}

	if(isset($_POST['import_filter']))
	{
		$_SESSION['meta_certificate']['course'] = $sel->getCourseSelection($_POST);
		$step++;
	}

	if(isset($_POST['insert']))
		$step++;

	if($step == 3)
	{
		$array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['users']);

		$array_user = array_unique($array_user);

		$res = true;

		$user_reset = array();
		$course_reset = array();
		$reasign = array();

		//array reasign

		$query =	"SELECT idUser, idCourse"
					." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
					." WHERE idMetaCertificate = '".$id_meta."'";

		$result = sql_query($query);

		while(list($id_user, $id_course) = sql_fetch_row($result))
			$reasign[$id_user][$id_course] = 1;

		//array user_reset

		$query =	"SELECT DISTINCT idUser"
					." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
					." WHERE idMetaCertificate = '".$id_meta."'";

		$result = sql_query($query);

		while(list($id_user) = sql_fetch_row($result))
			$user_reset[$id_user] = $id_user;

		//array course_reset

		$query =	"SELECT DISTINCT idCourse"
					." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
					." WHERE idMetaCertificate = '".$id_meta."'";

		$result = sql_query($query);

		while(list($id_course) = sql_fetch_row($result))
			$course_reset[$id_course] = $id_course;

		//finish array initialization

		$query_course =	"INSERT INTO ".$GLOBALS['prefix_lms']."_certificate_meta_course (idMetaCertificate, idUser, idCourse)"
						." VALUES ";

		$first = true;

		$array_user_flipped = array_flip($array_user);

		foreach($user_reset as $id_user)
			if(!isset($array_user_flipped[$id_user]))
			{
				$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
							." WHERE idUser = '".$id_user."'"
							." AND idMetaCertificate = '".$id_meta."'";

				if(!sql_query($query))
					$res = false;
			}

		if(!$res)
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=error_mod_assign');

		foreach($course_reset as $id_course)
			if(!isset($_SESSION['meta_certificate']['course'][$id_course]))
			{
				$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
							." WHERE idCourse = '".$id_course."'"
							." AND idMetaCertificate = '".$id_meta."'";

				if(!sql_query($query))
					$res = false;
			}

		if(!$res)
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=error_mod_assign');

		reset($_SESSION['meta_certificate']['course']);

		foreach($array_user as $id_user)
			foreach($_SESSION['meta_certificate']['course'] as $id_course)
			{
				if(isset($_POST['_'.$id_user.'_'.$id_course.'_']))
				{
					if(!isset($reasign[$id_user][$id_course]))
						if ($first)
						{
							$query_course .= "('".$id_meta."', '".$id_user."', '".$id_course."')";
							$first = false;
						}
						else
							$query_course .= ", ('".$id_meta."', '".$id_user."', '".$id_course."')";
				}
				else
					if(isset($reasign[$id_user][$id_course]))
					{
						$query = 	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
									." WHERE idUser = '".$id_user."'"
									." AND idCourse = '".$id_course."'"
									." AND idMetaCertificate = '".$id_meta."'";

						if(!sql_query($query))
							$res = false;
					}
			}

		if(!$res)
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=error_mod_assign');

		$res = sql_query($query_course);

		if($res)
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=ok');
		else
			Util::jump_to('index.php?modname=meta_certificate&op=assign&id_certificate='.$id_certificate.'&res=error_mod_assign');
	}
	elseif($step == 2)
	{
		YuiLib::load();
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);

		$tb	= new Table(0, $lang->def('_META_CERTIFICATE_NEW_ASSIGN_CAPTION'), $lang->def('_META_CERTIFICATE_NEW_ASSIGN_SUMMARY'));
		$tb->setLink('index.php?modname=meta_certificate&amp;op=modassignment');

		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('new_assign_step_2', 'index.php?modname=meta_certificate&amp;op=modassignment')
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->getHidden('idmeta', 'idmeta', $id_meta)
					.$form->getHidden('step', 'step', 2)
					.$form->getHidden('reasign', 'reasign', 1));

		$reasign = array();

		if(!isset($_POST['reasign']))
		{
			$query =	"SELECT idUser, idCourse"
						." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
						." WHERE idMetaCertificate = '".$id_meta."'";

			$result = sql_query($query);

			while(list($id_user, $id_course) = sql_fetch_row($result))
				$reasign[$id_user][$id_course] = 1;
		}

		$form_name = 'new_assign_step_2';

		$type_h = array('', '');
		$cont_h = array($lang->def('_FULLNAME'), $lang->def('_USERNAME'));

		foreach($_SESSION['meta_certificate']['course'] as $id_course)
		{
			$type_h[] = 'align_center';

			$course_info = $course_man->getCourseInfo($id_course);

			$cont_h[] = $course_info['code'].' - '.$course_info['name'];
		}

		$type_h[] = 'image';
		$cont_h[] = $lang->def('_CHECKALL');

		$type_h[] = 'image';
		$cont_h[] = $lang->def('_UNCHECKALL');

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		reset($_SESSION['meta_certificate']['course']);

		$array_user =& $aclManager->getAllUsersFromIdst($_SESSION['meta_certificate']['users']);

		$array_user = array_unique($array_user);

		$query =	"SELECT idst"
					." FROM ".$GLOBALS['prefix_fw']."_user"
					." WHERE idst IN (".implode(',',$array_user).")"
					." ORDER BY userid";

		$result = sql_query($query);

		$array_user = array();

		while(list($id_user) = sql_fetch_row($result))
			$array_user[] = $id_user;

		foreach($array_user as $id_user)
		{
			$cont = array();

			$user_info = $acl_man->getUser($id_user, false);

			$cont[] = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME];

			$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);

			foreach($_SESSION['meta_certificate']['course'] as $id_course)
			{
				if(isset($_POST['_'.$id_user.'_'.$id_course.'_']) || isset($_POST['select_all']) || isset($reasign[$id_user][$id_course]))
					$checked = true;
				else
					$checked = false;

				$cont[] = $form->getCheckbox('', '_'.$id_user.'_'.$id_course.'_', '_'.$id_user.'_'.$id_course.'_', 1, $checked);
			}

			$cont[] =	'<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', true); return false;">'
						.$lang->def('_CHECKALL')
						.'</a>';
			$cont[] =	'<a href="javascript:;" onclick="checkall_fromback_meta(\''.$form_name.'\', \''.$id_user.'\', false); return false;">'
						.$lang->def('_UNCHECKALL')
						.'</a>';

			$tb->addBody($cont);
		}

		reset($_SESSION['meta_certificate']['course']);

		$cont = array();

		$cont[] = '';
		$cont[] = '';

		foreach($_SESSION['meta_certificate']['course'] as $id_course)
		{
			$cont[] =	'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', true); return false;">'
						.$lang->def('_CHECKALL')
						.'</a><br/>'
						.'<a href="javascript:;" onclick="checkall_meta(\''.$form_name.'\', \''.$id_course.'\', false); return false;">'
						.$lang->def('_UNCHECKALL')
						.'</a>';
		}

		$cont[] = '';
		$cont[] = '';

		$tb->addBody($cont);

		$out->add(	$form->openElementSpace()
					.$tb->getTable()
					.$form->closeElementSpace()
					.$form->openButtonSpace()
					.$form->getButton('select_all', 'select_all', $lang->def('_SELECT_ALL'))
					.$form->getButton('insert', 'insert', $lang->def('_INSERT'))
					.$form->getButton('undo_assign', 'undo_assign', $lang->def('_UNDO'))
					.$form->closeButtonSpace()
					.$form->closeForm());

	}
	elseif($step == 1)
	{
		$sel->setLink('index.php?modname=meta_certificate&amp;op=modassignment');

		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
					.'<div class="std_block">'
					.$form->openForm('new_assign_step_1', 'index.php?modname=meta_certificate&amp;op=modassignment')
					.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
					.$form->getHidden('idmeta', 'idmeta', $id_meta)
					.$form->getHidden('step', 'step', 1)
					.$form->getHidden('course_reload', 'course_reload', 1));

		if(!isset($_POST['course_reload']))
		{
			$query =	"SELECT DISTINCT idCourse"
						." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
						." WHERE idMetaCertificate = '".$id_meta."'";

			$result = sql_query($query);

			$course_reset = array();

			while(list($id_course) = sql_fetch_row($result))
				$course_reset[$id_course] = $id_course;

			$sel->resetCourseSelection($course_reset);
		}

		$sel->loadSelector();

		$out->add(	Form::getHidden('update_tempdata', 'update_tempdata', 1)
					.Form::openButtonSpace()
					.Form::getBreakRow()
			  		.Form::getButton('ok_filter', 'import_filter', $lang->def('_NEXT'))
			  		.Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO'))
			  		.Form::closeButtonSpace()
					.Form::closeForm()
					.'</div>');
	}
	else
	{
		$user_select->show_orgchart_simple_selector = FALSE;
		$user_select->multi_choice = TRUE;

		$user_select->addFormInfo(	$form->getHidden('step', 'step', 0)
									.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
									.$form->getHidden('idmeta', 'idmeta', $id_meta)
									.$form->getHidden('user_reload', 'user_reload', 1));

		$user_select->setPageTitle(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_ASSIGN'), 'certificate')
									.'<div class="std_block">');

		if(!isset($_POST['user_reload']))
		{
			$query =	"SELECT DISTINCT idUser"
						." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
						." WHERE idMetaCertificate = '".$id_meta."'";

			$result = sql_query($query);

			$user_reset = array();

			while(list($id_user) = sql_fetch_row($result))
				$user_reset[$id_user] = $id_user;

			$user_select->resetSelection($user_reset);
		}

		$user_select->loadSelector('index.php?modname=meta_certificate&amp;op=modassignment',
				false,
				$lang->def('_USER_FOR_META_CERTIFICATE_ASSIGN'),
				true);
	}
}

function preview()
{
	checkPerm('view');

	require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

	$id_certificate = importVar('id_certificate', true, 0);

	$cert = new Certificate();
	$cert->send_preview_certificate($id_certificate, array());
}

function create()
{
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.table.php');
    require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('certificate', 'lms');

	$id_certificate = importVar('id_certificate', true, 0);
	$id_meta = array();

	$acl_man =& Docebo::user()->getAclManager();

	$first = true;

	$tot_element = 0;

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_META_CERTIFICATE_CREATE_CAPTION'), $lang->def('_META_CERTIFICATE_CREATE_CAPTION'));
	$tb->initNavBar('ini', 'button');
	$ini = $tb->getSelectedElement();

	$query =	"SELECT idMetaCertificate"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta"
				." WHERE idCertificate = '".$id_certificate."'";

	$result = sql_query($query);

	while(list($id_meta_temp) = sql_fetch_row($result))
		$id_meta[] = $id_meta_temp;

	$query =	"SELECT idCourse, idUser"
				." FROM ".$GLOBALS['prefix_lms']."_courseuser"
				." WHERE status = '"._CUS_END."'";

	$result = sql_query($query);
	$user_course_completed = array();

	while(list($id_course_t, $id_user_t) = sql_fetch_row($result))
		$user_course_completed[$id_user_t][$id_course_t] = $id_course_t;

	$query =	"SELECT idMetaCertificate, title"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta";

	$result = sql_query($query);
	$array_title = array();

	while(list($id_meta_t, $title_t) = sql_fetch_row($result))
		$array_title[$id_meta_t] = $title_t;

	$query =	"SELECT idUser, idMetaCertificate, COUNT(*)"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
				." GROUP BY idUser, idMetaCertificate";

	$result = sql_query($query);
	$array_control = array();

	while(list($id_user_t, $id_meta_t, $control_t) = sql_fetch_row($result))
		$array_control[$id_user_t][$id_meta_t] = $control_t;

    if(isset($_POST['undo_filter_create']))
	{
		unset($_POST['filter_username']);
		unset($_POST['filter_firstname']);
		unset($_POST['filter_lastname']);
        unset($_POST['filter_release_status']);
	}

	$query =	"SELECT m.idUser, u.lastname, u.firstname, u.userid"
				." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course as m"
				." JOIN ".$GLOBALS['prefix_fw']."_user as u ON u.idst = m.idUser"
				." WHERE m.idMetaCertificate IN (".implode(',', $id_meta).")"
                .(isset($_POST['filter_username']) ? "AND u.userid LIKE '%".$_POST['filter_username']."%'" : '')
                .(isset($_POST['filter_firstname']) ? "AND u.firstname LIKE '%".$_POST['filter_firstname']."%'" : '')
                .(isset($_POST['filter_lastname']) ? "AND u.lastname LIKE '%".$_POST['filter_lastname']."%'" : '')
				." GROUP BY m.idUser, u.lastname, u.firstname, u.userid"
				." ORDER BY u.lastname, u.firstname, u.userid";

	$result = sql_query($query);

	while(list($id_user, $lastname, $firstname, $userid) = sql_fetch_row($result))
	{
		foreach ($id_meta as $idmeta)
		{
			if(isset($array_control[$id_user][$idmeta]) && $array_control[$id_user][$idmeta])
			{
				$title = strip_tags($array_title[$idmeta]);

				$query =	"SELECT idCourse"
							." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_course"
							." WHERE idUser = '".$id_user."'"
							." AND idMetaCertificate = '".$idmeta."'";

				$result_int = sql_query($query);

				$control = true;

				while(list($id_course) = sql_fetch_row($result_int))
					if(!isset($user_course_completed[$id_user][$id_course]))
						$control = false;

				if($control)
				{
					$tot_element++;

                    if($tot_element > $ini && $tot_element <= ($ini + Get::sett('visuItem')))
					{
                        list($is_released) = sql_fetch_row(sql_query(	"SELECT COUNT(*)"
																." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
																." WHERE idUser = '".$id_user."'"
																." AND idMetaCertificate = '".$idmeta."'"));

						if(!isset($_POST['filter_release_status']) || (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == 0) || (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == '1' && $is_released == 1) || (isset($_POST['filter_release_status']) && $_POST['filter_release_status'] == '2' && $is_released == 0))
						{
                            if($first)
                            {
                                $first = false;

                                $type_h = array('', '', '', 'image', 'image', 'image');
                                $cont_h = array(	$lang->def('_FULLNAME'),
                                                    $lang->def('_USERNAME'),
                                                    $lang->def('_TITLE'),
                                                    Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate')),
                                                    Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate')),
                                                    '<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_ALT_REM_META_CERT').' : '.strip_tags($title).'" />');

                                $tb->setColsStyle($type_h);
                                $tb->addHead($cont_h);
                            }

                            $cont = array();

                            $cont[] = $lastname.' '.$firstname;
                            $cont[] = $acl_man->relativeId($userid);
                            $cont[] = $title;
                            $cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=preview_cert&amp;id_certificate='.$id_certificate.'&amp;idmeta='.$idmeta.'&amp;iduser='.$id_user.'">'
                                        .Get::img('standard/view.png', Lang::t('_PREVIEW', 'certificate').' : '.strip_tags($title)).'</a>';
                            $cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=release_cert&amp;id_certificate='.$id_certificate.'&amp;idmeta='.$idmeta.'&amp;iduser='.$id_user.'">'
                                        .Get::img('course/certificate.png', Lang::t('_TAKE_A_COPY', 'certificate').' : '.strip_tags($title)).'</a>';
                            if($is_released)
                                $cont[] =	'<a href="index.php?modname=meta_certificate&amp;op=del_released&amp;id_certificate='.$id_certificate.'&amp;idmeta='.$idmeta.'&amp;iduser='.$id_user.'">'
                                            .'<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_ALT_REM_META_CERT').' : '.strip_tags($title).'" /></a>';
                            else
                                $cont[] = '';

                            $tb->addBody($cont);
                        }
                    }
				}
			}
		}
	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=del_released]');

    $array_release_status = array(	$lang->def('_ALL') => '0',
								$lang->def('_ONLY_RELEASED') => '1',
								$lang->def('_ONLY_NOT_RELEASED') => '2');

	if($first)
    {
		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_CREATE'), 'certificate')
					.'<div class="std_block">');

		if(isset($_POST['filter']))
			$out->add(	Form::openForm('meta_certificate_filter', 'index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate)
						.Form::openElementSpace()
						.Form::getTextfield($lang->def('_USERNAME'), 'filter_username', 'filter_username', '255', isset($_POST['filter_username']) ? $_POST['filter_username'] : '')
						.Form::getTextfield($lang->def('_FIRSTNAME'), 'filter_firstname', 'filter_firstname', '255', isset($_POST['filter_firstname']) ? $_POST['filter_firstname'] : '')
						.Form::getTextfield($lang->def('_LASTNAME'), 'filter_lastname', 'filter_lastname', '255', isset($_POST['filter_lastname']) ? $_POST['filter_lastname'] : '')
						.Form::getRadioSet($lang->def('_RELEASE_STATUS_FILTER'), 'filter_release_status', 'filter_release_status', $array_release_status, isset($_POST['filter_release_status']) ? $_POST['filter_release_status'] : '0')
                        .Form::closeElementSpace()
						.Form::openButtonSpace()
						.Form::getButton('filter', 'filter', $lang->def('_FILTER'))
						.Form::getButton('undo_filter_create', 'undo_filter_create', $lang->def('_UNDO_FILTER'))
						.Form::closeButtonSpace()
						.Form::closeForm());

		$out->add(	$lang->def('_NO_USER_FOUND')
					.getBackUi('index.php?modname=meta_certificate&amp;op=meta_certificate', $lang->def('_BACK'))
					.'</div>');
	}
	else
	{
		$out->add(	getTitleArea($lang->def('_TITLE_META_CERTIFICATE_CREATE'), 'certificate')
					.'<div class="std_block">');

		if(isset($_GET['result']))
	{
		switch($_GET['result'])
		{
			case "ok":
				$out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
			break;
			case "err_del_cert":
				$out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			break;
		}
	}

		$out->add(	Form::openForm('meta_certificate_filter', 'index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate)
					.Form::openElementSpace()
					.Form::getTextfield($lang->def('_USERNAME'), 'filter_username', 'filter_username', '255', isset($_POST['filter_username']) ? $_POST['filter_username'] : '')
					.Form::getTextfield($lang->def('_FIRSTNAME'), 'filter_firstname', 'filter_firstname', '255', isset($_POST['filter_firstname']) ? $_POST['filter_firstname'] : '')
					.Form::getTextfield($lang->def('_LASTNAME'), 'filter_lastname', 'filter_lastname', '255', isset($_POST['filter_lastname']) ? $_POST['filter_lastname'] : '')
					.Form::getRadioSet($lang->def('_RELEASE_STATUS_FILTER'), 'filter_release_status', 'filter_release_status', $array_release_status, isset($_POST['filter_release_status']) ? $_POST['filter_release_status'] : '0')
                    .Form::closeElementSpace()
					.Form::openButtonSpace()
					.Form::getButton('filter', 'filter', $lang->def('_FILTER'))
					.Form::getButton('undo_filter_create', 'undo_filter_create', $lang->def('_UNDO_FILTER'))
					.Form::closeButtonSpace()
					.$tb->getTable()
					.$tb->getNavBar($ini, $tot_element)
					.Form::closeForm()
					.getBackUi('index.php?modname=meta_certificate&amp;op=meta_certificate', $lang->def('_BACK'))
					.'</div>');
	}
}

function delReleased()
{
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.upload.php');


	$id_certificate = importVar('id_certificate', true, 0);
	$id_meta = importVar('idmeta', true, 0);
	$id_user = importVar('iduser', true, 0);

	$lang 		=& DoceboLanguage::createInstance('certificate', 'lms');

	$acl_man =& Docebo::user()->getAclManager();

	if(Get::req('confirm', DOTY_INT, 0) == 1) {

		$query =	"SELECT cert_file"
					." FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
					." WHERE idUser = '".$id_user."'"
					." AND idMetaCertificate = '".$id_meta."'";

		list($cert_file) = sql_fetch_row(sql_query($query));

		$path = '/appLms/certificate/';

		sl_open_fileoperations();
		$res = sl_unlink($path.$cert_file);
		sl_close_fileoperations();

		if(!$res)
			Util::jump_to('index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate.'&result=err_del_cert');

		$query =	"DELETE FROM ".$GLOBALS['prefix_lms']."_certificate_meta_assign"
					." WHERE idUser = '".$id_user."'"
					." AND idMetaCertificate = '".$id_meta."'";

		if(!sql_query($query))
			Util::jump_to('index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate.'&result=err_del_cert');
		else
			Util::jump_to('index.php?modname=meta_certificate&op=create&id_certificate='.$id_certificate.'&result=ok');
	}
	else
	{
		list($name, $descr) = sql_fetch_row(sql_query("
		SELECT name, description
		FROM ".$GLOBALS['prefix_lms']."_certificate
		WHERE id_certificate = '".$id_certificate."'"));

		$user_info = $acl_man->getUser($id_user, false);

		$user = $user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME].' ('.$acl_man->relativeId($user_info[ACL_INFO_USERID]).')';

		$form = new Form();
		$page_title = array(
			'index.php?modname=meta_certificate&amp;op=meta_certificate' => $lang->def('_TITLE_CERTIFICATE'),
			$lang->def('_DEL_RELEASED')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'certificate')
			.'<div class="std_block">'
			.$form->openForm('del_certificate', 'index.php?modname=meta_certificate&amp;op=del_released')
			.$form->getHidden('id_certificate', 'id_certificate', $id_certificate)
			.$form->getHidden('idmeta', 'idmeta', $id_meta)
			.$form->getHidden('iduser', 'iduser', $id_user)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_NAME').' : </span>'.$name.'<br />'
							.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$descr.'<br />'
							.'<span>'.$lang->def('_USER').' : </span>'.$user,
							false,
							'confirm',
							'undo'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function preview_cert()
{
	checkPerm('view');

	require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

	$id_certificate = importVar('id_certificate', true, 0);
	$id_course = importVar('id_course', true, 0);
	$id_user = Get::req('iduser', DOTY_INT, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$cert = new Certificate();
	$subs = $cert->getSubstitutionArray($id_user, $id_course, $id_meta);
	$cert->send_facsimile_certificate($id_certificate, $id_user, $id_course, $subs);
}

function release_cert()
{
	checkPerm('view');

	require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));

	$id_certificate = importVar('id_certificate', true, 0);
	$id_course = importVar('id_course', true, 0);
	$id_user = Get::req('iduser', DOTY_INT, 0);
	$id_meta = Get::req('idmeta', DOTY_INT, 0);

	$cert = new Certificate();
	$subs = $cert->getSubstitutionArray($id_user, $id_course, $id_meta);
	$cert->send_certificate($id_certificate, $id_user, $id_course, $subs);
}

function metaCertificateDispatch($op)
{
	if(isset($_POST['undo']))
		$op = 'meta_certificate';
	if(isset($_POST['undo_assign']) || isset($_POST['cancelselector']) || isset($_POST['undo_filter']))
		$op = 'assign';

	switch($op)
	{
		case 'meta_certificate':
			metaCertificate();
		break;

		case 'addmetacertificate' :
			editMetaCertificate();
		break;

		case 'modmetacertificate' :
			editMetaCertificate(true);
		break;

		case 'savemetacertificate':
			saveMetaCertificate();
		break;

		case 'elemmetacertificate' :
			list_element_meta_certificate();
		break;

		case 'delmetacertificate':
			delMetaCertificate();
		break;

		case 'assign':
			assignMetaCertificate();
		break;

		case 'viewdetails':
			viewDetails();
		break;

		case 'new_assign':
			newAssignMetaCertificate();
		break;

		case 'delassignmetacertificate':
			delAssignMetaCertificate();
		break;

		case 'modassignmetacertificate':
			modAssignMetaCertificate();
		break;

		case 'modassignment':
			modAssignmentAssignMetaCertificate();
		break;

		case 'preview' :
			preview();
		break;

		case 'create':
			create();
		break;

		case 'preview_cert':
			preview_cert();
		break;

		case 'release_cert':
			release_cert();
		break;

		case 'del_released':
			delReleased();
		break;

		default:
			metaCertificate();
		break;
	}
}
?>
