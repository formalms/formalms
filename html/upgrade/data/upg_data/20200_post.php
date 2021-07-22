<?php // if (!defined('IN_FORMA')) { die('You can\'t access!'); }

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

// if this file is not needed for a specific version,
// just don't create it.

require_once _lib_ . '/lib.bootstrap.php';

function postUpgrade20200() {

    Boot::init(BOOT_CONFIG);
    LSMMenu();

    return true;
}

function LSMMenu() {

    require_once _lib_ . '/lib.aclmanager.php';
    require_once _lms_ . '/lib/lib.middlearea.php';
    require_once _lib_ . '/lib.coremenu.php';

    $acl_manager = new DoceboACLManager();
    $middlearea_manager = new Man_MiddleArea();

    $mycourses_visibility   = $middlearea_manager->getObjIdstList('mo_1');
    $mycourses_active       = !$middlearea_manager->isDisabled('mo_1');
    $catalogue_visibility   = $middlearea_manager->getObjIdstList('mo_46');
    $catalogue_active       = !$middlearea_manager->isDisabled('mo_46');
    $forum_visibility       = $middlearea_manager->getObjIdstList('mo_32');
    $forum_active           = !$middlearea_manager->isDisabled('mo_32');
    $helpdesk_visibility    = $middlearea_manager->getObjIdstList('mo_help');
    $helpdesk_active        = !$middlearea_manager->isDisabled('mo_help');

    CoreMenu::set(596, array('active' => $mycourses_active));
    if(count($mycourses_visibility)) {
        $acl_manager->removeFromRole(80, 1);
        foreach($mycourses_visibility as $idst) {
            $acl_manager->addToRole(80, $idst);
        }
    }
    CoreMenu::set(597, array('active' => $catalogue_active));
    if(count($catalogue_visibility)) {
        $acl_manager->removeFromRole(82, 1);
        foreach($catalogue_visibility as $idst) {
            $acl_manager->addToRole(82, $idst);
        }
    }
    CoreMenu::set(598, array('active' => $forum_active));
    if(count($forum_visibility)) {
        $acl_manager->removeFromRole(91, 1);
        foreach($forum_visibility as $idst) {
            $acl_manager->addToRole(91, $idst);
        }
    }
    CoreMenu::set(599, array('active' => $helpdesk_active));
    if(count($helpdesk_visibility)) {
        $acl_manager->removeFromRole(300, 1);
        foreach($helpdesk_visibility as $idst) {
            $acl_manager->addToRole(300, $idst);
        }
    }
}
