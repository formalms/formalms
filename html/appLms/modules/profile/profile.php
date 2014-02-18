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

function profile() {
	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');

	$lang =& DoceboLanguage::createInstance('profile', 'framework');

	$profile = new LmsUserProfile( getLogUserId() );
	$profile->init('profile', 'framework', 'modname=profile&op=profile&id_user='.getLogUserId(), 'ap');
	if(checkPerm('mod', true)) $profile->enableEditMode();

	
	if(Get::sett('profile_only_pwd') == 'on') {
		
		$GLOBALS['page']->add(
			$profile->getTitleArea()
	
			.$profile->getHead()
	
			.$profile->performAction(false, 'mod_password')
			
			.profileBackUrl()
	
			.$profile->getFooter()
		, 'content');
	} else {
		
		$GLOBALS['page']->add(
			$profile->getTitleArea()
	
			.$profile->getHead()
	
			.$profile->performAction()
			
			.profileBackUrl()
	
			.$profile->getFooter()
		, 'content');		
	}
}

function profileBackUrl()
	{
		$lang =& DoceboLanguage::createInstance('profile', 'framework');
		$id_user = importVar('id_user', true, 0);
		$type = importVar('type', false, 'false');
		$from = importVar('from', true, 0);
		$back_my_friend = importVar('back', true, 0);
		if ($type !== 'false')
			if ($from == 0)
				return getBackUi('index.php?modname=profile&op=profile&id_user='.$id_user.'&ap=goprofile', '<< '.$lang->def('_BACK').'');
			else
				return getBackUi('index.php?modname=myfiles&op=myfiles&working_area='.$type, '<< '.$lang->def('_BACK').'');
		if ($back_my_friend)
			return getBackUi('index.php?modname=myfriends&op=myfriends', '<< '.$lang->def('_BACK'));
		return false;
		
	}
	
// XXX: renewal expired password
function renewalpwd() {

	require_once(_base_.'/lib/lib.usermanager.php');
	$user_manager = new UserManager();
	$lang 		=& DoceboLanguage::createInstance('profile', 'framework');


	if($user_manager->clickSaveElapsed()) {

		$error = $user_manager->saveElapsedPassword();
		if($error['error'] != true) {
			
			unset($_SESSION['must_renew_pwd']);
			Util::jump_to('index.php?r=elearning/show&sop=unregistercourse');
		}
	}

	$_SESSION['must_renew_pwd'] = 1;
	$res = Docebo::user()->isPasswordElapsed();

	if($res == 2)  $GLOBALS['page']->add(getTitleArea($lang->def('_CHANGEPASSWORD')), 'content');
	else $GLOBALS['page']->add(getTitleArea($lang->def('_TITLE_CHANGE')), 'content');

	$GLOBALS['page']->add(
	'<div class="std_block">'
	.$user_manager->getElapsedPassword('index.php?modname=profile&amp;op=renewalpwd')
	.'</div>', 'content');
}

function profileDispatch($op) {
	if(isset($_POST['undo'])) $op = 'profile';
	switch($op) {
		case "profile" : {
			profile();
		};break;
		case "modprofile" : {
			modprofile();
		};break;
		case "saveprofile" : {
			saveprofile();
		};break;

		case "newavatar" : {
			newavatar();
		};break;
		case "upavatar" : {
			upavatar();
		};break;

		case "renewalpwd" : {
			renewalpwd();
		};break;
	}
}


?>