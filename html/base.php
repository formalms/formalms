<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/** absolute base path to the main directory of the docebo installation */
define('_base_', dirname(__FILE__));

/**
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
define( '_folder_addons_', 	'addons' );
define( '_folder_adm_', 	'appCore' );
define( '_folder_lms_', 	'appLms' );
define( '_folder_scs_', 	'appScs' );
define( '_folder_i18n_', 	'i18n' );
define( '_folder_lib_', 	'lib' );
define( '_folder_files_', 	'files' );
define( '_folder_files_lms_', 	'appLms' );
define( '_folder_files_app_', 	'appCore' );
define( '_folder_files_com_', 	'common' );
define( '_folder_plugins_', 	'plugins' );

/** absolute address to the addons folder */
define( '_addons_',		_base_.'/'._folder_addons_ );
define( '_adm_', 		_base_.'/'._folder_adm_ );
define( '_lms_', 		_base_.'/'._folder_lms_ );
define( '_scs_', 		_base_.'/'._folder_scs_ );
define( '_i18n_', 		_base_.'/'._folder_i18n_ );
define( '_lib_', 		_base_.'/'._folder_lib_ );
define( '_files_',		_base_.'/'._folder_files_ );
define( '_files_lms_',	_base_.'/'._folder_files_lms_ );
define( '_files_app_',	_base_.'/'._folder_files_app_ );
define( '_files_com_',	_base_.'/'._folder_files_com_ );


define( '_plugins_', 	_base_.'/'._folder_plugins_ );

/** other nice setting */
define( '_homepage_base_',      'adm/homepage' );
define( '_homecatalog_base_',   'lms/homecatalogue' );

define( '_homepage_',           _homepage_base_ . '/show' );
define( '_register_',           _homepage_base_ . '/register' );
define( '_login_',              _homepage_base_ . '/login' );
define( '_signup_',             _homepage_base_ . '/signup' );
define( '_logout_',             _homepage_base_ . '/logout' );
define( '_stopconcurrency_',    _homepage_base_ . '/stopconcurrency' );
define( '_lostpwd_',            _homepage_base_ . '/lostpwd' );
define( '_newpwd_',             _homepage_base_ . '/newpwd' );
define( '_homewebpage_',        _homepage_base_ . '/webpage' );
define( '_sso_',                _homepage_base_ . '/sso' );
define( '_homecatalog_',        _homecatalog_base_ . '/show' );