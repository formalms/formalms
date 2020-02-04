<?php

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
define("IN_FORMA", true);
define("_deeppath_", '../');
require(dirname(__FILE__) . '/../base.php');

// start buffer
ob_start();

// initialize
require(_base_ . '/lib/lib.bootstrap.php');
Boot::init(BOOT_DATETIME);

// not a pagewriter but something similar
$GLOBALS['operation_result'] = '';
if (!function_exists("aout")) {
    function aout($string)
    {
        $string = iconv(mb_detect_encoding($string), 'ISO-8859-1', $string);
        $GLOBALS['operation_result'] .= $string;
    }
}

function putNotificationLog($string)
{
    echo $string;

    $filename = _base_ . '/' . _folder_plugins_ . '/Notification/Cron/log.html';
    if (!file_exists($filename)) {
        $logFile = fopen($filename, "wb");
        fwrite($logFile, $string);
        fclose($logFile);
    } else {
        file_put_contents($filename, file_get_contents($filename) . $string);
    }
}

///////////////////////////////////////////////////////////
// do something
aout("[".date("Y-m-d H:i:s")."] START SMS Notification");
aout("<br>");
require_once(Docebo::inc(_folder_plugins_ . "/Notification/Util/Googl.php"));
require_once(Forma::inc(_folder_plugins_ . "/Notification/Features/appLms/models/TestnotificationLms.php"));
require_once(_base_ . '/lib/lib.aclmanager.php');
require_once(Docebo::inc(_adm_ . '/lib/Sms/SmsGatewayManager.php'));
require_once(_base_.'/lib/lib.mailer.php');

$mailer = DoceboMailer::getInstance();
$mailer->SMTPKeepAlive = true;

try {
    $aclManager = new DoceboACLManager();
    $shortUrlService = new Googl();

    $notifications = TestnotificationLms::findAll();
    $smsCellField = Get::sett('sms_cell_num_field');

    foreach ($notifications as $notification) {
        $nowDate = date("Y-m-d 00:00:00");
        $activeFrom = Format::dateDb($notification->getActiveFrom(), 'date');
        $activeTo = Format::dateDb($notification->getActiveTo(), 'date');
        $lastSend = Format::dateDb($notification->getLastSend(), 'datetime');
        $lastSendDateTime = DateTime::createFromFormat('Y-m-d H:i:s', $lastSend);

        // check if active
        if (!$notification->isActive()) {
            continue;
        }
        // check if not yet active
        if ($activeFrom > $nowDate) {
            continue;
        }
        // check if not active anymore
        if ($nowDate > $activeTo && $activeTo != '0000-00-00 00:00:00') {
            continue;
        }
        // check weekend
        if ($notification->isSkipWeekend() && (date("w") == 0 || date("w") == 6)) {
            continue;
        }

        // check dayFrequency
        $nextSendDate = DateTime::createFromFormat('Y-m-d H:i:s', $activeFrom);
        while ($nowDate > $nextSendDate->format('Y-m-d H:i:s')) {
            $nextSendDate = $nextSendDate->modify('+ ' . $notification->getDayFrequency() . ' day');
        }
        if ($nextSendDate->format('Y-m-d H:i:s') > $nowDate) {
            continue;
        }
        // check day hours
        $nextSendHour = array_filter($notification->getDayHours(), function ($elem) use ($lastSendDateTime) {
            return date('Y-m-d').' '.$elem > $lastSendDateTime->format('Y-m-d H:i');
        });
        if (empty($nextSendHour) || current($nextSendHour) > date("H:i")) {
            continue;
        }

        aout("[".date("Y-m-d H:i:s")."] Notification ID --> ".$notification->getId());
        aout("<br>");

        $userIds = $notification->getUserIds();
        foreach ($userIds as $userId) {
            try {
                $text = '';

                $userInfo = $aclManager->getUser($userId);
                $username = $aclManager->relativeId($userInfo[1]);
                $email = $aclManager->relativeId($userInfo[5]);

                $query = "SELECT user_entry FROM %adm_field_userentry WHERE id_common=" . $smsCellField . " AND id_user=" . $userId;
                list($userPhoneNumber) = sql_fetch_row(sql_query($query));
                $userPhoneNumber = ltrim(Get::sett('sms_international_prefix', '') . $userPhoneNumber, '+');

                $url = $shortUrlService->generateSmsShortUrl($username, $notification->getTestObj()->getIdCourse(), $notification->getTestObj()->getIdOrg());

                $text = $notification->getDescription();
                $text = str_ireplace('[URL]', $url, $text);
                $text = str_ireplace('[username]', $username, $text);
                $text = iconv(mb_detect_encoding($text), 'UTF-8', $text);

                if ($notification->getSendType() == 'sms') {
                    aout("[" . date("Y-m-d H:i:s") . "] SMS -> " . $username . " [" . $userPhoneNumber . "] :: " . strip_tags($text));
                    aout("<br>");
                    $result = SmsGatewayManager::send([$userPhoneNumber], strip_tags($text));
                } else {
                    aout("[" . date("Y-m-d H:i:s") . "] EMAIL -> " . $username . " [" . $email . "] :: " . strip_tags($text));
                    aout("<br>");
                    $subject = "Subject";
                    $result = $mailer->SendMail(
                        Get::sett('sender_event'), $email,
                        $subject,
                        $text,
                        false,
                        array(
                            MAIL_REPLYTO => Get::sett('sender_event'),
                            MAIL_SENDER_ACLNAME => false
                        )
                    );
                    if (!$result){
                        aout("[".date("Y-m-d H:i:s")."] ERROR -> " . $mailer->ErrorInfo . " :: [" . $username . "] " . strip_tags($userPhoneNumber?$userPhoneNumber:$email));
                        aout("<br>");
                    }
                }
            } catch (Exception $e) {
                aout("[".date("Y-m-d H:i:s")."] ERROR -> " . $e->getMessage() . " :: [" . $username . "] " . strip_tags($userPhoneNumber));
                aout("<br>");

            }
        }
        aout("[".date("Y-m-d H:i:s")."] SAVE LAST SEND");
        aout("<br>");
        $notification->setTitle(DbConn::getInstance()->escape($notification->getTitle()));
        $notification->setDescription(DbConn::getInstance()->escape($notification->getDescription()));
        $notification->setLastSend(Format::date(date("Y-m-d H:i:s"), 'datetime'));
        $notification->save();
    }
} catch (Exception $e) {
    aout("[".date("Y-m-d H:i:s")."] ERROR -> " . $e->getMessage());
    aout("<br>");

    if ($e->getCode() == GOOGLE_API_KEY_ERROR){

        $acl = new DoceboACL();

        $mail_subject = Lang::t('_GOOGLE_API_KEY_ERROR');

        $mail_text = "[".date("Y-m-d H:i:s")."] ".$_SERVER['HTTP_HOST']." : ERROR -> " . $e->getMessage();


        $godAdmins = $acl->getGroupUDescendants($acl->getGroupST(ADMIN_GROUP_GODADMIN));

        $admins = $acl->getGroupUDescendants($acl->getGroupST(ADMIN_GROUP_ADMIN));

        $recipients = array_merge($godAdmins, $admins);

        $recipients = Docebo::user()->getAclManager()->getArrUserST($recipients);

        foreach ($recipients as $recipient) {
            $rec_data = Docebo::user()->getAclManager()->getUser($recipient, false);
            $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
        }

        $result = $mailer->SendMail(Get::sett('sender_event'), $arr_recipients, $mail_subject, $mail_text);
    }
}

$mailer->smtpClose();

aout("[".date("Y-m-d H:i:s")."] END SMS Notification");
aout("<br>");
///////////////////////////////////////////////////////////////////////////
// finalize
Boot::finalize();

// remove all the echo
ob_clean();

// Print out the page
putNotificationLog($GLOBALS['operation_result']);

// flush buffer
ob_end_flush();


