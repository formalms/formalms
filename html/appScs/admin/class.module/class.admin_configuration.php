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
 * @version  $Id: class.admin_configuration.php 113 2006-03-08 18:08:42Z ema $
 *
 * @category Configuration
 */
require_once dirname(__FILE__) . '/class.definition.php';

class Module_Admin_configuration extends ScsAdminModule
{
    public function loadBody()
    {
        require_once dirname(__FILE__) . '/../modules/' . $this->module_name . '/' . $this->module_name . '.php';
        adminConfDispatch($GLOBALS['op']);
    }

    // Function for permission managment

    public function getAllToken($op)
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
            'mod' => ['code' => 'mod',
                                'name' => '_MOD',
                                'image' => 'standard/edit.png', ],
        ];
    }
}
