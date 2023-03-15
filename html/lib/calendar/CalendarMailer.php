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

class CalendarMailer extends FormaMailer
{
    public function sendCalendarToUser(CalendarDataContainer $calendar, $user)
    {
        $mail_text = Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT', 'course');
        $mail_text = str_replace(['[url]', '[userid]'], [FormaLms\lib\Get::site_url(), $user['userid']], $mail_text);

        $subject = Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT_TITLE', 'course');
        $this->SendMail(
            FormaLms\lib\Get::sett('sender_event'),
            [$user['email']],
            $subject,
            $mail_text,
            [$calendar->getFile()],
            [
                MAIL_REPLYTO => FormaLms\lib\Get::sett('sender_event'),
                MAIL_SENDER_ACLNAME => FormaLms\lib\Get::sett('use_sender_aclname'),
            ]
        );
    }
}
