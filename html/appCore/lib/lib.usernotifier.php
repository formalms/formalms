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
 * @version  $Id: lib.usernotifier.php 955 2007-02-03 15:19:40Z fabio $
 *
 * @author   Emanuele Sandri <esandri@docebo.com>
 *
 * This is the class for ClassEvents in Forma
 **/
require_once _base_ . '/lib/lib.event.php';

class FormaUserNotifier extends FormaEventConsumer
{
    public function _getConsumerName()
    {
        return 'FormaUserNotifier';
    }

    public function actionEvent(&$event)
    {
        parent::actionEvent($event);

        // initializing
        require_once _adm_ . '/lib/lib.field.php';

        $acl_man = &\FormaLms\lib\Forma::getAclManager();;
        $field_man = new FieldList();
        $send_to_field = FormaLms\lib\Get::sett('sms_cell_num_field');

        $arr_mail_recipients = [];
        $arr_sms_recipients = [];

        // recover event information
        $arr_recipients = explode(',', $event->getProperty('recipientid'));
        /** @var EventMessageComposer $msg_composer */
        $msg_composer = unserialize(urldecode($event->getProperty('MessageComposer')));
        $msg_composer->after_unserialize();
        $force_email_send = $event->getProperty('force_email_send');

        if (!is_array($arr_recipients) || empty($arr_recipients)) {
            return;
        }

        // recover user info and convert to idst if required
        if (is_numeric($arr_recipients[0])) {
            $idst_users = &$arr_recipients;
        } else {
            $idst_users = $acl_man->fromUseridToIdst($arr_recipients);
        }
        $users_info = &$acl_man->getUsers($idst_users);

        // recove setting
        $users_lang = $acl_man->getSettingValueOfUsers('ui.language', false, true);
        $users_sms = $field_man->showFieldForUserArr($idst_users, [$send_to_field]);

        // scan all users

        if (!is_array($users_info) || empty($users_info)) {
            return;
        }
        foreach ($users_info as $user_dett) {
            if ($user_dett[ACL_INFO_VALID] == '1') {
                // recover media setting
                $idst_user = $user_dett[ACL_INFO_IDST];
                $media = usernotifier_getUserEventChannel($idst_user, $event->getClassName());

                $lang = (isset($users_lang[$idst_user]) && $users_lang[$idst_user] !== null
                    ? $users_lang[$idst_user]
                    : Lang::getDefault());

                if (in_array('email', $media) || $force_email_send == 'true') {
                    if ($user_dett[ACL_INFO_EMAIL] != '') {
                        $arr_mail_recipients[$lang][$idst_user] = $user_dett[ACL_INFO_EMAIL];
                    }
                }

                if (in_array('sms', $media)) {
                    if ($users_sms[$idst_user][$send_to_field] != '') {
                        $arr_sms_recipients[$lang][$idst_user] = $users_sms[$idst_user][$send_to_field];
                    }
                }
            }
        }

        if (!empty($arr_mail_recipients)) {
            $lang_mail = array_keys($arr_mail_recipients);
            foreach ($lang_mail as $lang_code) {
                reset($arr_mail_recipients[$lang_code]);
                $this->_sendMail(
                    $msg_composer->getSubject('email', $lang_code),
                    $msg_composer->getBody('email', $lang_code),
                    $msg_composer->getAttachments(),
                    $arr_mail_recipients[$lang_code],
                    $users_info
                );
            }
        }
        if (!empty($arr_sms_recipients)) {
            $lang_sms = array_keys($arr_sms_recipients);
            foreach ($lang_sms as $lang_code) {
                reset($arr_sms_recipients[$lang_sms]);
                $this->_sendSms(
                    $msg_composer->getBody('sms', $lang_code),
                    $arr_sms_recipients[$lang_code],
                    $users_info
                );
            }
        }

        return true;
    }

    public function _sendMail($subject, $body, $attachments, &$mail_recipients, &$users_info = false)
    {
        $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();
        $acl_man = \FormaLms\lib\Forma::getAclManager();

        foreach ($mail_recipients as $id => $mail) {
            $base_body = $body;
            if (isset($users_info[$id])) {
                $base_body = str_replace(
                    ['[firstname]', '[lastname]', '[username]'],
                    [$users_info[$id][ACL_INFO_FIRSTNAME], $users_info[$id][ACL_INFO_LASTNAME], $acl_man->relativeId($users_info[$id][ACL_INFO_USERID])],
                    $base_body
                );
            }

            $mailer->SendMail(
                [$mail],
                $subject,
                $base_body,
                FormaLms\lib\Get::sett('sender_event'),
                $attachments,
                [
                    MAIL_REPLYTO => FormaLms\lib\Get::sett('sender_event'),
                    MAIL_SENDER_ACLNAME => FormaLms\lib\Get::sett('use_sender_aclname'),
                ]
            );
        }
    }

    public function _sendSms($body, &$sms_recipients, &$users_info = false)
    {
        require_once \FormaLms\lib\Forma::inc(_adm_ . '/lib/Sms/SmsGatewayManager.php');
        SmsGatewayManager::send($sms_recipients, strip_tags($body));
    }
}

function usernotifier_getUserEventChannel($idst, $event_class)
{
    $query = 'SELECT em.channel, em.permission'
        . ' FROM ' . $GLOBALS['prefix_fw'] . '_event_manager as em'
        . ' JOIN ' . $GLOBALS['prefix_fw'] . '_event_class as ec'
        . ' WHERE ec.idClass = em.idClass '
        . "   AND ec.class='" . $event_class . "'";

    $rs_manager = sql_query($query);
    if ($rs_manager === false) {
        return [];
    }
    if (sql_num_rows($rs_manager) == 0) {
        return [];
    }

    list($channel, $permission) = sql_fetch_row($rs_manager);

    $media = [];
    /*
        if( $permission == 'not_used' ) {
            return array();
        } elseif( $permission == 'mandatory' ) {

            if( strlen($channel) > 0 ) {
                $media = explode( ',', $channel );
            }
        } else {

            $query = "SELECT eu.channel "
                    ." FROM ".$GLOBALS['prefix_fw']."_event_user as eu"
                    ." JOIN ".$GLOBALS['prefix_fw']."_event_manager as em"
                    ." JOIN ".$GLOBALS['prefix_fw']."_event_class as ec"
                    ." WHERE eu.idEventMgr=em.idEventMgr "
                    ."   AND ec.idClass = em.idClass "
                    ."   AND eu.idst='".(int)$idst."'"
                    ."   AND ec.class='".$event_class."'";
            $rs_user = sql_query( $query );
            if( sql_num_rows($rs_user) == 1 ) {
                list($channel) = sql_fetch_row( $rs_user );

                if( strlen($channel) > 0 ) {
                    $media = explode( ',', $channel );
                }
            } else {
                list($channel) = sql_fetch_row( $rs_manager );
                if( strlen($channel) > 0 ) {
                    $media = explode( ',', $channel );
                }
            }
            return $media;
        }
    */
    if ($permission == 'mandatory') {
        if (strlen($channel) > 0) {
            $media = explode(',', $channel);
        }
    }

    return $media;
}

function usernotifier_getUserEventStatus($idst, $event_class)
{
    $media = usernotifier_getUserEventChannel($idst, $event_class);

    return count($media) > 0;
}
