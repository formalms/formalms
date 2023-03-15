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

function categoryDispatch($op, &$treeView)
{
    switch ($op) {
        case 'newfolder':
             $GLOBALS['page']->add($treeView->loadNewFolder(), 'content');
         break;
        case 'renamefolder':
            $GLOBALS['page']->add($treeView->loadRenameFolder(), 'content');
         break;
        case 'movefolder':
            $GLOBALS['page']->add($treeView->loadMoveFolder(), 'content');
         break;
        case 'deletefolder':
            $GLOBALS['page']->add($treeView->loadDeleteFolder(), 'content');
         break;
    }
}
