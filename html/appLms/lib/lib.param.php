<?php

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
 * @return param_value
 **/
function getLOParam( $idParam, $param_name ) {
	$query = "SELECT param_value FROM ".$GLOBALS['prefix_lms']."_lo_param "
			."WHERE idParam = '".(int)$idParam."'"
			."  AND param_name = '".$param_name."'";
	$rs = sql_query($query) or 
			errorCommunication( 'getLOParam' );
	if( sql_num_rows( $rs ) == 1 ) {
		list( $param_value ) = sql_fetch_row( $rs );
		return $param_value;
	} else 
		return FALSE;	
}

/**
 * @return array of param
 **/
function getLOParamArray( $idParam ) {
	$query = "SELECT param_name, param_value FROM ".$GLOBALS['prefix_lms']."_lo_param "
			."WHERE idParam = '".(int)$idParam."'";
	$rs = sql_query($query) or 
			errorCommunication( 'getLOParam' );
	$result = array();
	while( list( $param_name, $param_value ) = sql_fetch_row($rs) )
		$result[$param_name] = $param_value;
	return $result;	
}

/**
 * @return idParam if $idParam == NULL
 * else bool
 **/
function setLOParam( $idParam, $param_name, $param_value) {
	
	if( $idParam === NULL ) {
		$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_lo_param "
				."( idParam, param_name, param_value )"
				." VALUES "
				."( '0', '".$param_name."', '".$param_value."' )";
		if(sql_query( $query )) {
			
			$idParam = sql_insert_id();
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_lo_param SET "
					." idParam='".(int)$idParam."'"
					."WHERE id = '".(int)$idParam."'";
		} else errorCommunication( 'setLOParam' );
	} else {
		$val = getLOParam( $idParam, $param_name );
		if( $val === FALSE ) {
			$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_lo_param "
					."( idParam, param_name, param_value )"
					." VALUES "
					."( '".(int)$idParam."', '".$param_name."', '".$param_value."' )";
		} else {
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_lo_param SET "
					." param_value='".$param_value."'"
					."WHERE idParam = '".(int)$idParam."'"
					."  AND param_name = '".$param_name."'";
		}
	}
	sql_query( $query ) or 
		errorCommunication( 'setLOParam' );
	return $idParam;
}

function delLOParam( $idParam, $param_name ) {
	$query = "DELETE FROM ".$GLOBALS['prefix_lms']."_lo_param "
			."WHERE idParam = '".(int)$idParam."'"
			."  AND param_name = '".$param_name."'";
	$rs = sql_query($query) or 
			errorCommunication( 'delLOParam' );	
}

function delAllLOParam( $idParam ) {
	$query = "DELETE FROM ".$GLOBALS['prefix_lms']."_lo_param "
			."WHERE idParam = '".(int)$idParam."'";
	$rs = sql_query($query) or 
			errorCommunication( 'delLOParam' );	
}

?>