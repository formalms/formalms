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
 * @version  $Id: webpages.php 793 2006-11-21 15:43:19Z fabio $
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

if(Docebo::user()->isAnonymous()) die("You can't access");

function webpages() {
	checkPerm('view');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	
	$mod_perm = checkPerm('mod', true);
	
	$lang	=& DoceboLanguage::createInstance('admin_webpages', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$tb = new Table(0, $lang->def('_WEBPAGES_CAPTION'));
	$nav_bar = new NavBar('ini', Get::sett('visuItem'), 0, 'button');
	$ini = $nav_bar->getSelectedElement();
	
	//search query
	$query_pages = "
	SELECT idPages, title, publish, in_home, sequence 
	FROM ".$GLOBALS['prefix_lms']."_webpages 
	ORDER BY sequence 
	LIMIT $ini,".Get::sett('visuItem');
	
	$num_query_pages = "
	SELECT COUNT(*) 
	FROM ".$GLOBALS['prefix_lms']."_webpages ";
	
	//do query
	$re_pages = sql_query($query_pages);
	list($tot_pages) = sql_fetch_row(sql_query($num_query_pages));
	$nav_bar->setElementTotal($tot_pages);
	
	//-Table---------------------------------------------------------
	$cont_h = array(
		$lang->def('_TITLE'), 
		'<img src="'.getPathImage().'webpages/home.png" alt="'.$lang->def('_ALT_HOME').'" title="'.$lang->def('_TITLE_HOME').'" />',
		'<img src="'.getPathImage().'standard/publish.png" alt="'.$lang->def('_PUBLISH').'" title="'.$lang->def('_STATUS').'" />'	);
	$type_h = array('', 'image', 'image', 'image');
	if($mod_perm) {
		
		$cont_h[] = '<img src="'.getPathImage().'standard/down.png" alt="'.$lang->def('_DOWN').'" title="'.$lang->def('_MOVE_DOWN').'" />';
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage().'standard/up.png" alt="'.$lang->def('_UP').'" title="'.$lang->def('_MOVE_UP').'" />';
		$type_h[] = 'image';
		
		$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />';
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />';
		$type_h[] = 'image';
	}
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);
	
	$i = 1;
	while(list($id, $title, $publish, $in_home) = sql_fetch_row($re_pages)) {
		
		$cont = array(
			$title, 
			( $in_home ? '<img src="'.getPathImage().'webpages/home.png" alt="'.$lang->def('_ALT_HOME').'" title="'.$lang->def('_TITLE_HOME').'" />' : '')
		);
		if($publish) {
			$cont[] = '<a href="index.php?modname=webpages&amp;op=unpublish&amp;id_page='.$id.'" title="'.$lang->def('_PUBLISH').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/publish.png" alt="'.$lang->def('_PUBLISH').' : '.$title.'" /></a>';
		} else {
			$cont[] = '<a href="index.php?modname=webpages&amp;op=publish&amp;id_page='.$id.'" title="'.$lang->def('_UNPUBLISH').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/unpublish.png" alt="'.$lang->def('_UNPUBLISH').' : '.$title.'" /></a>';
		}
		if($mod_perm) {
			if($i != $tot_pages - ($ini * Get::sett('visuItem')) ) {
				$cont[] = '<a href="index.php?modname=webpages&amp;op=movedown&amp;id_page='.$id.'" title="'.$lang->def('_MOVE_DOWN').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/down.png" alt="'.$lang->def('_DOWN').' : '.$title.'" /></a>';
			} else {
				$cont[] = '&nbsp;';
			}
			if($i != 1 || $ini != 0) {
				$cont[] = '<a href="index.php?modname=webpages&amp;op=moveup&amp;id_page='.$id.'" title="'.$lang->def('_MOVE_UP').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/up.png" alt="'.$lang->def('_UP').' : '.$title.'" /></a>';
			} else {
				$cont[] = '&nbsp;';
			}
			
			$cont[] = '<a href="index.php?modname=webpages&amp;op=modpages&amp;id_page='.$id.'" title="'.$lang->def('_MOD').' : '.$title.'">'
					.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$title.'" /></a>';
			
			$cont[] = '<a href="index.php?modname=webpages&amp;op=delpages&amp;id_page='.$id.'" title="'.$lang->def('_DEL').' : '.$title.'">'
					.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$title.'" /></a>';
		}
		
		$tb->addBody($cont);
		++$i;
	}
	if($mod_perm) {
		$tb->addActionAdd( '<a class="ico-wt-sprite subs_add" href="index.php?modname=webpages&amp;op=addpages" title="'.Lang::t('_ADD', 'webpages').'"><span>'
			.Lang::t('_ADD', 'webpages').'</span></a>');
	}
	//visualize result
	$out->add(
		getTitleArea($lang->def('_TITLE_WEBPAGES'), 'webpages')
		.'<div class="std_block">'
	);
	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
			case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
		}
	}
	$out->add(
		$tb->getTable()
		.Form::openForm('nav_webpages', 'index.php?modname=webpages&amp;op=webpages')
		.$nav_bar->getNavBar($ini)
		.Form::closeForm()
		.'</div>');
	if($mod_perm) {
		require_once(_base_.'/lib/lib.dialog.php');
		setupHrefDialogBox('a[href*=delpages]');
	}
}

function editpages($load = false) {
	checkPerm('mod');
	require_once(_base_.'/lib/lib.form.php');
	
	$lang	=& DoceboLanguage::createInstance('admin_webpages', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$all_languages = Docebo::langManager()->getAllLangCode();
	
	$id_page = importVar('id_page', true, 0);
	
	if($load) {
		
		$query_page = "
		SELECT title, description, language, publish, in_home
		FROM ".$GLOBALS['prefix_lms']."_webpages 
		WHERE idPages = '".$id_page."'";
		list($title, $description, $language, $publish, $in_home) = sql_fetch_row(sql_query($query_page));
	} else {
		
		$title			= $lang->def('_NOTITLE');
		$description	= '';
		$language		= getLanguage();
		$publish		= 0;
		$in_home		= 0;
	}
	$page_title = array(
		'index.php?modname=webpages&amp;op=webpages' => $lang->def('_TITLE_WEBPAGES'), 
		( $load ? $lang->def('_MOD') : $lang->def('_ADD_WEBPAGES') )
	);
	$out->add(
		getTitleArea($page_title, 'webpages')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=webpages&amp;op=webpages', $lang->def('_BACK'))
		.Form::openForm('nav_webpages', 'index.php?modname=webpages&amp;op=savepages')
		.Form::openElementSpace()
	);
	if($load) {
		$out->add(Form::getHidden('load', 'load', 1)
				.Form::getHidden('id_page', 'id_page', $id_page) );
	}
	$out->add(
		Form::getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
		.Form::getDropdown($lang->def('_LANGUAGE'), 'language', 'language', $all_languages, array_search($language, $all_languages))
		
		.Form::getCheckbox($lang->def('_PUBLISH'), 'publish', 'publish', 1, $publish)
		.Form::getCheckbox($lang->def('_TITLE_HOME'), 'in_home', 'in_home', 1, $in_home)
		.Form::getTextarea($lang->def('_DESCRIPTION'), 'description', 'description', $description)
		.Form::closeElementSpace()
		
		.Form::openButtonSpace()
		.Form::getButton('save', 'save', $lang->def('_SAVE'))
		.Form::getButton('undo', 'undo', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
	);
}

function savepages() {
	checkPerm('mod');
	
	$lang	=& DoceboLanguage::createInstance('admin_webpages', 'lms');
	$all_languages = Docebo::langManager()->getAllLangCode();
	
	$id_page = importVar('id_page', true, 0);
	
	if($_POST['title'] == '') {
		$_POST['title'] = $lang->def('_NOTITLE');
	}
	$lang_sel = $_POST['language'];
	if(isset($_POST['in_home'])) {
		
		if(!sql_query("UPDATE ".$GLOBALS['prefix_lms']."_webpages SET in_home = 0 
			WHERE in_home = 1 
				AND language = '".$all_languages[$lang_sel]."'")) unset($_POST['in_home']);
	}
	if(isset($_POST['load'])) {

		$query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_webpages
		SET title = '".$_POST['title']."',
			description = '".$_POST['description']."',
			language = '".$all_languages[$lang_sel]."',
			in_home = '".( isset($_POST['in_home']) ? 1 : 0 )."',
			publish = '".( isset($_POST['publish']) ? 1 : 0 )."'
		WHERE idPages = '".$id_page."'";
	} else {
		/**/
		list($seq) = sql_fetch_row(sql_query("
		SELECT MAX(sequence) + 1
		FROM ".$GLOBALS['prefix_lms']."_webpages"));
		
		$query_insert = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_webpages
		( title, description, language, in_home, publish, sequence ) VALUES 
		( 	'".$_POST['title']."',
			'".$_POST['description']."',
			'".$all_languages[$lang_sel]."',
			'".( isset($_POST['in_home']) ? 1 : 0 )."',
			'".( isset($_POST['publish']) ? 1 : 0 )."',
			'".$seq."')";
	}
	if(!sql_query($query_insert)) Util::jump_to( 'index.php?modname=webpages&op=webpages&result=err');
	Util::jump_to( 'index.php?modname=webpages&op=webpages&result=ok');
}

function delpages() {
	checkPerm('mod');
	require_once(_base_.'/lib/lib.form.php');
	
	$lang	=& DoceboLanguage::createInstance('admin_webpages', 'lms');
	$id_page = importVar('id_page', true, 0);
	
	
	list($title, $seq) = sql_fetch_row(sql_query("
	SELECT title, sequence
	FROM ".$GLOBALS['prefix_lms']."_webpages 
	WHERE idPages = '".$id_page."'"));
	
	if(Get::req('confirm', DOTY_INT, 0) == 1) {
		
		$query_delete ="
		DELETE FROM ".$GLOBALS['prefix_lms']."_webpages 
		WHERE idPages = '".$id_page."'";
		
		if(!sql_query($query_delete)) Util::jump_to( 'index.php?modname=webpages&op=webpages&result=err');
		
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_webpages
		SET sequence = sequence -1
		WHERE sequence > '".$seq."'");
		
		Util::jump_to( 'index.php?modname=webpages&op=webpages&result=ok');
	} else {
		
		$form = new Form();
		$page_title = array(
			'index.php?modname=news&amp;op=news' => $lang->def('_NEWS'), 
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'admin_webpages')
			.'<div class="std_block">'
			.$form->openForm('del_pages', 'index.php?modname=webpages&amp;op=delpages')
			.$form->getHidden('id_page', 'id_page', $id_page)
			.getDeleteUi(	$lang->def('_AREYOUSURE'), 
							'<span>'.$lang->def('_TITLE').' : </span>'.$title, 
							false, 
							'confirm',
							'undo'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function publish($id_page, $publish) {
	checkPerm('mod');
	
	if($publish) {
		$query_publish = "
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET publish = 1 
		WHERE idPages = '".$id_page."'";
	} else {
		
		$query_publish = "
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET publish = 0 
		WHERE idPages = '".$id_page."'";
	}
	if(!sql_query($query_publish)) Util::jump_to( 'index.php?modname=webpages&op=webpages&result=err');
	Util::jump_to( 'index.php?modname=webpages&op=webpages&result=ok');
}

function movepages($direction) {
	checkPerm('mod');
	
	$id_page = importVar('id_page', true, 0);
	
	list($seq) = sql_fetch_row(sql_query("
	SELECT sequence
	FROM ".$GLOBALS['prefix_lms']."_webpages 
	WHERE idPages = '".$id_page."'"));
	
	if($direction == 'up') {
		if($seq == 0) return;
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET sequence = '$seq' 
		WHERE sequence = '".($seq - 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET sequence = sequence - 1 
		WHERE idPages = '".$id_page."'");
		
	}
	if($direction == 'down') {
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET sequence = '$seq' 
		WHERE sequence = '".($seq + 1)."'");
		sql_query("
		UPDATE ".$GLOBALS['prefix_lms']."_webpages 
		SET sequence = '".($seq + 1)."' 
		WHERE idPages = '".$id_page."'");
	}
	Util::jump_to( 'index.php?modname=webpages&op=webpages');
}

function webpagesDispatch($op) {
	
	if(isset($_POST['undo'])) $op = 'webpages';
	switch($op) {
		case "webpages" : {
			webpages();
		};break;
		case "addpages" : {
			editpages();
		};break;
		case "savepages" : {
			savepages();
		};break;
		
		case "publish" : {
			publish($_GET['id_page'], true);
		};break;
		case "unpublish" : {
			publish($_GET['id_page'], false);
		};break;
		
		case "movedown" : {
			movepages('down');
		};break;
		case "moveup" : {
			movepages('up');
		};break;
		
		case "modpages" : {
			editpages(true);
		};break;
		case "delpages" : {
			delpages();
		};break;
	}
}

?>