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
 * @module CPManager.php
 *
 * This module is the content package manager of the Docebo Scorm
 *
 * @author Emanuele Sandri
 * @version $Id: CPManager.php 113 2006-03-08 18:08:42Z ema $
 * @copyright 2004
 **/

include_once( dirname(__FILE__).'/scorm_utils.php');


/**
 * @class CPManager
 **/
class CPManager {
	var $path = '';
	var $id = '';
	var $identifier = '';
	var $scorm_version = '0.0';
	
	var $errCode = 0;
 	var $errText = '';

	var $imsManifestDom;
	var $defaultOrg;
	var $orgElems;
	var $resElems;

	function Open( $path ) {
		$this->path = $path;
		return TRUE;
	}

	/**
	 * Return the content of the file imsmanifest.xml
	 * @return content of the imsmanifest.xml file as a string or FALSE
	 *  it there is any error.
	 */
	function ReadImsmanifest() {
		$filename = $this->path . '/imsmanifest.xml';
		
		if(strpos($filename, 'http') === 0) {
			
			// Remote manifest
			require_once(_lib_.'/lib.fsock_wrapper.php');
			$fsock = new FSock();
			$contents = $fsock->send_request($filename, '80', '');
			if(!$contents) {
				$this->setError( SPSCORM_E_FILENOTFOND, 'File: '. $this->path. '/imsmanifest.xml not found' );
				return FALSE;
			}
		} else {
			
			// Local manifest
			$handle = @fopen($filename, 'r');
			if( $handle === FALSE ) {
				$this->setError( SPSCORM_E_FILENOTFOND, 'File: '. $this->path. '/imsmanifest.xml not found' );
				return FALSE;
			}
			$contents = fread($handle, filesize($filename));
			fclose($handle);
		}
		return $contents;
	}
	
	/** Parse the imsmanifest.xml file contained in Content Package
	 *  @return TRUE if success, FALSE otherwise; use GetLastError to get the
	 *  last generated error
	 */
	function ParseManifest() {
		// Create an DomDocument object from XML file
		
		// first read Imsmanifest in a string
		$strImsmanifest = $this->ReadImsmanifest();
		if( $strImsmanifest === FALSE )
		    return FALSE;
		// the create xml document from memory
		$this->dom = new DDomDocument();
		$this->dom->loadXML($strImsmanifest);
		if($this->dom->isNULL()) {
        	$this->setError( SPSCORM_E_INVALIDMANIFEST, 'The imsmanifest of '. $this->path .' is not valid' );
			return FALSE;
		}

		$root = $this->dom->getDocumentElement();
		$this->identifier = $root->getAttribute('identifier');
		
		// now we get the schema version in metadata tag
		$metadata = $root->getElementByNameAttrib('metadata');
		if( $metadata === NULL ) {
			$this->scorm_version = '1.2';
		} else {
			$schemaversion = $metadata->getElementByNameAttrib('schemaversion');
			if($schemaversion) {
				$splat_version = $schemaversion->getContent();
				if( $splat_version == '1.2' )
					$this->scorm_version = '1.2';
				else
					$this->scorm_version = '1.3';
			} else {
				$this->scorm_version = '1.2';
			}
		}
		// now get all the organizations
		// to avoid problem with differents behaviors in different versions
		// of PHP we get organization tag elements starting from
		// direct parent organizations.
        $organizations = $root->getElementByNameAttrib('organizations');
		$this->orgElems = $organizations->getElementsByTagName( 'organization' );
		
        $this->defaultOrg = $organizations->getAttribute('default');
		if( $this->defaultOrg == '' ) 
			if( $this->orgElems->getLength() > 0 ) {
				$tmpItem = $this->orgElems->item(0);
				$this->defaultOrg = $tmpItem->getAttribute('identifier');
			} else
				$this->defaultOrg = '-resource-';

		// and now get the reference to the resources element
		$resources = $root->getElementByNameAttrib('resources');
		$this->resElems = $resources->getElementsByTagName( 'resource' );
		
		return TRUE;
	}
	
	/** This method return an organization based on their identifier
	 *  @param $identifier the identifier of the organization to be returned
	 *      if this parameter is an empty string the returned organization is
	 *      the default.
	 *  @return the requested organization, or the default organization if
	 *      the identifier param has not defined, or NULL if no organization
	 *      was found with passed identifier.
     */
	function GetOrganization( $identifier ) {
		for( $i = 0; $i < $this->orgElems->getLength(); $i++ ) {
			$tmpItem = $this->orgElems->item($i);
			if( $tmpItem->getAttribute('identifier') == $identifier )
			    return $tmpItem;
		}
		return NULL;
	}

	/** This method return a resource based on their identifier
	 *  @param $identifier the identifier of the reource to be returned
	 *  @return the requested reource, or NULL if no resource
	 *      was found with passed identifier.
     */
	function GetResource( $identifier ) {
		for( $i = 0; $i < $this->resElems->getLength(); $i++ ) {
			$tmpItem = $this->resElems->item($i);
			if( $tmpItem->getAttribute('identifier') == $identifier )
			    return $tmpItem;
		}
		return NULL;
	}
	
	function GetResourceInfo( $identifier ) {
		$res = $this->GetResource( $identifier );
		if( !$res ) {
			echo "<!-- Resource $identifier not found. \n";
			print_r($this->resElems);
			echo " -->\n";
			return;
		}
		$xmlbase = $res->getAttribute('base');
		if( $xmlbase == '' )
			$xmlbase = $res->getAttribute('xml:base');
		$info['href'] = $xmlbase.$res->getAttribute('href');
		
		if( $info['href'] == '' ) {
			$filetag = $res->getElementByNameAttrib('file');
			if( $filetag !== NULL ) {
				$info['href'] = $xmlbase.$filetag->getAttribute('href');
			}
		}
		$info['identifier'] = $res->getAttribute('identifier');
		$info['type'] = $res->getAttribute('type');
		$attrName = '';
		if( $this->scorm_version == '1.2' )
			$attrName = 'scormtype';
		else
			$attrName = 'scormType';
		
		$info['scormtype'] = $res->getAttribute($attrName);
		if( $info['scormtype'] == '' )
			$info['scormtype'] = $res->getAttribute('adlcp:'.$attrName);
		return $info;
	}
	
	function GetResourceNumber() {
		return $this->resElems->getLength();
	}
	
	function GetResourceIdentifier( $nRes ) {
		$tmpItem =& $this->resElems->item($nRes);
		return $tmpItem->getAttribute('identifier');
	}
	/**
	 * This function render the organization identified by $identifier using
	 *  the $renderer object; this must be inherited from RendererAbstract
	 *  and must implement all the functions declared in this class
	 *  @param $identifier idendifier of the organization to render
	 *  @param &$renderer object that render the organization
	 */
	function RenderOrganization( $identifier, &$renderer) {
		if( $identifier == '' || $identifier == '-resource-' ) {	// no organization => resources
			return $this->RenderResourceOrganization( $renderer); 
		} 
		$organization = $this->GetOrganization( $identifier );
		$itemInfo['identifier'] = $identifier;
		$itemInfo['isLast'] = TRUE;     // the organization is always the last
        $itemInfo['identifierref'] = FALSE;
        $itemInfo['isvisible'] = TRUE;
        $itemInfo['parameters'] = FALSE;
        
		$nodeTitle = $this->getFirstElementNode($organization, 'title');
		$title = $nodeTitle->getFirstChild();
		$itemInfo['title'] = $title->getContent();
        
		$elem = $this->getFirstElementNode( $organization, 'item' );
        if( $elem )
			$itemInfo['isLeaf'] = FALSE;
		else
		    $itemInfo['isLeaf'] = TRUE;

        $renderer->RenderStartItem( $this, $itemInfo );
        
		while( $elem ) {
			if( ($elem->getNodeType() == XML_ELEMENT_NODE) && ($elem->getNodeName() == "item") ) {
				$this->RenderItem( $elem, $renderer, true );
			}
			$elem = $elem->getNextSibling();
		}
		
		$renderer->RenderStopItem( $this, $itemInfo );
		
	}
	
	function RenderItem( &$item, &$renderer, $isLast ) {
		// collect infos about item
		$itemInfo['identifier'] = $item->getAttribute('identifier');
		
		$itemInfo['isLast'] = $isLast;
		
		if( $item->hasAttribute('identifierref') )
			$itemInfo['identifierref'] = $item->getAttribute('identifierref');
		else
		    $itemInfo['identifierref'] = FALSE;

		if( $item->hasAttribute('isvisible') )
		    $itemInfo['isvisible'] = ($item->getAttribute('isvisible')=='true')?TRUE:FALSE;
		else
		    $itemInfo['isvisible'] = TRUE;

		if( $item->hasAttribute('parameters') )
		    $itemInfo['parameters'] = $item->getAttribute('parameters');
		else
		    $itemInfo['parameters'] = FALSE;
		
		$itemInfo['title'] = $this->getFirstElementValue($item, 'title');
		
		$itemInfo['adlcp_prerequisites'] = $this->getFirstElementValue($item, 'prerequisites', 'adlcp' );
		$itemInfo['adlcp_maxtimeallowed'] = $this->getFirstElementValue($item, 'maxtimeallowed', 'adlcp');
		$itemInfo['adlcp_timelimitaction'] = $this->getFirstElementValue($item, 'timelimitaction', 'adlcp');
		$itemInfo['adlcp_datafromlms'] = $this->getFirstElementValue($item, 'datafromlms', 'adlcp');
		$itemInfo['adlcp_masteryscore'] = $this->getFirstElementValue($item, 'masteryscore', 'adlcp');
		//$itemInfo['adlcp_completionthreshold'] = $this->getFirstElementValue($item, 'completionthreshold', 'adlcp');
		$threshold_elem = $this->getNodeElement($item, 'completionthreshold', 'adlcp');
		if( $threshold_elem && $threshold_elem->hasAttribute('minProgressMeasure') )
		    $itemInfo['adlcp_completionthreshold'] = $threshold_elem->getAttribute('minProgressMeasure');
		else
		    $itemInfo['adlcp_completionthreshold'] = '';
		
        $elem = $this->getFirstElementNode( $item, 'item');
        if( $elem )
			$itemInfo['isLeaf'] = FALSE;
		else
		    $itemInfo['isLeaf'] = TRUE;
		    
		if( $renderer == null ) {
			print_r( $itemInfo );
			
		}
		$renderer->RenderStartItem(	$this, $itemInfo );
		
		while( $elem ) {
			$nextElem = $this->getNextElementNode( $elem );
			
			/* pass the info about the last element */
			if( $nextElem === NULL )
			    $this->RenderItem( $elem, $renderer, true );
			else
			    $this->RenderItem( $elem, $renderer, false );
			    
			$elem = $nextElem;
		}

        $renderer->RenderStopItem( $this, $itemInfo );

	}

	function RenderResourceOrganization( &$renderer) {
		$itemInfo['identifier'] = '-resource-';
		$itemInfo['isLast'] = TRUE;     // the organization is always the last
        $itemInfo['identifierref'] = FALSE;
        $itemInfo['isvisible'] = TRUE;
        $itemInfo['parameters'] = FALSE;
        
		$itemInfo['title'] = 'Lesson resources';
		$resNum = $this->GetResourceNumber();
        $itemInfo['isLeaf'] = ($resNum == 0);

        $renderer->RenderStartItem( $this, $itemInfo );
		
		for( $i = 0; $i < $resNum; $i++ ) {
			$resInfo = $this->GetResourceInfo( $this->GetResourceIdentifier($i) );
			
			// href, identifier, type, scormtype
			$resInfo['isLast'] = ($i == ($resNum-1));
			$resInfo['identifierref'] = $resInfo['identifier'];
		    $resInfo['isvisible'] = TRUE;
			$resInfo['parameters'] = FALSE;
			$resInfo['title'] = $resInfo['identifier'];
			$resInfo['adlcp_prerequisites'] = '';
			$resInfo['adlcp_maxtimeallowed'] = '';
			$resInfo['adlcp_timelimitaction'] = '';
			$resInfo['adlcp_datafromlms'] = '';
			$resInfo['adlcp_masteryscore'] = '';
			$resInfo['isLeaf'] = TRUE;
				
			$renderer->RenderStartItem(	$this, $resInfo );
			$renderer->RenderStopItem( $this, $resInfo );
			
		}
		
		$renderer->RenderStopItem( $this, $itemInfo );
		
	}
	
	/**
	 * Return the first element in the next deep level
	 *	of that passed and with tag equal to $tagname
	 *  @param &$elem reference to current element
	 *  @param $tagname the tag of desired element
	 *  @return next sibling elment or NULL if not find
	 */
	function getFirstElementNode( &$elem, $tagname = '') {
		$nextElem = $elem->getFirstChild();

		while( $nextElem ) {
			if( ($nextElem->getNodeType() == XML_ELEMENT_NODE) ) { // only node elements
				if( $tagname != '' ) {
					if(strtolower($nextElem->getNodeName()) == $tagname)
						return $nextElem;
				} else {
                    return $nextElem;
				}
			}
			$nextElem = $nextElem->getNextSibling();
		}
	    return NULL;
	}
	
	/**
	 * Return the next element in the same level of that passed
	 *  and with node_name equal to $nodename
	 *  @param &$elem reference to current element
	 *  @param $sameTag the element retured must have same tag of $elem
	 *  @return next sibling elment or NULL if not find
	 */
	function getNextElementNode( &$elem, $sameTag = TRUE ) {
		$nextElem = &$elem->getNextSibling();
		
		while( $nextElem ) {
			if( ($nextElem->getNodeType() == XML_ELEMENT_NODE) ) { // only node elements
				if( $sameTag ) {
					if($nextElem->getNodeName() == $elem->getNodeName())
						return $nextElem;
				} else {
                    return $nextElem;
				}
			}
			$nextElem = $nextElem->getNextSibling();
		}
	    return NULL;
	}
	
	/**
	 * Return the value of the first element in the next deep level
	 *	of that passed and with tag equal to $tagname
	 *  @param &$elem reference to current element
	 *  @param $tagname the tag of desired element
	 *  @return next sibling elment or NULL if not find
	 */
	function getFirstElementValue( &$elem, $tagname = '', $prefix = '') {
		$nextElem = $elem->getFirstChild();
		$node = NULL;

		if( $GLOBALS['xmlv'] != XMLV4 && $prefix != '' )
			$tagname = $prefix.':'.$tagname;
			
		while( $nextElem ) {
			if( ($nextElem->getNodeType() == XML_ELEMENT_NODE) ) { // only node elements
				if( $tagname != '' ) {
					if(strtolower($nextElem->getNodeName()) == $tagname) {
						$node = $nextElem;
						break;
					}
				} else {
                    $node = $nextElem;
					break;
				}
			}
			$nextElem = $nextElem->getNextSibling();
		}
		if( $node != NULL ) {
			$elemValue = $node->getFirstChild();
			if( $elemValue != NULL )
				return $elemValue->getContent();
		}
	    return NULL;
	}

	/**
	 * Return the node requested with tag equal to $tagname
	 *  @param &$elem reference to current element
	 *  @param $tagname the tag of desired element
	 *  @return the searched element
	 */
	function getNodeElement( &$elem, $tagname = '', $prefix = '') {
		$nextElem = $elem->getFirstChild();
		$node = NULL;

		if( $GLOBALS['xmlv'] != XMLV4 && $prefix != '' )
			$tagname = $prefix.':'.$tagname;

		while( $nextElem ) {
			if( ($nextElem->getNodeType() == XML_ELEMENT_NODE) ) { // only node elements
				if( $tagname != '' ) {
					if(strtolower($nextElem->getNodeName()) == $tagname) {
						$node = $nextElem;
						break;
					}
				} else {
                    $node = $nextElem;
					break;
				}
			}
			$nextElem = $nextElem->getNextSibling();
		}
		return $node;
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
