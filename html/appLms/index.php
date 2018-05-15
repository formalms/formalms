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

// start buffer
ob_start();

// initialize
require(_base_.'/lib/lib.bootstrap.php');
Boot::init(BOOT_PAGE_WR);

// connect to the database
$db =& DbConn::getInstance();


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

if (file_exists(_base_.'/customscripts'.'/'._folder_lms_.'/lib/lib.preoperation.php') && Get::cfg('enable_customscripts', false) == true ){
        require_once(_base_ . '/customscripts' . '/' . _folder_lms_ . '/lib/lib.preoperation.php');
} else {
        require_once(_lms_.'/lib/lib.preoperation.php');
}
require_once(_lms_.'/lib/lib.module.php');

// create the class for management the called module
if(!empty($GLOBALS['modname'])) {
	$module_cfg =& createModule($GLOBALS['modname']);
	if(method_exists($module_cfg, 'beforeLoad')) $module_cfg->beforeLoad();
}

// header
if($module_cfg !== false && $module_cfg->hideLateralMenu()) {

	require(_lms_.'/menu/menu_over.php');
} else {

	if(!Docebo::user()->isAnonymous()) {

		require(_lms_.'/menu/menu_over.php');
		/*if(isset($_SESSION['idCourse'])) {


			require(_lms_.'/menu/menu_lat.php');
		}*/
	} else {

		require(_lms_.'/menu/menu_login.php');
	}
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

// -----------------------------------------------------------------------------

#// finalize TEST_COMPATIBILITA_PHP54
//Boot::finalize();

// remove all the echo and put them in the debug zone
$GLOBALS['page']->add(ob_get_contents(), 'debug');
ob_clean();

// layout
Layout::render( ( isset($_SESSION['idCourse']) ? 'lms' : 'lms_user' ) );

//\appCore\Events\DispatcherManager::addListener('prova.evento.appLms', array(new \appLms\Events\DumpAndDieLmsListener(), 'printOnlyADot'));

//\appCore\Events\DispatcherManager::dispatch('prova.evento.appLms');

#// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

// flush buffer
ob_end_flush();

?>