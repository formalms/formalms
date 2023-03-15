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

class Module_Faq extends LmsModule
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

    public function loadHeader()
    {
        //EFFECTS: write in standard output extra header information

        switch ($GLOBALS['op']) {
            case 'insfaqcat':
            case 'newfaq':
            case 'insfaq':
            case 'modfaqcat':
            case 'upfaqcat':
            case 'modfaq':
            case 'upfaq':
                loadHeaderHTMLEditor();
                break;
            default:
                break;
        }

        return;
    }

    public function useExtraMenu()
    {
        return false;
    }

    public function loadExtraMenu()
    {
    }

    public function loadBody()
    {
        //EFFECTS: include module language and module main file

        switch ($GLOBALS['op']) {
            case 'play':
                $idCategory = importVar('idCategory', true, 0);
                $id_param = importVar('id_param', true, 0);
                $back_url = importVar('back_url');

                $object_faq = createLO('faq', $idCategory);
                $object_faq->play($idCategory, $id_param, urldecode($back_url));
                break;
            default:
                parent::loadBody();
        }
    }
}
