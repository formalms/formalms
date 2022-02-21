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

class Module_Statistic extends LmsModule
{
    public function loadBody()
    {
        require_once $GLOBALS['where_lms'] . '/modules/statistic/statistic.php';
        statisticDispatch($GLOBALS['op']);
    }

    public function getAllToken()
    {
        return ['view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
                    'view_all' => ['code' => 'view_all',
                                'name' => '_VIEW_ALL',
                                'image' => 'standard/moduser.png', ], ];
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, ''),
            2 => $this->selectPerm($op, ''),
            3 => $this->selectPerm($op, ''),
            4 => $this->selectPerm($op, 'view'),
            5 => $this->selectPerm($op, 'view'),
            6 => $this->selectPerm($op, 'view,view_all'),
            7 => $this->selectPerm($op, 'view,view_all'),
        ];
    }
}
