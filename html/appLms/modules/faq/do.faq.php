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

function play( $object_faq, $id_param) {
	!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage');
	
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.param.php');
	
	$lang =& DoceboLanguage::createInstance('faq');
	
	$idCategory = $object_faq->getId();
	$mode 		= importVar('mode', false, 'faq');
	$back_coded = htmlentities(urlencode( $object_faq->back_url ));
	$search 	= importVar('search');
	if(isset($_POST['empty'])) $search = '';
	
	$idReference = getLOParam($id_param, 'idReference');
	$link = 'index.php?modname=faq&amp;op=play&amp;idCategory='.$idCategory
		.'&amp;id_param='.$id_param.'&amp;back_url='.$back_coded;
	
	// NOTE: Track only if $idReference is present 
	if( $idReference !== FALSE ) {
		require_once($GLOBALS['where_lms'].'/class.module/track.faq.php');
		list( $exist, $idTrack) = Track_Faq::getIdTrack($idReference, getLogUserId(), $idCategory, TRUE );
		if( $exist ) {
			$ti = new Track_Faq( $idTrack );
			$ti->setDate(date('Y-m-d H:i:s'));
			$ti->status = 'completed';
			$ti->update();
		} else {
			$ti = new Track_Faq( false );
			$ti->createTrack( $idReference, $idTrack, getLogUserId(), date('Y-m-d H:i:s'), 'completed', 'faq' );
		}
	}
	
	list($title) = sql_fetch_row(sql_query("
	SELECT title 
	FROM ".$GLOBALS['prefix_lms']."_faq_cat 
	WHERE idCategory = '".(int)$idCategory."'"));
	
	//$GLOBALS['page']->add('<div id="top" class="std_block">', 'content');

	cout('<div class="yui-navset yui-navset-top tab_block">
			<ul class="yui-nav">
				<li class="first'.($mode == 'faq' ? ' selected' : '').'">
					<a href="'.$link.'&amp;mode=faq">
						<em>'.Lang::t('_SWITCH_TO_FAQ', 'faq').'</em>
					</a>
				</li>
				<li'.($mode == 'help' ? ' class="selected"' : '').'>
					<a href="'.$link.'&amp;mode=help">
						<em>'.Lang::t('_SWITCH_TO_HELP', 'faq').'</em>
					</a>
				</li>
			</ul>
			<div class="yui-content">', 'content');

	$GLOBALS['page']->add(
		getBackUi( Util::str_replace_once('&', '&amp;', $object_faq->back_url ), $lang->def('_BACK')));

	$GLOBALS['page']->add(
		'<b>'.$lang->def('_TITLE').' : '.$title.'</b><br />'
		.'<br />', 'content');
	if( $mode == 'help' ) {
		
		$link .= '&amp;mode=help';
		$letter = importVar('letter', true, '');
		$search = urldecode(importVar('search'));
		
		// Display as help
		$textQuery = "
		SELECT keyword 
		FROM ".$GLOBALS['prefix_lms']."_faq 
		WHERE idCategory = '".importVar('idCategory', true)."'";
		if($search != '' && !isset($_POST['empty'])) {
			$textQuery .= " AND ( question LIKE '%".$search."%' OR answer LIKE '%".$search."%' ) ";
		}
		$result = sql_query($textQuery);
		
		$GLOBALS['page']->add(Form::openForm('glossary_play', 'index.php?modname=faq&amp;op=play')
			
			.Form::getOpenFieldset($lang->def('_FILTER'))
			.Form::getHidden('idCategory', 'idCategory', $idCategory)
			.Form::getHidden('id_param', 'id_param', $id_param)
			.Form::getHidden('back_url', 'back_url', $back_coded)
			.Form::getHidden('mode', 'mode', $mode)
			
			.Form::getTextfield($lang->def('_SEARCH'), 'search', 'search', 255, 
				( $search != '' && !isset($_POST['empty']) ? $search : '' )), 'content');
		$GLOBALS['page']->add('[ ', 'content');
		//letter selection
		for($i = 97; $i < 123; $i++) {
			if($letter == $i) $GLOBALS['page']->add('<span class="text_bold">(', 'content');
			$GLOBALS['page']->add('<a href="'.$link.'&amp;letter='.$i.'">'.chr($i).'</a>', 'content');
			
			if($letter == $i) $GLOBALS['page']->add(')</span>', 'content');
			if($i < 122) $GLOBALS['page']->add('-', 'content');
		}
		$GLOBALS['page']->add('&nbsp;]&nbsp;[&nbsp;', 'content');
		for($i = 48; $i < 58; $i++) {
			if($letter == $i) $GLOBALS['page']->add('<span class="text_bold">(', 'content');
			$GLOBALS['page']->add('<a href="'.$link.'&amp;letter='.$i.'">'.chr($i).'</a>', 'content');
			
			if($letter == $i) $GLOBALS['page']->add(')</span>', 'content');
			if($i < 57) $GLOBALS['page']->add('-', 'content');
		}
		$GLOBALS['page']->add(' ] ', 'content');
		
		$GLOBALS['page']->add(Form::getBreakRow()
			.Form::openButtonSpace()
			.Form::getButton('do_search', 'do_search', $lang->def('_SEARCH'))
			.Form::getButton('empty', 'empty', $lang->def('_RESET'))
			.Form::closeButtonSpace()
			.Form::getCloseFieldset()
			.Form::closeForm(), 'content');
		
		//analyze keyword
		$keyword_help = array();
		while(list($keyword) = sql_fetch_row($result)) {
			$keyword_split = explode(',', $keyword);
			if(is_array($keyword_split))
			while(list(, $value) = each($keyword_split)) {
				$value = trim($value);
				if($value != '') {
					if($letter == 0) {
						
						if(isset($keyword_help[$value])) ++$keyword_help[$value];
						else $keyword_help[$value] = 1;
					} elseif(substr($value, 0, 1) == chr($letter)) {
						
						if(isset($keyword_help[$value])) ++$keyword_help[$value];
						else $keyword_help[$value] = 1;
					}
				}
			}
		}
		ksort($keyword_help);
		reset($keyword_help);
		$GLOBALS['page']->add('<div class="yui-gf">'
			.'<div class="yui-u first" >'
			.'<div class="boxinfo_title">'.$lang->def('_TERM').'</div>'
			.'<div class="boxinfo_container">'
			.'<ul class="link_list">', 'content');
		while(list($key, $value) = each($keyword_help)) {
			
			$GLOBALS['page']->add('<li><a class="href_block" href="'.$link.'&amp;letter='.$letter.'&amp;search='
				.urlencode($search).'&amp;word='.($key).'">'
				.$key.' ('.$value.')</a></li>', 'content');
		}
		$GLOBALS['page']->add('</ul>'
			.'</div></div>'
			.'<div class="yui-u">', 'content');
		if( isset($_GET['word']) ) {
			$reDef = sql_query("
			SELECT title, answer 
			FROM ".$GLOBALS['prefix_lms']."_faq 
			WHERE keyword LIKE '%".($_GET['word'])."%' AND idCategory = '".(int)$_GET['idCategory']."'
			ORDER BY title");
			while(list($title, $answer) = sql_fetch_row($reDef)) {
				$GLOBALS['page']->add('<div class="boxinfo_title">'.$title.'</div>'
					.'<div class="boxinfo_container">'
					.( $search == '' ? $answer :
					 preg_replace($search, '<span class="highlight">'.$search.'</span>', $answer) ).'</div><br />', 'content');
			}
		}
		$GLOBALS['page']->add('</div>'
			.'<div class="nofloat"></div>'
			.'</div>', 'content');
			
		
	} else {
		
		// Display as faq
		$textQuery = "
		SELECT question, answer 
		FROM ".$GLOBALS['prefix_lms']."_faq 
		WHERE idCategory = '".(int)$idCategory."' "
		.( isset($_POST['search']) && !isset($_POST['empty']) ? 
			" AND ( question LIKE '%".$search."%' OR answer LIKE '%".$search."%' ) " : '' )
		."ORDER BY sequence";
		$result = sql_query($textQuery);
		
		$GLOBALS['page']->add(Form::openForm('glossary_play', 'index.php?modname=faq&amp;op=play')
			
			.Form::getOpenFieldset($lang->def('_FILTER'))
			.Form::getHidden('idCategory', 'idCategory', $idCategory)
			.Form::getHidden('id_param', 'id_param', $id_param)
			.Form::getHidden('back_url', 'back_url', $back_coded)
			
			.Form::getTextfield($lang->def('_SEARCH'), 'search', 'search', 255, 
				( $search != '' && !isset($_POST['empty']) ? $search : '' ))
			
			.Form::getBreakRow()
			.Form::openButtonSpace()
			.Form::getButton('do_search', 'do_search', $lang->def('_SEARCH'))
			.Form::getButton('empty', 'empty', $lang->def('_RESET'))
			.Form::closeButtonSpace()
			.Form::getCloseFieldset()
			.Form::closeForm(), 'content');
		
		while(list($question, $answer) = sql_fetch_row($result)) {
			$GLOBALS['page']->add('<div class="boxinfo_title">'
				.( $search == '' ? $question :
				 	preg_replace($search, '<span class="highlight">'.$search.'</span>', $question) )
				.'</div>'
				.'<div class="boxinfo_container">'
				.( $search == '' ? $answer :
				 	preg_replace($search, '<span class="highlight">'.$search.'</span>', $answer) ).'</div><br />', 'content');
		}
	}
	$GLOBALS['page']->add('<div class="align_center">'
		.'<a href="#top">'
			.'<img src="'.getPathImage().'standard/upcheck.gif" title="'.$lang->def('_BACKTOTOP').'" />'
			.$lang->def('_BACKTOTOP')
		.'</a>'
		.getBackUi( Util::str_replace_once('&', '&amp;', $object_faq->back_url ), $lang->def('_BACK')), 'content');

	cout('<div class="nofloat"></div>
		</div><!-- yui content -->
		</div></div>', 'content');
}

}

?>