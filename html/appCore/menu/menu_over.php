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
                    <a href="index.php">'.Lang::t('_DASHBOARD', 'dashboard').'</a>
                  </li>  
                  <li><a href="index.php?r=lms/profile/show">'.Lang::t('_PROFILE', 'profile').'</a>   
                  </li>'.
                        BackToLms()
                  .'<li> 
                    <a id="logout"  href="index.php?modname=login&amp;op=logout">                
                        <i class="fa fa-power-off" aria-hidden="true"></i>&nbsp;'.Lang::t('_LOGOUT', 'standard').'</span>
                    </a>
                  </li>
            </ul>
          </li> <li data-sm-reverse="true" style="float:right">'.Layout::change_lang().'</li>';
     
}


/**
 * @return string The default Company Logo
 */
function GetCompanyLogo()
{
    return  '<h2 class="nav-brand"><a href="#">
            <img width=100px src="'.Layout::path().'images/company_logo.png" alt="Left logo" />
            </a></h2>';
}

function BackToLms(){
    $lang     =& DoceboLanguage::createInstance('menu', 'framework');
    return '<li>
                <a href="'.$GLOBALS['where_lms_relative'].'"><i class="fa fa-home" aria-hidden="true"></i>&nbsp;'. 
                    $lang->def('_LMS', 'platform').
                '</a>
            </li>';
}


/**
 * @return array The menu label and its icon
 * @param  int The current menu header
 */
function GetMenuHeader($pcode){
    $lang =& DoceboLanguage::createInstance('menu', 'framework');

    $strLabel = '';
    $strIco='';
    switch ($pcode) {
        case 'menu_user':
            $strLabel = $lang->def('_USER_MANAGMENT', 'menu', 'framework');
            $strIco = '<i class="fa fa-users fa-fw"></i>';
            break;
        case 'menu_elearning':
            $strLabel = $lang->def('_FIRST_LINE_lms', 'menu', 'framework');
            $strIco = '<span class="glyphicon glyphicon glyphicon-education" aria-hidden="true"></span>';
            break;
        case 'menu_content':
            $strLabel = $lang->def('_CONTENTS', 'standard', 'framework');
            $strIco = '<i class="fa fa-clipboard fa-fw"></i>';
            break;
        case 'menu_report':
            $strLabel = $lang->def('_REPORT', 'standard', 'framework');
            $strIco = '<i class="fa fa-bar-chart-o fa-fw"></i>';
            break;
        case 'menu_config':
            $strLabel = $lang->def('_CONFIGURATION', 'menu', 'framework');
            $strIco = '<i class="fa fa-cogs fa-fw"></i>';
            break;
    }
    return array("strLabel"=>$strLabel, "strIco"=>$strIco);
}

function AdminBar()
{
    $p_man 	=& PlatformManager::createInstance();
    $platforms 	= $p_man->getPlatformList();
    $admin_menu_bar = '<nav id="main-nav" role="navigation">'
                    .GetCompanyLogo().
                    '<ul id="main-menu" class="sm sm-forma">';


    foreach ($platforms as $p_code => $p_name) {
        $menu_man = $p_code == 'lms' ? $p_man->getPlatofmMenuInstance($p_code) : $p_man->getPlatofmMenuInstanceFramework($p_code);
        $header = GetMenuHeader($p_code);


        if ($menu_man !== false) {
            $main_voice = $menu_man->getLevelOne();
            if (!empty($main_voice)) {
                $admin_menu_bar .= '<li><a href="#">' . $header["strIco"] . '&nbsp;' . $header["strLabel"] . '</a><ul>' . PHP_EOL;
                foreach ($main_voice as $id_m => $v_main) {
                    $under_voice = $menu_man->getLevelTwo($id_m);
                    if ($v_main['collapse'] == true) {
                        foreach ($under_voice as $id_m => $voice) {
                            $admin_menu_bar .= '<li><a href="' . Util::str_replace_once('&', '&amp;', $voice['link']) . '" >' . $voice['name'] . '</a></li>' . PHP_EOL;
                        }
                    }

                    if (!isset($v_main['collapse']) || $v_main['collapse'] === false) {
                        $admin_menu_bar .= '<li><a href="#">' . $v_main["name"] . '</a>' . PHP_EOL;
                        $admin_menu_bar .= '<ul>' . PHP_EOL;
                        foreach ($under_voice as $id_m => $voice) {
                            $admin_menu_bar .= '<li><a href="' . Util::str_replace_once('&', '&amp;', $voice['link']) . '" ><span class="desc">' . $voice['name'] . '</span></a></li>' . PHP_EOL;
                        }
                        $admin_menu_bar .= '</ul>' . PHP_EOL;
                        $admin_menu_bar .= '</li>' . PHP_EOL;
                    }

                }
                $admin_menu_bar .= '</ul></li>' . PHP_EOL;
            }
        }
    }

    $admin_menu_bar .=  GetAdminPanel();
    $admin_menu_bar .= '</ul>'.PHP_EOL;
    return $admin_menu_bar;
}


cout(AdminBar(), 'menu_over');
