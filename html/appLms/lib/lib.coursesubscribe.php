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
 * @version  $Id:  $
 */
// ----------------------------------------------------------------------------

class CourseSubscribe {

	var $prefix=NULL;
	var $dbconn=NULL;

	// Subscribe info
	var $subscribe_info;

	function CourseSubscribe($prefix=FALSE, $dbconn=NULL) {
		$this->prefix=($prefix !== false ? $prefix : $GLOBALS["prefix_lms"]);
		$this->dbconn=$dbconn;

		$this->subscribe_info=array("course"=>array(), "edition"=>array());
	}


	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = sql_query( $query );
		else
			$rs = sql_query( $query, $this->dbconn );
		return $rs;
	}


	function _executeInsert( $query ) {
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


	function _getCourseTable() {
		return $this->prefix."_course";
	}


	function _getEditionTable() {
		return $this->prefix."_course_edition";
	}


	function &getInstance($prefix=FALSE, $dbconn=NULL) {

		if(!isset($GLOBALS["course_subscribe_manager"])) {
			$GLOBALS["course_subscribe_manager"] = new CourseSubscribe($prefix, $dbconn);
		}

		return $GLOBALS["course_subscribe_manager"];
	}


	/**
	 * Use this one if you already have loaded the needed information
	 * If something is missing the _getInfo() method will reload
	 * information from the database.
	 */
	function setSubscribeInfo($data, $course_id, $edition_id=FALSE) {

		if (!$this->_isEdition($edition_id)) {
			$key="course";
			$item_id=(int)$course_id;
		}
		else {
			$key="edition";
			$item_id=(int)$edition_id;
		}

		$res=$this->getSubscribeInfo($course_id, $edition_id);

		$look_for=array("allow_overbooking", "can_subscribe", "sub_start_date", "sub_end_date",
		                "user_count", "waiting", "max_num_subscribe");

		foreach($look_for as $name) {
			if (isset($data[$name])) {
				$this->subscribe_info[$key][$item_id][$name]=$data[$name];
			}
		}
	}


	/**
	 * This is not private but you should use the getSubscribeInfo
	 */
	function loadSubscribeInfo($course_id, $edition_id=FALSE) {
		$res=array();
		$main_res=array();
		$user_res=array();

		if (!$this->_isEdition($edition_id)) {
			$id_name="idCourse";
			$id_name_user="idCourse";
			$id_val=(int)$course_id;
			$table=$this->_getCourseTable();
		}
		else {
			$id_name="idCourseEdition";
			$id_name_user="edition_id";
			$id_val=(int)$edition_id;
			$table=$this->_getEditionTable();
		}

		$fields ="max_num_subscribe, allow_overbooking, can_subscribe, sub_start_date, sub_end_date";
		$qtxt ="SELECT ".$fields." FROM ".$table." WHERE ".$id_name."='".$id_val."'";

		$q=sql_query($qtxt);
		if (($q) && (sql_num_rows($q) > 0)) {
			$main_res=sql_fetch_assoc($q);
		}

		$fields="sum(waiting = '1') as waiting, COUNT(*) as user_count";
		$qtxt ="SELECT ".$fields." FROM ".$GLOBALS["prefix_lms"]."_courseuser ";
		$qtxt.="WHERE ".$id_name_user."='".$id_val."' ";
		if (!$this->_isEdition($edition_id)) {
			$qtxt.="AND edition_id='0' ";
		}
		$qtxt.="GROUP BY ".$id_name_user;

		$q=sql_query($qtxt);
		if (($q) && (sql_num_rows($q) > 0)) {
			$user_res=sql_fetch_assoc($q);
		}
		else {
			$user_res["waiting"]=0;
			$user_res["user_count"]=0;
		}

		$res=$main_res+$user_res;
		return $res;
	}


	function getSubscribeInfo($course_id, $edition_id=FALSE, $use_cache=TRUE) {

		if (!$this->_isEdition($edition_id)) {
			$key="course";
			$item_id=(int)$course_id;
		}
		else {
			$key="edition";
			$item_id=(int)$edition_id;
		}

		if ((!isset($this->subscribe_info[$key][$item_id])) || (!$use_cache)) { //echo "Load! ";
			$this->subscribe_info[$key][$item_id]=$this->loadSubscribeInfo($course_id, $edition_id);
		}

		return $this->subscribe_info[$key][$item_id];
	}


	function _isEdition($edition_id) {

		if (($edition_id !== FALSE) && ($edition_id > 0))
			return TRUE;
		else
			return FALSE;
	}


	function _getInfo($name, $course_id, $edition_id=FALSE) {

		if (!$this->_isEdition($edition_id)) {
			$key="course";
			$item_id=(int)$course_id;
		}
		else {
			$key="edition";
			$item_id=(int)$edition_id;
		}

		if (!isset($this->subscribe_info[$key][$item_id][$name])) {
			$this->getSubscribeInfo($course_id, $edition_id);
		}

		return $this->subscribe_info[$key][$item_id][$name];
	}


	function canSubscribe($course_id, $edition_id=FALSE) {
		$res=FALSE;

		$today=date("Y-m-d")." 00:00:00";

		$can_subscribe=$this->_getInfo("can_subscribe", $course_id, $edition_id);

		if ($can_subscribe == 1) {

			$allow_overbooking=$this->allowOverbooking($course_id, $edition_id);
			$full=$this->isFull($course_id, $edition_id);
			if ((!$full) || ($allow_overbooking)) {
				$res=TRUE;
			}
		}
		else if ($can_subscribe == 2) {

			$sub_start_date=$this->_getInfo("sub_start_date", $course_id, $edition_id);
			$sub_end_date=$this->_getInfo("sub_end_date", $course_id, $edition_id);

			if ((strcmp($today, $sub_start_date) >= 0) && (strcmp($today, $sub_end_date) <= 0)) {
				$res=TRUE;
			}
		}

		return $res;
	}


	function allowOverbooking($course_id, $edition_id=FALSE) {
		$res=FALSE;

		$allow_overbooking=$this->_getInfo("allow_overbooking", $course_id, $edition_id);
		$res=($allow_overbooking == 1 ? TRUE : FALSE);

		return $res;
	}


	function isFull($course_id, $edition_id=FALSE) {
		$res=FALSE;

		$max=$this->_getInfo("max_num_subscribe", $course_id, $edition_id);
		$user_sub=$this->_getInfo("user_count", $course_id, $edition_id);
		$user_waiting=$this->_getInfo("waiting", $course_id, $edition_id);
		$sub=$user_sub+$user_waiting;
		$res=(($max == 0) || ($sub < $max) ? FALSE : TRUE);

		return $res;
	}


}


?>