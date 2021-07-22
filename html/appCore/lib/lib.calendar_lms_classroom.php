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
 * @package  admin-library
 * @subpackage calendar
 * @version  $Id:$
 */
 
require_once($GLOBALS['where_framework']."/lib/lib.calevent_lms.php");
require_once($GLOBALS['where_framework']."/lib/resources/lib.timetable.php");

class DoceboCal_lms_classroom extends DoceboCal_core{

	var $calClass="lms_classroom";

	function getEvents($year=0,$month=0,$day=0,$start_date="",$end_date="",$classroom="") {

		$where="";

		if (!$month and !$year and empty($start_date) and empty($end_date)) {
			$today=getdate();
			$month=$today['mon'];
			$year=$today['year'];
		}
		
		if ($classroom) {
			if ($where) $where.=" AND ";
			$where.="classroom_id='".$classroom."'";
		
		}
/*
		if ($day and empty($start_date) and empty($end_date)) {
			if ($where) $where.=" AND ";
			$where.="_day='".$day."'";

		}

		if ($month and empty($start_date) and empty($end_date)) {
			if ($where) $where.=" AND ";
			$where.="_month='".$month."'";

		}

		if ($year and empty($start_date) and empty($end_date)) {
			if ($where) $where.=" AND ";
			$where.="_year='".$year."'";

		} */

		if (!empty($start_date) and !empty($end_date)) {
			if (!empty($where)) $where.=" AND ";
			$where.="start_date>='".$start_date."' AND start_date<='".$end_date."'";
		}

		$tt=new TimeTable();
		$consumer_filter=array("course", "course_edition");
		$entries=$tt->getResourceEntries("classroom", FALSE, $start_date, $end_date, $consumer_filter);

		//return sql_num_rows($result);
		$calevents = array();
		$i=0;
		$parts=array();
		$loaded=array();
		foreach($entries as $row) {

			$loaded[$row["consumer"]][$i]=$row["resource_id"];

			/* you should call the constructor of the proper type of event class*/
			$calevents[$i]=new DoceboCalEvent_lms();
			$calevents[$i]->calEventClass=$this->calClass;

			/* the following should be set according to the type of event class*/
			$calevents[$i]->id=$row["id"];
            preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["start_date"],$parts);
			$calevents[$i]->start_year=$parts[1];
			$calevents[$i]->start_month=$parts[2];
			$calevents[$i]->start_day=$parts[3];
			$calevents[$i]->start_hour=$parts[4];
			$calevents[$i]->start_min=$parts[5];
			$calevents[$i]->start_sec=$parts[6];

            preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["end_date"],$parts);
			$calevents[$i]->end_year=$parts[1];
			$calevents[$i]->end_month=$parts[2];
			$calevents[$i]->end_day=$parts[3];
			$calevents[$i]->end_hour=$parts[4];
			$calevents[$i]->end_min=$parts[5];
			$calevents[$i]->end_sec=$parts[6];

/*			$calevents[$i]->_year=$row["_year"];
			$calevents[$i]->_month=$row["_month"];
			$calevents[$i]->_day=$row["_day"]; */
			$calevents[$i]->owner=$row["owner"];

			$calevents[$i]->editable=0;

			/*----------------------------------------------------------*/

			$i++;
		}


		// --- Loading additional information ---------------------------------

		// ------------------------- - -  -   -
		// We first grab edition information so we also find the course id of the
		// editions to later load the related course information too.
		// ------------------------- - -  -   -
		$qtxt ="SELECT * FROM ".$GLOBALS["prefix_lms"]."_course_edition ";
		$qtxt.="WHERE idCourseEdition IN (".implode(",", $loaded["course_edition"]).") ";

		$q=sql_query($qtxt);

		$edition_info=array();
		$calevents_keys=array_flip($loaded["course_edition"]);
		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {

				$id=$row["idCourseEdition"];
				$course_id=$row["idCourse"];
				$edition_info[$id]=$row;

				if (!in_array($course_id, $loaded["course"])) {
					$key=$calevents_keys[$id];
					$loaded["course"][$key]=$course_id;
				}
			}
		}

		// ------------------------- - -  -   -
		// Then we grab the course information too
		// ------------------------- - -  -   -
		$qtxt ="SELECT * FROM ".$GLOBALS["prefix_lms"]."_course ";
		$qtxt.="WHERE idCourse IN (".implode(",", $loaded["course"]).") ";

		$q=sql_query($qtxt);

		$course_info=array();
		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {

				$id=$row["idCourse"];
				$course_info[$id]=$row;
			}
		}

		// ------------------------- - -  -   -
		// And once we have them we setup the calendar information using the
		// key value of the $info array that guess what.. is the same of the
		// calevents array! :D
		// ------------------------- - -  -   -
		foreach($loaded as $consumer=>$info) {
			foreach($info as $calevents_key=>$id) {

				switch($consumer) {
					case "course": {
						$title=$course_info[$id]["name"];
						$description="";
					} break;

					case "course_edition": {
						$course_id=$edition_info[$id]["idCourse"];
						$title=$course_info[$course_id]["name"];
						$description=$edition_info[$id]["name"];
					} break;
				}

				$calevents[$calevents_key]->title=$title;
				$calevents[$calevents_key]->description=$description;
			}
		}
		// --------------------------------------------------------------------


		$qtxt ="SELECT * FROM ".$GLOBALS['prefix_lms']."_classroom_calendar";
		$qtxt.=(!empty($where) ? " WHERE ".$where : "");
		$q=sql_query($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {

				/* you should call the constructor of the proper type of event class*/
				$calevents[$i]=new DoceboCalEvent_lms();
				$calevents[$i]->calEventClass=$this->calClass;

				/* the following should be set according to the type of event class*/
				$calevents[$i]->id=$row["id"];
                preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["start_date"],$parts);
				$calevents[$i]->start_year=$parts[1];
				$calevents[$i]->start_month=$parts[2];
				$calevents[$i]->start_day=$parts[3];
				$calevents[$i]->start_hour=$parts[4];
				$calevents[$i]->start_min=$parts[5];
				$calevents[$i]->start_sec=$parts[6];

                preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["end_date"],$parts);
				$calevents[$i]->end_year=$parts[1];
				$calevents[$i]->end_month=$parts[2];
				$calevents[$i]->end_day=$parts[3];
				$calevents[$i]->end_hour=$parts[4];
				$calevents[$i]->end_min=$parts[5];
				$calevents[$i]->end_sec=$parts[6];

				$calevents[$i]->classroom=$row["classroom_id"];
				$calevents[$i]->title=$row["title"];
				$calevents[$i]->description=$row["description"];
				$calevents[$i]->owner=$row["owner"];


				$calevents[$i]->editable=1;

				/*----------------------------------------------------------*/

				$i++;
			}
		}


		return $calevents;
	}

}

?>