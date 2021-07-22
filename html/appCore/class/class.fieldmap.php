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
 * @subpackage field
 */

Class FieldMap {

	var $lang=NULL;

	/**
	 * class constructor
	 */
	function FieldMap() {

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


	function _getMainTable() {

	}


	function getPrefix() {
		return "";
	}


	function getPredefinedFieldLabel($field_id) {
		return ucfirst($field_id);
	}


	function getRawPredefinedFields() {
		return array();
	}


	function getPredefinedFields($with_prefix=TRUE) {
		$res=array();

		$pfx=($with_prefix ? $this->getPrefix()."predefined_" : "");
		foreach($this->getRawPredefinedFields() as $code) {
			$res[$pfx.$code]=$this->getPredefinedFieldLabel($code);
		}

		return $res;
	}


	function getCustomFields($with_prefix=TRUE) {
		return array();
	}
	

	/**
	 * @param array $predefined_data
	 * @param array $custom_data
	 * @param mixed $id
	 * @param boolean $dropdown_id if true will take dropdown values as id;
	 *                             else will search the id starting from the value.
	 */
	function saveFields($predefined_data, $custom_data, $id=FALSE) {
		return FALSE;
	}	

}

?>