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

class Module_Scorm extends LmsModule
{
    //class constructor
    public function __construct($module_name = '')
    {
        parent::__construct();
    }

    public function loadBody()
    {
        include \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm.php');
    }
}
