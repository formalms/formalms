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

class Module_Poll extends LmsModule
{
    public function hideLateralMenu()
    {
        if ($this->session->has('test_assessment')) {
            return true;
        }
        if ($this->session->has('direct_play')) {
            return true;
        }
        return false;
    }

    public function loadBody()
    {
        //EFFECTS: include module language and module main file

        switch ($GLOBALS['op']) {
            case 'play':
                $id_poll = importVar('id_poll', true, 0);
                $id_param = importVar('id_param', true, 0);
                $back_url = importVar('back_url');

                $object_poll = createLO('poll', $id_poll);
                $object_poll->play($id_poll, $id_param, Util::unserialize(urldecode($back_url)));
            ; break;
            default:
                parent::loadBody();
        }
    }
}
