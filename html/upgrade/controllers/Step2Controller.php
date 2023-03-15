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

require_once dirname(__FILE__) . '/StepController.php';

// docebo ce versions series 3.x.x ( 03xxx formely 3xxx )
// docebo ce versions series 4.x.x ( 04xxx formely 4xxx )
// forma     versions series 1.x   ( 1xxxx )

class Step2Controller extends StepController
{
    public $step = 2;
    public $session = null;

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function validate()
    {
        $this->session->set('start_version', FormaLms\lib\Get::req('start_version', DOTY_ALPHANUM, '3603'));
        $this->session->save();

        return true;
    }

    public function getNextStep($current_step)
    {
        $version = FormaLms\lib\Get::req('start_version', DOTY_ALPHANUM, '3603');
        if (version_compare($version, '3600', '>=') &&
            version_compare($version, '4000', '<')) {
            //docebo ce v 3.x.x => go to step 3 (config upgrade )
            $next_step = $current_step + 1;
        } elseif (version_compare($version, '4000', '>=') &&
            version_compare($version, '5000', '<')) {
            //docebo ce v 4.x.x => go to step 3 (config upgrade )
            $next_step = $current_step + 1;
        } else {
            // forma v1.x => skip step 3 and 4 (config upgrade, db upgrade from 3 to 4)

            $next_step = $current_step + 1;  // upgrade config via plugin
        }

        return $next_step;
    }

    public function getCurrentVersion()
    {
        list($current_version) = sql_fetch_row(sql_query("SELECT param_value FROM core_setting WHERE param_name = 'core_version' "));

        $current_version = getVersionIntNumber($current_version);

        return $current_version;
    }

    public function versionList()
    {
        $current_version = $this->getCurrentVersion();

        $txt = '<select id="start_version" name="start_version">';
        foreach ($GLOBALS['cfg']['versions'] as $k => $v) {
            $txt .= '<option value="' . $k . '"' . ($k === $current_version ? ' selected="selected"' : '') . '>' . $v . '</option>';
        }
        $txt .= '</select>';

        return $txt;
    }

    //TODO INSTALL_vs_UPGRADE: please share what you can
    public function checkRequirements()
    {
        $res = [];

        //TODO PHP7x: set const for Minimum PHP required version: 7.4
        //TODO PHP7x: set const for Maximum PHP suggested version: 7.4.x
        if (PHP_VERSION_ID < 70400) {
            $res['php'] = 'err';
        } elseif (PHP_VERSION_ID >= 80000) {
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
            // mysql client version, in php the version number is a string regcut it
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
        $res['ldap'] = (extension_loaded('ldap') ? 'ok' : 'err');
        $res['mbstring'] = (extension_loaded('mbstring') ? 'ok' : 'err');
        $res['strict_mode'] = ($this->checkStrictMode() ? 'ok' : 'err');
        $res['openssl'] = (extension_loaded('openssl') ? 'ok' : 'err');
        $res['allow_url_fopen'] = ($php_conf['allow_url_fopen']['local_value'] ? 'ok' : 'err');
        $res['allow_url_include'] = ($php_conf['allow_url_include']['local_value'] ? 'err' : 'ok');
        $res['mime_ct'] = (function_exists('mime_content_type') || (class_exists('file') && method_exists('finfo', 'file')) ? 'ok' : 'err');

        $current_version = $this->getCurrentVersion();
        $end_version = (int) $GLOBALS['cfg']['endversion'];

        if ($current_version == $end_version) {
            $res['upg_not_needed'] = true;
            $res['upg_not_needed_text'] = Lang::t('_UPGRADE_NOT_NEEDED');
        }
        if ($current_version > $end_version) {
            $res['upg_not_needed'] = true;
            $res['upg_not_needed_text'] = Lang::t('_UPGRADE_NOT_NEEDED_FILE_IS_LATER');
        }

        if (version_compare($current_version, '3600', '>=') &&
            version_compare($current_version, '4000', '<')) {
            // docebo ce versions series 3.x.x.x
            // Upgrader: we check if we are starting from a valid (old) config.php file:
            require_once _base_ . '/config.php';
            $res['config_v3'] = (!empty($GLOBALS['dbhost']) ? 'ok' : 'err');
            $res['config_v4'] = 'err';
            $res['config_v1'] = 'err';
        } else {
            require_once _base_ . '/config.php';
            $res['config_v4'] = (!empty($GLOBALS['cfg']['db_host']) ? 'ok' : 'err');
            $res['config_v3'] = 'err';
            $res['config_v1'] = 'ok';
        }

        return $res;
    }

    public function checkStrictMode()
    {
        return true;
        $qtxt = 'SELECT @@GLOBAL.sql_mode AS res';
        $q = sql_query($qtxt);
        list($r1) = sql_fetch_row($q);
        $qtxt = 'SELECT @@SESSION.sql_mode AS res';
        $q = sql_query($qtxt);
        list($r2) = sql_fetch_row($q);
        $res = ((strpos($r1 . $r2, 'STRICT_') === false) ? true : false);

        return $res;
    }

    public function checkFolderPerm()
    {
        $res = '';

        $platform_folders = $this->session->get('platform_arr');
        $file_to_check = ['config.php'];
        $dir_to_check = [];
        $empty_dir_to_check = [];

        // common dir to check
        $dir_to_check = [
            'files/cache',
            'files/tmp',
            'files/common/comment',
            'files/common/iofiles',
            'files/common/users',
        ];

        $current_version = $this->getCurrentVersion();
        if (version_compare($current_version, '3600', '>=') &&
            version_compare($current_version, '5000', '<')) {
            $dirprefix = 'docebo';
        } else {
            $dirprefix = 'app';
        }

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
                                'files/' . $dirprefix . 'Lms/certificate',
                                'files/' . $dirprefix . 'Lms/course',
                                'files/' . $dirprefix . 'Lms/forum',
                                'files/' . $dirprefix . 'Lms/item',
                                'files/' . $dirprefix . 'Lms/htmlpages',
                                'files/' . $dirprefix . 'Lms/label',
                                'files/' . $dirprefix . 'Lms/message',
                                'files/' . $dirprefix . 'Lms/project',
                                'files/' . $dirprefix . 'Lms/repo_light',
                                'files/' . $dirprefix . 'Lms/scorm',
                                'files/' . $dirprefix . 'Lms/sponsor',
                                'files/' . $dirprefix . 'Lms/test',
                            ];

                            if ($dirprefix == 'app') {
                                // new folders in formalms
                                $specific_dir_to_check[] = 'files/' . $dirprefix . 'Lms/htmlpages';
                            }
                            $empty_specific_dir_to_check = [];

                        break;

                    case 'framework':
                            $specific_dir_to_check = [
                                'files/' . $dirprefix . 'Core/field',
                                'files/' . $dirprefix . 'Core/photo',
                                'files/' . $dirprefix . 'Core/newsletter',
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
            $res .= '<h3 class="err">' . Lang::t('_CHECKED_DIRECTORIES') . '</h3>'
                . '<ul class="info"><li class="err"><span>' . implode('</span></li><li class="err"><span>', $checked_dir) . '</span></li></ul>';
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
}
