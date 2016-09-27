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
 * @subpackage utility
 * @author   Giovanni Derks <virtualdarkness[AT]gmail-com>
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

// TODO: this class should work stand alone but at this time is not complete and
// works only in the extended version WikiRevisionManager

class RevisionManager {

	var $prefix=NULL;
	var $dbconn=NULL;

	var $table_keys=array();
	var $default_keys_val=array();

	/** Table default fields are: author, version and rev_date **/
	var $table_extra_fields=array();

	var $revision_info=array();


	function RevisionManager($default_keys_val=array(), $prefix=FALSE, $dbconn=NULL) {
		$this->prefix=($prefix !== FALSE ? $prefix : $GLOBALS["prefix_fw"]);
		$this->dbconn=$dbconn;


		$this->setDefaultKeys($default_keys_val);
	}


	function _query( $query ) {

		if( $this->dbconn === NULL )$rs = sql_query( $query );
		else $rs = sql_query( $query, $this->dbconn );
		
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


	function _getRevisionTable() {
		return $this->prefix."_revision";
	}


	function cleanInput($arr) {

		if (isset($arr["author"]))
			$arr["author"]=(int)$arr["author"];

		if (isset($arr["version"]))
			$arr["version"]=(int)$arr["version"];

		return $arr;
	}


	function getTableKeys() {
		return $this->table_keys;
	}


	function getDefaultKeysVal() {
		return $this->default_keys_val;
	}


	function setDefaultKeys($default_keys_val) {

		if (!function_exists("array_combine")) {
			foreach($this->table_keys as $field_name) {
				$current=each($default_keys_val);
				$this->default_keys_val[$field_name]=$current["value"];
			}
		}
		else {
			$this->default_keys_val=array_combine($this->table_keys, $default_keys_val);
		}
	}


	function getTableExtraFields() {
		return $this->table_extra_fields;
	}


	function getLastRevision() {
		$res=array();

		$table_keys=$this->getTableKeys();
		$default_keys_val=$this->cleanInput($this->getDefaultKeysVal());

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." WHERE ";

		$where_arr=array();
		foreach($table_keys as $field_name) {
			$where_arr[]=$field_name."='".$default_keys_val[$field_name]."'";
		}
		$qtxt.=implode(" AND ", $where_arr)." ";


		$qtxt.="ORDER BY version DESC ";
		$qtxt.="LIMIT 0,1";
		$q=$this->_query($qtxt);

		if ($q) {
			if (sql_num_rows($q) > 0) {
				$row=sql_fetch_assoc($q);
				$version=$row["version"];
				$this->revision_info[$version]=$row;
				$res=$row;
			}
			else {
				$res=$this->getEmptyRevision();
			}
		}

		return $res;
	}


	function getEmptyRevision() {
		$res=array();

		$default_keys_val=$this->getDefaultKeysVal();
		foreach($this->getTableKeys() as $field_name) {
			$res[$field_name]=$default_keys_val[$field_name];
		}

		$res["version"]=0;
		$res["rev_date"]=date("Y-m-d H:i:s");

		foreach($this->getTableExtraFields() as $field_name) {
			$res[$field_name]="";
		}

		$res=$this->cleanInput($res); //print_r($res);

		return $res;
	}


	function loadRevision($version) {
		$res=array();

		$table_keys=$this->getTableKeys();
		$default_keys_val=$this->cleanInput($this->getDefaultKeysVal());

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE version='".(int)$version."'";

		$where_arr=array();
		foreach($table_keys as $field_name) {
			$where_arr[]=$field_name."='".$default_keys_val[$field_name]."'";
		}
		if (count($where_arr) > 1) {
			$qtxt.=" AND ".implode(" AND ", $where_arr);
		}

		$q=$this->_query($qtxt);

		if ($q) {
			if (sql_num_rows($q) > 0) {
				$res=sql_fetch_assoc($q);
			}
			else {
				$res=$this->getEmptyRevision();
			}
		}

		return $res;
	}


	function getRevision($version) {

		if (!isset($this->revision_info[$version])) {
			$this->revision_info[$version]=$this->loadRevision($version);
		}

		return $this->revision_info[$version];
	}



	function getRevisionList($ini=FALSE, $vis_item=FALSE) {

		$idst_arr=array();
		$data_info=array();
		$data_info["data_arr"]=array();


		$table_keys=$this->getTableKeys();
		$default_keys_val=$this->cleanInput($this->getDefaultKeysVal());

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE ";

		$where_arr=array();
		foreach($table_keys as $field_name) {
			$where_arr[]=$field_name."='".$default_keys_val[$field_name]."'";
		}
		$qtxt.=implode(" AND ", $where_arr)." ";

		$qtxt.="ORDER BY version DESC";
		$q=$this->_query($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.=" LIMIT ".$ini.",".$vis_item;
			$q=$this->_query($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_assoc($q)) {

				$version=$row["version"];
				$data_info["data_arr"][$i]=$row;
				$this->revision_info[$version]=$row;

				if (!in_array($row["author"], $idst_arr))
					$idst_arr[]=$row["author"];

				$i++;
			}
		}

		if (count($idst_arr) > 0) {
			$acl_manager=Docebo::user()->getAclManager();
			$user_info=$acl_manager->getUsers($idst_arr);
			foreach ($idst_arr as $idst) {
				$data_info["user"][$idst] = $user = $acl_manager->getUserName($idst);
			}
		}

		return $data_info;
	}


	function addRevision($data, $author=FALSE) {

		if ($author === FALSE) {
			$author=Docebo::user()->getIdSt();
		}

		if (!is_array($data))
			$data=array();

		$default_keys_val=$this->getDefaultKeysVal();
		foreach($this->getTableKeys() as $field_name) {
			$data[$field_name]=$default_keys_val[$field_name];
		}

		$data["author"]=$author;
		$data=$this->cleanInput($data);

		$last=$this->getLastRevision();
		$version=$last["version"]+1;

		$field_list ="version, rev_date";
		$field_val ="'".(int)$version."', NOW()";


		$field_list_arr=array();
		$field_val_arr=array();
		foreach($data as $key=>$val) {
			$field_list_arr[]=$key;
			$field_val_arr[]="'".$val."'";
		}
		if (count($field_list_arr) > 0) {
			$field_list.=", ".implode(", ", $field_list_arr);
			$field_val.=", ".implode(", ", $field_val_arr);
		}

		$qtxt="INSERT INTO ".$this->_getRevisionTable()." (".$field_list.") VALUES (".$field_val.")";
		$this->_query($qtxt);

		$res=$version;
		return $res;
	}


	/**
	 * Returns all the latest revisions of a specified type
	 * and, if available, the specified subkey
	 */
	function getLatestRevisionList($ini=FALSE, $vis_item=FALSE) {

		// TODO: make this works with standard core_revision table

		$fields="author, rev_date,  MAX(version) as version ";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";

		//$qtxt.="GROUP BY type, parent_id, sub_key ";
		$qtxt.="ORDER BY version DESC";

		$data_info=$this->getLatestRevisionListData($qtxt, $ini, $vis_item);

		return $data_info;
	}


	function getLatestRevisionListData($qtxt, $ini=FALSE, $vis_item=FALSE) {

		$idst_arr=array();
		$data_info=array();
		$data_info["data_arr"]=array();

		$q=$this->_query($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.=" LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_assoc($q)) {

				$version=$row["version"];
				$data_info["data_arr"][$i]=$row;
				$this->revision_info[$version]=$row;

				if (!in_array($row["author"], $idst_arr))
					$idst_arr[]=$row["author"];

				$i++;
			}
		}

		if (count($idst_arr) > 0) {
			$acl_manager=Docebo::user()->getAclManager();
			$user_info=$acl_manager->getUsers($idst_arr);
			foreach ($idst_arr as $idst) {
				$data_info["user"][$idst]=$acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
			}
		}

		return $data_info;
	}


	/**
	 * Returns all the parent_id values of the revision
	 * having content that matches the searched text for
	 * the current revision type and, if available, subkey.
	 */
	function searchInLatestRevision($return_val, $ini=FALSE, $vis_item=FALSE) {

		$data=$this->getLatestRevisionList($ini, $vis_item);

		$res=$this->searchInLatestRevisionData($return_val, $data);

		return $res;
	}


	function searchInLatestRevisionData($return_val, $data) {
		$res=array("found"=>array());

		$data_arr=$data["data_arr"];
		$cached=array();

		foreach($data_arr as $row) {
			$parent_id=$row[$return_val];
			if (!in_array($parent_id, $res)) {
				$res["found"][]=$parent_id;
				$res["cached"][$parent_id]=$this->getRevision($row["version"]);
			}
		}

		return $res;
	}


}



// ------------------------------------------------------------------------- //



Class OldRevisionManager {

	var $prefix=NULL;
	var $dbconn=NULL;
	var $type=NULL;
	var $parent_id=0;

	var $revision_info=array();


	function OldRevisionManager($type, $parent_id, $sub_key=FALSE, $prefix=FALSE, $dbconn=NULL) {
		$this->prefix=($prefix !== FALSE ? $prefix : $GLOBALS["prefix_fw"]);
		$this->dbconn=$dbconn;

		$this->type=$type;
		$this->parent_id=$parent_id;
		$this->sub_key=$sub_key;
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


	function _getRevisionTable() {
		return $this->prefix."_revision";
	}


	function getRevisionType() {
		return $this->type;
	}


	function getParentId() {
		return (int)$this->parent_id;
	}


	function getSubKey() {
		return $this->sub_key;
	}


	function getLastRevision() {
		$res=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE type='".$this->getRevisionType()."' AND ";
		$qtxt.="parent_id='".$this->getParentId()."' ";
		$qtxt.="AND ".($this->getSubKey() !== FALSE ? "sub_key='".$this->getSubKey()."'" : "sub_key='0'")." ";
		$qtxt.="ORDER BY version DESC ";
		$qtxt.="LIMIT 0,1";
		$q=$this->_executeQuery($qtxt);

		if ($q) {
			if (sql_num_rows($q) > 0) {
				$row=sql_fetch_assoc($q);
				$version=$row["version"];
				$this->revision_info[$version]=$row;
				$res=$row;
			}
			else {
				$res=$this->getEmptyRevision();
			}
		}

		return $res;
	}


	function loadRevision($version) {
		$res=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE type='".$this->getRevisionType()."' AND ";
		$qtxt.="parent_id='".$this->getParentId()."' AND version='".(int)$version."'";
		$qtxt.=" AND ".($this->getSubKey() !== FALSE ? "sub_key='".$this->getSubKey()."'" : "sub_key='0'");
		$q=$this->_executeQuery($qtxt);

		if ($q) {
			if (sql_num_rows($q) > 0) {
				$res=sql_fetch_assoc($q);
			}
			else {
				$res=$this->getEmptyRevision();
			}
		}

		return $res;
	}


	function getRevision($version) {

		if (!isset($this->revision_info[$version])) {
			$this->revision_info[$version]=$this->loadRevision($version);
		}

		return $this->revision_info[$version];
	}


	function getEmptyRevision() {
		$res=array();

		$res["type"]=$this->getRevisionType();
		$res["parent_id"]=$this->getParentId();
		$res["sub_key"]=FALSE;
		$res["version"]=0;
		$res["author"]="";
		$res["rev_date"]=date("Y-m-d H:i:s");
		$res["content"]="";

		return $res;
	}


	function getRevisionList($ini=FALSE, $vis_item=FALSE) {

		$idst_arr=array();
		$data_info=array();
		$data_info["data_arr"]=array();

		$fields="*";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE type='".$this->getRevisionType()."' AND ";
		$qtxt.="parent_id='".$this->getParentId()."' ";
		$qtxt.="AND ".($this->getSubKey() !== FALSE ? "sub_key='".$this->getSubKey()."'" : "sub_key='0'")." ";
		$qtxt.="ORDER BY version DESC";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.=" LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_assoc($q)) {

				$version=$row["version"];
				$data_info["data_arr"][$i]=$row;
				$this->revision_info[$version]=$row;

				if (!in_array($row["author"], $idst_arr))
					$idst_arr[]=$row["author"];

				$i++;
			}
		}

		if (count($idst_arr) > 0) {
			$acl_manager=Docebo::user()->getAclManager();
			$user_info=$acl_manager->getUsers($idst_arr);
			foreach ($idst_arr as $idst) {
				$data_info["user"][$idst]=$acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
			}
		}

		return $data_info;
	}


	function addRevision($content, $author=FALSE) {

		if ($author === FALSE) {
			$author=Docebo::user()->getIdSt();
		}

		$type=$this->getRevisionType();
		$parent_id=$this->getParentId();
		$sub_key=$this->getSubKey();

		$last=$this->getLastRevision();
		$version=$last["version"]+1;

		$field_list ="type, parent_id, version, sub_key, author, rev_date, content";
		$field_val ="'".$type."', '".(int)$parent_id."', '".(int)$version."', ";
		$field_val.=($sub_key !== FALSE ? "'".$sub_key."'" : "'0'").", ";
		$field_val.="'".(int)$author."', NOW(), '".$content."' ";

		$qtxt="INSERT INTO ".$this->_getRevisionTable()." (".$field_list.") VALUES (".$field_val.")";
		$this->_executeQuery($qtxt);

		$res=$version;
		return $res;
	}


	/**
	 * Returns all the latest revisions of a specified type
	 * and, if available, the specified subkey
	 */
	function getLatestRevisionList($search=FALSE, $ini=FALSE, $vis_item=FALSE, $use_subkey=TRUE, $parent_id_in=FALSE) {

		$type=$this->getRevisionType();
		$sub_key=($use_subkey ? $this->getSubKey() : FALSE);

		$idst_arr=array();
		$data_info=array();
		$data_info["data_arr"]=array();

		$fields ="type, parent_id, MAX(version) as version, sub_key, ";
		$fields.="author, rev_date, content";
		$qtxt ="SELECT ".$fields." FROM ".$this->_getRevisionTable()." ";
		$qtxt.="WHERE type='".$type."' ";
		if (($parent_id_in !== FALSE) && (is_array($parent_id_in))) {
			$qtxt.=(count($parent_id_in) > 0 ? "AND parent_id IN (".implode(",", $parent_id_in).") " : "AND parent_id='0' ");
		}
		$qtxt.=($sub_key !== FALSE ? "AND sub_key='".$sub_key."' " : "");
		$qtxt.=($search !== FALSE ? "AND content LIKE '%".$search."%' " : "");
		$qtxt.="GROUP BY type, parent_id, sub_key ";
		$qtxt.="ORDER BY version DESC";
		$q=$this->_executeQuery($qtxt);

		if ($q)
			$data_info["data_tot"]=sql_num_rows($q);
		else
			$data_info["data_tot"]=0;

		if (($ini !== FALSE) && ($vis_item !== FALSE)) {
			$qtxt.=" LIMIT ".$ini.",".$vis_item;
			$q=$this->_executeQuery($qtxt);
		}

		if (($q) && (sql_num_rows($q) > 0)) {
			$i=0;
			while($row=sql_fetch_assoc($q)) {

				$version=$row["version"];
				$data_info["data_arr"][$i]=$row;
				$this->revision_info[$version]=$row;

				if (!in_array($row["author"], $idst_arr))
					$idst_arr[]=$row["author"];

				$i++;
			}
		}

		if (count($idst_arr) > 0) {
			$acl_manager=Docebo::user()->getAclManager();
			$user_info=$acl_manager->getUsers($idst_arr);
			foreach ($idst_arr as $idst) {
				$data_info["user"][$idst]=$acl_manager->relativeId($user_info[$idst][ACL_INFO_USERID]);
			}
		}

		return $data_info;
	}


	/**
	 * Returns all the parent_id values of the revision
	 * having content that matches the searched text for
	 * the current revision type and, if available, subkey.
	 */
	function searchInLatestRevision($search, $ini=FALSE, $vis_item=FALSE, $use_subkey=TRUE, $parent_id_in=FALSE) {
		$res=array("found"=>array());

		$data=$this->getLatestRevisionList($search, $ini, $vis_item, $use_subkey, $parent_id_in);
		$data_arr=$data["data_arr"];

		foreach($data_arr as $row) {
			$parent_id=$row["parent_id"];
			if (!in_array($parent_id, $res)) {
				$res["found"][]=$parent_id;
			}
		}

		$res["data_arr"]=$data["data_arr"];

		return $res;
	}


}


?>
