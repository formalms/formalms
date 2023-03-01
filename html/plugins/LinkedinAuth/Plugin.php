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

namespace Plugin\LinkedinAuth;

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Plugin extends \FormaPlugin
{
    public static function getModule() {
		return "linkedinauth";
	}

    public static function install()
    {
        parent::addSetting('linkedin.oauth_key', 'string', 255);
        parent::addSetting('linkedin.oauth_secret', 'string', 255);
    }

    public static function uninstall()
    {

    }

    public static function activate()
    {

    }    

    public static function deactivate()
    {
        
    }
}
