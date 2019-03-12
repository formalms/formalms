<?php
namespace Plugin\Dummy;
defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                           |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */


class Plugin extends \FormaPlugin {
    public function install() {

        $acl_manager = \Docebo::user()->getAclManager();

        $permission_godadmin = array($acl_manager->getGroupST(ADMIN_GROUP_GODADMIN));
        $permission_org_chart_root = array($acl_manager->getGroupST('/oc_0'));
        
        // CORE MENU
        $menu = array ('name' => "_DUMMY_MENU_BUTTON", 'ofPlatform' => 'alms');
        $idMenu=self::addMenu($menu, null, $permission_godadmin);

        $menu = array ('name' => "_DUMMY_MENU_ARROW", 'idParent'=>$idMenu, 'ofPlatform' => 'alms');
        $idMenuArrow=self::addMenu($menu, null, $permission_godadmin);

        $menu = array ('name' => "_DUMMY_MENU_UNDER", 'idParent'=>$idMenuArrow, 'ofPlatform' => 'alms');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_MENU_UNDER"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'alms/dummy/show'
        , 'ofPlatform' => 'alms');
        $idMenuUnder=self::addMenu($menu, $menu_under, $permission_godadmin);

        $menu = array ('name' => "_DUMMY_MENU_RENDER_CALL", 'idParent'=>$idMenu, 'ofPlatform' => 'alms');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_MENU_RENDER_CALL"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'alms/dummy/render_call'
        , 'ofPlatform' => 'alms');
        $idMenu=self::addMenu($menu, $menu_under, $permission_godadmin);

        self::addRequest("alms", "dummy", "DummyAlmsController", "DummyAlms");

        // LMS MENU
        $menu = array ('name' => "_DUMMY_LMS_BUTTON", 'ofPlatform' => 'lms');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_LMS_BUTTON"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'lms/dummy/show'
        , 'ofPlatform' => 'lms');
        self::addMenu($menu, $menu_under, $permission_org_chart_root);

        self::addRequest("lms", "dummy", "DummyLmsController", "DummyLms");


        // addSetting is used to add a new setting in forma.lms
        parent::addSetting('dummy.foo', 'string', 255);
    }
}