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

// if this file is not needed for a specific version,
// just don't create it.

require_once _lib_ . '/lib.bootstrap.php';

function postUpgrade30301()
{
    Boot::init(BOOT_CONFIG);
    remapSmtpParams();

    return true;
}

function remapSmtpParams()
{
    require_once _lib_ . '/lib.aclmanager.php';
 


}
