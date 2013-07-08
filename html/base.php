<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
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
 * If you need to change the main folders's name of the Docebo dir you only need to change the followings constant, for example from:
 *    define( '_folder_lms_', 	'doceboLms' );
 * to:
 *    define( '_folder_lms_', 	'lms' );
 * in order to change the lms main dir to "lms"
 */
define( '_folder_addons_', 	'addons' );
define( '_folder_adm_', 	'doceboCore' );
define( '_folder_lms_', 	'doceboLms' );
define( '_folder_cms_', 	'doceboCms' );
define( '_folder_scs_', 	'doceboScs' );
define( '_folder_i18n_', 	'i18n' );
define( '_folder_lib_', 	'lib' );
define( '_folder_files_', 	'files' );
define( '_folder_plugins_', 	'plugins' );

/** absolute address to the addons folder */
define( '_addons_',		_base_.'/'._folder_addons_ );
define( '_adm_', 		_base_.'/'._folder_adm_ );
define( '_lms_', 		_base_.'/'._folder_lms_ );
define( '_cms_', 		_base_.'/'._folder_cms_ );
define( '_scs_', 		_base_.'/'._folder_scs_ );
define( '_i18n_', 		_base_.'/'._folder_i18n_ );
define( '_lib_', 		_base_.'/'._folder_lib_ );
define( '_files_', 		_base_.'/'._folder_files_ );
define( '_plugins_', 	_base_.'/'._folder_plugins_ );

/** other nice setting */
define( '_after_login_', 'lms/elearning/show' );
