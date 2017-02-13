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

// XXX: modfaqgui
function modfaqgui( $object_faq ) {
	checkPerm('view', false, 'storage');
	$del_perm = checkPerm('view', true, 'storage');
	
	require_once(_base_.'/lib/lib.table.php');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$back_coded = htmlentities(urlencode($object_faq->back_url));
	
	$textQuery = "
	SELECT idFaq, question, sequence 
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idCategory = '".$object_faq->getId()."' 
	ORDER BY sequence";
	$result = sql_query($textQuery);
	$num_faq = sql_num_rows($result);
	
	list($title_cat) = sql_fetch_row(sql_query("
	SELECT title
	FROM ".$GLOBALS['prefix_lms']."_faq_cat 
	WHERE idCategory = '".$object_faq->getId()."'"));
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_FAQ'), 'faq')
		.'<div class="std_block">'		
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_faq->back_url).'&amp;mod_result=0', $lang->def('_BACK') )
		
		.'<b>'.$lang->def('_TITLE').' : '.$title_cat.'</b><br /><br />'
		.'<div class="mod_container">'
		.'<a href="index.php?modname=faq&amp;op=modfaqcat&amp;idCategory='.$object_faq->getId()
			.'&amp;back_url='.$back_coded.'" title="'.$lang->def('_MOD').'">'
		.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" /> '.$lang->def('_MOD').'</a>'
		.'</div><br />', 'content');
	$tableCat = new Table(0, '', $lang->def('_SUMMARY_FAQ'));
	
	$contentH = array($lang->def('_QUESTION'),
		'<img src="'.getPathImage().'standard/down.png" alt="'.$lang->def('_DOWN').'" title="'.$lang->def('_MOVE_DOWN').'" />',
		'<img src="'.getPathImage().'standard/up.png" alt="'.$lang->def('_UP').'" title="'.$lang->def('_MOVE_UP').'" />',
		'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />', 
		'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />'
	);
	$typeH = array('', 'image', 'image', 'image', 'image');
	
	$tableCat->setColsStyle($typeH);
	$tableCat->addHead($contentH);
	$i = 1;
	while(list($idFaq, $title, $seq) = sql_fetch_row($result)) {
		$rowContent = array($seq.') '.$title);
		if($i != $num_faq) {
			$rowContent[] = '<a href="index.php?modname=faq&amp;op=movedown&amp;idFaq='
				.$idFaq.'&amp;back_url='.$back_coded.'">'
				.'<img src="'.getPathImage().'standard/down.png" alt="'.$lang->def('_DOWN').'" title="'.$lang->def('_MOVE_DOWN').'" /></a>';
		}
		else $rowContent[] = '&nbsp;';
		if($i != 1){
			$rowContent[] = '<a href="index.php?modname=faq&amp;op=moveup&amp;idFaq='
				.$idFaq.'&amp;back_url='.$back_coded.'">'
				.'<img src="'.getPathImage().'standard/up.png" alt="'.$lang->def('_UP').'" title="'.$lang->def('_MOVE_UP').'" /></a>';
		}
		else $rowContent[] = '&nbsp;';
		
		$rowContent[] = '<a href="index.php?modname=faq&amp;op=modfaq&amp;idFaq='
			.$idFaq.'&amp;back_url='.$back_coded.'">'
			.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" /></a>';
		$rowContent[] = '<a href="index.php?modname=faq&amp;op=delfaq&amp;idFaq='
				.$idFaq.'&amp;back_url='.$back_coded.'">'
				.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" /></a>';
		
		$tableCat->addBody($rowContent);
		++$i;
	}
	$tableCat->addActionAdd('<a href="index.php?modname=faq&amp;op=newfaq&amp;idCategory='
		.$object_faq->getId().'&amp;back_url='.$back_coded.'" title="'.$lang->def('_ADD').'">'
		.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" /> '.$lang->def('_ADDFAQ').'</a>');
	
	$GLOBALS['page']->add(
		$tableCat->getTable()
		.'<span class="text_bold text_little">[ '
		.'<a href="index.php?modname=faq&amp;op=fixsequence&amp;idCategory='.$object_faq->getId()
			.'&amp;back_url='.$back_coded.'">'.$lang->def('_FIX_SEQUENCE').'</a>'
		.' ]</span>'
		.'</div>', 'content');
}

// XXX: addfaqcat
function addfaqcat( $object_faq ) {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_SECT_FAQ'), 'faq')
		.'<div class="std_block">'
		
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_faq->back_url).'&amp;create_result=0', $lang->def('_BACK') )
		
		.Form::openForm('faqform', 'index.php?modname=faq&amp;op=insfaqcat')
		.Form::openElementSpace()
		.Form::getHidden('back_url', 'back_url', htmlentities(urlencode($object_faq->back_url)))
		
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $lang->def('_TITLE'))
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $lang->def('_DESCRIPTION'))
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addfaq', 'addfaq', $lang->def('_INSERT'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// XXX: insfaqcat
function insfaqcat() {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode($_POST['back_url']);
	
	$query_ins = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_faq_cat
	SET title = '".( (trim($_POST['title']) == '') ? Lang::t('_NOTITLE', 'faq', 'lms') : $_POST['title'])."',
		description = '".$_POST['description']."',
		author = '".(int)getLogUserId()."'";
	if(!sql_query($query_ins)) {
		
		$_SESSION['last_error'] = Lang::t('_OPERATION_FAILURECAT', 'faq', 'lms');
		Util::jump_to($back_url.'&create_result=0');
	}
	
	list($idFaq) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));
	Util::jump_to($back_url.'&id_lo='.$idFaq.'&create_result=1');
}

// XXX: modfaqcat
function modfaqcat() {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$idCategory = importVar('idCategory', true, 0);
	$back_url = urldecode(importVar('back_url'));
	$back_coded = htmlentities(urlencode($back_url));
	
	list($title, $descr) = sql_fetch_row(sql_query("
	SELECT title, description
	FROM ".$GLOBALS['prefix_lms']."_faq_cat 
	WHERE idCategory = '".$idCategory."'"));
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_FAQ'), 'faq')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=faq&amp;op=modfaqgui&amp;idCategory='
			.$idCategory.'&amp;back_url='.$back_coded, $lang->def('_BACK'))
		
		.Form::openForm('faqform', 'index.php?modname=faq&amp;op=upfaqcat')
		.Form::openElementSpace()
		.Form::getHidden('idCategory', 'idCategory', $idCategory)
		.Form::getHidden('back_url', 'back_url', $back_coded)
		
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $descr)
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addfaq', 'addfaq', $lang->def('_SAVE'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// XXX: upfaqcat
function upfaqcat() {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode(importVar('back_url'));
	$back_coded = htmlentities(urlencode($back_url));
	
	$query_ins = "
	UPDATE ".$GLOBALS['prefix_lms']."_faq_cat
	SET title = '".( (trim($_POST['title']) == '') ? Lang::t('_NOTITLE', 'faq') : $_POST['title'])."',
		description = '".$_POST['description']."' 
	WHERE idCategory = '".(int)$_POST['idCategory']."'";
	if(!sql_query($query_ins)) {
		
		$GLOBALS['page']->add(getBackUi(def('_OPERATION_FAILURECAT', 'faq', 'lms').getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
					.(int)$_POST['idCategory'].'&back_url='.$back_coded, Lang::t('_BACK', 'faq', 'lms'))), 'content');
		return;
	}
	
	require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
	Track_Object::updateObjectTitle($_POST['idCategory'], 'faq', ( (trim($_POST['title']) == '') ? Lang::t('_NOTITLE', 'faq') : $_POST['title']));
	
	Util::jump_to('index.php?modname=faq&op=modfaqgui&idCategory='.(int)$_POST['idCategory'].'&back_url='.$back_coded);
}

// XXX: movefaq
function movefaq($direction) {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode($_GET['back_url']);
	$back_coded = htmlentities(urlencode( $back_url ));
	
	list($idCategory, $seq) = sql_fetch_row(sql_query("
	SELECT idCategory, sequence
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idFaq = '".(int)$_GET['idFaq']."'"));
	
	if($direction == 'up') {
		if($seq == 0) return;
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = '$seq' 
		WHERE idCategory = '".$idCategory."' AND sequence = '".($seq - 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = sequence - 1 
		WHERE idFaq = '".(int)$_GET['idFaq']."'");
		
	}
	if($direction == 'down') {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = '$seq' 
		WHERE idCategory = '".$idCategory."' 
			AND sequence = '".($seq + 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = '".($seq + 1)."' 
		WHERE idFaq = '".(int)$_GET['idFaq']."'");
	}
	Util::jump_to( 'index.php?modname=faq&op=modfaqgui&idCategory='.$idCategory.'&back_url='.$back_coded);
}

// XXX: fixsequence
function fixsequence() {
	checkPerm('view', false, 'storage');
	
	$back_url = urldecode( $_GET['back_url'] );
	$back_coded = htmlentities(urlencode( $back_url ));
	
	$reQuest = sql_query("
	SELECT idFaq
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idCategory = '".(int)$_GET['idCategory']."'
	ORDER BY sequence");
	
	$i = 1;
	while(list($idFaq) = sql_fetch_row($reQuest)) {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = '".($i++)."' 
		WHERE idFaq = '$idFaq'");
	}
	
	Util::jump_to('index.php?modname=faq&op=modfaqgui&idCategory='.$_GET['idCategory'].'&back_url='.$back_coded);
}

// XXX: newfaq
function newfaq() {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$idCategory = importVar('idCategory', true, 0);
	$back_url = urldecode(importVar('back_url'));
	$back_coded = htmlentities(urlencode($back_url));
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_FAQ'), 'faq')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
			.$idCategory.'&back_url='.$back_coded, $lang->def('_BACK'))
		
		.Form::openForm('faqform', 'index.php?modname=faq&amp;op=insfaq')
		.Form::openElementSpace()
		.Form::getHidden('idCategory', 'idCategory', $idCategory)
		.Form::getHidden('back_url', 'back_url', $back_coded)
		
		.Form::getTextfield($lang->def('_QUESTION'), 'question', 'question', 255, $lang->def('_QUESTION'))
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $lang->def('_TITLE'))
		
		.Form::getSimpleTextarea($lang->def('_KEYWORD'), 'keyword', 'keyword', $lang->def('_KEYWORD'))
		
		.Form::getTextarea($lang->def('_ANSWER'), 'answer', 'answer', $lang->def('_ANSWER'))
		
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addsinglefaq', 'addsinglefaq', $lang->def('_INSERT'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// XXX: insfaq
function insfaq() {
	checkPerm('view', false, 'storage');
	
	$idCategory = importVar('idCategory', true, 0);
	$back_url = urldecode(importVar('back_url'));
	$back_coded = htmlentities(urlencode($back_url));
	
	list($seq) = sql_fetch_row(sql_query("
	SELECT MAX(sequence)
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idCategory = '".$idCategory."'"));
	
	$query_ins = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_faq
	SET idCategory = '".$idCategory."',
		question = '".$_POST['question']."',
		title = '".$_POST['title']."',
		keyword = '".$_POST['keyword']."',
		answer = '".$_POST['answer']."',
		sequence = '".($seq + 1)."'";
	if(!sql_query($query_ins)) {
		$GLOBALS['page']->add(getErrorUi(def('_OPERATION_FAILURE', 'faq').getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
			.$idCategory.'&back_url='.$back_coded, Lang::t('_BACK'))), 'content');
		return;
	}
	Util::jump_to( 'index.php?modname=faq&op=modfaqgui&idCategory='.$idCategory.'&back_url='.$back_coded);
}

// XXX: modfaq
function modfaq() {
	checkPerm('view', false, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$back_url = urldecode($_GET['back_url']);
	$back_coded = htmlentities(urlencode( $back_url ));
	
	list($idCategory, $question, $title, $keyword, $answer) = sql_fetch_row(sql_query("
	SELECT idCategory, question, title, keyword, answer 
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idFaq = '".(int)$_GET['idFaq']."'"));
	
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_FAQ'), 'faq')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
			.$idCategory.'&back_url='.$back_coded, $lang->def('_BACK'))
		
		.Form::openForm('faqform', 'index.php?modname=faq&amp;op=upfaq')
		.Form::openElementSpace()
		.Form::getHidden('idFaq', 'idFaq', $_GET['idFaq'])
		.Form::getHidden('back_url', 'back_url', $back_coded)
		
		.Form::getTextfield($lang->def('_QUESTION'), 'question', 'question', 255, $question)
		.Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		
		.Form::getSimpleTextarea($lang->def('_KEYWORD'), 'keyword', 'keyword', $keyword)
		
		.Form::getTextarea($lang->def('_ANSWER'), 'answer', 'answer', $answer)
		
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('addsinglefaq', 'addsinglefaq', $lang->def('_SAVE'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>', 'content');
}

// XXX: upfaq
function upfaq() {
	checkPerm('view', false ,'storage');
	
	$back_url = urldecode($_POST['back_url']);
	$back_coded = htmlentities(urlencode( $back_url ));	
	
	list($idCategory) = sql_fetch_row(sql_query("
	SELECT idCategory 
	FROM ".$GLOBALS['prefix_lms']."_faq 
	WHERE idFaq = '".(int)$_POST['idFaq']."'"));
	
	$query_ins = "
	UPDATE ".$GLOBALS['prefix_lms']."_faq
	SET question = '".$_POST['question']."',
		title = '".$_POST['title']."',
		keyword = '".$_POST['keyword']."',
		answer = '".$_POST['answer']."'
	WHERE idFaq = '".(int)$_POST['idFaq']."'";
	if(!sql_query($query_ins)) {
		$GLOBALS['page']->add(getErrorUi(def('_OPERATION_FAILURE', 'faq').getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
			.$idCategory.'&back_url='.$back_coded, Lang::t('_BACK'))), 'content');
		return;
	}
	Util::jump_to( 'index.php?modname=faq&op=modfaqgui&idCategory='.$idCategory.'&back_url='.$back_coded);
}

// XXX: delfaq
function delfaq() {
	checkPerm('view', false ,'storage');
	$lang =& DoceboLanguage::createInstance('faq');
	
	$back_url = urldecode($_GET['back_url']);
	$back_coded = htmlentities(urlencode( $back_url ));	
	
	$GLOBALS['page']->add(getTitleArea($lang->def('_SECT_FAQ'), 'faq'), 'content');
	if( isset($_GET['confirm']) ) {
		list($idCategory, $seq) = sql_fetch_row(sql_query("
		SELECT idCategory, sequence 
		FROM ".$GLOBALS['prefix_lms']."_faq 
		WHERE idFaq = '".(int)$_GET['idFaq']."'"));
		
		if(!sql_query("
		DELETE FROM ".$GLOBALS['prefix_lms']."_faq 
		WHERE idFaq  = '".(int)$_GET['idFaq']."'")) {
			$GLOBALS['page']->add(getErrorUi($lang->def('_OPERATION_FAILUREFAQ').getBackUi('index.php?modname=faq&op=modfaqgui&idCategory='
				.$idCategory.'&back_url='.$back_coded, $lang->def('_BACK'))), 'content');
			return;
		}
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_faq 
		SET sequence = sequence -1
		WHERE sequence > '".$seq."'");
		
		Util::jump_to( 'index.php?modname=faq&op=modfaqgui&idCategory='.$idCategory.'&back_url='.$back_coded);
	}
	else {
		list($idCategory, $question, $answer) = sql_fetch_row(sql_query("
		SELECT idCategory, question, answer 
		FROM ".$GLOBALS['prefix_lms']."_faq 
		WHERE idFaq = '".(int)$_GET['idFaq']."'"));
		
		$GLOBALS['page']->add(
			'<div class="std_block">'
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span>'.$lang->def('_QUESTION').' : </span>'.$question.'<br />'
								.'<span>'.$lang->def('_ANSWER').' : </span>'.$answer, 
							true,
							'index.php?modname=faq&amp;op=delfaq&amp;idFaq='.$_GET['idFaq'].'&amp;back_url='.$back_coded.'&amp;confirm=1', 
							'index.php?modname=faq&amp;op=modfaqgui&amp;idCategory='.$idCategory.'&amp;back_url='.$back_coded.'">' )
			.'</div>'
			.'</div>', 'content');
	}
}

// XXX: switch
if(isset($GLOBALS['op'])) switch($GLOBALS['op']) {
	case "modfaqgui" : {
		$idCategory = importVar('idCategory', true, 0);
		$back_url = importVar('back_url');
		
		$object_faq= createLO( 'faq', $idCategory );
		$object_faq->edit( $idCategory, urldecode( $back_url ) );
	};break;
	//add category
	case "addfaqcat" : {
		addfaqcat();
	};break;
	case "insfaqcat" : {
		insfaqcat();
	};break;
	//mod category
	case "modfaqcat" : {
		modfaqcat();
	};break;
	case "upfaqcat" : {
		upfaqcat();
	};break;
	//mod
	case "movedown" : {
		movefaq('down');
	};break;
	case "moveup" : {
		movefaq('up');
	};break;
	case "fixsequence" : {
		fixsequence();
	};break;
	//add faq
	case "newfaq" : {
		newfaq();
	};break;
	case "insfaq" : {
		insfaq();
	};break;
	//mod faq
	case "modfaq" : {
		modfaq();
	};break;
	case "upfaq" : {
		upfaq();
	};break;
	//del faq
	case "delfaq" : {
		delfaq();
	};break;
}

}

?>
