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

// XXX: additem
function additem( $object_item ) {
	//checkPerm('view', false, 'storage');
	$lang =& DoceboLanguage::createInstance('item');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
		.'<div class="std_block">'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_item->back_url).'&amp;create_result=0', $lang->def('_BACK') )
		
		.Form::openForm('itemform', 'index.php?modname=item&amp;op=insitem', 'std_form', 'post', 'multipart/form-data')
		.Form::openElementSpace()
		
		.Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url)))
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 100, $lang->def('_TITLE'))
		.Form::getFilefield($lang->def('_FILE'), 'file', 'attach')
		
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $lang->def('_DESCRIPTION'))
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('additem', 'additem', $lang->def('_INSERT'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function insitem() {
	//checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.upload.php');
	
	$back_url = urldecode($_POST['back_url']);
	
	//scanning title
	if(trim($_POST['title']) == "") $_POST['title'] = Lang::t('_NOTITLE');
	
	//save file
	if($_FILES['attach']['name'] == '') {
		
		$_SESSION['last_error'] = Lang::t('_FILEUNSPECIFIED');
		Util::jump_to( $back_url.'&create_result=0' );
	} else {
		if(isset($_SESSION['idCourse']) && defined("LMS")) {
			$quota = $GLOBALS['course_descriptor']->getQuotaLimit();
			$used = $GLOBALS['course_descriptor']->getUsedSpace();

			if(Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {

				$_SESSION['last_error'] = Lang::t('_QUOTA_EXCEDED');
				Util::jump_to( $back_url.'&create_result=0' );
			}
		}
		$path = '/appLms/'.Get::sett('pathlesson');
		$savefile = ( isset($_SESSION['idCourse']) ? $_SESSION['idCourse'] : '0' ).'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
		$savefile = str_replace("'", "\'", $savefile);//Patch file con apostrofo
		if(!file_exists( $GLOBALS['where_files_relative'].$path.$savefile )) {
			sl_open_fileoperations();
			if(!sl_upload($_FILES['attach']['tmp_name'], $path.$savefile)) {
				sl_close_fileoperations();
				$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
				Util::jump_to( $back_url.'&create_result=0' );
			}
			sl_close_fileoperations();
		} else {
			$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
			Util::jump_to( $back_url.'&create_result=0' );
		}
	}
	
	$insert_query = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_materials_lesson 
	SET author = '".getLogUserId()."',
		title = '".$_POST['title']."',
		description = '".$_POST['description']."',
		path = '$savefile'";
	
	if(!sql_query($insert_query)) {
		sl_unlink($GLOBALS['prefix_lms'].$savefile );
		$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE');
		Util::jump_to( $back_url.'&create_result=0' );
	}
	if(isset($_SESSION['idCourse']) && defined("LMS")) $GLOBALS['course_descriptor']->addFileToUsedSpace($GLOBALS['where_files_relative'].$path.$savefile);
	list($idLesson) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
	Util::jump_to( $back_url.'&id_lo='.$idLesson.'&create_result=1' );
}

//= XXX: edit=====================================================================

function moditem( $object_item ) {
	//checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('item');
	
	$back_coded = htmlentities(urlencode( $object_item->back_url ));
	
	list($title, $description) = sql_fetch_row(sql_query("
	SELECT title, description 
	FROM ".$GLOBALS['prefix_lms']."_materials_lesson 
	WHERE idLesson = '".$object_item->getId()."'"));
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECTIONNAME_ITEM'), 'item')
		.'<div class="std_block">'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_item->back_url).'&amp;mod_result=0', $lang->def('_BACK') )
		
		
		.Form::openForm('itemform', 'index.php?modname=item&amp;op=upitem', 'std_form', 'post', 'multipart/form-data')
		.Form::openElementSpace()
		
		.Form::getHidden('idItem', 'idItem', $object_item->getId())
		.Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_item->back_url)))
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 100, $title)
		.Form::getFilefield($lang->def('_FILE_MOD'), 'file', 'attach')
		
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('additem', 'additem', $lang->def('_SAVE'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function upitem() {
	//checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.upload.php' );
	
	$back_url = urldecode($_POST['back_url']);
	
	//scanning title
	if(trim($_POST['title']) == "") $_POST['title'] = Lang::t('_NOTITLE', 'item', 'lms');
	
	//save file
	if($_FILES['attach']['name'] != '') {
		
		$path = '/appLms/'.Get::sett('pathlesson');
		
		// retrive and delte ld file --------------------------------------------------
		
		list($old_file) = sql_fetch_row(sql_query("
		SELECT path 
		FROM ".$GLOBALS['prefix_lms']."_materials_lesson 
		WHERE idLesson = '".(int)$_POST['idItem']."'"));
		
		$size = Get::file_size($GLOBALS['where_files_relative'].$path.$old_file);
		if(!sl_unlink( $path.$old_file )) {
			
			sl_close_fileoperations();
			$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'item', 'lms');
			Util::jump_to($back_url.'&id_lo='.(int)$_POST['idItem'].'&mod_result=0' );
		}
		$GLOBALS['course_descriptor']->subFileToUsedSpace(false, $size);
		
		// control course quota ---------------------------------------------------

		$quota = $GLOBALS['course_descriptor']->getQuotaLimit();
		$used = $GLOBALS['course_descriptor']->getUsedSpace();
		
		if(Util::exceed_quota($_FILES['attach']['tmp_name'], $quota, $used)) {
				
			$_SESSION['last_error'] = Lang::t('_QUOTA_EXCEDED');
			Util::jump_to( $back_url.'&create_result=0' );
		}
				
		// save new file ------------------------------------------------------------
		
		sl_open_fileoperations();
		$savefile = $_SESSION['idCourse'].'_'.mt_rand(0,100).'_'.time().'_'.$_FILES['attach']['name'];
		if(!file_exists($GLOBALS['where_files_relative'].$path.$savefile )) {
			if(!sl_upload($_FILES['attach']['tmp_name'], $path.$savefile)) {
				
				sl_close_fileoperations();
				$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD', 'item', 'lms');
				Util::jump_to($back_url.'&id_lo='.(int)$_POST['idItem'].'&mod_result=0' );
			}
			sl_close_fileoperations();
		} else {
			
			$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD', 'item', 'lms');
			Util::jump_to($back_url.'&id_lo='.(int)$_POST['idItem'].'&mod_result=0');
		}
		$new_file = ", path = '".$savefile."'";
	}
	
	$insert_query = "
	UPDATE ".$GLOBALS['prefix_lms']."_materials_lesson 
	SET title = '".$_POST['title']."',
		description = '".$_POST['description']."'
		$new_file
	WHERE idLesson = '".(int)$_POST['idItem']."'";
	
	if(!sql_query($insert_query)) {
		sl_unlink($path.$savefile);
		$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURE', 'item', 'lms');
		Util::jump_to($back_url.'&id_lo='.(int)$_POST['idItem'].'&mod_result=0');
	}
	if(isset($_SESSION['idCourse']) && defined("LMS")) {
		$GLOBALS['course_descriptor']->addFileToUsedSpace($GLOBALS['where_files_relative'].$path.$savefile);
		require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
		Track_Object::updateObjectTitle($_POST['idItem'], 'item', $_POST['title']);
	}
	
	Util::jump_to($back_url.'&id_lo='.(int)$_POST['idItem'].'&mod_result=1');
}

//= XXX: switch===================================================================
switch($GLOBALS['op']) {
	
	case "insitem" : {
		insitem();
	};break;
	
	case "upitem" : {
		upitem();
	};break;
}

}

?>