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
        
        // CORE MENU
        $menu = array ('name' => "_DUMMY_MENU_BUTTON", 'ofPlatform' => 'framework');
        $idMenu=self::addMenu($menu, null, array(1));

        $menu = array ('name' => "_DUMMY_MENU_ARROW", 'idParent'=>$idMenu, 'ofPlatform' => 'framework');
        $idMenuArrow=self::addMenu($menu, null, array(1));

        $menu = array ('name' => "_DUMMY_MENU_UNDER", 'idParent'=>$idMenuArrow, 'ofPlatform' => 'framework');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_MENU_UNDER"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'alms/dummy/show'
        , 'ofPlatform' => 'alms');
        $idMenuUnder=self::addMenu($menu, $menu_under, array(1));

        $menu = array ('name' => "_DUMMY_MENU_RENDER_CALL", 'idParent'=>$idMenu, 'ofPlatform' => 'framework');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_MENU_RENDER_CALL"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'alms/dummy/render_call'
        , 'ofPlatform' => 'alms');
        $idMenu=self::addMenu($menu, $menu_under, array(1));

        self::addRequest("alms", "dummy", "DummyAlmsController", "DummyAlms");

        // LMS MENU
        $menu = array ('name' => "_DUMMY_LMS_BUTTON", 'ofPlatform' => 'lms');
        $menu_under = array ('moduleName' => "dummy"
        , 'defaultName' => "_DUMMY_LMS_BUTTON"
        , 'associatedToken'=>'view'
        , 'mvcPath'=>'lms/dummy/show'
        , 'ofPlatform' => 'lms');
        self::addMenu($menu, $menu_under, array(1));

        self::addRequest("lms", "dummy", "DummyLmsController", "DummyLms");


        // addSetting is used to add a new setting in forma.lms
        parent::addSetting('dummy.foo', 'string', 255);
    }
}