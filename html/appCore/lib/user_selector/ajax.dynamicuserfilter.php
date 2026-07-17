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

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Here we will process the dynamic filter actions ...
 */
require_once _adm_ . '/lib/user_selector/lib.dynamicuserfilter.php';

require_once _adm_ . '/lib/lib.field.php';
require_once _base_ . '/lib/lib.aclmanager.php';

$db = \FormaLms\db\DbConn::getInstance();

$op = FormaLms\lib\Get::req('op', DOTY_ALPHANUM);

switch ($op) {
    case 'checkuser':
    $user_to_check = FormaLms\lib\Get::req('user', DOTY_INT, false);
    $lib = new DynamicUserFilter('dynfilter');
    $output = $lib->chechUser($user_to_check);
    $x = ['response' => ($output ? 'true' : 'false'), '_value' => $output, '_test' => $_testvar];
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($x, \FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
   break;

  case 'filterusers':
    $lib = new DynamicUserFilter('dynfilter');
    $temp = $lib->getUsers();
    $output = ['response' => implode(', ', $temp), '_test' => $_testvar, 'query' => $query];
        if ($output['response'] == '') {
            $output['response'] = 'Selezione vuota.';
        }
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
   break;

  default:
}
