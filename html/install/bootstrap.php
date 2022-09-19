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

@error_reporting(E_COMPILE_ERROR | E_ERROR | E_CORE_ERROR);

//session_name('docebo_session');
//session_start();

const IN_FORMA = true;
const INSTALL_ENV = 'install';
const _deeppath_ = '../';
require __DIR__ . '/../base.php';
require_once _base_ . '/vendor/autoload.php';
if (file_exists(_base_ . '/config.php')) {
    require _base_ . '/config.php';
} // FOR upgrade import_lang

$request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
if (!$request->hasSession()) {
    $config = $cfg && isset($cfg['session']) ? $cfg['session'] : [];
    FormaLms\lib\Session\SessionManager::getInstance()->initSession($config);
    $session = FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $request->setSession($session);
}

const _installer_ = _base_ . '/install';

include _lib_ . '/lib.forma.php';
include _lib_ . '/lib.docebo.php';
include _lib_ . '/installer/lib.php';
include _lib_ . '/installer/lib.lang.php';
include _lib_ . '/installer/lib.step.php';
include _lib_ . '/installer/lib.pagewriter.php';
include _lib_ . '/installer/lib.template.php';

PageWriter::init();

include _base_ . '/lib/lib.utils.php';
include _base_ . '/lib/lib.yuilib.php';
include _base_ . '/lib/lib.form.php';

$GLOBALS['page']->setZone('page_head');
YuiLib::load();
$GLOBALS['page']->setZone('main');

$GLOBALS['page']->add(Util::get_css(getTemplatePath() . 'style/base.css', true), 'page_head');
$GLOBALS['page']->add(Util::get_css(getTemplatePath() . 'style/form.css', true), 'page_head');
$GLOBALS['page']->add(Util::get_js('./lib/base.js', true), 'page_head');
$GLOBALS['page']->add(Util::get_js('../addons/yui/event-mouseenter/event-mouseenter-min.js', true), 'page_head');
