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

require_once($GLOBALS['where_lms'].'/lib/lib.light_repo.php');

function repoList(&$url) {
	checkPerm('view');
	
	$lang =& DoceboLanguage::createInstance('light_repo');

	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
		
	$mod_perm = checkPerm('mod', true);
	
	$repositories 	= $file_man->getRepoList(!$mod_perm);
	
	if($repositories !== false && sql_num_rows($repositories) == 1 && !$mod_perm) {
		$repo = sql_fetch_row($repositories);
		return repoMyDetails($url, $repo[LR_ID]); 
	}
	cout(
		getTitleArea($lang->def('_TITLE_LIGHT_REPO'), 'light_repo')
		.'<div id="light_repo_block" class="std_block">'
	, 'content');
	
	if($repositories !== false && sql_num_rows($repositories) > 0) {
		
		if(isset($_GET['result'])) {
			
			switch($_GET['result']) {
				case "ok_mod" : { UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'light_repo')); };break;
				case "ok_del" : { UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'light_repo')); };break;
			}
		}
	} else { 
		
		cout($lang->def('_NO_REPOSITORY_FOUND'), 'content');
	}
	
	while($repo = sql_fetch_row($repositories)) {
		$last_enter = $file_man->getUserLastEnterInRepo($repo[LR_ID]);
		$new_file = $file_man->getNumberOfFileInReport($repo[LR_ID], $last_enter);
		cout(
			'<div class="list_block" id="repo_container_'.$repo[LR_ID].'">'
			.'<h2 class=heading"><a href="'.$url->getUrl('op='.( $mod_perm ? 'repo_manager_details' : 'repo_my_details' ).'&id_repo='.$repo[LR_ID]).'">'
				.$repo[LR_TITLE]
			.'</a></h2>'
			.'<div class="content">'.$repo[LR_DESCR]
			.'<br />'
			.''.$lang->def('_FILE_COUNT').': '.$repo[LR_FILECOUNT].'</div>'
		, 'content');
		if($mod_perm) {
			
			if(isset($new_file) && $new_file !== '0')
					cout('<b>('.$new_file.' '.$lang->def('_REPO_NEW_FILE').')</b>', 'content');
			
			cout('<div class="actions">'
				.'<ul class="link_list_inline">'
				.'<li>'
				.'<a class="ico-wt-sprite subs_mod" href="'.$url->getUrl('op=mod_repo&id_repo='.$repo[LR_ID]).'" title="'.$lang->def('_MOD').'">'
				.'<span>'.$lang->def('_MOD').'</span></a>'
				.'</li>'
				.'<li>'
				.'<a class="ico-wt-sprite subs_del" href="'.$url->getUrl('op=del_repo&id_repo='.$repo[LR_ID]).'" title="'.$lang->def('_DEL').' : '.$repo[LR_TITLE].'">'
				.'<span>'.$lang->def('_DEL').'</span></a>'
				.'</li>'
				.'</ul></div>', 'content');
		}
		
		cout('</div>', 'content');
	}
	if($mod_perm) {
		
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=del_repo]');
		
		cout('<br/><div class="table-container-below">'
				.'<a class="ico-wt-sprite subs_add" href="'.$url->getUrl('op=mod_repo').'"><span>'.$lang->def('_NEW_REPOSITORY').'</span></a>'
				.'</div>', 'content');
	}
	cout('</div>', 'content');
}

function modRepo(&$url) {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang 		=& DoceboLanguage::createInstance('light_repo');
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	
	$id_repo = importVar('id_repo', true, 0);
	
	// recovering file repository information
	$repo = false;
	if($id_repo != 0) { $repo = $file_man->getRepoDetails($id_repo); } 
	if($repo == false) {
		$repo[LR_TITLE] = '';
		$repo[LR_DESCR] = '';
	}  
	
	cout(
		getTitleArea(array(	$url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), 
			( $id_repo == 0 ? $lang->def('_NEW_REPOSITORY') : $lang->def('_MOD').' '.$repo[LR_TITLE] )
		), 'light_repo')
		.'<div class="std_block">', 'content');
	
	// save modification if needed
	if(isset($_POST['save'])) {
		
		$data[LR_IDCOURSE] = $_SESSION['idCourse'];
		$data[LR_TITLE] = importVar('repo_title', false, '');
		$data[LR_DESCR] = importVar('repo_descr', false, '');
		
		if(trim($data[LR_TITLE]) == '') $data[LR_TITLE] = $lang->def('_NOTITLE');
		
		if(!$file_man->saveRepo($id_repo, $data)) { 
			cout(Get::append_alert($lang->def('_ERR_MOD_REPO')), 'content');
		} else { Util::jump_to( $url->getUrl('result=ok_mod') ); }
	}
	
	// form for input 
	cout(''
		.Form::openForm('mod_repo_form', $url->getUrl('op=mod_repo'))
		
		.Form::openElementSpace()
		.Form::getHidden('id_repo', 'id_repo', $id_repo)
		.Form::getTextfield(	$lang->def('_TITLE'),
								'repo_title',
								'repo_title',
								255, 
								importVar('repo_title', false, $repo[LR_TITLE]) )
		.Form::getTextarea(		$lang->def('_DESCRIPTION'), 
								'repo_descr', 
								'repo_descr', 
								importVar('repo_descr', false, $repo[LR_DESCR]) )
		
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		
		.Form::closeButtonSpace()
		
		.Form::closeForm()
	, 'content');
	
	cout('</div>', 'content');
}

function delRepo(&$url) {
	checkPerm('mod');
	
	require_once($GLOBALS["where_lms"]."/lib/lib.light_repo.php");
	
	$re = false;
	if(isset($_GET['confirm'])) {
		$id_repo = Get::req('id_repo', DOTY_INT, 0);
		$file_man = new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
		$re = $file_man->deleteRepo($id_repo);
	}
	Util::jump_to($url->getUrl('op=repolist&result='.($re?'ok_del':'err')));	
}

function repoMyDetails(&$url, $passed_repo = 0) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('light_repo');
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	$acl_man	=& Docebo::user()->getAclManager();
	
	$id_repo = importVar('id_repo', true, $passed_repo);
	// recovering file repository information
	$repo = $file_man->getRepoDetails($id_repo);
	
	$file_man->setUserLastEnterInRepo($id_repo);
	
	$of_user = getLogUserId();
	$page_title = array($url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), $repo[LR_TITLE]);
	
	$file_list = $file_man->getRepoFileListOfAuthor($id_repo, $of_user);
	
	cout(
		getTitleArea($page_title , 'light_repo')
		.'<div class="std_block" id="light_repo_block">', 'content');
	
	if(isset($_GET['result'])) {
			
			switch($_GET['result']) {
				case "file_ok" : { UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'light_repo')); };break;
				case "file_err" : { UIFeedback::error(Lang::t('_FILE_ERR', 'light_repo')); };break;
			}
		}
	
	$table = new Table(0, $lang->def('_CAPTION_USER_FILE_LIST'), $lang->def('_SUMMARY_USER_FILE_LIST'));
	
	$content_h = array(
		$lang->def('_FILENAME'),
		$lang->def('_DESCRIPTION'),
		$lang->def('_DATE'),
		'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />',
		'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />'
	);
	$type_h = array('', '', '', 'image', 'image');
	$table->addHead($content_h, $type_h);
	
	$url->addToStdQuery('id_repo='.$id_repo);
	
	while($file = sql_fetch_row($file_list)) {
		
		// convert filename
		$file[LR_FILE_NAME] = implode( '_', array_slice(explode('_', $file[LR_FILE_NAME]), 3) );
		
		$content = array();
		
		$content[] = ''
			.'<a href="'.$url->getUrl('op=download_file&id_repo='.$id_repo.'&id_file='.$file[LR_FILE_ID]).'" title="'.$lang->def('_DOWNLOAD').''.strip_tags($file[LR_FILE_NAME]).'">'
				.'<img src="'.getPathImage().'standard/download.png" alt="'.$lang->def('_DOWNLOAD').'" /> '.$file[LR_FILE_NAME]
			.'</a>';
		
		$content[] = $file[LR_FILE_DESCR];
		
		$content[] = Format::date($file[LR_FILE_POSTDATE], 'datetime');
		//$content[] = $file[LR_FILE_DESCR];
		
		$content[] = ''
			.'<a href="'.$url->getUrl('op=mod_file&id_repo='.$id_repo.'&id_file='.$file[LR_FILE_ID]).'"' .
					' title="'.$lang->def('_MOD').''.strip_tags($file[LR_FILE_NAME]).'">'
				.'<img src="'.getPathImage('fw').'standard/edit.png" alt="'.$lang->def('_MOD').' : '.strip_tags($file[LR_FILE_NAME]).'" />'
			.'</a>';
		
		$content[] = ''
			.'<a href="'.$url->getUrl('op=del_file&id_repo='.$id_repo.'&id_file='.$file[LR_FILE_ID]).'"' .
					' title="'.$lang->def('_DEL').''.strip_tags($file[LR_FILE_NAME]).'">'
				.'<img src="'.getPathImage('fw').'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($file[LR_FILE_NAME]).'" />'
			.'</a>';
		
		$table->addBody($content, false, false, 'file_container_'.$file[LR_FILE_ID]);
	}
	$table->addActionAdd('<a class="dd_link" href="'.$url->getUrl('op=mod_file&id_repo='.$id_repo).'" title="'.$lang->def('_ADD_FILE').'">'
			.$lang->def('_UPLOAD')
		.'</a>');
	
	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=del_file]');
	
	cout($table->getTable(), 'content');
	
	cout('</div>', 'content');
}

function modFile(&$url) {
	checkPerm('view');
	$mod_perm = checkPerm('mod', true);
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang 		=& DoceboLanguage::createInstance('light_repo');
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	
	$id_repo = importVar('id_repo', true, 0);
	$id_file = importVar('id_file', true, 0);
	
	if(isset($_POST['undo'])) Util::jump_to($url->getUrl('op='.( $mod_perm ? 'repo_manager_details' : 'repo_my_details' ).'&id_repo='.$id_repo));
	if(isset($_POST['save'])) {
		
		// save changes 
		$file_info[LR_FILE_ID_REPO] = $id_repo;
		$file_info[LR_FILE_NAME] 	= ( isset($_FILES['file_name']) ? $_FILES['file_name'] : false );
		$file_info[LR_FILE_DESCR] 	= $_POST['file_descr'];
		$file_info[LR_FILE_AUTHOR] 	= getLogUserId();
		$file_info[LR_FILE_POSTDATE] = date("Y-m-d H:i:s");
		
		$re = $file_man->saveFile($id_file, $file_info);
		
		Util::jump_to($url->getUrl('op='.( $mod_perm ? 'repo_manager_details' : 'repo_my_details' ).'&id_repo='.$id_repo.'&result='.($re?'file_ok':'file_err')));
	}
	
	$repo = $file_man->getRepoDetails($id_repo);
	$page_title = array($url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), $url->getUrl('op='.( $mod_perm ? 'repo_manager_details' : 'repo_my_details' ).'&id_repo='.$id_repo) => $repo[LR_TITLE]);
	
	if($id_file == 0) {
		$page_title[] = $lang->def('_UPLOAD');
		$file = array();
		$file[LR_FILE_NAME] = false;
		$file[LR_FILE_DESCR] = '';
	} else {
		$file = sql_fetch_row($file_man->getFileInfo($id_file));
		$page_title[] = implode( '_', array_slice(explode('_', $file[LR_FILE_NAME]), 3) );
	}
	
	cout(
		getTitleArea($page_title , 'light_repo')
		.'<div class="std_block">', 'content');
	
	cout(
		Form::openForm('mod_file', $url->getUrl('op=mod_file&id_repo='.$id_repo), false, false, 'multipart/form-data')
		.Form::openElementSpace()
		
		.Form::getHidden('id_file', 'id_file', $id_file)
		.Form::getHidden('id_repo', 'id_repo', $id_repo)
		.Form::getExtendedFileField(	$lang->def('_UPLOAD'), 
										'file_name', 
										'file_name', 
										$file[LR_FILE_NAME], 
										implode( '_', array_slice(explode('_', $file[LR_FILE_NAME]), 3) ),
										true,
										false
									)
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'file_descr', 'file_descr', importVar('file_descr', false, $file[LR_FILE_DESCR], true) )
		
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	, 'content');
	
	cout('</div>', 'content');
}

function delFile(&$url) {
	checkPerm('view');
	$mod_perm = checkPerm('mod', true);
	
	require_once($GLOBALS["where_lms"]."/lib/lib.light_repo.php");
	
	$re = false;
	if(isset($_GET['confirm'])) {
		$id_file = Get::req('id_file', DOTY_INT, 0);
		$id_repo = Get::req('id_repo', DOTY_INT, 0);
		
		$file_man = new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
		$re = $file_man->deleteFile($id_file);
	}
	Util::jump_to($url->getUrl('op='.( $mod_perm ? 'repo_manager_details' : 'repo_my_details' ).'&id_repo='.$id_repo.'&result='.($re?'file_ok':'file_err')));	
}

function repoManagerDetails(&$url) {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('light_repo');
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	
	$id_repo = importVar('id_repo', true, 0);
	
	// recovering file repository information
	$repo = $file_man->getRepoDetails($id_repo);
	
	$file_man->setUserLastEnterInRepo($id_repo);	
	
	cout(
		getTitleArea( array($url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), $repo[LR_TITLE]), 'light_repo')
		.'<div class="std_block">', 'content');
	
	$last_enter = $file_man->getUserLastEnterInRepo($id_repo);
	$file_list = $file_man->getRepoUserListWithFileCount($id_repo, $last_enter);
	
	$table = new Table(0, $lang->def('_CAPTION_USER_FILE_LIST'), $lang->def('_SUMMARY_USER_FILE_LIST'));
	
	$content_h = array(
		$lang->def('_USERNAME'),
		$lang->def('_LOADED_FILE'),
		$lang->def('_VIEW')
	);
	$type_h = array('', '', 'image');
	$table->addHead($content_h, $type_h);
	
	$url->addToStdQuery('id_repo='.$id_repo);
	
	while(list(,$file) = each($file_list)) {
		
		$content = array();
		$content[] = $file['username'];
		$content[] = ( isset($file['file_count']) ? $file['file_count'] : '0' )
			.( isset($file['file_new']) ? '<b>('.$file['file_new'].$lang->def('_REPO_NEW_FILE').' )</b> ' : '' );
		
		if(isset($file['file_count'])) {
			$content[] = ''
			.'<a href="'.$url->getUrl('op=repo_user_details&id_user='.$file['id_user']).'" title="'.$lang->def('_VIEW_USER_FILE_LIST').''.strip_tags($file['username']).'">'
				.'<img src="'.getPathImage().'standard/view.png" alt="'.$lang->def('_VIEW_USER_FILE_LIST').''.strip_tags($file['username']).'" />'
			.'</a>';
		} else {
			$content[] = '';
		}
		
		$table->addBody($content);
	}
	cout($table->getTable(), 'content');
	
	cout('</div>', 'content');
}

function repoUserDetails(&$url, $passed_repo = 0) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('light_repo');
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	$acl_man	=& Docebo::user()->getAclManager();
	
	$id_repo = importVar('id_repo', true, $passed_repo);
	$of_user = importVar('id_user', true, 0);
	// recovering file repository information
	$repo = $file_man->getRepoDetails($id_repo);
	
	if(checkPerm('mod', true)) { 
		$page_title = array(
			$url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), 
			$url->getUrl('op=repo_manager_details&id_repo='.$id_repo) => $repo[LR_TITLE],
			$acl_man->getUserName($of_user)
		);
	} else {
		$of_user = getLogUserId();
		$page_title = array($url->getUrl() => $lang->def('_TITLE_LIGHT_REPO'), $repo[LR_TITLE]);
	}
	$file_list = $file_man->getRepoFileListOfAuthor($id_repo, $of_user);
	
	cout(
		getTitleArea($page_title , 'light_repo')
		.'<div class="std_block">', 'content');
	
	$table = new Table(0, $lang->def('_CAPTION_USER_FILE_LIST'), $lang->def('_SUMMARY_USER_FILE_LIST'));
	
	$content_h = array(
		$lang->def('_FILENAME'),
		$lang->def('_DESCRIPTION'),
		$lang->def('_DATE'),
		'<img src="'.getPathImage().'standard/download.png" alt="'.$lang->def('_DOWNLOAD').'" />',
		'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />'
	);
	$type_h = array('', '', '', 'image', 'image');
	$table->addHead($content_h, $type_h);
	
	$url->addToStdQuery('id_repo='.$id_repo);
	
	while($file = sql_fetch_row($file_list)) {
		
		$content = array();
		$content[] = implode( '_', array_slice(explode('_', $file[LR_FILE_NAME]), 3) );
		
		$content[] = $file[LR_FILE_DESCR];
		
		$content[] = Format::date($file[LR_FILE_POSTDATE], 'datetime');
		//$content[] = $file[LR_FILE_DESCR];
		
		$content[] = ''
			.'<a href="'.$url->getUrl('op=download_file&id_file='.$file[LR_FILE_ID]).'" title="'.$lang->def('_DOWNLOAD').''.strip_tags($file[LR_FILE_NAME]).'">'
				.'<img src="'.getPathImage().'standard/download.png" alt="'.$lang->def('_DOWNLOAD').''.strip_tags($file[LR_FILE_NAME]).'" />'
			.'</a>';
		
		$content[] = ''
			.'<a href="'.$url->getUrl('op=del_file&id_repo='.$id_repo.'&id_file='.$file[LR_FILE_ID]).'"' .
					' title="'.$lang->def('_DEL').''.strip_tags($file[LR_FILE_NAME]).'">'
				.'<img src="'.getPathImage('fw').'standard/delete.png" alt="'.$lang->def('_DEL').' : '.strip_tags($file[LR_FILE_NAME]).'" />'
			.'</a>';
		
		$table->addBody($content);
	}
	cout($table->getTable(), 'content');

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=del_file]');

	cout('</div>', 'content');
}

function downloadFile(&$url) {
	checkPerm('view');
	
	// retrive file info
	$id_file = importVar('id_file', true, 0);
	$file_man 	= new LightRepoManager( getLogUserId(), $_SESSION['idCourse'] );
	
	$file = $file_man->getFileInfo($id_file);
	if($file !== false) $file = sql_fetch_row($file);
		
	if(!checkPerm('mod', true) && ($file[LR_FILE_AUTHOR] != getLogUserId())) { 
		Util::jump_to($url->getUrl());
	}
	require_once(_base_.'/lib/lib.download.php' );
	sendFile($file_man->getFilePath(), $file[LR_FILE_NAME]);
}

function lightrepoDispatch($op) {
	
	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('light_repo');
	$url->setStdQuery('modname=light_repo&op=repolist');
	
	if(isset($_POST['undo'])) $op = 'repolist';
	switch($op) {
		case "repolist" : {
			repoList($url);
		};break;
		case "mod_repo" : {
			modRepo($url);
		};break;
		case "del_repo" : {
			delRepo($url);
		};break;
		
		case "repo_my_details" : {
			repoMyDetails($url);
		};break;
		case "mod_file" : {
			modFile($url);
		};break;
		case "del_file" : {
			delFile($url);
		};break;
		
		case "repo_manager_details" : {
			repoManagerDetails($url);
		};break;
		case "repo_user_details" : {
			repoUserDetails($url);
		};break;
		case "download_file" : {
			downloadFile($url);
		};break;
	}
}