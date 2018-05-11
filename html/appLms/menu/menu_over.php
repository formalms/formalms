<?php defined("IN_FORMA") or die('Direct access is forbidden.');


function LanguageBox()
{
    $lang_model = new LangAdm();
    $lang_list = $lang_model->getLangListNoStat(false, false, 'lang_description', 'ASC');
    $out = '';
    if (count($lang_list) > 1) {
        $out = '<div class="row lang">
                            <div class="col-xs-6">
                                <p>' . Lang::t('_CHANGELANG', 'register') . '</p>
                            </div>
                            <div class="col-xs-6">
                                ' . Layout::buildLanguages() . '
                            </div>
                    </div>';
    }
    return $out;
}


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

if (!Docebo::user()->isAnonymous()) {
    YuiLib::load('base,menu');
    require_once(_lms_ . '/lib/lib.middlearea.php');

    require_once('../widget/lms_block/lib.lms_block_menu.php');
    require_once(_lms_ . '/lib/lib.course.php');
    $widget = new  Lms_BlockWidget_menu();

    //** GESTIONE AREA PROFILO UTENTE **
    $profile = false;
    $credits = false;
    $career = $widget->career();
    $subscribe_course = $widget->subscribe_course();
    $news = $widget->news();


    $ma = new Man_MiddleArea();

    if ($ma->currentCanAccessObj('credits')) {
        $credits = $widget->credits();
    }

    if ($ma->currentCanAccessObj('user_details_full')) {
        require_once(_lib_ . '/lib.user_profile.php');
        $profile = new UserProfile(getLogUserId());
        $profile->init('profile', 'framework', 'index.php?r=' . _lms_home_, 'ap');
        $profile_box = $profile->homeUserProfile('normal', false, false);
        $photo = $profile->homePhotoProfile('normal', false, false);
    }

    $user_level = Docebo::user()->getUserLevelId();

    $query_menu = "
    SELECT mo.idModule, mo.module_name, mo.default_op, mo.mvc_path, mo.default_name, mo.token_associated, mo.module_info
    FROM " . $GLOBALS['prefix_lms'] . "_module AS mo
        JOIN " . $GLOBALS['prefix_lms'] . "_menucourse_under AS under
            ON ( mo.idModule = under.idModule)
    WHERE module_info IN ('all', 'user')   AND mo.idModule NOT IN(7,34)
    ORDER BY module_info, under.sequence ";


    $menu = array();
    $re_menu_voice = sql_query($query_menu);
    while (list($id_m, $module_name, $def_op, $mvc_path, $default_name, $token, $m_info) = sql_fetch_row($re_menu_voice)) {

        if ($ma->currentCanAccessObj('mo_' . $id_m) && checkPerm($token, true, $module_name, true)) {


            // if e-learning tab disabled, show classroom courses
            if ($module_name === 'course' && !$ma->currentCanAccessObj('tb_elearning'))
                $mvc_path = 'lms/classroom/show';


            $menu[$m_info][$id_m] = array(
                'index.php?' . ($mvc_path ? 'r=' . $mvc_path : 'modname=' . $module_name . '&amp;op=' . $def_op) . '&amp;sop=unregistercourse',
                Lang::t($default_name, 'menu_over'),
                false,
                $id_m
            );
        }
    }
    if (isset($menu['all'])) $menu_i = count($menu['all']) - 1;
    else $menu_i = -1;
    $setup_menu = '';

    // Customer help

    if ($ma->currentCanAccessObj('mo_help')) {
        $help_email = trim(Get::sett('customer_help_email', ''));
        $can_send_emails = !empty($help_email) ? true : false;
        $can_admin_settings = checkRole('/framework/admin/setting/view', true);

        $strTxtHelp = Lang::t('_CUSTOMER_HELP', 'customer_help') . "";
        $strHelp = "<span class='glyphicon glyphicon-question-sign top-menu__label'>" . $strTxtHelp . "</span>";

        if ($can_send_emails) {


            cout(Util::get_js(Get::rel_path('base') . '/lib/js_utils.js', true), 'scripts');
            cout(Util::get_js(Get::rel_path('lms') . '/modules/customer_help/customer_help.js', true), 'scripts');


            cout('<script type="text/javascript">' .
                ' var CUSTOMER_HELP_AJAX_URL = "ajax.server.php?mn=customer_help&plf=lms&op=getdialog"; ' .
                ' var ICON_LOADING = "' . Get::tmpl_path() . 'images/standard/loadbar.gif"; ' .
                ' var LANG = new LanguageManager({' .
                '    _CONFIRM: "' . Lang::t('_CONFIRM') . '",' .
                '    _UNDO: "' . Lang::t('_UNDO') . '",' .
                '    _COURSE_NAME: "' . Lang::t('_COURSE_NAME', 'course') . '",' .
                '    _VAL_COURSE_NAME: "' . (isset($GLOBALS['course_descriptor']) ? $GLOBALS['course_descriptor']->getValue('name') : "") . '",' .
                '    _DLG_TITLE: "' . Lang::t('_CUSTOMER_HELP', 'customer_help') . '",' .
                '    _LOADING: "' . Lang::t('_LOADING') . '"' .
                '}); '
                . '</script>'
                , 'scripts');

            $menu['all'][] = array('#inline', $strHelp, 'modalbox no-border-right no-before');
            $customer_help = ++$menu_i;
            $setup_menu .= " oMenuBar.getItem($customer_help).subscribe('click', CustomerHelpShowPopUp);";
        } else {
            // nessun email per help desk
            $menu['all'][] = array('#inline_no_help', $strHelp, 'modalbox no-border-right');
            $menu_i++;
        }
    }


    $pg = new PluginManager('MenuOverEvent');
    $pg->run('hook');

    $event = new \appLms\Events\Lms\MenuOverEvent($menu, $menu_i);
    \appCore\Events\DispatcherManager::dispatch($event::EVENT_NAME, $event);

    $menu = $event->getMenu();
    $menu_i = $event->getMenuI();

    // Link for the administration
    if ($user_level == ADMIN_GROUP_GODADMIN || $user_level == ADMIN_GROUP_ADMIN) {
        $menu['all'][] = array(
            Get::rel_path('adm'),
            Lang::t('_GO_TO_FRAMEWORK', 'menu_over'),
            false
        );
        $menu_i++;
    }


//** DEV: LR - creato un menu_over  responsive  attraverso bootstrap **
    $str_menu_over = '<header class="header white-bg">
                   <!-- Static navbar -->
                   <nav> 
                    <div class="row-fluid" id="lms_menu_container" >
                      <div class="navbar-header" >
                        <a class="navbar-brand" href="?r=elearning/show&sop=unregistercourse">
                          <img class="left_logo" src="' . Layout::path() . '/images/company_logo.png" alt="logo di sinistra"/>
                        </a> 
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                          <span  class="glyphicon glyphicon-align-justify"></span>
                        </button>  
                      </div>        
                      <div id="navbar" class="navbar-collapse collapse" >
                        <ul class="nav navbar-nav" >';

    cout($str_menu_over, 'menu_over');

    foreach ($menu['all'] as $row) {
        $active = "";
        if (strrpos($row[0], $_GET['r']) > 0 || strrpos($row[0], $_GET['modname']) > 0) $active = " class='active'";
        if (isset($_GET['id_cat']) && strpos($row[0], "catalog") > 0) $active = " class='active'";
        // ADMIN
        if (strrpos($row[0], 'appCore') > 0) {
            cout('<li class="green_menu"><a class="no-border-right no-before" href="' . $row[0] . '" title="' . $row[1] . '" title="' . Lang::t('_GO_TO_FRAMEWORK', 'menu_over') . '"><span class="glyphicon glyphicon-cog top-menu__label">' . Lang::t('_GO_TO_FRAMEWORK', 'menu_over') . '</span></a></li> ', 'menu_over');
        } else {
            // HELP DESK
            if (strrpos($row[1], 'sign') > 0) {
                cout('<li class="green_menu" ' . $active . '   ><a href="' . $row[0] . '" class="' . $row[2] . '" title="' . Lang::t('_CUSTOMER_HELP', 'customer_help') . '">' . $row[1] . '</a></li>', 'menu_over');
            } else if ($row[2] === false) {
                cout('<li ' . $active . '   ><a href="' . $row[0] . '" class="' . $row[2] . '" title="' . $row[1] . '"  >' . $row[1] . '</a></li>', 'menu_over');
            }
        }
        if ($row[2] !== false && count($menu[$row[2]]) != 0) {
            cout('<li class="dropdown" id="submenu_' . $id_m . '" >'
                . '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $row[1] . ' <span class="caret"></span></a>'
                . '<ul class="dropdown-menu">', 'menu_over');
            while (list($id_m, $s_voice) = each($menu[$row[2]])) {
                cout('<li>'
                    . '<a  href="' . $s_voice[0] . '"">'
                    . '' . $s_voice[1] . ''
                    . '</a> &nbsp; '
                    . '</li>', 'menu_over');
            }
            cout('</ul>'
                . '</li>', 'menu_over');
        }

    }

    require_once(_lms_ . '/lib/lib.cart.php');
    Learning_Cart::init();
    $num_item = Learning_Cart::cartItemCount();
    if ($num_item > 0) {
        cout('<li><a href="index.php?r=cart/show" id="cart_action" title="' . Lang::t("_CART", "cart") . '"><span  class="glyphicon glyphicon-shopping-cart top-menu__label"><sub id="cart_element" class="num_notify_bar">' . Learning_Cart::cartItemCount() . '</sub></span></a></li>', 'menu_over');
    }

    cout('
                        <li class="green_menu">                                
                            <div id="o-wrapper" class="o-wrapper">
                                <button id="c-button--slide-right" class="c-button" >
                                    <a data-toggle="dropdown"  href="#" title="' . Lang::t('_PROFILE', 'menu_course') . '">
                                       <span class="username icon--profile"> ' . Docebo::user()->getUserName() . '</span>
                                    </a>
                                </button>
                            </div>
                            <!-- /o-wrapper -->
                            
                        </li>
                   </ul>
               </div>
               <div id="c-mask" class="c-mask"></div><!-- /c-mask overlay -->', 'menu_over');

    /*
     *  SPOSTATO C-MENU DA RIGA 210 A RIGA 221 (DOPO I BR DELL'HEADER)
     *
     */

    $right_box = '</div>
                <!--/.nav-collapse -->
                </nav>
               
                <div id="c-menu--slide-right" class="c-menu c-menu--slide-right user-panel">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xs-6">
                                <a href="javascript:void(0)" class="c-menu__close"><span class="glyphicon glyphicon-remove">' . Lang::t('_HIDETREE', 'organization') . '</span></a><!-- pulsante nascondi menu -->
                            </div>
                            <div class="col-xs-6">
                                <a title="' . Lang::t('_LOGOUT', 'standard') . '" href="' . Get::rel_path('base') . '/index.php?r=' . _logout_ . '">
                                    <span class="glyphicon glyphicon-off">' . Lang::t('_LOGOUT', 'standard') . '</span>
                                </a>
                            </div>
                        </div>
                        <div class="tabnav js-tabnav">';
    if ($profile || $credits) {
        $right_box .= '
                        
                            <div class="tabnav__label-wrapper">';
    }
    if ($profile) {
        $right_box .= '<div class="tabnav__label selected" data-tab="profile">
                            ' . Lang::t('_PROFILE', 'standard') . '
                       </div>';
    }
    if ($credits) {
        $right_box .= '<div class="tabnav__label ' . (!$profile ? 'selected' : '') . '" data-tab="credits">
                            ' . Lang::t('_TIME_PERIODS', 'standard') . '
                       </div>';
    }

    $right_box .= '</div>
                        <div class="tabnav__content-wrapper">';
    if ($profile) {
        $right_box .= '<div class="tabnav__content tabnav__content--profile is-visible">
                                    ' . $profile_box . '
                                </div>';
    }
    if ($credits) {
        $right_box .= '<div class="tabnav__content tabnav__content--credits ' . (!$profile ? 'is-visible' : '') . '">
                                    <div class="row credits">
                                        <div class="col-xs-12">
                                        	<div class="js-credits-ajax-container">
                                          		' . $credits . '
											</div>
                                        </div> <!-- end col xs-12 -->
                                    </div>                                        
                                </div>';
    }

    if ($profile || $credits) {
        $right_box .= '</div>
                        ';
    }
    $right_box .= '</div>
                        <div class="row course-subscription">
                            <div class="col-xs-12">
                                ' . $subscribe_course . '
                            </div> <!-- end col xs-12 -->
                        </div>
                        <div class="row news">
                            <div class="col-xs-12">
                                ' . $news . '
                            </div> <!-- end col xs-12 -->
                        </div>';

    cout($right_box, 'menu_over');
    cout(LanguageBox(), 'menu_over');
    cout('<div class="row footer">
                        </div>
                    </div> <!-- /container-fluid -->
                </div><!-- /c-menu slide-right end profile right panel -->
            </header>', 'menu_over');

    $idst = getLogUserId();
    $acl_man = Docebo::user()->getAclManager();
    $user_info = $acl_man->getUser($idst, false);
    $user_email = $user_info[ACL_INFO_EMAIL];


    cout('<!-- hidden inline form -->
            <div id="inline" >
                <form id="contact" name="contact" action="#" method="post"  style="width: 470px;" role="form" style="display: block;"> 
				<legend>' . Lang::t('_CUSTOMER_HELP', 'customer_help') . '</legend>
                    <fieldset>
                              <!-- Form Name -->
                                             
                
                      <input type="hidden" id="sendto" name="sendto" class="txt" value="' . Get::sett('customer_help_email') . '" readonly>
                      <input type="hidden" id="authentic_request_newsletter" name="authentic_request" value="' . Util::getSignature() . '" />
                      <input type="hidden" id="username" name="username" class="txt" value="' . Docebo::user()->getUserId() . '" >
                      <input type="hidden" id="msg_ok" name="msg_ok" class="txt" value="' . Lang::t('_OPERATION_SUCCESSFUL', 'standard') . '" >
                      
                      <input type="hidden" id="help_req_resolution" name="help_req_resolution"   >
                      <input type="hidden" id="help_req_flash_installed" name="help_req_flash_installed" >
                      <table cellspacing=2 cellpaddin=2 width=98% border=0 > 
                     <tr>
                          <td width="27%"><label for="username">' . Lang::t('_USER', 'standard') . '</label></td>
                          <td>
                          <div class="input-group">
                          <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                          <input class="form-control" type="text" id="username" name="username" class="txt" value="' . Docebo::user()->getUserId() . '" readonly>
                          </div>
                          </td>
                     </tr>   
                     <tr>  
                            <td><label for="oggetto">' . Lang::t('_TITLE', 'menu') . '</label></td>
                            <td>
                            <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-file"></span></span>
                            <input  class="form-control" type="oggetto" id="oggetto" name="oggetto" class="txt" placeholder="' . Lang::t('_CUSTOMER_HELP_SUBJ_PFX', 'configuration') . '">
                            </div>
                            </td>
                    </tr>
                  
                    <tr>
                          <td><label for="email">' . Lang::t('_EMAIL', 'menu') . '</label> </td>
                          <td>
                          <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-envelope"></span></span>
                            <input class="form-control" type="email" id="email" name="email" class="txt" value="' . $user_email . '" placeholder="">
                          </div>
                          </td>
                    </tr>    
                    <tr>
                  <td><label for="telefono">' . Lang::t('_PHONE', 'classroom') . '</label></td>
                      <td>
                        <div class="input-group">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-phone-alt"></span></span>
                        <input class="form-control" type="text" id="telefono" name="telefono" class="txt" placeholder="">
                      </div>
                      </td>
                    </tr>
                <tr>
                      <td><label for="msg">' . Lang::t('_TEXTOF', 'menu') . '</label></td>
                      <td><textarea id="msg" name="msg" class="txtarea" placeholder="' . Lang::t('_WRITE_ASK_A_FRIEND', 'profile') . '"></textarea></td>
                </tr>
                <tr>
                      <td><label for="copia">' . Lang::t('_SEND_CC', 'standard') . '</label></td>
                      <td>   <input id="copia" name="copia" checked data-toggle="toggle" data-on="' . Lang::t('_GROUP_FIELD_NORMAL', 'admin_directory') . '"  data-size="small" data-off="' . Lang::t('_NO', 'standard') . '" data-onstyle="success" data-offstyle="danger" type="checkbox">
                   
                      </td>
                </tr>                
                 <tr>
                      <td><label for="priorita">' . Lang::t('_PRIORITY', 'message') . '</label></td>
                      <td>
                       <input id="priorita" name="priorita" data-size="small" checked data-toggle="toggle" data-on="' . Lang::t('_NORMAL', 'message') . '" data-off="' . Lang::t('_HIGH', 'message') . '" data-onstyle="success" data-offstyle="danger" type="checkbox">

                    </td>
                 </tr>           
                 <tr>
                      <td><label for="disclaimer">' . Lang::t('_CUSTOMER_HELP_DISCLAIMER_TITLE', 'customer_help') . '</label></td>
                      <td>
                      <p>' . Lang::t('_CUSTOMER_HELP_DISCLAIMER', 'customer_help') . '</p><br>
                       <input id="disclaimer" name="disclaimer" data-size="small" data-toggle="toggle" data-on="' . Lang::t('_NORMAL', 'message') . '" data-off="' . Lang::t('_HIGH', 'message') . '" data-onstyle="success" data-offstyle="danger" type="checkbox">
                    </td>
                 </tr>                  
                </table>
                   <br>

                      <table width=88% border=0 bgcolor="#ffcc99"> <tr><td align=center>
                        <button id="close">' . Lang::t('_CANCEL') . '</button>
                    </td><td align=center>
                       <button id="send" disabled="true">' . Lang::t('_CONFIRM') . '</button>
                    </td></tr>
                      </table>

        </fieldset>
     </form>                    
</div>', 'menu_over');


    cout('<!-- hidden inline form -->
 <style>
#inline_no_help {
    display: none;
    width: 100px;
    height: 100px;
}
</style>

<script>
$("#disclaimer").change(function() {
    $("#send").attr("disabled", !$("#disclaimer").is(":checked"));
});

</script>

            <div id="inline_no_help" >
                    No Help Desk            
        
</div>', 'menu_over');


}


?>


