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
 * @version  $Id: coursepath.php 767 2006-10-31 10:09:25Z fabio $
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

if(!Docebo::user()->isAnonymous()) {

function pathlist() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.table.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');
	$acl_man 	=& Docebo::user()->getAclManager();

	$subscribe_perm = checkPerm('subscribe', true);
	$mod_perm 		= checkPerm('mod', true);
	$del_perm 		= checkPerm('mod', true);

	if(Get::cfg('demo_mode'))
		$del_perm = false;

	$query_pathlist = "
	SELECT id_path, path_code, path_name, path_descr
	FROM ".$GLOBALS['prefix_lms']."_coursepath ";

	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
	{
		require_once(_base_.'/lib/lib.preference.php');
		$adminManager = new AdminPreference();
		$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
		$all_courses = false;
		if(isset($admin_courses['course'][0]))
			$all_courses = true;
		if(isset($admin_courses['course'][-1]))
		{
			$query =	"SELECT id_path"
						." FROM %lms_coursepath_user"
						." WHERE idUser = '".$id_user."'";

			$result = sql_query($query);
			$admin_courses['coursepath'] = array();

			while(list($id_path) = sql_fetch_row($result))
				$admin_courses['coursepath'][$id_path] = $id_path;

			if(!empty($admin_courses['coursepath']) && Get::sett('on_catalogue_empty', 'off') == 'on')
				$all_courses = true;
		}
		else
		{
			if(!empty($admin_courses['catalogue']))
			{
				require_once(_lms_.'/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();
				foreach($admin_courses['catalogue'] as $id_cat)
				{
					$catalogue_coursepath = $cat_man->getCatalogueCoursepath($id_cat, true);
					$admin_courses['coursepath'] = array_merge($admin_courses['coursepath'], $catalogue_coursepath);
				}
			}
		}

		if($all_courses)
			$query_pathlist .= "WHERE 1 ";
		elseif(empty($admin_courses['coursepath']))
			$query_pathlist = "WHERE 0";
		else
			$query_pathlist .= "WHERE id_path IN (".implode(',', $admin_courses['coursepath']).") ";
	}
	$query_pathlist .= " ORDER BY path_name ";
	$re_pathlist = sql_query($query_pathlist);

	// find subscriptions
	$subscriptions = array();
	$query_subcription = "
	SELECT id_path, COUNT(idUser), SUM(waiting)
	FROM ".$GLOBALS['prefix_lms']."_coursepath_user
	GROUP BY id_path";

	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
	{
		require_once(_base_.'/lib/lib.preference.php');
		$adminManager = new AdminPreference();
		$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
		$admin_users = $acl_man->getAllUsersFromIdst($admin_tree);

		if(!empty($admin_users))
			$query_subcription .= " AND idUser IN (".implode(',', $admin_users).")";
		else
			$query_subcription .= " AND idUser = 0";
	}

	$re_subscription = sql_query($query_subcription);
	while(list($id_path, $users, $waitings) = sql_fetch_row($re_subscription)) {
		$subscriptions[$id_path]['users'] = $users - $waitings;
		$subscriptions[$id_path]['waiting'] = $waitings;
	}

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_COURSEPATH'), 'coursepath')
		.'<div class="std_block">');
	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "ok"  : $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));	break;
			case "err" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));		break;
		}
	}
	$tb_path = new Table(0, $lang->def('_COURSE_PATH_CAPTION'), $lang->def('_COURSE_PATH_CAPTION'));

	$cont_h = array($lang->def('_CODE'),
					$lang->def('_NAME'),
					$lang->def('_DESCRIPTION'));
	$type_h = array('course_code', '', '');
   
   // $subscribe_perm = false;
	$cont_h[] = '<img src="'.getPathImage().'standard/moduser.png" alt="'.$lang->def('_SUBSCRIBE').'" />';
	$type_h[] = 'image';

	$cont_h[] = '<img src="'.getPathImage().'standard/modelem.png" alt="'.$lang->def('_COURSES').'" />';
	$type_h[] = 'image';

	if($mod_perm) {
		$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />';
		$type_h[] = 'image';
	}
	if($del_perm) {
		$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />';
		$type_h[] = 'image';
	} 

	$tb_path->setColsStyle($type_h);
	$tb_path->addHead($cont_h);
	while(list($id_path, $path_code, $path_name, $path_descr) = sql_fetch_row($re_pathlist)) {

		$cont = array($path_code, $path_name, $path_descr);
		if($subscribe_perm) {
			$url_subscribe = 'index.php?r=alms/subscription/show_coursepath&id_path='.(int)$id_path;
			$cont[] = '<a href="'.$url_subscribe.'" ' //index.php?modname=coursepath&amp;op=addsubscription&amp;id_path='.$id_path.'&amp;load=1
						.'title="'.$lang->def('_SUBSCRIBE').' : '.$path_name.'">'
					.( isset($subscriptions[$id_path]['users'] ) ? $subscriptions[$id_path]['users'].' ' : '' )
					.'<img src="'.getPathImage().'standard/moduser.png" alt="'.$lang->def('_SUBSCRIBE').' : '.$path_name.'" /></a>';
			$cont[] =	'<a href="index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path.'" '
						.'title="'.$lang->def('_MOD').' : '.$path_name.'">'
						.'<img src="'.getPathImage().'standard/modelem.png" alt="'.$lang->def('_COURSES').' : '.$path_name.'" /></a>';
		}
		else
		{
			$cont[] = ( isset($subscriptions[$id_path]['users'] ) ? $subscriptions[$id_path]['users'].' ' : '' );
			$cont[] =	'<a href="index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path.'" '
						.'title="'.$lang->def('_MOD').' : '.$path_name.'">'
						.'<img src="'.getPathImage().'standard/modelem.png" alt="'.$lang->def('_MOD').' : '.$path_name.'" /></a>';
		}

		if($mod_perm) {
			$cont[] = '<a href="index.php?modname=coursepath&amp;op=modcoursepath&amp;id_path='.$id_path.'" '
						.'title="'.$lang->def('_MOD').' : '.$path_name.'">'
					.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$path_name.'" /></a>';
		}
		if($del_perm) {
			$cont[] = '<a href="index.php?modname=coursepath&amp;op=deletepath&amp;id_path='.$id_path.'" '
						.'title="'.$lang->def('_DEL').' : '.$path_name.'">'
					.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$path_name.'" /></a>';
		}
		$tb_path->addBody($cont);
	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=deletepath]');

	if($mod_perm) {

		$tb_path->addActionAdd(
			'<a href="index.php?modname=coursepath&amp;op=newcoursepath" title="'.$lang->def('_ADD').'">'
			.'<img src="'.getPathimage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
			.$lang->def('_ADD')
			.'</a>');
	}
	$out->add($tb_path->getTable());

	$out->add('</div>');
}


function mancoursepath($load_id = false) {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	if($load_id === false) {

		$path_code 			= '';
		$path_name 			= '';
		$path_descr 		= '';
		$subscribe_method 	= 0;
	} else {

		$query_pathlist = "
		SELECT path_code, path_name, path_descr, subscribe_method
		FROM ".$GLOBALS['prefix_lms']."_coursepath
		WHERE id_path = '".(int)$load_id."'
		ORDER BY path_name";
		list($path_code, $path_name, $path_descr, $subscribe_method) = sql_fetch_row(sql_query($query_pathlist));
	}

	$title_area = array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'));
	if($load_id === false) {

		$title_area[] = $lang->def('_ADD');
	} else {

		$title_area[] = $path_name;
	}
	$out->add(
		getTitleArea($title_area, 'coursepath')
		.'<div class="std_block">'
		.Form::openForm('mancoursepath', 'index.php?modname=coursepath&amp;op=savecoursepath')
		.Form::openElementSpace()
		.( $load_id === false ? '' : Form::getHidden('id_path', 'id_path', $load_id) )
		.Form::getTextfield($lang->def('_CODE'), 'path_code', 'path_code', 255,
			$path_code )
		.Form::getTextfield($lang->def('_NAME'), 'path_name', 'path_name', 255,
			$path_name )
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'path_descr', 'path_descr',
			$path_descr )

		.Form::getOpenCombo($lang->def('_COURSE_PATH_SUBSCRIBE'))
		.Form::getRadio($lang->def('_COURSE_S_GODADMIN'), 'course_subs_godadmin', 'subscribe_method', '0', ($subscribe_method == 0) )
		//.Form::getRadio($lang->def('_COURSE_S_MODERATE'), 'course_subs_moderate', 'subscribe_method', '1', ($subscribe_method == 1))
		.Form::getRadio($lang->def('_COURSE_S_FREE'), 'course_subs_free', 'subscribe_method', '2', ($subscribe_method == 2))
		.Form::getCloseCombo()

		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function savecoursepath() {
	checkPerm('mod');

	$re = true;
	if(isset($_POST['id_path'])) {

		// Update existing
		$query_update = "
		UPDATE ".$GLOBALS['prefix_lms']."_coursepath
		SET path_code = '".$_POST['path_code']."',
			path_name = '".$_POST['path_name']."',
			path_descr = '".$_POST['path_descr']."',
			subscribe_method = '".$_POST['subscribe_method']."'
		WHERE id_path = '".(int)$_POST['id_path']."'";
		$re = sql_query($query_update);
	} else {
		// Create new
		$query_insert = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_coursepath
		( path_code, path_name, path_descr, subscribe_method ) VALUES
		( '".$_POST['path_code']."',
		  '".( $_POST['path_name'] != '' ? $_POST['path_name'] : Lang::t('_EMPTY_NAME', 'coursegpath') )."',
		  '".$_POST['path_descr']."',
		  '".$_POST['subscribe_method']."' )";
		$re = sql_query($query_insert);
		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

			list($id_path) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$adminManager->addAdminCoursepath($id_path, Docebo::user()->getIdSt());
		}
	}
	Util::jump_to('index.php?modname=coursepath&op=pathlist&result='.( $re ? 'ok' : 'err' ));
}

function deletepath() {
	checkPerm('mod');

	if(Get::cfg('demo_mode'))
		die('Cannot delete coursepath during demo mode.');

	$id_path = importVar('id_path', true, 0);

	if(Get::req('confirm', DOTY_INT, 0) == 1) {

		$re = true;

		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_coursepath_courses
		WHERE id_path = '".$id_path."'"))
			Util::jump_to('index.php?modname=coursepath&op=pathlist&result=err' );

		// Update existing
		$query_delete = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_coursepath
		WHERE id_path = '".(int)$id_path."'";
		$re = sql_query($query_delete);
		Util::jump_to('index.php?modname=coursepath&op=pathlist&result='.( $re ? 'ok' : 'err' ));
	} else {
		require_once(_base_.'/lib/lib.form.php');

		$query_pathlist = "
		SELECT path_name, path_descr
		FROM ".$GLOBALS['prefix_lms']."_coursepath
		WHERE id_path = '".(int)$id_path."'
		ORDER BY path_name";
		list($path_name, $path_descr) = sql_fetch_row(sql_query($query_pathlist));

		$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

		$GLOBALS['page']->add(
			getTitleArea($lang->def('_COURSEPATH'), 'coursepath')
			.'<div class="std_block">'
			.Form::openForm('deletepath', 'index.php?modname=coursepath&amp;op=deletepath')
			.Form::getHidden('id_path', 'id_path', $id_path)
			.getDeleteUi(
				$lang->def('_AREYOUSURE'),
				'<span class="text_bold">'.$lang->def('_NAME').' : </span>'.$path_name.'<br />'
				.'<span class="text_bold">'.$lang->def('_DESCRIPTION').' : </span>'.$path_descr,
				false,
				'confirm',
				'undo')
			.Form::closeForm()
			.'</div>', 'content');
	}
}

function coursePathSubstPrer($id_string, $names) {

	$prereq = '';
	if($id_string == '') return $prereq;
	$all_id = explode(',', $id_string);
	$i = 0;
	while(list(, $id) = each($all_id)) {

		$i++;
		$prereq .= $names[$id]['name'].', ';
	}
	return '( '.$i.' ) '.substr($prereq, 0, -2);
}

function pathelem() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	$id_path = importVar('id_path', true, 0);
	$mod_perm = checkPerm('mod', true);

	$path_man 	= new CoursePath_Manager();
	$course_man = new Man_Course();

	$path = $path_man->getCoursepathInfo($id_path);

	// retriving id of the courses in this path
	$slots 		= $path_man->getPathSlot($id_path);
	$courses 	= $path_man->getPathElem($id_path);

	// retrive all i need about courses name
	if(isset($courses['course_list'])) $course_info 	= $course_man->getAllCourses(false, 'all', $courses['course_list']);
	else $course_info = array();

	$area_title = array('index.php?modname=coursepath&amp;op=pathlist'=> $lang->def('_COURSEPATH'),
		$path['path_name']);

	$GLOBALS['page']->add(getTitleArea($area_title, 'coursepath')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=coursepath&amp;op=pathlist', $lang->def('_BACK'))
	,'content');
	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "ok"  : $GLOBALS['page']->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')), 'content');	break;
			case "err" : $GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');			break;
		}
	}

	$tb_path = new Table(0, $lang->def('_COURSE_PATH_COURSES_CAPTION'), $lang->def('_COURSE_PATH_COURSES_CAPTION'));

	$cont_h = array($lang->def('_CODE'), $lang->def('_COURSE_NAME'), $lang->def('_PREREQUISITES'));
	$type_h = array('coursepath_code', 'coursepath_name', '', 'image');
	if($mod_perm) {
		$cont_h[] = Get::img('standard/down.png', Lang::t('_DOWN', 'coursepath'));
		$type_h[] = 'image';
		$cont_h[] = Get::img('standard/up.png', Lang::t('_UP', 'coursepath'));
		$type_h[] = 'image';
		$cont_h[] = Get::img('standard/moderate.png', Lang::t('_MOD', 'coursepath'));
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />';
		$type_h[] = 'image';
	}
	$tb_path->setColsStyle($type_h);
	$tb_path->addHead($cont_h);

	$slot_number = 0;
	foreach($slots as $id_slot => $slot_info) {


		$tb_path->emptyBody();
		$tb_path->emptyFoot();

		$i = 0;
		if(!isset($courses[$id_slot])) $num_course = 0;
		else {

			$num_course = count($courses[$id_slot]);

			$all_courses = true;

			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
			{
				$all_courses = false;

				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
				$all_course = false;
				if(isset($admin_courses['course'][0]))
					$all_course = true;
				elseif(isset($admin_courses['course'][-1]))
				{
					require_once(_lms_.'/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();

					$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
					if(count($user_catalogue) > 0)
					{
						$courses = array(0);

						foreach($user_catalogue as $id_cat)
						{
							$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

							$courses = array_merge($courses, $catalogue_course);
						}

						foreach($courses as $id_course)
							if($id_course != 0)
								$admin_courses['course'][$id_course] = $id_course;
					}
					elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
						$all_courses = true;
				}
				else
				{
					$array_courses = array();
					$array_courses = array_merge($array_courses, $admin_courses['course']);

					if(!empty($admin_courses['coursepath']))
					{
						require_once(_lms_.'/lib/lib.coursepath.php');
						$path_man = new CoursePath_Manager();
						$coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
						$array_courses = array_merge($array_courses, $coursepath_course);
					}
					if(!empty($admin_courses['catalogue']))
					{
						require_once(_lms_.'/lib/lib.catalogue.php');
						$cat_man = new Catalogue_Manager();
						foreach($admin_courses['catalogue'] as $id_cat)
						{
							$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
							$array_courses = array_merge($array_courses, $catalogue_course);
						}
					}
					$admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);
				}
			}

			while(list($id_item, $prerequisites) = each($courses[$id_slot])) {

				$cont = array(	$course_info[$id_item]['code'],
								$course_info[$id_item]['name'] );
				if($prerequisites != '') $cont[] = coursePathSubstPrer($prerequisites, $course_info );
				else $cont[] = '';
				if($mod_perm) {
					if(in_array($id_item, $admin_courses['course']) || $all_courses)
					{
						if($i != $num_course - 1) {
							$cont[] = '<a href="index.php?modname=coursepath&amp;op=downelem&amp;id_path='.$id_path.'&amp;id_course='.$id_item.'&amp;id_slot='.$id_slot.'" '
										.'title="'.$lang->def('_MOVE_DOWN').' : '.$course_info[$id_item]['name'].'">'
									.Get::img('standard/down.png', Lang::t('_DOWN', 'coursepath')).'</a>';
						} else $cont[] = '';

						if($i != 0) {
							$cont[] = '<a href="index.php?modname=coursepath&amp;op=upelem&amp;id_path='.$id_path.'&amp;id_course='.$id_item.'&amp;id_slot='.$id_slot.'" '
										.'title="'.$lang->def('_MOVE_UP').' : '.$course_info[$id_item]['name'].'">'
									.Get::img('standard/up.png', Lang::t('_UP', 'coursepath')).'</a>';
						} else $cont[] = '';

						$cont[] = '<a href="index.php?modname=coursepath&amp;op=modprerequisites&amp;id_path='.$id_path.'&amp;id_course='.$id_item.'&amp;id_slot='.$id_slot.'" '
									.'title="'.$lang->def('_MOD').' : '.$course_info[$id_item]['name'].'">'
								.Get::img('standard/moderate.png', Lang::t('_MOD', 'coursepath').' : '.$course_info[$id_item]['name']).'</a>';

						$cont[] = '<a href="index.php?modname=coursepath&amp;op=delcoursepath&amp;id_path='.$id_path.'&amp;id_course='.$id_item.'&amp;id_slot='.$id_slot.'" '
									.'title="'.$lang->def('_DEL').' : '.$course_info[$id_item]['name'].'">'
								.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$course_info[$id_item]['name'].'" /></a>';
					}
					else
					{
						$cont[] = '';
						$cont[] = '';
						$cont[] = '';
						$cont[] = '';
					}
				}
				$tb_path->addBody($cont);
				$i++;
			}
		}
		// add link
		if($mod_perm) {

			$tb_path->addActionAdd(
				'<a class="ico-wt-sprite subs_import" href="index.php?modname=coursepath&amp;op=importcourse&amp;load=1&amp;id_path='.$id_path.'&amp;id_slot='.$id_slot.'" '
					.'title="'.$lang->def('_IMPORT_COURSE').'">'
				.'<span>'.$lang->def('_IMPORT_COURSE').'</span>'
				.'</a>');
		}
		$GLOBALS['page']->add($tb_path->getTable().'<br />','content');
		$slot_number++;
	}
	/*
	$GLOBALS['page']->add(
		'<a href="index.php?modname=coursepath&amp;op=modslot&amp;id_path='.$id_path.'" '
			.'title="'.$lang->def('_NEW_SLOT_TITLE').'">'
			.'<img src="'.getPathimage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
			.$lang->def('_NEW_SLOT')
		.'</a>'
	,'content');
	*/
	$GLOBALS['page']->add(getBackUi('index.php?modname=coursepath&amp;op=pathlist', $lang->def('_BACK') )
		.'</div>'
	,'content');
}

function downelem() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$id_path = importVar('id_path', true, 0);
	$id_slot = importVar('id_slot', true, 0);
	$id_course = importVar('id_course', true, 0);

	$path_man 	= new CoursePath_Manager();
	$path_man->moveDown($id_path, $id_slot, $id_course);
	Util::jump_to('index.php?modname=coursepath&op=pathelem&id_path='.$id_path);
}

function upelem() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$id_path = importVar('id_path', true, 0);
	$id_slot = importVar('id_slot', true, 0);
	$id_course = importVar('id_course', true, 0);

	$path_man 	= new CoursePath_Manager();
	$path_man->moveUp($id_path, $id_slot, $id_course);
	Util::jump_to('index.php?modname=coursepath&op=pathelem&id_path='.$id_path);
}

function importcourse() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	$id_path = importVar('id_path', true, 0);
	$id_slot = importVar('id_slot', true, 0);

	$selector = new Selector_Course();
	$selector->parseForState($_POST);

	$path_man 	= new CoursePath_Manager();

	if(isset($_GET['load'])) {

		$initial_selection = $path_man->getSlotElem($id_path, $id_slot);

		if(isset($_GET['load'])) $selector->resetSelection($initial_selection);
	}
	if(isset($_POST['import'])) {

		$initial_selection 	= $path_man->getSlotElem($id_path, $id_slot);
		$selected_courses 	= $selector->getSelection();

		$to_add = array_diff($selected_courses, $initial_selection);
		$to_del = array_diff($initial_selection, $selected_courses);

		$re = true;
		$added_courses = array();
		$removed_courses = array();
		while(list(,$id_c) = each($to_add)) {

			$re_s = $path_man->addToSlot($id_path, $id_slot, $id_c);
			if($re_s) $added_courses[] = $id_c;
			$re &= $re_s;
		}
		while(list(,$id_c) = each($to_del)) {

			$re_s = $path_man->delFromSlot($id_path, $id_slot, $id_c);
			if($re_s) $removed_courses[] = $id_c;
			$re &= $re_s;
		}
		// update users course subscription
		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');

		$cpath_man 	= new CoursePath_Manager();
		$subs_man 	= new CourseSubscribe_Management();
		$users 		= $cpath_man->getSubscribed($id_path);

		if(!empty($added_courses) && !empty($users))
			$re &= $subs_man->multipleSubscribe($users , $added_courses, 3);

		if(!$re) die('<a href="index.php?modname=coursepath&op=pathelem&id_path='.$id_path.'">waths happen in insert ???</a>');
		if(!empty($removed_courses) && !empty($users))
			$re &= $subs_man->multipleUnsubscribe($users , $removed_courses);

		$cpath_man->fixSequence($id_path, $id_slot);
		Util::jump_to('index.php?modname=coursepath&op=pathelem&id_path='.$id_path.'&result='.( $re ? 'ok' : 'err' ));
	}

	$query_pathlist = "
	SELECT path_name, path_descr
	FROM ".$GLOBALS['prefix_lms']."_coursepath
	WHERE id_path = '".(int)$id_path."'
	ORDER BY path_name";
	list($path_name, $path_descr) = sql_fetch_row(sql_query($query_pathlist));

	$page_title = array(
		'index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'),
		'index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path => $path_name,
		$lang->def('_IMPORT_COURSE')
	);
	$out->add(
		getTitleArea($page_title, 'coursepath')
		.'<div class="std_block">'
		.Form::openForm('mancoursepath', 'index.php?modname=coursepath&amp;op=importcourse')
		.Form::getHidden('id_path', 'id_path', $id_path)
		.Form::getHidden('id_slot', 'id_slot', $id_slot)
		, 'content'
	);
	$selector->loadCourseSelector(false, true);
	$out->add(
		Form::openButtonSpace()
		.Form::getBreakRow()
		.Form::getButton('import', 'import', $lang->def('_IMPORT'))
		.Form::getButton('undoelem', 'undoelem', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function modprerequisites() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	$id_path 	= importVar('id_path', true, 0);
	$id_course 	= importVar('id_course', true, 0);

	$mod_perm = checkPerm('mod', true);

	$query_pathlist = "
	SELECT path_name
	FROM ".$GLOBALS['prefix_lms']."_coursepath
	WHERE id_path = '".$id_path."'";
	list($path_name) = sql_fetch_row(sql_query($query_pathlist));

	$query_pathelem = "
	SELECT id_item, prerequisites
	FROM ".$GLOBALS['prefix_lms']."_coursepath_courses
	WHERE id_path = '".$id_path."'";
	$repath_elem = sql_query($query_pathelem);
	while(list($id_c, $prer) = sql_fetch_row($repath_elem)) {

		$courses_in_path[$id_c] = $id_c;
		$courses_prer[$id_c] 	= $prer;
	}
	$course_man = new Man_Course();
	$course_info =& $course_man->getAllCourses(false, 'all', $courses_in_path);

	// prerequisites of this course
	$this_course_prer = array_flip(explode(',', $courses_prer[$id_course]));

	$area_title = array(
		'index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'),
		'index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path => $path_name,
		$course_info[$id_course]['name'].' - '.$lang->def('_PREREQUISITES')
	);
	$out->setWorkingZone('content');
	$out->add(getTitleArea($area_title, 'coursepath')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path, $lang->def('_BACK') )


		.Form::openForm('prerequisites', 'index.php?modname=coursepath&amp;op=writeprerequisites')
		.Form::getHidden('id_path', 'id_path', $id_path)
		.Form::getHidden('id_course', 'id_course', $id_course) );

	$tb_path_ass = new Table(0, $lang->def('_PREREQUISITES'), $lang->def('_PREREQUISITES'));

	$cont_h = array(
		$lang->def('_PREREQUISITES'),
		$lang->def('_CODE'),
		$lang->def('_COURSE_NAME')
	);
	$type_h = array('image', '', '');

	$tb_path_ass->setColsStyle($type_h);
	$tb_path_ass->addHead($cont_h);

	while(list($id_c, $course) = each($course_info)) {

		if($id_c != $id_course) {

			if(isset($courses_prer[$id_c]) && strpos($courses_prer[$id_c], $id_course)) {

				// this course contain the current working course as a  prerequisites
				$cont = array(
					'<img src="'.getPathImage('lms').'course/locked.gif" alt="'.$lang->def('_LOCKED').'" />',
					$course['code'],
					$course['name']);
			} else {

				$cont = array(
					Form::getInputCheckbox(	'prerequisites_'.$id_c,
											'prerequisites['.$id_c.']',
											$id_c,
											isset($this_course_prer[$id_c]),
											'' ),
					'<label for="prerequisites_'.$id_c.'">'.$course['code'].'</label>',
					'<label for="prerequisites_'.$id_c.'">'.$course['name'].'</label>');
			}

			$tb_path_ass->addBody($cont);
		}
	}
	$out->add(
		$tb_path_ass->getTable()
		.Form::openButtonSpace()
		.Form::getButton('accept', 'accept', $lang->def('_SAVE'))
		.Form::getButton('undoelem', 'undoelem', $lang->def('_UNDO'))
		.Form::closeForm()
	);

	$out->add(getBackUi('index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path, $lang->def('_BACK') )
		.'</div>');
}

function writeprerequisites() {
	checkPerm('mod');

	$id_course = importVar('id_course', true, 0);
	$id_path = importVar('id_path', true, 0);

	$new_prerequisites = '';
	$new_prerequisites = implode(',', $_POST['prerequisites']);

	$re = sql_query("
	UPDATE ".$GLOBALS['prefix_lms']."_coursepath_courses
	SET prerequisites = '".$new_prerequisites."'
	WHERE id_path = '".$id_path."' AND id_item = '".$id_course."'");

	Util::jump_to('index.php?modname=coursepath&op=pathelem&amp;id_path='.$id_path.'&amp;result='.( $re ? 'ok' : 'err' ) );
}

function delcoursepathelem() {
	checkPerm('mod');

	$id_course 	= importVar('id_course', true, 0);
	$id_path 	= importVar('id_path', true, 0);
	$id_slot 	= importVar('id_slot', true, 0);

	if(isset($_POST['confirm'])) {

		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		$cpath_man 	= new CoursePath_Manager();

		$re = $cpath_man->delFromSlot($id_path, $id_slot, $id_course);
		if($re) {
			// update users course subscription
			require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');

			$subs_man 	= new CourseSubscribe_Management();

			$users 		= $cpath_man->getSubscribed($id_path);
			if(!empty($users)) $re &= $subs_man->unsubscribeUsers($users , $id_course);
		}
		Util::jump_to('index.php?modname=coursepath&op=pathelem&amp;id_path='.$id_path.'&amp;result='.( $re ? 'ok' : 'err' ) );
	} else {
		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		$arr_course = array($id_course => $id_course);
		$course_info =& getCoursesInfo($arr_course);
		$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

		$query_pathlist = "
		SELECT path_name
		FROM ".$GLOBALS['prefix_lms']."_coursepath
		WHERE id_path = '".(int)$id_path."'
		ORDER BY path_name";
		list($path_name) = sql_fetch_row(sql_query($query_pathlist));

		$title_area = array(
			'index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'),
			'index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path => $path_name,
			$lang->def('_DEL')
		);

		$GLOBALS['page']->add(
			getTitleArea($title_area, 'coursepath')
			.'<div class="std_block">'
			.Form::openForm('deletepath', 'index.php?modname=coursepath&amp;op=delcoursepath')
			.Form::getHidden('id_path', 'id_path', $id_path)
			.Form::getHidden('id_course', 'id_course', $id_course)
			.Form::getHidden('id_slot', 'id_slot', $id_slot)
			.getDeleteUi(
				$lang->def('_AREE_YOU_SURE_TO_REMOVE_COURSE_FROM_PATH'),
				'<span class="text_bold">'.$lang->def('_COURSE_NAME').' : </span>'.$course_info[$id_course]['name'].'<br />'
				.'<span class="text_bold">'.$lang->def('_DESCRIPTION').' : </span>'.$course_info[$id_course]['description'],
				false,
				'confirm',
				'undoelem')
			.Form::closeForm()
			.'</div>', 'content');
	}
}

//-----------------------------------------------------------------

function waitingsubscription() {
	checkPerm('moderate');

	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');

	$id_path 	= importVar('id_path', true, 0);
	$lang		=& DoceboLanguage::createInstance('coursepath', 'lms');
	$acl_man 	=& Docebo::user()->getAclManager();

	if(isset($_POST['accept'])) {

		$cpath_man = new CoursePath_Manager();
		$courses = $cpath_man->getAllCourses(array($id_path));

		$subs_man = new CourseSubscribe_Management();

		$re = true;
		if(isset($_POST['approve_user'])) {

			$users_subsc = array();
			while(list($id_user) = each($_POST['approve_user'])) {

				$text_query = "
				UPDATE ".$GLOBALS['prefix_lms']."_coursepath_user
				SET waiting = 0
				WHERE id_path = '".$id_path."' AND idUser = '".$id_user."'";
				$re_s = sql_query($text_query);
				if($re_s == true) $users_subsc[] = $id_user;
				$re &= $re_s;
			}
			// now subscribe user to all the course
			if(!empty($users_subsc)) $re &= $subs_man->multipleSubscribe($users_subsc, $courses, 3);

		}
		if(isset($_POST['deny_user'])) {

			while(list($id_user) = each($_POST['deny_user'])) {

				$text_query = "
				DELETE FROM ".$GLOBALS['prefix_lms']."_coursepath_user
				WHERE id_path = '".$id_path."' AND idUser = '".$id_user."'";
				$re &= sql_query($text_query);
			}
		}
		Util::jump_to('index.php?modname=coursepath&amp;op=pathlist&result='.( $re ? 'ok' : 'err' ));
	}


	$subscriptions = array();
	$query_subcription = "
	SELECT idUser, subscribed_by
	FROM ".$GLOBALS['prefix_lms']."_coursepath_user
	WHERE id_path = '".$id_path."' AND waiting = '1'";
	$re_subscription = sql_query($query_subcription);
	while(list($id_user, $subscribed_by) = sql_fetch_row($re_subscription)) {

		$subs_by[$id_user] = $subscribed_by;
		$users[$id_user] = $id_user;
		$users[$subscribed_by] = $subscribed_by;
	}
	if(!empty($users)) $users_waiting = $acl_man->getUsers($users);

	$tb = new Table(0, $lang->def('_WAITING_USERS'), $lang->def('_WAITING_USERS'));

	$type_h = array('', '', '', 'image', 'image');
	$cont_h = array($lang->def('_USERNAME'), $lang->def('_FULLNAME'),
		$lang->def('_SUBSCRIBED_BY'),
		$lang->def('_APPROVE'),
		$lang->def('_DENY')
	);
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	if(!empty($users))
	while(list($id_user, $user_info) = each($users_waiting)) {

		$cont = array( $acl_man->relativeId($user_info[ACL_INFO_USERID]),
						$user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME],
						$acl_man->getConvertedUserName($users_waiting[$subs_by[$id_user]]) );

		$cont[] = Form::getInputCheckbox(
				'approve_user_'.$id_user,
				'approve_user['.$id_user.']',
				$id_user,
				false,
				'' ).'<label class="access-only" for="approve_user_'.$id_user.'">'.$user_info[ACL_INFO_USERID].'</label>';

		$cont[] = Form::getInputCheckbox(
				'deny_user_'.$id_user,
				'deny_user['.$id_user.']',
				$id_user,
				false,
				'' ).'<label class="access-only" for="deny_user_'.$id_user.'">'.$user_info[ACL_INFO_USERID].'</label>';

		$tb->addBody($cont);
	}

	$query_pathlist = "
	SELECT path_name
	FROM ".$GLOBALS['prefix_lms']."_coursepath
	WHERE id_path = '".$id_path."'
	ORDER BY path_name ";
	list($path_name) = sql_fetch_row(sql_query($query_pathlist));

	$GLOBALS['page']->add(
		getTitleArea( array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'), $path_name)
			, 'coursepath')
		.'<div class="std_block">'
		.Form::openForm('deletepath', 'index.php?modname=coursepath&amp;op=waitingsubscription')
		.Form::getHidden('id_path', 'id_path', $id_path)
		.$tb->getTable()
		.Form::openButtonSpace()
		.Form::getButton('accept', 'accept', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function addsubscription() {
	checkPerm('subscribe');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_adm_.'/class.module/class.directory.php');
	require_once(_lms_.'/lib/lib.subscribe.php');
	require_once(_lms_.'/lib/lib.coursepath.php');

	$id_path = importVar('id_path', true, 0);
	$lang =& DoceboLanguage::createInstance('coursepath', 'lms');
	$out =& $GLOBALS['page'];
	$acl_man =& Docebo::user()->getAclManager();

	if(isset($_POST['cancelselector'])) Util::jump_to('index.php?modname=coursepath&amp;op=pathlist');

	$user_select = new UserSelector();

	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = TRUE;
	$user_select->show_orgchart_simple_selector = TRUE;

	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
	{
		require_once(_base_.'/lib/lib.preference.php');
		$adminManager = new AdminPreference();
		$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
		$admin_users = $acl_man->getAllUsersFromIdst($admin_tree);

		$user_select->setUserFilter('user', $admin_users);
		$user_select->setUserFilter('group', $admin_tree);
	}

	$query_pathlist = "
	SELECT path_name, subscribe_method
	FROM ".$GLOBALS['prefix_lms']."_coursepath
	WHERE id_path = '".$id_path."'
	ORDER BY path_name ";
	list($path_name, $subscribe_method) = sql_fetch_row(sql_query($query_pathlist));


	if(isset($_GET['load'])) {

		$cp_man = new CoursePath_Manager();
		$users = $cp_man->getSubscribed($id_path);

		$user_select->resetSelection($users);
	}
	if(isset($_POST['okselector']))
	{
		$acl_manager = new DoceboACLManager();

		$user_selected 	= $user_select->getSelection($_POST);

		$user_selected =& $acl_manager->getAllUsersFromIdst($user_selected);

		$user_selected = array_unique($user_selected);

		$cp_man = new CoursePath_Manager();
		$users = $cp_man->getSubscribed($id_path);

		$user_selected = array_diff($user_selected, $users);

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
		{
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
			$admin_users = $acl_man->getAllUsersFromIdst($admin_tree);

			$user_selected = array_intersect($user_selected, $admin_users);
		}

		if(empty($user_selected )) Util::jump_to('index.php?modname=coursepath&amp;op=pathlist');

		$cpath_man = new CoursePath_Manager();
		$subs_man = new CourseSubscribe_Management();

		$courses = $cpath_man->getAllCourses(array($id_path));

		require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
		$course_man = new Man_Course();
		$classroom = $course_man->getAllCourses(false, 'classroom', $courses);
		$edition = $course_man->getAllCourses(false, 'edition', $courses);

		if(!empty($classroom) || !empty($edition))
		{
			$user_selected_post = urlencode(Util::serialize($user_selected));

			cout(	getTitleArea( array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'), $path_name), 'coursepath')
					.'<div class="std_block">'
					.Form::openForm('edition_selection_form', 'index.php?modname=coursepath&amp;op=addsubscriptionedition&amp;id_path='.$id_path)
					.Form::getHidden('users', 'users', $user_selected_post));

			if(!empty($classroom))
			{
				require_once(_lms_.'/lib/lib.date.php');
				$date_man = new DateManager();

				foreach($classroom as $id_course => $info)
				{
					$editions = $date_man->getCourseDate($id_course, true);

					$edition_for_dropdown = array();
					$edition_for_dropdown[0] = Lang::t('_NONE', 'coursepath');

					foreach($editions as $editions_info)
						$edition_for_dropdown[$editions_info['id_date']] = $editions_info['code'].' - '.$editions_info['name'].' - '.Format::date($editions_info['date_begin'], 'date').' - '.Format::date($editions_info['date_end'], 'date');

					cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'classroom_'.$id_course, 'classroom_'.$id_course, $edition_for_dropdown));
				}
			}

			if(!empty($edition))
			{
				require_once(_lms_.'/lib/lib.edition.php');
				$edition_man = new EditionManager();

				foreach($edition as $id_course => $info)
				{
					$editions = $edition_man->getEditionsInfoByCourses($id_course);

					$edition_for_dropdown = array();
					$edition_for_dropdown[0] = Lang::t('_NONE', 'coursepath');

					foreach($editions[$id_course] as $editions_info)
						$edition_for_dropdown[$editions_info['id_edition']] = $editions_info['code'].' - '.$editions_info['name'].' - '.Format::date($editions_info['date_begin'], 'date').' - '.Format::date($editions_info['date_end'], 'date');

					cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'edition_'.$id_course, 'edition_'.$id_course, $edition_for_dropdown));
				}
			}

			cout(	Form::openButtonSpace()
					.Form::getButton('save', 'save',Lang::t('_SAVE', 'coursepath'))
					.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'coursepath'))
					.Form::closeButtonSpace()
					.Form::closeForm()
					.'</div>');
		}
		else
		{
			$re = true;

			if($subscribe_method != 1 && !checkPerm('moderate', true)) $waiting = 1;
			else $waiting = 0;
			$users_subsc =array();

			require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
			$course_man = new Man_Course();
			$assessment = $course_man->getAllCourses(false, 'assessment', $courses);

			while(list(,$id_user) = each($user_selected)) {

				$text_query = "
				INSERT INTO ".$GLOBALS['prefix_lms']."_coursepath_user
				( id_path, idUser, waiting, subscribed_by ) VALUES
				( '".$id_path."', '".$id_user."', '".$waiting."', '".getLogUserId()."' )";
				$re_s = sql_query($text_query);
				if($re_s == true) $users_subsc[] = $id_user;
				$re &= $re_s;

				foreach($assessment as $id_assessment => $assessment_info)
					sql_query("INSERT INTO %lms_assessment_user (id_assessment, id_user, type_of) VALUES ('".$id_assessment."', '".$id_user."', 'user')");
			}
			// now subscribe user to all the course
			if($waiting == 0) $re &= $subs_man->multipleSubscribe($users_subsc, $courses, 3);

			Util::jump_to('index.php?modname=coursepath&amp;op=pathlist&result='.( $re ? 'ok' : 'err' ));
		}
	}
	else
	{
		$user_select->setPageTitle(getTitleArea( array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'), $path_name)
				, 'coursepath'));
		$user_select->loadSelector('index.php?modname=coursepath&amp;op=addsubscription&amp;id_path='.$id_path,
				$lang->def('_SUBSCRIBE'),
				false,
				true);
	}
}

function addsubscriptionedition()
{
	require_once(_lms_.'/lib/lib.subscribe.php');
	require_once(_lms_.'/lib/lib.coursepath.php');

	$cpath_man = new CoursePath_Manager();
	$subs_man = new CourseSubscribe_Management();

	$id_path = Get::req('id_path', DOTY_INT, 0);
	$user_selected = Util::unserialize(urldecode(Get::req('users', DOTY_MIXED, array())));

	$courses = $cpath_man->getAllCourses(array($id_path));

	if(isset($_POST['undo']) || !isset($_POST['save']))
		Util::jump_to('index.php?modname=coursepath&amp;op=addsubscription&amp;id_path='.$id_path);

	$re = true;

	$query_pathlist = "
	SELECT path_name, subscribe_method
	FROM ".$GLOBALS['prefix_lms']."_coursepath
	WHERE id_path = '".$id_path."'
	ORDER BY path_name ";
	list($path_name, $subscribe_method) = sql_fetch_row(sql_query($query_pathlist));

	if($subscribe_method != 1 && !checkPerm('moderate', true)) $waiting = 1;
	else $waiting = 0;
	$users_subsc =array();

	$course_man = new Man_Course();
	$assessment = $course_man->getAllCourses(false, 'assessment', $courses);
	$classroom = $course_man->getAllCourses(false, 'classroom', $courses);
	$edition = $course_man->getAllCourses(false, 'edition', $courses);

	$array_id_date = array();
	$array_id_edition = array();

	if(!empty($classroom))
		foreach($classroom as $id_course => $info)
			if(Get::req('classroom_'.$id_course, DOTY_INT, 0) != 0)
				$array_id_date[Get::req('classroom_'.$id_course, DOTY_INT, 0)] = Get::req('classroom_'.$id_course, DOTY_INT, 0);

	if(!empty($edition))
		foreach($edition as $id_course => $info)
			if(Get::req('edition_'.$id_course, DOTY_INT, 0) != 0)
				$array_id_edition[Get::req('edition_'.$id_course, DOTY_INT, 0)] = Get::req('edition_'.$id_course, DOTY_INT, 0);

	require_once(_lms_.'/lib/lib.date.php');
	$date_man = new DateManager();

	require_once(_lms_.'/lib/lib.edition.php');
	$edition_man = new EditionManager();

	while(list(,$id_user) = each($user_selected)) {

		$text_query = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_coursepath_user
		( id_path, idUser, waiting, subscribed_by ) VALUES
		( '".$id_path."', '".$id_user."', '".$waiting."', '".getLogUserId()."' )";
		$re_s = sql_query($text_query);
		if($re_s == true) $users_subsc[] = $id_user;
		$re &= $re_s;

		if(!empty($assessment))
		{
			foreach($assessment as $id_assessment => $assessment_info)
				sql_query("INSERT INTO %lms_assessment_user (id_assessment, id_user, type_of) VALUES ('".$id_assessment."', '".$id_user."', 'user')");

			reset($assessment);
		}

		if(!empty($array_id_date))
		{
			foreach($array_id_date as $id_date)
				$date_man->addUserToDate($id_date, $id_user, Docebo::user()->getIdSt());

			reset($array_id_date);
		}

		if(!empty($array_id_edition))
		{
			foreach($array_id_edition as $id_edition)
				$edition_man->addUserToEdition($id_edition, $id_user, Docebo::user()->getIdSt());

			reset($array_id_edition);
		}
	}
	// now subscribe user to all the course
	if($waiting == 0) $re &= $subs_man->multipleSubscribe($users_subsc, $courses, 3);

	Util::jump_to('index.php?modname=coursepath&amp;op=pathlist&result='.( $re ? 'ok' : 'err' ));
}

function modslot() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	$id_slot = importVar('id_slot');
	$id_path = importVar('id_path');

	$cpath_man = new CoursePath_Manager();
	$path = $cpath_man->getCoursepathInfo($id_path);

	if(isset($_POST['save'])) {

		if($id_slot == false) {

			$re = $cpath_man->createSlot($id_path, $_POST['min_selection'], $_POST['max_selection']);
		} else {

			$re = $cpath_man->saveSlot($id_slot, $_POST['min_selection'], $_POST['max_selection']);
		}
		Util::jump_to('index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path);
	}

	if($id_slot == false) {

		$min_selection 			= 1;
		$max_selection 			= 1;
	} else {

		$slot = $cpath_man->getSlotInfo($id_slot);

		$min_selection = $slot[CP_SLOT_MIN];
		$max_selection = $slot[CP_SLOT_MAX];
	}

	$title_area = array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'));
	$title_area['index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path] = $path['path_name'];
	$title_area[] = $lang->def('_MANAGE_SLOT');
	$out->add(
		getTitleArea($title_area, 'coursepath')
		.'<div class="std_block">'
		.Form::openForm('mancoursepath', 'index.php?modname=coursepath&amp;op=modslot')
		.Form::openElementSpace()
		.Form::getHidden('id_path', 'id_path', $id_path)
		.Form::getHidden('id_slot', 'id_slot', $id_slot)
		.Form::getTextfield($lang->def('_MIN_SELECTION'), 'min_selection', 'min_selection', 3,
			$min_selection )
		.Form::getTextfield($lang->def('_MAX_SELECTION'), 'max_selection', 'max_selection', 3,
			$max_selection )

		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function delslot() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');

	$out 	=& $GLOBALS['page'];
	$lang	=& DoceboLanguage::createInstance('coursepath', 'lms');

	$id_slot = importVar('id_slot');
	$id_path = importVar('id_path');

	$cpath_man = new CoursePath_Manager();
	$path = $cpath_man->getCoursepathInfo($id_path);

	if(isset($_POST['confirm'])) {

		if($id_slot != false) {

			$re = $cpath_man->deleteSlot($id_slot, $id_path);
		}
		Util::jump_to('index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path);
	}

	$slot = $cpath_man->getSlotInfo($id_slot);

	$title_area = array('index.php?modname=coursepath&amp;op=pathlist' => $lang->def('_COURSEPATH'));
	$title_area['index.php?modname=coursepath&amp;op=pathelem&amp;id_path='.$id_path] = $path['path_name'];
	$title_area[] = $lang->def('_DEL_SLOT');

	$GLOBALS['page']->add(
			getTitleArea($title_area, 'coursepath')
			.'<div class="std_block">'
			.Form::openForm('deletepath', 'index.php?modname=coursepath&amp;op=delslot')
			.Form::getHidden('id_path', 'id_path', $id_path)
			.Form::getHidden('id_slot', 'id_slot', $id_slot)
			.getDeleteUi(
				$lang->def('_AREE_YOU_SURE_TO_DELETE_SLOT'),
				'<span class="text_bold">'.$lang->def('_MIN_SELECTION').' : </span>'.$slot['min_selection'].'<br />'
				.'<span class="text_bold">'.$lang->def('_MAX_SELECTION').' : </span>'.$slot['max_selection'],
				false,
				'confirm',
				'undo')
			.Form::closeForm()
			.'</div>', 'content');
}

//-----------------------------------------------------------------

function coursepathDispatch($op) {

	if(isset($_POST['undo'])) $op = 'pathlist';
	if(isset($_POST['undoelem'])) $op = 'pathelem';
	switch($op) {
		case "pathlist" : {
			pathlist();
		};break;

		case "newcoursepath" : {
			mancoursepath(false);
		};break;
		case "modcoursepath" : {
			mancoursepath(importVar('id_path', true, 0));
		};break;
		case "savecoursepath" : {
			savecoursepath();
		};break;

		case "deletepath" : {
			deletepath();
		};break;
		//----------------------
		case "pathelem" : {
			pathelem();
		};break;
		case "upelem" : {
			upelem();
		};break;
		case "downelem" : {
			downelem();
		};break;
		case "importcourse" : {
			importcourse();
		};break;

		case "modprerequisites" : {
			modprerequisites();
		};break;
		case "writeprerequisites" : {
			writeprerequisites();
		};break;

		case "delcoursepath" : {
			delcoursepathelem();
		};break;

		//---------------------
		case "waitingsubscription" : {
			waitingsubscription();
		};break;
		case "addsubscription" : {
			addsubscription();
		};break;
		case "addsubscriptionedition" : {
			addsubscriptionedition();
		};break;
		case "modslot" : {
			modslot();
		};break;
		case "delslot" : {
			delslot();
		};break;
	}
}

}

?>