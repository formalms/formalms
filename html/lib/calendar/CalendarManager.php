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

use Symfony\Component\Uid\Uuid;

class CalendarManager
{
    public static function generateUniqueCalendarId()
    {
        return Uuid::v4()->toRfc4122();
    }

    /**
     * @param int   $idUser
     * @param false $immediateOutput
     *
     * @return CalendarDataContainer
     */
    public static function getCalendarDataContainerForDateDays(int $idCourse, int $idDate, ?int $idUser = -1, bool $immediateOutput = false)
    {
        $dateManager = new DateManager();
        $dateManager->generateCalendarIdForDate($idDate);
        $timezoneString = FormaLms\lib\Get::sett('timezone', 'Europe/Rome');

        $classroomModel = new ClassroomAlms($idCourse, $idDate);

        $dateInfo = $classroomModel->getDateInfo();

        $calendar = new \Eluceo\iCal\Domain\Entity\Calendar();
        $calendar->setProductIdentifier(FormaLms\lib\Get::sett('page_title'));
        $datetimezone = new DateTimeZone($timezoneString);
        $calendar->setCalId($dateInfo['calendarId']);
        $calendar->setName(sprintf('%s - %s', FormaLms\lib\Get::sett('page_title'), $dateInfo['name']));

        $date = $classroomModel->getDateInfo();

        $days = $classroomModel->getAllDateDay();

        $classrooms = $classroomModel->getClassroomForDropdown();

        $idOrganization = null;
        if ($idUser > 0) {
            $uma = new UsermanagementAdm();

            if ($nodes = $uma->getUserFolders($idUser)) {
                $idst_oc = array_keys($nodes)[0];

                if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                    $idOrganization = sql_fetch_object($query)->idOrg;
                }
            }
        }

        foreach ($days as $row) {
            $timezone = \Eluceo\iCal\Domain\Entity\TimeZone::createFromPhpDateTimeZone($datetimezone,
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_begin'], $datetimezone),
                DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_end'], $datetimezone)
            );
            $calendar->addTimeZone($timezone);

            if (empty($row['calendarId'])) {
                $row['calendarId'] = $dateManager->generateCalendarIdForDateDay($idDate, $row['id']);
            }

            $event = new \Eluceo\iCal\Domain\Entity\Event(new \Eluceo\iCal\Domain\ValueObject\UniqueIdentifier($row['calendarId']));
            $event->setSummary($date['name']);

            $event->setDescription(strip_tags($date['description']));

            if ($row['deleted']) {
                $event->setStatus(\Eluceo\iCal\Domain\Enum\Status::CANCELLED());
                $event->setMethod(\Eluceo\iCal\Domain\Enum\Method::CANCELLED());
            }

            $event->setOrganizer(new \Eluceo\iCal\Domain\ValueObject\Organizer(
                new \Eluceo\iCal\Domain\ValueObject\EmailAddress(FormaLms\lib\Get::sett('sender_event')),
                FormaLms\lib\Get::sett('use_sender_aclname', '')
            ));

            if (array_key_exists((int) $row['classroom'], $classrooms)) {
                $classroomString = strip_tags($classrooms[(int) $row['classroom']]);
                $event->setLocation(
                    (new \Eluceo\iCal\Domain\ValueObject\Location(getCurrentDomain($idOrganization) ?: FormaLms\lib\Get::site_url(), $classroomString))
                );
            } else {
                $event->setLocation(
                    (new \Eluceo\iCal\Domain\ValueObject\Location(getCurrentDomain($idOrganization) ?: FormaLms\lib\Get::site_url(), getCurrentDomain($idOrganization) ?: FormaLms\lib\Get::site_url()))
                );
            }

            $event->setOccurrence(
                new \Eluceo\iCal\Domain\ValueObject\TimeSpan(
                    new \Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_begin'], new DateTimeZone($timezoneString)), true),
                    new \Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_end'], new DateTimeZone($timezoneString)), true)
                )
            );

            $calendarAlarmText = Lang::t('_COURSE_CALENDAR_ALARM_TEXT');
            $calendarAlarmText = str_replace('[name]', $date['name'], $calendarAlarmText);

            $event->addAlarm(
                new \Eluceo\iCal\Domain\ValueObject\Alarm(
                    new \Eluceo\iCal\Domain\ValueObject\Alarm\DisplayAction($calendarAlarmText),
                    (new \Eluceo\iCal\Domain\ValueObject\Alarm\RelativeTrigger(DateInterval::createFromDateString('-15 minutes')))->withRelationToEnd()
                )
            );
            /*$event->addAttachment(
                new \Eluceo\iCal\Domain\ValueObject\Attachment(
                    new \Eluceo\iCal\Domain\ValueObject\Uri('https://ical.poerschke.nrw/favicon.ico'),
                    'image/x-icon'
                )
            );*/

            $calendar->addEvent($event);
        }
        $fileName = $date['name'] . '.ics';

        $calendarContainer = new CalendarDataContainer($fileName, $calendar);

        if ($immediateOutput) {
            $calendarContainer->download();
        }

        return $calendarContainer;
    }
}
