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
 * @version  $Id: class.certificate.php,v 1
 *
 * @category Certification management
 *
 * @author	 Claudio Demarinis <claudiodema [at] docebo [dot] com>
 */
require_once Forma::inc(_lms_ . '/admin/class.module/class.definition.php');

class Module_Certificate extends LmsAdminModule
{
    public function loadBody()
    {
        require_once Forma::inc(_lms_ . '/admin/modules/' . $this->module_name . '/' . $this->module_name . '.php');
        certificateDispatch($GLOBALS['op']);
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
            'assign' => ['code' => 'assign',
                                'name' => '_ASSIGN',
                                'image' => 'standard/assign.png', ],
            'release' => ['code' => 'release',
                                'name' => '_RELEASE',
                                'image' => 'standard/release.png', ],
        ];
    }
}
