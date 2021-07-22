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
 * @module sorm_utils.php
 * Utilities functions
 *
 * @version $Id: scorm_utils.php 469 2006-07-21 09:33:46Z fabio $
 * @copyright 2004 
 **/

//include_once( $GLOBALS['where_lms'] . '/config.php' );
require_once Forma::inc(_lms_. '/modules/scorm/config.scorm.php');
 
/*
 * Cerca tra gli elementi del nodo il primo elemento che ha tag $name
 * ed un attributo con nome $attribname e valore $attribvalue 
 * se $attribname == "" && $attribvalue == "" non c'e' ricerca tra gli 
 * attributi.
 * Torna l'elemento trovato o NULL.
 */
function todelete_getelementbynameattrib($node, $name, $attribname = "", $attribvalue = ""){
/*	soap__dbgOut("+getelementbynameattrib( ".$node->tagName.", $name, $attribname , $attribvalue)");
	$elem = $node->firstChild;
	soap__dbgOut(" getelementbynameattrib class ".get_class($elem));
	while( $elem ) {
		soap__dbgOut(" getelementbynameattrib new step");
		$elem->nodeType;
		soap__dbgOut(" getelementbynameattrib process ".$elem->nodeType);
		if( ($elem->nodeType == XML_ELEMENT_NODE) && ($elem->nodeName == $name) ) {
			if( $attribname != "" ) {
				if( $elem->getAttribute($attribname) == $attribvalue) 
					break;	// trovato
			} else 
				break; // trovato
		}
		$nextelem = $elem->nextSibling;	// successivo elemento
		soap__dbgOut(" getelementbynameattrib ".get_class($nextelem));
		$elem = $nextelem;
		soap__dbgOut(" getelementbynameattrib ".get_class($elem));
	}
	soap__dbgOut("-getelementbynameattrib");
	return $elem;*/
	soap__dbgOut("+getelementbynameattrib node=".print_r($node,true).", name=$name, attribname=".print_r($attribname,true).", attribvalue=$attribvalue");
	$xpath = new DoceboDOMXPath($node->ownerDocument);
	$result = $xpath->query("*",$node);
	$elem = NULL;
	for( $iResult = 0; $iResult < $result->length; $iResult++ ) {
		soap__dbgOut(" getelementbynameattrib process:".$iResult);
		$elem = $result->item($iResult);
		soap__dbgOut(" getelementbynameattrib process:".print_r($elem,true));
		if( $elem->nodeName == $name ) {
			soap__dbgOut(" getelementbynameattrib name ok:".$elem->nodeName);
			if( $attribname != "" ) {
				soap__dbgOut(" getelementbynameattrib test for attribute: ".$attribname);
				if( $elem->getAttribute($attribname) == $attribvalue) {
					soap__dbgOut(" getelementbynameattrib found:".$elem->nodeName);
					break;	// trovato
				}
			} else { 
				soap__dbgOut(" getelementbynameattrib found:".$elem->nodeName);
				break; // trovato
			}
		}
		$elem = NULL;
	}
	return $elem;
	soap__dbgOut("-getelementbynameattrib");

	/*$xpath = new DoceboDOMXPath($node->ownerDocument);
	$query = "child::".$name;
	if( $attribname != "" ) {
		$query .= '[@'.$attribname.' = "'.$attribvalue.'"]';
	}
	if( $node->nodeType == XML_ELEMENT_NODE) {
		$result = $xpath->query($query,$node);
	} else {
		$result = $xpath->query($query);
	}

	if( isset($result->length) && $result->length > 0 ) {
		$return = $result->item(0);
		return $return;
	} 
	
	return NULL;*/
}

function translateParam($parampath)
{
	$path = explode('.', $parampath);
	return $path;
} 


function getXmlDocTrackingTemplate($scormVersion) {
	soap__dbgOut("+getXmlDocTrackingTemplate");
	//$pathToFile = $_SERVER['PATH_TRANSLATED'];
	//$pathToFile = stripslashes(substr($pathToFile, 0, strrpos($pathToFile, '\\')+1));
	//$pathToFile = 'trackingdatamodel-1.2.xml';
	//$pathToFile = 'modules/scorm/trackingdatamodel-1.2.xml';
    $pathToFile = Forma::inc(_lms_ . '/modules/scorm/trackingdatamodel-'.$scormVersion.'.xml');
	$xmldoc = new DDOMDocument();
	$xmldoc->loadXML(file_get_contents( $pathToFile ));
		/*or die("Error opening template file: $pathToFile<br/>"
		."Directory di lavoro: ". getcwd());*/
	soap__dbgOut("-getXmlDocTrackingTemplate");
	return $xmldoc;
}

function getXmlElementTrackingTempalte(&$base, $pathelem) {
	soap__dbgOut("+getXmlElementTrackingTempalte");
	$path = translateParam($pathelem);
	$elem = $base;
	$increment = 1;
	for($i = 0; $i < count($path) && $elem; $i += $increment) {
		$step = $path[$i];
		if ($i < (count($path)-1))
			$next = $path[$i + 1];
		else
			$next = "";
		$index = "";
		$increment = 1; 
		
		// Virtual elements
		if( $step == '_count' ||	$step == '_children') {
			soap__dbgOut("-getXmlElementTrackingTempalte return false");
			return false;
		}
		// if next step is a number the element to find is an
		// element of a list
		if (is_numeric($next)) {
			$index = $next;
			$increment = 2;
			$elem =& $elem->getElementByNameAttrib($step, "index", "yes");
		} else {
			$elem =& $elem->getElementByNameAttrib($step );
		}
	}
	soap__dbgOut("-getXmlElementTrackingTempalte");
	return $elem;
}

function getElementsArrayTrackingTemplate($pathelem, $scormVersion) {
	soap__dbgOut("+getElementsArrayTrackingTemplate($pathelem)");
	$path = translateParam($pathelem);
	$xmldoc = getXmlDocTrackingTemplate($scormVersion);
	$elem = $xmldoc->getDocumentElement();
	$increment = 1;
	$resultArray = array();
	for($i = 0; $i < count($path) && $elem; $i += $increment) {
		$step = $path[$i];
		soap__dbgOut(" getElementsArrayTrackingTemplate procesing step = $step");
		if ($i < (count($path)-1))
			$next = $path[$i + 1];
		else
			$next = "";
		$index = "";
		$increment = 1; 
		
		// Virtual elements
		switch( $step ) {
			case '_count':
				if( strlen($next) > 0 ) { 
					$resultArray[] = array($step, false); // after _count can't be anything
				} elseif( $elem->getAttribute('index') != 'yes' ) {
					$resultArray[] = array($step, false); // count on an uncountable element 
				} else {
					$resultArray[] = array($step, true); // ok 
				}
				return $resultArray;
			break;
			case '_children':
				if( strlen($next) > 0 ) {
					$resultArray[] = array($step, false); // after _children can't be anything
				} elseif( $elem->getAttribute('item') == 'yes' ) {
					$resultArray[] = array($step, false); // can't get children of a terminal element
				} else {
					$resultArray[] = array($step, true); // ok
				}
				return $resultArray;
			break;
			case '_version':
				$step = 'version'; // the element name in template is 'version'
			break;
		}

		// if next step is a number the element to find is an
		// element of a list
		if (is_numeric($next)) {
			$index = $next;
			$increment = 2;
			$name = $index;
			$elem =& $elem->GetElementByNameAttrib($step, "index", "yes");
		} elseif (strlen($next)>0) {
			// it's not the last element
			soap__dbgOut(" getElementsArrayTrackingTemplate search $step elem in ". $elem->getTagName());
			$elem = $elem->GetElementByNameAttrib($step, "item", "no" );
			//soap__dbgOut(" getElementsArrayTrackingTemplate elem tag ". $elem->tagName);
			$name = $step;
		} else {
			// last element
			$elem = $elem->GetElementByNameAttrib($step, "item", "yes" );
			$name = $step;
		}
		if( $elem )
			$resultArray[]=array($name,$elem);
		else
			$resultArray[]=array($name,false);
	}
	soap__dbgOut("-getElementsArrayTrackingTemplate");
	return $resultArray;
}

/**
 * Create the run-time environment
 * @param $idUser id of the user
 * @param $idReference
 * @param $scoid id of the sco (obsolete) set to FALSE
 * @param $idscorm_organization
 * @param $cpid id of the content package
 * @param $scormws web service for scorm 
 * 			(e.g. "http://127.0.0.1/splearning/modules/scorm/soaplms.php" )
 * @param $scormserviceid identifier of the service (e.g. "urn:SOAPLMS")
 */
function createSCORM_RunTimeEnvironment( $idUser, $idReference, $idscorm_organization, $scormws, $scormserviceid ){
	$debugScormAPI = false;
	if( $debugScormAPI ) {
		echo '<div id="dbgWindow" style="overflow:scroll;width:390;height:400">';
		echo '</div>';
	}
	
	echo '<SCRIPT type="text/javascript" language="JavaScript" src="modules/scorm5/ScormApi.js"></SCRIPT>'."\n";
	echo '<SCRIPT type="text/javascript" language="JavaScript">'."\n";
	echo '<!--'."\n";
	echo 'window.API = new ScormApiUI( "'. $_SERVER['HTTP_HOST'] .'",'
		.'"'. $scormws. '",'
		.'"'. $scormserviceid. '",' 
		.'"'. $idUser. '",' 
		.'"'. $idReference. '",' 
		.'"'. $idscorm_organization. '");'."\n";
		
	echo 'window.parent.API = window.API;'."\n";
	echo 'window.top.API = window.API;'."\n";
	if( $debugScormAPI ) {
		echo 'window.API.dbgLevel = 1;';
		echo 'window.API.dbgOut = document.getElementById("dbgWindow");';
	}
	/*echo 'function LoadSco( scoid, itemid, orgid ) { '."\n";
	echo '	window.API.setScoid( scoid );'."\n";
	echo '	window.API.setItemid( itemid );'."\n";
	echo '	window.API.setOrgid( orgid );'."\n";
	echo '	window.open( "index.php?op=scoload&iduser='.$idUser.'&scoid=" + scoid + "&itemid=" + itemid + "&orgid=" + orgid + "&idscormpackage='.$idscormpackage.'" );'."\n";
	echo '}'."\n";*/
	echo ' // -->'."\n";
	echo '</SCRIPT>'."\n";
}

function sumScormTime( $t1, $t2 ) {
	// HHHH:MM::SS.SS
	
	if(strpos($t1, "P") === false) {
		
		$a1 = explode(":", $t1);
		$h1 = $a1[0];
		$m1 = $a1[1];
		$pos = strpos($a1[2], ".");
		if( $pos === FALSE ) {
			$s1 = $a1[2];
			$c1 = 0;
		} else {
			$s1 = substr($a1[2], 0, $pos);
			$c1 = substr($a1[2], $pos+1);
		}
		
		$a2 = explode(":", $t2);
		$h2 = $a2[0];
		$m2 = $a2[1];
		$pos = strpos($a2[2], ".");
		if( $pos === FALSE ) {
			$s2 = $a2[2];
			$c2 = 0;
		} else {
			$s2 = substr($a2[2], 0, $pos);
			$c2 = substr($a2[2], $pos+1);
		}
		
		$h = 0;
		$m = 0;
		$s = 0;
		$c = 0;
		
		$c += $c1 + $c2;
		if( $c >= 100 ) {
			$s++;
			$c -= 100;
		}
		$s += $s1 + $s2;
		if( $s >= 60 ) {
			$m++;
			$s -= 60;
		}
		$m += $m1 + $m2;
		if( $m >= 60 ) {
			$h++;
			$m -= 60;
		}
		$h += $h1 + $h2;
		
		return sprintf("%04u:%02u:%02u.%02u", $h, $m, $s, $c);
	} else {
		
		// scorm 2004 format, break it
		if($t2 == '' || $t2 == '0000:00:00.00') $t2 = 'P0000Y00M00DT0000H00M00S'; 
		
		$t1_s = array();
		$t2_2 = array();
		$re1 = preg_match ('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $t1, $t1_s );
		if(strpos($t2, "P") === false) {
			$a2 = explode(":", $t2);
			$t2_s[9] = $a2[0];
			$t2_s[11] = $a2[1];
			$pos = strpos($a2[2], ".");
			if( $pos === FALSE ) {
				$t2_s[13] = $a2[2];
				$t2_s[15] = 0;
			} else {
				$t2_s[13] = substr($a2[2], 0, $pos);
				$t2_s[15] = substr($a2[2], $pos+1);
			}
			$re2 = true;
		}
		else $re2 = preg_match ('/^P((\d*)Y)?((\d*)M)?((\d*)D)?(T((\d*)H)?((\d*)M)?((\d*)(\.(\d{1,2}))?S)?)?$/', $t2, $t2_s );
		
		
		if(!$re1 || !$re2) return $t2;
		
		if(!isset($t1_s[15])) $t1_s[15] = 0;
		if(!isset($t1_s[13])) $t1_s[13] = 0;
		if(!isset($t1_s[11])) $t1_s[11] = 0;
		if(!isset($t1_s[9])) $t1_s[9] = 0;
		
		if(!isset($t2_s[15])) $t2_s[15] = 0;
		if(!isset($t2_s[13])) $t2_s[13] = 0;
		if(!isset($t2_s[11])) $t2_s[11] = 0;
		if(!isset($t2_s[9])) $t2_s[9] = 0;
		
		$tot['cent'] 	= $t1_s[15] + $t2_s[15];
		if($tot['cent'] >= 100) {
			$remainder = floor($tot['cent']/100);
			$tot['cent'] = floor($tot['cent'] % 100 ); 
		} else $remainder = 0;
		
		$tot['second'] 	= $t1_s[13] + $t2_s[13] + $remainder;
		if($tot['second'] >= 60) {
			$remainder = floor($tot['second']/60);
			$tot['second'] = floor($tot['second'] % 60 ); 
		} else $remainder = 0;
		
		$tot['minute'] 	= $t1_s[11] + $t2_s[11] + $remainder;
		if($tot['minute'] >= 60) {
			$remainder = floor($tot['minute']/60);
			$tot['minute'] = floor($tot['minute'] % 60 ); 
		} else $remainder = 0;
		
		$tot['hour'] 	= $t1_s[9] + $t2_s[9] + $remainder;
		
		return sprintf("PT%04uH%02uM%02uS", $tot['hour'], $tot['minute'], $tot['second'], $tot['cent']);
	}
}

function delDirTree( $path ) {
	
	$dirobj = dir($path);
	while( false !== ($entry = $dirobj->read())) {
		if (($entry!=".")&&($entry!="..")) {
			$currpath = $dirobj->path.'/'.$entry;
			if( is_dir($currpath) ) {
				delDirTree($currpath);
			} else {
				unlink($currpath);
			}
		}
	}
	$dirobj->close();
	rmdir($path);
}

/**
 * isTrackingAvailable()
 * 
 * @param $idscorm_package
 * @return boolean TRUE if there are some tracking for this package,
 * 			FALSE otherwise
 **/
function isTrackingAvailable( $idscorm_package, $idProg ) { 
	if( $idscorm_package == NULL ) {
		$query = "SELECT st.idscorm_tracking"
				." FROM ".$GLOBALS['prefix_lms']."_scorm_tracking st,".$GLOBALS['prefix_lms']."_scorm_resources sr,".$GLOBALS['prefix_lms']."_scorm_package sp"
				." WHERE sp.idProg=".$idProg
				." AND sp.idscorm_package=sr.idscorm_package"
				." AND sr.idscorm_resource=st.idscorm_resource";
	} else {
		$query = "SELECT st.idscorm_tracking"
				." FROM ".$GLOBALS['prefix_lms']."_scorm_tracking st,".$GLOBALS['prefix_lms']."_scorm_resources sr"
				." WHERE sr.idscorm_package=".$idscorm_package 
				." AND sr.idscorm_resource=st.idscorm_resource";
	}
	// debug echo "<!-- isTrackingAvailable:Query = $query -->";
	$rs = sql_query($query) or die(sql_error());
	return (sql_num_rows($rs)>0)?TRUE:FALSE;
}

/*
 * this function recover the actual scorm version from varius id
 */
function getScormVersion( $idtype, $id) {
	$query = '';
	switch( $idtype ) {
		case 'idscorm_package':
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package'
					.' WHERE idscorm_package='.$id;
			break;
		case 'scorm_package_path': /*slow*/
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package'
					.' WHERE path='.$id;
			break;
		case 'idscorm_organization':
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package as lp, '
					          .$GLOBALS['prefix_lms'].'_scorm_organizations as lo'
					.' WHERE idscorm_organization='.$id
					.'   AND lp.idscorm_package=lo.idscorm_package';
			break;
		case 'idscorm_item':
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package as lp, '
							  .$GLOBALS['prefix_lms'].'_scorm_organizations as lo, '
							  .$GLOBALS['prefix_lms'].'_scorm_items as li '							  
					.' WHERE idscorm_item='.$id
					.'   AND lo.idscorm_organization=li.idscorm_organization'
					.'   AND lp.idscorm_package=lo.idscorm_package';
			break;
		case 'idscorm_item_track':
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package as lp, '
							  .$GLOBALS['prefix_lms'].'_scorm_organizations as lo, '
							  .$GLOBALS['prefix_lms'].'_scorm_items_track as lit '							  
					.' WHERE idscorm_item_track='.$id
					.'   AND lo.idscorm_organization=lit.idscorm_organization'
					.'   AND lp.idscorm_package=lo.idscorm_package';			
			break;
		case 'idscorm_tracking':
			$query = 'SELECT scormVersion ' 
					.'  FROM '.$GLOBALS['prefix_lms'].'_scorm_package as lp, '
							  .$GLOBALS['prefix_lms'].'_scorm_organizations as lo, '
							  .$GLOBALS['prefix_lms'].'_scorm_items_track as lit, '
							  .$GLOBALS['prefix_lms'].'_scorm_tracking as ltr '
					.' WHERE ltr.idscorm_tracking='.$id
					.'   AND lit.idscorm_tracking=ltr.idscorm_tracking'
					.'   AND lo.idscorm_organization=lit.idscorm_organization'
					.'   AND lp.idscorm_package=lo.idscorm_package';			
			break;
		default:
			return FALSE;
	}
	$rs = sql_query($query);
	if( sql_num_rows($rs) != 1 )
		return FALSE;
	list($scormVersion) = sql_fetch_row($rs);
	
	return $scormVersion;
}

function getPackIdOrgIdFromProgId( $progId, $prefix ) {
	$query = "SELECT so.idscorm_package, so.idscorm_organization "
			." FROM ".$prefix."_scorm_package sp"
			.",".$prefix."_scorm_organizations so"
			." WHERE sp.idProg=".$progId
			." AND sp.idscorm_package=so.idscorm_package"
			." AND sp.defaultOrg=so.org_identifier";
	$rs = sql_query($query) or die(sql_error());
	return sql_fetch_row($rs);
}

global $scorm_pre_patterns;
$scorm_pre_patterns[] = '/~/';
$scorm_pre_patterns[] = '/&/';
$scorm_pre_patterns[] = '/\|/';
$scorm_pre_patterns[] = '/(?<=[\x2C&|!({^])\s*([a-z,A-Z,0-9,_,\-]+)\s*(?=[\x2C}&|!);])/';
$scorm_pre_patterns[] = '/([a-z,A-Z,0-9,_,\-]+)\s*=\s*"([a-z,A-Z,0-9,_,\-]+)"/';
$scorm_pre_patterns[] = '/([a-z,A-Z,0-9,_,\-]+)\s*<>\s*"([a-z,A-Z,0-9,_,\-]+)"/';
$scorm_pre_patterns[] = '/(\d+)\s*\*\s*\{(.*)\}/';

global $scorm_pre_replacements;
$scorm_pre_replacements[] = '!';
$scorm_pre_replacements[] = '&&';
$scorm_pre_replacements[] = '||';
$scorm_pre_replacements[] = '$this->isOk_itemid("\\1")';
$scorm_pre_replacements[] = '$this->getValue_itemid("\\1")=="\\2"';
$scorm_pre_replacements[] = '$this->getValue_itemid("\\1")!="\\2"';
$scorm_pre_replacements[] = '$this->testArray_itemid(\\1, array(\\2))';

class SCORM12_Sequencing {

	var $idUser;
	var $idReference;
	var $idscorm_organization;
	var $dbconn;
	var $prefix;
	
	function SCORM12_Sequencing( $idReference, $idUser, $idscorm_organization, $dbconn, $prefix ) {
		if( $idUser === FALSE ) 
			$this->idUser = sl_sal_getUserId();
		else 
			$this->idUser = $idUser;
		
		$this->idReference= $idReference;
		$this->idscorm_organization = $idscorm_organization;
		$this->dbconn = $dbconn;
		$this->prefix = $prefix;
	}

	function isOk_itemid( $itemid ) {
		//$status = $this->merge_status($this->getValue_itemid( $itemid ));
		$status = $this->getValue_itemid( $itemid );
		if ( (strcmp($status,"completed")==0) || (strcmp($status,"passed") == 0) ) {
		    return TRUE;
		} else {
			return FALSE;
		}
	}
	
	function merge_status( $arrStatus ) {
		// debug echo "\n<!-- arrStatus ";
		// debug print_r($arrStatus);
		// debug echo "\n -->";
		if( !is_array($arrStatus) ) 
			return $arrStatus;

		foreach( $arrStatus as $currStatus ) {
			if( (strcmp($currStatus,"completed")!=0) && (strcmp($currStatus,"passed") != 0) )
				return "incomplete";
		}
		return "completed";
	}
	
	function getValue_itemid( $itemid, $idscorm_item = NULL ) {
		/* new implementation in version 2.0 and later */
		if( $idscorm_item === NULL ) {
			$query = "SELECT sit.status"
					." FROM ".$this->prefix."_scorm_items_track sit, ".$this->prefix."_scorm_items si"
					." WHERE sit.idscorm_item = si.idscorm_item"
					."   AND sit.idUser = '".(int)$this->idUser."'"
					."   AND sit.idReference = '".(int)$this->idReference."'"
					."   AND si.item_identifier = '".$itemid."'";
		} else {
			$query = "SELECT status"
					." FROM ".$this->prefix."_scorm_items_track"
					." WHERE idUser = '".(int)$this->idUser."'"
					."   AND idReference = '".(int)$this->idReference."'"
					."   AND idscorm_item = '".(int)$idscorm_item."'";
		}
		$rs = sql_query( $query, $this->dbconn );
		list( $status ) = sql_fetch_row( $rs );
		return $status;
	}
	
	function testArray_itemid( $nComplete, $array_values ) {
		$nTotComplete = 0;
		foreach( $array_values as $val ) {
			if( $val )
			    $nTotComplete++;
		}
		return $nTotComplete >= $nComplete;
	}
			
	function evauatePrerequisites( $prerequisites ) {
		global $scorm_pre_replacements,$scorm_pre_patterns;
		// debug echo "\n<!-- evauatePrerequisites $prerequisites\n";
		// debug print_r($scorm_pre_replacements);
		// debug print_r($scorm_pre_patterns);
		// debug echo " -->";
		$cmdString = 'return ' . preg_replace( $scorm_pre_patterns , $scorm_pre_replacements, '('.$prerequisites.');');
//echo "<!-- command string is $cmdString -->";
		return eval($cmdString);
	}

}


?>
