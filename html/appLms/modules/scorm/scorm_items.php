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
 * @module scorm_items.php
 *
 * @version $Id: scorm_items.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004 
 * @author Emanuele Sandri
 **/

require_once(dirname(__FILE__) . '/config.scorm.php');
require_once(dirname(__FILE__) . '/CPManager.php');

class Scorm_Item {

	var $idscorm_item;
	var $idscorm_organization;
	var $item_identifier;
	var $identifierref;
	var $idscormresource;
	var $isvisible;
	var $parameters;
	var $title;

	var $adlcp_prerequisites = '';
	var $adlcp_maxtimeallowed = '';
	var $adlcp_timelimitaction = '';
	var $adlcp_datafromlms = '';
	var $adlcp_masteryscore = '';
	
	var $dbconn;
	var $err_code = 0;
	var $err_text = "";

	var $itemtable = "scorm_items";
	
	function Scorm_Item($item_identifier, $idscorm_organization, $idpackage, $connection, $createonfail = false, $idscorm_item = NULL )
	{
		$this->item_identifier = $item_identifier;
		$this->idscorm_organization = $idscorm_organization;
		$this->dbconn = $connection; 
		
		
		$this->itemtable = $GLOBALS['prefix_lms']."_scorm_items";
		
		// Find the idresource for this idsco, idscormpackage
		if( $idscorm_item !== NULL ) {
			$query = "SELECT idscorm_item, idscorm_organization, item_identifier, identifierref, idscorm_resource, isvisible, parameters, title, "
					."adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction, adlcp_datafromlms, adlcp_masteryscore"
					." FROM ".$this->itemtable
					." WHERE idscorm_item = '". (int)$idscorm_item."'";
		} else if( $idscorm_organization != FALSE ) {
			$query = "SELECT idscorm_item, idscorm_organization, item_identifier, identifierref, idscorm_resource, isvisible, parameters, title, "
					."adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction, adlcp_datafromlms, adlcp_masteryscore"
					." FROM ".$this->itemtable
					." WHERE idscorm_organization = ". $idscorm_organization
					." AND item_identifier = '". $item_identifier ."'";
		} else {
			$query = "SELECT item.idscorm_item, item.idscorm_organization, item.item_identifier, item.identifierref, item.idscorm_resource, item.isvisible, item.parameters, item.title, "
					."item.adlcp_prerequisites, item.adlcp_maxtimeallowed, item.adlcp_timelimitaction, item.adlcp_datafromlms, item.adlcp_masteryscore"
					." FROM ".$this->itemtable." item, ".$GLOBALS['prefix_lms']."_scorm_organization org"
					." WHERE item.idscorm_organization = org.idscorm_organization"
					." AND org.idscorm_package = ". $idpackage
					." AND item.item_identifier = '". $item_identifier ."'";			
		}
		
				
		//die($query);
		$rs = sql_query($query, $this->dbconn);
		if ($rs == false || sql_num_rows($rs) == 0) {
			if ($createonfail) {
				// not found => create new item record
				$query = "INSERT INTO $this->itemtable "
				. "(item_identifier,idscorm_organization) VALUES ( $this->item_identifier, $this->idscorm_organization )";
				if (sql_query($query, $this->dbconn)) {
					if (sql_affected_rows($this->dbconn) == 1) {
						// get the id of the last insert = idscorm_tracking
						$this->idscorm_item = sql_insert_id($this->dbconn);
					} else {
						$this->setError(1, "Scorm_Item::Scorm_Item " . sql_error($this->dbconn) . "[" .$query ."]");
						return false;
					} 
				} else {
					$this->setError(1, "Scorm_Item::Scorm_Item " . sql_error($this->dbconn) . "[" .$query ."]");
					return false;
				} 
			} else {
				$this->setError(1, "Scorm_Item::Scorm_Item " . sql_error($this->dbconn) . "[" .$query ."]");
				return false;
			} 
		} else {
			list(	$this->idscorm_item, 
					$this->idscorm_organization,
					$this->item_identifier,
					$this->identifierref, 
					$this->idscorm_resource, 
					$this->isvisible,
					$this->parameters,
					$this->title,
					$this->adlcp_prerequisites, 
					$this->adlcp_maxtimeallowed, 
					$this->adlcp_timelimitaction, 
					$this->adlcp_datafromlms, 
					$this->adlcp_masteryscore ) = sql_fetch_array($rs);
			sql_free_result($rs);
		} 
		return true;
	} 
	
	function save() {
		$query = "UPDATE $this->itemtable"
				." SET identifierref = '$this->identifierref',"
				." idscorm_resource = $this->idscorm_resource,"
				." isvisible = '$this->isvisible',"
				." title = '$this->title',"
				." idscorm_resource = '$this->idscorm_resource',"
				." adlcp_prerequisites = '$this->adlcp_prerequisites',"
				." adlcp_maxtimeallowed = '$this->adlcp_maxtimeallowed',"
				." adlcp_timelimitaction = '$this->adlcp_timelimitaction',"
				." adlcp_datafromlms = '$this->adlcp_datafromlms',"
				." adlcp_masteryscore = '$this->adlcp_masteryscore',"
				." WHERE idscorm_item = $this->idscorm_item";
		if( sql_query === false ) {
			$this->setError(2, "Scorm_Item::save 1 ". sql_error($this->dbconn) . "[" .$query ."]" );
			return false;
		} else {
			if( sql_affected_rows($this->dbconn) == 0 && sql_errno($this->dbconn) != 0 ) {
				$this->setError(2, "Scorm_Item::save 2 ". sql_error($this->dbconn) . "[" .$query ."]" );
				return false;		
			}
		}
		return true;
	}
	
	function setError($ecode, $etext)
	{
		$this->err_code = $ecode;
		$this->err_text = $etext;
	} 

	function getErrorCode()
	{
		return $this->err_code;
	} 
	function getErrorText()
	{
		return $this->err_text;
	} 
	
}

?>