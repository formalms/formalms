<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

define("CORE", true);
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');

define('_file_version_', '2.0');

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

// connect to the database
$db =& DbConn::getInstance();

// some specific lib to load
require_once(_base_.'/lib/lib.platform.php');
require_once(_base_.'/lib/lib.menu.php');
require_once(_adm_.'/lib/lib.permission.php');
require_once(_adm_.'/lib/lib.istance.php');
require_once(_adm_.'/class.module/class.definition.php');

// -----------------------------------------------------------------------------

$module_cfg = false;
$GLOBALS['modname'] = Get::req('modname', DOTY_ALPHANUM, '');
$GLOBALS['op']		= Get::req('op', DOTY_ALPHANUM, '');
// create instance of StdPageWriter
StdPageWriter::createInstance();

if (file_exists(_base_.'/customscripts'.'/'._folder_adm_.'/lib/lib.preoperation.php') && Get::cfg('enable_customscripts', false) == true ){
        require_once(_base_.'/customscripts'.'/'._folder_adm_.'/lib/lib.preoperation.php');
} else {
        require_once(_adm_.'/lib/lib.preoperation.php');
}

if(empty($GLOBALS['modname']) && empty($GLOBALS['r'])) {
	$GLOBALS['req'] = (checkPerm('view', true, 'dashboard', 'framework') ? 'adm/dashboard/show' : '');
	$_SESSION['current_action_platform'] = 'framework';
}

if($GLOBALS['modname'] != '') {
	$module_cfg =& createModule($GLOBALS['modname']);
}

// yui base lib loading
YuiLib::load();
YuiLib::activateConnectLoadingBox();

//general menu
require(_adm_.'/menu/menu_over.php');

$GLOBALS['page']->setWorkingZone('content');

// New MVC structure
if(isset($_GET['r'])) { $GLOBALS['req'] = preg_replace('/[^a-zA-Z0-9\-\_\/]+/', '', $_GET['r']); }
if (!empty($GLOBALS['req'])){

    $requesthandler = new RequestHandler($GLOBALS['req'],'adm');
    $requesthandler->run();
} else {

    // load module body
    if(!empty($GLOBALS['modname'])) {
        $module_cfg->loadBody();
    }
}
// -----------------------------------------------------------------------------

#// finalize TEST_COMPATIBILITA_PHP54
//Boot::finalize();

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

// layout
Layout::render('adm');

#// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

// flush buffer
ob_end_flush();

?>
