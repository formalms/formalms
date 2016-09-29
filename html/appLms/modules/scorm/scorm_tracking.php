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
 * @module Module to handle scorm tracking of lessons
 * 
 * @version $Id: scorm_tracking.php 229 2006-04-10 11:35:05Z ema $
 * @copyright 2004
 */
 
require_once(dirname(__FILE__) . '/config.scorm.php');
require_once(dirname(__FILE__) . '/scorm_utils.php');

$direct_access_params['cmi.core.student_id'] 		= 'student_id';
$direct_access_params['cmi.core.student_name'] 		= 'student_name';
$direct_access_params['cmi.core.lesson_location'] 	= 'lesson_location';
$direct_access_params['cmi.core.credit'] 			= 'credit';
$direct_access_params['cmi.core.lesson_status'] 	= 'lesson_status';
$direct_access_params['cmi.core.entry'] 			= 'entry';
$direct_access_params['cmi.core.score.raw'] 		= 'score_raw';
$direct_access_params['cmi.core.score.max'] 		= 'score_max';
$direct_access_params['cmi.core.score.min'] 		= 'score_min';
$direct_access_params['cmi.core.total_time'] 		= 'total_time';
$direct_access_params['cmi.core.lesson_mode'] 		= 'lesson_mode';
$direct_access_params['cmi.core.exit'] 				= 'exit';
$direct_access_params['cmi.core.session_time'] 		= 'session_time';
$direct_access_params['cmi.suspend_data'] 			= 'suspend_data';
$direct_access_params['cmi.launch_data'] 			= 'launch_data';
$direct_access_params['cmi.comments'] 				= 'comments';
$direct_access_params['cmi.comments_from_lms'] 		= 'comments_from_lms';

/**
 * Scorm_Tracking
 * 
 * @package SCORM
 * @author Emanuele Sandri
 * @copyright Copyright (c) 2004
 * @version $Id: scorm_tracking.php 229 2006-04-10 11:35:05Z ema $
 * @access public
 **/
class Scorm_Tracking {
	var $idUser;
	var $idReference;
	var $idscrom_item;
	var $idscorm_package;
	var $idtrack;
	var $dbconn;
	var $err_code = 0;
	var $err_text = '';
	var $xmldoc;
	var $scormVersion = '';

	var $tracktable = "scorm_tracking";
	var $historytable = "scorm_tracking_history";
	
	/**
	 * Constructor of the Scorm_Tracking
	 * @param $idUser
	 * @param $idReference
	 * @param $id if $idUser is null => $idscorm_tracking, 
	 * 			  if $id_isitemid is true => $idscorm_item, and $idRefrence is needed
	 * 			  else idsco from manifest
	 * @param $idscorm_package
	 * @param $connection
	 * @param $createonfile
	 */
	function Scorm_Tracking($idUser, $idRefernce, $id, $idscorm_package, $connection, $createonfail = true, $id_isitemid = false)
	{
		$this->idUser = $idUser;
		$this->idReference = $idRefernce;
		$this->idscorm_item = NULL;
		$this->idscorm_package = $idscorm_package;
		$this->dbconn = $connection; 
		$this->xmldoc = null;
		$this->tracktable = $GLOBALS['prefix_lms']."_scorm_tracking";
		$this->historytable = $GLOBALS['prefix_lms']."_scorm_tracking_history";
		
		// Find the idtrack for this peer iditem, iduser
		if( $idUser === null ) {
			// id is idscorm_tracking
			$query = "SELECT idUser, idscorm_item FROM ".$GLOBALS['prefix_lms']."_scorm_tracking WHERE idscorm_tracking='".(int)$id."'";
			$rs = sql_query($query, $this->dbconn);
			if ($rs == false || sql_num_rows($rs) == 0) {
				return false;
			}
			list($this->idUser, $this->idscorm_item) = sql_fetch_row($rs);
			$this->idtrack = $id;
			$this->scormVersion = getScormVersion('idscorm_item',$this->idscorm_item);
			return true;
		}
		if( $id_isitemid ) {
			$query = "SELECT tracking.idscorm_item, tracking.idscorm_tracking"
					." FROM ".$GLOBALS['prefix_lms']."_scorm_tracking tracking"
					." WHERE tracking.idscorm_item=".$id
					." AND tracking.idUser=".$idUser;
		} 
		
		$rs = sql_query($query, $this->dbconn);
		if( $rs == false ) {
			$this->setError(1, "Scorm_Tracking::Scorm_Tracking " . sql_error($this->dbconn) . "[" .$query ."]");
			return false;
		}
		if ( sql_num_rows($rs) == 0) {
			if ($createonfail) {
				// not found => create new track record
				$query = "INSERT INTO $this->tracktable "
				." (idUser, idReference, idscorm_item)"
				." VALUES"
				." ( $this->idUser, $this->idReference, $id )";
				if (sql_query($query, $this->dbconn)) {
					if (sql_affected_rows($this->dbconn) == 1) {
						// get the id of the last insert = idscorm_tracking
						$this->idtrack = sql_insert_id($this->dbconn);
						$this->scormVersion = getScormVersion('idscorm_item',$id);
						//update history
						
						//end update
					} else {
						$this->setError(1, "Scorm_Tracking::Scorm_Tracking " . sql_error($this->dbconn) . "[" .$query ."]");
						return false;
					} 
				} else {
					$this->setError(1, "Scorm_Tracking::Scorm_Tracking " . sql_error($this->dbconn) . "[" .$query ."]");
					return false;
				} 
			} else {
				$this->setError(1, "Scorm_Tracking::Scorm_Tracking " . sql_error($this->dbconn) . "[" .$query ."]");
				return false;
			} 
		} else {
			list($this->idscorm_item, $this->idtrack) = sql_fetch_array($rs);
			$this->scormVersion = getScormVersion('idscorm_item',$this->idscorm_item);
			sql_free_result($rs);
		} 
		return true;
	} 

	function getTrackData($idtrack=false) {
		if (!$idtrack) $idtrack = $this->idtrack;
		$qry = "SELECT * FROM $this->tracktable WHERE idscorm_tracking=$idtrack";
		if ($res = sql_query($qry)) {
			$temp = sql_fetch_assoc($res);
			return $temp;
		} else
			return false;
	}

	function saveHistory($idtrack, $score_raw, $score_max, $session_time, $lesson_status) {
		if ($lesson_status=='') return true; //no need to historize this values
		if ($score_raw=='') $score_raw='NULL';
		if ($score_max=='') $score_max='NULL';
		if ($score_raw!='') $score_raw = str_replace(',', '.', $score_raw);
		//if ($session_time=='') $session_time='NULL'; else 
		$session_time="'$session_time'";
		$qry = "INSERT INTO $this->historytable "
			." (idscorm_tracking, date_action, score_raw, score_max, session_time, lesson_status) "
			." VALUES "
			." ($idtrack, NOW(), ".$score_raw.", ".$score_max.", ".$session_time.", '".$lesson_status."')";
		return sql_query($qry);
	}

	function setError($ecode, $etext)
	{
		$this->err_code = $ecode;
		$this->err_text = $etext;
		soap__dbgOut("Scorm_Tracking::setError( $ecode, $etext);" , SOAP_DBG_LEVEL_ERROR, SOAP_DBG_FILTER_SETERROR );
	} 

	function getErrorCode()
	{
		return $this->err_code;
	} 
	function getErrorText()
	{
		return $this->err_text;
	} 

	function getXmlDoc()
	{
		if( $this->xmldoc != null )
			return $this->xmldoc;
			
		$rs = sql_query("SELECT xmldata FROM $this->tracktable WHERE idscorm_tracking = $this->idtrack", $this->dbconn);
		if ($rs === false) {
			// set error and exit with false
			$this->setError(1, "Scorm_Tracking::getXmlDoc " . sql_error($this->dbconn));
			return false;
		} 

		$data = sql_fetch_row($rs);
		$this->xmldoc = new DDomDocument();
		if (strlen($data[0]) == 0) {	
			$this->xmldoc->createNew('1.0');
			$this->precompileXmlDoc();
		} else {
			$this->xmldoc->loadXML(stripslashes($data[0]));
		} 
		return $this->xmldoc;
	} 

	/*function readScormXMLTemplate( ) {
		$filename = dirname(__FILE__) . '/scormItemTrackData-'.$this->scormVersion.'.xml';
		$handle = @fopen($filename, 'r');
		if( $handle === FALSE ) {
		    $this->setError( SPSCORM_E_FILENOTFOND, $filename );
		    return FALSE;
		}
		$contents = fread($handle, filesize($filename));
		fclose($handle);
		return $contents;		
	}*/
	
	function precompileXmlDoc() {
		require_once(dirname(__FILE__) . '/scorm-'.$this->scormVersion.'.php');
		$root = $this->xmldoc->createElement('trackobj');
		$this->xmldoc->appendChild($root);
		$root->setAttribute('iduser', $this->idUser);
		$root->setAttribute('idresource', $this->idscoresource);
		$this->setParam(SCORM_RTE_LEARNERID, $this->idUser, false, true);
	}
	
	function setXmlDoc(&$xmldoc)
	{
		$this->xmldoc = $xmldoc;
		$query = "UPDATE $this->tracktable SET "
		 . "xmldata = '" . addslashes($xmldoc->saveXML()) . "' "
		 . "WHERE idscorm_tracking = $this->idtrack";
		if (sql_query($query)) {
			if (sql_affected_rows($this->dbconn) == 1) {
				//action removed, left blank intentionaly
			} else {
				if(	sql_errno($this->dbconn) != 0 ) {
					$this->setError(1, "Scorm_Tracking::setXmlDoc " . sql_error($this->dbconn));
					return false;
				} else 
					return true;
			} 
		} else {
			$this->setError(1, "Scorm_Tracking::setXmlDoc " . sql_error($this->dbconn));
			return false;
		} 
	} 
	
	function getParam($parampath, $SCOAccessibility = true) {
		soap__dbgOut("+Scorm_Tracking::getParam($parampath, $SCOAccessibility)",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
		$aelem = getElementsArrayTrackingTemplate($parampath, $this->scormVersion );
		// if last element is false => signal error
		$last = $aelem[count($aelem)-1];
		//soap__dbgOut(" last[1] = {$last[1]} ");
		if( $last[1] == false ) {
			switch( $last[0] ){
				case '_count':
					$this->setError(203, 'Element not an array. Cannot have count.');
					return false;
				break;
				case '_children':
					$this->setError(202, 'Element cannot have children');
					return false;
				break;
				default:
					$this->setError(201, 'Invalid argument error');
					return false;
				break;
			}
		} else {
			// if it is write only return error
			if( is_object($last[1]) 
			 && $last[1]->getAttribute('SCOAccessibility') == 'writeonly' 
			 && $SCOAccessibility ) {
				$this->setError(404, 'Element is write only');
				return false;
			}
		}
		
		if( is_object($last[1]) ) {
			soap__dbgOut(" Scorm_Tracking::getParam try to get value from direct map in table",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
			// try to get value from direct map in table
			$fieldMap = $last[1]->getAttribute('dbRef');
			if( strlen($fieldMap) > 0 ) {
				soap__dbgOut(" Scorm_Tracking::getParam param direct access in db",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
				// extract value from the db
				$query = "SELECT `$fieldMap` FROM $this->tracktable WHERE idscorm_tracking = $this->idtrack";
				$rs = sql_query( $query, $this->dbconn);
				if ($rs === false) {
					// set error and exit with false
					$this->setError(1, "Scorm_Tracking::getParam [$query] " . sql_error($this->dbconn));
					return false;
				} else {
					list($value) = sql_fetch_array($rs);
					soap__dbgOut( "-Scorm_Tracking::getParam return ".$value,SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM );
					return $value;
				}			
			}
			soap__dbgOut(" Scorm_Tracking::getParam value not fount in table map => go ahead",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
		}
		// navigate troughout elements of xmldoc
		if( $this->xmldoc == null )
			$this->getXmlDoc();
		$elem = $this->xmldoc->getDocumentElement();

		if( $aelem[count($aelem)-1][0] == '_children' ) {
			soap__dbgOut(" Scorm_Tracking::getParam requested _children",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
			// the last token in name of requested param is _children
			$result = array();
			$childs = $aelem[count($aelem)-2][1]->getChildNodes();
			
			soap__dbgOut(" Scorm_Tracking::getParam # childs ".count($childs),SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);

			for( $iCNode = 0; $iCNode < $childs->getLength(); $iCNode++) {
				$tmpItem = $childs->item($iCNode);
				if( $tmpItem->getNodeType() == XML_ELEMENT_NODE )
					$result[] = $tmpItem->getNodeName();
			}
			soap__dbgOut("-Scorm_Tracking::getParam return ".implode(',', $result),SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
			return implode(',', $result);		
		}

		for( $i = 0; $i < count($aelem) && $elem; $i++ ) {
			$infotemplate = $aelem[$i];
			$elemtemplate = $infotemplate[1];
			soap__dbgOut(" Scorm_Tracking::getParam analyze ". $infotemplate[0],SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
			switch( $infotemplate[0] ) {
				case '_count': // not used jet. See default
					soap__dbgOut(" Scorm_Tracking::getParam value is _count return it!",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM);
					$cnodes = $elem->getChildNodes();
					return $cnodes->getLength();
				break;
				case '_children':
					$result = array();
					$cnodes = $aelem[$i-1][1]->getChildNodes();
					for( $iCNode = 0; $iCNode < $cnodes->getLength(); $iCNode++ ) {
						$tmpItem = $cnodes->item($iCNode);
						if( $tmpItem->getNodeType() == XML_ELEMENT_NODE ) 
							$result[] = $tmpItem->getNodeName();
					}
					return implode(',', $result);
				break;
				default:
					if( $elemtemplate->getAttribute('item') == 'no' 
					 && $elemtemplate->getAttribute('index') == 'yes' ) {
						// we must search for indexed element (index is in $infotemplate[0] 
						if( $i < (count($aelem)-1) ) {
							// next element is _count ?
							if( $aelem[$i+1][0] == "_count" ) {
								$tmpItem = $elem->getElementsByTagname( $elemtemplate->getNodeName() );
								return $tmpItem->getLength();
							} 
						}
							
						$elem = $elem->getElementByNameAttrib($elemtemplate->getNodeName(), 'index', $infotemplate[0]);
					} else {
						// if element is terminal or nothing is not important now
						$elem = $elem->getElementByNameAttrib( $elemtemplate->getNodeName());
					}
				break;
			}
		}
		// if elem is null return empty string, the value is not initialized

		if( $elem ) {
			$textelem = $elem->getFirstChild();
			if( !$textelem ) {
				soap__dbgOut( "-Scorm_Tracking::getParam return ".$elem->getContent(),SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM );
				return $elem->getContent();
			} else {
				soap__dbgOut( "-Scorm_Tracking::getParam return ".$textelem->getContent(),SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM );
				return $textelem->getContent();
			}
		} else {
			if( !is_object($last[1]) ) { // is _count but not all path are been present now
				soap__dbgOut( "-Scorm_Tracking::getParam return 0",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM );
				return "0";
			} 
			soap__dbgOut( "-Scorm_Tracking::getParam return \"\"",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_GETPARAM );
			return "";
		}
		
	}
	
	function setParam($parampath, $paramval, $SCOAccessibility = true, $create = true)
	{
		soap__dbgOut( "+Scorm_Tracking::setParam($parampath, $paramval, $SCOAccessibility, $create)",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM);
		$aelem = getElementsArrayTrackingTemplate($parampath, $this->scormVersion );
		// if last element is false => signal error
		$last = $aelem[count($aelem)-1];
		if( $last[1] == false ) {
			switch( $last[0] ){
				case '_count':
				case '_children':
				case 'version':
					$this->setError(402, 'Invalid set value, element is a keyword');
					return false;
				break;
				default:
					$this->setError(201, 'Invalid argument error');
					return false;
				break;
			}
		} else {
			// if it is read only return error
			if( is_object($last[1]) 
			 && $last[1]->getAttribute('SCOAccessibility') == 'readonly' 
			 && $SCOAccessibility ) {
				$this->setError(403, 'Element is read only');
				return false;
			}
		}

		// try to get value from direct map in table
		$fieldMap = $last[1]->getAttribute('dbRef');
		soap__dbgOut( " Scorm_Tracking::setParam found filedMap = $fieldMap" ,SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM );

		if( strlen($fieldMap) > 0 ) {
			// set value to the db
			$query = "UPDATE $this->tracktable SET "
					."`$fieldMap` = '" . addslashes($paramval) . "' "
	 				." WHERE idscorm_tracking = $this->idtrack";
			
			soap__dbgOut( " Scorm_Tracking::setParam query for update fieldMap = $query", SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM );

			if (sql_query($query, $this->dbconn)) {
				if (sql_affected_rows($this->dbconn) == 1) {
					
					// return true;
					// We must also set the value in xmlData filed!
					soap__dbgOut( " Scorm_Tracking::setParam OK affected row = 1" ,SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM );
				} else {
					// mysql return 0 also when $fieldMap is already $paramval
					soap__dbgOut( " Scorm_Tracking::setParam affected row != 1" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
					if( sql_errno($this->dbconn) != 0 ) {
						soap__dbgOut( " Scorm_Tracking::setParam report Error" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
						$this->setError(1, "Scorm_Tracking::setParam 1 [ $query ] " . sql_error($this->dbconn));
						return false;
					}
				} 
			} else {
				soap__dbgOut( " Scorm_Tracking::setParam report Error" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
				$this->setError(1, "Scorm_Tracking::setParam 2 [ $query ] " . sql_error($this->dbconn));
				return false;
			} 
		}

		
		// navigate troughout elements of xmldoc
		if( $this->xmldoc == null ) 
			$this->getXmlDoc();
		$elem = $this->xmldoc->getDocumentElement();
		for( $i = 0; $i < count($aelem) && $elem; $i++ ) {
			$infotemplate = $aelem[$i];
			switch( $infotemplate[0] ) {
				case '_count':
				case '_children':
					$this->setError(402, 'Invalid set value, element is a keyword' );
					return false;
				break;
				default:
					$elemtemplate = $infotemplate[1];
					if( $elemtemplate->getAttribute('item') == 'no' 
					 && $elemtemplate->getAttribute('index') == 'yes' ) {
						// we must search for indexed element (index is in $infotemplate[0] 
						$newelem = $elem->getElementByNameAttrib( $elemtemplate->getNodeName(), 'index', $infotemplate[0]);
					} else {
						// if element is terminal or nothing is not important now
						$newelem = $elem->getElementByNameAttrib( $elemtemplate->getNodeName());
					}
					if( !$newelem && $create) {
						$newelem = $this->xmldoc->createElement($elemtemplate->getNodeName());
						if( $elemtemplate->getAttribute('index') == 'yes')
							$newelem->setAttribute('index',$infotemplate[0]);
						if( $elemtemplate->getAttribute('item') == 'yes') 
							$newelem->setAttribute('item', 'yes');
						else
							$newelem->setAttribute('item', 'no');
						$elem->appendChild($newelem);
					} 
					$elem = $newelem;				
				break;
			}
		}

		$rc = $elem->getFirstChild();
		while( $rc !== NULL  ) {
			$elem->removeChild($rc);
			$rc = $elem->getFirstChild();
		}
		
		// now $elem point to the element to set
		$textelem = $this->xmldoc->createTextNode($paramval);
		$elem->appendChild(	$textelem );	
		
		$this->setXmlDoc($this->xmldoc);
		soap__dbgOut( "-Scorm_Tracking::setParam return true ",SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM );

		return true;
	} 
	
	function setParamXML( $xmldoc ) {
		require_once(dirname(__FILE__) . '/scorm-'.$this->scormVersion.'.php');
		$arrFields = array();
		$xpath = new DDomXPath($xmldoc);
		foreach( $GLOBALS['xpathwritedb'] as $fieldName => $xpathquery ) {
			$xpath_ns = $xpath->query( $xpathquery );
	
			if( $xpath_ns->getLength() > 0 ) {
				$elem = $xpath_ns->item(0);
				$arrFields[$fieldName] = $elem->getContent();
			}
		}
		
		if( count( $arrFields ) > 0 ) {
			$isFirst = TRUE;
			$query = "UPDATE $this->tracktable SET ";
			foreach( $arrFields as $fname => $fvalue ) {

                if($fname == 'score_raw') $fvalue = str_replace(',', '.', $fvalue);
				if( $isFirst ) {
					$query .= "`$fname` = '" . addslashes($fvalue) . "' ";
					$isFirst = FALSE;					
				} else {
					$query .= ", `$fname` = '" . addslashes($fvalue) . "' ";
				}
			}
	 		$query .= "WHERE idscorm_tracking = $this->idtrack";
			//echo "<!-- $query -->\n";
			if (sql_query($query, $this->dbconn)) {
				if (sql_affected_rows($this->dbconn) == 1) {
					
					// return true;
					// We must also set the value in xmlData filed!
					soap__dbgOut( " Scorm_Tracking::setParam OK affected row = 1" ,SOAP_DBG_LEVEL_LOG,SOAP_DBG_FILTER_SETPARAM );
				} else {
					// mysql return 0 also when $fieldMap is already $paramval
					soap__dbgOut( " Scorm_Tracking::setParam affected row != 1" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
					if( sql_errno($this->dbconn) != 0 ) {
						soap__dbgOut( " Scorm_Tracking::setParam report Error" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
						$this->setError(1, "Scorm_Tracking::setParam 1 [ $query ] " . sql_error($this->dbconn));
						return false;
					}
				} 
			} else {
				soap__dbgOut( " Scorm_Tracking::setParam report Error" ,SOAP_DBG_LEVEL_ERROR,SOAP_DBG_FILTER_SETPARAM );
				$this->setError(1, "Scorm_Tracking::setParam 2 [ $query ] " . sql_error($this->dbconn));
				return false;
			}
			 				
		}
		
		$this->setXmlDoc($xmldoc);
		
	}
	
} 

?>
