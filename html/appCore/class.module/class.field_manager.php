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

require_once dirname(__FILE__) . '/class.definition.php';

class Module_Field_Manager extends Module
{
    public function loadBody()
    {
        require_once _adm_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
    }

    public static function getAllToken($op = null)
    {
        return [
            'view' => ['code' => 'view',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],

            'add' => ['code' => 'add',
                                'name' => '_ADD',
                                'image' => 'standard/add.png', ],

            'mod' => ['code' => 'mod',
                                'name' => '_MOD',
                                'image' => 'standard/edit.png', ],

            'del' => ['code' => 'del',
                                'name' => '_DEL',
                                'image' => 'standard/delete.png', ],
        ];
    }
}
