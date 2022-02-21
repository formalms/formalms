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

namespace Plugin\ConferenceBBB;

class Plugin extends \FormaPlugin
{
    public function install()
    {
        // test salt : 8cd8ef52e8e101574e400365b55e11a6
        parent::addSetting('ConferenceBBB_max_mikes', 'string', 255, '2');
        parent::addSetting('ConferenceBBB_max_participant', 'string', 255, '300');
        parent::addSetting('ConferenceBBB_max_room', 'string', 255, '999');
        parent::addSetting('ConferenceBBB_password_moderator', 'string', 255, 'password.moderator');
        parent::addSetting('ConferenceBBB_password_viewer', 'string', 255, 'password.moderator');
        parent::addSetting('ConferenceBBB_salt', 'string', 255, 'to be changed with a complex string');
        parent::addSetting('ConferenceBBB_server', 'string', 255, 'http://test-install.blindsidenetworks.com/bigbluebutton/');
        parent::addSetting('ConferenceBBB_port', 'string', 255, '80');
        parent::addSetting('ConferenceBBB_user', 'string', 255, '');
    }
}
