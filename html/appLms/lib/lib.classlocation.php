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

Class ClassLocationManager {

	var $prefix=NULL;
	var $dbconn=NULL;

	var $status_info=array();


	function ClassLocationManager($prefix="learning", $dbconn=NULL) {
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
		return $this->prefix."_class_location";
	}


	function getClassLocationTable() {
		// lol:
		return $this->_getMainTable();
	}


	function GetLastOrd($table) {
		//require_once(_base_.'/lib/lib.utils.php');
		return utilGetLastOrd($table, "ord");
	}


	function moveItem($direction, $id_val) {
		//require_once(_base_.'/lib/lib.utils.php');

		$table=$this->_getMainTable();

		utilMoveItem($direction, $table, "location_id", $id_val, "ord");
	}


	function getClassLocationList($ini=FALSE, $vis_item=FALSE) {

		$data_info=array();
		$data_info["data_arr"]=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getMainTable()." ";
		$qtxt.="ORDER BY location ";
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

				$id=$row["location_id"];
				$data_info["data_arr"][$i]=$row;
				$this->status_info[$id]=$row;

				$i++;
			}
		}

		return $data_info;
	}


	function getClassLocationArray($include_any=FALSE) {
		$res=array();

		$class_locations=$this->getClassLocationList(FALSE, FALSE);
		$locations_list=$class_locations["data_arr"];

		if ($include_any)
			$res[0]= Lang::t("_ALL", "classroom", "lms");

		foreach ($locations_list as $location) {
			$id=$location["location_id"];
			$res[$id]=$location["location"];
		}

		return $res;
	}


	function loadClassLocationInfo($id) {
		$res=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getMainTable()." ";
		$qtxt.="WHERE location_id='".(int)$id."'";
		$q=$this->_executeQuery($qtxt);

		if (($q) && (sql_num_rows($q) > 0)) {
			$res=sql_fetch_array($q);
		}

		return $res;
	}


	function getClassLocationInfo($id) {

		if (!isset($this->status_info[$id]))
			$this->status_info[$id]=$this->loadClassLocationInfo($id);

		return $this->status_info[$id];
	}



	function saveData($data) {

		$id=(int)$data["id"];
		$location=$data["location"];

		if ($id == 0) {

			if (empty($location)) {
				$lang=& DoceboLanguage::createInstance("classlocation", "lms");
				$location=$lang->def("_UNAMED");
			}

			$field_list="location";
			$field_val="'".$location."'";

			$qtxt="INSERT INTO ".$this->_getMainTable()." (".$field_list.") VALUES(".$field_val.")";
			$id=$this->_executeInsert($qtxt);
		}
		else if ($id > 0) {

			$qtxt="UPDATE ".$this->_getMainTable()." SET location='".$location."' WHERE location_id='".$id."'";
			$q=$this->_executeQuery($qtxt);

		}

		return $id;
	}


	function deleteClassLocation($id) {
		$qtxt="DELETE FROM ".$this->_getMainTable()." WHERE location_id='".$id."' LIMIT 1";
		$q=$this->_executeQuery($qtxt);

		return $q;
	}

}


?>