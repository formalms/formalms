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

class ClassroomManager {

	var $prefix=NULL;
	var $dbconn=NULL;


	function ClassroomManager($prefix="learning", $dbconn=NULL) {
		$this->prefix=$prefix;
		$this->dbconn=$dbconn;
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


	function _getMainTable() {
		return $this->prefix."_classroom";
	}


	function getClassroomList($ini=FALSE, $vis_item=FALSE, $where=FALSE) {

		require_once($GLOBALS["where_lms"]."/lib/lib.classlocation.php");
		$clm=new ClassLocationManager();

		$data_info=array();
		$data_info["data_arr"]=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getMainTable()." as t1, ";
		$qtxt.=$clm->getClassLocationTable()." as t2 ";
		$qtxt.="WHERE t1.location_id=t2.location_id ";
		if ($where !== FALSE) {
			$qtxt.="AND ".$where." ";
		}
		$qtxt.="ORDER BY name ";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.="LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_array($q)) {

				$id=$row["idClassroom"];
				$data_info["data_arr"][$i]=$row;
				$this->classroom_info[$id]=$row;

				$i++;
			}
		}

		return $data_info;
	}
	
	function getClassroomNameList($where = false) {

		require_once($GLOBALS["where_lms"]."/lib/lib.classlocation.php");
		$clm = new ClassLocationManager();

		$data_info = array();

		$qtxt = "
		SELECT t1.idClassroom, t1.name, t2.location 
		FROM ".$this->_getMainTable()." as t1 
			JOIN ".$clm->getClassLocationTable()." as t2 
		WHERE t1.location_id = t2.location_id ";
		if($where !== FALSE) {
			$qtxt .= " AND ".$where." ";
		}
		$qtxt .= "ORDER BY t1.name ";
		$q = $this->_executeQuery($qtxt);
		
		while(list($id, $name, $location) = sql_fetch_row($q)) {

			$data_info[$id] = array('classroom' => $name, 'location' => $location);
		}
		return $data_info;
	}
	
	function getClassroomArray($include_any=FALSE) {
		$res=array();

		$classrooms=$this->getClassroomList(FALSE, FALSE);
		$rooms_list=$classrooms["data_arr"];

		if ($include_any)
			$res[0]= Lang::t("_ALL", "classroom", "lms");

		foreach ($rooms_list as $room) {
			$id=$room["idClassroom"];
			$res[$id]=$room["name"];
		}

		return $res;
	}


	function loadClassroomInfo($id) {
		$res=array();
		require_once($GLOBALS["where_lms"]."/lib/lib.classlocation.php");
		$clm = new ClassLocationManager();
		
		$qtxt = " SELECT * "
			." FROM ".$this->_getMainTable()." as t1, "
			.$clm->getClassLocationTable()." as t2 "
			." WHERE t1.location_id = t2.location_id "
			."	AND t1.idClassroom='".(int)$id."' ";
		$qtxt.="ORDER BY t1.name ";

		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$res=sql_fetch_array($q);
		}

		return $res;
	}


	function getClassroomInfo($id) {

		if (!isset($this->classroom_info[$id]))
			$this->classroom_info[$id]=$this->loadClassroomInfo($id);

		return $this->classroom_info[$id];
	}



}


?>