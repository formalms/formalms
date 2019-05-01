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

function loadWebPage() {

	//load info
	if(isset($_GET['idPages'])) {
		$textQuery = "
		SELECT title, description, publish
		FROM ".$GLOBALS['prefix_lms']."_webpages
		WHERE idPages  = '".(int)$_GET['idPages']."'";
	} else {
		$textQuery = "
		SELECT title, description, publish
		FROM ".$GLOBALS['prefix_lms']."_webpages
		WHERE in_home  = '1'";
	}
	list($title, $description, $publish) = sql_fetch_row(sql_query($textQuery));

	$GLOBALS['page']->add('<li><a href="#home_page">'. Lang::t('_JUMP').' : '.$title.'</a></li>', 'blind_navigation');

	$GLOBALS['page']->add(
		 '<div class="home_block">'
		 .'<h1 id="home_page">'.$title .'</h1>'
		 .'<div class="home_textof">'.$description.'</div>'
		.'</div>', 'content' );
}

function loadNews() {

	if(Get::sett('visuNewsHomePage') == '0') return;
	if(Get::sett('activeNews') == 'off') return;
	$textQuery = "
	SELECT idNews, publish_date, title, short_desc
	FROM ".$GLOBALS['prefix_lms']."_news
	WHERE language = '".getLanguage()."'
	ORDER BY important DESC, publish_date DESC
	LIMIT 0,".Get::sett('visuNewsHomePage');

	$lang = DoceboLanguage::createInstance('login');

	$GLOBALS['page']->add('<li><a href="#home_page">'.$lang->def('_JUMP_TO').' : '.$lang->def('_NEWS').'</a></li>', 'blind_navigation');

	$GLOBALS['page']->add(
		'<div class="news_block">'
		.'<h1>'.$lang->def('_NEWS').'</h1>'
		.'<div class="news_list">', 'content');

	//do query
	$result = sql_query($textQuery);
	while( list($idNews, $publish_date, $title, $short_desc) = sql_fetch_row($result)) {

		$GLOBALS['page']->add(
			'<h2><a href="index.php?modname=login&amp;op=readnews&amp;idNews='.$idNews.'">'.$title.'</a></h2>'
			.'<p class="news_textof">'
			.'<span class="news_data">'.$lang->def('_DATE').' '
				.Format::date($publish_date).' - </span>'
				.$short_desc
			.'</p>', 'content' );
	}
	$GLOBALS['page']->add(
		'</div>'
		.'</div>', 'content');
}

function news() {

	$textQuery = "
	SELECT idNews, publish_date, title, short_desc
	FROM ".$GLOBALS['prefix_lms']."_news
	WHERE language = '".getLanguage()."'
	ORDER BY important DESC, publish_date DESC";

	$lang = DoceboLanguage::createInstance('login');

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_NEWS'), 'news', $lang->def('_NEWS'))
		.'<div class="news_block">'
		.getBackUi( 'index.php', $lang->def('_BACK') ), 'content');

	//do query
	$result = sql_query($textQuery);
	while( list($idNews, $publish_date, $title, $short_desc) = sql_fetch_row($result)) {

		$GLOBALS['page']->add(
			'<div class="news_title">'
			.'<a href="index.php?modname=login&amp;op=readnews&amp;idNews='.$idNews.'">'.$title.'</a></div>'
			.'<div class="news_textof">'
			.'<span class="news_data">'.$lang->def('_DATE').' '.$publish_date.' - </span>'
			.$short_desc
			.'</div>', 'content');
	}
	if(sql_num_rows($result) == 0) {
		$GLOBALS['page']->add( $lang->def('_NO_CONTENT'), 'content');
	} elseif(sql_num_rows($result) >= 3) {
		$GLOBALS['page']->add( getBackUi( 'index.php', $lang->def('_BACK') ).'</div>', 'content');
	}
	$GLOBALS['page']->add('</div>', 'content');
}

function readnews() {

	$textQuery = "
	SELECT publish_date, title, long_desc
	FROM ".$GLOBALS['prefix_lms']."_news
	WHERE idNews = '".$_GET['idNews']."'";
	//do query
	$result = sql_query($textQuery);
	list($publish_date, $title, $long_desc) = sql_fetch_row($result);

	$l_login = DoceboLanguage::createInstance('login');
	$l_std = DoceboLanguage::createInstance('standard');

	$GLOBALS['page']->add(
		getTitleArea($l_login->def('_NEWS'), 'news', $l_login->def('_NEWS'))
		.'<div class="news_block">'
		.getBackUi( 'index.php', $l_std->def('_BACK') )
		.'<div class="news_title_reading">'.$title.'</div>'
		.'<div class="news_textof">'
		.'<span class="news_data">'.$l_login->def('_DATE').' '.$publish_date.'</span><br />'
		.$long_desc
		.'</div>'
		.getBackUi( 'index.php?modname=login&amp;op=news', $l_std->def('_BACK') )
		.'</div>', 'content');
}

// XXX: lostpwd
function lostpwd() {

	require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));

	$lang = DoceboLanguage::createInstance('login');
	$user_manager = new UserManager();

	$GLOBALS['page']->add( getTitleArea($lang->def('_LOGIN'), 'login')
		.'<div class="std_block">'
		.getBackUi( 'index.php', $lang->def('_BACK') ), 'content');
	if($user_manager->haveToLostpwdConfirm()) {

		$GLOBALS['page']->add($user_manager->performLostpwdConfirm(), 'content');
	}
	if($user_manager->haveToLostpwdAction()) {

		$GLOBALS['page']->add($user_manager->performLostpwdAction('index.php?modname=login&amp;op=lostpwd'), 'content');
	}
	if($user_manager->haveToLostpwdMask()) {

		$GLOBALS['page']->add($user_manager->getLostpwdMask('index.php?modname=login&amp;op=lostpwd'), 'content');
	}
	$GLOBALS['page']->add( '</div>', 'content');
}


function register() {

	require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
	require_once(Forma::inc(_base_ . '/lib/lib.form.php'));

	$user_manager = new UserManager();

	$link = 'http://'.$_SERVER['HTTP_HOST']
    		.( strlen(dirname($_SERVER['PHP_SELF'])) != 1 ? dirname($_SERVER['PHP_SELF']) : '' )
			. Get::rel_path("base") . "/index.php?r=" . _signup_;

	$GLOBALS['page']->add(
		getTitleArea(Lang::t('_REGISTER', 'register', 'lms'), 'register')
		.'<div class="std_block">'
		.getBackUi( 'index.php', Lang::t('_BACK', 'standard') )
		.Form::openForm('login_confirm_form', Get::rel_path("base") . "/index.php?r=" . _register_, false, false, 'multipart/form-data')
		.$user_manager->getRegister($link)
		.Form::closeForm()
		.'</div>', 'content');
}

function register_confirm() {

	require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
	require_once(Forma::inc(_base_ . '/lib/lib.form.php'));

	$user_manager = new UserManager();

	$GLOBALS['page']->add(
		getTitleArea(Lang::t('_REGISTER', 'register', 'lms'), 'register')
		.'<div class="std_block">'
		.$user_manager->confirmRegister()
		.'<br/><a href="./index.php">&lt;&lt; '. Lang::t('_GOTO_LOGIN', 'register', 'lms').'</a>'
		.'</div>', 'content');
}

function login_coursecatalogueJsSetup() {

	YuiLib::load(array(
		'animation' 		=> 'animation-min.js',
		'dragdrop' 			=> 'dragdrop-min.js',
		'button' 			=> 'button-min.js',
		'container' 		=> 'container-min.js',
		'my_window' 		=> 'windows.js'
	), array(
		'container/assets/skins/sam' => 'container.css',
		'button/assets/skins/sam' => 'button.css'
	));

	addCss('style_course_list', 'lms');
	addJs($GLOBALS['where_lms_relative'].'/modules/coursecatalogue/', 'ajax.coursecatalogue.js');
	//addCss('style_yui_docebo', 'lms');
	$GLOBALS['page']->add('<script type="text/javascript"> server_location = "'.$GLOBALS['where_lms_relative'].'/"; </script>', 'content');
}

function externalCourselist() {

	require_once($GLOBALS['where_lms'].'/modules/coursecatalogue/lib.coursecatalogue.php');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.user_profile.php');
	require_once(_base_.'/lib/lib.navbar.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.catalogue.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('login');
	$url->setStdQuery('modname=login&op=courselist');

	addCss('style_tab', 'lms');
	login_coursecatalogueJsSetup();

	$GLOBALS['page']->add(
	'<!--[if lt IE 7.]>
		<script type="text/javascript" src="'.$GLOBALS['where_framework_relative'].'/lib/lib.pngfix.js"></script>
	<![endif]-->', 'page_head');

	$lang 	=& DoceboLanguage::createInstance('catalogue');
	$lang_c =& DoceboLanguage::createInstance('course');

	// list of tab ---------------------------------------------------------------------------
	$tab_list = array(
		'time' 		=> $lang->def('_TAB_VIEW_TIME'),
		'category' 	=> $lang->def('_TAB_VIEW_CATEGORY'),
		'all' 		=> $lang->def('_ALL')
	);
	if(Get::sett('use_coursepath') == '1') {
		$tab_list['pathcourse'] = $lang->def('_COURSEPATH');
	}
	if(Get::sett('use_social_courselist') == 'on') {
		$tab_list['mostscore'] 	= $lang->def('_TAB_VIEW_MOSTSCORE');
		$tab_list['popular'] 	= $lang->def('_TAB_VIEW_MOSTPOPULAR');
		$tab_list['recent'] 	= $lang->def('_TAB_VIEW_RECENT');
	}
	$tab_selected = Util::unserialize(urldecode(Get::sett('tablist_coursecatalogue')));
	foreach($tab_list as $tab_code => $v) {
		if(!isset($tab_selected[$tab_code])) unset($tab_list[$tab_code]);
	}
	reset($tab_list);

	// tab selected for courses -------------------------------------------------------------
	$first_coursecatalogue_tab = Get::sett('first_coursecatalogue_tab', key($tab_list));
	if(!isset($tab_list[$first_coursecatalogue_tab])) $first_coursecatalogue_tab = key($tab_list);

	if(isset($_GET['tab']) || isset($_POST['tab'])) {
		$selected_tab = $_SESSION['cc_tab'] = Get::req('tab', DOTY_MIXED, $first_coursecatalogue_tab);
	}
	elseif(isset($_SESSION['cc_tab'])) $selected_tab = $_SESSION['cc_tab'];
	else $selected_tab = $first_coursecatalogue_tab;

	$GLOBALS['page']->add(
		'<div id="coursecatalogue_tab_container">'
		.'<ul class="flat_tab">', 'content');
	foreach($tab_list as $key => $tab_name) {

		$GLOBALS['page']->add('<li'.( $selected_tab == $key ? ' class="now_selected"' : '').'>'
			.'<a href="'.$url->getUrl('tab='.$key).'"><span>'.$tab_name.'</span></a></li>', 'content');
	}
	$GLOBALS['page']->add('</ul>'
		.'</div>'
		.'<div class="std_block" id="coursecatalogue">', 'content');
	switch($selected_tab) {
		case "pathcourse" : {
			displayCoursePathList($url, $selected_tab);
		};break;/*
		case "time" : {
			displayTimeCourseList($url, $selected_tab);
		};break;*/
		default: {
			displayCourseList($url, $selected_tab);
		}
	}

	$GLOBALS['page']->add('</div>', 'content');

	// end of function ----------------------------------------------------------------
}


function showdemo() {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.multimedia.php');
	$lang = DoceboLanguage::createInstance('course', 'lms');

	$id_course = importVar('id_course', true, 0);

	$man_course = new Man_Course();
	$course = $man_course->getCourseInfo($id_course);

	$back = importVar('back', false, '');
	if($back == 'details') {

		$page_title = array('index.php?modname=coursecatalogue&amp;op=courselist' => $lang->def('_COURSE_LIST'),
							$lang->def('_SHOW_DEMO') );
	} else {

		$page_title = array('index.php?modname=coursecatalogue&amp;op=courselist' => $lang->def('_COURSE_LIST'),
							'index.php?modname=coursecatalogue&amp;op=coursedetails&amp;id_course='.$id_course => $course['name'],
							$lang->def('_SHOW_DEMO') );
	}
	$GLOBALS['page']->add( getTitleArea($page_title, 'course')
		.'<div class="std_block">'
		.'<div class="align_center">'
	, 'content');

	$ext = end(explode('.', $course['course_demo']));
	$GLOBALS['page']->add(
		getEmbedPlay('/appLms/'.Get::sett('pathcourse'), $course['course_demo'], $ext, '450', '450', true, $lang->def('_SHOW_DEMO') )
	, 'content');

	$GLOBALS['page']->add(
		'</div>'
		.'<h2><span class="code_course">'.$course['code'].' - </span> '.$course['name'].'</h2>'
		.'<p>'.$course['description'].'</p>'
		.'</div>', 'content');
}

function donwloadmaterials() {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.multimedia.php');
	$lang = DoceboLanguage::createInstance('course', 'lms');

	$id_course = importVar('id_course', true, 0);
	$edition_id = importVar('edition_id', true, 0);

	if($id_course != 0) {

		$man_course = new DoceboCourse($id_course);
		$file = $man_course->getValue('img_material');
	}
	if($edition_id != 0) {
		$select_edition = " SELECT img_material ";
		$from_edition 	= " FROM ".$GLOBALS["prefix_lms"]."_course_edition";
		$where_edition 	= " WHERE idCourseEdition = '".$edition_id."' ";

		list($file) = sql_fetch_row(sql_query($select_edition.$from_edition.$where_edition));
	}
	require_once(_base_.'/lib/lib.download.php' );
	$ext = end(explode('.', $file));
	sendFile('/appLms/'.Get::sett('pathcourse'), $file, $ext);
}

function showprofile() {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.lms_user_profile.php');

	$lang =& DoceboLanguage::createInstance('catalogue');
	$lang =& DoceboLanguage::createInstance('course');

	$id_user 	= importVar('id_user');
	$id_course 	= importVar('id_course');
	$man_course = new Man_Course();
	$course = $man_course->getCourseInfo($id_course);

	$profile = new LmsUserProfile( $id_user );
	$profile->init('profile', 'framework', 'modname=login&op=showprofile&id_course'.$id_course.'&id_user='.$id_user, 'ap');


	$GLOBALS['page']->add(
		getTitleArea($lang->def('_NAME', 'catalogue'), 'catalogue')
		.'<div class="std_block">'
		.getBackUi('index.php?modname=login&amp;op=courselist&amp;id_parent='.$course['idCategory'], $lang->def('_BACK')), 'content');

	$GLOBALS['page']->add(
		'<p class="category_path">'
			.'<b>'.$lang->def('_CATEGORY_PATH').' :</b> '
			.$man_course->getCategoryPath(	$course['idCategory'],
											$lang->def('_MAIN_CATEGORY'),
											$lang->def('_TITLE_CATEGORY_JUMP'),
											'index.php?modname=login&amp;op=courselist',
											'id_parent' )
			.' &gt; '.$course['name']
		.'</p>'
		.$profile->getProfile( getLogUserId() )
		.'</div>'
	, 'content');
}

// XXX: switch
switch($GLOBALS['op']) {

	case "register" : {
		register();
	};break;
	case "register_opt" : {
		register_confirm();
	};break;

	case "courselist" : {
		externalCourselist();
	};break;

	case "showdemo" : {
		showdemo();
	};break;
	case "donwloadmaterials" : {
		donwloadmaterials();
	};break;
	case "showprofile" : {
		showprofile();
	};break;
	case "buycourse" : {
		buycourse();
	};break;



	case "readwebpages" : {
		loadWebPage();
	};break;
	case "news" : {
		news();
	};break;
	case "readnews" : {
		readnews();
	};break;
	//lost user or password
	case "lostpwd" : {
		lostpwd();
	};break;

	default: {

		if(Get::sett('home_course_catalogue') == 'on') {
			externalCourselist();
		} else {
			loadWebPage();
			loadNews();
		}
	}
}

?>