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

define('CORE', true);
define('IN_FORMA', true);
define('_deeppath_', '../');
require dirname(__DIR__,1) . '/base.php';

// start buffer
ob_start();

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(BOOT_HOOKS);
// some specific lib to load
require_once _base_ . '/lib/lib.platform.php';
require_once _adm_ . '/lib/lib.permission.php';
require_once _adm_ . '/lib/lib.istance.php';
require_once _adm_ . '/class.module/class.definition.php';

// -----------------------------------------------------------------------------

$GLOBALS['operation_result'] = '';

$module_cfg = &createModule('iotask');
$GLOBALS['operation_result'] = $module_cfg->doTasks();

// -----------------------------------------------------------------------------

// finalize
Boot::finalize();


// remove all the echo
ob_clean();

// Print out the page
echo $GLOBALS['operation_result'];

// flush buffer
ob_end_flush();
