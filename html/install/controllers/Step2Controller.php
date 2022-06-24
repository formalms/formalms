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

require_once dirname(__FILE__) . '/StepController.php';

class Step2Controller extends StepController
{
    public $step = 2;

    public function validate()
    {
        return true;
    }
}

//TODO INSTALL_vs_UPGRADE: please share what you can
function checkRequirements()
{
    $res = [];

    //TODO PHP7x: set const for Minimum PHP required version: 7.4
    //TODO PHP7x: set const for Maximum PHP suggested version: 7.4.x
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        $res['php'] = 'err';
    } elseif (version_compare(PHP_VERSION, '8.0', '>=')) {
        $res['php'] = 'warn';
    } else {
        $res['php'] = 'ok';
    }

    $driver = [
        'mysqli' => extension_loaded('mysqli'),
    ];
    if (array_filter($driver)) {
        // mysql client version, in php the version number is a string regcut it
        preg_match('/([0-9]+\.[\.0-9]+)/', sql_get_client_info(), $version);

        if (empty($version[1])) {
            $res['mysql_client'] = 'ok';
        } else {
            $res['mysql_client'] = (version_compare($version[1], PHP_VERSION) >= 0 ? 'ok' : 'err');
        }
    } else {
        $res['mysql_client'] = 'err';
    }
    if (array_filter($driver)) {
        // mysql version, in easyphp the version number is ina string regcut it
        preg_match('/([\.0-9][\.0-9]+\.[\.0-9]+)/', sql_get_server_version(), $mysqlVersion);

        if (empty($mysqlVersion[1])) {
            $res['mysql'] = 'ok';
        } else {
            $checkMysql = version_compare($mysqlVersion[1], '5.6') >= 0 && version_compare($mysqlVersion[1], '8.1') < 0;
            $checkMariaDB = version_compare($mysqlVersion[1], '10.0') >= 0 && version_compare($mysqlVersion[1], '11.0') < 0;

            if ($checkMysql || $checkMariaDB) {
                $res['mysql'] = 'ok';
            } else {
                $res['mysql'] = 'err';
            }
        }
    } else {
        $res['mysql'] = 'err';
    }
    $res['xml'] = (extension_loaded('domxml') ? 'ok' : 'err');
    $res['mbstring'] = (extension_loaded('mbstring') ? 'ok' : 'err');
    $res['ldap'] = (extension_loaded('ldap') ? 'ok' : 'err');
    $res['openssl'] = (extension_loaded('openssl') ? 'ok' : 'err');
    $res['allow_url_fopen'] = ($php_conf['allow_url_fopen']['local_value'] ? 'ok' : 'err');
    $res['allow_url_include'] = ($php_conf['allow_url_include']['local_value'] ? 'err' : 'ok');
    $res['mime_ct'] = (function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');

    return $res;
}

function checkFolderPerm()
{
    $res = '';

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    $platform_folders = $session->get('platform_arr');
    $file_to_check = ['config.php'];
    $dir_to_check = [];
    $empty_dir_to_check = [];

    // common dir to check
    $dir_to_check = [
        'files/tmp',
        'files/common/comment',
        'files/common/iofiles',
        'files/common/users',
    ];

    foreach ($platform_folders as $platform_code => $dir_name) {
        $specific_file_to_check = [];
        $specific_dir_to_check = [];

        if (!is_dir(_base_ . '/' . $dir_name . '/')) {
            $install[$platform_code] = false;
        } else {
            $install[$platform_code] = true;

            $empty_specific_dir_to_check = null;

            switch ($platform_code) {
                case 'lms':
                        $specific_dir_to_check = [
                            'files/appLms/certificate',
                            'files/appLms/forum',
                            'files/appLms/htmlpages',
                            'files/appLms/item',
                            'files/appLms/label',
                            'files/appLms/message',
                            'files/appLms/project',
                            'files/appLms/repo_light',
                            'files/appLms/sponsor',
                            'files/appLms/test',
                        ];
                        $empty_specific_dir_to_check = ['files/appLms/course', 'files/appLms/scorm'];

                    break;

                case 'framework':
                        $specific_dir_to_check = [
                            'files/appCore/field',
                            'files/appCore/photo',
                            'files/appCore/newsletter',
                            'files/common/users',
                        ];

                    break;
            }

            $dir_to_check = array_merge($dir_to_check, $specific_dir_to_check);
            $file_to_check = array_merge($file_to_check, $specific_file_to_check);

            if ((is_array($specific_dir_to_check)) && (count($specific_dir_to_check) > 0) && (is_array($empty_specific_dir_to_check))) {
                $empty_dir_to_check = array_merge($empty_dir_to_check, $empty_specific_dir_to_check);
            }
        }
    }

    // Write permission
    $checked_dir = [];
    foreach ($dir_to_check as $dir_name) {
        if (!is_dir(_base_ . '/' . $dir_name . '/')) {
            $checked_dir[] = $dir_name;
        } elseif (!is_writable(_base_ . '/' . $dir_name . '/')) {
            $checked_dir[] = $dir_name;
        }
    }
    if (!empty($checked_dir)) {
        $res .= '<h3>' . Lang::t('_CHECKED_DIRECTORIES') . '</h3>'
            . '<ul class="info"><li class="err">' . implode('</li><li class="err">', $checked_dir) . '</li></ul>';
    }

    $checked_file = [];
    foreach ($file_to_check as $file_name) {
        if (!is_writable(_base_ . '/' . $file_name)) {
            $checked_file[] = $file_name;
        }
    }
    if (!empty($checked_file)) {
        $res .= '<h3>' . Lang::t('_CHECKED_FILES') . '</h3>'
            . '<ul class="info"><li class="err">' . implode('</li><li class="err">', $checked_file) . '</li></ul>';
    }

    return $res;
}
