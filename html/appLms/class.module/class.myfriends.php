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
 * @author Fabio Pirovano
 *
 * @version $Id:$
 *
 * @since 3.1.0
 */
class Module_MyFriends extends LmsModule
{
    public function loadBody()
    {
        require_once _lms_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        myfriendsDispatch($GLOBALS['op']);
    }

    public static function getAllToken()
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
        ];
    }
}
