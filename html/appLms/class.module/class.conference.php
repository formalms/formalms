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

/**
 * @category
 *
 * @author Fabio Pirovano
 *
 * @version $Id:$
 *
 * @since 3.5
 *
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 )
 */
class Module_Conference extends LmsModule
{
    public function loadBody()
    {
        require_once $GLOBALS['where_lms'] . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        dispatchConference($GLOBALS['op']);
    }

    public function getAllToken($op = '')
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'mod' => ['code' => 'mod',
                                'name' => '_SCHEDULE',
                                'image' => 'standard/edit.png', ],
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
            6 => $this->selectPerm($op, 'view,mod'),
            7 => $this->selectPerm($op, 'view,mod'),
        ];
    }
}
