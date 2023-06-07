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

if (!defined('IN_FORMA')) {
    exit('You can\'t access!');
}

// if this file is not needed for a specific version,
// just don't create it.

/**
 * This function must always return a boolean value
 * Error message can be appended to $GLOBALS['debug'].
 */
function postUpgrade4050()
{
    //echo "post-upgrade";

    return true;
}
