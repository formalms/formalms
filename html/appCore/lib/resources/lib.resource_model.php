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
 * @package admin-core
 * @subpackage resource
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

class ResourceModel {

	var $prefix=NULL;
	var $dbconn=NULL;
	var $timetable_table=FALSE;

	var $resource_code=FALSE;
	var $allowed_simultaneously=1;


	function ResourceModel($prefix=FALSE, $dbconn=NULL) {
		$this->prefix=($prefix !== false ? $prefix : $GLOBALS["prefix_fw"]);
		$this->dbconn=$dbconn;
	}


	function _query( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}


	function _insQuery( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) )
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return sql_insert_id();
		else
			return sql_insert_id($this->dbconn);
	}


	function setTimeTableTable($table) {
		$this->timetable_table=$table;
	}


	function getTimeTableTable() {
		if ($this->timetable_table === FALSE)
			return $this->prefix."_resource_timetable";
		else
			return $this->timetable_table;
	}


	function setResourceCode($code) {
		$this->resource_code=$code;
	}


	function getResourceCode() {
		if ($this->resource_code !== FALSE)
			return $this->resource_code;
		else
			return "0";
	}


	function setAllowedSimultaneously($max) {
		$this->allowed_simultaneously=$max;
	}


	function getAllowedSimultaneously() {
		return $this->allowed_simultaneously;
	}


	function getResourceEntries($resource_id=FALSE, $start_date=FALSE, $end_date=FALSE, $consumer_filter=FALSE) {
		$res=array();

		$qtxt ="SELECT * FROM ".$this->getTimeTableTable()." WHERE ";
		$qtxt.="resource='".$this->getResourceCode()."'";
		$qtxt.=($resource_id !== FALSE ? " AND resource_id='".$resource_id."'" : "");

		if (($consumer_filter !== FALSE) && (is_array($consumer_filter)) && (count($consumer_filter) > 0)) {
			$consumer_filter=addSurroundingQuotes($consumer_filter);
			$qtxt.=" AND consumer IN (".implode(",", $consumer_filter).")";
		}

		$where_start_date=" AND (start_date >= '".$start_date."' OR start_date IS NULL)";
		$qtxt.=($start_date !== FALSE ? $where_start_date : "");

		$where_end_date=" AND (end_date <= '".$end_date."' OR end_date IS NULL)";
		$qtxt.=($end_date !== FALSE ? $where_end_date : "");

		$q=$this->_query($qtxt); //echo $qtxt;

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {
				$res[]=$row;
				// TODO: cache result in global variable
			}
		}

		return $res;
	}


	function getResourcesInUse($start_date=FALSE, $end_date=FALSE, $allow_partial=FALSE, $exclude_consumer_id=FALSE) {
		$res=array();
		$first =TRUE;

		$qtxt ="SELECT * FROM ".$this->getTimeTableTable()." WHERE ";
		$qtxt.="resource='".$this->getResourceCode()."' ";

		if (($start_date !== FALSE) && ($end_date !== FALSE)) {
			$qtxt.=" AND (";
		}

		if ($start_date !== FALSE) {
			if ((!$first) && (!$allow_partial)) {
				$qtxt.=" AND ";
			}
			else if ((!$first) && ($allow_partial)) {
				$qtxt.=" OR ";
			}
			$qtxt.="((start_date >= '".$start_date."'";
			if ($allow_partial) {
				$qtxt.=" AND (start_date <= '".$end_date."')";
			}
			$qtxt.=") OR start_date IS NULL)";
			$first =FALSE;
		}

		if ($end_date !== FALSE) {
			if ((!$first) && (!$allow_partial)) {
				$qtxt.=" AND ";
			}
			else if ((!$first) && ($allow_partial)) {
				$qtxt.=" OR ";
			}
			$qtxt.="((end_date <= '".$end_date."'";
			if ($allow_partial) {
				$qtxt.=" AND (end_date >= '".$start_date."')";
			}
			$qtxt.=") OR end_date IS NULL)";
			$first =FALSE;
		}

		if (($start_date !== FALSE) && ($end_date !== FALSE)) {
			$qtxt.=")";
		}

		if (($exclude_consumer_id !== FALSE) && (is_array($exclude_consumer_id)) && (count($exclude_consumer_id) > 0)) {
			$qtxt.=" AND consumer_id NOT IN (".implode(",", $exclude_consumer_id).")";
		}


		$q=$this->_query($qtxt); //echo $qtxt;

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_assoc($q)) {
				$resource_id=$row["resource_id"];
				$res[$resource_id]=$resource_id;
			}
		}

		return $res;
	}


	function checkAvailability($resource_id, $start_date=FALSE, $end_date=FALSE) {
		return false;
	}


}





?>
