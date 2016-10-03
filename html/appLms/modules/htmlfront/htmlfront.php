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

if(Docebo::user()->isAnonymous()) die('You cannot access as anonymous');

function showhtml() {
	checkPerm('view');
	
	$lang =& DoceboLanguage::createInstance('htmlfront', 'lms');
	
	$query = "
	SELECT textof
	FROM ".$GLOBALS['prefix_lms']."_htmlfront 
	WHERE id_course = '".$_SESSION['idCourse']."'";
	$re_htmlfront = sql_query($query);
	list($textof) = sql_fetch_row($re_htmlfront);
	
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_HTMLFRONT'), 'htmlfront')
		.'<div class="std_block">'
		.( isset($_GET['saveok']) 
			? getResultUi($lang->def('_OPERATION_SUCCESSFUL'))
			: '' )
		.'<div class="htmlfront_container">'
		.$textof
		.'</div>'
		
		.( checkPerm('mod', true) 
			? '<p class="table-container-below">'
				.'<a class="infomod" href="index.php?modname=htmlfront&amp;op=edithtml" title="'.$lang->def('_MOD').'">'
				.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" />&nbsp;'
				.$lang->def('_MOD').'</a></p>'
			: '' )
		.'</div>', 'content');
}

function edithtml() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$query = "
	SELECT textof
	FROM ".$GLOBALS['prefix_lms']."_htmlfront 
	WHERE id_course = '".$_SESSION['idCourse']."'";
	$re_htmlfront = sql_query($query);
	
	$error = false;
	if(isset($_POST['save'])) {
		
		if(sql_num_rows($re_htmlfront) > 0) {
			
			$upd_query = "
			UPDATE ".$GLOBALS['prefix_lms']."_htmlfront 
			SET textof = '".$_POST['description']."'
			WHERE id_course = '".$_SESSION['idCourse']."'";
			$re = sql_query($upd_query);
		} else {
			
			$ins_query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_htmlfront 
			( id_course, textof) VALUES 
			( 	'".$_SESSION['idCourse']."',
				'".$_POST['description']."' )";
			$re = sql_query($ins_query);
		}
		if($re) Util::jump_to('index.php?modname=htmlfront&amp;op=showhtml&amp;saveok=1');
		else $error = true;
	}
	
	$lang =& DoceboLanguage::createInstance('htmlfront', 'lms');
	
	list($textof) = sql_fetch_row($re_htmlfront);
	
	$title_page = array(
		'index.php?modname=htmlfront&amp;op=showhtml' => $lang->def('_HTMLFRONT'), 
		$lang->def('_MOD')
	);
	$GLOBALS['page']->add(
		getTitleArea($title_page, 'htmlfront')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=htmlfront&amp;op=showhtml', $lang->def('_BACK') )
		.( $error 
			? getErrorUi($lang->def('_ERROR_IN_SAVE'))
			: '' )
		.Form::openForm('formnotes', 'index.php?modname=htmlfront&amp;op=edithtml')
		.Form::openElementSpace()
		.Form::getTextarea($lang->def('_TEXTOF'), 'description', 'description', 
			importVar('description', false, $textof) )
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// dispatch function ================================================== //

function htmlfrontDispatch($op) {
	
	if(isset($_POST['undo'])) $op= 'showhtml';
	
	switch($op) {
		case "showhtml" : showhtml(); break;
		case "edithtml" : edithtml(); break;
	}
}

?>