<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

/**
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5.0
 *
 * ( editor = Eclipse 3.2.0 [phpeclipse,subclipse,WTP], tabwidth = 4 )
 */

require_once($GLOBALS['where_framework']."/lib/lib.calendar_core.php");
require_once($GLOBALS['where_framework']."/lib/lib.calevent_core.php");
require_once($GLOBALS['where_framework']."/lib/lib.calevent_lms.php");

require_once(_adm_."/models/PluginConferenceAdm.php");

class Conference_Manager {

  var $PluginConferenceAdm;

	function Conference_Manager() {
		$this->creation_limit_per_user = Get::sett('conference_creation_limit_per_user');

    $this->PluginConferenceAdm = new PluginConferenceAdm();
	}

	function _getRoomTable() {

		return $GLOBALS['prefix_scs'].'_room';
	}

	function _query($query) {

		$re = sql_query($query);
		return $re;
	}

	function canOpenRoom($start_time) {
		return true;
	}

	function getRoomMaxParticipants($id_room)
	{
		list($max_participants) = sql_fetch_row(sql_query(	"SELECT maxparticipants"
																." FROM ".$this->_getRoomTable().""
																." WHERE id = '".$id_room."'"));

		return $max_participants;
	}

	function insert_room($idCourse,$idSt,$name,$room_type,$start_timestamp,$end_timestamp,$meetinghours,$maxparticipants,$bookable, $startdate, $starthour, $startminute) {

		//save in calendar the corresponding event

		$start_date = date("Y-m-d H:i:s", $start_timestamp);
		$end_date = date("Y-m-d H:i:s", $end_timestamp);

		$parts[1] = substr($start_date, 0, 4);
		$parts[2] = substr($start_date, 5, 2);
		$parts[3] = substr($start_date, 8, 2);
		$parts[4] = substr($start_date, 11, 2);
		$parts[5] = substr($start_date, 14, 2);
		$parts[6] = substr($start_date, 17, 2);


		$event=new DoceboCalEvent_lms();
		$event->calEventClass="lms";
		$event->start_year=$parts[1];
		$event->start_month=$parts[2];
		$event->start_day=$parts[3];

		$event->_year=$event->start_year;
		$event->_month=$event->start_month;
		$event->_day=$event->start_day;

		$event->start_hour=$parts[4];
		$event->start_min=$parts[5];
		$event->start_sec=$parts[6];

		$parts[1] = substr($end_date, 0, 4);
		$parts[2] = substr($end_date, 5, 2);
		$parts[3] = substr($end_date, 8, 2);
		$parts[4] = substr($end_date, 11, 2);
		$parts[5] = substr($end_date, 14, 2);
		$parts[6] = substr($end_date, 17, 2);

		$event->end_year=$parts[1];
		$event->end_month=$parts[2];
		$event->end_day=$parts[3];

		$event->end_hour=$parts[4];
		$event->end_min=$parts[5];
		$event->end_sec=$parts[6];

		$event->title=$name;
		$event->description=$name;

		$event->_owner=$idSt;
		if (!$event->_owner) $event->_owner==Docebo::user()->getIdSt();

		$event->category="b";
		$event->private="";
		$event->idCourse=$idCourse;

		$idCal=$event->store();

		//save in database the roomid for user login
		$insert_room = "
		INSERT INTO ".$this->_getRoomTable()."
		( idCal,idCourse,idSt,name, room_type, starttime,endtime,meetinghours,maxparticipants,bookable) VALUES (
			'".$idCal."',
			'".$idCourse."',
			'".$idSt."',
			'".$name."',
			'".$room_type."',
			'".$start_timestamp."',
			'".$end_timestamp."',
			'".$meetinghours."',
			'".$maxparticipants."',
			'".$bookable."'
		)";

		$id_room="";
		$ok=true;
		if(!sql_query($insert_room)) $ok=false;
		if ($ok) $idConference=sql_insert_id();

		if ($ok) {


            $pg=new PluginManager('Conference');
            $classconference=$pg->get_plugin($room_type);


		  $success = $classconference->insertRoom($idConference,$name, $start_date,$end_date, $maxparticipants);

			if (!$success) {
				sql_query("DELETE FROM ".$this->_getRoomTable()." WHERE id=".(int)$idConference);
				$idConference = false;
			}		
		}

		return $idConference;
	}

	function updateRoom($id,$name,$room_type,$start_timestamp,$end_timestamp,$meetinghours,$maxparticipants,$bookable, $startdate, $starthour, $startminute)
	{
            $start_date = date("Y-m-d H:i:s", $start_timestamp);
		$end_date = date("Y-m-d H:i:s", $end_timestamp);

		$parts[1] = substr($start_date, 0, 4);
		$parts[2] = substr($start_date, 5, 2);
		$parts[3] = substr($start_date, 8, 2);
		$parts[4] = substr($start_date, 11, 2);
		$parts[5] = substr($start_date, 14, 2);
		$parts[6] = substr($start_date, 17, 2);


		$event=new DoceboCalEvent_lms();
		$event->calEventClass="lms";
		$event->start_year=$parts[1];
		$event->start_month=$parts[2];
		$event->start_day=$parts[3];

		$event->_year=$event->start_year;
		$event->_month=$event->start_month;
		$event->_day=$event->start_day;

		$event->start_hour=$parts[4];
		$event->start_min=$parts[5];
		$event->start_sec=$parts[6];

		$parts[1] = substr($end_date, 0, 4);
		$parts[2] = substr($end_date, 5, 2);
		$parts[3] = substr($end_date, 8, 2);
		$parts[4] = substr($end_date, 11, 2);
		$parts[5] = substr($end_date, 14, 2);
		$parts[6] = substr($end_date, 17, 2);

		$event->end_year=$parts[1];
		$event->end_month=$parts[2];
		$event->end_day=$parts[3];

		$event->end_hour=$parts[4];
		$event->end_min=$parts[5];
		$event->end_sec=$parts[6];

		$event->title=$name;
		$event->description=$name;

		$event->_owner=$idSt;
		if (!$event->_owner) $event->_owner==Docebo::user()->getIdSt();

		$event->category="b";
		$event->private="";
		$event->idCourse=$idCourse;

		$idCal=$event->store();

		//save in database the roomid for user login
		$update_room = "
		UPDATE ".$this->_getRoomTable()."
		SET 
                idCal='".$idCal."',
                name='".$name."',
                room_type='".$room_type."', 
                starttime='".$start_timestamp."',
                endtime='".$end_timestamp."',
                meetinghours='".$meetinghours."',
                maxparticipants='".$maxparticipants."',
                bookable='".$bookable."'
                WHERE id='".$id."'";

		$ok=sql_query($update_room);
                $idConference = $id;

		if ($ok) {
          $pg = new PluginManager("Conference");
          $classconference=$pg->get_plugin($room_type);

		  $success = $classconference->insertRoom($idConference,$name, $start_date,$end_date, $maxparticipants);

			if (!$success) {
				sql_query("DELETE FROM ".$this->_getRoomTable()." WHERE id=".(int)$idConference);
				$idConference = false;
			}
		}
	}

	function roomInfo($room_id) {

		$room_open = "
		SELECT id,idCal,idCourse,idSt,name,room_type,starttime,endtime,meetinghours,maxparticipants,bookable
		FROM ".$this->_getRoomTable()."
		WHERE id = '".$room_id."'";
		$re_room = $this->_query($room_open);

		return $this->nextRow($re_room);
	}

	function roomActive($idCourse, $at_date = false) {

		$room_open = "
		SELECT id,idCourse,idSt,name,room_type,starttime,endtime,meetinghours,maxparticipants,bookable
		FROM ".$this->_getRoomTable()."
		WHERE idCourse = '".$idCourse."'";

		if ($at_date !== false) {
			$room_open .= " AND endtime >= '".$at_date."'";
		}

		$room_open .= " ORDER BY starttime";

		$re_room = $this->_query($room_open);

		return $re_room;
	}

	function getOldRoom($id_course, $limit)
	{
		$query =	"SELECT id, idSt, idCourse, name, room_type, starttime, endtime, meetinghours, maxparticipants"
					." FROM ".$this->_getRoomTable().""
					." WHERE idCourse = '".$id_course."'"
					." AND endtime < '".time()."'";

		$date = Get::req('filter_date', DOTY_MIXED, '');

		if($date !== '')
		{
			$date = substr(Format::dateDb($date, 'date'), 0, 10);

			$query .= 	" AND starttime >= '".fromDatetimeToTimestamp($date)."'"
						." AND starttime <= '".fromDatetimeToTimestamp($date.' 23:59:59')."'";
		}

		$query .=	" ORDER BY starttime DESC"
					." LIMIT ".$limit.", 10";

		$result = sql_query($query);

		$res = array();

		while($row = sql_fetch_assoc($result))
			$res[] = $row;

		return $res;
	}

	function getOldRoomNumber($id_course)
	{
		$query =	"SELECT COUNT(*)"
					." FROM ".$this->_getRoomTable().""
					." WHERE idCourse = '".$id_course."'"
					." AND endtime < '".time()."'";

		$date = Get::req('filter_date', DOTY_MIXED, '');

		if($date !== '')
		{
			$date = substr(Format::dateDb($date, 'date'), 0, 10);

			$query .= 	" AND starttime >= '".fromDatetimeToTimestamp($date)."'"
						." AND starttime <= '".fromDatetimeToTimestamp($date.' 23:59:59')."'";
		}

		list($result) = sql_fetch_row(sql_query($query));

		return $result;
	}

	function totalRoom($re_room) {

		return sql_num_rows($re_room);
	}

	function nextRow($re_room) {

		return sql_fetch_array($re_room);
	}

	function deleteRoom($room_id) {
		$conference = $this->roomInfo($room_id);

		$room_del = "
		DELETE FROM ".$this->_getRoomTable()."
		WHERE id = '".$room_id."'";
		$re_room = $this->_query($room_del);

		$event=new DoceboCalEvent_lms();
		$event->id=$conference["idCal"];
		$event->del();

        $pg=new PluginManager('Conference');
        $classconference =$pg->get_plugin('ConferenceBBB'); //$conference["room_type"]

    $url = $classconference->deleteRoom($room_id);
		
		return $re_room;
	}

	function getUrl($idConference,$room_type) {
		$conference = $this->roomInfo($idConference);

        $pg = new PluginManager('Conference');
        $classconference = $pg->get_plugin('ConferenceBBB');// PluginManager::getPlugins($plugin_conference['name']); #PLUGIN_SYSTEM_OLD


    $url = $classconference ->getUrl($idConference,$room_type);

		return $url;
	}

	function can_create_user_limit($idSt,$idCourse,$start_timestamp) {
		$ok=true;

		if ($this->creation_limit_per_user) {
			$query="SELECT * FROM  ".$this->_getRoomTable().
			" WHERE idSt='$idSt' AND idCourse='$idCourse' AND starttime=>'$start_timestamp'";
			$re_room=$this->_query($query);
			$p=sql_error();
			$n_room=$this->totalRoom($re_room);

			if ($n_room >= $this->creation_limit_per_user) {
				$ok=false;
			}
		};

		return $ok;
	}

	function can_create_room_limit($idSt,$idCourse,$room_type,$start_timestamp,$end_timestamp) {
		$ok=true;

		$room_limit = Get::sett($room_type.'_max_room');

		$query="SELECT * FROM  ".$this->_getRoomTable().
		" WHERE room_type='$room_type' AND idCourse='$idCourse' AND starttime<='$end_timestamp' AND endtime>='$start_timestamp'";
		$re_room=$this->_query($query);
		$n_room=$this->totalRoom($re_room);
		if ($n_room >= $room_limit) {
			$ok=false;
		}

		return $ok;
	}
}

?>