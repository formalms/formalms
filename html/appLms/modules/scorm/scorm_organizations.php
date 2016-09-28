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
 * @module	scorm_organizations.php
 *
 * @version $Id: scorm_organizations.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004
 * @author Emanuele Sandri
 **/

require_once(dirname(__FILE__) . '/config.scorm.php');
require_once(dirname(__FILE__) . '/CPManager.php');

class Scorm_Organization {

	var $idscorm_organization;
	var $org_identifier;
	var $idscorm_package;
	
	var $title;
	
	var $dbconn;
	var $err_code = 0;
	var $err_text = "";

	var $orgtable = "scorm_organizations";
	
	function Scorm_Organization($org_identifier, $idscorm_package, $connection, $createonfail = false, $title='' )
	{
		$this->org_identifier = $org_identifier;
		$this->idscorm_package = $idscorm_package;
		$this->dbconn = $connection; 
		
		$this->orgtable = $GLOBALS['prefix_lms']."_scorm_organizations";
		
		// Find the idresource for this idsco, idscorm_package
		$query = "SELECT idscorm_organization, title "
				." FROM ".$this->orgtable
				." WHERE idscorm_package = ". $idscorm_package
				." AND org_identifier = '". addslashes($org_identifier) ."'";
				
		//die($query);
		$rs = sql_query($query, $this->dbconn);
		if ($rs == false || sql_num_rows($rs) == 0) {
			if ($createonfail) {
				// not found => create new resource record
				$query = "INSERT INTO $this->orgtable "
				. "(org_identifier,idscorm_packege,title) VALUES "
				. "( '$this->org_identifier', $this->idscorm_package, '$this->title' )";
				if (sql_query($query, $this->dbconn)) {
					if (sql_affected_rows($this->dbconn) == 1) {
						// get the id of the last insert = idscorm_tracking
						$this->idscorm_organization = sql_insert_id($this->dbconn);
					} else {
						$this->setError(1, "Scorm_Organization::Scorm_Organization " . sql_error($this->dbconn) . "[" .$query ."]");
					} 
				} else {
					$this->setError(2, "Scorm_Organization::Scorm_Organization " . sql_error($this->dbconn) . "[" .$query ."]");
				} 
			} else {
				$this->setError(3, "Scorm_Organization::Scorm_Organization " . sql_error($this->dbconn) . "[" .$query ."]");
			} 
		} else {
			list(	$this->idscorm_organization,
					$this->title ) = sql_fetch_array($rs);
			sql_free_result($rs);
		} 
	} 

	function extractFromCPManager( $cpm ) {
		
	}
	
	function save() {
		$query = "UPDATE ".$this->orgtable
				." SET title = '".$this->title."',"
				." WHERE idscorm_organization = '".(int)$this->idscorm_organization."'";
		if( sql_query === false ) {
			$this->setError(4, "Scorm_Organization::save 1 ". sql_error($this->dbconn) . "[" .$query ."]" );
			return false;
		} else {
			if( sql_affected_rows($this->dbconn) == 0 && sql_errno($this->dbconn) != 0 ) {
				$this->setError(5, "Scorm_Organization::save 2 ". sql_error($this->dbconn) . "[" .$query ."]" );
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