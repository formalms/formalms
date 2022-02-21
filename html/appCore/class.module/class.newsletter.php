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

class Module_Newsletter extends Module
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
        global $op, $modname, $prefix;
        require_once $GLOBALS['where_framework'] . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
    }
}
