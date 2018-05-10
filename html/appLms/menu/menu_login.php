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

// XXX: loadMenu
function loadMenu() {
	
	$lang = DoceboLanguage::createInstance('login');
	
	$query = "
	SELECT idPages, title 
	FROM ".$GLOBALS['prefix_lms']."_webpages 
	WHERE publish = '1' AND language = '".getLanguage()."'
	";
	//if(Get::sett('home_course_catalogue') == 'off') {
	if(Get::sett('home_course_catalogue', 'off')) {
		$query .= "  AND in_home = '0' ";
	}
	$query .= " ORDER BY sequence ";
	$result = sql_query( $query);
	
	$out = '<div class="login_menu_box">'."\n"
		.'<ul class="log_list">'."\n"
		.'<li class="first_row"><a class="voice" href="index.php">'.$lang->def('_HOMEPAGE').'</a></li>';
	while( list($idPages, $title) = sql_fetch_row($result)) {
		$out .= '<li>'
			.'<a class="voice" href="index.php?modname=login&amp;op=readwebpages&amp;idPages='.$idPages.'">'
			.$title.'</a></li>';
	}
	//if(Get::sett('activeNews') == 'link') {
	if(Get::sett('activeNews', '')) {
		$out .= '<li><a class="voice" href="index.php?modname=login&amp;op=news">'.$lang->def('_NEWS').'</a></li>';
	}
	$lang = DoceboLanguage::createInstance('course', 'lms');

	if(Get::sett('course_block', 'on') == 'on' && (Get::sett('home_course_catalogue', 'off') == 'off')) {
		
		$out .= '<li><a class="voice" href="index.php?modname=login&amp;op=courselist">'
				.$lang->def('_COURSE_LIST').'</a></li>';
	}
	$out .= '</ul>'."\n"
		.'</div>'."\n";
	return $out;
}

// XXX: loadLogin
function loadLogin() {
	
	require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
	require_once(_base_.'/lib/lib.form.php');
	
	$user_manager = new UserManager();
	
	$user_manager->setRegisterTo('link', Get::rel_path("base") . "/index.php?r=" . _register_);
	$user_manager->setLostPwdTo('link', 'index.php?modname=login&amp;op=lostpwd');
	
	$extra = false;
	if(isset($GLOBALS['logout'])) {
		$extra = array( 'style' => 'logout_action', 'content' => Lang::t('_UNLOGGED', 'login') );
	}
	if(isset($GLOBALS['access_fail'])) {
		$extra = array( 'style' => 'noaccess', 'content' => Lang::t('_NOACCESS', 'login') );
	}
	return Form::openForm('login_confirm', 'index.php?modname=login&amp;op=confirm')
		.$user_manager->getLoginMask('index.php?modname=login&amp;op=login', $extra)
		.Form::closeForm();
}

function loadNewsBlock() {
		
	$lang = DoceboLanguage::createInstance('login');
	
	$textQuery = "
	SELECT idNews, publish_date, title, short_desc 
	FROM ".$GLOBALS['prefix_lms']."_news 
	WHERE language = '".getLanguage()."'
	ORDER BY important DESC, publish_date DESC";
	
	$result = sql_query($textQuery);
	$html = '<div class="home_news_block">'
		.'<h1>'.$lang->def('_NEWS').'</h1>';
	while( list($idNews, $publish_date, $title, $short_desc) = sql_fetch_row($result)) {
		
		$html .= '<h2>'
			.'<a href="index.php?modname=login&amp;op=readnews&amp;idNews='.$idNews.'">'.$title.'</a></h2>'
			.'<p><span class="news_data">'.$lang->def('_DATE').' '.Format::date($publish_date, 'date').': </span>'
			.$short_desc.'</p>';
	}
	if(sql_num_rows($result) == 0) {
		$html .= $lang->def('_NO_CONTENT');
	}
	$html .= '</div>';
	return $html;
}

// XXX: compose menu
$GLOBALS['page']->add(
	loadMenu()
	.loadLogin()
	.( Get::sett('activeNews') == 'block' ? loadNewsBlock() : '' )
	, 'menu');


?>
