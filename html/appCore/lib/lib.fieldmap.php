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
 * @package admin-library
 * @subpackage field
 * @version  $Id:$
 */
 
Class FieldMapManager {

	var $prefix=NULL;
	var $dbconn=NULL;

	var $map_table=FALSE;
	var $map_from_table=FALSE;
	var $resource_arr=array();
	var $field_map=FALSE;
	var $map_extra_filter=FALSE;


	function FieldMapManager($prefix=FALSE, $dbconn=NULL) {
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


	/**
	 * Set a WHERE query statement to filter the reading of the
	 * map data using some extra parameters, for example a form_id
	 * See the loadFieldMap() function for more information.
	 */
	function setMapExtraFilter($filter) {
		$this->map_extra_filter=$filter;
		$this->field_map=FALSE;
	}


	function getMapExtraFilter() {
		return $this->map_extra_filter;
	}


	function setResourceList($resource_arr) {
		$res=array();

		if (!is_array($resource_arr)) {
			$resource_arr=func_get_args();
		}

		foreach($resource_arr as $key=>$val) {

			$code=(is_array($val) ? $key : $val);

			if ((is_array($val)) && (isset($val["class_path"])))
				$class_path=$val["class_path"];
			else
				$class_path=$GLOBALS["where_framework"]."/class/";

			if ((is_array($val)) && (isset($val["class_file"])))
				$class_file=$val["class_file"];
			else
				$class_file="class.fieldmap_".strtolower($code).".php";

			if ((is_array($val)) && (isset($val["class_name"])))
				$class_name=$val["class_name"];
			else
				$class_name="FieldMap".ucfirst($code);

			$res["list"][$code]["code"]=$code;
			$res["list"][$code]["class_path"]=$class_path;
			$res["list"][$code]["class_file"]=$class_file;
			$res["list"][$code]["class_name"]=$class_name;
			$res["raw_list"][]=$code;
			$res["query_list"][]="'".$code."'";
		}

		$this->resource_arr=$res;
		$this->field_map=FALSE;
	}


	function getResourceList($what=FALSE) {
		if ($what !== FALSE) {
			return $this->resource_arr[$what];
		}
		else {
			return $this->resource_arr;
		}
	}


	function setMapTable($map_table) {
		$this->map_table=$map_table;
		$this->field_map=FALSE;
	}


	function _getMapTable() {
		if ($this->map_table === FALSE)
			return $this->prefix."_field_map";
		else
			return $this->map_table;
	}


	function setMapFromTable($map_from_table) {
		$this->map_from_table=$map_from_table;
	}


	function _getMapFromTable() {
		return $this->map_from_table;
	}


	function free() {
		$this->field_map=FALSE;
	}


	function loadFieldMap() {
		$res=array("map"=>array(), "custom_fields"=>array());

		$extra_filter=$this->getMapExtraFilter();

		$qtxt ="SELECT * FROM ".$this->_getMapTable()." WHERE ";
		$qtxt.="field_map_resource IN (".implode(",", $this->getResourceList("query_list")).") ";
		$qtxt.=($extra_filter !== FALSE ? "AND ".$extra_filter : "")." ";
		$qtxt.="ORDER BY field_map_resource, field_type";
		$q=$this->_query($qtxt); //--DEBUG--// echo $qtxt;

		if (($q) && (sql_num_rows($q) > 0)) {
			while($row=sql_fetch_array($q)) {
					$field_id=$row["field_id"];
					$res["map"][$field_id]["resource"]=$row["field_map_resource"];
					$res["map"][$field_id]["type"]=$row["field_type"];
					$res["map"][$field_id]["map_to"]=$row["field_map_to"];

					if (($row["field_type"] == "custom") && (!in_array($row["field_map_to"], $res["custom_fields"]))) {
						$res["custom_fields"][]=$row["field_map_to"];
					}
			}
		}

		return $res;
	}


	function getFieldMap() {
		$res=array();

		if ($this->field_map === FALSE) {
			$res=$this->loadFieldMap();
			$this->field_map=$res;
		}
		else {
			$res=$this->field_map;
		}

		return $res;
	}


	function getMappedFields($field_list, $id) {
		$res=array();
		$debug=FALSE;

		require_once($GLOBALS["where_framework"]."/lib/lib.field.php");
		$fl=new FieldList();

		$field_map_info=$this->getFieldMap();
		$field_map=$field_map_info["map"];
		$map_custom_fields=$field_map_info["custom_fields"];
		if ($debug) { echo "<pre>\n"; print_r($field_map_info); } //--DEBUG--//
		unset($field_map_info);

		$fl->setFieldEntryTable($this->_getMapFromTable());
		$user_field_arr=$fl->showFieldForUserArr(array($id), $field_list); // to cache: arr[id]=res
		if ($debug) { print_r($user_field_arr); } //--DEBUG--//

		if (is_array($user_field_arr[$id]))
	 		$field_val=$user_field_arr[$id];
		else
			$field_val=array();


		// This way we are going to load only the information
		// about the fields we really need.
		$field_list=$field_list+$map_custom_fields;
		if ($debug) { print_r($field_list); } //--DEBUG--//

		$field_info=$fl->getFieldsFromArray($field_list); // to cache? maybe one for all
		if ($debug) { print_r($field_info); } //--DEBUG--//


		// $mro: Map Resource Object (array)
		// We'll use this later to read predefined fields names
		$mro=array();
		foreach($this->getResourceList("list") as $code=>$resource) {

			require_once($resource["class_path"].$resource["class_file"]);
			$mro[$code]=new $resource["class_name"]();

		}


		// Creating empty schema that will contain useful information
		// like field description..
		foreach($mro as $resource=>$resource_obj) {
			foreach($resource_obj->getRawPredefinedFields() as $code) {
				$res[$resource]["predefined"][$code]["description"]=$resource_obj->getPredefinedFieldLabel($code);
				$res[$resource]["predefined"][$code]["value"]="";
			}
		}


		foreach ($field_info as $field_id=>$info) {

			if (isset($field_map[$field_id])) {

				$type=$field_map[$field_id]["type"];
				$resource=$field_map[$field_id]["resource"];
				$new_id=$field_map[$field_id]["map_to"];


				if ($type == "custom") {
					$res[$resource][$type][$new_id]["description"]=$field_info[$new_id][FIELD_INFO_TRANSLATION];
				}
				$res[$resource][$type][$new_id]["value"]=$field_val[$field_id];
			}
			else if ((!isset($field_map[$field_id])) && (!in_array($field_id, $map_custom_fields))) {
				$resource="_not_mapped";
				$type="custom";
				$res[$resource][$type][$field_id]["description"]=$field_info[$field_id][FIELD_INFO_TRANSLATION];
				$res[$resource][$type][$field_id]["value"]=$field_val[$field_id];
				if (($debug) && (empty($field_val[$field_id]))) { echo $field_id." :: "; }
			}
		}

		if ($debug) { print_r($res); echo "\n</pre>\n"; } //--DEBUG--//
		return $res;
	}


}





?>
