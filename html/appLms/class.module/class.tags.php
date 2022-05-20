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

defined('IN_FORMA') or exit("You can't access this file directly");

class Module_Tags extends LmsModule
{
    public function loadBody()
    {
        require_once _lms_ . '/modules/tags/tags.php';
        tags_dispatch($GLOBALS['op']);
    }

    public function getAllToken()
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
