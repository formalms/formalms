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

function loginDispatch($op)
{
    switch ($op) {
        case 'login':
            Util::jump_to(FormaLms\lib\Get::rel_path('base') . '/index.php?modname=login&amp;op=login');
         break;
    }
}
