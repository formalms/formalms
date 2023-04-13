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

/**
 * @category ajax server
 *
 * @version $Id:$
 */

// here all the specific code ==========================================================

$op = FormaLms\lib\Get::req('op', DOTY_ALPHANUM, '');

switch ($op) {
    case 'get_lang':
        $module_name = FormaLms\lib\Get::req('module_name', DOTY_ALPHANUM, '');
        $platform = FormaLms\lib\Get::req('platform', DOTY_ALPHANUM, '');

        $lang = &FormaLanguage::createInstance('standard', 'framework');
        $lang->setGlobal();
        $lang = &FormaLanguage::createInstance($module_name, $platform);

        $value = [
//			'_TITLE_ASK_A_FRIEND' 	=> $lang->def('_TITLE_ASK_A_FRIEND'),
//			'_WRITE_ASK_A_FRIEND' 	=> $lang->def('_WRITE_ASK_A_FRIEND'),
            '_SEND' => $lang->def('_SEND'),
            '_UNDO' => $lang->def('_UNDO'),
//			'_ASK_FRIEND_SEND' 		=> $lang->def('_SEND'),
//			'_ASK_FRIEND_FAIL' 		=> $lang->def('failed'),

            '_SUBJECT' => $lang->def('_SUBJECT'),
            '_MESSAGE_TEXT' => $lang->def('_MESSAGE_TEXT'),
            '_OPERATION_SUCCESSFUL' => $lang->def('_OPERATION_SUCCESSFUL'),
            '_OPERATION_FAILURE' => $lang->def('_OPERATION_FAILURE'),
        ];

        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $output = $json->encode($value);
        aout($output);
     break;
    case 'send_ask_friend':
        require_once _adm_ . '/lib/lib.myfriends.php';

        $module_name = FormaLms\lib\Get::req('module_name', DOTY_ALPHANUM, '');
        $platform = FormaLms\lib\Get::req('platform', DOTY_ALPHANUM, '');

        $id_friend = importVar('id_friend');
        $message_request = importVar('message_request');

        $lang = &FormaLanguage::createInstance('standard', 'framework');
        $lang->setGlobal();
        $lang = &FormaLanguage::createInstance($module_name, $platform);

        $my_fr = new MyFriends(getLogUserId());
        if ($my_fr->addFriend($id_friend, MF_WAITING, $message_request)) {
            $value = ['re' => true];
        } else {
            $value = ['re' => false];
        }

        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $output = $json->encode($value);
        aout($output);
     break;
    case 'send_message':
        require_once _adm_ . '/lib/lib.message.php';

        $module_name = importVar('module_name');
        $platform = importVar('platform');

        $recipient = importVar('send_to');
        $message_subject = importVar('message_subject');
        $message_text = importVar('message_text');

        $lang = &FormaLanguage::createInstance('standard', 'framework');
        $lang->setGlobal();
        $lang = &FormaLanguage::createInstance($module_name, $platform);

        if (MessageModule::quickSendMessage(getLogUserId(), $recipient, $message_subject, $message_text)) {
            $value = ['re' => true];
        } else {
            $value = ['re' => false];
        }
        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $output = $json->encode($value);
        aout($output);
     break;
}
