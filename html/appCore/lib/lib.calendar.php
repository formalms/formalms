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
 
class DoceboCal {
	
	var $lastId;
	
	function getEvents($year=0,$month=0,$day=0,$start_date="",$end_date="",$category="",$type="",$owner="") {
		
		$where="";
		
		if (!$month and !$year and empty($start_date) and empty($end_date)) {
			$today=getdate();
			$month=$today['mon'];
			$year=$today['year'];
		}
		
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
		
		}
		
		if (!empty($start_date) and !empty($end_date)) {
			if ($where) $where.=" AND ";
			$where.="start_date>='".$start_date."' AND start_date<='".$end_date."'";
		}
		
		
		if (!empty($category)) {
			if ($where) $where.=" AND ";
			$where.="category='".$category."'";
		}
		
		if (!empty($type)) {
			if ($where) $where.=" AND ";
			$where.="type='".$type."'";
		}
		
		if (!empty($owner)) {
			if ($where) $where.=" AND ";
			$where.="_owner='".$owner."'";
		}
		
		
		$query="SELECT * FROM ".$GLOBALS['prefix_fw']."_calendar WHERE ".$where." ORDER BY start_date";
		$result=sql_query($query);
		if (sql_error()) die(sql_error()."<br>".$query);
		$calevents = array();
		$i=0;
		while ($row=sql_fetch_array($result)) {
			$calevents[$i]=new CalEvent();
			
			$calevents[$i]->id=$row["id"];

            preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["start_date"],$parts);
			$calevents[$i]->start->year=$parts[1];
			$calevents[$i]->start->month=$parts[2];
			$calevents[$i]->start->day=$parts[3];
			$calevents[$i]->start->hour=$parts[4];
			$calevents[$i]->start->min=$parts[5];
			$calevents[$i]->start->sec=$parts[6];

            preg_match("^(.+)-(.+)-(.+) (.+):(.+):(.+)$",$row["end_date"],$parts);
			$calevents[$i]->end->year=$parts[1];
			$calevents[$i]->end->month=$parts[2];
			$calevents[$i]->end->day=$parts[3];
			$calevents[$i]->end->hour=$parts[4];
			$calevents[$i]->end->min=$parts[5];
			$calevents[$i]->end->sec=$parts[6];
			
			$calevents[$i]->title=$row["title"];
			$calevents[$i]->description=$row["description"];
			$calevents[$i]->category=$row["category"];
			$calevents[$i]->type=$row["type"];
			$calevents[$i]->private=$row["private"];
			$calevents[$i]->visibility_rules=$row["visibility_rules"];
			
			$calevents[$i]->_year=$row["_year"];
			$calevents[$i]->_month=$row["_month"];
			$calevents[$i]->_day=$row["_day"];
			$calevents[$i]->_owner=$row["_owner"];

			$i++;
		}
		
		return $calevents;
	}
	
	function setEvent($event) {
	
		$start_date=$event->start->year."-".$event->start->month."-".$event->start->day." ".$event->start->hour.":".$event->start->min.":".$event->start->sec;
		
		$end_date=$event->end->year."-".$event->end->month."-".$event->end->day." ".$event->end->hour.":".$event->end->min.":".$event->end->sec;
	
		if (!$event->id) {
			$query="INSERT INTO ".$GLOBALS['prefix_fw']."_calendar SET create_date=NOW(),";
		} else {
			$query="UPDATE ".$GLOBALS['prefix_fw']."_calendar SET ";
		};
		
		$query.="start_date='".$start_date."',";
		$query.="end_date='".$end_date."',";
		$query.="title='".$event->title."',";
		$query.="description='".$event->description."',";
		$query.="category='".$event->category."',";
		$query.="type='".$event->type."',";
		$query.="private='".$event->private."',";
		$query.="visibility_rules='".$event->visibility_rules."',";
		$query.="_year='".$event->_year."',";
		$query.="_month='".$event->_month."',";
		$query.="_day='".$event->_day."',";
		$query.="_owner='".$event->_owner."'";

		if ($event->id) $query.=" WHERE id='".$event->id."'";
		
		$result=sql_query($query);
		if (sql_error()) die(sql_error()."<br />".$query);
		
		if (!$event->id) $this->lastId=sql_insert_id();
	}
	
	function getLastId() {
		return sql_insert_id();
	}
	
	function delEvent($id) {
		$query="DELETE FROM ".$GLOBALS['prefix_fw']."_calendar WHERE id='".$id."'";
		$result=sql_query($query);
	}
	
}


class evDate {
	var $year;
	var $month;
	var $day;
	var $hour;
	var $min;
	var $sec;
}

class CalEvent {
	var $id;
	var $create;
	var $start;
	var $end;
	var $title;
	var $description;
	var $category;
	var $type;
	var $private;
	var $visibility_rules;
	
	var $_year;
	var $_month;
	var $_day;
	var $_owner;


	function CalEvent() {
		$this->start = new evDate();
		$this->end = new evDate();
	}
}

?>