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

if(!Docebo::user()->isAnonymous()) {

define('_PATH_COURSE', '/appLms/'.Get::sett('pathcourse'));

require_once($GLOBALS['where_lms'].'/lib/lib.levels.php');

function statusNoEnter($perm, $status) {
	return ( $perm & (1 << $status) );
}

function newStatusEnter($arr_stat) {
	$new_perm = 0;
	if(!is_array($arr_stat)) return $new_perm;
	while( list($status) = each($arr_stat) ) {
		$new_perm |= (1 << $status);
	}
	return $new_perm;
}

function loadMaterials($idCourse) {
	$mod_perm = checkPerm('mod', true);
	
	require_once(_base_.'/lib/lib.table.php');
	
	$lang =& DoceboLanguage::createInstance('course');
	
	$re_file = sql_query("
	SELECT id_file, title, path 
	FROM ".$GLOBALS['prefix_lms']."_course_file 
	WHERE id_course='".$idCourse."'");
	
	if(!sql_num_rows($re_file) && !$mod_perm) return '';
	
	$tb = new Table(0, $lang->def('_MATERIALS'), $lang->def('_MATERIALS_TABLE'));
	
	
	$cont_h = array($lang->def('_TITLE'));
	$type_h = array('');
	if($mod_perm) {
		
		$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />';
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" />';
		$type_h[] = 'image';
	}
	//$tb->setTableStyle('');
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);
	
	$html = '<div class="floating_box">';
	if(sql_num_rows($re_file)) {
		
		while(list($idFile, $title, $file) = sql_fetch_row($re_file)) {
			
			$cont = array('<a href="index.php?modname=course&amp;op=downloadcourse&amp;id='.$idFile.'">'
				.'<img src="'.getPathImage('fw').mimeDetect($file).'" alt="mime-type" />&nbsp;'
				.$title.'</a>');
			if($mod_perm) {
				$cont[] = '<a href="index.php?modname=course&amp;op=modfiles&amp;id_file='.$idFile.'" title="'.$lang->def('_MOD').' : '.$title.'">'
					.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$title.'" /></a>';
				$cont[] = '<a href="index.php?modname=course&amp;op=remfiles&amp;id_file='.$idFile.'" title="'.$lang->def('_DEL').' : '.$title.'">'
					.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$title.'" /></a>';
			}
			$tb->addBody($cont);
		}
		$html .=  $tb->getTable();
		
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=remfiles]');
	
	}
	if($mod_perm) {
		$html .=  '<br/>'
			.'<a class="ico-wt-sprite subs_add" href="index.php?modname=course&amp;op=addfiles">'
			.'<span>'.$lang->def('_ADDFILE').'</span></a>';
	}
	$html .= '</div>';
	return $html;
}

function infocourse() {
	checkPerm('view_info');
	
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	
	//finding course information
	$mod_perm 	= checkPerm('mod', true);
	$lang 		=& DoceboLanguage::createInstance('course');
	
	$acl_man 	= Docebo::user()->getAclManager();
	$course 	= $GLOBALS['course_descriptor']->getAllInfo();
	$levels 	= CourseLevel::getLevels();
	
	$status_lang = array( 
		0 =>$lang->def('_NOACTIVE'), 
		1 =>$lang->def('_ACTIVE'), 
		2 =>$lang->def('_CST_CONFIRMED'),
		3 =>$lang->def('_CST_CONCLUDED'),
		4 =>$lang->def('_CST_CANCELLED'));
	
	$difficult_lang = array(
		'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
		'easy' => $lang->def('_DIFFICULT_EASY'),
		'medium'=> $lang->def('_DIFFICULT_MEDIUM'),
		'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
		'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'));
	
	$subs_lang = array(
		0 => $lang->def('_COURSE_S_GODADMIN'),
		1 => $lang->def('_COURSE_S_MODERATE'),
		2 => $lang->def('_COURSE_S_FREE'),
		3 => $lang->def('_COURSE_S_SECURITY_CODE') );
	
	
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_INFO'), 'course')
		.'<div class="std_block">'
	, 'content');
	  
	
	$GLOBALS['page']->add(
		'<table class="vertical_table">'
			.'<caption class="cd_name">'.$course['name'].'</caption>'
			.'<tr><th scope="row">'.$lang->def('_CODE').'</th><td>'.$course['code'].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_COURSE').'</th><td>'.$course['name'].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_DIFFICULTY').'</th><td>'.$difficult_lang[$course['difficult']].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_DESCRIPTION').'</th><td>'.$course['description'].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_SUBSCRIBE_METHOD').'</th><td>'.$subs_lang[$course['subscribe_method']].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_LANGUAGE').'</th><td>'.$course['lang_code'].'</td></tr>'
	, 'content');
	while(list($num_lv, $name_level) = each($levels)) {
		
		if($course['level_show_user'] & (1 << $num_lv)) {
			
			$users =& $acl_man->getUsers( Man_Course::getIdUserOfLevel($_SESSION['idCourse'], $num_lv, $_SESSION['idEdition']) );
			if(!empty($users)) {
				
				$first = true;
				$GLOBALS['page']->add('<tr><th scope="row">'.$name_level.'</th><td>', 'content');
				while(list($id_user, $user_info) = each($users)) {
					
					if($first) $first = false;
					else $GLOBALS['page']->add(', ', 'content');
					$GLOBALS['page']->add(
						'<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user='.$id_user.'">'
						.$acl_man->getConvertedUserName($user_info)
						.'</a>', 'content');
				} // end while
				$GLOBALS['page']->add('</td></tr>', 'content');
			} // end if
		} // end if
	} // end while
	
	if($course['show_extra_info'] == '1') {
		
		$GLOBALS['page']->add(
			'<tr><th scope="row">'.$lang->def('_STATUS').'</th><td>'.$status_lang[$course['status']].'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_PERMCLOSE').'</th><td>'.( $course['permCloseLO'] ? $lang->def('_MANUALACTION') :  $lang->def('_ENDOBJECT') ).'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_MEDIUMTIME').'</th><td>'.$course['mediumTime'].' '.$lang->def('_DAYS').'</td></tr>'
			.'<tr><th scope="row">'.$lang->def('_STATCANNOTENTER').'</th><td>'
		, 'content');
		
		$first = true;
		if( statusNoEnter($course['userStatusOp'], _CUS_SUBSCRIBED) ) {
			$GLOBALS['page']->add( $lang->def('_USER_STATUS_SUBS'), 'content');
			$first = false;
		}
		if( statusNoEnter($course['userStatusOp'], _CUS_BEGIN) ) {
			$GLOBALS['page']->add( ( $first ? '' : ', ' ).$lang->def('_USER_STATUS_BEGIN'), 'content');
			$first = false;
		}
		if( statusNoEnter($course['userStatusOp'], _CUS_SUSPEND) ) {
			$GLOBALS['page']->add( ( $first ? '' : ', ' ).$lang->def('_USER_STATUS_SUSPEND'), 'content');
			$first = false;
		}
		if( statusNoEnter($course['userStatusOp'], _CUS_END) ) {
			$GLOBALS['page']->add( ( $first ? '' : ', ' ).$lang->def('_USER_STATUS_END'), 'content');
			$first = false;
		}
		$GLOBALS['page']->add('</td></tr>', 'content');
	}
	
	// course disk quota
	if($_SESSION['levelCourse'] >= 4) {
		
		$max_quota 		= $GLOBALS['course_descriptor']->getQuotaLimit();
		$actual_space 	= $GLOBALS['course_descriptor']->getUsedSpace();
		
		$actual_space 	= number_format( ($actual_space / (1024*1024)), '2');
		if($max_quota == 0) { $percent = 0; }
		else $percent 	= ( $actual_space != 0 ?  number_format( (($actual_space / $max_quota) * 100), '2')  : '0' );
		
		$GLOBALS['page']->add(
			'<tr>'
				.'<th scope="row">'.$lang->def('_USED_DISK').'</th><td>'
				.( $max_quota == USER_QUOTA_UNLIMIT 
							? ' '.$actual_space.' MB / '.$lang->def('_UNLIMITED_QUOTA').' '
							: ''.$actual_space.' / '.$max_quota.' MB '.Util::draw_progress_bar($percent, true, 'progress_bar cp_quota_bar', false, false)
				)
			.'</td></tr>', 'content');
	}
	
	$GLOBALS['page']->add('</table>', 'content');
	
	if($mod_perm) {
		$GLOBALS['page']->add( '<br /><div class="table-container-below">'
			.'<a class="infomod" href="index.php?modname=course&amp;op=modcourseinfo">'
			.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />&nbsp;'.$lang->def('_MOD').'</a>'
			.'</div>', 'content');
	}
	
	$GLOBALS['page']->add( '</div>', 'content');
}

function modcourseinfo() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang_c =& DoceboLanguage::createInstance('course');
	$lang =& DoceboLanguage::createInstance('course');
	
	$out 		=& $GLOBALS['page'];
	$id_course 	= $_SESSION['idCourse'];
	$form 		= new Form();
	$levels 	= CourseLevel::getLevels();
	$array_lang 	= Docebo::langManager()->getAllLangCode();
	$difficult_lang = array(
		'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
		'easy' => $lang->def('_DIFFICULT_EASY'),
		'medium'=> $lang->def('_DIFFICULT_MEDIUM'),
		'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
		'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'));
	
	$query_course = "
	SELECT code, name, description, lang_code, status, level_show_user, subscribe_method, 
		linkSponsor, mediumTime, permCloseLO, userStatusOp, difficult, 
		show_progress, show_time, show_extra_info, show_rules, date_begin, date_end, valid_time 
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = '".$id_course."'";
	$course = sql_fetch_array(sql_query($query_course));
	
	$lang_code = array_search($course['lang_code'], $array_lang);
	
	$out->setWorkingZone('content');
	$out->add(
		getTitleArea($lang_c->def('_INFO'), 'infocourse')
		.'<div class="std_block">'
		.$form->openForm('course_modify', 'index.php?modname=course&amp;op=upcourseinfo')
		.$form->openElementSpace()
		
		.$form->getTextfield($lang->def('_CODE'), 'course_code', 'course_code', '50', $course['code'])
		.$form->getTextfield($lang->def('_COURSE_NAME'), 'course_name', 'course_name', '255', $course['name'])
		.$form->getDropdown($lang->def('_COURSE_LANG_METHOD'), 'course_lang', 'course_lang', $array_lang, 
			$lang_code )
		.$form->getDropdown($lang->def('_DIFFICULTY'), 'course_difficult', 'course_difficult', $difficult_lang,
			$course['difficult'] )
		.$form->getDropdown($lang->def('_STATUS'), 'course_status', 'course_status', array(
			CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
			CST_AVAILABLE 	=> Lang::t('_CST_AVAILABLE', 'course'),
			CST_EFFECTIVE 	=> Lang::t('_CST_CONFIRMED', 'course'),
			CST_CONCLUDED 	=> Lang::t('_CST_CONCLUDED', 'course'),
			CST_CANCELLED 	=> Lang::t('_CST_CANCELLED', 'course')
		), $course['status'] )
		.$form->getTextarea($lang->def('_DESCRIPTION'), 'course_descr', 'course_descr', 
			$course['description'])
		
		.$form->closeElementSpace()
		
		.$form->openButtonSpace()
			.$form->getButton('upd_course', 'upd_course', $lang->def('_SAVE'))
			.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		
		.$form->openElementSpace());
	
	//-display-mode----------------------------------------------------
	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_DISPLAY_MODE'))
		
		//-list-of-user---------------------------------------------------
		.$form->getOpenCombo($lang->def('_SHOW_USER_OF_LEVEL')));
	while(list($level, $level_name) = each($levels)) {
		
		$out->add($form->getCheckbox($level_name, 'course_show_level_'.$level, 'course_show_level['.$level.']', $level, 
			($course['level_show_user'] & (1 << $level))));
	}
	$out->add(
		$form->getCloseCombo()
		
		//-where-show-course----------------------------------------------
		.$form->getOpenCombo($lang->def('_WHERE_SHOW_COURSE'))
		.$form->getRadio($lang->def('_SC_EVERYWHERE'), 'course_show_rules_every', 'course_show_rules', '0', 
			($course['show_rules'] == 0) )
		.$form->getRadio($lang->def('_SC_ONLY_IN'), 'course_show_rules_only_in', 'course_show_rules', '1', 
			($course['show_rules'] == 1))
		.$form->getRadio($lang->def('_SC_ONLYINSC_USER'), 'course_show_rules_onlyinsc_user', 'course_show_rules', '2', 
			($course['show_rules'] == 2))
		.$form->getCloseCombo()
		
		//-what-show------------------------------------------------------
		.$form->getOpenCombo($lang->def('_WHAT_SHOW'))
		.$form->getCheckbox($lang->def('_SHOW_PROGRESS'), 'course_progress', 'course_progress', '1', 
			$course['show_progress'] )
		.$form->getCheckbox($lang->def('_SHOW_TIME'), 'course_time', 'course_time', '1', 
			$course['show_time'] )
		.$form->getCheckbox($lang->def('_SHOW_ADVANCED_INFO'), 'course_advanced', 'course_advanced', '1',
			$course['show_extra_info'] )
		.$form->getCloseCombo()
		
		.$form->getCloseFieldset());
	
	//-user-interaction--------------------------------------------------
	$out->add(
		$form->getOpenFieldset($lang->def('_USER_INTERACTION_OPTION'))
		/*
		//-subscribe-method-----------------------------------------------
		.$form->getOpenCombo($lang->def('_COURSE_SUBSRIBE'))
		.$form->getRadio($lang->def('_COURSE_S_GODADMIN'), 'course_subs_godadmin', 'course_subs', '0', 
			($course['subscribe_method'] == 0) )
		.$form->getRadio($lang->def('_COURSE_S_MODERATE'), 'course_subs_moderate', 'course_subs', '1', 
			($course['subscribe_method'] == 1))
		.$form->getRadio($lang->def('_COURSE_S_FREE'), 'course_subs_free', 'course_subs', '2', 
			($course['subscribe_method'] == 2))
		.$form->getCloseCombo()
		*/
		//mode for course end---------------------------------------------
		.$form->getOpenCombo($lang->def('_COURSE_END_MODE'))
		.$form->getRadio($lang->def('_COURSE_EM_TEACHER'), 'course_em_manual', 'course_em', '1', 
			$course['permCloseLO'] )
		.$form->getRadio($lang->def('_COURSE_EM_LO'), 'course_em_lo', 'course_em', '0', 
			!$course['permCloseLO'])
		.$form->getCloseCombo()
		
		//status that can enter------------------------------------------
		.$form->getOpenCombo($lang->def('_COURSE_STATUS_CANNOT_ENTER'))
		.$form->getCheckbox($lang->def('_USER_STATUS_SUBS'), 'user_status_0', 'user_status[0]', 0, 
			statusNoEnter($course['userStatusOp'], _CUS_SUBSCRIBED))
		.$form->getCheckbox($lang->def('_USER_STATUS_BEGIN'), 'user_status_1', 'user_status[1]', 1, 
			statusNoEnter($course['userStatusOp'], _CUS_BEGIN))
		.$form->getCheckbox($lang->def('_USER_STATUS_END'), 'user_status_2', 'user_status[2]', 2, 
			statusNoEnter($course['userStatusOp'], _CUS_END))
		.$form->getCheckbox($lang->def('_SUSPENDED'), 'user_status_3', 'user_status[3]', 3, 
			statusNoEnter($course['userStatusOp'], _CUS_SUSPEND))
		.$form->getCloseCombo()
		/*
		// max number of user that can be subscribed
		.$form->getTextfield($lang->def('_MAX_NUM_SUBSCRIBE'), 'max_num_subscribe', 'max_num_subscribe', 11, $course['max_num_subscribe'])
		
		// sms budget
		.$form->getTextfield($lang->def('_MAX_SMS_BUDGET'), 'max_sms_budget', 'max_sms_budget', 11, $course['max_sms_budget'])
		*/
		.$form->getCloseFieldset());
	
	//-expiration---------------------------------------------------------
	
    // BUG: LR, non registrava il tempo medio del corso
	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_TIME_OPTION'))/*
		.$form->getDatefield($lang->def('_DATE_BEGIN'), 'course_date_begin', 'course_date_begin', 
			$course['date_begin'])
		.$form->getDatefield($lang->def('_DATE_END'), 'course_date_end', 'course_date_end', 
			$course['date_end'])
		.$form->getTextfield($lang->def('_DAY_OF_VALIDITY'), 'course_day_of', 'course_day_of', '10', 
			$course['valid_time'])*/
		.$form->getTextfield($lang->def('_MEDIUM_TIME'), 'course_medium_time', 'course_medium_time', '10',  $course['mediumTime'])
		.$form->getCloseFieldset());
	
	//sponsor-and-logo----------------------------------------------------
	/*
	$out->add(
		$form->getTextfield($lang->def('_SPONSOR_LINK'), 'course_sponsor_link', 'course_sponsor_link', '255', 
			$course['linkSponsor'])
		.$form->getFilefield($lang->def('_SPONSOR_LOGO'), 'course_sponsor_logo', 'course_sponsor_logo')
		.$form->getFilefield($lang->def('_COURSE_LOGO'), 'course_logo', 'course_logo'));
	*/
	$out->add(
		$form->closeElementSpace()
		.$form->openButtonSpace()
			.$form->getButton('upd_course', 'upd_course', $lang->def('_SAVE'))
			.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace());
	
	$out->add($form->closeForm()
		.'</div>', 'content');
}


function upcourseinfo() {
	checkPerm('mod');
	$array_lang = Docebo::langManager()->getAllLangCode();
	
	$user_status = 0;
	if(isset($_POST['user_status'])) {
		while(list($status) = each($_POST['user_status'])) {
			$user_status |= (1 << $status);
		}
	}
	$file_sponsor = '';
	$file_logo = '';
	$re = true;
	$show_level = 0;
	if(isset($_POST['course_show_level'])) {
		while(list($lv) = each($_POST['course_show_level'])) {
			$show_level |= (1 << $lv);
		}
	}
	$query_course = "
	UPDATE ".$GLOBALS['prefix_lms']."_course 
	SET code = '".$_POST['course_code']."', 
		name = '".$_POST['course_name']."', 
		description = '".$_POST['course_descr']."', 
		lang_code = '".$array_lang[$_POST['course_lang']]."', 
		status = '".(int)$_POST['course_status']."', 
		level_show_user = '".$show_level."', 
		mediumTime = '".$_POST['course_medium_time']."',
		permCloseLO = '".$_POST['course_em']."', 
		userStatusOp = '".$user_status."', 
		difficult = '".$_POST['course_difficult']."', 
		show_progress = '".( isset($_POST['course_progress']) ? 1 : 0 )."', 
		show_time = '".( isset($_POST['course_time']) ? 1 : 0 )."', 
		show_extra_info = '".( isset($_POST['course_advanced']) ? 1 : 0 )."', 
		show_rules = '".(int)$_POST['course_show_rules']."' 
	WHERE idCourse = '".$_SESSION['idCourse']."'";
	if(!sql_query($query_course)) {
		
		$re = false;
	}
	
	$acl_man =& Docebo::user()->getAclManager();
	// send alert
	require_once(_base_.'/lib/lib.eventmanager.php'); 
	
	$msg_composer = new EventMessageComposer();
	
	$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT_MODCOURSE_INFO', false);
	$msg_composer->setBodyLangText('email', '_ALERT_TEXT_MODCOURSE_INFO', array(	'[url]' => Get::sett('url'), 
																		'[course_code]' => $_POST['course_code'], 
																		'[course]' => $_POST['course_name'] ) );
	
	$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_MODCOURSE_INFO_SMS', array(	'[url]' => Get::sett('url'), 
																		'[course_code]' => $_POST['course_code'], 
																		'[course]' => $_POST['course_name'] ) );
	
	require_once($GLOBALS['where_lms'] . '/lib/lib.course.php'); 
	$course_man = new Man_Course();
	$recipients = $course_man->getIdUserOfLevel($_SESSION['idCourse']);
	
	createNewAlert(	'CoursePorpModified', 
					'course', 
					'add', 
					'1', 
					'Inserted course '.$_POST['course_name'], 
					$recipients, 
					$msg_composer );
					
	Util::jump_to( 'index.php?modname=course&op=infocourse&result='.( $re ? 'ok' : 'err' ));
}

function downloadcourse() {
	checkPerm('view_info');
	
	require_once(_base_.'/lib/lib.download.php' );
	
	//find selected file
	list($filename) = sql_fetch_row(sql_query("
	SELECT path 
	FROM ".$GLOBALS['prefix_lms']."_course_file 
	WHERE id_course='".$_SESSION['idCourse']."' AND id_file = '".(int)$_GET['id']."'"));
	if(!$filename) {
		$GLOBALS['page']->add(getErrorUi('Sorry, such file does not exist!'), 'content');
		return;
	}
	//recognize mime type
	$extens = array_pop(explode('.', $filename));
	sendFile(_PATH_COURSE, $filename, $extens);
}

function addfiles() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang =& DoceboLanguage::createInstance('course');
	
	$GLOBALS['page']->add(
		getTitleArea(
			array('index.php?modname=course&amp;op=infocourse' => $lang->def('_INFO'), $lang->def('_ADDFILES'))
			, 'infocourse')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=course&amp;op=infocourse', $lang->def('_BACK'))
		.Form::openForm('', 'index.php?modname=course&amp;op=insfiles', false, false, 'multipart/form-data')
		
		.Form::openElementSpace()
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 50)
		.Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach')
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('insert', 'insert', $lang->def('_INSERT'))
		.Form::getButton('undo_info', 'undo_info', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	, 'content');
}

function insfiles() {
	checkPerm('mod');
	$lang =& DoceboLanguage::createInstance('course');
	require_once(_base_.'/lib/lib.upload.php');
	
	if($_POST['title'] == "") $_POST['title'] = $lang->def('_NOTITLE');
	if($_FILES['attach']['name'] == '') {
		$GLOBALS['page']->add(getErrorUi($lang->def('_FILEUNSPECIFIED')));
		return;
	} else {
		
		$quota = $GLOBALS['course_descriptor']->getQuotaLimit();
		$used = $GLOBALS['course_descriptor']->getUsedSpace();
		
		if(Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {
				
			$GLOBALS['page']->add(getErrorUi($lang->def('_QUOTA_EXCEDED')));
			return;
		}
		$savefile = $_SESSION['idCourse'].'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
		if(!file_exists($GLOBALS['where_files_relative']._PATH_COURSE.$savefile)) {
			
			sl_open_fileoperations();
			if(!sl_upload($_FILES['attach']['tmp_name'], _PATH_COURSE.$savefile)){
				sl_close_fileoperations();
				$GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_UPLOAD')));
				return;
			}
			sl_close_fileoperations();
		} else {
			$GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_UPLOAD')));
			return;
		}
	}
	$insert_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_course_file 
	SET id_course = '".(int)$_SESSION["idCourse"]."', 
		title = '".$_POST['title']."', 
		path = '$savefile'";
	
	if(!sql_query($insert_query)) {
		sl_unlink(_PATH_COURSE.$savefile);
		$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
		return;
	}
	$GLOBALS['course_descriptor']->addFileToUsedSpace($GLOBALS['where_files_relative']._PATH_COURSE.$savefile);
	Util::jump_to( 'index.php?modname=course&op=infocourse');
}

function modfiles() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang =& DoceboLanguage::createInstance('course');
	$id_file = importVar('id_file', true, 0);
	
	list($title) = sql_fetch_row(sql_query("
	SELECT title 
	FROM ".$GLOBALS['prefix_lms']."_course_file 
	WHERE id_course='".$_SESSION['idCourse']."' AND id_file='".$id_file."'"));
	
	$GLOBALS['page']->add(
		getTitleArea(
			array('index.php?modname=course&amp;op=infocourse' => $lang->def('_INFO'), $lang->def('_MOD'))
			, 'infocourse')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=course&amp;op=infocourse', $lang->def('_BACK'))
		.Form::openForm('', 'index.php?modname=course&amp;op=upfiles', false, false, 'multipart/form-data')
		
		.Form::openElementSpace()
		.Form::getHidden('id_file', 'id_file', $id_file)
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 50, $title)
		.Form::getFilefield($lang->def('_UPLOAD'), 'attach', 'attach')
		.$lang->def('_IF_NEW_FILE')
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('insert', 'insert', $lang->def('_INSERT'))
		.Form::getButton('undo_info', 'undo_info', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	, 'content');
}

function upfiles() {
	checkPerm('mod');
	$lang =& DoceboLanguage::createInstance('course');
	require_once(_base_.'/lib/lib.upload.php');
	
	if($_POST['title'] == "") $_POST['title'] = $lang->def('_NOTITLE');
	
	$savefile = '';
	if($_FILES['attach']['name'] != '') {
		
		list($old_file) = sql_fetch_row(sql_query("
		SELECT path 
		FROM ".$GLOBALS['prefix_lms']."_course_file 
		WHERE id_course='".$_SESSION['idCourse']."' AND id_file='".(int)$_POST['id_file']."'"));
		
		
		$GLOBALS['course_descriptor']->subFileToUsedSpace($GLOBALS['where_files_relative']._PATH_COURSE.$old_file);
		
		$quota = $GLOBALS['course_descriptor']->getQuotaLimit();
		$used = $GLOBALS['course_descriptor']->getUsedSpace();
		sl_unlink(_PATH_COURSE.$old_file);
		if(Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {
				
			$GLOBALS['page']->add(getErrorUi($lang->def('_QUOTA_EXCEDED')));
			return;
		}
		$savefile = $_SESSION['idCourse'].'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
		if(!file_exists($GLOBALS['where_files_relative']._PATH_COURSE.$savefile)) {
			
			sl_open_fileoperations();
			if(!sl_upload($_FILES['attach']['tmp_name'], _PATH_COURSE.$savefile)){
				
				sl_close_fileoperations();
				$GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_UPLOAD')));
				return;
			}
			sl_close_fileoperations();
		} else {
			$GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_UPLOAD')));
			return;
		}
	}
	
	$insertQuery = "
	UPDATE ".$GLOBALS['prefix_lms']."_course_file 
	SET id_course = '".(int)$_SESSION["idCourse"]."', 
		title = '".$_POST['title']."'";
	if($savefile != '')
		$insertQuery .= ", path = '".$savefile."'";
	$insertQuery .= " WHERE id_file = '".(int)$_POST['id_file']."'";
		
	if(!sql_query($insertQuery)) {
		
		$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
		sl_unlink(_PATH_COURSE.$savefile);
		return;
	}
	$GLOBALS['course_descriptor']->addFileToUsedSpace($GLOBALS['where_files_relative']._PATH_COURSE.$savefile);
	Util::jump_to( 'index.php?modname=course&op=infocourse');
}

function remfiles() {
	checkPerm('mod');
	$lang =& DoceboLanguage::createInstance('course');
	require_once(_base_.'/lib/lib.upload.php');
	
	if( isset($_GET['confirm']) && ($_GET['confirm'] == '1')) {
		
		list($old_file) = sql_fetch_row(sql_query("
		SELECT path 
		FROM ".$GLOBALS['prefix_lms']."_course_file 
		WHERE id_course='".$_SESSION['idCourse']."' AND id_file='".(int)$_GET['id_file']."'"));
		
		$size = Get::file_size($GLOBALS['where_files_relative']._PATH_COURSE.$old_file);
		if(!sl_unlink(_PATH_COURSE.$old_file)) {
			
			$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			return;
		}
		$GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_course_file 
		WHERE id_course = '".(int)$_SESSION['idCourse']."' AND id_file = '".(int)$_GET['id_file']."'")) {
			$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILURE')));
			return;
		}
		
		Util::jump_to( 'index.php?modname=course&op=infocourse');
	} else {
		list($title, $file) = sql_fetch_row(sql_query("
		SELECT title, path 
		FROM ".$GLOBALS['prefix_lms']."_course_file 
		WHERE id_course = '".(int)$_SESSION['idCourse']."' AND id_file = '".(int)$_GET['id_file']."'"));
		
		//request erase confirm
		$GLOBALS['page']->add(
			getTitleArea(
				array('index.php?modname=course&amp;op=infocourse' => $lang->def('_INFO'), $lang->def('_DEL'))
				, 'infocourse')
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'), 
							'<img src="'.getPathImage('fw').mimeDetect($file).'" alt="mime-type" /> '.$title,
							true,
							'index.php?modname=course&amp;op=remfiles&amp;id_file='.(int)$_GET['id_file'].'&amp;confirm=1',
							'index.php?modname=course&amp;op=infocourse'
			)
			.'</div>'
		, 'content');
	}
}

function viewprofile() {
	checkPerm('view_info');
	
	
	$lang =& DoceboLanguage::createInstance('course');
	require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');
	
	$profile = new LmsUserProfile( importVar('id_user', true, 0) );
	$profile->init('profile', 'framework', 'modname=course&op=profile&infocourse', 'ap');
	
	$GLOBALS['page']->add(
		getTitleArea(	array(	'index.php?modname=course&amp;op=infocourse' => $lang->def('_INFO').': '.$GLOBALS['course_descriptor']->getValue('name'),
						$profile->resolveUsername() )
						, 'infocourse')
		.'<div class="std_block">'
		.$profile->performAction()
		.getBackUi('index.php?modname=course&amp;op=infocourse', $lang->def('_BACK'))
		.'</div>'
	, 'content');
}

function infocourseDispatch($op) {
	
	$GLOBALS['page']->setWorkingZone('content');
	
	if(isset($_POST['undo_info'])) $op = 'infocourse';
	
	switch($op) {
		case "newinfocourse" :
		case "modinfocourse" :
		case "reminfocourse" :
	
		case "infocourse" : {
			infocourse();
		};break;
		
		case "viewprofile" : {
			viewprofile();
		};break;
		
		case "downloadcourse" : {
			downloadcourse();
		};break;
		
		case "modcourseinfo" : {
			modcourseinfo();
		};break;
	
		case "upcourseinfo" : {
			upcourseinfo();
		};break;
	
		case "addfiles" : {
			addfiles();
		};break;
		case "insfiles" : {
			insfiles();
		};break;
	
		case "modfiles" : {
			modfiles();
		};break;
		case "upfiles" : {
			upfiles();
		};break;
	
		case "remfiles" : {
			remfiles();
		};break;
	}
}

} elseif (!isset($_SESSION['idCourse'])) {
	errorCommunication($lang->def('_FIRSTACOURSE'));

} else echo "You can't access";

?>