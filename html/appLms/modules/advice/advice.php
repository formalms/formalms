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

function adviceList() {

	require_once(_base_.'/lib/lib.navbar.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang 		=& DoceboLanguage::createInstance('advice');
	$mod_perm 	= checkPerm('mod', true);
	$out		= $GLOBALS['page'];
	$out->setWorkingZone('content');

	$nav_bar 	=	new NavBar('ini', Get::sett('visuItem'), 0);
	$nav_bar->setLink('index.php?modname=advice&amp;op=advice&amp;tab=advice');
	$ini 		= $nav_bar->getSelectedElement();

	$user_idst 		= Docebo::user()->getArrSt();// $acl->getUserGroupsST(getLogUserId());

	$query_my_advice = "
		SELECT DISTINCT idAdvice
		FROM ".$GLOBALS['prefix_lms']."_adviceuser
		WHERE ( idUser IN ( ".implode($user_idst, ',')." ) AND archivied = '0' )";
	$re_my_advice = sql_query($query_my_advice);

	$advice_all = array();
	while(list($id) = sql_fetch_row($re_my_advice)) {

		$advice_all[$id] = $id;
	}
	$query_my_arch_advice = "
		SELECT DISTINCT idAdvice
		FROM ".$GLOBALS['prefix_lms']."_adviceuser
		WHERE idUser = '".getLogUserId()."' AND archivied = '1'";
	$re_my_arch_advice = sql_query($query_my_arch_advice);
	while(list($id) = sql_fetch_row($re_my_arch_advice)) {

		$advice_arch[] = $id;
	}
	if(isset($advice_arch) && is_array($advice_arch)) $advice_all = array_diff($advice_all, $advice_arch);
	if(!empty($advice_all)) {

		$query_advice = "
			SELECT idAdvice, posted, title, description, important, author
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idCourse='".$_SESSION['idCourse']."' AND idAdvice IN ( ".implode($advice_all, ',')." )
			ORDER BY posted DESC
			LIMIT $ini,".Get::sett('visuItem');
		$re_advice = sql_query($query_advice);

		list($numofadvice) = sql_fetch_row(sql_query("
			SELECT COUNT(DISTINCT idAdvice)
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idCourse='".$_SESSION['idCourse']."' AND idAdvice IN ( ".implode($advice_all, ',')." )"));
		$nav_bar->setElementTotal($numofadvice);

		if(isset($_GET['result'])) {
			switch($_GET['result']) {
				case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
				case "err" 		: $out->add(getErrorUi($lang->def('_ERR_INSERT')));break;
				case "err_user" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			}
		}
		if(sql_num_rows($re_advice) >= 1 && $mod_perm) {
			$out->add('<div class="table-container-over">'
				.'<a class="ico-wt-sprite subs_add" href="index.php?modname=advice&amp;op=addadvice" title="'.$lang->def('_ADD_ADVICE').'">'
				.'<span>'.$lang->def('_ADD_ADVICE').'</span></a>'
				.'</div>');
		}
		while(list($idA, $posted, $title, $description, $impo, $author) = sql_fetch_row($re_advice)) {

			$out->add('<div class="list_block'.($impo?' highlight':'').'">'
				.'<h2 class="heading">');
			if($impo) {
				$out->add('<img src="'.getPathImage().'standard/important.png" alt="'.$lang->def('_IMPORTANT').'" /> ');
			}
			else {
				$out->add('');
			}
			$out->add($title.'</h2>'
				.'<div class="content"><p class="publish-date">'.Format::date($posted).'</p>'
				.$description
				.'</div>'

				.'<div class="actions">'
				.'<ul class="link_list_inline">'
				.'<li><a href="index.php?modname=advice&amp;op=archiveadvice&amp;idAdvice='.$idA.'" title="'.$lang->def('_ARCHIVE_THIS_ADVICE').' : '.$title.'">'
					.'<img src="'.getPathImage().'standard/msg_read.png" alt="'.$lang->def('_ALT_ARCHIVE').'" /> '.$lang->def('_ALT_ARCHIVE').'</a></li>');
			if($mod_perm) {
				$out->add('<li><a class="ico-wt-sprite subs_users" href="index.php?modname=advice&amp;op=modreader&amp;id_advice='.$idA.'&amp;load=1" title="'.$lang->def('_VIEW_PERMISSION').' : '.$title.'">'
					.'<span>'.$lang->def('_MOD').'</span></a></li>'
					.'<li><a class="ico-wt-sprite subs_mod" href="index.php?modname=advice&amp;op=modadvice&amp;idAdvice='.$idA.'" title="'.$lang->def('_MOD').' : '.$title.'">'
					.'<span>'.$lang->def('_MOD').'</span></a></li>'
					.'<li><a class="ico-wt-sprite subs_del" href="index.php?modname=advice&amp;op=deladvice&amp;idAdvice='.$idA.'" title="'.$lang->def('_DEL').' : '.$title.'">'
					.'<span>'.$lang->def('_DEL').'</span></a></li>' );
			}
			$out->add(
				'</ul>'
				.'</div>'
				.'</div>');
		}
	}
	if($mod_perm) {
		cout('<br/><div class="table-container-below">
			<a class="ico-wt-sprite subs_add" href="index.php?modname=advice&amp;op=addadvice" title="'.$lang->def('_ADD_ADVICE').'">
			<span>'.$lang->def('_ADD_ADVICE').'</span></a>
		</div>', 'content');

		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=deladvice]');
	}
	$form = new Form();
	$out->add($nav_bar->getNavBar($ini));
}

function archiveList() {
	require_once(_base_.'/lib/lib.table.php');

	$lang 		=& DoceboLanguage::createInstance('advice');
	$mod_perm 	= checkPerm('mod', true);
	$out		= $GLOBALS['page'];
	$out->setWorkingZone('content');

	$nav_bar 	=	new NavBar('ini', Get::sett('visuItem'), 0, 'button');
	$ini 		= $nav_bar->getSelectedElement();

	$query_my_arch_advice = "
		SELECT DISTINCT idAdvice
		FROM ".$GLOBALS['prefix_lms']."_adviceuser
		WHERE idUser = '".getLogUserId()."' AND archivied = '1'";
	$re_my_arch_advice = sql_query($query_my_arch_advice);
	while(list($id) = sql_fetch_row($re_my_arch_advice)) {
		$advice_arch[] = $id;
	}
	if(!empty($advice_arch)) {

		$query_advice = "
			SELECT idAdvice, posted, title, description, important, author
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idCourse='".$_SESSION['idCourse']."' AND idAdvice IN ( ".implode($advice_arch, ',')." )
			ORDER BY posted DESC
			LIMIT $ini,".Get::sett('visuItem');
		$re_advice = sql_query($query_advice);

		list($numofadvice) = sql_fetch_row(sql_query("
			SELECT COUNT(DISTINCT idAdvice)
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idCourse='".$_SESSION['idCourse']."' AND idAdvice IN ( ".implode($advice_arch, ',')." )"));
		$nav_bar->setElementTotal($numofadvice);

		if(isset($_GET['result'])) {
			switch($_GET['result']) {
				case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
				case "err" 		: $out->add(getErrorUi($lang->def('_ERR_INSERT')));break;
				case "err_user" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			}
		}

		while(list($idA, $posted, $title, $description, $impo, $author) = sql_fetch_row($re_advice)) {

			$out->add('<div class="list_block">'
				.'<h2 class="heading">');
			if($impo) {
				$out->add('<img src="'.getPathImage().'standard/important.png" alt="'.$lang->def('_IMPORTANT').'" /> ');
			}
			else {
				$out->add('');
			}
			$out->add($title.'</h2>'
				.'<div class="content"><p class="publish-date">'.Format::date($posted).'</p>'
				.$description
				.'</div>'
				.'<div class="actions">');
			if($mod_perm) {
				$out->add(
					'<ul class="link_list_inline">'
					.'<li><a class="ico-wt-sprite subs_users" href="index.php?modname=advice&amp;op=modreader&amp;id_advice='.$idA.'&amp;load=1" title="'.$lang->def('_VIEW_PERMISSION').' : '.$title.'">'
					.'<span>'.$lang->def('_MOD').'</span></a></li>'
					.'<li><a class="ico-wt-sprite subs_mod" href="index.php?modname=advice&amp;op=modadvice&amp;idAdvice='.$idA.'" title="'.$lang->def('_MOD').' : '.$title.'">'
					.'<span>'.$lang->def('_MOD').'</span></a></li>'
					.'<li><a class="ico-wt-sprite subs_del" href="index.php?modname=advice&amp;op=deladvice&amp;idAdvice='.$idA.'" title="'.$lang->def('_DEL').' : '.$title.'">'
					.'<span>'.$lang->def('_DEL').'</span></a></li>'
					.'</ul>');

				require_once(_base_.'/lib/lib.dialog.php');
				setupHrefDialogBox('a[href*=deladvice]');
			}
			$out->add('</div>'
				.'</div><br />');
		}
	}

	$form = new Form();
	$out->add(	$form->getHidden('archive_status', 'archive_status', '1')
				.$nav_bar->getNavBar($ini));
}

function advice() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.tab.php');
	require_once(_base_.'/lib/lib.form.php');

	$active_tab = Get::req('tab', DOTY_ALPHANUM, 'advice');

	$lang 		=& DoceboLanguage::createInstance('advice');
	$mod_perm 	= checkPerm('mod', true);
	$out		= $GLOBALS['page'];
	$out->setWorkingZone('content');

            $out->add(
        getTitleArea($lang->def('_ADVICE'), 'advice'));
    
    
	cout('<br><div class="yui-navset yui-navset-top tab_block">
		<ul class="nav nav-tabs">
			<li class="first '.($active_tab == 'advice' ? 'active' : '').'">
				<a href="index.php?modname=advice&amp;op=advice&amp;tab=advice">
					<em>'.Lang::t('_UNREAD', 'advice').'</em>
				</a>
			</li>
			<li class="'.($active_tab == 'archive' ? 'active' : '').'">
				<a href="index.php?modname=advice&amp;op=advice&amp;tab=archive">
					<em>'.Lang::t('_HISTORY', 'advice').'</em>
				</a>
			</li>
		</ul>
		<div class="yui-content">');
	switch($active_tab) {
		case "advice" : {
			adviceList();
		};break;
		case "archive" : {
			archiveList();
		};break;
	}
    
  

    
	cout('<div class="nofloat"></div>
		</div>
	</div>');
}

function addadvice() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	$lang 	=& DoceboLanguage::createInstance('advice');
	$form 	= new Form();

	//finding group
	$acl_man =& Docebo::user()->getAclManager();
	$db_groups = $acl_man->getBasePathGroupST('/lms/course/'.$_SESSION['idCourse'].'/group/', true);
	$groups = array();
	$groups['me'] = $lang->def('_YOUONLY');
	while(list($idst, $groupid) = each($db_groups)) {

		$groupid = substr($groupid, strlen('/lms/course/'.$_SESSION['idCourse'].'/group/'));
		if($groupid == 'alluser') {
			$groupid = $lang->def('_ALL');
			$sel = $idst;
		}
		$groups[$idst] = $groupid;
	}
	$groups['sel_user'] = $lang->def('_MANUAL_USER_SEL');
	$title = array(
		'index.php?modname=advice&amp;op=advice' => $lang->def('_ADVICE'),
		$lang->def('_ADD_ADVICE')
	);



	$GLOBALS['page']->add(getTitleArea($title, 'advice')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=advice&amp;op=advice', $lang->def('_BACK') )
		.$form->openForm('adviceform', 'index.php?modname=advice&amp;op=insadvice')
		.$form->openElementSpace()
		.$form->getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $lang->def('_NOTITLE'))
		.$form->getCheckbox($lang->def('_MARK_AS_IMPORTANT'), 'impo', 'impo', 1)
		.$form->getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $lang->def('_DESCRIPTION'))

		.$form->getDropDown($lang->def('_RECIPIENTS') ,'group', 'idGroup', $groups, $sel)
		.$form->closeElementSpace()
		.$form->openButtonSpace()
		.$form->getButton('addadvice', 'addadvice', $lang->def('_INSERT'),false,'onclick="showMsg(\''.$lang->def('_WAITING').'\');"')
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm()
		.'</div>', 'content' );
}

function insadvice() {
	checkPerm('mod');

	if( $_POST['title'] == "" ) $_POST['title'] = Lang::t('_NOTITLE');
	if(isset($_POST['impo'])) $impo = 1;
	else $impo = '0';

	$queryIns = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_advice
		( idCourse, author, title, description, posted, important ) VALUES
		( 	'".(int)$_SESSION['idCourse']."',
			'".getLogUserId()."',
			'".$_POST['title']."',
			'".$_POST['description']."',
			'".date("Y-m-d H:i:s")."',
			'".(int)$impo."' )";

	if(!sql_query($queryIns)) Util::jump_to('index.php?modname=advice&op=advice&result=err');
	list($id_advice) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

	$acl_man =& Docebo::user()->getAclManager();

	switch($_POST['idGroup']) {
		case "sel_user" : {

			Util::jump_to('index.php?modname=advice&op=modreader&id_advice='.$id_advice.'&load=1');
		};break;
		case "me" : {

			$members = array(getLogUserId());
			$query_insert = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_adviceuser
				( idUser, idAdvice ) VALUES
				( '".getLogUserId()."', '".$id_advice."' )";
				if(!sql_query($query_insert)) Util::jump_to('index.php?modname=advice&op=advice&result=err_user');
		};break;
		default: {

			$query_insert = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_adviceuser
				( idUser, idAdvice ) VALUES
				( '".getLogUserId()."', '".$id_advice."' )";
			if(!sql_query($query_insert)) Util::jump_to('index.php?modname=advice&op=advice&result=err_user');

			$query_insert = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_adviceuser
				( idUser, idAdvice ) VALUES
				( '".$_POST['idGroup']."', '".$id_advice."' )";
			if(!sql_query($query_insert)) Util::jump_to('index.php?modname=advice&op=advice&result=err_user');

			$members = $acl_man->getGroupAllUser($_POST['idGroup']);
		};break;
	}
	$members[] = getLogUserId();
	require_once(_base_.'/lib/lib.eventmanager.php');

	$msg_composer = new EventMessageComposer();
	$_POST['description'] = str_replace(array('\r', '\n'), '', $_POST['description']);
	$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
	$msg_composer->setBodyLangText('email', '_ALERT_TEXT', array(	'[url]' => Get::sett('url'),
																	'[course]' => $GLOBALS['course_descriptor']->getValue('name'),
																	'[title]' => stripslashes($_POST['title']),
																	'[text]' => stripslashes($_POST['description']) ) );

	$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', array(	'[url]' => Get::sett('url'),
																	'[course]' => $GLOBALS['course_descriptor']->getValue('name'),
																	'[title]' => stripslashes($_POST['title']),
																	'[text]' => stripslashes($_POST['description']) ) );

	createNewAlert(	'AdviceNew',
					'advice',
					'add',
					'1',
					'Inserted advice in course '.$_SESSION['idCourse'],
					$members,
					$msg_composer );

	Util::jump_to('index.php?modname=advice&op=advice&result=ok');
}

function modadvice() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('advice');
	$form = new Form();

	$query_advice = "
		SELECT title, description, important
		FROM ".$GLOBALS['prefix_lms']."_advice
		WHERE idAdvice='".(int)$_GET['idAdvice']."'";
	list($title, $description, $impo) = sql_fetch_row( sql_query( $query_advice ));

	$page_title = array(
		'index.php?modname=advice&amp;op=advice' => $lang->def('_ADVICE'),
		$lang->def('_MOD')
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'advice')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=advice&amp;op=advice', $lang->def('_BACK'))
		.$form->openForm('adviceform', 'index.php?modname=advice&amp;op=upadvice')
		.$form->openElementSpace()
		.$form->getHidden('idAdvice', 'idAdvice', $_GET['idAdvice'])
		.$form->getTextfield($lang->def('_TITLE'), 'title', 'title', 60, $title)
		.$form->getCheckbox($lang->def('_MARK_AS_IMPORTANT'), 'impo', 'impo', 1, $impo)
		.$form->getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
		.$form->closeElementSpace()
		.$form->openButtonSpace()
		.$form->getButton('addadvice', 'addadvice', $lang->def('_SAVE'))
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm()
		.'</div>', 'content');
}

function upadvice() {
	checkPerm('mod');

	if ($_POST['title'] == "") $_POST['title'] = Lang::t('_NOTITLE');
	if($_POST['impo'] != '1') $impo = '0';
	else $impo = '1';

	$query_advice = "
		UPDATE ".$GLOBALS['prefix_lms']."_advice
		SET title='".$_POST['title']."',
			description='".$_POST['description']."',
			important='".( isset($_POST['impo']) ? 1 : 0 )."'
		WHERE idAdvice='".(int)$_POST['idAdvice']."'";
	if(!sql_query($query_advice)) Util::jump_to('index.php?modname=advice&op=advice&result=err');

	Util::jump_to('index.php?modname=advice&op=advice&result=ok');
}

function modreader() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.userselector.php');
	$lang =& DoceboLanguage::createInstance('advice', 'lms');
	$out =& $GLOBALS['page'];
	$id_advice = importVar('id_advice', true, 0);

	$aclManager = new DoceboACLManager();
	$user_select = new UserSelector();

	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = FALSE;
	$user_select->show_fncrole_selector = FALSE;
	$user_select->nFields = 0;
	$user_select->learning_filter = 'course';

	if(isset($_GET['load'])) {

		$query_reader = "
			SELECT idUser
			FROM ".$GLOBALS['prefix_lms']."_adviceuser
			WHERE idAdvice = '".$id_advice."'";
		$re_reader = sql_query($query_reader);
		$users = array();
		$all_reader = false;
		while(list($id_user) = sql_fetch_row($re_reader)) {

			if($id_user == 'all') $all_reader = true;
			$users[] = $id_user;
		}
		if($all_reader == true) {
			$query_reader = "
				SELECT idUser
				FROM ".$GLOBALS['prefix_lms']."_courseuser
				WHERE idCourse = '".$_SESSION['idCourse']."'";
			$re_reader = sql_query($query_reader);
			$users = array();
			while(list($id_user) = sql_fetch_row($re_reader)) {

				$users[] = $id_user;
			}
		}
		$user_select->resetSelection($users);
	}
	$arr_idstGroup = $aclManager->getGroupsIdstFromBasePath('/lms/course/'.(int)$_SESSION['idCourse'].'/subscribed/');
	$me = array(getLogUserId());
	$user_select->setUserFilter('exclude', $me);
	$user_select->setUserFilter('group',$arr_idstGroup);
	$arr_idstUser = $aclManager->getAllUsersFromIdst($arr_idstGroup);
	$user_select->setUserFilter('user',$arr_idstUser);
	//$user_select->setGroupFilter('path', '/lms/course/'.$_SESSION['idCourse'].'/group');

	$user_select->setPageTitle(getTitleArea(
		array('index.php?modname=advice&amp;op=advice' => $lang->def('_ADVICE'),
		$lang->def('_VIEW_PERMISSION')), 'advice'));
	$user_select->loadSelector('index.php?modname=advice&amp;op=modreader&amp;id_advice='.$id_advice,
			$lang->def('_ADVICE'),
			$lang->def('_CHOOSE_READER'),
			true);

}

function updreader() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.userselector.php');

	$lang =& DoceboLanguage::createInstance('advice', 'lms');

	$id_advice 		= importVar('id_advice', true, 0);

	$user_select 	= new UserSelector();
	$user_selected 	= $user_select->getSelection($_POST);

	$query_reader = "
		SELECT idUser
		FROM ".$GLOBALS['prefix_lms']."_adviceuser
		WHERE idAdvice = '".$id_advice."'";
	$re_reader = sql_query($query_reader);
	$old_users = array();

	$found = false;
	$me = getLogUserId();
	while(list($id_user) = sql_fetch_row($re_reader)) {

		$old_users[] = $id_user;
		if($id_user == $me) $found = true;
	}
	$add_reader = array_diff($user_selected, $old_users);
	$del_reader = array_diff($old_users, $user_selected);

	if(!$found) $add_reader[] = $me;

	$dest = array();
	if(is_array($add_reader)) {

		while(list(, $idst) = each($add_reader)) {

			$query_insert = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_adviceuser
				( idUser, idAdvice ) VALUES
				( 	'".$idst."',
					'".$id_advice."' )";
			sql_query($query_insert);
			$dest[] = $idst;
		}
	}
	if(is_array($del_reader)) {

		while(list(, $idst) = each($del_reader)) {

			$query_delete = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_adviceuser
				WHERE idUser='".$idst."' AND idAdvice='".$id_advice."'";
			sql_query($query_delete);
		}
	}
	if(is_array($dest)) {
		require_once(_base_.'/lib/lib.eventmanager.php');

		$query_advice = "
			SELECT title, description, important
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idAdvice='".(int)$id_advice."'";
		list($title, $description, $impo) = sql_fetch_row( sql_query( $query_advice ));

		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_ALERT_TEXT', array(	'[url]' => Get::sett('url'),
																		'[course]' => $GLOBALS['course_descriptor']->getValue('name'),
																		'[title]' => stripslashes($title),
																		'[text]' => stripslashes($description) ) );

		$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', array(	'[url]' => Get::sett('url'),
																		'[course]' => $GLOBALS['course_descriptor']->getValue('name'),
																		'[title]' => stripslashes($title),
																		'[text]' => stripslashes($description) ) );

	createNewAlert(	'AdviceNew',
					'advice',
					'add',
					'1',
					'Inserted advice '.$title.' in course '.$_SESSION['idCourse'],
					$dest,
					$msg_composer );
	}
	Util::jump_to('index.php?modname=advice&op=advice');
}

function deladvice() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$lang 		=& DoceboLanguage::createInstance('advice');
	$id_advice 	= importVar('idAdvice', true, 0);

	if(isset($_POST['undo'])) {

		Util::jump_to('index.php?modname=advice&op=advice');
	} elseif(isset($_GET['confirm'])) {

		if(!sql_query("
			DELETE FROM ".$GLOBALS['prefix_lms']."_adviceuser
			WHERE idAdvice='".$id_advice."'")) Util::jump_to('index.php?modname=advice&op=advice&result=err_del');

		elseif(!sql_query("
			DELETE FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idAdvice='".$id_advice."'")) Util::jump_to('index.php?modname=advice&op=advice&result=err_del');

		else Util::jump_to('index.php?modname=advice&op=advice&result=ok');
	} else {

		list($advice, $text) = sql_fetch_row(sql_query("
			SELECT title, description
			FROM ".$GLOBALS['prefix_lms']."_advice
			WHERE idAdvice = '".(int)$_GET['idAdvice']."'"));

		$form = new Form();
		$page_title = array(
			'index.php?modname=advice&amp;op=advice' => $lang->def('_ADVICE'),
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'advice')
			.'<div class="std_block">'
			.$form->openForm('del_advice', 'index.php?modname=advice&amp;op=deladvice')
			.$form->getHidden('idAdvice', 'idAdvice', $id_advice)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_TITLE').' : </span>'.$advice.'<br />'
								.'<span>'.$lang->def('_DESCRIPTION').' : </span>'.$text,
							false,
							'confirm',
							'undo'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function archiveadvice() {
	checkPerm('view');

	$id_advice = importVar('idAdvice');

	$acl 			=& Docebo::user()->getAcl();
	$user_idst 		= $acl->getUserGroupsST(getLogUserId());
	$iam		 	= getLogUserId();
	$user_idst[]	= $iam;

	$query_my_advice = "
		SELECT DISTINCT idAdvice, idUser
		FROM ".$GLOBALS['prefix_lms']."_adviceuser
		WHERE idUser IN ( ".implode($user_idst, ',')." ) AND idAdvice = '".$id_advice."'";
	$re_my_advice = sql_query($query_my_advice);

	if(sql_num_rows($re_my_advice)) {

		$direct = false;
		while(list($id, $id_u) = sql_fetch_row($re_my_advice)) {

			if($id_u == $iam) $direct = true;
		}
		if($direct) {

			$query_advice = "
				UPDATE ".$GLOBALS['prefix_lms']."_adviceuser
				SET archivied = '1'
				WHERE idAdvice = '".$id_advice."' AND idUser = '".$iam."'";
		} else {

			$query_advice = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_adviceuser
				( idUser, idAdvice, archivied ) VALUES
				( '".$iam."', '".$id_advice."', '1' )";
		}
		if(!sql_query($query_advice)) Util::jump_to('index.php?modname=advice&op=advice&result=err');
		Util::jump_to('index.php?modname=advice&op=advice&result=ok');
	} else {
		Util::jump_to('index.php?modname=advice&op=advice');
	}
}

function adviceDispatch($op) {

	if(isset($_POST['undo'])) 			$op = 'advice';
	if(isset($_POST['okselector'])) 	$op = 'updreader';
	if(isset($_POST['cancelselector'])) $op = 'advice';

	switch($op) {
		case "advice" : {
			advice();
		};break;
		case "readadvice" : {
			readadvice();
		};break;

		case "addadvice" : {
			addadvice();
		};break;
		case "insadvice" : {
			insadvice();
		};break;

		case "modadvice" : {
			modadvice();
		};break;
		case "upadvice" : {
			upadvice();
		};break;
		case "modreader" : {
			modreader();
		};break;
		case "updreader" : {
			updreader();
		};break;

		case "deladvice" : {
			deladvice();
		};break;

		case "archiveadvice" : {
			archiveadvice();
		};break;
	}
}

?>