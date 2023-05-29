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
 * @version  $Id: class.catalogue.php 573 2006-08-23 09:38:54Z fabio $
 *
 * @category Course managment
 */
require_once __DIR__ . '/class.definition.php';

class Module_Catalogue extends LmsAdminModule
{
    public function loadBody()
    {
        require_once dirname(__DIR__) . '/modules/catalogue/catalogue.php';
        catalogueDispatch($GLOBALS['op']);
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
            'subscribe' => ['code' => 'associate',
                                'name' => '_ASSIGN_USERS',
                                'image' => 'standard/groups.gif', ],
        ];
    }
}
