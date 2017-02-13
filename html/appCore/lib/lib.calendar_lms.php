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

class DoceboCal_lms extends DoceboCal_core{

	function getEvents($year=0,$month=0,$day=0,$start_date="",$end_date="") {
	
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
		
		
		$query="SELECT a.* FROM ".$GLOBALS['prefix_fw']."_calendar AS a,".$GLOBALS['prefix_lms']."_calendar AS b WHERE a.id=b.id AND (a.private<>'on' OR (a.private='on' AND a._owner='".Docebo::user()->getIdSt()."')) AND b.idCourse='".$_SESSION['idCourse']."' AND ".$where." ORDER BY start_date";

		$result=sql_query($query);
		//return sql_num_rows($result);
		$calevents = array();
		$i=0;
		while ($row=sql_fetch_array($result)) {
		
			/* you should call the constructor of the proper type of event class*/
			$calevents[$i]=new DoceboCalEvent_lms();
			$calevents[$i]->calEventClass="lms";
	
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
			
			$calevents[$i]->title=htmlentities($row["title"]);
			$calevents[$i]->description=htmlentities($row["description"]);
			$calevents[$i]->category=$row["category"];
			$calevents[$i]->type=$row["type"];
			$calevents[$i]->private=$row["private"];
			$calevents[$i]->visibility_rules=$row["visibility_rules"];
			
			$calevents[$i]->_year=$row["_year"];
			$calevents[$i]->_month=$row["_month"];
			$calevents[$i]->_day=$row["_day"];
			$calevents[$i]->_owner=$row["_owner"];
			
			$calevents[$i]->editable=$calevents[$i]->getPerm();
			$calevents[$i]->idCourse=$row["idCourse"];
			/*----------------------------------------------------------*/
			
			$i++;
		}
		
		return $calevents;
	}
	
}

?>