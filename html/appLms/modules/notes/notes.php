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

function notes() {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	$nav_bar = new NavBar('ini', Get::sett('visuItem'), 0 );
	
	$ini = $nav_bar->getSelectedElement();
	$ord = importVar( 'ord' );
	$inv = importVar( 'inv' );

	switch($ord) {
		case "tit" : {
			$ord = $order = 'title';
			if( $inv != 'y' ) $a_down = '&amp;inv=y';
			else {
				$order .= ' DESC';
				$a_down = '';
			}
		};break;
		default : {
			$ord = $order = 'data';
			if( $inv == 'y' ) $a_down = '';
			else {
				$order .= ' DESC';
				$a_down = '&amp;inv=y';
			}
		}
	}
	
	$reNotes = sql_query("
	SELECT idNotes, data, title 
	FROM ".$GLOBALS['prefix_lms']."_notes 
	WHERE owner ='".getLogUserId()."' AND idCourse='".$_SESSION['idCourse']."' 
	ORDER BY $order 
	LIMIT $ini,".Get::sett('visuItem'));
	
	
	list($num_notes) = sql_fetch_row(sql_query("SELECT COUNT(*) 
	FROM ".$GLOBALS['prefix_lms']."_notes 
	WHERE owner ='".getLogUserId()."' AND idCourse='".$_SESSION['idCourse']."' "));
	$nav_bar->setElementTotal($num_notes);
	
	$img_up = '<img class="valing-middle" src="'.getPathImage().'standard/up_arrow.png" alt="'.$lang->def('_UP').'"/>';
	$img_down = '<img class="valing-middle" src="'.getPathImage().'standard/down_arrow.png" alt="'.$lang->def('_DOWN').'"/>';
	$tb = new Table(	Get::sett('visuItem'),
						$lang->def('_NOTES'), 
						$lang->def('_NOTES') );
                        
     //** CR: LR TABLE OF NOTES RESPONSIVE **
     $tb->setTableId("table_note");
     
     $info_forum .='<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
     
                                        #table_note td:nth-of-type(1):before { content: "Data"; }
                                        #table_note td:nth-of-type(2):before { content: "Titolo"; }
                                        #table_note td:nth-of-type(3):before { content: "Modifica"; }
                                        #table_note td:nth-of-type(4):before { content: "Cancella"; }
                                        }        
                                        </style>
                                    ';   
    
    $GLOBALS['page']->add($info_forum,'content');
     
     
     
     
     
     
     //******************************************                   
	
	$contentH = array(
		( $ord == 'data' ? ( $inv == 'y' ? $img_up : $img_down ) : '' )
			.'<a href="index.php?modname=notes&amp;op=notes'.$a_down.'"> '.$lang->def('_DATE').'</a>',
		( $ord == 'title' ? ( $inv == 'y' ? $img_up : $img_down ) : ''  )
			.'<a href="index.php?modname=notes&amp;op=notes&amp;ord=tit'.$a_down.'">'.$lang->def('_TITLE').'</a>',
		'<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" />', 
		'<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" alt="'.$lang->def('_DEL').'" />'
	);	
	$typeH = array('min-cell', '', 'image', 'image');
	$tb->setColsStyle($typeH);
	$tb->addHead($contentH);
	while(list( $idNotes, $data, $title ) = sql_fetch_row($reNotes)) {
		
		$content = array(
			Format::date($data), 
			'<a href="index.php?modname=notes&amp;op=displaynotes&amp;idNotes='.$idNotes.'" title="'.$lang->def('_MORET').'">'.$title.'</a>',
			'<a href="index.php?modname=notes&amp;op=modnotes&amp;idNotes='.$idNotes.'">
				<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" alt="'.$lang->def('_MOD').'" /></a>', 
			'<a id="delnotes_'.$idNotes.'"'
				.' href="index.php?modname=notes&amp;op=delnotes&amp;idNotes='.$idNotes.'"'
				.' title="'.$lang->def('_TITLE').' : '.strip_tags(str_replace(array('"',"'"),'',$title)).'">
				<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" /></a>' );
		$tb->addBody($content);
	}
	$tb->addActionAdd(
		'<a href="index.php?modname=notes&amp;op=addnotes">'
		.'<img src="'.getPathImage().'standard/add.png" title="'.$lang->def('_ADD').'" alt="'.$lang->def('_ADD').'" /> '
		.$lang->def('_ADD_NOTES').'</a>'
	);
	$GLOBALS['page']->add(
		getTitleArea(array($lang->def('_NOTES')), 'notes')
		.'<div class="std_block">', 'content');
	if(isset($_POST['result'])) {
		switch($_POST['result']) {
			case "ok" 	: $GLOBALS['page']->add( getResultUi($lang->def('_OPERATION_SUCCESSFUL')), 'content');
			case "err" 	: $GLOBALS['page']->add( getErrorUi($lang->def('_OPERATION_FAILURE')), 'content');
		}
	}
	$GLOBALS['page']->add(
		$tb->getTable()
		.$nav_bar->getNavBar($ini)
	, 'content');
		
	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delnotes]');
	
	$GLOBALS['page']->add('</div>', 'content');
}

function displaynotes() {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.table.php');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	list($data, $title, $textof) = sql_fetch_row(sql_query("
	SELECT data,title,textof 
	FROM ".$GLOBALS['prefix_lms']."_notes 
	WHERE idNotes='".$_GET['idNotes']."' AND owner ='".getLogUserid()."' and idCourse='".$_SESSION['idCourse']."'"));
	
	$page_title = array(
		'index.php?modname=notes&amp;op=notes' => $lang->def('_NOTES'), 
		$title
	);
	$GLOBALS['page']->add(
		getTitleArea($page_title, 'notes')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=notes&amp;op=notes', $lang->def('_BACK') )
		
		.'<h2>'.$title.'</h2>'
		.'<div class="boxinfo_container">'
		.Format::date($data).'<br /><br />'
		.'<b>'.$lang->def('_TEXTOF').'</b><br />'.$textof.'</div>'
		
		.getBackUi( 'index.php?modname=notes&amp;op=notes', $lang->def('_BACK') )
		.'</div>', 'content');
}

function addnotes() {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	$title_page = array(
		'index.php?modname=notes&amp;op=notes' => $lang->def('_NOTES'), 
		$lang->def('_ADD_NOTES')
	);
	$GLOBALS['page']->add(
		getTitleArea($title_page, 'notes')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=notes&amp;op=notes', $lang->def('_BACK') )
		
		.Form::openForm('formnotes', 'index.php?modname=notes&amp;op=insnotes')
		.Form::openElementSpace()
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $lang->def('_NOTITLE'))
		.Form::getTextarea($lang->def('_TEXTOF'), 'description', 'description')
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('insert', 'insert', $lang->def('_INSERT'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

function insnotes() {
	checkPerm('view');
	
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	if(isset($_POST['undo'])) Util::jump_to( 'index.php?modname=notes&op=notes');
	if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
	
	$query_ins = "
	INSERT INTO ".$GLOBALS ['prefix_lms']."_notes 
	SET owner = '".getLogUserId()."',
		idCourse = '".(int)$_SESSION['idCourse']."',
		data = '".date("Y-m-d H:i:s")."',
		title = '".$_POST['title']."',
		textof = '".$_POST['description']."'";
	
	if(!sql_query($query_ins)) Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=err');
	Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=ok');
}

function modnotes() {
	checkPerm('view');
	
	list($title, $textof) = sql_fetch_row(sql_query("
	SELECT title, textof 
	FROM ".$GLOBALS['prefix_lms']."_notes 
	WHERE  idNotes = '".$_GET['idNotes']."'  AND owner ='".getLogUserId()."' AND idCourse='".$_SESSION['idCourse']."'"));
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	$page_title = array(
		'index.php?modname=notes&amp;op=notes' => $lang->def('_NOTES'),
		$lang->def('_MOD_NOTES')
	);
	
	$GLOBALS['page']->add(
		getTitleArea(array(), 'notes')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=notes&amp;op=notes', $lang->def('_BACK') )
		
		.Form::openForm('formnotes', 'index.php?modname=notes&amp;op=upnotes')
		.Form::openElementSpace()
		.Form::getHidden('idNotes', 'idNotes', $_GET['idNotes'])
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		.Form::getTextarea($lang->def('_TEXTOF'), 'description', 'description', $textof)
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		
		.Form::closeForm()
		.'</div>', 'content' );
}

function upnotes() {
	checkPerm('view');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	if(isset($_POST['undo'])) Util::jump_to( 'index.php?modname=notes&op=notes');
	if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
	
	$query_ins = "
	UPDATE ".$GLOBALS['prefix_lms']."_notes 
	SET data = '".date("Y-m-d H:i:s")."',
		title = '".$_POST['title']."',
		textof = '".$_POST['description']."'
	WHERE idNotes = '".(int)$_POST['idNotes']."' AND owner = '".(int)getLogUserId()."'";
	
	if(!sql_query($query_ins)) Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=err');
	Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=ok');
}

function delnotes() {
	checkPerm('view');
	$lang =& DoceboLanguage::createInstance('notes', 'lms');
	
	if( isset($_GET['confirm']) ) {
		
		$query = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_notes
		WHERE idNotes='".$_GET['idNotes']."' AND owner='".getLogUserId()."' AND idCourse='".$_SESSION['idCourse']."'";
		if(!sql_query($query)) Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=err');
		Util::jump_to( 'index.php?modname=notes&op=notes&amp;result=ok');
	}
	else {
		
		list($title) = sql_fetch_row(sql_query("
		SELECT title
		FROM ".$GLOBALS['prefix_lms']."_notes 
		WHERE owner = '".getLogUserId()."' AND idNotes = '".(int)$_GET['idNotes']."'"));
		
		$title_page = array(
			'index.php?modname=notes&amp;op=notes' => $lang->def('_NOTES'), 
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add( 
			getTitleArea($title_page, 'notes')
			.'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_TITLE').' : </span>'.$title,
							true, 
							'index.php?modname=notes&amp;op=delnotes&amp;idNotes='.$_GET['idNotes'].'&amp;confirm=1',
							'index.php?modname=notes&amp;op=notes' )
			.'</div>', 'content');
	}
}

function notesDispatch($op) {

switch($op) {
	case "notes" : {
		notes();
	};break;
	case "displaynotes" : {
		displaynotes();
	};break;
	
	case "addnotes" : {
		addnotes();
	};break;
	case "insnotes" : {
		insnotes();
	};break;
	
	case "modnotes" : {
		modnotes();
	};break;
	case "upnotes" : {
		upnotes();
	};break;
	
	case "delnotes" :  {
		delnotes();
	};break;
}
}

}

?>