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

function view_area() {
    checkPerm('view');

    require_once(_lms_.'/lib/lib.middlearea.php');

    $lang     =& DoceboLanguage::createInstance('middlearea', 'lms');
    $lc     =& DoceboLanguage::createInstance('menu_course', 'lms');

    $query_menu = "SELECT mo.idModule, mo.default_name, module_name
    FROM %lms_module as mo WHERE mo.module_info IN ('all','user')
    ORDER BY mo.idModule"; 
    
    $re_menu_voice = sql_query($query_menu);

    $base_url = 'index.php?modname=middlearea&amp;op=select_permission&amp;load=1&amp;obj_index=';
    $second_url = 'index.php?modname=middlearea&amp;op=switch_active&amp;obj_index=';
    $third_url = 'index.php?modname=middlearea&amp;op=set_home&amp;obj_index=';

    $ma = new Man_MiddleArea();
    $disabled_list = $ma->getDisabledList();
    $menu_on_slider = array('mo_7'=> Lang::t('_MY_CERTIFICATE', 'menu_over'), 
                            'mo_34' => Lang::t('_MYCOMPETENCES', 'menu_over' ));

    // Main men
    $main_menu = '';
    while(list($id_m, $default_name, $my_name) = sql_fetch_row($re_menu_voice)) {
        
        if (array_key_exists ('mo_'.$id_m, $menu_on_slider) == false ) {
            $main_menu .= '<li>'
                .'<span>'.Lang::t($default_name, 'menu_over', false, false, $default_name).'</span>'
                .' <a class="ico-sprite subs_users" href="'.$base_url.'mo_'.$id_m.'"><span>'.Lang::t('_VIEW_PERMISSION', 'standard').'</span></a>'
                .' <a class="ico-sprite subs_'.( isset($disabled_list['mo_'.$id_m]) ? 'noac' : 'actv' ).'" href="'.$second_url.'mo_'.$id_m.'"><span>'.Lang::t('_ENABLE_AREA', 'middlearea').'</span></a>'
                .'</li>';
        }        

    }
    $main_menu .= '<li>'
            .'<span>'.Lang::t('_CUSTOMER_HELP', 'customer_help').'</span>'
            .' <a class="ico-sprite subs_users" href="'.$base_url.'mo_help'.'"><span>'.Lang::t('_VIEW_PERMISSION', 'standard').'</span></a>'
            .' <a class="ico-sprite subs_'.( isset($disabled_list['mo_help']) ? 'noac' : 'actv' ).'" href="'.$second_url.'mo_help'.'"><span>'.Lang::t('_ENABLE_AREA', 'middlearea').'</span></a>'
            .'</li>';

    // Tab list
    $tab_list = '';
    $tab = array(
        'tb_elearning' => Lang::t('_ELEARNING', 'middlearea'),
        'tb_home' => Lang::t('_HOME', 'middlearea'),
        'tb_label' => Lang::t('_LABELS', 'label'),
        'tb_coursepath' => Lang::t('_COURSEPATH', 'coursepath'),
        'tb_games' => Lang::t('_CONTEST', 'middlearea'),
        'tb_communication' => Lang::t('_COMMUNICATIONS', 'middlearea'),
        'tb_videoconference' => Lang::t('_VIDEOCONFERENCE', 'middlearea'),
        'tb_kb' => Lang::t('_CONTENT_LIBRARY', 'middlearea')  
    );

    
 if(Get::cfg('enable_plugins', false)){
        $pl = new PluginManager();
        $list_pl = $pl->get_all_plugins();
        
        foreach ($list_pl as $key){
            $plugin_name = strtolower ($key['name']);
            $tab["tb_".$plugin_name] = Lang::t('_'.strtoupper ($key['name']), 'middlearea');
        }     
 }    
    

    $query_menu = "SELECT obj_index, is_home from %lms_middlearea where obj_index like 'tb_%' ORDER BY sequence";
    $re_tablist = sql_query($query_menu);

    while(list($obj_index, $is_home) = sql_fetch_row($re_tablist)) {
        $id = $obj_index;
        $name = $tab[$id];

        $tab_list .= '<li id="'.$id.'">'
            .'<a class="ico-sprite subs_location'.($is_home ? '_green':'').'" href="'.$third_url.$id.'"><span>'.Lang::t('_VIEW_PERMISSION', 'standard').'</span></a>'
            .' <span>'.$name.'</span>'
            .' <a class="ico-sprite subs_users" href="'.$base_url.$id.'"><span>'.Lang::t('_VIEW_PERMISSION', 'standard').'</span></a>'
            .' <a class="ico-sprite subs_'.( isset($disabled_list[$id]) ? 'noac' : 'actv' ).'" href="'.($is_home? "":$second_url.$id).'"><span>'.Lang::t('_ENABLE_AREA', 'middlearea').'</span></a>'
            .'</li>';
    }
    // Block List
    $block_list = '';
    $block = array(
        //'user_details_short' => Lang::t('_SIMPLE_USER_PROFILE', 'middlearea'),
        'user_details_full' => Lang::t('_PROFILE', 'profile'),
        'credits' => Lang::t('_CREDITS', 'middlearea'),
        //aggiunto box carriera
        'career' => Lang::t('_CAREER', 'middlearea'),
        //aggiunto box iscrizione corso
        'course' => Lang::t('_SUBSCRIBE_COURSE', 'middlearea'),
        'news' => Lang::t('_NEWS', 'middlearea'),
        'mo_message' =>  Lang::t('_MESSAGES', 'menu_over')
    );
    $slider_options = array_merge($block, $menu_on_slider);
    while(list($id, $name) = each($slider_options)) {

        $block_list .= '<div class="direct_block">'
            .'<span>'.$name.'</span>'
            .' <a class="ico-sprite subs_users" href="'.$base_url.$id.'"><span>'.Lang::t('_VIEW_PERMISSION', 'standard').'</span></a>'
            .' <a class="ico-sprite subs_'.( isset($disabled_list[$id]) ? 'noac' : 'actv' ).'" href="'.$second_url.$id.'"><span>'.Lang::t('_ENABLE_AREA', 'middlearea').'</span></a>'
            .'</div><br/>';
    }

    

    cout( getTitleArea($lang->def('_MIDDLE_AREA'), 'middlearea')
        .'<div class="std_block">');

    cout('<h2>'.Lang::t('_MAN_MENU', 'menu').'</h2>'

        .'<ul class="action-list">'
        .$main_menu
        .'</ul>');

    cout('<div id="lms_main_container" class="yui-t5">'
            .'<div class="yui-b">'
            .'<h2>'.Lang::t('_BLOCKS', 'middlearea').'</h2>'
            .$block_list
            .'</div>'
            .'<div id="yui-main">'
                .'<div class="yui-b" id="tablist">'
                .'<h2>'.Lang::t('_TABS', 'middlearea').'</h2>'
                .'<ul class="action-list">'
                .$tab_list
                .'</ul>'
                .'</div>'
            .'</div>'
            .'<div class="nofloat"></div>'
        .'</div>');
    cout('</div>');
    $js = "
    <script src=\"http://yui.yahooapis.com/3.9.1/build/yui/yui-min.js\"></script>
    <script>
    YUI().use('sortable', function (Y) {
        var sortable;
        sortable = new Y.Sortable({
            container: '#tablist ul',
            nodes    : 'li',
            opacity  : '0.1'
        });

        sortable.delegate.after('drag:end', function (e) {
            var node = sortable.delegate.get('currentNode');

                // rewind
                while(node.previous()) {
                    node = node.previous();
}
                // ciclo
                a = node.get('id');
                while(node.next()) {
                    node = node.next();
                    a += ','+node.get('id');
                }

                sUrl = 'ajax.adm_server.php?r=middlearea/order&list='+a;

                var callback = {
                        success: function(o) {
                        },
                        failure:function(o) {
                        }
                };
                YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);


        });
    });


    </script>";

    cout($js);
}

function switch_active() {

    require_once($GLOBALS['where_lms'].'/lib/lib.middlearea.php');

    $man_ma = new Man_MiddleArea();

    $obj_index = importVar('obj_index', false, '');

    $lang =& DoceboLanguage::createInstance('middlearea', 'lms');
    $selected = $man_ma->getObjIdstList($obj_index);
    $man_ma->setObjIdstList($obj_index, $selected);

    $re = $man_ma->changeDisableStatus($obj_index);


    Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result='.($re ? 'ok' : 'err' ));
}



function set_home_page(){
    require_once($GLOBALS['where_lms'].'/lib/lib.middlearea.php');

    $man_ma = new Man_MiddleArea();
    $obj_index = importVar('obj_index', false, '');
    $selected = $man_ma->setHomePageTab($obj_index);
    Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result=ok');
    
}



function select_permission() {
    checkPerm('view');

    require_once($GLOBALS['where_lms'].'/lib/lib.middlearea.php');
    require_once(_base_.'/lib/lib.userselector.php');
    require_once(_base_.'/lib/lib.form.php');

    $lang =& DoceboLanguage::createInstance('middlearea', 'lms');

    $obj_index = importVar('obj_index', false, '');

    // first step load selector

    $man_ma      = new Man_MiddleArea();
    $acl_manager = new DoceboACLManager();
    $user_select = new UserSelector();

    $user_select->show_user_selector = TRUE;
    $user_select->show_group_selector = TRUE;
    $user_select->show_orgchart_selector = TRUE;
    $user_select->show_orgchart_simple_selector = false;
    //$user_select->multi_choice = TRUE;

    // try to load previous saved
    if(isset($_GET['load'])) {

        $selected = $man_ma->getObjIdstList($obj_index);
        if(is_array($selected))    $user_select->resetSelection($selected);
    }
    if(isset($_POST['okselector'])) {

        $selected = $user_select->getSelection($_POST);
        $re = $man_ma->setObjIdstList($obj_index, $selected);
        Util::jump_to('index.php?modname=middlearea&amp;op=view_area&amp;result='.($re ? 'ok' : 'err' ));
    }


    cout( getTitleArea(array(
            'index.php?modname=middlearea&amp;op=view_area' => $lang->def('_MIDDLE_AREA'),
            Lang::t('_VIEW_PERMISSION', 'standard')
        ), 'middlearea')
        .'<div class="std_block">');
    $user_select->addFormInfo(Form::getHidden('obj_index', 'obj_index', $obj_index));
    $user_select->loadSelector('index.php?modname=middlearea&amp;op=select_permission',
            false,
            false,
            true);

    cout('</div>');
}


//------------------------------------------------------------------------------

function view() {
    //addJs($GLOBALS['where_lms_relative'].'/admin/modules/middlearea/', 'middlearea.js');
    cout(Util::get_js(Get::rel_path('lms').'/admin/modules/middlearea/middlearea.js', true), 'scripts');

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
    cout('<script type="text/javascript">'.$script.'</script>', 'scripts');
}



/**
 * Dispatching
 **/
function MiddleAreaDispatch($op) {

    if(isset($_POST['cancelselector'])) $op = 'view_area';

    switch($op) {
        case "select_permission" : {
            select_permission();
        };break;
        case "switch_active" : {
            switch_active();
        };break;
        case "set_home": {
            set_home_page();
        }
        case "view_area" :
        default : {
            //view_area();
            view_area();
        };break;
    }


}

?>