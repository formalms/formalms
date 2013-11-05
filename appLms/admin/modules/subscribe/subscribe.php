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
 * @package  DoceboLms
 * @subpackage course
 * @version  $Id: subscribe.php 1002 2007-03-24 11:55:51Z fabio $
 * @author	 Fabio Pirovano <fabio[at]docebo-com>
 */

require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

function subscribeadd() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.userselector.php');

	$id_course = importVar('id_course', true, 0);
	$edition_id=getCourseEditionId();
	$ed_url_param=getEditionUrlParameter($edition_id);

	$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$out =& $GLOBALS['page'];

	$user_select = new UserSelector();
	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = TRUE;
	$user_select->show_orgchart_simple_selector = TRUE;
	if(isset($_GET['load'])) {
		// ema -- add requested_tab to show user selector
		$user_select->requested_tab = PEOPLEVIEW_TAB;
		$user_alredy_subscribed = getSubscribed($id_course, FALSE, FALSE, FALSE, $edition_id);
		$user_select->resetSelection($user_alredy_subscribed);
	}
	
	$acl_man =& Docebo::user()->getAclManager();
	$user_select->setUserFilter('exclude', array($acl_man->getAnonymousId()));
	
	$user_select->loadSelector('index.php?modname=subscribe&amp;op=subscribeadd&amp;id_course='.$id_course.$ed_url_param.'&amp;jump=1',
			$lang->def('_SUBSCRIBE'),
			$lang->def('_CHOOSE_SUBSCRIBE'),
			true);
}

function chooselevel() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.userselector.php');

	$id_course 		= importVar('id_course', true, 0);
	$course_info 	= Man_Course::getCourseInfo($id_course);

	$edition_id=getCourseEditionId();

	if ($edition_id > 0) {
		$edition_info =Man_Course::getEditionInfo($edition_id, $id_course);
		$course_info =$edition_info+$course_info;
	}

	$out 			=& $GLOBALS['page'];
	$acl_man		=& Docebo::user()->getAclManager();
	$lang 			=& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$levels 		= CourseLevel::getLevels();

	// Find limitation
	$can_subscribe = true;
	$max_num_subscribe 	= $course_info['max_num_subscribe'];
	$subscribe_method 	= $course_info['subscribe_method'];
	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

		$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
		$max_subscribe 		= Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
		$direct_subscribe 	= Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

		if($limited_subscribe == 'on') $limited_subscribe = true;
		else $limited_subscribe = false;
		if($direct_subscribe == 'on') $direct_subscribe = true;
		else $direct_subscribe = false;
	} else {

		$limited_subscribe 	= false;
		$max_subscribe 		= 0 ;
		$direct_subscribe 	= true;
	}

	// Print page
	$page_title = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSES'),
		$lang->def('_SUBSCRIBE'),
		$course_info['name']
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'subscribe')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=course&amp;op=course_list', $lang->def('_BACK'))
	, 'content');
	// User selected
	$user_alredy_subscribed 	= getSubscribed($id_course, FALSE, FALSE, FALSE, $edition_id);
	$num_all_user = count($user_alredy_subscribed);

	if(!isset($_POST['user_level_sel'])) {

		$user_select 	= new UserSelector();
		$entity_selected 	= $user_select->getSelection($_POST);
		// convert to user only

		$user_selected =& $acl_man->getAllUsersFromIdst($entity_selected);

		$user_selected = array_diff($user_selected, $user_alredy_subscribed);
		$num_selected = count($user_selected);
	} else {

		$num_selected = 0;
		$user_selected = array();
		while(list($id_user, $lv) = each($_POST['user_level_sel'])) {

			$user_selected[$id_user] = $id_user;
			if($lv != 0) $num_selected++;
		}
		reset($_POST['user_level_sel']);
	}
	$user_selected_info =& $acl_man->getUsers($user_selected);

	if($num_selected == 0) {
		$GLOBALS['page']->add($lang->def('_EMPTY_SELECTION').'</div>', 'content');
		return;
	}
	
	if($subscribe_method != 3 && !$direct_subscribe)
		$GLOBALS['page']->add(getResultUi($lang->def('_BEFORE_THIS_APPROVE').'<br />'), 'content');

	if(isset($_POST['subscribe']) && $can_subscribe) {
		// do subscription

		//retrive id of group of the course for the varioud level
		$level_idst =& getCourseLevel($id_course);
		if(count($level_idst) == 0) {

			//if the group doesn't exists create it
			$level_idst =& DoceboCourse::createCourseLevel($id_course);
		}
		// Subscirbing user
		$waiting = 0;
		$user_subscribed = array();
		$user_waiting = array();
		if($subscribe_method != 3 && !$direct_subscribe) $waiting = 1;
		while(list($id_user, $lv_sel) = each($_POST['user_level_sel'])) {
			if(!$limited_subscribe || $max_subscribe)
			{
				if($lv_sel != 0) {
	
					// Add in group for permission
					$acl_man->addToGroup($level_idst[$lv_sel], $id_user);
	
					// Add to edition group
					if ($edition_id > 0) {
	
						$group ='/lms/course_edition/'.$edition_id.'/subscribed';
						$group_idst =$acl_man->getGroupST($group);
						if ($group_idst === FALSE) {
							$group_idst =$acl_man->registerGroup($group, 'all the user of a course edition', true, "course");
						}
	
						$acl_man->addToGroup($group_idst, $id_user);
					}
	
					// Add in table
					$re = sql_query("
					INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
					( idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr )
					VALUES
					( '".$id_user."', '".$id_course."', '".$edition_id."', '".$lv_sel."', '".$waiting."', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )	");
					if($re) {
						if($waiting) $user_waiting[] = $id_user;
						else $user_subscribed[] = $id_user;
	
						addUserToTimeTable($id_user, $id_course, $edition_id);
					}
				}
				
				$max_subscribe--;
			}
		}//end while
		Docebo::user()->loadUserSectionST('/lms/course/private/');
		Docebo::user()->SaveInSession();

		require_once(_base_.'/lib/lib.eventmanager.php');
		$array_subst = array(
			'[url]' => Get::sett('url'),
			'[course]' => $course_info['name'],
			'[medium_time]' => $course_info['mediumTime'],
			'[course_name]' => $course_info['name'],
			'[course_code]' => $course['code']
		);
		if(!empty($user_subscribed)) {
			// message to user subscribed
			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);

			$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert(	'UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed',
						$user_subscribed, $msg_composer  );

		}
		if(!empty($user_waiting)) {
			// message to user that is waiting
			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBS_WAITING_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_NEW_USER_SUBS_WAITING_TEXT', $array_subst);

			$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBS_WAITING_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert(	'UserCourseInsertModerate', 'subscribe', 'insert', '1', 'User subscribed with moderation',
						$user_waiting, $msg_composer  );
		}

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			Docebo::user()->preference->setPreference('admin_rules.max_course_subscribe', $max_subscribe);
		}
		backcourse('ok_subs');
	}

	$GLOBALS['page']->add(
		Form::openForm('levelselection', 'index.php?modname=subscribe&amp;op=chooselevel')
		.Form::getHidden('id_course', 'id_course', $id_course)
		.Form::getHidden('edition_id', 'edition_id', $edition_id)
	, 'content');

	$tb = new Table( 0, $lang->def('_CAPTION_SELECT_LEVELS'), $lang->def('_SUMMARY_SELECT_LEVEL') );
	$type_h = array('image', '', '');
	$img ='<img src="'.getPathImage('fw').'standard/warning_triangle.png" ';
	$img.='alt="'.$lang->def("_USER_IS_BUSY").'" title="'.$lang->def("_USER_IS_BUSY").'" />';
	$content_h = array($img, $lang->def('_USERNAME'), $lang->def('_FULLNAME'));
	foreach($levels as $lv => $lv_name) {
		$type_h[]	 = 'image';
		$content_h[] = '<a href="javascript:SelAll(\''.$lv.'\');">'.$lv_name.'</a>';
	}
	$type_h[]	 = 'image';
	$content_h[] = $lang->def('_CANCEL');
	$tb->addHead($content_h, $type_h);

	if ($course_info["course_type"] === "elearning") {
		$busy_users=array();
	}
	else {
		require_once($GLOBALS['where_framework']."/lib/resources/lib.timetable.php");
		$tt=new TimeTable();
		$busy_users=$tt->getResourcesInUse("user", $course_info["date_begin"], $course_info["date_end"], TRUE);
	}

	$num_user_sel = 0;
	$enought_credit = true;
	reset($user_selected_info);

        $jsArr = "var elementi = new Array(";
        $i=0;

	while( (list($id_user, $user_info) = each($user_selected_info)) && ($enought_credit)) {
        if ($i != 0)
            $jsArr .= ",";
        $i++;
        $jsArr .= "'".$id_user."'";
        
		// if the user isn't alredy subscribed to the course
		if(!isset($user_alredy_subscribed[$id_user])) {

			if (in_array($id_user, $busy_users)) {
				
				$img ='<img src="'.getPathImage('fw').'standard/warning_triangle.png" ';
				$img.='alt="'.$lang->def("_USER_IS_BUSY").'" title="'.$lang->def("_USER_IS_BUSY").'" />';
				$msg =$lang->def("_USER_IS_BUSY_MSG");
				
				$is_user_busy=$img;//."</a>";
			}
			else {
				$is_user_busy="&nbsp;";
			}

			$content = array(	$is_user_busy, substr($user_info[ACL_INFO_USERID], 1),
								$user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]);
			foreach($levels as $lv => $lv_name) {

				$content[] = Form::getInputRadio(	'user_level_sel_'.$id_user.'_'.$lv,
													'user_level_sel['.$id_user.']',
													$lv,
													( isset($_POST['user_level_sel']) ? $lv == $_POST['user_level_sel'][$id_user] : $lv == 3 ),
													'' )
							.'<label class="access-only" for="user_level_sel_'.$id_user.'_'.$lv.'">'.$lv_name.'</label>';
			}
			$content[] = Form::getInputRadio(	'user_level_sel_'.$id_user.'_0',
													'user_level_sel['.$id_user.']',
													0,
													( isset($_POST['user_level_sel']) ? 0 == $_POST['user_level_sel'][$id_user] : false ),
													'' )
							.'<label class="access-only" for="user_level_sel_'.$id_user.'_0">'.$lang->def('_CANCEL').'</label>';
			$tb->addBody($content);
			$num_user_sel++;
		}
	}
	$GLOBALS['page']->add($tb->getTable(), 'content');
	$GLOBALS['page']->add(
		Form::openButtonSpace()
		.'<br />'
		.Form::getButton('subscribe', 'subscribe', $lang->def('_SUBSCRIBE'))
		.Form::getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	, 'content');
	$GLOBALS['page']->add('</div>', 'content');

        $GLOBALS['page']->add('
<script>
'.$jsArr.');
function SelAll (lvl)
{
        var nb;
        ne = elementi.length;
        mod = document.getElementById(\'levelselection\');
        for (var i=0;i<ne;i++)
        {
                elem = \'user_level_sel_\'+elementi[i]+\'_\'+lvl;
                var e = document.getElementById(elem);
                e.checked = 1;
        }
}
</script>');
}

function backcourse($result = false) {

	Util::jump_to('index.php?modname=course&op=course_list'.( $result !== false ? '&result='.$result : '' ) );
}

/******************************************************************************/

function subscribemod() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
	require_once(_base_.'/lib/lib.table.php');
	
	$id_course 		= importVar('id_course', true, 0);
	$course_info 	= Man_Course::getCourseInfo($id_course);
	$edition_id 	= getCourseEditionId();
	$fman 			= new FieldList();
	
	//addScriptaculousJs();
	YuiLib::load(array('json'=>'json-min.js'));
	$GLOBALS['page']->add(
		'<script type="text/javascript">'
		.' function reloadInfo() {
		  var $ = YAHOO.util.Dom.get;
			var selection = $("extra_info").value;
			var id_course = $("id_course").value;
		
			var data = "op=get_info&id_course="+id_course+"&id_field=" + selection;
			
			var objAjax = YAHOO.util.Connect.asyncRequest("POST",
		        "'.$GLOBALS['where_lms_relative'].'/ajax.adm_server.php?mn=subscribe",
		        {onSuccess: callback_change}, data
		    );
			$("extra_info").disabled = true;
		} '
		
		.' function callback_change(o) {
			
			var result = YAHOO.lang.JSON.parse(o.responseText);
			var table = $("subscribed_list");
			for(var i= 0;i < table.rows.length;i++) {
				var ind = table.rows[i].id.indexOf("user_");
				if(ind >= 0) {
					var id_user = table.rows[i].id.substr(5);
					table.rows[i].cells[1].innerHTML = result[id_user];
				}
			}
			$("extra_info").disabled = false;
		}'
		
		.'</script>'
	, 'page_head');

	$out 			=& $GLOBALS['page'];
	$lang 			=& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$acl_man		=& Docebo::user()->getAclManager();
	$levels 		= CourseLevel::getLevels();
	
	$arr_absent = array(0 => $lang->def('_NO'),
						1 => $lang->def('_JUSTIFIED'),
						2 => $lang->def('_NOT_JUSTIFIED') );
	
	$arr_status = array(_CUS_CONFIRMED 		=> $lang->def('_USER_STATUS_CONFIRMED'),
						
						_CUS_SUBSCRIBED 	=> $lang->def('_USER_STATUS_SUBS'),
						_CUS_BEGIN 			=> $lang->def('_USER_STATUS_BEGIN'),
						_CUS_END 			=> $lang->def('_USER_STATUS_END'),
						_CUS_SUSPEND 		=> $lang->def('_USER_STATUS_SUSPEND'),
                        _CUS_CANCELLED		=> $lang->def('_USER_STATUS_CANCELLED') );
	
	$field = $fman->getFlatAllFields();
	$field = array(
		'name' => $lang->def('_FULLNAME'),
		'email' => $lang->def('_EMAIL')
	) + $field;
	
	// Retrive info about the selected user
	
	$user_alredy_subscribed 	= getSubscribed($id_course, false, false, true, $edition_id);
	$user_levels 				= getSubscribedInfo($id_course, false, false, false, false, $edition_id);
	
	require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
	
	$adminManager = new AdminManager();
	$acl_manager = new DoceboACLManager();
	
	$idst_associated = $adminManager->getAdminTree(getLogUserId());
	$array_user_associated =& $acl_manager->getAllUsersFromIdst($idst_associated);
	
	$user_level = Docebo::user()->getUserLevelId();
	
	if($user_level != ADMIN_GROUP_GODADMIN)
		$user_alredy_subscribed = array_intersect($user_alredy_subscribed, $array_user_associated);
	
	$user_selected_info 		=& $acl_man->getUsers($user_alredy_subscribed);

	$page_title = array(
		'index.php?modname=course&op=course_list' => $lang->def('_COURSES'),
		$course_info['name'] ,
		$lang->def('_SUBSCRIBE')
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'subscribe')
		.'<div class="std_block">'
		
		.Form::openForm('levelselection', 'index.php?modname=subscribe&amp;op=subscribeupdate')
		.Form::getHidden('id_course', 'id_course', $id_course)
		.Form::getHidden('edition_id', 'edition_id', $edition_id)
		, 'content');

	$tb 	= new Table( 0, $lang->def('_CAPTION_SELECT_LEVELS'), $lang->def('_SUMMARY_SELECT_LEVEL') );
	$tb->setTableId('subscribed_list');


	$type_h = array('', '');
	$content_h = array($lang->def('_USERNAME'),
		//$lang->def('_FULLNAME')
		Form::getInputDropdown('dropdown_nowh', 'extra_info', 'extra_info', $field, 0, ' onchange="reloadInfo();"')
	);
	
	foreach($levels as $lv => $lv_name) {
	
		$type_h[]	 = 'image';
		$content_h[] = '<a href="javascript:SelAll(\''.$lv.'\');">'.$lv_name.'</a>';
	}
	$type_h[]	 = 'image';
	$content_h[] = $lang->def('_STATUS');
	
	if($course_info['course_type'] != 'elearning') {
		$type_h[]	 = 'image';
		$content_h[] = $lang->def('_ABSENT');
	}
	$tb->addHead($content_h, $type_h);

	$num_user_sel = 0;
	if(is_array($user_selected_info)) {

		reset($user_selected_info);

	        $jsArr = "var elementi = new Array(";
        	$i=0;

		while( (list($id_user, $user_info) = each($user_selected_info))) {
            if ($i != 0)
               	$jsArr .= ",";
	        $i++;
            $jsArr .= "'".$id_user."'";

			// if the user isn't alredy subscribed to the course
			$content = array(	substr($user_info[ACL_INFO_USERID], 1),
								$user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME]);
			foreach($levels as $lv => $lv_name) {

				$content[] = Form::getInputRadio(	'user_level_sel_'.$id_user.'_'.$lv,
													'user_level_sel['.$id_user.']',
													$lv,
													($lv == $user_levels[$id_user]['level']),
													'' )
							.'<label class="access-only" for="user_level_sel_'.$id_user.'_'.$lv.'">'.$lv_name.'</label>';
			}
			$content[] = Form::getInputDropdown(	'dropdown',
													'user_status_sel_'.$id_user.'',
													'user_status_sel['.$id_user.']',
													$arr_status,  
													$user_levels[$id_user]['status'],
													'')
						.'<label class="access-only" for="user_status_sel_'.$id_user.'">'.$lang->def('_STATUS').'</label>';
						
			if($course_info['course_type'] != 'elearning') {
				
				$content[] = Form::getInputDropdown('dropdown_nowh',
													'user_absent'.$id_user.'',
													'user_absent['.$id_user.']',
													$arr_absent,  
													$user_levels[$id_user]['absent'],
													'')
						.'<label class="access-only" for="user_absent_'.$id_user.'">'.$lang->def('_ABSENT').'</label>';	
			}
						
			$tb->addBody($content, false, false, 'user_'.$id_user);
		}
		$GLOBALS['page']->add($tb->getTable(), 'content');
	}
	$GLOBALS['page']->add(
		Form::openButtonSpace()
		.'<br />'
		.Form::getButton('subscribe', 'subscribe', $lang->def('_MOD'))
		.Form::getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	, 'content');
	$GLOBALS['page']->add('</div>', 'content');

        $GLOBALS['page']->add('                                 
<script>                
'.$jsArr.');
function SelAll (lvl)           
{                                                                                                       
        var nb;                                                                                         
        ne = elementi.length;
        mod = document.getElementById(\'levelselection\');
        for (var i=0;i<ne;i++)                                                                          
        {                                               
                elem = \'user_level_sel_\'+elementi[i]+\'_\'+lvl;
                var e = document.getElementById(elem);
                e.checked = 1;                                                                          
        }                                                                                               
}
</script>');

}

function subscribeupdate() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.stats.php');

	$id_course 		= importVar('id_course', true, 0);
	$edition_id 	= getCourseEditionId();
	$course_info 	= Man_Course::getCourseInfo($id_course);
	
	$lang 		=& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$out 		=& $GLOBALS['page'];
	$acl_man	=& Docebo::user()->getAclManager();

	if(!isset($_POST['user_level_sel'])) {

		//the user selection is empty, return to course selection
		backcourse('err_selempty');
	}
	
	//retrive id of group of the course for the various level ---------------------------
	$level_idst 		=& getCourseLevel($id_course);
	$actual_user_level 	= getSubscribedLevel($id_course, false, false, $edition_id);
	if(count($level_idst) == 0) {

		//if the group doesn't exists create it
		$level_idst =& DoceboCourse::createCourseLevel($id_course);
	}

	// Subscirbing user ----------------------------------------------------------------- 
	
	$re = true;
	$user_subs = array();
	while(list($id_user, $lv_sel) = each($_POST['user_level_sel'])) {

		$lv_old = $actual_user_level[$id_user];
		if($lv_sel != $lv_old) {

			// Add in group for permission
			$acl_man->removeFromGroup($level_idst[$lv_old], $id_user);
			$acl_man->addToGroup($level_idst[$lv_sel], $id_user);
		}
		
		$new_status = $_POST['user_status_sel'][$id_user];
		
		$upd_query = "
		UPDATE ".$GLOBALS['prefix_lms']."_courseuser
		SET level = '".$lv_sel."',
			status = '".$new_status."',
			absent = '".( isset($_POST['user_absent'][$id_user]) ? $_POST['user_absent'][$id_user] : '0' )."'
		".( $new_status == _CUS_RESERVED || $new_status == _CUS_WAITING_LIST || $new_status == _CUS_CONFIRMED
			? ", waiting = '1'" 
			: ""  )."
		
		".( $_POST['user_status_sel'][$id_user] == _CUS_CANCELLED 
			? ", cancelled_by = '".getLogUserId()."'" 
			: ", cancelled_by = '0'"  )."
		
		WHERE idUser = '".$id_user."' 
			 AND idCourse = '".$id_course."'
			 AND edition_id='".$edition_id."'";
		
		if($new_status == _CUS_END) {
			saveTrackStatusChange((int)$id_user, (int)$id_course , _CUS_END);
		}
		
		//update user's competence score
		require_once($GLOBALS['where_lms'].'/lib/lib.competences.php');
		$cman = new Competences_Manager();
		switch ($new_status) {
			case _CUS_END: {
				$cman->AssignCourseCompetencesToUser($id_course, $id_user);
			} break;
			
			//...
		}
		
		// Add in table
		$re_sing = sql_query($upd_query);
		if($re_sing) {
			$user_subs[] = $id_user;
			addUserToTimeTable($id_user, $id_course, $edition_id);
		}
		$re &= $re_sing;
		/*".( $_POST['user_status_sel'][$id_user] == _CUS_CANCELLED 
			? ", cancelled_by = '".getLogUserId()."'" 
			: ", cancelled_by = '0'"  )."*/
	}

	Docebo::user()->loadUserSectionST('/lms/course/private/');
	Docebo::user()->SaveInSession();

	require_once(_base_.'/lib/lib.eventmanager.php');
	$array_subst = array(	'[url]' => Get::sett('url'),
							'[course]' => $course_info['name'] );
	if(!empty($user_subs)) {
		// message to user that is waiting
		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_MOD_USER_SUBSCRIPTION_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_MOD_USER_SUBSCRIPTION_TEXT', $array_subst);

		$msg_composer->setBodyLangText('sms', '_MOD_USER_SUBSCRIPTION_TEXT_SMS', $array_subst);

		// send message to the user subscribed
		createNewAlert(	'UserCourseLevelChanged', 'subscribe', 'modify', '1', 'User subscribed',
					$user_subs, $msg_composer  );

	}
	backcourse( ( $re ? 'ok_subs' : 'err_subs' ) );
}

/************************************************************************************/

function subscribedel() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$id_course 			= importVar('id_course', true, 0);
	$course_to_save 	= Man_Course::saveCourseStatus();

	$edition_id = getCourseEditionId();

	$out 			=& $GLOBALS['page'];
	$lang 			=& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$acl_man		=& Docebo::user()->getAclManager();
	$levels 		= CourseLevel::getLevels();

	$user_alredy_subscribed	= getSubscribed($id_course, false, false, true, $edition_id);
	$user_levels 				= getSubscribedLevel($id_course, false, false, $edition_id);
	
	require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
	
	$adminManager = new AdminManager();
	$acl_manager = new DoceboACLManager();
	
	$idst_associated = $adminManager->getAdminTree(getLogUserId());
	$array_user_associated =& $acl_manager->getAllUsersFromIdst($idst_associated);
	
	$user_level = Docebo::user()->getUserLevelId();
	
	if($user_level != ADMIN_GROUP_GODADMIN)
		$user_alredy_subscribed = array_intersect($user_alredy_subscribed, $array_user_associated);
	
	$user_selected_info =& $acl_man->getUsers($user_alredy_subscribed);

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_SUBSCRIBE'), 'subscribe')
		.'<div class="std_block">'
		.Form::openForm('levelselection', 'index.php?modname=subscribe&amp;op=subscriberemove')
		.Form::getHidden('id_course', 'id_course', $id_course)
		.Form::getHidden('edition_id', 'edition_id', $edition_id)
		, 'content');

	$tb 	= new Table( 0, $lang->def('_CAPTION_SELECT_LEVELS'), $lang->def('_SUMMARY_SELECT_LEVEL') );

	$type_h = array('', '', '', 'image');
	$content_h = array($lang->def('_USERNAME'), $lang->def('_FULLNAME'), $lang->def('_LEVEL'),
				'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'">');

	$tb->addHead($content_h, $type_h);

	$num_user_sel = 0;
	if(is_array($user_selected_info)) {

		reset($user_selected_info);
		while( (list($id_user, $user_info) = each($user_selected_info))) {

			// if the user isn't alredy subscribed to the course
			$content = array(	substr($user_info[ACL_INFO_USERID], 1),
								$user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME],
								$levels[$user_levels[$id_user]],
								$content[] = Form::getInputCheckbox('user_to_remove'.$id_user,
													'user_to_remove['.$id_user.']',
													$id_user,
													false,
													'' )
							.'<label class="access-only" for="user_to_remove'.$id_user.'">'.$user_info[ACL_INFO_USERID].'</label>');

			$tb->addBody($content);
		}
		$GLOBALS['page']->add($tb->getTable(), 'content');
	}
	$GLOBALS['page']->add(
		Form::openButtonSpace()
		.'<br />'
		.Form::getButton('subscribe', 'subscribe', $lang->def('_DEL'))
		.Form::getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	, 'content');
	$GLOBALS['page']->add('</div>', 'content');
}

function subscriberemove() {
	checkPerm('subscribe', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	$id_course = importVar('id_course', true, 0);
	$course_info 	= Man_Course::getCourseInfo($id_course);

	$edition_id=getCourseEditionId();

	if ($edition_id > 0) {
		$edition_info =Man_Course::getEditionInfo($edition_id, $id_course);
		$course_info =$edition_info+$course_info;
	}

	$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$out =& $GLOBALS['page'];
	$acl_man	=& Docebo::user()->getAclManager();

	if(!isset($_POST['user_to_remove'])) {

		//the user selection is empty, return to course selection
		backcourse('err_selempty');
	}

	$group_levels 	= getCourseLevel($id_course);
	$user_levels 	= getSubscribedLevel($id_course, false, false, $edition_id);
	// Subscirbing user
	$re = true;
	$user_del = array();
	while(list($id_user, $v) = each($_POST['user_to_remove'])) {

		$date_begin =$course_info["date_begin"];
		$date_end =$course_info["date_end"];

		$re_sing = removeSubscription($id_course, $id_user, $group_levels[$user_levels[$id_user]], $edition_id, $date_begin, $date_end);
		if($re_sing) $user_del[] = $id_user;
		$re &= $re_sing;
	}

		require_once(_base_.'/lib/lib.eventmanager.php');
	$array_subst = array(	'[url]' => Get::sett('url'),
							'[course]' => $course_info['name'] );
	if(!empty($user_del)) {
		// message to user that is waiting
		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_DEL_USER_SUBSCRIPTION_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_DEL_USER_SUBSCRIPTION_TEXT', $array_subst);

		$msg_composer->setBodyLangText('sms', '_DEL_USER_SUBSCRIPTION_TEXT_SMS', $array_subst);

		// send message to the user subscribed
		createNewAlert(	'UserCourseRemoved', 'subscribe', 'remove', '1', 'User removed form a course',
					$user_del, $msg_composer  );

	}
	backcourse( ( $re ? 'ok_subs' : 'err_subs' ) );
}

/************************************************************************************/


function waitinguser() {
	checkPerm('moderate', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.user_profile.php');

	$id_course 		= importVar('id_course', true, 0);
	$man_course		= new Man_Course();
	$course_info 	= $man_course->getCourseInfo($id_course);

	$edition_id 	= getCourseEditionId();
	$ed_url_param 	= getEditionUrlParameter($edition_id);

	$out 			=& $GLOBALS['page'];
	$lang 			=& DoceboLanguage::CreateInstance('course', 'lms');
	$lang 			=& DoceboLanguage::CreateInstance('subscribe', 'lms');
	$acl_man		=& Docebo::user()->getAclManager();
	$levels 		= CourseLevel::getLevels();

	$waiting_users	=& $man_course->getWaitingSubscribed($id_course, $edition_id);
	$users_name =& $acl_man->getUsers($waiting_users['all_users_id']);

	$arr_status = array(//_CUS_RESERVED		=> $lang->def('_USER_STATUS_RESERVED'),
						_CUS_WAITING_LIST	=> $lang->def('_WAITING_USERS'),
						_CUS_CONFIRMED 		=> $lang->def('_USER_STATUS_CONFIRMED'),
						
						_CUS_SUBSCRIBED 	=> $lang->def('_USER_STATUS_SUBS'),
						_CUS_BEGIN 			=> $lang->def('_USER_STATUS_BEGIN'),
						_CUS_END 			=> $lang->def('_USER_STATUS_END'),
						_CUS_SUSPEND 		=> $lang->def('_SUSPENDED') );

	$page_title = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE', 'course', 'lms'),
		$course_info['name'],
		$lang->def('_USERWAITING', 'course', 'lms')
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'subscribe')
		.'<div class="std_block">'
		.Form::openForm('approve users', 'index.php?modname=subscribe&amp;op=approveusers')
		.Form::getHidden('id_course', 'id_course', $id_course)
		.Form::getHidden('edition_id', 'edition_id', $edition_id)
	, 'content');

	$tb 	= new Table( 0, $lang->def('_SELECT_WHO_CONFIRM'), $lang->def('_SUMMARY_SELECT_WHO_CONFIRM') );

	$type_h = array('', '', '', '', '', 'image', 'image', 'image');
	$content_h = array($lang->def('_USERNAME'), $lang->def('_FULLNAME'), $lang->def('_LEVEL'),
		$lang->def('_SUBSCRIBED_BY'),
		$lang->def('_STATUS'),
		$lang->def('_APPROVE'),
		$lang->def('_DENY'),
		$lang->def('_WAIT')
	);
	$tb->addHead($content_h, $type_h);

	if(is_array($waiting_users['users_info'])) {

		reset($waiting_users['users_info']);
		while((list($id_user, $info) = each($waiting_users['users_info']))) {

			$id_sub_by = $info['subscribed_by'];
			$subscribed 	= ( $users_name[$id_sub_by][ACL_INFO_LASTNAME].''.$users_name[$id_sub_by][ACL_INFO_FIRSTNAME] != ''
				? $users_name[$id_sub_by][ACL_INFO_LASTNAME].' '.$users_name[$id_sub_by][ACL_INFO_FIRSTNAME]
				: $acl_man->relativeId($users_name[$id_sub_by][ACL_INFO_USERID]) );
			$more = ( isset($_GET['id_user']) &&  $_GET['id_user'] == $id_user
				? '<a href="index.php?modname=subscribe&amp;op=waitinguser&amp;id_course='.$id_course.$ed_url_param.'"><img src="'.getPathImage().'standard/less.gif"></a> '
				: '<a href="index.php?modname=subscribe&amp;op=waitinguser&amp;id_course='.$id_course.$ed_url_param.'&amp;id_user='.$id_user.'"><img src="'.getPathImage().'standard/more.gif"></a> ');
			$content = array(
				$more.
				$acl_man->relativeId($users_name[$id_user][ACL_INFO_USERID]),
				$users_name[$id_user][ACL_INFO_LASTNAME].' '.$users_name[$id_user][ACL_INFO_FIRSTNAME],
				$levels[$info['level']],
				$subscribed.' ['.$users_name[$id_user][ACL_INFO_EMAIL].']'
			);
			$content[] = $arr_status[$info['status']];
			$content[] = Form::getInputRadio(
					'waiting_user_0_'.$id_user,
					'waiting_user['.$id_user.']',
					'0',
					false,
					'' ).'<label class="access-only" for="waiting_user_0_'.$id_user.'">'.$users_name[$id_user][ACL_INFO_USERID].'</label>';

			$content[] = Form::getInputRadio(
					'waiting_user_1_'.$id_user,
					'waiting_user['.$id_user.']',
					'1',
					false,
					'' ).'<label class="access-only" for="waiting_user_1_'.$id_user.'">'.$users_name[$id_user][ACL_INFO_USERID].'</label>';
			
			$content[] = Form::getInputRadio(
						'waiting_user_2_'.$id_user,
						'waiting_user['.$id_user.']',
						'2',
						true,
						'' ).'<label class="access-only" for="waiting_user_1_'.$id_user.'">'.$users_name[$id_user][ACL_INFO_USERID].'</label>';
					
			$tb->addBody($content);
			if (isset($_GET['id_user']) &&  $id_user == $_GET['id_user']) {
				$field = new FieldList();
				$info = $field->playFieldsForUser( $id_user, false, true );
				$tb->addBodyExpanded(( $info != '' ? $info : $lang->def('_NO_EXTRAINFO_AVAILABLE') ), 'user_specific_info');
			}
		}
	}

	$GLOBALS['page']->add(
		$tb->getTable()
		.'<br />'
		.Form::openElementSpace()
		.Form::getSimpleTextarea($lang->def('_SUBSCRIBE_ACCEPT'), 'subscribe_accept', 'subscribe_accept')
		.Form::getSimpleTextarea($lang->def('_SUBSCRIBE_REFUSE'), 'subscribe_refuse','subscribe_refuse')
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.'<br />'
		.Form::getButton('subscribe', 'subscribe', $lang->def('_SAVE'))
		.Form::getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
	, 'content');
	$GLOBALS['page']->add('</div>', 'content');
}

function approveusers() {
	checkPerm('moderate', false, 'course');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.preference.php');

	$id_course 		= importVar('id_course', true, 0);
	$course_info 	= Man_Course::getCourseInfo($id_course);

	$edition_id 	= getCourseEditionId();
	
	$re= true;
	$approve_user 	= array();
	$deny_user 		= array();
	if(isset($_POST['waiting_user'])) {
		
		$man_course		= new Man_Course();
		$waiting_users	=& $man_course->getWaitingSubscribed($id_course);
		$tot_deny 		= array();

		require_once(_lms_.'/lib/lib.course.php');

		$docebo_course = new DoceboCourse($id_course);

		$group_levels 	= $docebo_course->getCourseLevel($id_course);
				
		while(list($id_user, $action) = each($_POST['waiting_user'])) {
			
			if($action == 0) {
				// approved -----------------------------------------------
				
				$text_query = "
				UPDATE ".$GLOBALS['prefix_lms']."_courseuser
				SET waiting = 0, 
					status = '"._CUS_SUBSCRIBED."'
				WHERE idCourse = '".$id_course."' AND idUser = '".$id_user."' ";
				$text_query.= "AND edition_id='".$edition_id."'";
				$result = sql_query($text_query);
				if($result) $approve_user[] = $id_user;
				$re &= $result;
				
			} elseif($action == 1) {
				// refused --------------------------------------------------
				
				$level 		= $waiting_users['users_info'][$id_user]['level'];
				$sub_by 	= $waiting_users['users_info'][$id_user]['subscribed_by'];
				$result 	= removeSubscription($id_course, $id_user, $group_levels[$level], $edition_id);
				if($sub_by != 0 && ($id_user != $sub_by)) {
	
					if(isset($tot_deny[$sub_by])) $tot_deny[$sub_by]++;
					else $tot_deny[$sub_by] = 1;
				}
				if($result) $deny_user[] = $id_user;
				$re &= $result;
			}
		}
	}
	if(!empty($tot_deny)) {

		while(list($id_user, $inc) = each($tot_deny)) {

			$pref = new UserPreferences($id_user);
			$max_subscribe = $pref->getAdminPreference('admin_rules.max_course_subscribe');
			$pref->setPreference('admin_rules.max_course_subscribe', ($max_subscribe + $inc));
		}
	}
	require_once(_base_.'/lib/lib.eventmanager.php');
	$array_subst = array(	'[url]' => Get::sett('url'),
							'[course]' => $course_info['name'] );
	if(!empty($approve_user)) {

		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_APPROVED_SUBSCRIBED_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_APPROVED_SUBSCRIBED_TEXT', $array_subst);
		$msg_composer->setBodyLangText('email', "\n\n".$_POST['subscribe_accept'], array(), true);

		$msg_composer->setBodyLangText('sms', '_APPROVED_SUBSCRIBED_TEXT_SMS', $array_subst);

		// send message to the user subscribed
		createNewAlert(	'UserCourseInserted', 'subscribe', 'approve', '1', 'User course approve',
					$approve_user, $msg_composer, true );

	}
	if(!empty($deny_user)) {

		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_DENY_SUBSCRIBED_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_DENY_SUBSCRIBED_TEXT', $array_subst);
		$msg_composer->setBodyLangText('email', "\n\n".$_POST['subscribe_refuse'], array(), true);

		$msg_composer->setSubjectLangText('sms', '_DENY_SUBSCRIBED_SUBJECT_SMS', false);
		$msg_composer->setBodyLangText('sms', '_DENY_SUBSCRIBED_TEXT_SMS', $array_subst);

		// send message to the user subscribed
		createNewAlert(	'UserCourseInserted', 'subscribe', 'deny', '1', 'User course deny',
					$deny_user, $msg_composer, true );
	}
	backcourse( ( $re ? 'ok' : 'err' ) );

}

function removeSubscription($id_course, $id_user, $lv_group, $edition_id=0, $start_date=FALSE, $end_date=FALSE) {

	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();
	// ----------------------------------------
	$resource="user";
	$resource_id=$id_user;
	if ($edition_id > 0) {
		$consumer="course_edition";
		$consumer_id=$edition_id;
	}
	else {
		$consumer="course";
		$consumer_id=$id_course;
	}
	// ----------------------------------------
	$tt->deleteEvent(FALSE, $resource, $resource_id, $consumer, $consumer_id, $start_date, $end_date);

	$acl_man =& Docebo::user()->getAclManager();
	$acl_man->removeFromGroup($lv_group, $id_user);

	if ($edition_id > 0) {
		$group ='/lms/course_edition/'.$edition_id.'/subscribed';
		$group_idst =$acl_man->getGroupST($group);
		$acl_man->removeFromGroup($group_idst, $id_user);
	}

	return sql_query("
	DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idUser = '".$id_user."' AND idCourse = '".$id_course."'
	AND edition_id='".(int)$edition_id."'");
}


function getCourseEditionId() {

	if (isset($_POST["edition_id"])) {
		$res=(int)$_POST["edition_id"];
	}
	else if (isset($_GET["edition"])) {
		$res=(int)$_GET["edition"];
	}
	else {
		$res=0;
	}
	
	return $res;
}


function getEditionUrlParameter($edition_id) {

	if ($edition_id > 0) {
		$res="&amp;edition=".$edition_id;
	}
	else {
		$res="";
	}

	return $res;
}


function addUserToTimeTable($user_id, $course_id, $edition_id=0) {

	// -- timetable setup ------------------------------------------------
	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();

	$resource="user";
	$resource_id=$user_id;
	if ($edition_id > 0) {
		$consumer="course_edition";
		$consumer_id=$edition_id;
		$table=$GLOBALS["prefix_lms"]."_course_edition";
		$id_name="idCourseEdition";
	}
	else {
		$consumer="course";
		$consumer_id=$course_id;
		$table=$GLOBALS["prefix_lms"]."_course";
		$id_name="idCourse";
	}
	// -------------------------------------------------------------------


	$qtxt ="SELECT date_begin, date_end FROM ".$table." ";
	$qtxt.="WHERE ".$id_name."='".(int)$consumer_id."'";

	$q=sql_query($qtxt);

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_assoc($q);

		$start_date=$row["date_begin"];
		$end_date=$row["date_end"];
	}
	else {
		return FALSE;
	}


	$res=$tt->saveEvent(FALSE, $start_date, $end_date, $start_date, $end_date, $resource, $resource_id, $consumer, $consumer_id);

	return $res;
}


function loadImportCourseUser()
{	
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
	
	$id_course = importVar('id_course', true, 0);
	$id_course_edition = importVar('edition', true, 0);
	
	require_once(_base_.'/lib/lib.form.php');
	$form = new Form();
	
	$out->add(getTitleArea(	$lang->def('_SUBSCRIBE')).
							'<div class="std_block">');
	
	$out->add($form->openForm('import_course_users', 'index.php?modname=subscribe&amp;op=import_course_user_2', false, false, 'multipart/form-data'));
	$out->add($form->openElementSpace());

	$out->add($form->getFilefield( $lang->def('_IMPORT_FILE'), 'file_import', 'file_import'));
	$out->add($form->getCheckbox( $lang->def('_IMPORT_HEADER'), 'import_first_row_header', 'import_first_row_header', 'true', false ));
	$out->add('<p>'.$lang->def('_IMPORT_INFO').'</p>');
	$out->add($form->getHidden('import_separator', 'import_separator', ','));
	$out->add($form->getHidden('import_charset', 'import_charset', 20, 'UTF-8'));
	$out->add($form->getHidden('id_course', 'id_course', $id_course));
	$out->add($form->getHidden('edition', 'edition', $id_course_edition));
	
	$out->add($form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('next', 'next', $lang->def('_NEXT'))
			.$form->getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
			.$form->closeButtonSpace()
			.$form->closeForm());
	
	$out->add('</div>');
}

function loadImportCourseUser2()
{
	require_once(_base_.'/lib/lib.upload.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.import.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.table.php');
	
	$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
	
	$back_url = 'index.php?modname=course&op=course_list';
	
	$acl_man =& Docebo::user()->getAclManager();
	
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$id_course = importVar('id_course', true, 0);
	$id_course_edition = importVar('edition', true, 0);
	
	$level_idst =& getCourseLevel($id_course);
	
	if(count($level_idst) == 0)
		$level_idst =& DoceboCourse::createCourseLevel($id_course);
	
	// ----------- file upload -----------------------------------------
	if($_FILES['file_import']['name'] == '') {
		$_SESSION['last_error'] = Lang::t('_FILEUNSPECIFIED');
		Util::jump_to( $back_url.'&import_result=-1' );
	} else {
		$path = '/appCore/';
		$savefile = mt_rand(0,100).'_'.time().'_'.$_FILES['file_import']['name'];
		if(!file_exists( $GLOBALS['where_files_relative'].$path.$savefile )) {
			sl_open_fileoperations();
			if(!sl_upload($_FILES['file_import']['tmp_name'], $path.$savefile)) {
				
				sl_close_fileoperations();
				$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
				Util::jump_to( $back_url.'&import_result=-1' );
			}
			sl_close_fileoperations();
		} else {
			$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
			Util::jump_to( $back_url.'&create_result=-1' );
		}
	}
	
	$out->add(getTitleArea(	$lang->def('_SUBSCRIBE')).
							'<div class="std_block">');
	
	$separator = importVar('import_separator', false, ',');
	
	if ($separator == '')
		$separator = ',';
	
	$first_row_header = importVar('import_first_row_header', false, false);
	$import_charset = importVar('import_charset', false, 'UTF-8');
	
	if( trim($import_charset) === '')
		$import_charset = 'UTF-8';
	
	$src = new DeceboImport_SourceCSV(array('filename'=>$GLOBALS['where_files_relative'].$path.$savefile,
											'separator'=>$separator,
											'first_row_header'=>$first_row_header,
											'import_charset'=>$import_charset));
	
	$src->connect();
	
	$user_added = 0;
	$user_error = 0;
	$user_not_needed = 0;
	
	$id_user_added = array();
	
	$counter = 0;
	
	if (is_array($row = $src->get_first_row()) && !empty($row))
	{
		$user_info = $acl_man->getUser(false, $row[0]);
		if($user_info) {
			$id_user = $user_info[ACL_INFO_IDST];

			// Add in group for permission
			$acl_man->addToGroup($level_idst['3'], $id_user);

			// Add to edition group
			if ($id_course_edition > 0) {

				$group ='/lms/course_edition/'.$id_course_edition.'/subscribed';
				$group_idst =$acl_man->getGroupST($group);
				if ($group_idst === FALSE) {
					$group_idst =$acl_man->registerGroup($group, 'all the user of a course edition', true, "course");
				}

				$acl_man->addToGroup($group_idst, $id_user);
			}

			// Add in table
			$re = sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
			( idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr )
			VALUES
			( '".$id_user."', '".$id_course."', '".$id_course_edition."', '3', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )	");
			if($re)
			{
				addUserToTimeTable($id_user, $id_course, $id_course_edition);
				$user_added++;
				$id_user_added[$counter]['id_user'] = $id_user;
				$id_user_added[$counter]['status'] = '_CORRECT';
			}
			else
			{
				$query = 	"SELECT COUNT(*)"
							." FROM ".$GLOBALS['prefix_lms']."_courseuser"
							." WHERE idUser = '".$id_user."'"
							." AND idCourse = '".$id_course."'"
							." AND edition_id = '".$id_course_edition."'";

				list($control) = sql_fetch_row(sql_query($query));

				if ($control)
				{
					$user_not_needed++;
					$id_user_added[$counter]['id_user'] = $id_user;
					$id_user_added[$counter]['status'] = '_NOT_NEEDED';
				}
				else
				{
					$user_error++;
					$id_user_added[$counter]['id_user'] = $id_user;
					$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
				}
			}

			$counter++;
		} else {

			$user_error++;
			$id_user_added[$counter]['id_user'] = $id_user;
			$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
		}
	}
	
	while (is_array($row = $src->get_next_row()) && !empty($row))
	{
		$user_info = $acl_man->getUser(false, $row[0]);
		if($user_info) {

			$id_user = $user_info[ACL_INFO_IDST];

			// Add in group for permission
			$acl_man->addToGroup($level_idst['3'], $id_user);

			// Add to edition group
			if ($id_course_edition > 0) {

				$group ='/lms/course_edition/'.$id_course_edition.'/subscribed';
				$group_idst =$acl_man->getGroupST($group);
				if ($group_idst === FALSE) {
					$group_idst =$acl_man->registerGroup($group, 'all the user of a course edition', true, "course");
				}

				$acl_man->addToGroup($group_idst, $id_user);
			}

			// Add in table
			$re = sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
			( idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr )
			VALUES
			( '".$id_user."', '".$id_course."', '".$id_course_edition."', '3', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )	");
			if($re)
			{
				addUserToTimeTable($id_user, $id_course, $id_course_edition);
				$user_added++;
				$id_user_added[$counter]['id_user'] = $id_user;
				$id_user_added[$counter]['status'] = '_CORRECT';
			}
			else
			{
				$query = 	"SELECT COUNT(*)"
							." FROM ".$GLOBALS['prefix_lms']."_courseuser"
							." WHERE idUser = '".$id_user."'"
							." AND idCourse = '".$id_course."'"
							." AND edition_id = '".$id_course_edition."'";

				list($control) = sql_fetch_row(sql_query($query));

				if ($control)
				{
					$user_not_needed++;
					$id_user_added[$counter]['id_user'] = $id_user;
					$id_user_added[$counter]['status'] = '_NOT_NEEDED';
				}
				else
				{
					$user_error++;
					$id_user_added[$counter]['id_user'] = $id_user;
					$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
				}
			}

			$counter++;
		} else {

			$user_error++;
			$id_user_added[$counter]['id_user'] = $id_user;
			$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
		}
	}
	
	$src->close();
	unset($row);
	
	$type_h = array('align_center','align_center','align_center', 'align_center');
	$cont_h = array($lang->def('_USERNAME'), $lang->def('_LASTNAME'), $lang->def('_FIRSTNAME'), $lang->def('_INSER_STATUS'));
	
	$tb = new Table(false, $lang->def('_USER_SUBSCRIBED'), $lang->def('_USER_SUBSCRIBED'));
	$tb->addHead($cont_h, $type_h);
	
	while (list(, $id_user_added_detail) = each($id_user_added))
	{
		$cont = array();
		
		$user_info = $acl_man->getUser($id_user_added_detail['id_user'], false);
		
		$cont[] = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
		$cont[] = $user_info[ACL_INFO_FIRSTNAME];
		$cont[] = $user_info[ACL_INFO_LASTNAME];
		$cont[] = $lang->def($id_user_added_detail['status']);
		
		$tb->addBody($cont);
	}
	
	sl_open_fileoperations();
	
	sl_unlink($path.$savefile);
	
	sl_close_fileoperations();
	
	$out->add(	getBackUi($back_url, $lang->def('_BACK'))
				.'<b>'.$lang->def('_INSERT_CORRECT').' : '.'</b>'.$user_added.' '
				.'<b>'.$lang->def('_OPERATION_FAILURE').' : '.'</b>'.$user_error.' '
				.'<b>'.$lang->def('_INSERT_NOT_NEEDED').' : '.'</b>'.$user_not_needed
				.'<br/>'
				.'<br/>'
				.$tb->getTable()
				.'<br/>'
				.getBackUi($back_url, $lang->def('_BACK'))
				.'</div>');
}

function subscribeFromCourse()
{
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');
	
	$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
	
	$back_url = 'index.php?modname=course&op=course_list';
	
	$acl_man =& Docebo::user()->getAclManager();
	
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$id_course = Get::req('id_course', DOTY_INT, 0);
	$edition_id = Get::req('edition_id', DOTY_INT, 0);
	$alert			= Get::req('alert', DOTY_INT, 0);
	$delete_prev	= Get::req('delete_from_prev', DOTY_INT, 0);
	
	$sel = new Course_Manager();
	
	if(isset($_POST['subscribe_import']))
	{
		$course_info = Man_Course::getCourseInfo($id_course);
		
		$level_idst =& getCourseLevel($id_course);
		
		if(count($level_idst) == 0)
			$level_idst =& DoceboCourse::createCourseLevel($id_course);
		
		$course_selected = $sel->getCourseSelection($_POST);
		
		if(empty($course_selected))
			Util::jump_to('index.php?modname=course&op=course_list&result=err');
		
		$id_course		= Get::req('id_course', DOTY_INT, 0);
		$level			= Get::req('level', DOTY_INT, 0);
		$status			= Get::req('status', DOTY_INT, 0);
		
		$gsel = array();
		foreach($course_selected as $trash => $id) {
			
			$gsel[$id] = getCourseLevel($id);
        }
		
		
		$query =	"SELECT idUser, idCourse, level"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE idCourse IN (".implode(',', $course_selected).")";
		if($level) $query .= " AND level = '".$level."'";
		if($status != '-2') $query .= " AND status = '".$status."'";
		
		$result = sql_query($query);
		
		$array_user = array();
		$user_subscribed = array();

		while(list($id_user, $id_prev_course, $lv_sel) = sql_fetch_row($result)) {

			if($delete_prev) removeSubscription($id_prev_course, $id_user, $gsel[$id_prev_course][$lv_sel]);
			
			// Add in group for permission
			$acl_man->addToGroup($level_idst[$lv_sel], $id_user);
			
			// Add in table
			$re = sql_query("
			INSERT INTO ".$GLOBALS['prefix_lms']."_courseuser
			( idUser, idCourse, edition_id, level, waiting, subscribed_by, date_inscr )
			VALUES
			( '".$id_user."', '".$id_course."', '".$edition_id."', '".$lv_sel."', '0', '".getLogUserId()."', '".date("Y-m-d H:i:s")."' )	");
			if($re) {
				$user_subscribed[] = $id_user;

				addUserToTimeTable($id_user, $id_course, $edition_id);
			}

		}
		
		Docebo::user()->loadUserSectionST('/lms/course/private/');
		Docebo::user()->SaveInSession();

		require_once(_base_.'/lib/lib.eventmanager.php');
		$array_subst = array(
			'[url]' => Get::sett('url'),
			'[course]' => $course_info['name'],
			'[medium_time]' => $course_info['mediumTime'],
			'[course_name]' => $course_info['name'],
			'[course_code]' => $course['code']
		);
		if(!empty($user_subscribed) && $alert)
		{
			// message to user that is subscribed
			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);

			$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', $user_subscribed, $msg_composer);

		}
		
		Util::jump_to('index.php?modname=course&op=course_list&result=ok');
	}
	
	$sel->setLink('index.php?modname=meta_certificate&amp;op=new_assign');
	
	$sel->show_coursepath_selector = false;
	
	$sel->show_catalogue_selector = false;
	
	$array_level = CourseLevel::getLevels();
	
	$array_level['0'] = $lang->def('_ALL');
	
	$arr_status = array(	'-2'					=> $lang->def('_ALL'),
							_CUS_CONFIRMED 		=> $lang->def('_USER_STATUS_CONFIRMED'),
							_CUS_SUBSCRIBED 	=> $lang->def('_USER_STATUS_SUBS'),
							_CUS_BEGIN 			=> $lang->def('_USER_STATUS_BEGIN'),
							_CUS_END 			=> $lang->def('_USER_STATUS_END'),
							_CUS_SUSPEND 		=> $lang->def('_SUSPENDED'),
	                        _CUS_CANCELLED		=> $lang->def('_USER_STATUS_CANCELLED'));
	
	$out->add(	getTitleArea($lang->def('_IMPORT_FROM_COURSE'))
				.'<div class="std_block">'
				.Form::openForm('course_selection', 'index.php?modname=subscribe&amp;op=subscribe_from_course')
				.Form::openElementSpace()
				.Form::getDropdown($lang->def('_LEVEL_TO_IMPORT'), 'level', 'level', $array_level, isset($_POST['level']) ? $_POST['level'] : '0')
				.Form::getDropdown($lang->def('_STATUS_TO_IMPORT'), 'status', 'status', $arr_status, isset($_POST['status']) ? $_POST['status'] : '-2')
				.Form::getCheckbox($lang->def('_SEND_ALERT'), 'alert', 'alert', '1', $delete_prev)
				.Form::getCheckbox($lang->def('_DELETE'), 'delete_from_prev', 'delete_from_prev', '1', $delete_prev)
				.Form::closeElementSpace());
	
	$sel->loadSelector(false);
	
	$out->add(	Form::getHidden('id_course', 'id_course', $id_course)
				.Form::getHidden('edition_id', 'edition_id', $edition_id)
				.Form::openButtonSpace()
				.Form::getBreakRow()
		  		.Form::getButton('subscribe_import', 'subscribe_import', $lang->def('_SUBSCRIBE'))
		  		.Form::getButton('undo_course', 'undo_course', $lang->def('_UNDO'))
		  		.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>');
}

/****************************************************************************************/

function subscribeDispatch($op) {
	if(isset($_GET['ini_hidden']) || isset($_POST['ini_hidden'])) {
		
		$_SESSION['course_category']['ini_status'] = importVar('ini_hidden', true, 0);
	}
	if(isset($_POST['okselector'])) {
		$op = 'chooselevel';
	}
	if(isset($_POST['cancelselector']) || isset($_POST['undo_course'])) {
		$op = 'backcourse';
	}
	switch($op) {
		case "subscribeadd" : {
			subscribeadd();
		};break;
		case "chooselevel" : {
			chooselevel();
		};break;

		case "subscribemod" : {
			subscribemod();
		};break;
		case "subscribeupdate" : {
			subscribeupdate();
		};break;

		case "subscribedel" : {
			subscribedel();
		};break;
		case "subscriberemove" : {
			subscriberemove();
		};break;

		case "waitinguser" : {
			waitinguser();
		};break;
		case "approveusers" : {
			approveusers();
		};break;

		case "backcourse" : {
			backcourse();
		};break;
		
		case 'import_course_user':
			loadImportCourseUser();
		break;
		
		case 'import_course_user_2':
			loadImportCourseUser2();
		break;
		
		case 'subscribe_from_course':
			subscribeFromCourse();
		break;
	}
}

?>
