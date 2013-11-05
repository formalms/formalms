<?php defined("IN_FORMA") or die("You can't access this file directly");

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

if(Docebo::user()->isAnonymous()) die("You must login first.");

function tagslist() {
	
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.tags.php');
	$lang =& DoceboLanguage::createInstance('tags', 'framework');
	
	$id_tag 	= Get::req( 'id_tag', DOTY_INT, 0 );
	$tag_name 	= Get::req( 'tag', DOTY_STRING, '' );
	$filter 	= Get::req( 'filter', DOTY_STRING, '' );
	
	$nav_bar = new NavBar('ini', Get::sett('visuItem'), 0 );
	$nav_bar->setLink('index.php?modname=tags&amp;op=tags&amp;id_tag='.$id_tag);
	$ini = $nav_bar->getSelectedElement();

	$tags = new Tags('*');
	$resources = $tags->getResourceByTags($id_tag, false, false, $ini, Get::sett('visuItem'));

	$GLOBALS['page']->add(
		getTitleArea(array($lang->def('_TAGS')), 'tags')
		.'<div class="std_block">'
		.'<div class="tag_list">'
	, 'content');
	
	while(list(, $res) = each($resources['list'])) {
		
		$link = $res['permalink'];
		$delim = ( strpos($link, '?') === false ? '?' : '&' );
		if( strpos($link, '#') === false) {
			$link = $link . $delim . 'sop=setcourse&sop_idc='.$res['id_course'];
		} else {
			$link = str_replace('#', $delim . 'sop=setcourse&sop_idc='.$res['id_course'].'#', $link);
		}
		
		$GLOBALS['page']->add(''
			.'<h2>'
				.'<a href="'.$link.'">'.$res['title'].'</a>'
			.'</h2>'
			.'<p>'
				.$res['sample_text']
			.'</p>'
			.'<div class="tag_cloud">'
				.'<span>'.$lang->def('_TAGS').' : </span>'
				.'<ul><li>'
					.implode('</li><li>', $res['related_tags'])
				.'</li></ul>'
			.'</div>'
			.'<br />'
		, 'content');
	}
	$GLOBALS['page']->add(
		'</div>'	
		.$nav_bar->getNavBar($ini, $resources['count'])
		.'</div>'
	, 'content');
}

function tags_dispatch($op) {
	switch($op) {
		default: tagslist();
	}
}

?>