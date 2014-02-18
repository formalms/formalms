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

function getprofile($id_user) {

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

	$acl_man 	=& Docebo::user()->getAClManager();
	$lang 		=& DoceboLanguage::createInstance('profile', 'framework');

	$user_info = $acl_man->getUser($id_user, false);

	$txt = '<div>';

	$txt .= '<div class="boxinfo_title">'.$lang->def('_USERPARAM').'</div>'
		.Form::getLineBox($lang->def('_USERNAME'), $acl_man->relativeId($user_info[ACL_INFO_USERID]) )
		.Form::getLineBox($lang->def('_LASTNAME'), $user_info[ACL_INFO_LASTNAME] )
		.Form::getLineBox($lang->def('_NAME'), $user_info[ACL_INFO_FIRSTNAME] )
		.Form::getLineBox($lang->def('_EMAIL'), $user_info[ACL_INFO_EMAIL] )
		.Form::getBreakRow()
		.'<div class="boxinfo_title">'.$lang->def('_USERFORUMPARAM').'</div>'
		.'<table class="profile_images">'
		.'<tr><td>';
	// NOTE: avatar
	if($user_info[ACL_INFO_AVATAR] != "") {

		$img_size = getimagesize($path.$user_info[ACL_INFO_AVATAR]);
		$txt .= '<img class="profile_image'
			.( $img_size[0] > 150 || $img_size[1] > 150 ? ' image_limit' : '' )
			.'" src="'.$path.$user_info[ACL_INFO_AVATAR].'" alt="'.$lang->def('_AVATAR').'" /><br />';
	} else {

		$txt .= '<div class="text_italic">'.$lang->def('_NOAVATAR', 'profile').'</div>';
	}
	// NOTE: signature
	$txt .= '</td></tr></table>'
		.'<div class="title">'.$lang->def('_SIGNATURE').'</div>'
		.'<div class="profile_signature">'.$user_info[ACL_INFO_SIGNATURE].'</div><br />'."\n";

	$txt .='</div>';
	return $txt;
}

?>