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

function getTemplate()
{
    return 'standard';
}

function getTemplatePath()
{
    switch (INSTALL_ENV) {
        case 'upgrade':
            return '../install/templates/' . getTemplate() . '/';
         break;

        default:
        case 'install':
            return './templates/' . getTemplate() . '/';
         break;
    }
}
