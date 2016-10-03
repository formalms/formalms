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
 * @module CPManagerDb
 *
 * @author Emanuele Sandri
 * @version $Id: CPManagerDb.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004 
 **/

include_once( dirname(__FILE__).'/scorm_utils.php');

/**
 * @class CPManagerDb
 **/
class CPManagerDb {
	var $identifier = '';

	var $errCode = 0;
 	var $errText = '';

	var $defaultOrg;
	var $idscorm_package;
	var $idReference;
	var $dbconn;
	var $prefix;

	function Open( $idReference, $idscorm_package, $dbconn, $prefix ) {
		$this->idReference = $idReference;
		$this->idscorm_package = $idscorm_package;
		$this->dbconn = $dbconn;
		$this->prefix = $prefix;
		return TRUE;
	}

	/** Parse the imsmanifest.xml file contained in Content Package
	 *  @return TRUE if success, FALSE otherwise; use GetLastError to get the
	 *  last generated error
	 */
	function ParseManifest() {
		$query = "SELECT idpackage, path, defaultOrg"
				." FROM ".$this->prefix."_scorm_package"
				." WHERE idscorm_package=".$this->idscorm_package;
		$rs = sql_query($query,$this->dbconn);
		if( $rs === FALSE ) {
			$this->setError( SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} else if( sql_num_rows($rs) == 0 ) {
			$this->setError( SPSCORM_E_RECORDNOTFOUND, 'Package with id='.$this->idscorm_package.' not found' );
			return FALSE;
		}
			
		list($this->identifier,$this->path,$this->defaultOrg) = sql_fetch_row($rs);
		
		return TRUE;
	}
	
	function GetOrganizationId( $identifier ) {
		$query = "SELECT idscorm_organization"
				." FROM ".$this->prefix."_scorm_organizations"
				." WHERE org_identifier='".addslashes($identifier)."'"
				." AND idscorm_package=".$this->idscorm_package;
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) {
			$this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} else if( sql_num_rows($rs) == 0 ) {
			$this->setError(SPSCORM_E_RECORDNOTFOUND, 'Organization with identifier='.addslashes($identifier).' and idscorm_package='.$this->idscorm_package.' not found' );
			return FALSE;			
		}
		$row = sql_fetch_row($rs);
		return $row[0];
	}
	
	function GetResourceInfo( $identifier ) {
		$query = "SELECT idscorm_resource, scormtype, href"
				." FROM ".$this->prefix."_scorm_resources"
				." WHERE idsco='".addslashes($identifier)."'"
				." AND idscorm_package=".$this->idscorm_package;
		$rs = sql_query($query,$this->dbconn);
		if( $rs === FALSE ) {
			$this->setError( SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} else if( sql_num_rows($rs) == 0 ) {
			$this->setError( SPSCORM_E_RECORDNOTFOUND, 'Resource with scoid='.addslashes($identifier).' and idscorm_package='.$this->idscorm_package.' not found' );
			return FALSE;
		}
				
		$row = sql_fetch_assoc($rs);
						
		$info['href'] = $row['href'];
		$info['identifier'] = $identifier;
		$info['type'] = 'webcontent';
		$info['scormtype'] = $row['scormtype'];
		$info['uniqueid'] = $row['idscorm_resource'];
		return $info;
	}
	
	/**
	 * This function render the organization identified by $identifier using
	 *  the $renderer object; this must be inherited from RendererAbstract
	 *  and must implement all the functions declared in this class
	 *  @param $identifier idendifier of the organization to render
	 *  @param &$renderer object that render the organization
	 */
	function RenderOrganization( $identifier, &$renderer) {
		$query = "SELECT idscorm_organization, title"
				." FROM ".$this->prefix."_scorm_organizations"
				." WHERE org_identifier='".addslashes($identifier)."'"
				." AND idscorm_package=".$this->idscorm_package;
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) {
			$this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} else if( sql_num_rows($rs) == 0 ) {
			$this->setError(SPSCORM_E_RECORDNOTFOUND, 'Organization with identifier='.$identifier.' and idscorm_package='.$this->idscorm_package.' not found' );
			return FALSE;			
		}
		
		list( $idscorm_organization, $title ) = sql_fetch_row($rs);
		
		$itemInfo['identifier'] = $identifier;
		$itemInfo['isLast'] = TRUE;     // the organization is always the last
        $itemInfo['identifierref'] = FALSE;
        $itemInfo['isvisible'] = TRUE;
        $itemInfo['parameters'] = FALSE;
		$itemInfo['prerequisites'] = TRUE;
		$itemInfo['title'] = $title;
		$itemInfo['uniqueid'] = $idscorm_organization;
		$itemInfo['idscorm_package'] = $this->idscorm_package;

		$query = "SELECT idscorm_item, idscorm_organization, idscorm_parentitem, item_identifier,"
				." identifierref, idscorm_resource, isvisible, parameters, title,"
				." adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction,"
				." adlcp_datafromlms, adlcp_masteryscore"
				." FROM ".$this->prefix."_scorm_items"
				." WHERE idscorm_organization=".$idscorm_organization
				." AND idscorm_parentitem IS NULL"
				." ORDER BY idscorm_item";
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) {
			$this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} 
		
		if( sql_num_rows($rs) == 0 ) {
			// is ok a organization without resources?
			$itemInfo['isLeaf'] = TRUE;
			// debug -- echo "<!-- Organization is leaf [$query] -->";
		} else {
			$itemInfo['isLeaf'] = FALSE;
		}
		
        $renderer->RenderStartItem( $this, $itemInfo );
        
		while( FALSE !== ($record = sql_fetch_assoc($rs)) ) {
			$this->RenderItem( $record, $renderer, true );
		}
		
		$renderer->RenderStopItem( $this, $itemInfo );
		
	}
	
	function RenderItem( &$record, &$renderer, $isLast ) {
		// collect infos about item
		
		$itemInfo['uniqueid'] = $record['idscorm_item'];
		$itemInfo['identifier'] = $record['item_identifier'];
		
		$itemInfo['isLast'] = $isLast;
		
		if( strlen($record['identifierref']) > 0 )
			$itemInfo['identifierref'] = $record['identifierref'];
		else
		    $itemInfo['identifierref'] = FALSE;

		$itemInfo['isvisible'] = (strcmp($record['isvisible'],'true') == 0)?TRUE:FALSE;
		
		if( strlen($record['parameters']) > 0 ) 
			$itemInfo['parameters'] = $record['parameters'];
		else 
			$itemInfo['parameters'] = FALSE;
			
		$itemInfo['title'] = $record['title'];
		$itemInfo['adlcp_prerequisites'] = $record['adlcp_prerequisites'];
		$itemInfo['adlcp_maxtimeallowed'] = $record['adlcp_maxtimeallowed'];
		$itemInfo['adlcp_timelimitaction'] = $record['adlcp_timelimitaction'];
		$itemInfo['adlcp_datafromlms'] = $record['adlcp_datafromlms'];
		$itemInfo['adlcp_masteryscore'] = $record['adlcp_masteryscore'];
		
		if( strlen($record['adlcp_prerequisites']) > 0 && $this->idReference !== NULL ) {
			$scorm12seq = new SCORM12_Sequencing($this->idReference, FALSE, $record['idscorm_organization'], $this->dbconn, $this->prefix );
			$itemInfo['prerequisites'] = $scorm12seq->evauatePrerequisites($record['adlcp_prerequisites']);
		} else {
			$itemInfo['prerequisites'] = TRUE;
		}

		$query = "SELECT idscorm_item, idscorm_organization, idscorm_parentitem, item_identifier,"
				." identifierref, idscorm_resource, isvisible, parameters, title,"
				." adlcp_prerequisites, adlcp_maxtimeallowed, adlcp_timelimitaction,"
				." adlcp_datafromlms, adlcp_masteryscore"
				." FROM ".$this->prefix."_scorm_items"
				." WHERE idscorm_organization=".$record['idscorm_organization']
				." AND idscorm_parentitem=".$record['idscorm_item']
				." ORDER BY idscorm_item";				
        
		$rs = sql_query($query, $this->dbconn);
		if( $rs === FALSE ) {
			echo '<!-- Generic db error: '.sql_error($this->dbconn)." [$query] -->";
			$this->setError(SPSCORM_E_DB_ERROR, 'Generic db error: '.sql_error($this->dbconn) );
			return FALSE;
		} 
		
		if( sql_num_rows($rs) == 0 ) {
			$itemInfo['isLeaf'] = TRUE;
			// debug echo "<!-- Organization is leaf [$query] -->";
		} else {
			$itemInfo['isLeaf'] = FALSE;
			// debug echo "<!-- Organization is not leaf [$query] -->";
		}

		$renderer->RenderStartItem(	$this, $itemInfo );
		
		$subrecord = sql_fetch_assoc($rs);
		
		while( $subrecord ) {
			
			$nextRecord = sql_fetch_assoc($rs);
			
			/* pass the info about the last element */
			if( $nextRecord === FALSE )
			    $this->RenderItem( $subrecord, $renderer, true );
			else
			    $this->RenderItem( $subrecord, $renderer, false );
			    
			$subrecord = $nextRecord;
		}

        $renderer->RenderStopItem( $this, $itemInfo );

	}
	
	/**
	 * Set the error
	 */
	function setError( $errCode, $errText ) {
		$this->errCode = $errCode;
		$this->errText = $errText;
	}
}

?>
