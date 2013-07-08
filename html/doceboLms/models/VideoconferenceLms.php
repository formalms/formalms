<?php
class VideoconferenceLms extends LmsController
{
	protected $id_user;

	public function __construct($id_user)
	{
		$this->id_user = $id_user;
	}

	protected function getUserCourse()
	{
		$query =	"SELECT idCourse"
					." FROM %lms_courseuser"
					." WHERE idUser = '".$this->id_user."'";

		$result = sql_query($query);
		$res = array(0 => 0);

		while(list($id_course) = sql_fetch_row($result))
			$res[] = $id_course;

		return $res;
	}

	public function getCourseName()
	{
		$query =	"SELECT idCourse, name"
					." FROM %lms_course";

		$result = sql_query($query);
		$res = array();

		while(list($id_course, $name) = sql_fetch_row($result))
			$res[$id_course] = $name;

		return $res;
	}

	public function getActiveConference()
	{
		$query =	"SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants"
					." FROM conference_room"
					." WHERE idCourse IN(".implode(',', $this->getUserCourse()).")"
					." AND starttime <= '".fromDatetimeToTimestamp(date('Y-m-d H:i:s'))."'"
					." AND endtime >= '".fromDatetimeToTimestamp(date('Y-m-d H:i:s'))."'"
					." ORDER BY starttime, name";

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[$row['id']] = $row;

		return $res;
	}

	public function getPlannedConference()
	{
		$query =	"SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants"
					." FROM conference_room"
					." WHERE idCourse IN(".implode(',', $this->getUserCourse()).")"
					." AND starttime > '".fromDatetimeToTimestamp(date('Y-m-d H:i:s'))."'"
					." ORDER BY starttime, name";

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[$row['id']] = $row;

		return $res;
	}

	public function getHistoryConference()
	{
		$query =	"SELECT id, idCal, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants"
					." FROM conference_room"
					." WHERE idCourse IN(".implode(',', $this->getUserCourse()).")"
					." AND endtime < '".fromDatetimeToTimestamp(date('Y-m-d H:i:s'))."'"
					." ORDER BY starttime, name";

		$result = sql_query($query);
		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[$row['id']] = $row;

		return $res;
	}
}
?>