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

class Module_Link extends LmsModule
{
    public function loadBody()
    {
        //EFFECTS: include module language and module main file

        switch ($GLOBALS['op']) {
            case 'play':
                $idCategory = importVar('idCategory', true, 0);
                $id_param = importVar('id_param', true, 0);
                $back_url = importVar('back_url');

                $object_link = createLO('link', $idCategory);
                $object_link->play($idCategory, $id_param, urldecode($back_url));
             break;
            default:
                parent::loadBody();
        }
    }
}
