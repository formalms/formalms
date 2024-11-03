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
 * @version  $Id: class.course.php 1003 2007-03-31 13:59:46Z fabio $
 *
 * @category Category
 */
require_once dirname(__FILE__) . '/class.definition.php';

class Module_Stats extends LmsAdminModule
{
    public function loadBody()
    {
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
