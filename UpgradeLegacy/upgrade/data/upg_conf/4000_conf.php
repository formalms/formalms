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

// if this file is not needed for a specific version,
// just don't create it.

//require_once('bootstrap.php');
//require_once('../config.php');

// Upgrade procedure from Docebo 3.6.x series
// Source and converted from old procedure

/**
 * This function must always an array with 2 value
 * 1) return a status :  0 = error , 1 = no change required, 2 = made change
 * 2) the config data file
 * Error message can be appended to $GLOBALS['debug'].
 */

// Create this function only if needed, else you can remove it
// (we check it with function_exists)
function upgradeConfig4000($config)
{
    $config_sts = 0;

    if (isset($GLOBALS['cfg']['db_type'])) {
        // config already upgraded
        $config_sts = 1;
    } else {
        // config  upgraded
        $config_sts = 2;

        require_once _base_ . '/config.php';

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $dbInfo = $session->get('db_info');

        $dbInfo['db_host'] = $GLOBALS['dbhost'];
        $dbInfo['db_user'] = $GLOBALS['dbuname'];
        $dbInfo['db_pass'] = $GLOBALS['dbpass'];
        $dbInfo['db_name'] = $GLOBALS['dbname'];

        $session->set('db_info', $dbInfo);

        $uploadMethod = 'http';
        if ($GLOBALS['uploadType'] == 'fs') {
            $uploadMethod = 'http';
        } else {
            $uploadMethod = 'ftp';
        }
        $session->set('upload_method', $uploadMethod);

        $ulInfo['ftp_host'] = $GLOBALS['ftphost'];
        $ulInfo['ftp_port'] = $GLOBALS['ftpport'];
        $ulInfo['ftp_user'] = $GLOBALS['ftpuser'];
        $ulInfo['ftp_pass'] = $GLOBALS['ftppass'];
        $ulInfo['ftp_path'] = $GLOBALS['ftppath'];

        $session->set('ul_info', $ulInfo);

        $session->save();

        $fn = _upgrader_ . '/data/config_template.php';

        // generateConfig from install/lib/lib.php
        $config = generateConfig($fn);
    }

    return [$config_sts, $config];
}
