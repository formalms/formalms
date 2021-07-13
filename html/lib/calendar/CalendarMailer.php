<?php


class CalendarMailer extends DoceboMailer
{
    public function sendCalendarToUser(CalendarDataContainer $calendar, $user)
    {

        $mail_text = Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT', 'course');
        $mail_text = str_replace(['[url]', '[userid]'], [Get::site_url(), $user['userid']], $mail_text);

        $subject = Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT_TITLE', 'course');
        $this->SendMail(
            Get::sett('sender_event'),
            $user['email'],
            $subject,
            $mail_text,
            $calendar->getFile(),
            array(
                MAIL_REPLYTO => Get::sett('sender_event'),
                MAIL_SENDER_ACLNAME => Get::sett('use_sender_aclname')
            )
        );
    }
}