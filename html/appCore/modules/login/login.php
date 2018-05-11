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

function login() {
	die();
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
	
	$lang 	=& DoceboLanguage::CreateInstance('login', 'framework');
	$out 	=& $GLOBALS['page'];
	
	$user_manager = new UserManager();
	
	$out->setWorkingZone('content');
	
	$extra = false;
	if(isset($GLOBALS['logout'])) {
		$extra = array( 'style' => 'logout_action', 'content' => $lang->def('_UNLOGGED') );
	}
	if(isset($GLOBALS['access_fail'])) {
		$extra = array( 'style' => 'noaccess', 'content' => $lang->def('_NOACCESS') );
	}
	
	$out->add(
		Form::openForm('admin_box_login', 'index.php?modname=login&amp;op=confirm')
		.$user_manager->getLoginMask('index.php?modname=login&amp;op=login', $extra)
		.Form::closeForm()
	);
}

function loginDispatch($op) {
switch($op) {
	case "login" : {
		Util::jump_to(Get::rel_path('base').'/index.php?modname=login&amp;op=login');
		//login();
	};break;
}
}
?>