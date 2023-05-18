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

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (Forma::user()->isAnonymous()) {
    exit("You can't access");
}

function drawCalendar()
{
    checkPerm('view');

    $size = importVar('size', false, 'max');

    $width = '90%';
    if ($size == 'min') {
        $width = '200px';
    }

    addCss('calendar_' . $size);

    YuiLib::load('base,dragdrop');
    Util::get_js(FormaLms\lib\Get::rel_path('lms') . '/modules/calendar/calendar.js', true, true);
    Util::get_js(FormaLms\lib\Get::rel_path('lms') . '/modules/calendar/calendar_helper.js', true, true);

    //permissions = permissions granted to the logged user according to his/her level and role
    //	2 => can create/delete/modify all events
    //	1 => can create/delete/modify only own events
    //	0 => can view only

    $permissions = 0;
    if (checkPerm('mod', true)) {
        $permissions = 2;
    } elseif (checkPerm('personal', true)) {
        $permissions = 1;
    }

    //mode="edit" => events can be added and edited according to given permissions
    //mode="view" => events can only be viewed regardless the permissions

    $GLOBALS['page']->add('<script type="text/javascript">'
        . '	setup_cal(	null, '
        . '\'lms\', '
        . '\'lms\', '
        . '\'edit\', '
        . '\'' . $permissions . '\', '
        . '\'' . Forma::user()->getIdSt() . '\' '
        . ');'
         . '</script>', 'page_head');

    $GLOBALS['page']->add("\n"
        . getTitleArea(Lang::t('_CALENDAR', 'calendar'), 'calendar')
        . '<div class="std_block">'
        . '<div id="displayCalendar" style="clear: both; width:' . $width . '"></div>'
        . '<div class="nofloat"></div>'
        . '</div>', 'content');
}
