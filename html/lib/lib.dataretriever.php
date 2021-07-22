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
 * @subpackage interaction
 * @version $Id: lib.dataretriever.php 908 2007-01-15 17:55:24Z giovanni $
 */

class DataRetriever {
	// the database connection
	var $dbConn = NULL;
	// prefix for table access
	var $prefix = "";
	// the recordset
	var $rs = NULL;
	// array of order columns
	var $orderCols = NULL;

	function DataRetriever( $dbConn, $prefix ) {
		$this->dbConn = $dbConn;
		$this->prefix = $prefix;
		$this->orderCols = array();
	}

	function setOrderCol( $filedName, $descendant ) {
		$this->orderCols[] = array( $filedName, $descendant );
	}

	function getFieldCount() {
		return sql_num_fields( $this->rs );
	}

	function getFieldsInfo() {
		$result = array();
		while( ($fInfo = sql_fetch_field( $this->rs )) != NULL ) {
			$result[$fInfo->name] = $fInfo;
		}
		return $result;
	}

	function _getData( $query, $startRow=FALSE, $numRows=FALSE ) {
		if( count( $this->orderCols ) > 0 ) {
			$query .= " ORDER BY ";
			$index = 0;
			for( ; $index < (count( $this->orderCols ) - 1); $index++ ) {
				$oc = $this->orderCols[$index];
				if($oc[0] != '') {
					if( $oc[1] )
						$query .= $oc[0] ." DESC, ";
					else
						$query .= $oc[0] .", ";
				}
			}
			$oc = $this->orderCols[$index];
			if($oc[0] != '') {
				if( $oc[1] )
					$query .= $oc[0] ." DESC";
				else
					$query .= $oc[0];
			}

		}
		if (((int)$startRow !== false) && ((int)$numRows > 0)) {
			$query .= " LIMIT $startRow,$numRows";
		}

		if( $this->dbConn === NULL )
			$this->rs = sql_query( $query );
		else
			$this->rs = sql_query( $query, $this->dbConn );
		return $this->rs;
	}

	function getRows( $startRow, $numRows ) {
		// put here your query
		// tipical query is:
		// SELECT field1, field2, field3
		// 	FROM myTable
		// 	WHERE field1 = 'something'
		$query = "SELECT 'Hello', 'World.', 'How', 'are', 'you?' ";

		return $this->_getData( $query, $startRow, $numRows );
	}

	function fetchRecord() {
		return sql_fetch_assoc ( $this-> rs );
	}

	function getTotalRows() {
		return -1;
	}
}

?>
