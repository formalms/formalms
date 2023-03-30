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

$GLOBALS['debug'] = '';

function generateConfig($tpl_fn)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    $dbInfo = $session->get('db_info');
    $ulInfo = $session->get('ul_info');
    $smtpInfo = $session->get('smtp_info');
    $config = '';

    if (file_exists($tpl_fn)) {
        $handle = fopen($tpl_fn, 'r');
        $config = fread($handle, filesize($tpl_fn));
        fclose($handle);
    }
    $config = str_replace('[%-DB_TYPE-%]', addslashes($dbInfo['db_type']), $config);
    $config = str_replace('[%-DB_HOST-%]', addslashes($dbInfo['db_host']), $config);
    $config = str_replace('[%-DB_USER-%]', addslashes($dbInfo['db_user']), $config);
    $config = str_replace('[%-DB_PASS-%]', addslashes($dbInfo['db_pass']), $config);
    $config = str_replace('[%-DB_NAME-%]', addslashes($dbInfo['db_name']), $config);

    switch ($session->get('upload_method')) {
        case 'http':
            $upload_method = 'fs';

            $config = str_replace('[%-FTP_HOST-%]', 'localhost', $config);
            $config = str_replace('[%-FTP_PORT-%]', '21', $config);
            $config = str_replace('[%-FTP_USER-%]', '', $config);
            $config = str_replace('[%-FTP_PASS-%]', '', $config);
            $config = str_replace('[%-FTP_PATH-%]', '/', $config);
            break;
        case 'ftp':
            $upload_method = 'ftp';

            $config = str_replace('[%-FTP_HOST-%]', addslashes($ulInfo['ftp_host']), $config);
            $config = str_replace('[%-FTP_PORT-%]', addslashes($ulInfo['ftp_port']), $config);
            $config = str_replace('[%-FTP_USER-%]', addslashes($ulInfo['ftp_user']), $config);
            $config = str_replace('[%-FTP_PASS-%]', addslashes($ulInfo['ftp_pass']), $config);
            $config = str_replace('[%-FTP_PATH-%]', addslashes($ulInfo['ftp_path']), $config);
            break;
        default:
            break;
    }

    if ($smtpInfo) {
        $config = str_replace('[%-SMTP_USE_DATABASE-%]', addslashes($smtpInfo['use_smtp_database']), $config);
        $config = str_replace('[%-SMTP_USE_SMTP-%]', addslashes($smtpInfo['use_smtp']), $config);
        $config = str_replace('[%-SMTP_HOST-%]', addslashes($smtpInfo['smtp_host']), $config);
        $config = str_replace('[%-SMTP_PORT-%]', addslashes($smtpInfo['smtp_port']), $config);
        $config = str_replace('[%-SMTP_SECURE-%]', addslashes($smtpInfo['smtp_secure']), $config);
        $config = str_replace('[%-SMTP_AUTO_TLS-%]', addslashes($smtpInfo['smtp_auto_tls']), $config);
        $config = str_replace('[%-SMTP_USER-%]', addslashes($smtpInfo['smtp_user']), $config);
        $config = str_replace('[%-SMTP_PWD-%]', addslashes($smtpInfo['smtp_pwd']), $config);
        $config = str_replace('[%-SMTP_DEBUG-%]', addslashes('0'), $config);
    } else {
        $config = str_replace('[%-SMTP_USE_DATABASE-%]', addslashes('off'), $config);
        $config = str_replace('[%-SMTP_USE_SMTP-%]', addslashes('off'), $config);
        $config = str_replace('[%-SMTP_HOST-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_PORT-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_SECURE-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_AUTO_TLS-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_USER-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_PWD-%]', addslashes(''), $config);
        $config = str_replace('[%-SMTP_DEBUG-%]', addslashes('0'), $config);
    }

    $config = str_replace('[%-UPLOAD_METHOD-%]', $upload_method, $config);

    return $config;
}

function getPlatformArray()
{
    return [
        'framework' => 'appCore',
        'lms' => 'appLms',
        'scs' => 'appScs',
    ];
}

function importSqlFile($fn, $allowed_err_codes = [])
{
    $res = ['ok' => true, 'log' => ''];

    $handle = fopen($fn, 'r');
    $content = fread($handle, filesize($fn));
    fclose($handle);

    // This two regexp works fine; don't edit them! :)
    $content = preg_replace('/--(.*)[^$]/', '', $content);
    $sql_arr = preg_split("/;([\s]*)[\n\r]/", $content);

    foreach ($sql_arr as $sql) {
        $qtxt = trim($sql);

        if (!empty($qtxt)) {
            $q = sql_query($qtxt);
            if (!$q) {
                if (!in_array(sql_errno(), $allowed_err_codes)) {
                    $res['log'] .= sql_error() . "\n";
                    $res['ok'] = false;
                }
            }
        }
    }

    $GLOBALS['debug'] .= $res['log'];

    return $res;
}

function getToUpgradeArray()
{
    $to_upgrade_arr = [];

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    foreach ($GLOBALS['cfg']['versions'] as $ver => $label) {
        if ($ver > $session->get('start_version')) {
            $to_upgrade_arr[] = $ver;
        }
    }

    // $to_upgrade_arr[]=getVersionIntNumber($GLOBALS['cfg']['endversion']);

    return $to_upgrade_arr;
}

//  3xxx : docebo ce versions series 3.x.x
//  4xxx : docebo ce versions series 4.x.x
// 1xxxx : forma     versions series 1.x  (formely 1.xx.xx )

function getVersionIntNumber($ver)
{
    if (version_compare($ver, '3.6.0', '>=') && version_compare($ver, '4.0.5', '<=')) {
        // docebo ce versions series
        $res = str_pad(str_replace('.', '', $ver), 4, '0', STR_PAD_RIGHT);
    } else {
        $res = array_search($ver, $GLOBALS['cfg']['versions'], true);
        /* OLD METHOD
                // forma     versions series 1.x   (formely 1.xx.nn ) =>  1xxnn
                // forma versions series 1:  1.0 - 1.1 - 1.2 - .. - 1.9 .. => 10900
                $arr_v = explode(".", $ver);
                $res = "";
                $first = true;
                foreach ($arr_v as $key => $val) {
                    if ($first) {
                        $res = $val;
                        $first = false;
                    } else {
                        $res = $res . str_pad($val, 2, '0', STR_PAD_LEFT);
                    }
                }
                $res = str_pad($res, 5, '0', STR_PAD_RIGHT);
        */
    }

    return $res;
}
