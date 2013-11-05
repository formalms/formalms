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

function adminManager_list() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('adminrules', 'framework');
	$aclManager = new DoceboACLManager();

	// get users to show --------------------------------------------------
	$admin_group_idst = $aclManager->getGroupST( ADMIN_GROUP_ADMIN );
	$arr_admin_idst = $aclManager->getGroupUMembers( $admin_group_idst );
	$arr_admin_id = array_flip($aclManager->getArrUserST( $arr_admin_idst ));

	$usres_info =& $aclManager->getUsers($arr_admin_idst);


	$pm=& PlatformManager::createInstance();

	$lms_is_active = $pm->isLoaded("lms");
	$cms_is_active = $pm->isLoaded("cms");

	// print table --------------------------------------------------------
	$table = new Table( 	Get::sett('visuItem'),
							$lang->def('_ADMIN_USER'),
							$lang->def('_ADMIN_USER') );
	$table->initNavBar('ini', 'link');
	$table->setLink('index.php?modname=admin_manager&amp;op=view&amp;ini=');
	$ini = $table->getSelectedElement();

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">', 'content');

	$contentH = array( 	$lang->def( '_USERNAME' ),
						$lang->def( '_EMAIL' ),
						'<img src="'.getPathImage().'admin_manager/man_pref.gif" alt="'.$lang->def( '_ADMIN_PREFERENCES_TITLE' ).'" '
							.'title="'.$lang->def( '_ADMIN_PREFERENCES_TITLE' ).'" />',

						'<img src="'.getPathImage().'admin_manager/man_menu.gif" alt="'.$lang->def( '_ASSIGN_USERS' ).'" '
							.'title="'.$lang->def( '_ADMIN_MENU_TITLE' ).'" />',

						'<img src="'.getPathImage().'directory/tree.gif" alt="'.$lang->def( '_ASSIGN_USERS' ).'" '
							.'title="'.$lang->def( '_ASSIGN_USERS' ).'" />',

						'<img src="'.getPathImage().'admin_manager/lang_lang.gif" alt="'.$lang->def( '_ASSIGN_USERS' ).'" '
							.'title="'.$lang->def( '_ASSIGN_USERS' ).'" />'	);
	$typeH = array( '', '', 'image', 'image', 'image', 'image');
	if ($lms_is_active) {

		$contentH[] = '<img src="'.getPathImage().'admin_manager/man_course.gif" alt="'.$lang->def( '_ASSIGN_USERS' ).'" '
							.'title="'.$lang->def( '_ASSIGN_USERS' ).'" />';
		$typeH[] = 'image';
	}
	if ($cms_is_active) {

		// Cms Pages
		$contentH[] = '<img src="'.getPathImage().'admin_manager/pagetree.png" alt="'.$lang->def( '_ASSIGN_CMS_PAGES' ).'" '
							.'title="'.$lang->def( '_ASSIGN_CMS_PAGES' ).'" />';
		$typeH[] = 'image';
		// Cms News
		$contentH[] = '<img src="'.getPathImage().'admin_manager/newstree.png" alt="'.$lang->def( '_NEWS' ).'" '
							.'title="'.$lang->def( '_NEWS' ).'" />';
		$typeH[] = 'image';
		// Cms Documents
		$contentH[] = '<img src="'.getPathImage().'admin_manager/docstree.png" alt="'.$lang->def( '_ASSIGN_CMS_DOCS' ).'" '
							.'title="'.$lang->def( '_ASSIGN_CMS_DOCS' ).'" />';
		$typeH[] = 'image';
		// Cms Media
		$contentH[] = '<img src="'.getPathImage().'admin_manager/mediatree.png" alt="'.$lang->def( '_ASSIGN_CMS_MEDIA' ).'" '
							.'title="'.$lang->def( '_ASSIGN_CMS_MEDIA' ).'" />';
		$typeH[] = 'image';
		// Cms Contents
		$contentH[] = '<img src="'.getPathImage().'admin_manager/contenttree.png" alt="'.$lang->def( '_ASSIGN_CMS_CONTENT' ).'" '
							.'title="'.$lang->def( '_ASSIGN_CMS_CONTENT' ).'" />';
		$typeH[] = 'image';
	}
	$table->setColsStyle($typeH);
	$table->addHead($contentH);

	$maxItem = ( count($arr_admin_id) < ( $ini + Get::sett('visuItem') ) )
		? count($arr_admin_id)
		: $ini + Get::sett('visuItem');

	for( $index = $ini; $index < $maxItem; $index++ ) {

		$admin_userid = substr($arr_admin_id[$arr_admin_idst[$index]], 1);
		$rowContent = array($admin_userid, $usres_info[$arr_admin_idst[$index]][ACL_INFO_EMAIL]);

		// Edit preferences
		$rowContent[] = '<a href="index.php?modname=admin_manager&amp;op=edit_preferences&amp;adminidst='.$arr_admin_idst[$index].'"
						 title="'.$lang->def( '_ADMIN_PREFERENCES_TITLE' ).' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'admin_manager/man_pref.gif"'
							.' alt="'.$lang->def( '_ADMIN_PREFERENCES_TITLE' ).' : '.$admin_userid.'" /></a>';

		// Edit menu
		$rowContent[] = '<a href="index.php?modname=admin_manager&amp;op=edit_menu&amp;adminidst='.$arr_admin_idst[$index].'"
						 title="'.$lang->def( '_ADMIN_MENU_TITLE' ).' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'admin_manager/man_menu.gif"'
							.' alt="'.$lang->def( '_ADMIN_MENU_TITLE' ).' : '.$admin_userid.'" /></a>';

		// Edit user
		$rowContent[] = '<a href="index.php?modname=admin_manager&amp;op=assign_tree&amp;adminidst='.$arr_admin_idst[$index].'"
		 					title="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'directory/tree.gif" '
							.'alt="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'" /></a>';

		// Edit lang
		$rowContent[] = '<a href="index.php?modname=admin_manager&amp;op=edit_lang&amp;adminidst='.$arr_admin_idst[$index].'"
						 	title="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'">'
						.'<img src="'.getPathImage().'admin_manager/lang_lang.gif"'
							.' alt="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'" /></a>';

		// Edit course
		if($lms_is_active) {

			$rowContent[] = '<a href="index.php?modname=admin_manager&amp;op=edit_course&amp;adminidst='.$arr_admin_idst[$index].'&amp;load=1"
								 title="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'">'
							.'<img src="'.getPathImage().'admin_manager/man_course.gif"'
								.' alt="'.$lang->def( '_ASSIGN_USERS' ).' : '.$admin_userid.'" /></a>';
		}
		// Assign cms trees permissions
		if ($cms_is_active) {
			// Cms Pages
			$img ="<img src=\"".getPathImage()."admin_manager/pagetree.png\" alt=\"".$lang->def("_ASSIGN_CMS_PAGES")."\" ";
			$img.="title=\"".$lang->def("_ASSIGN_CMS_PAGES")."\" />";
			$url="index.php?modname=admin_manager&amp;op=assign_cmspag&amp;adminidst=".$arr_admin_idst[$index];
			$rowContent[]="<a href=\"".$url."\">".$img."</a>\n";

			// Cms News
			$img ="<img src=\"".getPathImage()."admin_manager/newstree.png\" alt=\"".$lang->def("_NEWS")."\" ";
			$img.="title=\"".$lang->def("_NEWS")."\" />";
			$url="index.php?modname=admin_manager&amp;op=assign_cmsnews&amp;adminidst=".$arr_admin_idst[$index];
			$rowContent[]="<a href=\"".$url."\">".$img."</a>\n";

			// Cms Documents
			$img ="<img src=\"".getPathImage()."admin_manager/docstree.png\" alt=\"".$lang->def("_ASSIGN_CMS_DOCS")."\" ";
			$img.="title=\"".$lang->def("_ASSIGN_CMS_DOCS")."\" />";
			$url="index.php?modname=admin_manager&amp;op=assign_cmsdocs&amp;adminidst=".$arr_admin_idst[$index];
			$rowContent[]="<a href=\"".$url."\">".$img."</a>\n";

			// Cms Media
			$img ="<img src=\"".getPathImage()."admin_manager/mediatree.png\" alt=\"".$lang->def("_ASSIGN_CMS_MEDIA")."\" ";
			$img.="title=\"".$lang->def("_ASSIGN_CMS_MEDIA")."\" />";
			$url="index.php?modname=admin_manager&amp;op=assign_cmsmedia&amp;adminidst=".$arr_admin_idst[$index];
			$rowContent[]="<a href=\"".$url."\">".$img."</a>\n";

			// Cms Contents
			$img ="<img src=\"".getPathImage()."admin_manager/contenttree.png\" alt=\"".$lang->def("_ASSIGN_CMS_CONTENT")."\" ";
			$img.="title=\"".$lang->def("_ASSIGN_CMS_CONTENT")."\" />";
			$url="index.php?modname=admin_manager&amp;op=assign_cmscontent&amp;adminidst=".$arr_admin_idst[$index];
			$rowContent[]="<a href=\"".$url."\">".$img."</a>\n";
		}
		$table->addBody($rowContent);
	}

	$GLOBALS['page']->add(
		$table->getTable()
		.$table->getNavBar($ini, count($arr_admin_id)),'content');

	$GLOBALS['page']->add( '</div>', 'content' );
}

function adminManager_assign_tree( $adminidst ) {
	checkPerm('view');
	if( $adminidst == 0 )
		return;
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
	$directory = new UserSelector();
	$lang =& DoceboLanguage::createInstance('adminrules', 'framework');
	$aclManager = new DoceboACLManager();
	$adminManager = new AdminManager();
	if( isset($_POST['okselector']) ) {

		$arr_selected = $directory->getSelection($_POST);
		$arr_unselected = $directory->getUnselected();
		foreach( $arr_unselected as $idstTree )
			$adminManager->removeAdminTree( $idstTree, $adminidst );
		foreach( $arr_selected as $idstTree )
			$adminManager->addAdminTree( $idstTree, $adminidst );
		Util::jump_to( 'index.php?modname=admin_manager&amp;op=view' );
	} elseif( isset($_POST['cancelselector']) ) {
		Util::jump_to( 'index.php?modname=admin_manager&amp;op=view' );
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
		$directory->loadSelector('index.php?modname=admin_manager&amp;op=assign_tree&amp;adminidst='.$adminidst.'&amp;stayon=1',
						$lang->def( '_ASSIGN_USERS' ),
						'<span class="text_bold">'.substr($admin_info[ACL_INFO_USERID], 1).'</span>',
						TRUE);
	}

}

function adminManager_edit_preferences() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.preference.php');

	$lang =& DoceboLanguage::createInstance('adminrules', 'framework');
	$aclManager =& Docebo::user()->getAclManager();

	$adminidst = importVar('adminidst', true, 0);
	$user_pref = new UserPreferences($adminidst);

	if(isset($_POST['save_pref'])) {

		$user_pref->savePreferences($_POST);
		Util::jump_to('index.php?modname=admin_manager&op=view');
	}
	$admin_info = $aclManager->getUser($adminidst, false);
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.Form::getFormHeader($lang->def('_ADMIN_SPECIAL_SETTING').' '.substr($admin_info[ACL_INFO_USERID], 1))
		.Form::openForm('admin_preferences', 'index.php?modname=admin_manager&amp;op=edit_preferences')
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

function adminManager_edit_menu() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.tab.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');

	$lang 			=& DoceboLanguage::createInstance('adminrules', 'framework');
	$aclManager 	=& Docebo::user()->getAclManager();
	$adminidst 		= importVar('adminidst', true, 0);
	$out 			=& $GLOBALS['page'];

	$admin_manager 	= new AdminManager();
	// perform other platforms login operation
	require_once(_base_.'/lib/lib.platform.php');
	$pm =& PlatformManager::createInstance();

	//prefetching tab-------------------------------------------
	$tabs = new TabView('admin_menu_tab_editing', 'index.php?modname=admin_manager&amp;op=edit_menu&amp;adminidst='.$adminidst);

	$plat = $pm->getPlatformList();
	$active_tab = importVar('tab', false, 'framework');
	foreach($plat as $code => $descr) {

		if(isset($_POST['tabelem_'.$code.'_status'])) $active_tab = $code;
		$tab = new TabElemDefault($code, $lang->def('_MENU_MANAGE_'.strtoupper($code)), getPathImage().'main_zone/'.$code.'.gif');
		$tabs->addTab($tab);
	}
	$admin_menu =& $pm->getPlatformAdminMenuInstance($active_tab);

	$all_admin_permission =& $admin_manager->getAdminPermission($adminidst);
	// save if is it required
	if(isset($_POST['save_permission'])) {
		$re = $admin_menu->savePreferences($_POST, $adminidst, $all_admin_permission);

		$all_admin_permission =& $admin_manager->getAdminPermission($adminidst);
	}

	$tabs->setActiveTab($active_tab);
	$out->setWorkingZone('content');

	$out->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.$tabs->printTabView_Begin()
		.Form::openForm('admin_menu_editing', '')
		.Form::getHidden('adminidst', 'adminidst', $adminidst)
		.Form::getHidden('tab', 'tab', $active_tab)

		.( $admin_menu !== false
			? $admin_menu->getPermissionUi($all_admin_permission, 'admin_menu_editing', 'admin_menu_editing')
			: '' )

		.Form::openButtonSpace()
		.Form::getButton('save_permission', 'save_permission', $lang->def('_SAVE'))
		.Form::getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'))
		.Form::closeButtonSpace()

		.Form::closeForm()
		.$tabs->printTabView_End()
		.'</div>');
}

function adminManager_lang_course() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');

	$lang 		=& DoceboLanguage::createInstance('adminrules', 'framework');
	$adminidst 	= importVar('adminidst', true, 0);

	$user_pref = new UserPreferences($adminidst);

	if(isset($_POST['save'])) {

		$re = $user_pref->setPreference('admin_rules.user_lang_assigned', ( isset($_POST['admin_lang']) ? urlencode(serialize($_POST['admin_lang'])) : '' ));

		Util::jump_to('index.php?modname=admin_manager&op=view&result='.($re ? 1 : 0 ));
	}

	$all_languages = Docebo::langManager()->getAllLangCode();
	$assigned_lang = unserialize(urldecode($user_pref->getAdminPreference('admin_rules.user_lang_assigned')));

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))

		.'<div class="std_block">'
		.Form::getFormHeader($lang->def('_SELECT_LANG_TO_ASSIGN'))
		.Form::openForm('admin_lang_editing', 'index.php?modname=admin_manager&amp;op=edit_lang')
		.Form::openElementSpace()
		.Form::getHidden('adminidst', 'adminidst', $adminidst)
	, 'content' );

	while(list(,$lang_code) = each($all_languages)) {

		$GLOBALS['page']->add(
			Form::getCheckbox(	'<img src="'.getPathImage('cms').'language/'.$lang_code.'.png" alt="'.$lang_code.'" /> '.$lang_code,
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

function updateEntry(&$new_sel, &$old_sel, $type, $user) {

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

function adminManager_edit_course() {
	checkPerm('view');

	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

	$lang 		=& DoceboLanguage::createInstance('adminrules', 'framework');
	$adminidst 	= importVar('adminidst', true, 0);
	$out 		=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$sel = new Course_Manager();
	$sel->setLink('index.php?modname=admin_manager&amp;op=edit_course');

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
		$re &= updateEntry($course, $course_initial_sel, 'course', $adminidst);

		$coursepath = $sel->getCoursePathSelection($_POST);
		$re &= updateEntry($coursepath, $coursepath_initial_sel, 'coursepath', $adminidst);

		$catalogue = $sel->getCatalogueSelection($_POST);
		$re &= updateEntry($catalogue, $catalogue_initial_sel, 'catalogue', $adminidst);

		Util::jump_to('index.php?modname=admin_manager&amp;op=view&amp;result='.( $re ? 'ok' : 'err' ));
	}
	if(isset($_POST['undo_pref'])) {
		Util::jump_to('index.php?modname=admin_manager&amp;op=view');
	}
	$out->addStart(
		getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'))
		.'<div class="std_block">'
		.Form::openForm('admin_menu_editing', 'index.php?modname=admin_manager&amp;op=edit_course')
		.Form::getHidden('adminidst', 'adminidst', $adminidst)
		//.Form::getHidden('tab', 'tab', $active_tab)
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


function adminManager_assignCmsPages($admin_idst) {
	checkPerm('view');
	if ($admin_idst < 1)
		return FALSE;

	if (isset($_POST["save"])) {
		adminManager_saveCmsPages($admin_idst);
		die();
	}

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");
	require_once($GLOBALS["where_cms"]."/admin/modules/manpage/class.page_selector.php");

	$res="";
	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("admin_manager", "framework");
	$form=new Form();
	$ctp=new CmsTreePermissions("page");

	addCss("base-old-treeview", "cms");

	$res.=getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'));
	$res.="<div class=\"std_block\">\n";

	$tree = new pageDb();
	$pagsel=new Selector_Page_TreeView($tree, FALSE);
	$pagsel->parsePositionData($_POST, $_POST, $_POST);
	$pagsel->setNodePerm($ctp->loadAllNodePerm($admin_idst));

	$url="index.php?modname=admin_manager&amp;op=assign_cmspag&amp;adminidst=".$admin_idst;
	$res.=$form->openForm("main_form", $url);
	$res.=$pagsel->load();


	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $lang->def('_SAVE'));
	$res.=$form->getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();

	$res.="</div>\n";
	$out->add($res);
}


function adminManager_saveCmsPages($admin_idst) {

	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");

	$ctp=new CmsTreePermissions("page");
	$ctp->saveNodePerm($admin_idst, $_POST["sel_type"]);

	Util::jump_to("index.php?modname=admin_manager&op=view");
}


function adminManager_assignCmsNews($admin_idst) {
	checkPerm('view');
	if ($admin_idst < 1)
		return FALSE;

	if (isset($_POST["save"])) {
		adminManager_saveCmsNews($admin_idst);
		die();
	}

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");
	require_once($GLOBALS["where_cms"]."/admin/modules/news/class.news_selector.php");

	$res="";
	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("admin_manager", "framework");
	$form=new Form();
	$ctp=new CmsTreePermissions("news");

	addCss("base-old-treeview", "cms");

	$res.=getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'));
	$res.="<div class=\"std_block\">\n";

	$tree = new newsDb();
	$news_sel=new Selector_News_TreeView($tree, FALSE);
	$news_sel->parsePositionData($_POST, $_POST, $_POST);
	$news_sel->setNodePerm($ctp->loadAllNodePerm($admin_idst));

	$url="index.php?modname=admin_manager&amp;op=assign_cmsnews&amp;adminidst=".$admin_idst;
	$res.=$form->openForm("main_form", $url);
	$res.=$news_sel->load();


	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $lang->def('_SAVE'));
	$res.=$form->getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();

	$res.="</div>\n";
	$out->add($res);
}


function adminManager_saveCmsNews($admin_idst) {

	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");

	$ctp=new CmsTreePermissions("news");
	$ctp->saveNodePerm($admin_idst, $_POST["sel_type"]);

	Util::jump_to("index.php?modname=admin_manager&op=view");
}


function adminManager_assignCmsDocs($admin_idst) {
	checkPerm('view');
	if ($admin_idst < 1)
		return FALSE;

	if (isset($_POST["save"])) {
		adminManager_saveCmsDocs($admin_idst);
		die();
	}

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");
	require_once($GLOBALS["where_cms"]."/admin/modules/docs/class.docs_selector.php");

	$res="";
	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("admin_manager", "framework");
	$form=new Form();
	$ctp=new CmsTreePermissions("document");

	addCss("base-old-treeview", "cms");

	$res.=getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'));
	$res.="<div class=\"std_block\">\n";

	$tree = new docsDb();
	$docs_sel=new Selector_Docs_TreeView($tree, FALSE);
	$docs_sel->parsePositionData($_POST, $_POST, $_POST);
	$docs_sel->setNodePerm($ctp->loadAllNodePerm($admin_idst));

	$url="index.php?modname=admin_manager&amp;op=assign_cmsdocs&amp;adminidst=".$admin_idst;
	$res.=$form->openForm("main_form", $url);
	$res.=$docs_sel->load();


	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $lang->def('_SAVE'));
	$res.=$form->getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();

	$res.="</div>\n";
	$out->add($res);
}


function adminManager_saveCmsDocs($admin_idst) {

	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");

	$ctp=new CmsTreePermissions("document");
	$ctp->saveNodePerm($admin_idst, $_POST["sel_type"]);

	Util::jump_to("index.php?modname=admin_manager&op=view");
}


function adminManager_assignCmsMedia($admin_idst) {
	checkPerm('view');
	if ($admin_idst < 1)
		return FALSE;

	if (isset($_POST["save"])) {
		adminManager_saveCmsMedia($admin_idst);
		die();
	}

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");
	require_once($GLOBALS["where_cms"]."/admin/modules/media/class.media_selector.php");

	$res="";
	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("admin_manager", "framework");
	$form=new Form();
	$ctp=new CmsTreePermissions("media");

	addCss("base-old-treeview", "cms");

	$res.=getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'));
	$res.="<div class=\"std_block\">\n";

	$tree = new mediaDb();
	$media_sel=new Selector_Media_TreeView($tree, FALSE);
	$media_sel->parsePositionData($_POST, $_POST, $_POST);
	$media_sel->setNodePerm($ctp->loadAllNodePerm($admin_idst));

	$url="index.php?modname=admin_manager&amp;op=assign_cmsmedia&amp;adminidst=".$admin_idst;
	$res.=$form->openForm("main_form", $url);
	$res.=$media_sel->load();


	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $lang->def('_SAVE'));
	$res.=$form->getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();

	$res.="</div>\n";
	$out->add($res);
}


function adminManager_saveCmsMedia($admin_idst) {

	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");

	$ctp=new CmsTreePermissions("media");
	$ctp->saveNodePerm($admin_idst, $_POST["sel_type"]);

	Util::jump_to("index.php?modname=admin_manager&op=view");
}



function adminManager_assignCmsContent($admin_idst) {
	checkPerm('view');
	if ($admin_idst < 1)
		return FALSE;

	if (isset($_POST["save"])) {
		adminManager_saveCmsContent($admin_idst);
		die();
	}

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");
	require_once($GLOBALS["where_cms"]."/admin/modules/content/class.content_selector.php");

	$res="";
	$out=& $GLOBALS["page"];
	$out->setWorkingZone("content");
	$lang=& DoceboLanguage::createInstance("admin_manager", "framework");
	$form=new Form();
	$ctp=new CmsTreePermissions("content");

	addCss("base-old-treeview", "cms");

	$res.=getTitleArea($lang->def('_ADMIN_MANAGMENT'), 'admin_managmer', $lang->def('_ADMIN_MANAGMENT'));
	$res.="<div class=\"std_block\">\n";

	$tree = new contentDb();
	$content_sel=new Selector_Content_TreeView($tree, FALSE);
	$content_sel->parsePositionData($_POST, $_POST, $_POST);
	$content_sel->setNodePerm($ctp->loadAllNodePerm($admin_idst));

	$url="index.php?modname=admin_manager&amp;op=assign_cmscontent&amp;adminidst=".$admin_idst;
	$res.=$form->openForm("main_form", $url);
	$res.=$content_sel->load();


	$res.=$form->openButtonSpace();
	$res.=$form->getButton('save', 'save', $lang->def('_SAVE'));
	$res.=$form->getButton('undo_pref', 'undo_pref', $lang->def('_UNDO'));
	$res.=$form->closeButtonSpace();
	$res.=$form->closeForm();

	$res.="</div>\n";
	$out->add($res);
}


function adminManager_saveCmsContent($admin_idst) {

	require_once($GLOBALS["where_cms"]."/lib/lib.tree_perm.php");

	$ctp=new CmsTreePermissions("content");
	$ctp->saveNodePerm($admin_idst, $_POST["sel_type"]);

	Util::jump_to("index.php?modname=admin_manager&op=view");
}



// ----------------------------------------------------------------------------

function adminManagerDispatch( $op ) {

	if(isset($_POST['undo_pref'])) $op = 'view';
	switch($op) {
		case "view" : {
			adminManager_list();
		};break;
		case "assign_tree": {
			adminManager_assign_tree(ImportVar( 'adminidst', true, 0));
		};break;
		// Extra preferences and settings
		case "edit_preferences" : {
			adminManager_edit_preferences();
		};break;
		// Menu managment
		case "edit_menu" : {
			adminManager_edit_menu();
		};break;
		// Lang managment
		case "edit_lang" : {
			adminManager_lang_course();
		};break;
		// Course managment
		case "edit_course" : {
			adminManager_edit_course();
		};break;
		// Cms page management
		case "assign_cmspag": {
			adminManager_assignCmsPages(ImportVar( 'adminidst', true, 0));
		};break;
		// Cms news tree management
		case "assign_cmsnews": {
			adminManager_assignCmsNews(ImportVar( 'adminidst', true, 0));
		};break;
		// Cms documents tree management
		case "assign_cmsdocs": {
			adminManager_assignCmsDocs(ImportVar( 'adminidst', true, 0));
		};break;
		// Cms media tree management
		case "assign_cmsmedia": {
			adminManager_assignCmsMedia(ImportVar( 'adminidst', true, 0));
		};break;
		// Cms media tree management
		case "assign_cmscontent": {
			adminManager_assignCmsContent(ImportVar( 'adminidst', true, 0));
		};break;
	}
}

?>
