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
require_once dirname(__FILE__) . '/class.definition.php';

class Module_MiddleArea extends LmsAdminModule
{
    public function loadBody()
    {
        require_once Forma::inc(_lms_ . '/admin/modules/middlearea/middlearea.php');
        MiddleAreaDispatch($GLOBALS['op']);
    }

    // Function for permission managment

    public function getAllToken($op)
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
        ];
    }
}
