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

@error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);

//session_name('docebo_session');
//session_start();

const IN_FORMA = true;
const IN_DOCEBO = true;            // need for upgrade from doceboce
const INSTALL_ENV = 'upgrade';

const _deeppath_ = '../';
require __DIR__ . '/../base.php';
require_once _base_ . '/vendor/autoload.php';
require _base_ . '/config.php';
const _installer_ = _base_ . '/install';
const _upgrader_ = _base_ . '/upgrade';

$request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
if (!$request->hasSession()) {
    $config = $cfg && isset($cfg['session']) ? $cfg['session'] : [];
    FormaLms\lib\Session\SessionManager::getInstance()->initSession($config);
    $session = FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $request->setSession($session);
}

require_once _base_ . '/lib/loggers/lib.logger.php';

include _lib_ . '/installer/lib.php';
include _lib_ . '/installer/lib.lang.php';
include _lib_ . '/installer/lib.step.php';
include _lib_ . '/installer/lib.pagewriter.php';
include _lib_ . '/installer/lib.template.php';

PageWriter::init();

include _base_ . '/lib/lib.bootstrap.php';
include _base_ . '/lib/lib.utils.php';
include _base_ . '/lib/lib.yuilib.php';
include _base_ . '/lib/lib.form.php';
include _upgrader_ . '/config.php';

// todo: backward compatibility
$GLOBALS['where_framework_relative'] = (!defined('CORE') ? _deeppath_ : '') . _folder_adm_;
$GLOBALS['where_lms_relative'] = (!defined('LMS') ? _deeppath_ : '') . _folder_lms_;
$GLOBALS['where_scs_relative'] = _deeppath_ . _folder_scs_;
$GLOBALS['where_files_relative'] = _deeppath_ . _folder_files_;
$GLOBALS['where_templates_relative'] = _deeppath_ . _folder_templates_;

$GLOBALS['where_files_lms_relative'] = _deeppath_ . _folder_files_lms_;
$GLOBALS['where_files_app_relative'] = _deeppath_ . _folder_files_app_;
$GLOBALS['where_files_com_relative'] = _deeppath_ . _folder_files_com_;

$GLOBALS['where_framework'] = _adm_;
$GLOBALS['where_lms'] = _lms_;
$GLOBALS['where_scs'] = _scs_;
$GLOBALS['where_files'] = _files_;

$GLOBALS['where_files_lms'] = _files_lms_;
$GLOBALS['where_files_app'] = _files_app_;
$GLOBALS['where_files_com'] = _files_com_;

$GLOBALS['page']->setZone('page_head');
YuiLib::load();
$GLOBALS['page']->setZone('main');
