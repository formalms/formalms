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

ob_start();

define('CORE', true);
define('POPUP', true);
define('IN_FORMA', true);
define('_deeppath_', '../../../');
require dirname(__FILE__) . '/' . _deeppath_ . 'base.php';

// Initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(CHECK_SYSTEM_STATUS);

// Utils and so on
require_once _base_ . '/lib/lib.platform.php';

// create instance of StdPageWriter
StdPageWriter::createInstance();

// Popup content

$lang = &FormaLanguage::createInstance('popup_' . POPUP_MOD_NAME, 'framework');
$GLOBALS['page']->setWorkingZone('content');

$GLOBALS['page']->add(
    getTitleArea($lang->def('_AREA_' . strtoupper(POPUP_MOD_NAME)), strtolower(POPUP_MOD_NAME))
    . '<div class="std_block">');
if (!defined('POPUP_MOD_NAME')) {
    exit();
}
require_once '../' . POPUP_MOD_NAME . '/body.php';
$GLOBALS['page']->add('</div>');

// finalize
Boot::finalize();

/* output all */
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

// layout
Layout::render('popup');

// flush buffer
ob_end_flush();

// Page functions:
function drawMenu($menu_label, $menu_url, $sel = '')
{
    if (is_array($menu_label)) {
        $GLOBALS['page']->add("<div class=\"popup_menu\"><ul>\n", 'content');

        foreach ($menu_label as $key => $val) {
            if ($sel == $key) {
                $class = 'class="selected" ';
            } else {
                $class = '';
            }

            $GLOBALS['page']->add('<li><a ' . $class . 'href="' . $menu_url[$key] . '">' . $val . "</a></li>\n", 'content');
        }
        $GLOBALS['page']->add("</ul></div>\n", 'content');
    }
}

function getPopupBaseUrl()
{
    return basename($_SERVER['SCRIPT_NAME']) . '?sn=' . FormaLms\lib\Get::cur_plat();
}
