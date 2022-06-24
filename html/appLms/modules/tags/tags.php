<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit("You can't access this file directly");

if (Docebo::user()->isAnonymous()) {
    exit('You must login first.');
}

function tagslist()
{
    require_once _base_ . '/lib/lib.table.php';
    require_once _adm_ . '/lib/lib.tags.php';
    $lang = &DoceboLanguage::createInstance('tags', 'framework');

    $id_tag = FormaLms\lib\Get::req('id_tag', DOTY_INT, 0);
    $tag_name = FormaLms\lib\Get::req('tag', DOTY_STRING, '');
    $filter = FormaLms\lib\Get::req('filter', DOTY_STRING, '');

    $nav_bar = new NavBar('ini', FormaLms\lib\Get::sett('visuItem'), 0);
    $nav_bar->setLink('index.php?modname=tags&amp;op=tags&amp;id_tag=' . $id_tag);
    $ini = $nav_bar->getSelectedElement();

    $tags = new Tags('*');
    $resources = $tags->getResourceByTags($id_tag, false, false, $ini, FormaLms\lib\Get::sett('visuItem'));

    $GLOBALS['page']->add(
        getTitleArea([$lang->def('_TAGS')], 'tags')
        . '<div class="std_block">'
        . '<div class="tag_list">', 'content');

    foreach ($resources['list'] as $res) {
        $link = $res['permalink'];
        $delim = (strpos($link, '?') === false ? '?' : '&');
        if (strpos($link, '#') === false) {
            $link = $link . $delim . 'sop=setcourse&sop_idc=' . $res['id_course'];
        } else {
            $link = str_replace('#', $delim . 'sop=setcourse&sop_idc=' . $res['id_course'] . '#', $link);
        }

        $GLOBALS['page']->add(''
            . '<h2>'
                . '<a href="' . $link . '">' . $res['title'] . '</a>'
            . '</h2>'
            . '<p>'
                . $res['sample_text']
            . '</p>'
            . '<div class="tag_cloud">'
                . '<span>' . $lang->def('_TAGS') . ' : </span>'
                . '<ul><li>'
                    . implode('</li><li>', $res['related_tags'])
                . '</li></ul>'
            . '</div>'
            . '<br />', 'content');
    }
    $GLOBALS['page']->add(
        '</div>'
        . $nav_bar->getNavBar($ini, $resources['count'])
        . '</div>', 'content');
}

function tags_dispatch($op)
{
    switch ($op) {
        default: tagslist();
    }
}
