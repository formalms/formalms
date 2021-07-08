<?php


use Symfony\Component\Uid\Uuid;

class CalendarManager
{

    public static function generateUniqueCalendarId()
    {
        return Uuid::v4()->toRfc4122();
    }

    public static function getCalendarForDateDays($idCourse, $idDate, $idUser = false, $immediateOutput = false)
    {
        $classroomModel = new ClassroomAlms($idCourse, $idDate);
        $calendar = new \Eluceo\iCal\Domain\Entity\Calendar();
        $calendar->setProductIdentifier(Get::sett('page_title'));

        $date = $classroomModel->getDateInfo();

        $days = $classroomModel->getAllDateDay();

        $classrooms = $classroomModel->getClassroomForDropdown();

        $idOrganization = null;
        if (!empty($idUser)) {
            $uma = new UsermanagementAdm();

            if ($nodes = $uma->getUserFolders($idUser)) {
                $idst_oc = array_keys($nodes)[0];

                if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                    $idOrganization = sql_fetch_object($query)->idOrg;
                }
            }
        }

        foreach ($days as $row) {

            $event = new \Eluceo\iCal\Domain\Entity\Event(new \Eluceo\iCal\Domain\ValueObject\UniqueIdentifier($row['calendarId']));
            $event->setSummary($date['name']);
            $event->setDescription($date['description']);
            if ($row['deleted']) {
                $event->setStatus(\Eluceo\iCal\Domain\Enum\StatusType::CANCELLED());
                $event->setMethod(\Eluceo\iCal\Domain\Enum\MethodType::CANCELLED());
            }
            $event->setOrganizer(new \Eluceo\iCal\Domain\ValueObject\Organizer(
                new \Eluceo\iCal\Domain\ValueObject\EmailAddress(Get::sett('sender_event')),
                Get::sett('use_sender_aclname', '')
            ));

            if (array_key_exists((int)$row['classroom'], $classrooms)) {
                $event->setLocation(
                    (new \Eluceo\iCal\Domain\ValueObject\Location(getCurrentDomain($idOrganization) ?: Get::site_url(), $classrooms[(int)$row['classroom']]))
                );
            } else {
                $event->setLocation(
                    (new \Eluceo\iCal\Domain\ValueObject\Location(getCurrentDomain($idOrganization) ?: Get::site_url(), getCurrentDomain($idOrganization) ?: Get::site_url()))
                );
            }

            $event->setOccurrence(
                new \Eluceo\iCal\Domain\ValueObject\TimeSpan(
                    new \Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_begin']), true),
                    new \Eluceo\iCal\Domain\ValueObject\DateTime(DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $row['date_end']), true)
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

        $componentFactory = new \Eluceo\iCal\Presentation\Factory\CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);

        $fileName = $date['name'] . '.ics';
        if ($immediateOutput) {
// 4. Set HTTP headers.
            header('Content-Type: text/calendar; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo $calendarComponent;
        }

        return [
            'name' => $fileName,
            'data' => $calendarComponent
        ];
    }

}