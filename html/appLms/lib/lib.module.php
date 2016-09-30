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
 * @package 	DoceboLMS
 * @category 	function for class istance
 * @author 		Fabio Pirovano <fabio@docebo.com>
 * @version 	$Id: lib.module.php 573 2006-08-23 09:38:54Z fabio $
 */

/**
 * create a istance of a specified class of a module
 * automaticaly include the file that contains the class of the module
 *
 * @param string	$module_name 	the name og the module to istance
 * @param string 	$class_name 	the name of the class relative to the module, if not passed is 
 *									extracted from the $module_name
 * 
 * @return mixed 	the class istance
 */


function createLO( $objectType, $id_resource = false, $environment = false ) {
	
	$query = "SELECT className, fileName FROM %lms_lo_types WHERE objectType='".$objectType."'";
	$rs = sql_query( $query );
	list( $class_name, $file_name ) = sql_fetch_row( $rs );
	if (trim($file_name) == "") return false;
	/*if (trim($file_name) == "") {
		if (isset($_SESSION['idCourse'])) {
			Util::jump_to('index.php?modname=organization&op=organization');
		}
		Util::jump_to('index.php');
	}*/
	require_once(dirname(__FILE__).'/../class.module/learning.object.php' );
	if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$file_name ) && Get::cfg('enable_customscripts', false) == true ){
		require_once(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$file_name );
	} else {
		require_once(Docebo::inc(_lms_.'/class.module/'.$file_name));
	}
	$lo = new $class_name($id_resource, $environment);
	return $lo;
}

function createLOTrack( $idTrack, $objectType, $idResource, $idParams, $back_url ) {
	
	$query = "SELECT classNameTrack, fileNameTrack FROM %lms_lo_types WHERE objectType='".$objectType."'";
	$rs = sql_query( $query );
	list( $className, $fileName ) = sql_fetch_row( $rs );
	if ( trim( $fileName ) == '' ) return false;
	require_once(dirname(__FILE__).'/../class.module/learning.object.php' );
	if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$fileName ) && Get::cfg('enable_customscripts', false) == true ){
		require_once(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$fileName );
	} else {
		require_once(dirname(__FILE__).'/../class.module/'.$fileName );
	}
	$lo = new $className ( $idTrack, $idResource, $idParams, $back_url );
	return $lo;
}

function createLOTrackShort( $idReference, $idUser, $back_url ) {
	
	$query = "SELECT o.idParam, o.objectType, o.idResource,"
			." ct.idTrack, lt.classNameTrack, lt.fileNameTrack"
 			." FROM %lms_organization o"
			." JOIN %lms_commontrack ct"
			." JOIN %lms_lo_types lt"
			." WHERE (o.objectType = lt.objectType)"
			."   AND (o.idOrg = ct.idReference)"
 			."   AND (o.idOrg = '".(int)$idReference."')"
 			."   AND (ct.idUser = '".(int)$idUser."')";
	$rs = sql_query( $query );
	list( $idParams, $objectType, $idResource, $idTrack, $className, $fileName ) = sql_fetch_row( $rs );
	if ( trim( $fileName ) == '' ) return false;
	require_once( dirname(__FILE__).'/../class.module/learning.object.php' );
	if (file_exists(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$fileName ) && Get::cfg('enable_customscripts', false) == true ){
		require_once(_base_ . '/customscripts/'._folder_lms_.'/class.module/'.$fileName );
	} else {
		require_once(dirname(__FILE__).'/../class.module/'.$fileName );
	}
	$lo = new $className ( $idTrack, $idResource, $idParams, $back_url );
	return $lo;
}

?>