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

function publicAdminManager_list() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.table.php');
	
	$lang =& DoceboLanguage::createInstance('adminrules', 'framework');
	$lang =& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$aclManager = new DoceboACLManager();

	// get users to show --------------------------------------------------
	$admin_group_idst = $aclManager->getGroupST(ADMIN_GROUP_PUBLICADMIN);
	$arr_admin_idst = $aclManager->getGroupUMembers( $admin_group_idst );
	$arr_admin_id = array_flip($aclManager->getArrUserST( $arr_admin_idst ));


	$pm =& PlatformManager::createInstance();

	$lms_is_active = $pm->isLoaded("lms");
	/*$cms_is_active = $pm->isLoaded("cms");*/

	// print table --------------------------------------------------------
	$table = new Table( 	Get::sett('visuItem'),
							$lang->def('_PUBLIC_ADMIN_USER'),
							$lang->def('_PUBLIC_ADMIN_USER') );
	$table->initNavBar('ini', 'link');
	$table->setLink('index.php?modname=public_admin_manager&amp;op=view&amp;ini=');
	$ini = $table->getSelectedElement();

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">', 'content');

	$contentH = array( 	$lang->def( '_USERNAME' ),

						'<img src="'.getPathImage().'admin_manager/man_pref.gif" alt="'.$lang->def( '_ADMIN_PREFERENCES_TITLE', 'adminrules').'" '
							.'title="'.$lang->def( '_ADMIN_PREFERENCES_TITLE', 'adminrules').'" />',

						'<img src="'.getPathImage().'admin_manager/man_menu.gif" alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" '
							.'title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" />',

						'<img src="'.getPathImage().'directory/tree.gif" alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" '
							.'title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" />');
	$typeH = array( '', 'image', 'image', 'image');
	if ($lms_is_active) {

		$contentH[] = '<img src="'.getPathImage().'admin_manager/man_course.gif" alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" '
							.'title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').'" />';
		$typeH[] = 'image';
	}
	
	$table->setColsStyle($typeH);
	$table->addHead($contentH);

	$maxItem = ( count($arr_admin_id) < ( $ini + Get::sett('visuItem') ) )
		? count($arr_admin_id)
		: $ini + Get::sett('visuItem');

	for( $index = $ini; $index < $maxItem; $index++ ) {

		$admin_userid = substr($arr_admin_id[$arr_admin_idst[$index]], 1);
		$rowContent = array($admin_userid);

		// Edit preferences
		$rowContent[] = '<a href="index.php?modname=public_admin_manager&amp;op=edit_preferences&amp;adminidst='.$arr_admin_idst[$index].'"
						 title="'.$lang->def( '_ADMIN_PREFERENCES_TITLE', 'adminrules').' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'admin_manager/man_pref.gif"'
							.' alt="'.$lang->def( '_ADMIN_PREFERENCES_TITLE', 'adminrules').' : '.$admin_userid.'" /></a>';

		// Edit menu
		$rowContent[] = '<a href="index.php?modname=public_admin_manager&amp;op=edit_menu&amp;adminidst='.$arr_admin_idst[$index].'"
						 title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'admin_manager/man_menu.gif"'
							.' alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'" /></a>';

		// Edit user
		$rowContent[] = '<a href="index.php?modname=public_admin_manager&amp;op=assign_tree&amp;adminidst='.$arr_admin_idst[$index].'"
		 					title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'directory/tree.gif" '
							.'alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'" /></a>';

		// Edit course
		if($lms_is_active) {

			$rowContent[] = '<a href="index.php?modname=public_admin_manager&amp;op=edit_course&amp;adminidst='.$arr_admin_idst[$index].'&amp;load=1"
								 title="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'">'
							.'<img src="'.getPathImage().'admin_manager/man_course.gif"'
								.' alt="'.$lang->def( '_ASSIGN_USERS', 'adminrules').' : '.$admin_userid.'" /></a>';
		}
		
		$table->addBody($rowContent);
	}

	$GLOBALS['page']->add(
		$table->getTable()
		.$table->getNavBar($ini, count($arr_admin_id)),'content');

	$GLOBALS['page']->add( '</div>', 'content' );
}

function publicAdminManager_assign_tree( $adminidst ) {
	checkPerm('view');
	if( $adminidst == 0 )
		return;
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.publicadminmanager.php');
	$directory = new UserSelector();
	$lang =& DoceboLanguage::createInstance('adminrules', 'framework');
	$lang =& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$aclManager = new DoceboACLManager();
	$adminManager = new PublicAdminManager();
	if( isset($_POST['okselector']) ) {

		$arr_selected = $directory->getSelection($_POST);
		$arr_unselected = $directory->getUnselected();
		foreach( $arr_unselected as $idstTree )
			$adminManager->removeAdminTree( $idstTree, $adminidst );
		foreach( $arr_selected as $idstTree )
			$adminManager->addAdminTree( $idstTree, $adminidst );
		Util::jump_to( 'index.php?modname=public_admin_manager&amp;op=view' );
	} elseif( isset($_POST['cancelselector']) ) {
		Util::jump_to( 'index.php?modname=public_admin_manager&amp;op=view' );
	} else {
		if( !isset($_GET['stayon']) ) {
			$directory->resetSelection($adminManager->getAdminTree($adminidst));
		}
		$admin_info = $aclManager->getUser($adminidst, false);
		$directory->show_user_selector = FALSE;
		$directory->show_group_selector = TRUE;
		$directory->show_orgchart_selector = TRUE;
		$directory->show_orgchart_simple_selector = TRUE;
		$directory->multi_choice = TRUE;
		$directory->loadSelector('index.php?modname=public_admin_manager&amp;op=assign_tree&amp;adminidst='.$adminidst.'&amp;stayon=1',
						$lang->def( '_ASSIGN_USERS', 'adminrules'),
						'<span class="text_bold">'.substr($admin_info[ACL_INFO_USERID], 1).'</span>',
						TRUE);
	}

}

function PublicAdminManager_edit_preferences() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.preference.php');

	$lang =& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$aclManager =& Docebo::user()->getAclManager();

	$adminidst = importVar('adminidst', true, 0);
	$user_pref = new UserPreferences($adminidst);

	if(isset($_POST['save_pref'])) {

		$user_pref->savePreferences($_POST);
		Util::jump_to('index.php?modname=public_admin_manager&op=view');
	}
	$admin_info = $aclManager->getUser($adminidst, false);
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.Form::getFormHeader($lang->def('_ADMIN_SPECIAL_SETTING').' '.substr($admin_info[ACL_INFO_USERID], 1))
		.Form::openForm('admin_preferences', 'index.php?modname=public_admin_manager&amp;op=edit_preferences')
		.Form::openElementSpace()
		.Form::getHidden('adminidst', 'adminidst', $adminidst)

		.$user_pref->getModifyMask('admin_rules')

		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save_pref', 'save_pref', $lang->def('_SAVE'))
		.Form::getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'))
		.Form::closeElementSpace()
		.'</div>', 'content');
}

function publicAdminManager_edit_menu() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.publicadminmanager.php');

	$lang 			=& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$aclManager 	=& Docebo::user()->getAclManager();
	$adminidst 		= importVar('adminidst', true, 0);
	$out 			=& $GLOBALS['page'];

	$admin_manager 	= new PublicAdminManager();
	$role_list = $admin_manager->getPublicUserAdminRole();



	$out->setWorkingZone('content');

	$out->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.Form::openForm('admin_menu_editing', '')
		.Form::getHidden('adminidst', 'adminidst', $adminidst));

	$table = new Table( 	0,
							$lang->def('_ADMIN_MANAGMENT'),
							$lang->def('_ADMIN_MANAGMENT') );
	$head = array('',$lang->def('_ACTION'));
	$style = array('image','');

	$table->addHead($head, $style);
	foreach($role_list as $role)
	{

		if (isset($_POST['save_permission']))
		{ 
			if (isset($_POST[$role['roleid']]) && $_POST[$role['roleid']] == $role['idst'])
			{
				if (!$admin_manager->controlPublicUserAdminRole($adminidst, $role['idst']))
				{
				
					$admin_manager->addPublicAdminRole($adminidst, $role['idst']);
				}
			}
			else
			{
				if ($admin_manager->controlPublicUserAdminRole($adminidst, $role['idst']))
				{
					$admin_manager->delPublicAdminRole($adminidst, $role['idst']);
				}
			}
		}
		$lang_string = $role['roleid'];
		$lang_string = str_replace('/lms/course/public/public_user_admin/', '_USER_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_course_admin/', '_COURSE_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_subscribe_admin/', '_SUBSCRIBE_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_report_admin/', '_REPORT_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_newsletter_admin/', '_NEWSLETTER_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_certificate_release/', '_CERTIFICATES_', $lang_string);
		$lang_string = str_replace('/lms/course/public/public_coursepanel/', '_COURSEPANEL_COMPETENCES', $lang_string);
		$lang_string = strtoupper($lang_string);

		
		$content = array();
		$content[] = '<input class="check" type="checkbox" '
			.'id="'.$role['roleid'].'" '
			.'name="'.$role['roleid'].'" value="'.$role['idst'].'"'
			.( $admin_manager->controlPublicUserAdminRole($adminidst, $role['idst']) ? ' checked="checked"' : '' ).' />';
		$content[] = $lang->def($lang_string);
		
		$table->addBody($content);
	}

	$out->add(
		$table->getTable()
		.Form::openButtonSpace()
		.Form::getButton('save_permission', 'save_permission', $lang->def('_SAVE'))
		.Form::getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>');
}

function publicAdminManager_lang_course() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');

	$lang 		=& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$adminidst 	= importVar('adminidst', true, 0);

	$user_pref = new UserPreferences($adminidst);

	if(isset($_POST['save'])) {

		$re = $user_pref->setPreference('admin_rules.user_lang_assigned', ( isset($_POST['admin_lang']) ? urlencode(serialize($_POST['admin_lang'])) : '' ));

		Util::jump_to('index.php?modname=public_admin_manager&op=view&result='.($re ? 1 : 0 ));
	}

	$all_languages = Docebo::langManager()->getAllLangCode();
	$assigned_lang = unserialize(urldecode($user_pref->getAdminPreference('admin_rules.user_lang_assigned')));

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))

		.'<div class="std_block">'
		.Form::getFormHeader($lang->def('_SELECT_LANG_TO_ASSIGN'))
		.Form::openForm('admin_lang_editing', 'index.php?modname=public_admin_manager&amp;op=edit_lang')
		.Form::openElementSpace()
		.Form::getHidden('adminidst', 'adminidst', $adminidst)
	, 'content' );

	while(list(,$lang_code) = each($all_languages)) {

		$GLOBALS['page']->add(
			Form::getCheckbox(	'<img src="'.getPathImage('cms').'language/'.$lang_code.'.gif" alt="'.$lang_code.'" /> '.$lang_code,
								'admin_lang_'.$lang_code,
								'admin_lang['.$lang_code.']',
								'1',
								isset($assigned_lang[$lang_code]) )
		, 'content');
	}
	$GLOBALS['page']->add(
		Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	, 'content' );
}

function publicUpdateEntry(&$new_sel, &$old_sel, $type, $user) {

	$re = true;
	$to_add 	= array_diff($new_sel, $old_sel);
	$to_del 	= array_diff($old_sel, $new_sel);
	while(list(,$id_c) = each($to_add)) {

		$re &= sql_query("
		INSERT INTO ".$GLOBALS['prefix_fw']."_admin_course
		( id_entry, type_of_entry, idst_user ) VALUES
		( '".$id_c."', '".$type."', '".$user."') ");
	}
	while(list(,$id_c) = each($to_del)) {

		$re &= sql_query("
		DELETE FROM ".$GLOBALS['prefix_fw']."_admin_course
		WHERE id_entry = '".$id_c."' AND type_of_entry = '".$type."' AND idst_user = '".$user."'");
	}
	return $re;
}

function publicAdminManager_edit_course() {
	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

	$lang 		=& DoceboLanguage::createInstance('public_admin_manager', 'framework');
	$adminidst 	= importVar('adminidst', true, 0);
	$out 		=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$sel = new Course_Manager();
	$sel->setLink('index.php?modname=public_admin_manager&amp;op=edit_course');

	if(isset($_GET['load']) || isset($_POST['save_selection'])) {

		$course_initial_sel = array();
		$coursepath_initial_sel = array();
		$catalogue_initial_sel = array();
		$query = "
		SELECT id_entry, type_of_entry
		FROM ".$GLOBALS['prefix_fw']."_admin_course
		WHERE idst_user = '".$adminidst."'";
		$re_entry = sql_query($query);
		while(list($id, $type) = sql_fetch_row($re_entry)) {

			switch($type) {
				case "course" : 		$course_initial_sel[$id] = $id;break;
				case "coursepath" : 	$coursepath_initial_sel[$id] = $id;break;
				case "catalogue" : 		$catalogue_initial_sel[$id] = $id;break;
			}
		}
		if(isset($_GET['load'])) {
			$sel->resetCourseSelection($course_initial_sel);
			$sel->resetCoursePathSelection($coursepath_initial_sel);
			$sel->resetCatalogueSelection($catalogue_initial_sel);
		}
	}
	if(isset($_POST['save_selection'])) {

		$re = true;
		$course = $sel->getCourseSelection($_POST);
		$re &= publicUpdateEntry($course, $course_initial_sel, 'course', $adminidst);

		$coursepath = $sel->getCoursePathSelection($_POST);
		$re &= publicUpdateEntry($coursepath, $coursepath_initial_sel, 'coursepath', $adminidst);

		$catalogue = $sel->getCatalogueSelection($_POST);
		$re &= publicUpdateEntry($catalogue, $catalogue_initial_sel, 'catalogue', $adminidst);

		Util::jump_to('index.php?modname=public_admin_manager&amp;op=view&amp;result='.( $re ? 'ok' : 'err' ));
	}
	if(isset($_POST['undo_pref'])) {
		Util::jump_to('index.php?modname=public_admin_manager&amp;op=view');
	}
	$out->addStart(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.Form::openForm('admin_menu_editing', 'index.php?modname=public_admin_manager&amp;op=edit_course')
		.Form::getHidden('adminidst', 'adminidst', $adminidst)
		, 'content' );

	$out->addEnd(
		Form::openButtonSpace()
		.Form::getButton('save_selection', 'save_selection', $lang->def('_SAVE'))
		.Form::getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
		, 'content' );

	$sel->loadSelector();
}

// ----------------------------------------------------------------------------

function publicAdminManagerDispatch( $op ) {

	if(isset($_POST['undo_pref'])) $op = 'view';
	switch($op) {
		case "view" : {
			publicAdminManager_list();
		};break;
		case "assign_tree": {
			publicAdminManager_assign_tree(ImportVar( 'adminidst', true, 0));
		};break;
		// Extra preferences and settings
		case "edit_preferences" : {
			publicAdminManager_edit_preferences();
		};break;
		// Menu managment
		case "edit_menu" : {
			publicAdminManager_edit_menu();
		};break;
		// Lang managment
		case "edit_lang" : {
			publicAdminManager_lang_course();
		};break;
		// Course managment
		case "edit_course" : {
			publicAdminManager_edit_course();
		};break;
	}
}

?>