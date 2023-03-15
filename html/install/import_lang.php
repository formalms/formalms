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

include 'bootstrap.php';

require_once _installer_ . '/lib/lib.lang_import.php';
require_once _lib_ . '/loggers/lib.logger.php';
require_once _base_ . '/db/lib.docebodb.php';

$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

$dbInfo = $session->get('db_info');

DbConn::getInstance(false, [
    'db_type' => $dbInfo['db_type'],
    'db_host' => $dbInfo['db_host'],
    'db_user' => $dbInfo['db_user'],
    'db_pass' => $dbInfo['db_pass'],
    'db_name' => $dbInfo['db_name'],
]);

sql_query("SET NAMES 'utf8'");
sql_query("SET CHARACTER SET 'utf8'");
//TODO NO_Strict_MODE: to be confirmed
sql_query("SET SQL_MODE = 'NO_AUTO_CREATE_USER'");

$platform_code = FormaLms\lib\Get::pReq('platform', DOTY_STRING);
$lang = FormaLms\lib\Get::pReq('lang', DOTY_STRING);
$upgrade = FormaLms\lib\Get::pReq('upgrade', DOTY_INT);

$lang_arr = array_keys($session->get('lang_install'));
$pl_arr = ['framework'];

$cur['value'] = current($pl_arr);
next($pl_arr);
$prev = '';
while ($cur && $prev != $platform_code) {
    $prev = $cur['value'];
    $cur['value'] = current($pl_arr);
    next($pl_arr);
}
$next_platform = ($cur ? $cur['value'] : false);

if ($next_platform === false) {
    $cur['value'] = current($lang_arr);
    next($lang_arr);
    $prev = '';
    while ($cur && $prev != $lang) {
        $prev = $cur['value'];
        $cur['value'] = current($lang_arr);
        next($lang_arr);
    }
    $next_lang = ($cur ? $cur['value'] : false);
    $next_platform = $pl_arr[0];
} else {
    $next_lang = $lang;
}

$fn = _base_ . '/xml_language/lang[' . $lang . '].xml';

/* $overwrite =true;
if ($upgrade == 1) {
    $overwrite =false;
} */
// we always overwrite, also on upgrade
// cause there are too many changes

if (file_exists($fn)) {
    $LangAdm = new LangAdm();
    $LangAdm->importTranslation($fn, true, false);
}

$res = [];
$res['current_lang'] = $lang;
$res['current_platform'] = $platform_code;
$res['next_lang'] = $next_lang;
$res['next_platform'] = $next_platform;

require_once _base_ . '/lib/lib.json.php';
$json = new Services_JSON();
ob_clean();
echo $json->encode($res);

exit();
