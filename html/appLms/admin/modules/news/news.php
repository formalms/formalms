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
 * @version  $Id: news.php 573 2006-08-23 09:38:54Z fabio $
 * @author	 Fabio Pirovano <fabio [at] docebo [dot] com>
 */

if(Docebo::user()->isAnonymous()) die("You can't access");

function news() {
	checkPerm('view');
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.navbar.php');
	
	$mod_perm	= checkPerm('mod', true);
	$lang 		=& DoceboLanguage::createInstance('admin_news', 'lms');
	$out 		=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$tb	= new Table(Get::sett('visuItem'), $lang->def('_NEWS'), $lang->def('_NEWS_SUMMARY'));
	$nav_bar = new NavBar('ini', Get::sett('visuItem'), 0, 'link');
	
	$ini = $nav_bar->getSelectedElement();
	
	//search query
	$query_news = "
	SELECT idNews, publish_date, title, short_desc, important 
	FROM ".$GLOBALS['prefix_lms']."_news 
	ORDER BY important DESC, publish_date DESC 
	LIMIT $ini,".Get::sett('visuItem');
	
	$query_news_tot = "
	SELECT COUNT(*) 
	FROM ".$GLOBALS['prefix_lms']."_news";
	
	$re_news = sql_query($query_news);
	list($tot_news) = sql_fetch_row(sql_query($query_news_tot));
	
	$nav_bar->setElementTotal($tot_news);
	$impo_gif = '<img src="'.getPathImage('lms').'standard/important.png" '
			.'title="'.$lang->def('_TITLE_IMPORTANT').'" '
			.'alt="'.$lang->def('_IMPORTANT').'" />';
	
	$type_h = array('image', '', '', 'news_short_td');
	$cont_h	= array(
		$impo_gif, 
		$lang->def('_DATE'), 
		$lang->def('_TITLE'), 
		$lang->def('_SHORTDESC')
	);
	if($mod_perm) {
		$cont_h[] = '<img src="'.getPathImage().'standard/edit.png" title="'.$lang->def('_MOD').'" '
						.'alt="'.$lang->def('_MOD').'" />';
		$type_h[] = 'image';
		$cont_h[] = '<img src="'.getPathImage().'standard/delete.png" title="'.$lang->def('_DEL').'" '
						.'alt="'.$lang->def('_DEL').'"" />';
		$type_h[] = 'image';
	}
	
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);
	while(list($id_news, $publish_date, $title, $short_desc, $impo) = sql_fetch_row($re_news)) {
		
		$cont = array(
			( $impo ? $impo_gif : '' ), 
			Format::date($publish_date), 
			$title, 
			Util::cut($short_desc)
		);
		if($mod_perm) {
			$cont[] = '<a href="index.php?modname=news&amp;op=modnews&amp;id_news='.$id_news.'" '
						.'title="'.$lang->def('_MOD').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_MOD').' : '.$title.'" /></a>';
						
			$cont[] = '<a href="index.php?modname=news&amp;op=delnews&amp;id_news='.$id_news.'" '
						.'title="'.$lang->def('_DEL').' : '.$title.'">'
						.'<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_DEL').' : '.$title.'" /></a>';
		}
		$tb->addBody($cont);
	}
	
	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delnews]');
	
	if($mod_perm) {
		$tb->addActionAdd(
			'<a href="index.php?modname=news&amp;op=addnews" title="'.$lang->def('_NEW').'">'
				.'<img src="'.getPathImage().'standard/add.png" alt="'.$lang->def('_ADD').'" />'
				.$lang->def('_NEW').'</a>'
		);
	}
	
	$out->add(getTitleArea($lang->def('_NEWS'), 'news')
			.'<div class="std_block">'	);
	if(isset($_GET['result'])) {
		switch($_GET['result']) {
			case "ok" 		: $out->add(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));break;
			case "err" 		: $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
			case "err_del" : $out->add(getErrorUi($lang->def('_OPERATION_FAILURE')));break;
		}
	}
	if($mod_perm) {
		$form = new Form();
		$how_much = Get::sett('visuNewsHomePage');
		if(isset($_POST['save_homepage'])) {
			
			$query_how_news = "
			UPDATE %adm_setting
			SET param_value = '".abs((int)$_POST['howmuch'])."'
			WHERE param_name = 'visuNewsHomePage'";
			if(sql_query($query_how_news)) $how_much = abs((int)$_POST['howmuch']);
		}
		$out->add(
			$form->openForm('how_much_news', 'index.php?modname=news&amp;op=news')
			.$form->openElementSpace()
			.$form->getTextfield($lang->def('_NEWSINHOMEPAGE'), 'howmuch', 'howmuch', 3, $how_much)
			.$form->closeButtonSpace()
			
			.$form->openButtonSpace()
			.$form->getButton('save_homepage', 'save_homepage', $lang->def('_SAVE'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		);
	}
	$out->add($tb->getTable()
			.$nav_bar->getNavBar($ini)
			.'</div>');
}

function editnews($load = false) {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$lang 		=& DoceboLanguage::createInstance('admin_news', 'lms');
	$form		= new Form();
	$out 		=& $GLOBALS['page'];
	$out->setWorkingZone('content');
	
	$id_news = importVar('id_news', true, 0);
	$all_languages = Docebo::langManager()->getAllLangCode();
	
	if($load) {
		
		$query_news = "
		SELECT title, short_desc, long_desc, important, language 
		FROM ".$GLOBALS['prefix_lms']."_news 
		WHERE idNews = '".$id_news."'";
		list($title, $short_desc, $long_desc, $impo, $lang_sel) = sql_fetch_row(sql_query($query_news));
	} else {
		
		$title =  $lang->def('_NOTITLE');
		$short_desc = '';
		$long_desc = '';
		$impo = 0;
		$lang_sel = getLanguage();
	}
	
	$page_title = array(
		'index.php?modname=news&amp;op=news' => $lang->def('_NEWS'), 
		( $load ? $lang->def('_MOD') : $lang->def('_NEW') )
	);
	$out->add(getTitleArea($page_title, 'news')
			.'<div class="std_block">'
			.getBackUi( 'index.php?modname=news&amp;op=news', $lang->def('_BACK') )
			
			.$form->openForm('adviceform', 'index.php?modname=news&amp;op=savenews')
	);
	if($load) {
		
		$out->add($form->getHidden('id_news', 'id_news', $id_news)
				.$form->getHidden('load', 'load', 1)	);
	}
	$out->add($form->openElementSpace()
			
			.$form->getTextfield($lang->def('_TITLE'), 'title', 'title', 255, $title)
			.$form->getCheckbox($lang->def('_MARK_AS_IMPORTANT'), 'impo', 'impo', 1, $impo)
			.$form->getDropdown($lang->def('_LANGUAGE'), 'language', 'language', $all_languages, array_search($lang_sel, $all_languages))
			
			.$form->getTextarea($lang->def('_SHORTDESC'), 'short_desc', 'short_desc', $short_desc)
			.$form->getTextarea($lang->def('_LONG_DESCR'), 'long_desc', 'long_desc', $long_desc)
			
			.$form->closeElementSpace()
			.$form->openButtonSpace()
			.$form->getButton('news', 'news', ( $load ? $lang->def('_SAVE') : $lang->def('_INSERT') ) )
			.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
			.$form->closeButtonSpace()
			.$form->closeForm()
			.'</div>');
	
}

function savenews() {
	checkPerm('mod');
	
	$id_news 	= importVar('id_news', true, 0);
	$load 		= importVar('load', true, 0);
	$all_languages = Docebo::langManager()->getAllLangCode();
	$lang 		=& DoceboLanguage::createInstance('admin_news', 'lms');
	
	if($_POST['title'] == '') $_POST['title'] = $lang->def('_NOTITLE');
	$lang_sel = $_POST['language'];
	
	if($load == 1) {
		
		$query_insert = "
		UPDATE ".$GLOBALS['prefix_lms']."_news 
		SET	title = '".$_POST['title']."' ,
			short_desc = '".$_POST['short_desc']."' ,
			long_desc = '".$_POST['long_desc']."' ,
			important = '".( isset($_POST['impo']) ? 1 : 0 )."' ,
			language = '".$all_languages[$lang_sel]."'
		WHERE idNews = '".$id_news."'";
		if(!sql_query($query_insert)) Util::jump_to('index.php?modname=news&op=news&result=err');
		Util::jump_to('index.php?modname=news&op=news&result=ok');
	} else {
		
		$query_insert = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_news 
		( title, publish_date, short_desc, long_desc, important, language ) VALUES
		( 	'".$_POST['title']."' ,
			'".date("Y-m-d H:i:s")."', 
			'".$_POST['short_desc']."' ,
			'".$_POST['long_desc']."' ,
			'".( isset($_POST['impo']) ? 1 : 0 )."' ,
			'".$all_languages[$lang_sel]."' )";
			
		if(!sql_query($query_insert)) Util::jump_to('index.php?modname=news&op=news&result=err');
		Util::jump_to('index.php?modname=news&op=news&result=ok');
	}
}

function delnews() {
	checkPerm('mod');
	
	require_once(_base_.'/lib/lib.form.php');
	
	$id_news 	= importVar('id_news', true, 0);
	$lang 		=& DoceboLanguage::createInstance('admin_news', 'lms');
	
	if(Get::req('confirm', DOTY_INT, 0) == 1) {
		
		$query_news = "
		DELETE FROM ".$GLOBALS['prefix_lms']."_news 
		WHERE idNews = '".$id_news."'";
		if(!sql_query($query_news)) Util::jump_to('index.php?modname=news&op=news&result=err_del');
		else Util::jump_to('index.php?modname=news&op=news&result=ok');
	} else {
		
		list($title, $short_desc) = sql_fetch_row(sql_query("
		SELECT title, short_desc
		FROM ".$GLOBALS['prefix_lms']."_news 
		WHERE idNews = '".$id_news."'"));
		
		$form = new Form();
		$page_title = array(
			'index.php?modname=news&amp;op=news' => $lang->def('_NEWS'), 
			$lang->def('_DEL')
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'admin_news')
			.'<div class="std_block">'
			.$form->openForm('del_news', 'index.php?modname=news&amp;op=delnews')
			.$form->getHidden('id_news', 'id_news', $id_news)
			.getDeleteUi(	$lang->def('_AREYOUSURE'), 
							'<span>'.$lang->def('_TITLE').' : </span>'.$title.'<br />'
								.'<span>'.$lang->def('_SHORTDESC').' : </span>'.$short_desc, 
							false, 
							'confirm',
							'undo'	)
			.$form->closeForm()
			.'</div>', 'content');
	}
}

function newsDispatch($op) {
	
	if(isset($_POST['undo'])) $op = 'news';
	switch($op) {
		case "news" : {
			news();
		};break;
		case "addnews" : {
			editnews();
		};break;
		case "modnews" : {
			editnews(true);
		};break;
		case "savenews" : {
			savenews();
		};break;
		case "delnews" : {
			delnews();
		};break;
	}
}

?>