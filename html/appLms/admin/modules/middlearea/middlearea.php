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

function view_area()
{
    checkPerm('view');

    require_once _lms_ . '/lib/lib.middlearea.php';

    $lang = &FormaLanguage::createInstance('middlearea', 'lms');
    $lc = &FormaLanguage::createInstance('menu_course', 'lms');

    $query_menu = "SELECT mo.idModule, mo.default_name, module_name
    FROM %lms_module as mo WHERE mo.module_info IN ('all','user')
    ORDER BY mo.idModule";

    $re_menu_voice = sql_query($query_menu);

    $base_url = 'index.php?r=adm/userselector/show&id=';
    $second_url = 'index.php?modname=middlearea&amp;op=switch_active&amp;obj_index=';
    $third_url = 'index.php?modname=middlearea&amp;op=set_home&amp;obj_index=';

    $ma = new Man_MiddleArea();
    $disabled_list = $ma->getDisabledList();
    $menu_on_slider = ['mo_7' => Lang::t('_MY_CERTIFICATE', 'menu_over'),
        'mo_34' => Lang::t('_MYCOMPETENCES', 'menu_over'), ];

    /* NEW MENU */
    $menu = CoreMenu::getList('lms', false);

    $active_icon = 'subs_actv';
    $noactive_icon = 'subs_noac';
    $vp = Lang::t('_VIEW_PERMISSION', 'standard');
    $ea = Lang::t('_ENABLE_AREA', 'standard');

    $main_menu = '';
    foreach ($menu as $item) {
        // TODO: submenus handling
        $title = Lang::t($item->name, 'menu_over');
        $activation_icon = $item->is_active === 'true' ? $active_icon : $noactive_icon;
        if ($item->of_platform === 'lms') {
            $main_menu .=
                <<<HTML
<li id="$item->idMenu">
    <span>$title</span>
    <a class="ico-sprite subs_users" href="index.php?r=adm/userselector/show&id=$item->idMenu&instance=lmsmenu&load=1&showSelectAll=true"><span>$vp</span></a>
    <a class="ico-sprite $activation_icon" href="index.php?modname=middlearea&op=switch_menu_active&id=$item->idMenu"><span>$ea</span></a>
</li>
HTML;
        }
    }
    /* NEW MENU */

    // Tab list
    $tab_list = '';
    $tab = [
        'tb_elearning' => Lang::t('_ELEARNING', 'middlearea'),
        'tb_home' => Lang::t('_HOME', 'middlearea'),
        'tb_dashboard' => Lang::t('_DASHBOARD', 'middlearea'),
        'tb_label' => Lang::t('_LABELS', 'label'),
        'tb_coursepath' => Lang::t('_COURSEPATH', 'coursepath'),
        'tb_games' => Lang::t('_CONTEST', 'middlearea'),
        'tb_communication' => Lang::t('_COMMUNICATIONS', 'middlearea'),
        'tb_videoconference' => Lang::t('_VIDEOCONFERENCE', 'middlearea'),
        'tb_kb' => Lang::t('_CONTENT_LIBRARY', 'middlearea'),
    ];

    $pl = new PluginManager('');
    $list_pl = $pl->get_all_plugins();

    foreach ($list_pl as $key) {
        $plugin_name = strtolower($key['name']);
        $tab['tb_' . $plugin_name] = Lang::t('_' . strtoupper($key['name']), 'middlearea');
    }

    $query_menu = "SELECT obj_index, is_home from %lms_middlearea where obj_index like 'tb_%' ORDER BY sequence";
    $re_tablist = sql_query($query_menu);

    while (list($obj_index, $is_home) = sql_fetch_row($re_tablist)) {
        $id = $obj_index;
        $name = $tab[$id];

        $tab_list .= '<li id="' . $id . '">'
            . '<a class="ico-sprite subs_location' . ($is_home ? '_green' : '') . '" href="' . $third_url . $id . '"><span>' . Lang::t('_VIEW_PERMISSION', 'standard') . '</span></a>'
            . ' <span>' . $name . '</span>'
            . ' <a class="ico-sprite subs_users" href="' . $base_url . $id . '&instance=lmstab"><span>' . Lang::t('_VIEW_PERMISSION', 'standard') . '</span></a>'
            . ' <a class="ico-sprite subs_' . (isset($disabled_list[$id]) ? 'noac' : 'actv') . '" href="' . ($is_home ? '' : $second_url . $id) . '"><span>' . Lang::t('_ENABLE_AREA', 'middlearea') . '</span></a>'
            . '</li>';
    }
    // Block List
    $block_list = '';
    $block = [
        //'user_details_short' => Lang::t('_SIMPLE_USER_PROFILE', 'middlearea'),
        'user_details_full' => Lang::t('_PROFILE', 'profile'),
        'credits' => Lang::t('_CREDITS', 'middlearea'),
        //aggiunto box carriera
        'career' => Lang::t('_CAREER', 'middlearea'),
        //aggiunto box iscrizione corso
        'course' => Lang::t('_SUBSCRIBE_COURSE', 'middlearea'),
        'news' => Lang::t('_NEWS', 'middlearea'),
        'mo_message' => Lang::t('_MESSAGES', 'menu_over'),
    ];
    $slider_options = array_merge($block, $menu_on_slider);
    foreach ($slider_options as $id => $name) {
        $block_list .= '<div class="direct_block">'
            . '<span>' . $name . '</span>'
            . ' <a class="ico-sprite subs_users" href="' . $base_url . $id . '&instance=lmsblock"><span>' . Lang::t('_VIEW_PERMISSION', 'standard') . '</span></a>'
            . ' <a class="ico-sprite subs_' . (isset($disabled_list[$id]) ? 'noac' : 'actv') . '" href="' . $second_url . $id . '"><span>' . Lang::t('_ENABLE_AREA', 'middlearea') . '</span></a>'
            . '</div><br/>';
    }

    cout(getTitleArea($lang->def('_MIDDLE_AREA'), 'middlearea')
        . '<div class="std_block">');

    cout('<h2>' . Lang::t('_MAN_MENU', 'menu') . '</h2>'
        . '<ul id="menu_label" class="action-list">'
        . $main_menu
        . '</ul>');

    cout('<div id="lms_main_container" class="yui-t5">'
        . '<div class="yui-b">'
        . '<h2>' . Lang::t('_BLOCKS', 'middlearea') . '</h2>'
        . $block_list
        . '</div>'
        . '<div id="yui-main">'
        . '<div class="yui-b" id="tablist">'
        . '<h2>' . Lang::t('_TABS', 'middlearea') . '</h2>'
        . '<ul id="h_label" class="action-list">'
        . $tab_list
        . '</ul>'
        . '</div>'
        . '</div>'
        . '<div class="nofloat"></div>'
        . '</div>');
    cout('</div>');
    $js = "<script>
            var label_current_order = ''
            $('#h_label li').each(function(){
                label_current_order +=  String($(this).attr('id')) + ','       
            });
            
            
            var menu_current_order = '';
            $('#menu_label li').each(function(){
                menu_current_order +=  String($(this).attr('id')) + ','           
            });
            
            $( function(){
                $('#h_label').sortable({
                    deactivate: function(e, ui){
                        var label_new_order = ''
                        $('#h_label li').each(function(){
                            label_new_order += ',' + String($(this).attr('id')) 
                        })
                        if (label_new_order != label_current_order) {
                            label_new_order = label_new_order.substr(1);
                            var posting = $.get(
                                'ajax.adm_server.php', 
                                {
                                    r: 'middlearea/order',
                                    list: label_new_order
                                }
                            )
                            posting.done(function(r){
                                label_current_order = label_new_order
                            })
                            posting.fail(function(r){
                                console.log('error=' + r)
                            })
                        }
                    },
                    cursor: 'crosshair',
                    cursorAt: {left:5},
                    revert: true
                });
                $('#h_label').disableSelection();
                
               
                $('#menu_label').sortable({
                    deactivate: function(e, ui){
                        var menu_new_order = ''
                        $('#menu_label li').each(function(){
                            menu_new_order += ',' + String($(this).attr('id')) 
                        })
                        if (menu_new_order !== menu_current_order) {
                            menu_new_order = menu_new_order.substr(1);
                            var posting = $.get(
                                'ajax.adm_server.php',
                                {
                                    r: 'middlearea/menuOrder',
                                    list: menu_new_order
                                }
                            )
                            posting.done(function(r){
                                menu_current_order = menu_new_order
                            })
                            posting.fail(function(r){
                                console.log('error=' + r)
                            })
                        }
                    },
                    cursor: 'crosshair',
                    cursorAt: {left:5},
                    revert: true
                });
                $('#menu_label').disableSelection();
            })
          </script>";

    cout($js);
}

function switch_active()
{
    require_once _lms_ . '/lib/lib.middlearea.php';

    $man_ma = new Man_MiddleArea();

    $obj_index = importVar('obj_index', false, '');

    $lang = &FormaLanguage::createInstance('middlearea', 'lms');
    $selected = $man_ma->getObjIdstList($obj_index);
    $man_ma->setObjIdstList($obj_index, $selected);

    $re = $man_ma->changeDisableStatus($obj_index);

    Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=' . ($re ? 'ok' : 'err'));
}

function switch_menu_active()
{
    $id = FormaLms\lib\Get::req('id', DOTY_INT);

    $menu = CoreMenu::get($id);
    switch (FormaLms\lib\Get::sett('home_page_option')) {
        case 'my_courses':
            $is_homepage = $menu->mvc_path == 'elearning/show';
            break;
        case 'catalogue':
            $is_homepage = $menu->mvc_path == 'lms/catalog/show';
            break;
        default:
            $is_homepage = false;
            break;
    }

    if (!$is_homepage) {
        $res = CoreMenu::set($id, ['is_active' => $menu->is_active === 'true' ? 'false' : true]);
    } else {
        $res = false;
    }

    Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=' . ($res ? 'ok' : 'err'));
}

function set_home_page()
{
    require_once _lms_ . '/lib/lib.middlearea.php';

    $man_ma = new Man_MiddleArea();
    $obj_index = importVar('obj_index', false, '');
    $selected = $man_ma->setHomePageTab($obj_index);
    Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=ok');
}

function select_permission()
{
    checkPerm('view');

    require_once _lms_ . '/lib/lib.middlearea.php';
    require_once _base_ . '/lib/lib.userselector.php';
    require_once _base_ . '/lib/lib.form.php';

    $lang = &FormaLanguage::createInstance('middlearea', 'lms');

    $obj_index = importVar('obj_index', false, '');

    // first step load selector

    $man_ma = new Man_MiddleArea();
    $acl_manager = new FormaACLManager();
    $user_select = new UserSelector();

    $user_select->show_user_selector = true;
    $user_select->show_group_selector = true;
    $user_select->show_orgchart_selector = true;
    $user_select->show_orgchart_simple_selector = false;
    //$user_select->multi_choice = TRUE;

    // try to load previous saved
    if (isset($_GET['load'])) {
        $selected = $man_ma->getObjIdstList($obj_index);
        if (is_array($selected)) {
            $user_select->resetSelection($selected);
        }
    }
    if (isset($_POST['okselector'])) {
        $selected = $user_select->getSelection($_POST);
        $re = $man_ma->setObjIdstList($obj_index, $selected);
        Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=' . ($re ? 'ok' : 'err'));
    }

    cout(getTitleArea([
            'index.php?modname=middlearea&amp;op=view_area' => $lang->def('_MIDDLE_AREA'),
            Lang::t('_VIEW_PERMISSION', 'standard'),
        ], 'middlearea')
        . '<div class="std_block">');
    $user_select->addFormInfo(Form::getHidden('obj_index', 'obj_index', $obj_index));
    $user_select->loadSelector('index.php?modname=middlearea&amp;op=select_permission',
        false,
        false,
        true);

    cout('</div>');
}

/**
 * @deprecated
 */
function select_menu_permission() //DEPRECATED FOR MULTIUSERSELECTOR
{
    checkPerm('view');

    require_once _lms_ . '/lib/lib.middlearea.php';
    require_once _base_ . '/lib/lib.userselector.php';
    require_once _base_ . '/lib/lib.form.php';

    $lang = &FormaLanguage::createInstance('middlearea', 'lms');

    $id = FormaLms\lib\Get::req('id', DOTY_INT);

    // first step load selector

    $man_ma = new Man_MiddleArea();
    $acl_manager = new FormaACLManager();
    $user_select = new UserSelector();

    $user_select->show_user_selector = true;
    $user_select->show_group_selector = true;
    $user_select->show_orgchart_selector = true;
    $user_select->show_orgchart_simple_selector = false;
    //$user_select->multi_choice = TRUE;

    $menu = CoreMenu::get($id);
    $am = Forma::user()->getACLManager();
    $role_idst = $am->getRole(false, $menu->role)[0];

    $members = $am->getRoleMembers($role_idst);

    // try to load previous saved
    if (isset($_GET['load'])) {
        if (in_array(1, $members)) {
            $members = [];
            $all = true;
        } else {
            $all = false;
        }
        $user_select->resetSelection($members);
    }
    if (isset($_POST['okselector'])) {
        foreach ($members as $member) {
            $am->removeFromRole($role_idst, $member);
        }
        $all = FormaLms\lib\Get::req('all', DOTY_BOOL);
        if ($all) {
            $selected = [1];
        } else {
            $selected = $user_select->getSelection($_POST);
        }
        foreach ($selected as $member) {
            $am->addToRole($role_idst, $member);
        }
        Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=' . ($re ? 'ok' : 'err'));
    }

    cout(getTitleArea([
            'index.php?modname=middlearea&amp;op=view_area' => $lang->def('_MIDDLE_AREA'),
            Lang::t('_VIEW_PERMISSION', 'standard'),
        ], 'middlearea')
        . '<div class="std_block">');
    $user_select->addFormInfo(Form::getHidden('id', 'id', $id));
    $user_select->addFormInfo(Form::getRadioHoriz(Lang::t('_SELECT'), 'all', 'all', [Lang::t('_ALL') => 1, Lang::t('_MANUAL') => 0], (int) $all));
    $user_select->addFormInfo('<script type="text/javascript">' .
        <<<JAVASCRIPT
function switch_selection() {
    if($('input[name="all"]:checked').val() == 1) {
        $('#main_selector_tabview').hide();
    } else {
        $('#main_selector_tabview').show();
    }
}
$(function() { switch_selection(); });
$('input[name="all"]').change(switch_selection);
JAVASCRIPT
        . '</script>');
    $user_select->loadSelector('index.php?modname=middlearea&op=select_menu_permission',
        false,
        false,
        false);

    cout('</div>');
}

//------------------------------------------------------------------------------

function view()
{
    //addJs($GLOBALS['where_lms_relative'].'/admin/modules/middlearea/', 'middlearea.js');
    cout(Util::get_js(FormaLms\lib\Get::rel_path('lms') . '/admin/modules/middlearea/middlearea.js', true), 'scripts');

    cout('<div class="std_block">');

    cout('<div class="lms_management">');
    cout('<div id="menu_area" class="area"></div>');
    cout('<div id="left_area" class="area"></div>');
    cout('<div id="tabs_area" class="area"></div>');
    cout('<div id="right_area" class="area"></div>');
    cout('</div>');

    cout('</div>');

    $script = 'YAHOO.util.Event.onDOMReady(function() {
            var blocks = ["block_1", "block_2", "block_3", "block_4"];
            var o = new BlockList("left_area", blocks);
        });';
    cout('<script type="text/javascript">' . $script . '</script>', 'scripts');
}

/**
 * Dispatching.
 **/
function MiddleAreaDispatch($op)
{
    if (isset($_POST['cancelselector'])) {
        $op = 'view_area';
    }

    switch ($op) {
        case 'select_permission':
                select_permission();

            break;
        case 'switch_active':
                switch_active();

            break;
        case 'select_menu_permission':
                select_menu_permission();

            break;
        case 'switch_menu_active':
                switch_menu_active();

            break;
        case 'set_home':
            set_home_page();

            // no break
        case 'view_area':
        default:
                //view_area();
                view_area();

            break;
    }
}
