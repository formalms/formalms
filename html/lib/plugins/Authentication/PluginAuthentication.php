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

abstract class PluginAuthentication extends FormaPlugin
{
    public const AUTH_TYPE_BASE = 'baseLogin';
    public const AUTH_TYPE_SOCIAL = 'socialLogin';

    protected static $session = null;

    public function __construct()
    {
        $this->session = \Forma\lib\Session\SessionManager::getInstance()->getSession();
    }
}
