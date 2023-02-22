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
const _system_base_ = 'adm/system';
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
const _install_ = _system_base_ . '/install';
const _upgradeclass_ = 'upgrade_class';


const BOOT_COMPOSER = 0;
const BOOT_PHP = 1;
const BOOT_CONFIG = 2;
const BOOT_UTILITY = 3;
const BOOT_REQUEST = 4;
const BOOT_PLATFORM = 5;
const BOOT_DOMAIN_AND_TEMPLATE = 6;
const BOOT_SETTING = 7;
const BOOT_PLUGINS = 8;
const BOOT_USER = 9;
const BOOT_SESSION_CHECK = 10;
const BOOT_INPUT = 11;
const BOOT_INPUT_ALT = 12;
const BOOT_LANGUAGE = 13;
const BOOT_HOOKS = 14;
const BOOT_DATETIME = 15;
const BOOT_TEMPLATE = 16;
const BOOT_PAGE_WR = 17;
const CHECK_SYSTEM_STATUS = 18;
