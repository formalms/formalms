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

const LMS = true;
const IN_FORMA = true;
const _deeppath_ = '../';
require __DIR__ . '/../base.php';
require_once _lms_ . '/lib/LMSTemplateController.php';

LMSTemplateController::init();

// initialize
require _base_ . '/lib/lib.bootstrap.php';
Boot::init(CHECK_SYSTEM_STATUS);

// connect to the database
$db = DbConn::getInstance();

// some specific lib to load
require_once _lms_ . '/lib/lib.istance.php';
require_once _lms_ . '/lib/lib.permission.php';
require_once _lms_ . '/lib/lib.track_user.php';
require_once _lms_ . '/class.module/class.definition.php';

// -----------------------------------------------------------------------------

$module_cfg = false;
$GLOBALS['modname'] = FormaLms\lib\Get::req('modname', DOTY_ALPHANUM, '');
$GLOBALS['op'] = FormaLms\lib\Get::req('op', DOTY_ALPHANUM, '');
$GLOBALS['req'] = FormaLms\lib\Get::req('r', DOTY_MIXED, '');

YuiLib::activateConnectLoadingBox();

$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
// instanciate the page-writer that we want (maybe we can rewrite it in a
// different way with the introduction of the layout manager)
if (isset($_GET['no_redirect']) || isset($_POST['no_redirect'])) {
    onecolPageWriter::createInstance();
} elseif ((!$session->has('idCourse') || empty($session->get('idCourse'))) && !Docebo::user()->isAnonymous()) {
    onecolPageWriter::createInstance();
} elseif ($module_cfg !== false && $module_cfg->hideLateralMenu()) {
    onecolPageWriter::createInstance();
} else {
    require_once _lms_ . '/lib/lib.lmspagewriter.php';
    LmsPageWriter::createInstance();
}

require_once Forma::inc(_lms_ . '/lib/lib.preoperation.php');

require_once _lms_ . '/lib/lib.module.php';

// create the class for management the called module
if (!empty($GLOBALS['modname'])) {
    $module_cfg = createModule($GLOBALS['modname']);
    if (method_exists($module_cfg, 'beforeLoad')) {
        $module_cfg->beforeLoad();
    }
}

// New MVC structure
if (!empty($GLOBALS['req'])) {
    $requesthandler = new RequestHandler($GLOBALS['req'], 'lms');
    $requesthandler->run();
} else {
    // load module body
    if (!empty($GLOBALS['modname'])) {
        if (method_exists($module_cfg, 'loadBody')) {
            $module_cfg->loadBody();
        }
    }
}

LMSTemplateController::getInstance()->show();

// -----------------------------------------------------------------------------

//// finalize TEST_COMPATIBILITA_PHP54
Boot::finalize();

LMSTemplateController::flush();
