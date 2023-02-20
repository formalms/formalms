<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (!Docebo::user()->isAnonymous()) {
    function play($object_link, $id_param)
    {
        //-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
        $lang = &DoceboLanguage::createInstance('link');

        $idCategory = $object_link->getId();
        $mode = importVar('mode', false, 'link');
        $back_coded = htmlentities(urlencode($object_link->back_url));

        require_once _lms_ . '/lib/lib.param.php';
        $idReference = getLOParam($id_param, 'idReference');
        // NOTE: Track only if $idReference is present
        if ($idReference !== false) {
            require_once _lms_ . '/class.module/track.link.php';
            list($exist, $idTrack) = Track_Link::getIdTrack($idReference, getLogUserId(), $idCategory, true);
            if ($exist) {
                $ti = new Track_Link($idTrack);
                $ti->setDate(date('Y-m-d H:i:s'));
                $ti->status = 'completed';
                $ti->update();
            } else {
                $ti = new Track_Link(false);
                $ti->createTrack($idReference, $idTrack, getLogUserId(), date('Y-m-d H:i:s'), 'completed', 'link');
            }
        }

        list($title) = sql_fetch_row(sql_query('
	SELECT title 
	FROM ' . $GLOBALS['prefix_lms'] . "_link_cat 
	WHERE idCategory = '" . (int) $idCategory . "'"));

        $link = 'index.php?modname=link&amp;op=play&amp;idCategory=' . $idCategory
        . '&amp;id_param=' . $id_param . '&amp;back_url=' . $back_coded;

        /*$GLOBALS['page']->add('<div id="top" class="std_block">'
            .'<div class="colum_container">' */

        cout('<div id="top" class="yui-navset yui-navset-top tab_block">
		<ul class="yui-nav">
			<li class="first' . ($mode != 'keyw' ? ' selected' : '') . '">
				<a href="' . $link . '&amp;mode=list">
					<em>' . Lang::t('_SWITCH_TO_LIST', 'link') . '</em>
				</a>
			</li>
			<li' . ($mode == 'keyw' ? ' class="selected"' : '') . '>
				<a href="' . $link . '&amp;mode=keyw">
					<em>' . Lang::t('_SWITCH_TO_KEYWORD', 'link') . '</em>
				</a>
			</li>
		</ul>
		<div class="yui-content">', 'content');

        cout(getBackUi(Util::str_replace_once('&', '&amp;', $object_link->back_url), $lang->def('_BACK')), 'content');

        $GLOBALS['page']->add('<b>' . $lang->def('_TITLE') . ' : ' . $title . '</b><br /><br />'
        . $lang->def('_LINKIUNNEWWINDOW')
        . '<br /><br />', 'content');
        if ($mode == 'keyw') {
            displayAsKey($idCategory, $link . '&amp;mode=keyw');
        } else {
            displayAsList($idCategory);
        }
        $GLOBALS['page']->add('<div class="align_center">'
        . '<a href="#top"><img src="' . getPathImage() . 'standard/up.png" title="' . $lang->def('_BACKTOTOP') . '" />' . $lang->def('_BACKTOTOP') . '</a>'
        . getBackUi(Util::str_replace_once('&', '&amp;', $object_link->back_url), $lang->def('_BACK'))
        . '</div>', 'content');
        cout('</div></div>', 'content');
    }

    function displayAsList($idCategory)
    {
        //-kb-play-// if(!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) die("You can't access");
        $lang = &DoceboLanguage::createInstance('link');

        $textQuery = '
	SELECT title, link_address, description 
	FROM ' . $GLOBALS['prefix_lms'] . "_link 
	WHERE idCategory = '" . (int) $idCategory . "' 
	ORDER BY sequence";
        $result = sql_query($textQuery);

        while (list($title, $link_a, $description) = sql_fetch_row($result)) {
            $GLOBALS['page']->add('<div class="padding_04">'
            . '<div class="boxinfo_title">' . $title . '</div>'
            . '<div class="boxinfo_container">'
            . '<div class="text_indent"><a href="' . $link_a . '" onclick="window.open(\'' . $link_a . '\'); return false;">' . $link_a . '</a></div><br />' . $description
            . '</div>'
            . '</div><br />', 'content');
        }
    }

    function displayAsKey($idCategory, $link)
    {
        if (!checkPerm('view', true, 'organization') && !checkPerm('view', true, 'storage')) {
            exit("You can't access");
        }
        $lang = &DoceboLanguage::createInstance('link');

        $textQuery = '
	SELECT keyword 
	FROM ' . $GLOBALS['prefix_lms'] . "_link 
	WHERE idCategory = '" . (int) $_GET['idCategory'] . "'";
        $result = sql_query($textQuery);

        //analyze keyword
        $keyword_help = [];
        while (list($keyword) = sql_fetch_row($result)) {
            $keyword_split = explode(',', $keyword);
            if (is_array($keyword_split)) {
                foreach ($keyword_split as $value) {
                    $value = trim($value);
                    if ($value != '') {
                        if (isset($keyword_help[$value])) {
                            ++$keyword_help[$value];
                        } else {
                            $keyword_help[$value] = 1;
                        }
                    }
                }
            }
        }
        ksort($keyword_help);
        reset($keyword_help);

        $GLOBALS['page']->add('<div class="yui-gf">'
        . '<div class="yui-u first">'
        . '<div class="boxinfo_title">' . $lang->def('_TERM') . '</div>'
        . '<div class="boxinfo_container">'
        . '<ul class="link_list">', 'content');
        foreach ($keyword_help as $key => $value) {
            $GLOBALS['page']->add('<li><a class="href_block" href="' . $link . '&amp;word=' . ($key) . '">'
            . $key . ' (' . $value . ')</a></li>', 'content');
        }
        $GLOBALS['page']->add('</ul></div>'
        . '</div>'
        . '<div class="yui-u">', 'content');

        if (isset($_GET['word'])) {
            $reDef = sql_query('
		SELECT title, link_address, description 
		FROM ' . $GLOBALS['prefix_lms'] . "_link 
		WHERE keyword LIKE '%" . ($_GET['word']) . "%' AND idCategory = '" . (int) $_GET['idCategory'] . "'
		ORDER BY title");
            while (list($title, $link_a, $description) = sql_fetch_row($reDef)) {
                $GLOBALS['page']->add('<div class="boxinfo_title">' . $title . '</div>'
                . '<div class="boxinfo_container">'
                . '<div class="text_indent"><a href="" onclick="window.open(\'' . $link_a . '\'); void 0;">' . $link_a . '</a></div><br />' . $description
                . '</div><br />', 'content');
            }
        } else {
            $GLOBALS['page']->add($lang->def('_SESLECTTERM'), 'content');
        }
        $GLOBALS['page']->add('</div>'
        . '</div>'
        . '<div class="nofloat"></div><br />', 'content');
    }
}
