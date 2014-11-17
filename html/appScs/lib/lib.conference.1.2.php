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

// TODO : support for BBB is experimental - must be refined
require_once($GLOBALS['where_scs'].'/lib/lib.bbb.php');
require_once($GLOBALS['where_scs'].'/lib/lib.dimdim.php');
//require_once($GLOBALS['where_scs'].'/lib/lib.intelligere.php');
require_once($GLOBALS['where_scs'].'/lib/lib.teleskill.php');

require_once($GLOBALS['where_framework']."/lib/lib.calendar_core.php");
require_once($GLOBALS['where_framework']."/lib/lib.calevent_core.php");
require_once($GLOBALS['where_framework']."/lib/lib.calevent_lms.php");

class Conference_Manager {

	function Conference_Manager() {
		$this->creation_limit_per_user = Get::sett('conference_creation_limit_per_user');
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
			switch($room_type) {
				case "bbb":
// TODO : support for BBB is experimental - must be refined
					$acl_manager =& Docebo::user()->getAclManager();
					$bbb = new Bbb_Manager();
					$display_name = Docebo::user()->getUserName();
					$u_info = $acl_manager->getUser(getLogUserId(), false);
					$user_email=$u_info[ACL_INFO_EMAIL];
					$confkey = $bbb->generateConfKey();
					$audiovideosettings=1;
					$maxmikes=(int)Get::sett("bbb_max_mikes");
					$extra_conf = array();
					(isset($_POST['lobbyEnabled']) ? $extra_conf['lobbyEnabled'] = true : $extra_conf['lobbyEnabled'] = false);
					//(isset($_POST['display_phone_info']) ? $extra_conf['display_phone_info'] = true : $extra_conf['display_phone_info'] = false);
					//(isset($_POST['show_part_list']) ? $extra_conf['show_part_list'] = true : $extra_conf['show_part_list'] = false);
					(isset($_POST['privateChatEnabled']) ? $extra_conf['privateChatEnabled'] = true : $extra_conf['privateChatEnabled'] = false);
					(isset($_POST['publicChatEnabled']) ? $extra_conf['publicChatEnabled'] = true : $extra_conf['publicChatEnabled'] = false);
					(isset($_POST['screenShareEnabled']) ? $extra_conf['screenShareEnabled'] = true : $extra_conf['screenShareEnabled'] = false);
					//(isset($_POST['meeting_assistant_visibility']) ? $extra_conf['meeting_assistant_visibility'] = true : $extra_conf['meeting_assistant_visibility'] = false);
					(isset($_POST['autoAssignMikeOnJoin']) ? $extra_conf['autoAssignMikeOnJoin'] = true : $extra_conf['autoAssignMikeOnJoin'] = false);
					(isset($_POST['whiteboardEnabled']) ? $extra_conf['whiteboardEnabled'] = true : $extra_conf['whiteboardEnabled'] = false);
					(isset($_POST['enable_documents_sharing']) ? $extra_conf['documentSharingEnabled'] = true : $extra_conf['documentSharingEnabled'] = false);
					//(isset($_POST['enable_web_sharing']) ? $extra_conf['enable_web_sharing'] = true : $extra_conf['enable_web_sharing'] = false);
					(isset($_POST['recordingEnabled']) ? $extra_conf['recordingEnabled'] = true : $extra_conf['recordingEnabled'] = false);
					//(isset($_POST['allow_attendees_invitation']) ? $extra_conf['allow_attendees_invitation'] = true : $extra_conf['allow_attendees_invitation'] = false);
					(isset($_POST['autoHandsFreeOnAVLoad']) ? $extra_conf['autoHandsFreeOnAVLoad'] = true : $extra_conf['autoHandsFreeOnAVLoad'] = false);
					(isset($_POST['joinEmailRequired']) ? $extra_conf['joinEmailRequired'] = true : $extra_conf['joinEmailRequired'] = false);

					//$extra_conf['recording_code'] = Get::req('recording_code', DOTY_MIXED, '');

					$success = $bbb->insert_room($idConference,$user_email,$display_name,$confkey,$audiovideosettings,$maxmikes,$maxparticipants,
						$startdate,
						$starthour,
						$startminute,
						$meetinghours*60, //we need it in minutes for dimdim
						$extra_conf //Extra configuration added for the new dimdim API
					);
					if (!$success) {
						sql_query("DELETE FROM ".$this->_getRoomTable()." WHERE id=".(int)$idConference);
						$idConference = false;
					}
					break;

				case "dimdim":
					$acl_manager =& Docebo::user()->getAclManager();
					$dimdim = new DimDim_Manager();
					$display_name = Docebo::user()->getUserName();
					$u_info = $acl_manager->getUser(getLogUserId(), false);
					$user_email=$u_info[ACL_INFO_EMAIL];
					$confkey = $dimdim->generateConfKey();
					$audiovideosettings=1;
					$maxmikes=(int)Get::sett("dimdim_max_mikes");
					$extra_conf = array();
					(isset($_POST['lobbyEnabled']) ? $extra_conf['lobbyEnabled'] = true : $extra_conf['lobbyEnabled'] = false);
					//(isset($_POST['display_phone_info']) ? $extra_conf['display_phone_info'] = true : $extra_conf['display_phone_info'] = false);
					//(isset($_POST['show_part_list']) ? $extra_conf['show_part_list'] = true : $extra_conf['show_part_list'] = false);
					(isset($_POST['privateChatEnabled']) ? $extra_conf['privateChatEnabled'] = true : $extra_conf['privateChatEnabled'] = false);
					(isset($_POST['publicChatEnabled']) ? $extra_conf['publicChatEnabled'] = true : $extra_conf['publicChatEnabled'] = false);
					(isset($_POST['screenShareEnabled']) ? $extra_conf['screenShareEnabled'] = true : $extra_conf['screenShareEnabled'] = false);
					//(isset($_POST['meeting_assistant_visibility']) ? $extra_conf['meeting_assistant_visibility'] = true : $extra_conf['meeting_assistant_visibility'] = false);
					(isset($_POST['autoAssignMikeOnJoin']) ? $extra_conf['autoAssignMikeOnJoin'] = true : $extra_conf['autoAssignMikeOnJoin'] = false);
					(isset($_POST['whiteboardEnabled']) ? $extra_conf['whiteboardEnabled'] = true : $extra_conf['whiteboardEnabled'] = false);
					(isset($_POST['enable_documents_sharing']) ? $extra_conf['documentSharingEnabled'] = true : $extra_conf['documentSharingEnabled'] = false);
					//(isset($_POST['enable_web_sharing']) ? $extra_conf['enable_web_sharing'] = true : $extra_conf['enable_web_sharing'] = false);
					(isset($_POST['recordingEnabled']) ? $extra_conf['recordingEnabled'] = true : $extra_conf['recordingEnabled'] = false);
					//(isset($_POST['allow_attendees_invitation']) ? $extra_conf['allow_attendees_invitation'] = true : $extra_conf['allow_attendees_invitation'] = false);
					(isset($_POST['autoHandsFreeOnAVLoad']) ? $extra_conf['autoHandsFreeOnAVLoad'] = true : $extra_conf['autoHandsFreeOnAVLoad'] = false);
					(isset($_POST['joinEmailRequired']) ? $extra_conf['joinEmailRequired'] = true : $extra_conf['joinEmailRequired'] = false);

					//$extra_conf['recording_code'] = Get::req('recording_code', DOTY_MIXED, '');

					$success = $dimdim->insert_room($idConference,$user_email,$display_name,$confkey,$audiovideosettings,$maxmikes,$maxparticipants,
						$startdate,
						$starthour,
						$startminute,
						$meetinghours*60, //we need it in minutes for dimdim
						$extra_conf //Extra configuration added for the new dimdim API
					);
			if (!$success) {
				sql_query("DELETE FROM ".$this->_getRoomTable()." WHERE id=".(int)$idConference);
				$idConference = false;
			}
					break;

				case "teleskill":
					$start_date = date("Y-m-d H:i:s", $start_timestamp);
					$end_date = date("Y-m-d H:i:s", $end_timestamp);
					$teleskill = new Teleskill_Management();
					$re_creation_room=$teleskill->openRoom($idConference,$name, $start_date,$end_date, FALSE, FALSE,$maxparticipants);
					break;
			}
		}

		return $idConference;
	}

	function updateRoom()
	{

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

		switch ($conference["room_type"]) {
			case "dimdim":
				$dimdim=new DimDim_Manager();
				$dimdim->deleteRoom($room_id);
				break;

			case "bbb":
				$bbb=new Bbb_Manager();
				$bbb->deleteRoom($room_id);
				break;

			case "teleskill":
				$teleskill = new Teleskill_Management();
				$teleskill->deleteRemoteRoom($room_id);
				break;
		}
		return $re_room;
	}

	function getUrl($idConference,$room_type) {
		$conference = $this->roomInfo($idConference);

		switch($room_type) {
			case "bbb":
// TODO : support for BBB is experimental - must be refined
				$bbb=new Bbb_Manager();
				$url=$bbb->getUrl($idConference,$room_type);
				break;

			case "dimdim":
				$dimdim=new DimDim_Manager();
				$url=$dimdim->getUrl($idConference,$room_type);
				break;

				case "teleskill":
				$teleskill = new Teleskill_Management();
				$url=$teleskill->getUrl($idConference,$room_type);
				break;
		}

		return $url;
	}

	function can_create_user_limit($idSt,$idCourse,$start_timestamp) {
		$ok=true;

		if ($this->creation_limit_per_user) {
			$query="SELECT * FROM  ".$this->_getRoomTable().
			" WHERE idSt='$idSt' AND idCourse='$idCourse' AND starttime=>'$start_timestamp'";
			$re_room=$this->_query($query);
			$p=mysql_error();
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