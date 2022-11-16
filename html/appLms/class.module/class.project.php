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

class Module_Project extends LmsModule
{
    public function loadBody()
    {
        require_once _lms_ . '/modules/project/project.php';
        projectDispatch($GLOBALS['op']);
    }

    public function useExtraMenu()
    {
        return false;
    }

    public static function getAllToken()
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'add' => ['code' => 'add',
                                'name' => '_ALT',
                                'image' => 'standard/add.png', ],
            'mod' => ['code' => 'mod',
                                'name' => '_MOD',
                                'image' => 'standard/edit.png', ],
            'del' => ['code' => 'del',
                                'name' => '_DEL',
                                'image' => 'standard/delete.png', ],
        ];
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, 'view'),
            2 => $this->selectPerm($op, 'view'),
            3 => $this->selectPerm($op, 'view'),
            4 => $this->selectPerm($op, 'view'),
            5 => $this->selectPerm($op, 'view,mod'),
            6 => $this->selectPerm($op, 'view,add,mod,del'),
            7 => $this->selectPerm($op, 'view,add,mod,del'),
        ];
    }
}
