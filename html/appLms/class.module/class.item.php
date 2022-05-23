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

class Module_Item extends LmsModule
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
        global $op;

        switch ($op) {
            case 'additem':
            case 'insitem':
            case 'moditem':
            case 'upitem':
                loadHeaderHTMLEditor();
            ; break;
            case 'category':
                echo '<link href="' . getPathTemplate() . 'style/base-old-treeview.css" rel="stylesheet" type="text/css" />' . "\n";
            ; break;
        }

        return;
    }
}
