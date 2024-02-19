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

namespace FormaLms\lib\Alert;

class AlertService
{

    private static $instance = null;
    public static function getInstance()
    {
        if (self::$instance == null) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }

    public function send($user_selected,$course_info = [], $forceSendAlert = false) {

        require_once _base_ . '/lib/lib.eventmanager.php';
        require_once _base_ . '/lib/lib.docebo.php';
        require_once _base_ . '/lib/calendar/CalendarManager.php';
        require_once _base_ . '/appCore/models/UsermanagementAdm.php';

        $uma = new \UsermanagementAdm();

        foreach ($user_selected as $user_id) {
            $reg_code = null;
            if ($nodes = $uma->getUserFolders($user_id)) {
                $idst_oc = array_keys($nodes)[0];

                if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                    $reg_code = sql_fetch_object($query)->idOrg;
                }
            }

            $array_subst = [
                '[url]' => \FormaLms\lib\Get::site_url(),
                '[dynamic_link]' => getCurrentDomain($reg_code) ?: \FormaLms\lib\Get::site_url(),
                '[course]' => $course_info['name'],
                '[medium_time]' => $course_info['mediumTime'], //Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'))
                '[course_name]' => $course_info['name'],
                '[course_code]' => $course_info['code'],
            ];

            // message to user that is waiting
            $msg_composer = new \EventMessageComposer();
            $msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', $array_subst);
            $msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);
            $msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

            if ($course_info['sendCalendar'] && $course_info['course_type'] == 'classroom') {
                $uinfo = \Docebo::aclm()->getUser($user_id, false);
                $calendar = \CalendarManager::getCalendarDataContainerForDateDays((int) $course_info['id'], (int) $course_info['id_date'], (int) $uinfo[ACL_INFO_IDST]);
                $msg_composer->setAttachments([$calendar->getFile()]);
            }

            createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', [$user_id], $msg_composer, $forceSendAlert);
        }
    }

}