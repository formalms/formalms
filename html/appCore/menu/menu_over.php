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

/**
 * @return string The Admin User panel
 */
function GetAdminPanel()
{
    $ret_str = '
        <li data-sm-reverse="true" style="float:right">
            <a href="#"><i class="fa fa-user"></i>&nbsp;<b>' . \FormaLms\lib\FormaUser::getCurrentUser()->getUserName() . '</b></a>
            <ul> ';

    if (FormaLms\lib\Get::sett('profile_modify', 'allow') != 'disallow') {
        $ret_str .= '<li>
                        <a href="index.php?r=lms/profile/show">' . Lang::t('_PROFILE', 'profile') . '</a>   
                  </li> ';
    }
    $ret_str .= '<li> 
            <a id="logout"  href="' . FormaLms\lib\Get::rel_path('base') . '/index.php?r=' . _logout_ . '">                
                <i class="fa fa-power-off" aria-hidden="true"></i>&nbsp;' . Lang::t('_LOGOUT', 'standard') . '</span>
            </a>
          </li>
    </ul>
  </li>' . BackToLms();

    return $ret_str;
}

/**
 * @return string The default Company Logo
 */
function GetCompanyLogo()
{
    return '<a href="index.php?r=adm/dashboard/show" class="forma-admin-logo">
                <img src="' . Layout::path() . 'images/company_logo_admin.png" alt="Left logo" />
            </a>';
}

function BackToLms()
{
    $lang = FormaLanguage::createInstance('menu', 'framework');

    return '<li data-sm-reverse="true" style="float:right">
                <a href="' . $GLOBALS['where_lms_relative'] . '"><i class="fa fa-home" aria-hidden="true"></i>&nbsp;' .
                    $lang->def('_LMS', 'platform') .
                '</a>
            </li>';
}

function AdminBar()
{
    $admin_menu_bar = '<nav id="main-nav" role="navigation">
                        <ul id="main-menu" class="sm sm-forma">
                        <li>' . GetCompanyLogo() . '</li>';

    $menu = CoreMenu::getList(['framework', 'alms']);

    $menu_html = '';
    foreach ($menu as $menu_0) {
        $menu_1_html = '';
        foreach ($menu_0->submenu as $menu_1) {
            $menu_2_html = '';
            foreach ($menu_1->submenu as $menu_2) {
                if (checkRole($menu_2->role)) {
                    $menu_2_html .= "<li><a href='$menu_2->url'>" . Lang::t($menu_2->name, 'menu') . '</a></li>';
                }
            }
            if ($menu_2_html) {
                $menu_1_html .= "<li><a href='#'>" . Lang::t($menu_1->name, 'menu') . "</a><ul>$menu_2_html</ul></li>";
            } elseif (checkRole($menu_1->role)) {
                $menu_1_html .= "<li><a href='$menu_1->url'>" . Lang::t($menu_1->name, 'menu') . '</a></li>';
            }
        }
        if ($menu_1_html) {
            $menu_html .= "<li><a href='#'>$menu_0->image&nbsp;" . Lang::t($menu_0->name, 'menu') . "</a><ul>$menu_1_html</ul></li>";
        } elseif (checkRole($menu_0->role)) {
            $menu_html .= "<li><a href='$menu_0->url'>$menu_0->image&nbsp;" . Lang::t($menu_0->name, 'menu') . '</a></li>';
        }
    }

    $admin_menu_bar .= $menu_html;
    $admin_menu_bar .= GetAdminPanel();
    $admin_menu_bar .= '</ul>';

    return $admin_menu_bar;
}

cout(AdminBar(), 'menu_over');
