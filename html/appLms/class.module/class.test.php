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

class Module_Test extends LmsModule
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
                $idTest = importVar('id_test', true, 0);
                $id_param = importVar('id_param', true, 0);
                $back_url = importVar('back_url');
                $test_type = importVar('test_type', false, 'test');

                $object_poll = createLO($test_type, $idTest);
                $object_poll->play($idTest, $id_param, Util::unserialize(urldecode($back_url)));
            ; break;
            default:
                parent::loadBody();
        }
    }
}
