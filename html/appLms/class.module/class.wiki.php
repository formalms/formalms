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

class Module_Wiki extends LmsModule
{
    public function loadBody()
    {
        require_once _lms_ . '/modules/wiki/wiki.php';
        wikiDispatch($GLOBALS['op']);
    }

    public function useExtraMenu()
    {
        return false;
    }

    public function getAllToken()
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'edit' => ['code' => 'edit',
                                'name' => '_MOD_WIKI',
                                'image' => 'standard/edit.png', ],
            'admin' => ['code' => 'admin',
                                'name' => '_ADMIN_WIKI',
                                'image' => 'standard/property.png', ],
        ];
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, 'view'),
            2 => $this->selectPerm($op, 'view'),
            3 => $this->selectPerm($op, 'view'),
            4 => $this->selectPerm($op, 'view'),
            5 => $this->selectPerm($op, 'view,edit'),
            6 => $this->selectPerm($op, 'view,edit'),
            7 => $this->selectPerm($op, 'view,edit,admin'),
        ];
    }
}
