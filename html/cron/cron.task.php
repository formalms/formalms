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

define('IN_FORMA', true);
define('_deeppath_', '../');
require dirname(__DIR__,1) . '/base.php';

// start buffer
ob_start();

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_HOOKS);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if (!function_exists('aout')) {
    function aout($string)
    {
        $GLOBALS['operation_result'] .= $string;
    }
}

// do something

// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();
