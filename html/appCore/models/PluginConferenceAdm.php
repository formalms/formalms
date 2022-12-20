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

include_once __DIR__ . '/PluginmanagerAdm.php';

class PluginConferenceAdm extends PluginManagerAdm
{
    public string $CATEGORY;

    public function __construct()
    {
        parent::__construct();
        $this->CATEGORY = 'conference';
    }
}
