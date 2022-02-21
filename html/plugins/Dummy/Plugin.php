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

namespace Plugin\Dummy;

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Plugin extends \FormaPlugin
{
    public function install()
    {
        $acl_manager = \Docebo::user()->getAclManager();

        $permission_godadmin = [$acl_manager->getGroupST(ADMIN_GROUP_GODADMIN)];
        $permission_org_chart_root = [$acl_manager->getGroupST('/oc_0')];

        // CORE MENU
        $menu = ['name' => '_DUMMY_MENU_BUTTON', 'ofPlatform' => 'alms'];
        $idMenu = self::addMenu($menu, null, $permission_godadmin);

        $menu = ['name' => '_DUMMY_MENU_ARROW', 'idParent' => $idMenu, 'ofPlatform' => 'alms'];
        $idMenuArrow = self::addMenu($menu, null, $permission_godadmin);

        $menu = ['name' => '_DUMMY_MENU_UNDER', 'idParent' => $idMenuArrow, 'ofPlatform' => 'alms'];
        $menu_under = ['moduleName' => 'dummy', 'defaultName' => '_DUMMY_MENU_UNDER', 'associatedToken' => 'view', 'mvcPath' => 'alms/dummy/show', 'ofPlatform' => 'alms'];
        $idMenuUnder = self::addMenu($menu, $menu_under, $permission_godadmin);

        $menu = ['name' => '_DUMMY_MENU_RENDER_CALL', 'idParent' => $idMenu, 'ofPlatform' => 'alms'];
        $menu_under = ['moduleName' => 'dummy', 'defaultName' => '_DUMMY_MENU_RENDER_CALL', 'associatedToken' => 'view', 'mvcPath' => 'alms/dummy/render_call', 'ofPlatform' => 'alms'];
        $idMenu = self::addMenu($menu, $menu_under, $permission_godadmin);

        self::addRequest('alms', 'dummy', 'DummyAlmsController', 'DummyAlms');

        // LMS MENU
        $menu = ['name' => '_DUMMY_LMS_BUTTON', 'ofPlatform' => 'lms'];
        $menu_under = ['moduleName' => 'dummy', 'defaultName' => '_DUMMY_LMS_BUTTON', 'associatedToken' => 'view', 'mvcPath' => 'lms/dummy/show', 'ofPlatform' => 'lms'];
        self::addMenu($menu, $menu_under, $permission_org_chart_root);

        self::addRequest('lms', 'dummy', 'DummyLmsController', 'DummyLms');

        // addSetting is used to add a new setting in forma.lms
        parent::addSetting('dummy.foo', 'string', 255);
    }
}
