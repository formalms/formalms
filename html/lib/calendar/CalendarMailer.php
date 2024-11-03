<?php

namespace FormaLms\lib\Calendar;
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
use CalendarDataContainer;
use FormaLms\lib\Mailer\FormaMailer;
use FormaLms\lib\Domain\DomainHandler;

class CalendarMailer extends FormaMailer
{
    public function sendCalendarToUser(CalendarDataContainer $calendar, $user)
    {
        $mail_text = \Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT', 'course');
        $mail_text = str_replace(['[url]', '[userid]'], [\FormaLms\lib\Get::site_url(), $user['username']], $mail_text);

        $subject = \Lang::t('_COURSE_DATE_CALENDAR_MAILTEXT_TITLE', 'course');
        $this->SendMail(
            [$user['email']],
            $subject,
            $mail_text,
            DomainHandler::getInstance()->getMailerField('sender_mail_system'),
            [$calendar->getFile()],
            [
                MAIL_REPLYTO => DomainHandler::getInstance()->getMailerField('replyto_mail'),
                MAIL_SENDER_ACLNAME => DomainHandler::getInstance()->getMailerField('sender_name_system'),
            ]
        );
    }
}
