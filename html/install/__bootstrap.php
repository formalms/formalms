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
include _lib_ . '/lib.bootstrap.php';

echo "qui";