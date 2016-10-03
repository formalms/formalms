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

class DoceboCalEvent_lms_classroom extends DoceboCalEvent_core {


	var $idCourse;

	function assignVar() {
		$this->id=importVar("id");
		$this->calEventClass=importVar("calEventClass");
		$this->start_year=importVar("start_year");
		$this->start_month=importVar("start_month");
		$this->start_day=importVar("start_day");

		$this->_year=$this->start_year;
		$this->_month=$this->start_month;
		$this->_day=$this->start_day;

		$this->start_hour=importVar("start_hour");
		$this->start_min=importVar("start_min");
		$this->start_sec=importVar("start_sec");
		$this->end_year=importVar("end_year");
		$this->end_month=importVar("end_month");
		$this->end_day=importVar("end_day");
		$this->end_hour=importVar("end_hour");
		$this->end_min=importVar("end_min");
		$this->end_sec=importVar("end_sec");

		$this->title=importVar("title");
		$this->description=importVar("description");
		$this->classroom=importVar("classroom");

		$this->_owner=importVar("_owner");
		if (!$this->_owner) $this->_owner==Docebo::user()->getIdSt();

		$this->category=importVar("category");
		$this->idCourse=(isset($_SESSION["idCourse"]) ? $_SESSION["idCourse"] : 0 );
	}

	function getForm() {

		require_once($GLOBALS["where_lms"]."/lib/lib.classroom.php");
		$cm=new ClassroomManager();

		$class_arr=$cm->getClassroomArray();
		$class_list="[".implode(",", addSurroundingQuotes($class_arr, '"'))."]";
		$class_keys="[".implode(",", addSurroundingQuotes(array_keys($class_arr), '"'))."]";


		$form_obj='{
		"form":[
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_START"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"day","id":"start_day"},
			{"type":"string","value":"/"},
			{"type":"month","id":"start_month"},
			{"type":"string","value":"/"},
			{"type":"year","id":"start_year"},
			{"type":"string","value":"&nbsp;"},
			{"type":"hour","id":"start_hour"},
			{"type":"string","value":":"},
			{"type":"min","id":"start_min"},
			{"type":"string","value":":"},
			{"type":"sec","id":"start_sec"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_END"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"day","id":"end_day"},
			{"type":"string","value":"/"},
			{"type":"month","id":"end_month"},
			{"type":"string","value":"/"},
			{"type":"year","id":"end_year"},
			{"type":"string","value":"&nbsp;"},
			{"type":"hour","id":"end_hour"},
			{"type":"string","value":":"},
			{"type":"min","id":"end_min"},
			{"type":"string","value":":"},
			{"type":"sec","id":"end_sec"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_SUBJECT"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"text","id":"title","style":"width:300px"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"'. Lang::t("_CLASSROOM", "admin_classroom", "lms").'","translatevalue":"0"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},	{"type":"select","id":"classroom","value":'.$class_list.',"key":'.$class_keys.',"translatevalue":"0"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"},
			{"type":"structure","value":"row"},
			{"type":"structure","value":"cell","field_class":"label"},
			{"type":"label","value":"_DESCR"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"cell","field_class":"field"},
			{"type":"textarea","id":"description"},
			{"type":"structure","value":"/cell"},
			{"type":"structure","value":"/row"}
		]

		}';

		return $form_obj;
	}

	function store() {
		if ($this->getPerm()) {
			
			$start_date=$this->start_year."-".$this->start_month."-".$this->start_day." ".$this->start_hour.":".$this->start_min.":".$this->start_sec;
			$end_date=$this->end_year."-".$this->end_month."-".$this->end_day." ".$this->end_hour.":".$this->end_min.":".$this->end_sec;

			if ($this->id > 0) {

				$action_add=FALSE;

				$table =$GLOBALS['prefix_lms']."_classroom_calendar";
				$qtxt ="SELECT start_date, end_date FROM ".$table." ";
				$qtxt.="WHERE id='".(int)$this->id."'";

				$q=sql_query($qtxt);

				list($old_start_date, $old_end_date)=sql_fetch_row($q);

				$query="UPDATE ".$GLOBALS['prefix_lms']."_classroom_calendar SET ";
			}
			else {

				$action_add=TRUE;

				$old_start_date=$start_date;
				$old_end_date=$end_date;

				$query="INSERT INTO ".$GLOBALS['prefix_lms']."_classroom_calendar SET owner='".Docebo::user()->getIdSt()."',";
			}

			$query.="start_date='".$start_date."',";
			$query.="end_date='".$end_date."',";
			$query.="classroom_id='".(int)$this->classroom."',";
			$query.="title='".$this->title."',";
			$query.="description='".$this->description."' ";

			if ($this->id > 0)
				$query.=" WHERE id='".$this->id."'";
			
			$q=sql_query($query);
			if (sql_error()) die(sql_error()."<br />".$query);


			if ($q) {
				if ($action_add) {
					$this->id = sql_insert_id();
				}


				// -- timetable setup ------------------------------------------------
				require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
				$tt=new TimeTable();

				$resource="classroom";
				$resource_id=(int)$this->classroom;
				$consumer="classroom_event";
				$consumer_id=$this->id;
				// -------------------------------------------------------------------


				$save_ok=$tt->saveEvent(FALSE, $start_date, $end_date, $old_start_date, $old_end_date, $resource, $resource_id, $consumer, $consumer_id);

				if (!$save_ok) {
					// Not very optimized but we're late :P
					$query="DELETE FROM ".$GLOBALS['prefix_lms']."_classroom_calendar WHERE id='".$this->id."'";
					$q=sql_query($query);
					//$this->id=0;
					return false;
				}

			} else {
				//$this->id=0;
			}

			return $this->id;
		} else {
			return 0;
		}
	}

	function del() {
		if ($this->getPerm()) {

			$start_date=$this->start_year."-".$this->start_month."-".$this->start_day." ".$this->start_hour.":".$this->start_min.":".$this->start_sec;
			$end_date=$this->end_year."-".$this->end_month."-".$this->end_day." ".$this->end_hour.":".$this->end_min.":".$this->end_sec;

			// -- timetable setup ------------------------------------------------
			require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
			$tt=new TimeTable();

			$resource="classroom";
			$resource_id=(int)$this->classroom;
			$consumer="classroom_event";
			$consumer_id=$this->id;
			// -------------------------------------------------------------------


			$delete_ok=$tt->deleteEvent(FALSE, $resource, $resource_id, $consumer, $consumer_id, $start_date, $end_date);

			if ($delete_ok) {
				$query="DELETE FROM ".$GLOBALS['prefix_lms']."_classroom_calendar WHERE id='".$this->id."'";
				$q=sql_query($query);

				$this->id=0;
			}
		};
	}

	function getPerm() {

		$permissions=2;


		if ($permissions==2) return 1;
		if ($permissions==1 and Docebo::user()->getIdSt()==$this->_owner) return 1;

		return 0;

	}
}
?>