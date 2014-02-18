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
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.1.0
 * 
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,GEF,EMF], tabwidth = 4, font = Courier New )
 */

if(Docebo::user()->isAnonymous()) die('You can\'t access');

function myfriends(&$url) {
	checkPerm('view');
	
	require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('myfriends', 'lms');
	$acl_man 	=& Docebo::user()->getAclManager();
	
	$my_fr 		= new MyFriends(getLogUserId());
	
	$users_info = $my_fr->getFriendsList(false, false, false);
	
	require_once(_base_.'/lib/lib.user_profile.php');
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_MY_FRIENDS'), 'myfriends')
		.'<div class="std_block">'

		.'<p class="new_elem_link"><a href="'.$url->getUrl('op=searchuser').'">'.$lang->def('_SEARCH_USER').'</a></p>'
	, 'content');
	$controlled_id = array();
	$controlled_id = $my_fr->controlPendentRequest();
	if (count($controlled_id))
		for ($i = 0; $i < count($controlled_id); $i++)
		{
			$controlled_user = $acl_man->getUser($controlled_id[$i], false);
			$controlled_username =$acl_man->relativeId($controlled_user[ACL_INFO_USERID]);
			$GLOBALS['page']->add('<p>'.$lang->def('_THEUSER').' <b>'.$controlled_username.'</b> '.$lang->def('_REQUEST').'</p>', 'content');
		}
	$req_number = $my_fr->getPendentRequestCount();
	if($req_number > 0) {
		
		$GLOBALS['page']->add(
			'<p>'.str_replace('[request_number]', $req_number, $lang->def('_PENDENDT_FRIEND')).'<br />'
				.'<a href="'.$url->getUrl('op=approveuser').'">'.$lang->def('_SHOW_ME').'</a>'
			.'</p>'
			, 'content');
	}
	
	if(is_array($users_info) && !empty($users_info))
	while(list(, $ui) = each($users_info)) {
		
		$profile = new UserProfile( $ui['id'] );
		$profile->init('profile', 'framework', 'modname=myfriends&op=searchuser', 'ap');
		
		$GLOBALS['page']->add($profile->minimalUserInfo(), 'content');
		
		// action line
		$GLOBALS['page']->add(
		'<p class="mf_action_line">'
			.( $ui['waiting'] == MF_WAITING ? $lang->def('_WAITING_FOR_APPROVE').' ' : '' )
			.'<a href="'.$url->getUrl('op=delfriend&id_friend='.$ui['id']).'" '
				.'title="'.( $ui['waiting'] == MF_WAITING ? $lang->def('_DEL_WAITING_FRIEND').' : '.$profile->resolveUsername() : $lang->def('_DEL').' : '.$profile->resolveUsername()).'">'
				//.'title="'.$lang->def('_DEL').' : '.$profile->resolveUsername().'">'
				.'<img src="'.getPathImage().'standard/remfriend.gif" alt="'.( $ui['waiting'] == MF_WAITING ? $lang->def('_DEL_WAITING_FRIEND').' : '.$profile->resolveUsername() : $lang->def('_DEL').' : '.$profile->resolveUsername()).'" />'
				//.'<img src="'.getPathImage().'standard/remfriend.gif" alt="'.$lang->def('_DEL').' : '.$profile->resolveUsername().'" />'
			.'</a>'
		.'</p>', 'content');
	}
	
	$GLOBALS['page']->add(
		'</div>', 'content');
}

function approveuser(&$url) {
	checkPerm('view');
	
	require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
	require_once(_base_.'/lib/lib.table.php');
	
	$lang 		=& DoceboLanguage::createInstance('myfriends', 'lms');
	$acl_man 	=& Docebo::user()->getAclManager();
	
	$my_fr 		= new MyFriends(getLogUserId());
	
	if(isset($_GET['id_friend'])) {
		switch($_GET['action']) {
			case "2" : {
				$my_fr->addFriend($_GET['id_friend'], MF_APPROVED, '');
				$my_fr->approveFriend($_GET['id_friend']);
			};break;
			case "1" : $my_fr->approveFriend($_GET['id_friend']);break;
			case "0" : $my_fr->refuseFriend($_GET['id_friend']);break;
		}
	}
	
	$users_info = $my_fr->getPendentRequest();
	
	require_once(_base_.'/lib/lib.user_profile.php');
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_MY_FRIENDS'), 'myfriends')
		.'<div class="std_block">'
	, 'content');
	
	if(!is_array($users_info) || empty($users_info)) Util::jump_to($url->getUrl());
	while(list(, $ui) = each($users_info)) {
		
		$profile = new UserProfile( $ui['id'] );
		$profile->init('profile', 'framework', 'modname=myfriends&op=approveuser', 'ap');
		
		$GLOBALS['page']->add($profile->minimalUserInfo()
			.'<p class="mf_request">'.$ui['request'].'</p>', 'content');
		
		// action line
		$GLOBALS['page']->add(
		'<p class="mf_action_line">'
			.'<a href="'.$url->getUrl('op=approveuser&id_friend='.$ui['id']).'&action=1" '
				.'	title="'.$lang->def('_TITLE_APPROVE_FRIEND').' : '.$profile->resolveUsername().'">"'
				.$lang->def('_APPROVE_FRIEND')
			.'"</a> , '.'<a href="'.$url->getUrl('op=approveuser&id_friend='.$ui['id']).'&action=2" '
				.'	title="'.$lang->def('_TITLE_APPROVE_FRIEND_ADD_MYLIST').' : '.$profile->resolveUsername().'">"'
				.$lang->def('_APPROVE_FRIEND_ADD_TO_MYLIST')
			.'"</a> , '
			.'<a href="'.$url->getUrl('op=approveuser&id_friend='.$ui['id']).'&action=0" '
				.'	title="'.$lang->def('_TITLE_REFUSE_FRIEND').' : '.$profile->resolveUsername().'">"'
				.$lang->def('_REFUSE_FRIEND')
			.'"</a>'
		.'</p>', 'content');
	}
	
	$GLOBALS['page']->add(
		'</div>', 'content');
}
function searchUser(&$url) {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
	
	$lang 		=& DoceboLanguage::createInstance('myfriends', 'lms');
	$my_fr 		= new MyFriends(getLogUserId());
	$acl_man 	=& Docebo::user()->getAclManager();
	
	$GLOBALS['page']->add(
		getTitleArea( array( $url->getUrl() => $lang->def('_MY_FRIENDS'), $lang->def('_SEARCH_USER') ), 'myfriends')
		.'<div class="std_block">', 'content');
	
	if(isset($_POST['send'])) {
		
		if($my_fr->addFriend($_POST['id_friend'], MF_WAITING, $_POST['request'])) Util::jump_to($url->getUrl('result=ok_del'));
		$GLOBALS['page']->add( getErrorUi($lang->def('_ERR_REMOVE_FRIEND')) );
	} elseif(isset($_GET['id_friend'])) {
		
		$GLOBALS['page']->add(
			Form::openForm('send_request', $url->getUrl('op=searchuser'))
			.Form::getHidden('id_friend', 'id_friend', $_GET['id_friend'])
			
			.Form::openElementSpace()
			.Form::getTextarea(	$lang->def('_REQUEST_MESSAGE'),
									'request', 
									'request')
									
			.Form::closeElementSpace()
			
			.Form::openButtonSpace()
			.Form::getButton('send', 'send', $lang->def('_SEND_REQUEST') )
			.Form::getButton('back_search', 'back_search', $lang->def('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>'
		, 'content');
		return;		
	}
	
	$GLOBALS['page']->add(
		Form::openForm('', $url->getUrl('op=searchuser'))
		.Form::getOpenFieldset($lang->def('_SEARCH_USER'))
		
		.Form::getTextfield(	$lang->def('_SEARCH_USERNAME'),
								'username', 
								'username', 
								255,
								importVar('username', false, '') )
		
		.Form::openButtonSpace()
		.Form::getButton('search', 'search', $lang->def('_SEARCH') )
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::getCloseFieldset()
		.Form::closeForm()
	, 'content');
	
	if(isset($_POST['username'])) {
		
		$finded_user = $acl_man->getUser(false, $acl_man->absoluteId($_POST['username']));
		if($finded_user === false) {
			
			$GLOBALS['page']->add($lang->def('_NO_USER_FINDED'), 'content');
		} else {
			
			require_once(_base_.'/lib/lib.user_profile.php');
			
			if(getLogUserId() != $finded_user[ACL_INFO_IDST]) {
					
				$GLOBALS['page']->add('<p class="confirm_friend">'
					.'<a href="'.$url->getUrl('op=searchuser&id_friend='.$finded_user[ACL_INFO_IDST].'').'">'.$lang->def('_ADD_TO_MY_FIREND').'</a>'
					.'</p>', 'content');
			}
			$profile = new UserProfile( $finded_user[ACL_INFO_IDST] );
			$profile->init('profile', 'framework', 'modname=myfriends&op=searchuser', 'ap');
			
			$GLOBALS['page']->add($profile->getUserInfo(), 'content');
		}
	}
	$GLOBALS['page']->add('</div>', 'content');
}

function delfriend(&$url) {
	checkPerm('view');
	
	require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
	
	$lang 		=& DoceboLanguage::createInstance('myfriends', 'lms');
	$my_fr 		= new MyFriends(getLogUserId());
	$id_friend = importVar('id_friend', true, 0);
	
	
	$GLOBALS['page']->add(
		getTitleArea( array( $url->getUrl() => $lang->def('_MY_FRIENDS'), $lang->def('_REMOVE_FRIEND') ), 'myfriends')
		.'<div class="std_block">', 'content');
	
	if(isset($_GET['confirm'])) {
		
		if($my_fr->delFriend($id_friend)) Util::jump_to($url->getUrl('result=ok_del'));
		$GLOBALS['page']->add( getErrorUi($lang->def('_ERR_REMOVE_FRIEND')) );
	}
	$ui = $my_fr->getFriendsInfo(array($id_friend));
	if($ui == false) {
		
		$GLOBALS['page']->add( getErrorUi($lang->def('_INVALID_FRIEND')) );
	} else {
		
		$acl_man 	=& Docebo::user()->getAclManager();
		$ui 		= current($ui);
		$GLOBALS['page']->add(
			getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_USERNAME').' : </span>'.$acl_man->relativeId($ui[ACL_INFO_USERID]).'<br />'
								.'<span>'.$lang->def('_USERCOMPLETENAME').' : </span>'.$ui[ACL_INFO_LASTNAME]
								.( $ui[ACL_INFO_LASTNAME] != '' ? ' ' : '' ).$ui[ACL_INFO_FIRSTNAME],
							true, 
							$url->getUrl('op=delfriend&id_friend='.$id_friend.'&confirm=1'),
							$url->getUrl() ), 'content');
	}
	$GLOBALS['page']->add('</div>', 'content');
}

// -----------------------------------------------------------------------------------------

function myfriendsDispatch($op) {
	
	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('myfriends');
	$url->setStdQuery('modname=myfriends&op=myfriends');
	
	if(isset($_POST['undo']) || isset($_GET['undo'])) $op = 'myfriends';
	switch($op) {
		case "myfriends" : {
			myfriends($url);
		};break;
		case "searchuser" : {
			searchUser($url);
		};break;
		case "approveuser" : {
			approveuser($url);
		};break;
		case "delfriend" : {
			delfriend($url);
		};break;
	}
}

?>