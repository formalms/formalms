<?php

defined("IN_FORMA") or die('Direct access is forbidden.');

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
 * The language model class
 *
 * This Model is used to retrieve and manipulate all kind of
 * information about the classrooms and their locations (add-edit-delete).
 * @since 4.0
 */
class LocationAlms extends Model {

	protected $db;

	public function __construct() {

		$this->db = DbConn::getInstance();
	}

	/**
	 * Retrun the permission list for this module
	 * @return array
	 */
	public function getPerm() {
		return array(
			'view' => 'standard/view.png',
			'mod' => 'standard/edit.png'
		);
	}

	public function getLocationList($startIndex = false, $results = false, $sort = false, $dir = false, $filter = false) {

		$query = "SELECT location_id AS id_location, location "
				. " FROM  %lms_class_location "
				. " WHERE 1 ";
		if ($filter != "") $query .= " AND location LIKE '%".$filter."%'";
		if ($sort && $dir)
			$query .= " ORDER BY $sort $dir ";
		if ($startIndex && $results)
			$query .= " LIMIT " . (int) $startIndex . ", " . (int) $results;

		$rs = $this->db->query($query);
		$result = array();
		while ($location = $this->db->fetch_obj($rs)) {
			$result[] = $location;
		}

		return $result;
	}

	public function getLocationTotal($filter = false) {

		$query = "SELECT COUNT(*) "
				. " FROM %lms_class_location "
				. " WHERE 1 ";
		if ($filter != "") $query .= " AND location LIKE '%".$filter."%'";
		if (!$rs = $this->db->query($query))
			return 0;
		list($tot) = $this->db->fetch_row($rs);
		return $tot;
	}

	public function getLocationAll() {
		$query = "SELECT location_id FROM %lms_class_location";
		$rs = $this->db->query($query);
		if (!$rs) return false;
		$output = array();
		while (list($id_location) = $this->db->fetch_row($rs)) {
			$output[] = (int)$id_location;
		}
		return $output;
	}

	public function insertLocation($location) {

		$query = "INSERT INTO %lms_class_location "
				. " (location) VALUES ("
				. " '" . $location . "' "
				. ")";
		if (!$res = $this->db->query($query))
			return false;

		if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			$id_entry = sql_insert_id();

			$re = sql_query("
				INSERT INTO ".$GLOBALS['prefix_fw']."_admin_course 
				( id_entry, type_of_entry, idst_user ) VALUES 
				( '".$id_entry."', 'classlocation', '".getLogUserId()."') ");
		}

		return true;
	}

	public function delLocation($location) {

		$query = "DELETE FROM %lms_class_location WHERE location_id = '" . $location . "' ";
		if (!$this->db->query($query))
			return false;
		return true;
	}

	public function getLocation($location) {
		$query = "SELECT * "
				. " FROM %lms_class_location "
				. " WHERE location_id = '" . $location . "'";
		$rs = $this->db->query($query);
		return $this->db->fetch_obj($rs);
	}

	public function updateLocation($location_id, $location_new) {

		$query = " update %lms_class_location ";
		$query .= " set location = '" . $location_new . "' ";
		$query .= " WHERE location_id = " . (int) $location_id . " ";

		if (!$this->db->query($query))
			return false;
		return true;
	}

	public function getClassroomList($id_location, $startIndex, $results, $sort = false, $dir = false, $filter = false) {

		$query = "SELECT lc.name, lc.idClassroom as id_classroom ";
		$query .= " FROM %lms_classroom lc JOIN %lms_class_location lcl ";
		$query .= " ON lc.location_id = lcl.location_id ";
		$query .= " AND lc.location_id = " . (int) $id_location . "";
		if ($sort && $dir)
			$query .= " ORDER BY $sort $dir ";
		else
			$query .= " ORDER BY lc.name";

		$rs = $this->db->query($query);
		$result = array();

		while ($classroom = $this->db->fetch_obj($rs)) {
			$result[] = $classroom;
		}
		return $result;
	}

	public function getClassroomTotal($id_location, $filter = false) {

		$query = "SELECT count(*) "
				. " FROM %lms_classroom lc join %lms_class_location lcl on lc.location_id = lcl.location_id "
				. " and lc.location_id = " . (int) $id_location;

		if (!$rs = $this->db->query($query))
			return 0;
		list($tot) = $this->db->fetch_row($rs);
		return $tot;
	}

	public function getLocationName($id_location) {
		$query = "SELECT location FROM %lms_class_location WHERE location_id = " . (int) $id_location;
		$res = $this->db->query($query);
		$output = "";
		if ($res && $this->db->num_rows($res) > 0) {
			list($output) = $this->db->fetch_row($res);
		}
		return $output;
	}

	public function getLocationClassrooms($id_location) {

		$query = "SELECT lc.idClassroom, lc.name "
				. " FROM %lms_classroom lc JOIN %lms_class_location lcl "
				. " ON lc.location_id = lcl.location_id "
				. " AND lc.location_id = " . (int) $id_location
				. " ORDER BY lc.name";

		$rs = $this->db->query($query);
		$result = array();
		while ($classroom = $this->db->fetch_obj($rs)) {
			$result[$classroom->idClassroom] = $classroom->name;
		}
		return $result;
	}

	public function delClassroom($classroom_id) {

		$query = "DELETE FROM %lms_classroom WHERE idClassroom = '" . $classroom_id . "' ";
		if (!$this->db->query($query))
			return false;
		return true;
	}

	public function getClassroomDetails($id_classroom) {

		// estrarre tutti i dettagli della classe che recupero tramite l'idclassroom

		$query_details =
				"SELECT name, description , location_id , room , street, city, state , zip_code,
				phone,fax, capacity, disposition, instrument, available_instrument,note,responsable, idClassroom
			FROM %lms_classroom WHERE idClassroom = " . (int) $id_classroom . "";

		$rs = $this->db->query($query_details);
		$classroom = $this->db->fetch_obj($rs);
		$result = $classroom;
		return $result;
	}

	public function UpdateClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable, $idClassroom) {

		$query_update = "
		UPDATE %lms_classroom
		SET	name = '" . $name . "' ,
			description = '" . $description . "',
			location_id = " . $id_location . ",
			room = '" . $room . "',
			street = '" . $street . "',
			city = '" . $city . "',
			state = '" . $state . "' ,
			zip_code = '" . $zip_code . "' ,
			phone = '" . $phone . "' ,
			fax = '" . $fax . "' ,
			capacity = '" . $capacity . "' ,
			disposition = '" . $disposition . "' ,
			instrument = '" . $instrument . "' ,
			available_instrument = '" . $available_instrument . "' ,
			note = '" . $note . "' ,
			responsable = '" . $responsable . "'
			WHERE idClassroom = " . $idClassroom . "";

		if (!sql_query($query_update))
			return false;
		Util::jump_to('index.php?r=alms/location/show_classroom&amp;id_location=' . $id_location . '');
	}

	public function InsertClassroomMod($name, $description, $id_location, $room, $street, $city, $state, $zip_code, $phone, $fax, $capacity, $disposition, $instrument, $available_instrument, $note, $responsable) {

		$query_insert = "
		INSERT INTO %lms_classroom
		(name, description , location_id , room , street, city, state , zip_code,
		phone,fax, capacity, disposition, instrument, available_instrument,note,responsable) VALUES
		( 	'" . $name . "' ,
			'" . $description . "',
			" . $id_location . ",
			'" . $room . "',
			'" . $street . "',
			'" . $city . "',
			'" . $state . "',
			'" . $zip_code . "',
			'" . $phone . "',
			'" . $fax . "',
			'" . $capacity . "',
			'" . $disposition . "',
			'" . $instrument . "',
			'" . $available_instrument . "',
			'" . $note . "',
			'" . $responsable . "'
			)";
		if (!sql_query($query_insert))
			return false;
		Util::jump_to('index.php?r=alms/location/show_classroom&amp;id_location=' . $id_location . '');
	}

	public function getClassroomDates($id_classroom) {

		$query_dates =
				"SELECT substring(lcdd.date_begin,1,10) as date," . (int) $id_classroom . " as idClassroom
			FROM %lms_course_date as lcd JOIN %lms_course_date_day as lcdd ON lcd.id_date = lcdd.id_date AND lcdd.Classroom = " . (int) $id_classroom . "";

		

		$rs = $this->db->query($query_dates);
		$classroom_num = $this->db->num_rows($rs);

		if ($classroom_num == 0) return false;
		else {
		while ($classroom = $this->db->fetch_obj($rs)) {
			$result[] = $classroom->date;
		}
		 return $result;
		}
		
	}


		public function getClassroomDates2($id_classroom, $startIndex, $results, $sort = false, $dir = false, $filter = false) {

		// estrarre tutti i dettagli della classe che recupero tramite l'idclassroom
		$sort = $this->clean_sort($sort, array('date'));
		$dir = $this->clean_dir($dir);
		
		$query_dates =
				"SELECT substring(lcdd.date_begin,1,10) as date, lcd.name as name," . (int) $id_classroom . " as idClassroom
			FROM %lms_course_date as lcd JOIN %lms_course_date_day as lcdd ON lcd.id_date = lcdd.id_date AND lcdd.Classroom = " . (int) $id_classroom . "";
				if ($sort && $dir)
		$query_dates .= " ORDER BY $sort $dir ";



		$rs = $this->db->query($query_dates);

		$classroom_num = $this->db->num_rows($rs);

		$result = array();
		while ($date = $this->db->fetch_obj($rs)) {
			$result[] = $date;
		}
		return ($classroom_num > 0) ?  $result : false;

	}


	public function getClassroomDateTotal($id_classroom) {

		$query_total_dates =
				"SELECT count(lcdd.date_begin) as dates
			FROM %lms_course_date as lcd JOIN %lms_course_date_day as lcdd ON lcd.id_date = lcdd.id_date AND lcdd.Classroom = " . (int) $id_classroom . "";
		if (!$rs = $this->db->query($query_total_dates))
			return 0;
		list($tot) = $this->db->fetch_row($rs);
		return $tot;
	}

	public function getClassroomDates2Date($id_classroom, $date, $startIndex, $results, $sort = false, $dir = false, $filter = false) {


		
		$sort = $this->clean_sort($sort, array('date'));
		$dir = $this->clean_dir($dir);

		$position = stripos($date,"-");
		
		if ($position == 1) $date_in = substr($date,2,5)."-0".substr($date,0,1) ;
		else $date_in = substr($date,3,5)."-".substr($date,0,2) ;


		if ($position == 1) $date1_month= substr($date,0,1);
		else $date1_month= substr($date,0,2);
		if ($position == 1) $date1_year= substr($date,2,5);
		else $date1_year= substr($date,3,5);

		if (($date1_month > 0) && ($date1_month < 10))  $date1_month = "0".$date1_month;

		if ($date1_month == '00') {$date1_month = 12; $date1_year--;  }

		$date_in = $date1_year."-".$date1_month;

		if ($position == 1) $date2_month= substr($date,0,1);
		else $date2_month= substr($date,0,2);
		$date2_month++;
		if ($position == 1) $date2_year= substr($date,2,5);
		else $date2_year= substr($date,3,5);
		if ($date2_month == 13) { $date2_month = 1 ; $date2_year++; }
		if (($date2_month > 0) && ($date2_month < 10))  $date2_month = "0".$date2_month;
		$date2_in = $date2_year."-".$date2_month;

		$date3_year = $date2_year;
		$date3_month = $date2_month;
		$date3_month++;
		if ($date3_month == 13) { $date3_month = 1 ; $date3_year++; }
		if (($date3_month > 0) && ($date3_month < 10))  $date3_month = "0".$date3_month;
		$date3_in = $date3_year."-".$date3_month;

		$query_dates =
				"SELECT substring(lcdd.date_begin,1,10) as date, lcd.name as name," . (int) $id_classroom . " as idClassroom
			FROM %lms_course_date as lcd JOIN %lms_course_date_day as lcdd ON lcd.id_date = lcdd.id_date AND lcdd.Classroom = " . (int) $id_classroom . "
				AND ( (substring(lcdd.date_begin,1,7) like '".$date_in."%') OR (substring(lcdd.date_begin,1,7) like '".$date2_in."%') OR (substring(lcdd.date_begin,1,7) like '".$date3_in."%'))     ";
		if ($sort && $dir)
			$query_dates .= " ORDER BY $sort $dir ";


		$rs = $this->db->query($query_dates);

		$classroom_num = $this->db->num_rows($rs);

		$result = array();
		while ($date = $this->db->fetch_obj($rs)) {
			$result[] = $date;
		}
		return ($classroom_num > 0) ?  $result : false;

	}


	public function getClassroomDateTotalDate($id_classroom,$date) {

		$position = stripos($date,"-");

		if ($position == 1) $date_in = substr($date,2,5)."-0".substr($date,0,1) ;
		else $date_in = substr($date,3,5)."-".substr($date,0,2) ;


		if ($position == 1) $date1_month= substr($date,0,1);
		else $date1_month= substr($date,0,2);
		if ($position == 1) $date1_year= substr($date,2,5);
		else $date1_year= substr($date,3,5);

		if (($date1_month > 0) && ($date1_month < 10))  $date1_month = "0".$date1_month;

		if ($date1_month == '00') {$date1_month = 12; $date1_year--;  }

		$date_in = $date1_year."-".$date1_month;

		if ($position == 1) $date2_month= substr($date,0,1);
		else $date2_month= substr($date,0,2);
		$date2_month++;
		if ($position == 1) $date2_year= substr($date,2,5);
		else $date2_year= substr($date,3,5);
		if ($date2_month == 13) { $date2_month = 1 ; $date2_year++; }
		if (($date2_month > 0) && ($date2_month < 10))  $date2_month = "0".$date2_month;
		$date2_in = $date2_year."-".$date2_month;

		$date3_year = $date2_year;
		$date3_month = $date2_month;
		$date3_month++;
		if ($date3_month == 13) { $date3_month = 1 ; $date3_year++; }
		if (($date3_month > 0) && ($date3_month < 10))  $date3_month = "0".$date3_month;
		$date3_in = $date3_year."-".$date3_month;


		$query_total_dates =
				"SELECT count(lcdd.date_begin) as dates
			FROM %lms_course_date as lcd JOIN %lms_course_date_day as lcdd ON lcd.id_date = lcdd.id_date AND lcdd.Classroom = " . (int) $id_classroom . "
				AND ( (substring(lcdd.date_begin,1,7) like '".$date_in."%') OR (substring(lcdd.date_begin,1,7) like '".$date2_in."%') OR (substring(lcdd.date_begin,1,7) like '".$date3_in."%'))";


		if (!$rs = $this->db->query($query_total_dates))
			return 0;
		list($tot) = $this->db->fetch_row($rs);
		return $tot;
	}


	

}