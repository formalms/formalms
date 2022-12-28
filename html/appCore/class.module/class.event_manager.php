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
 * @version  $Id: class.event_manager.php 220 2006-04-09 14:55:58Z fabio $
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 */
require_once _base_ . '/lib/lib.event.php';
require_once _adm_ . '/class.module/class.definition.php';

class Module_Event_Manager extends Module
{
    public function useExtraMenu()
    {
        return true;
    }

    public function loadExtraMenu()
    {
        loadAdminModuleLanguage($this->module_name);
    }

    public function loadBody()
    {
        require_once _adm_ . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        eventDispatch($GLOBALS['op']);
    }

    // Function for permission managment
    public static function getAllToken($op)
    {
        return [
            'view' => ['code' => 'view_event_manager',
                                'name' => '_VIEW',
                                'image' => 'standard/view.png', ],
        ];
        $op = $op;
    }
}
