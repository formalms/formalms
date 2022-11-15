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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/* absolute base path to the main directory of the docebo installation */
define('_base_', dirname(__FILE__));

/*
 * Subsystem folder
 * -------------------------------------------------------------------------
 * addons path
 * admin area (core) path
 * lms area path
 * cms area path
 * scs area path
 * i18n path
 * libraries path
 * file uploaded path (this directory and his sub-directories must be writable and readable by apache)
 * -------------------------------------------------------------------------
 * If you need to change the main folders's name of the Forma dir you only need to change the followings constant, for example from:
 *    define( '_folder_lms_', 	'appLms' );
 * to:
 *    define( '_folder_lms_', 	'lms' );
 * in order to change the lms main dir to "lms"
 */
const _folder_addons_ = 'addons';
const _folder_adm_ = 'appCore';
const _folder_lms_ = 'appLms';
const _folder_scs_ = 'appScs';
const _folder_i18n_ = 'i18n';
const _folder_lib_ = 'lib';
const _folder_langs_ = 'xml_language';
const _folder_files_ = 'files';
const _folder_files_lms_ = 'appLms';
const _folder_files_app_ = 'appCore';
const _folder_files_com_ = 'common';
const _folder_plugins_ = 'plugins';
const _folder_templates_ = 'templates';

/* absolute address to the addons folder */
const _addons_ = _base_ . '/' . _folder_addons_;
const _adm_ = _base_ . '/' . _folder_adm_;
const _lms_ = _base_ . '/' . _folder_lms_;
const _scs_ = _base_ . '/' . _folder_scs_;
const _i18n_ = _base_ . '/' . _folder_i18n_;
const _lib_ = _base_ . '/' . _folder_lib_;
const _langs_ = _base_ . '/' . _folder_langs_;
const _files_ = _base_ . '/' . _folder_files_;
const _files_lms_ = _files_ . '/' . _folder_files_lms_;
const _files_app_ = _files_ . '/' . _folder_files_app_;
const _files_com_ = _files_ . '/' . _folder_files_com_;
const _templates_ = _base_ . '/' . _folder_templates_;

const _plugins_ = _base_ . '/' . _folder_plugins_;

/* other nice setting */
const _homepage_base_ = 'adm/homepage';
const _install_base_ = 'adm/install';
const _homecatalog_base_ = 'lms/homecatalogue';

const _homepage_ = _homepage_base_ . '/show';
const _register_ = _homepage_base_ . '/register';
const _login_ = _homepage_base_ . '/login';
const _signup_ = _homepage_base_ . '/signup';
const _logout_ = _homepage_base_ . '/logout';
const _stopconcurrency_ = _homepage_base_ . '/stopconcurrency';
const _lostpwd_ = _homepage_base_ . '/lostpwd';
const _newpwd_ = _homepage_base_ . '/newpwd';
const _homewebpage_ = _homepage_base_ . '/webpage';
const _sso_ = _homepage_base_ . '/sso';
const _homecatalog_ = _homecatalog_base_ . '/show';
const _install_ = _install_base_ . '/show';
