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

/**
 * @return string The Admin User panel
 */
function GetAdminPanel(){

    return '
        <li data-sm-reverse="true" style="float:right">
            <a href="#"><i class="fa fa-user"></i>&nbsp;<b>'.Docebo::user()->getUserName().'</b></a>
            <ul>
                  <li>
                        <a href="index.php?r=lms/profile/show">'.Lang::t('_PROFILE', 'profile').'</a>   
                  </li>
                  <li> 
                    <a id="logout"  href='.Get::rel_path('base').'/index.php?r='. _logout_ .' ">                
                        <i class="fa fa-power-off" aria-hidden="true"></i>&nbsp;'.Lang::t('_LOGOUT', 'standard').'</span>
                    </a>
                  </li>
            </ul>
          </li>'.BackToLms();
     
}

/**
 * @return string The default Company Logo
 */
function GetCompanyLogo(){
    
    return  '<a href="index.php?r=adm/dashboard/show">
            <img width=100px src="'.Layout::path().'images/company_logo.png" alt="Left logo" />
            </a>';
}

function BackToLms(){
    $lang     =& DoceboLanguage::createInstance('menu', 'framework');
    return '<li data-sm-reverse="true" style="float:right">
                <a href="'.$GLOBALS['where_lms_relative'].'"><i class="fa fa-home" aria-hidden="true"></i>&nbsp;'. 
                    $lang->def('_LMS', 'platform').
                '</a>
            </li>';
}

function AdminBar()
{
    $amenu=array();
    $current_platform = "framework";//$_SESSION['current_action_platform'];

    $p_man 	=& MenuManager::createInstance($current_platform);
    $lang =& DoceboLanguage::createInstance('menu', 'framework');

    $strLabel = '';
    $strIco='';

    $menu_man=$p_man->getMenuInstanceFramework($current_platform);
    //Level0
    $menus 	= $p_man->getLevel();

    $admin_menu_bar = '<nav id="main-nav" role="navigation">
                        <ul id="main-menu" class="sm sm-forma">
                        <li>'.GetCompanyLogo().'</li>';

    foreach ($menus as $p_code => $p_name) {
//        $menu_man=$p_man->getPlatofmMenuInstanceFramework($p_code);
        
        $strLabel = $p_name['name'];//$lang->def($p_name['name'], 'menu', $current_platform);
        $strIco = $p_name['image'];
        $strLink = Util::str_replace_once('&', '&amp;', $p_name['link']);

        $idmenu=$p_man->menu[$p_code]['idMenu'];
        //Level1
        $main_voice = $p_man->getLevel($idmenu);
        if (!empty($main_voice)) {
            $admin_menu_bar .= '<li><a href="'. ($strLink != '' ? $strLink : '#' ) . '">' . $strIco . '&nbsp;' . $strLabel . '</a>' . PHP_EOL;
            $admin_menu_bar .= '<ul>' . PHP_EOL;
            foreach ($main_voice as $id_m => $v_main) {
                $admin_menu_bar .= '<li>';
                $admin_menu_bar .= '<a href="' . Util::str_replace_once('&', '&amp;', $v_main['link']) . '" >' . $v_main['name'] . '</a>';
                //Level2
                $under_voice = $p_man->getLevel($id_m);
                if (!empty($under_voice)) {
                    $admin_menu_bar .= '<ul>' . PHP_EOL;
                    foreach ($under_voice as $id_m => $voice) {
                        $admin_menu_bar .= '<li>';
                        $admin_menu_bar .= '<a href="' . Util::str_replace_once('&', '&amp;', $voice['link']) . '" >' . $voice['name'] . '</a>';
                        $admin_menu_bar .= '</li>';
                        $admin_menu_bar .= '</li>' . PHP_EOL;
                    }
                    $admin_menu_bar .= '</ul>' . PHP_EOL;
                }
            }
            $admin_menu_bar .= '</ul></li>' . PHP_EOL;
        }
        else{
            $admin_menu_bar .= '<li>';
            $admin_menu_bar .= '<a href="'. ($strLink != '' ? $strLink : '#' ) . '">' . $strIco . '&nbsp;' . $strLabel . '</a>';
            $admin_menu_bar .= '</li>';
        }
    }

    $admin_menu_bar .=  GetAdminPanel();
    $admin_menu_bar .= '</ul>'.PHP_EOL;
    return $admin_menu_bar;
}


cout(AdminBar(), 'menu_over');
