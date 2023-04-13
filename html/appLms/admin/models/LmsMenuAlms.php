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

class LmsMenuAlms extends Model
{
    protected $db;

    protected $aclManager;
    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->aclManager = Forma::user()->getACLManager();
        parent::__construct();
    }

    /**
     * Method to get role members bounded to menu
     * 
     * @param int $idMenu id menu da recuperare
     * 
     * @return array
    */
    public function getRoleMembers(int $idMenu) : array {

        $menu = CoreMenu::get($idMenu);
        $roleIdst = $this->aclManager->getRole(false, $menu->role)[0];
    
        $members = $this->aclManager->getRoleMembers($roleIdst);
        
        return $members;
    }


    /**
     * Method to save role members bounded to menu
     * 
     * @param int $idMenu id menu da recuperare
     * @param array $selection array di idst selezionati
     * @param array $oldMembers array degli idst vecchi selezionati
     * 
     * @return bool
    */
    public function saveMembersAssociation(int $idMenu, array $selection, array $oldMemebers) : bool {
        $menu = CoreMenu::get($idMenu);
        $roleIdst = $this->aclManager->getRole(false, $menu->role)[0];
        foreach($oldMemebers as $oldMember) {
            $this->aclManager->removeFromRole($roleIdst, $oldMember);
        }
        
        foreach ($selection as $member) {
            $this->aclManager->addToRole($roleIdst, $member);
        }

        return true;
    }

}