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

require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');

function fileList(&$url) {
	checkPerm('view');

	require_once(_base_.'/lib/lib.tab.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');

	$file_man 	= new MyFile( getLogUserId() );
	$tab_man 	= new TabView('myfiles', '');

	$lang 		=& DoceboLanguage::createInstance('myfiles');

	$areas = $file_man->getFilesAreas();
	while(list($id_page, $area_name) = each($areas)) {

		$new_tab = new TabElemDefault(	$id_page,
										$lang->def($area_name),
										getPathImage('fw').'myfiles/'.$id_page.'.gif');
		$tab_man->addTab($new_tab);
	}
	$tab_man->parseInput($_POST, $_SESSION);

	$active_tab = $tab_man->getActiveTab();
	if(!$active_tab) {
		$active_tab = importVar('working_area', false, $file_man->getDefaultArea());
		$tab_man->setActiveTab($active_tab);
	}

	$GLOBALS['page']->addStart(
		Form::openForm('tab_myfiles', $url->getUrl('op=myfiles'))
		.Form::getHidden('working_area', 'working_area', $active_tab)

		.$tab_man->printTabView_Begin('', false), 'content');

	$GLOBALS['page']->addEnd(
		$tab_man->printTabView_End()
		.Form::closeForm(), 'content');

	$tb = new Table(	0,
						$lang->def('_MYFILES_CAPTION'),
						$lang->def('_MYFILES_SUMMARY') );

	$cont_h = array(
		$lang->def('_TITLE'),
		$lang->def('_FILE_POLICY'),
		'<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />',
		'<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_REM_FILE').'" alt="'.$lang->def('_DEL').'" />'
	);
	$type_h = array('', 'image nowrap', 'image', 'image');
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	$re_files = $file_man->getFileList($active_tab, false, MYFILE_TITLE);
	while($file_info = $file_man->fetch_row($re_files)) {

		$cont = array($file_info[MYFILE_TITLE] );
		switch($file_info[MYFILE_POLICY]) {
			case MF_POLICY_FREE : 		$cont[] = $lang->def('_MF_POLICY_FREE');break;
			case MF_POLICY_TEACHER : 	$cont[] = $lang->def('_MF_POLICY_TEACHER');break;
			case MF_POLICY_FRIENDS : 	$cont[] = $lang->def('_MF_POLICY_FRIENDS');break;
			case MF_POLICY_TEACHER_AND_FRIENDS : 	$cont[] = $lang->def('_MF_POLICY_TEACHER_AND_FRIENDS');break;
			case MF_POLICY_NOONE : 		$cont[] = $lang->def('_MF_POLICY_NOONE');break;
		}
		$cont[] = '<a href="'.$url->getUrl('op=modfiles&working_area='.$active_tab.'&id_file='.$file_info[MYFILE_ID_FILE]).'">'
				.'<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').' : '.$file_info[MYFILE_TITLE].'" alt="'.$lang->def('_MOD').'" />'
				.'</a>';
		$cont[] = '<a href="'.$url->getUrl('op=delfiles&id_file='.$file_info[MYFILE_ID_FILE]).'">'
				.'<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_REM_FILE').' : '.$file_info[MYFILE_TITLE].'" alt="'.$lang->def('_DEL').'" />'
				.'</a>';
		$tb->addBody($cont);
	}
	$tb->addActionAdd('<p class="new_elem_link"><a href="'.$url->getUrl('op=modfiles&working_area='.$active_tab).'">'
		.$lang->def('_ADD_'.$active_tab).'</a></p>');
	$tb->addActionAdd('<p>'
		.'<a href="'
		.$url->getUrl('modname=profile&op=profile&id_user='.getLogUserId().'&ap=view_files&type='.$active_tab.'&from=1').'">'
		.'<img src="'.getPathImage('fw').'myfiles/'.$active_tab.'.gif" title="'.$lang->def($active_tab).'" alt="'.$lang->def('_VIEW_'.$active_tab).'" />'
		.'</a>'
		.'<a href="'
		.$url->getUrl('modname=profile&op=profile&id_user='.getLogUserId().'&ap=view_files&type='.$active_tab.'&from=1').'">'
		.$lang->def('_VIEW_'.$active_tab).'</a></p>');

	$GLOBALS['page']->add($tb->getTable(), 'content');
}

function modfiles(&$url) {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	$file_man 	= new MyFile( getLogUserId() );
	$lang 		=& DoceboLanguage::createInstance('myfiles');

	$area 		= importVar('working_area', false, $file_man->getDefaultArea());
	$id_file 	= importVar('id_file', true, 0);
	$title 			= '';
	$description 	= '';
	$file_policy 	= MF_POLICY_FREE;

	if($id_file != 0) {

		$f_info = $file_man->getFileInfo($id_file);
		$title 			= $f_info[MYFILE_TITLE];
		$description 	= $f_info[MYFILE_DESCRIPTION];
		$file_policy 	= $f_info[MYFILE_POLICY];

	}
	$arr_policy = array(
		MF_POLICY_FREE 		=> $lang->def('_MF_POLICY_FREE'),
		MF_POLICY_TEACHER 	=> $lang->def('_MF_POLICY_TEACHER'),
		MF_POLICY_FRIENDS 	=> $lang->def('_MF_POLICY_FRIENDS'),
		MF_POLICY_TEACHER_AND_FRIENDS => $lang->def('_MF_POLICY_TEACHER_AND_FRIENDS'),
		MF_POLICY_NOONE 	=> $lang->def('_MF_POLICY_NOONE')
	);

	$title_page = array(
		$url->getUrl('op=myfiles') => $lang->def('_MYFILE'),
		$lang->def('_ADD_'.$area)
	);
	$GLOBALS['page']->add(
		getTitleArea($title_page, 'myfile')
		.'<div class="std_block">'
		.getBackUi($url->getUrl('op=myfiles'), $lang->def('_BACK') )

		.Form::openForm('add_file', $url->getUrl('op=savefiles'), false, false, 'multipart/form-data')
		.Form::openElementSpace()

		.Form::getHidden('id_file', 'id_file', $id_file)
		.Form::getHidden('working_area', 'working_area', $area)
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		.Form::getFilefield($lang->def('_UPLOAD'), 'uploaded_file', 'uploaded_file')
		.Form::getDropdown(	$lang->def('_FILE_POLICY'),
							'file_policy',
							'file_policy',
							$arr_policy,
							$file_policy )
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
		.Form::closeElementSpace()

		.Form::openButtonSpace()
		.Form::getButton('save', 'save', ( $id_file == 0  ? $lang->def('_INSERT') : $lang->def('_SAVE') ) )
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}


function savefiles(&$url) {
	checkPerm('view');

	$file_man 	= new MyFile(getLogUserId());

	$area 		= importVar('working_area', false, $file_man->getDefaultArea());
	$id_file 	= importVar('id_file', true, 0);

	$result = $file_man->insertFile(	$_POST['id_file'],
										$_POST['working_area'],
										$_POST['title'],
										$_POST['description'],
										( isset($_FILES['uploaded_file']) ? $_FILES['uploaded_file'] : '' ),
										$_POST['file_policy'] );

	Util::jump_to($url->getUrl('op=myfiles&working_area='.$_POST['working_area'].'&result='.( $result ? 'insert_ok' : 'insert_fail' ) ));
}

function delfiles(&$url) {
	checkPerm('view');

	$file_man 	= new MyFile( getLogUserId() );
	$lang 		=& DoceboLanguage::createInstance('myfiles');

	$area 		= importVar('working_area', false, $file_man->getDefaultArea());
	$id_file 	= importVar('id_file', true, 0);

	if( isset($_GET['confirm']) ) {

		$result = $file_man->deleteFile($id_file);
		Util::jump_to($url->getUrl('op=myfiles&working_area='.$area.'&result='.( $result ? 'delete_ok' : 'delete_fail' ) ));
	} else {

		$f_info = $file_man->getFileInfo($id_file);

		$title_page = array(
			$url->getUrl('op=myfiles') => $lang->def('_MYFILE'),
			$lang->def('_DEL').' : '.$f_info[MYFILE_TITLE]
		);
		$GLOBALS['page']->add(
			getTitleArea($title_page, 'myfile')
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_TITLE').' : </span>'.$f_info[MYFILE_TITLE].'<br />'
							.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$f_info[MYFILE_DESCRIPTION],
							true,
							$url->getUrl('op=delfiles&id_file='.$id_file.'&confirm=1&working_area='.$area ),
							$url->getUrl('op=myfiles&working_area='.$area ) )
			.'</div>', 'content');
	}
}

function myfilesDispatch($op) {

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('myfiles');
	$url->setStdQuery('modname=myfiles&op=myfiles');

	if(isset($_POST['undo'])) $op = 'myfiles';
	switch($op) {
		case "myfiles" : {
			fileList($url);
		};break;

		case "modfiles" : {
			modfiles($url);
		};break;
		case "savefiles" : {
			savefiles($url);
		};break;

		case "delfiles" : {
			delfiles($url);
		};break;
	}
}