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

define("LMS", true);
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__).'/../base.php');
require_once _lms_ . '/lib/LMSTemplateController.php';

LMSTemplateController::init();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

// connect to the database
$db =& DbConn::getInstance();

//Remvoe session param layoutToRender
unset($_SESSION['layoutToRender']);


// some specific lib to load
require_once(_lms_.'/lib/lib.istance.php');
require_once(_lms_.'/lib/lib.permission.php');
require_once(_lms_.'/lib/lib.track_user.php');
require_once(_lms_.'/class.module/class.definition.php');

// -----------------------------------------------------------------------------

$module_cfg = false;
$GLOBALS['modname'] = Get::req('modname', DOTY_ALPHANUM, '');
$GLOBALS['op']		= Get::req('op', DOTY_ALPHANUM, '');
$GLOBALS['req']		= Get::req('r', DOTY_MIXED, '');

YuiLib::activateConnectLoadingBox();

// instanciate the page-writer that we want (maybe we can rewrite it in a
// different way with the introduction of the layout manager)
if(isset($_GET['no_redirect']) || isset($_POST['no_redirect'])) {

	onecolPageWriter::createInstance();
} elseif(!isset($_SESSION['idCourse']) && !Docebo::user()->isAnonymous()) {

	onecolPageWriter::createInstance();
} elseif($module_cfg !== false && $module_cfg->hideLateralMenu()) {

	onecolPageWriter::createInstance();
} else {

	require_once(_lms_.'/lib/lib.lmspagewriter.php');
	LmsPageWriter::createInstance();
}

require_once(Forma::inc(_lms_.'/lib/lib.preoperation.php'));

require_once(_lms_.'/lib/lib.module.php');

// create the class for management the called module
if(!empty($GLOBALS['modname'])) {
	$module_cfg =& createModule($GLOBALS['modname']);
	if(method_exists($module_cfg, 'beforeLoad')) $module_cfg->beforeLoad();
}

// New MVC structure
if (!empty($GLOBALS['req'])){

    $requesthandler = new RequestHandler($GLOBALS['req'],'lms');
    $requesthandler->run();
} else {

    // load module body
    if(!empty($GLOBALS['modname'])) {
        $module_cfg->loadBody();
    }
}

LMSTemplateController::getInstance()->show();

// -----------------------------------------------------------------------------

#// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

LMSTemplateController::flush();

?>