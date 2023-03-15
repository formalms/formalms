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

class VideoconferenceLms extends LmsController
{
    protected $id_user;

    public function __construct($id_user)
    {
        $this->id_user = $id_user;
    }

    protected function getUserCourse()
    {
        $query = 'SELECT idCourse'
                    . ' FROM %lms_courseuser'
                    . " WHERE idUser = '" . $this->id_user . "'";

        $result = sql_query($query);
        $res = [0 => 0];

        while (list($id_course) = sql_fetch_row($result)) {
            $res[] = $id_course;
        }

        return $res;
    }

    public function getCourseName()
    {
        $query = 'SELECT idCourse, name'
                    . ' FROM %lms_course';

        $result = sql_query($query);
        $res = [];

        while (list($id_course, $name) = sql_fetch_row($result)) {
            $res[$id_course] = $name;
        }

        return $res;
    }

    public function getActiveConference()
    {
        $query = 'SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants'
                    . ' FROM conference_room'
                    . ' WHERE idCourse IN(' . implode(',', $this->getUserCourse()) . ')'
                    . " AND starttime <= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
                    . " AND endtime >= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
                    . ' ORDER BY starttime, name';

        $result = sql_query($query);
        $res = [];

        while ($row = sql_fetch_assoc($result)) {
            $res[$row['id']] = $row;
        }

        return $res;
    }

    public function getPlannedConference()
    {
        $query = 'SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants'
                    . ' FROM conference_room'
                    . ' WHERE idCourse IN(' . implode(',', $this->getUserCourse()) . ')'
                    . " AND starttime > '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
                    . ' ORDER BY starttime, name';

        $result = sql_query($query);
        $res = [];

        while ($row = sql_fetch_assoc($result)) {
            $res[$row['id']] = $row;
        }

        return $res;
    }

    public function getHistoryConference()
    {
        $query = 'SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants'
                    . ' FROM conference_room'
                    . ' WHERE idCourse IN(' . implode(',', $this->getUserCourse()) . ')'
                    . " AND endtime < '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
                    . ' ORDER BY starttime, name';

        $result = sql_query($query);
        $res = [];

        while ($row = sql_fetch_assoc($result)) {
            $res[$row['id']] = $row;
        }

        return $res;
    }
}
