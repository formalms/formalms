<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

include_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/scorm/scorm_utils.php');

/**
 * @class CPManager
 **/
class CPManager
{
    public $path = '';
    public $id = '';
    public $identifier = '';
    public $scorm_version = '0.0';

    public $errCode = 0;
    public $errText = '';

    public $imsManifestDom;
    public $defaultOrg;
    public $orgElems;
    public $resElems;
    public DDomDocument $dom;

    public function Open($path)
    {
        $this->path = $path;

        return true;
    }

    /**
     * Return the content of the file imsmanifest.xml.
     *
     * @return content of the imsmanifest.xml file as a string or FALSE
     *                 it there is any error.
     */
    public function ReadImsmanifest()
    {
        $filename = $this->path . '/imsmanifest.xml';

        if (strpos($filename, 'http') === 0) {
            // Remote manifest
            require_once \FormaLms\lib\Forma::inc(_lib_ . '/lib.fsock_wrapper.php');
            $fsock = new FSock();
            $contents = $fsock->send_request($filename, '80', '');
            if (!$contents) {
                $this->setError(SPSCORM_E_FILENOTFOND, 'File: ' . $this->path . '/imsmanifest.xml not found');

                return false;
            }
        } else {
            // Local manifest
            $handle = @fopen($filename, 'r');
            if ($handle === false) {
                $this->setError(SPSCORM_E_FILENOTFOND, 'File: ' . $this->path . '/imsmanifest.xml not found');

                return false;
            }
            $contents = fread($handle, filesize($filename));
            fclose($handle);
        }

        return $contents;
    }

    /** Parse the imsmanifest.xml file contained in Content Package.
     *  @return true if success, FALSE otherwise; use GetLastError to get the
     *  last generated error
     */
    public function ParseManifest()
    {
        // Create an DomDocument object from XML file

        // first read Imsmanifest in a string
        $strImsmanifest = $this->ReadImsmanifest();
        if ($strImsmanifest === false) {
            return false;
        }
        // the create xml document from memory
        $this->dom = new DDomDocument();
        $this->dom->loadXML($strImsmanifest);
        if ($this->dom->isNULL()) {
            $this->setError(SPSCORM_E_INVALIDMANIFEST, 'The imsmanifest of ' . $this->path . ' is not valid');

            return false;
        }

        $root = $this->dom->getDocumentElement();
        $this->identifier = $root->getAttribute('identifier');

        // now we get the schema version in metadata tag
        $metadata = $root->getElementByNameAttrib('metadata');
        if ($metadata === null) {
            $this->scorm_version = '1.2';
        } else {
            $schemaversion = $metadata->getElementByNameAttrib('schemaversion');
            if ($schemaversion) {
                $splat_version = $schemaversion->getContent();
                if ($splat_version == '1.2') {
                    $this->scorm_version = '1.2';
                } else {
                    $this->scorm_version = '1.3';
                }
            } else {
                $this->scorm_version = '1.2';
            }
        }
        // now get all the organizations
        // to avoid problem with differents behaviors in different versions
        // of PHP we get organization tag elements starting from
        // direct parent organizations.
        $organizations = $root->getElementByNameAttrib('organizations');
        $this->orgElems = $organizations->getElementsByTagName('organization');

        $this->defaultOrg = $organizations->getAttribute('default');
        if ($this->defaultOrg == '') {
            if ($this->orgElems->getLength() > 0) {
                $tmpItem = $this->orgElems->item(0);
                $this->defaultOrg = $tmpItem->getAttribute('identifier');
            } else {
                $this->defaultOrg = '-resource-';
            }
        }

        // and now get the reference to the resources element
        $resources = $root->getElementByNameAttrib('resources');
        $this->resElems = $resources->getElementsByTagName('resource');

        return true;
    }

    /** This method return an organization based on their identifier.
     *  @param $identifier the identifier of the organization to be returned
     *      if this parameter is an empty string the returned organization is
     *      the default
     *
     *  @return the requested organization, or the default organization if
     *      the identifier param has not defined, or NULL if no organization
     *      was found with passed identifier
     */
    public function GetOrganization($identifier)
    {
        for ($i = 0; $i < $this->orgElems->getLength(); ++$i) {
            $tmpItem = $this->orgElems->item($i);
            if ($tmpItem->getAttribute('identifier') == $identifier) {
                return $tmpItem;
            }
        }

        return null;
    }

    /** This method return a resource based on their identifier.
     *  @param $identifier the identifier of the reource to be returned
     *
     *  @return the requested reource, or NULL if no resource
     *      was found with passed identifier
     */
    public function GetResource($identifier)
    {
        for ($i = 0; $i < $this->resElems->getLength(); ++$i) {
            $tmpItem = $this->resElems->item($i);
            if ($tmpItem->getAttribute('identifier') == $identifier) {
                return $tmpItem;
            }
        }

        return null;
    }

    public function GetResourceInfo($identifier)
    {
        $res = $this->GetResource($identifier);
        if (!$res) {
            echo "<!-- Resource $identifier not found. \n";
            print_r($this->resElems);
            echo " -->\n";

            return;
        }
        $xmlbase = $res->getAttribute('base');
        if ($xmlbase == '') {
            $xmlbase = $res->getAttribute('xml:base');
        }
        $info['href'] = $xmlbase . $res->getAttribute('href');

        if ($info['href'] == '') {
            $filetag = $res->getElementByNameAttrib('file');
            if ($filetag !== null) {
                $info['href'] = $xmlbase . $filetag->getAttribute('href');
            }
        }
        $info['identifier'] = $res->getAttribute('identifier');
        $info['type'] = $res->getAttribute('type');
        $attrName = '';
        if ($this->scorm_version == '1.2') {
            $attrName = 'scormtype';
        } else {
            $attrName = 'scormType';
        }

        $info['scormtype'] = $res->getAttribute($attrName);
        if ($info['scormtype'] == '') {
            $info['scormtype'] = $res->getAttribute('adlcp:' . $attrName);
        }

        return $info;
    }

    public function GetResourceNumber()
    {
        return $this->resElems->getLength();
    }

    public function GetResourceIdentifier($nRes)
    {
        $tmpItem = &$this->resElems->item($nRes);

        return $tmpItem->getAttribute('identifier');
    }

    /**
     * This function render the organization identified by $identifier using
     *  the $renderer object; this must be inherited from RendererAbstract
     *  and must implement all the functions declared in this class.
     *
     *  @param $identifier idendifier of the organization to render
     *  @param &$renderer object that render the organization
     */
    public function RenderOrganization($identifier, &$renderer)
    {
        if ($identifier == '' || $identifier == '-resource-') {	// no organization => resources
            return $this->RenderResourceOrganization($renderer);
        }
        $organization = $this->GetOrganization($identifier);
        $itemInfo['identifier'] = $identifier;
        $itemInfo['isLast'] = true;     // the organization is always the last
        $itemInfo['identifierref'] = false;
        $itemInfo['isvisible'] = true;
        $itemInfo['parameters'] = false;

        $nodeTitle = $this->getFirstElementNode($organization, 'title');
        $title = $nodeTitle->getFirstChild();
        $itemInfo['title'] = $title->getContent();

        $elem = $this->getFirstElementNode($organization, 'item');
        if ($elem) {
            $itemInfo['isLeaf'] = false;
        } else {
            $itemInfo['isLeaf'] = true;
        }

        $renderer->RenderStartItem($this, $itemInfo);

        while ($elem) {
            if (($elem->getNodeType() == XML_ELEMENT_NODE) && ($elem->getNodeName() == 'item')) {
                $this->RenderItem($elem, $renderer, true);
            }
            $elem = $elem->getNextSibling();
        }

        $renderer->RenderStopItem($this, $itemInfo);
    }

    public function RenderItem(&$item, &$renderer, $isLast)
    {
        // collect infos about item
        $itemInfo['identifier'] = $item->getAttribute('identifier');

        $itemInfo['isLast'] = $isLast;

        if ($item->hasAttribute('identifierref')) {
            $itemInfo['identifierref'] = $item->getAttribute('identifierref');
        } else {
            $itemInfo['identifierref'] = false;
        }

        if ($item->hasAttribute('isvisible')) {
            $itemInfo['isvisible'] = ($item->getAttribute('isvisible') == 'true') ? true : false;
        } else {
            $itemInfo['isvisible'] = true;
        }

        if ($item->hasAttribute('parameters')) {
            $itemInfo['parameters'] = $item->getAttribute('parameters');
        } else {
            $itemInfo['parameters'] = false;
        }

        $itemInfo['title'] = $this->getFirstElementValue($item, 'title');

        $itemInfo['adlcp_prerequisites'] = $this->getFirstElementValue($item, 'prerequisites', 'adlcp');
        $itemInfo['adlcp_maxtimeallowed'] = $this->getFirstElementValue($item, 'maxtimeallowed', 'adlcp');
        $itemInfo['adlcp_timelimitaction'] = $this->getFirstElementValue($item, 'timelimitaction', 'adlcp');
        $itemInfo['adlcp_datafromlms'] = $this->getFirstElementValue($item, 'datafromlms', 'adlcp');
        $itemInfo['adlcp_masteryscore'] = $this->getFirstElementValue($item, 'masteryscore', 'adlcp');
        //$itemInfo['adlcp_completionthreshold'] = $this->getFirstElementValue($item, 'completionthreshold', 'adlcp');
        $threshold_elem = $this->getNodeElement($item, 'completionthreshold', 'adlcp');
        if ($threshold_elem && $threshold_elem->hasAttribute('minProgressMeasure')) {
            $itemInfo['adlcp_completionthreshold'] = $threshold_elem->getAttribute('minProgressMeasure');
        } else {
            $itemInfo['adlcp_completionthreshold'] = '';
        }

        $elem = $this->getFirstElementNode($item, 'item');
        if ($elem) {
            $itemInfo['isLeaf'] = false;
        } else {
            $itemInfo['isLeaf'] = true;
        }

        if ($renderer == null) {
            print_r($itemInfo);
        }
        $renderer->RenderStartItem($this, $itemInfo);

        while ($elem) {
            $nextElem = $this->getNextElementNode($elem);

            /* pass the info about the last element */
            if ($nextElem === null) {
                $this->RenderItem($elem, $renderer, true);
            } else {
                $this->RenderItem($elem, $renderer, false);
            }

            $elem = $nextElem;
        }

        $renderer->RenderStopItem($this, $itemInfo);
    }

    public function RenderResourceOrganization(&$renderer)
    {
        $itemInfo['identifier'] = '-resource-';
        $itemInfo['isLast'] = true;     // the organization is always the last
        $itemInfo['identifierref'] = false;
        $itemInfo['isvisible'] = true;
        $itemInfo['parameters'] = false;

        $itemInfo['title'] = 'Lesson resources';
        $resNum = $this->GetResourceNumber();
        $itemInfo['isLeaf'] = ($resNum == 0);

        $renderer->RenderStartItem($this, $itemInfo);

        for ($i = 0; $i < $resNum; ++$i) {
            $resInfo = $this->GetResourceInfo($this->GetResourceIdentifier($i));

            // href, identifier, type, scormtype
            $resInfo['isLast'] = ($i == ($resNum - 1));
            $resInfo['identifierref'] = $resInfo['identifier'];
            $resInfo['isvisible'] = true;
            $resInfo['parameters'] = false;
            $resInfo['title'] = $resInfo['identifier'];
            $resInfo['adlcp_prerequisites'] = '';
            $resInfo['adlcp_maxtimeallowed'] = '';
            $resInfo['adlcp_timelimitaction'] = '';
            $resInfo['adlcp_datafromlms'] = '';
            $resInfo['adlcp_masteryscore'] = '';
            $resInfo['isLeaf'] = true;

            $renderer->RenderStartItem($this, $resInfo);
            $renderer->RenderStopItem($this, $resInfo);
        }

        $renderer->RenderStopItem($this, $itemInfo);
    }

    /**
     * Return the first element in the next deep level
     *	of that passed and with tag equal to $tagname.
     *
     *  @param &$elem reference to current element
     *  @param $tagname the tag of desired element
     *
     *  @return next sibling elment or NULL if not find
     */
    public function getFirstElementNode(&$elem, $tagname = '')
    {
        $nextElem = $elem->getFirstChild();

        while ($nextElem) {
            if (($nextElem->getNodeType() == XML_ELEMENT_NODE)) { // only node elements
                if ($tagname != '') {
                    if (strtolower($nextElem->getNodeName()) == $tagname) {
                        return $nextElem;
                    }
                } else {
                    return $nextElem;
                }
            }
            $nextElem = $nextElem->getNextSibling();
        }

        return null;
    }

    /**
     * Return the next element in the same level of that passed
     *  and with node_name equal to $nodename.
     *
     *  @param &$elem reference to current element
     *  @param $sameTag the element retured must have same tag of $elem
     *
     *  @return next sibling elment or NULL if not find
     */
    public function getNextElementNode(&$elem, $sameTag = true)
    {
        $nextElem = &$elem->getNextSibling();

        while ($nextElem) {
            if (($nextElem->getNodeType() == XML_ELEMENT_NODE)) { // only node elements
                if ($sameTag) {
                    if ($nextElem->getNodeName() == $elem->getNodeName()) {
                        return $nextElem;
                    }
                } else {
                    return $nextElem;
                }
            }
            $nextElem = $nextElem->getNextSibling();
        }

        return null;
    }

    /**
     * Return the value of the first element in the next deep level
     *	of that passed and with tag equal to $tagname.
     *
     *  @param &$elem reference to current element
     *  @param $tagname the tag of desired element
     *
     *  @return next sibling elment or NULL if not find
     */
    public function getFirstElementValue(&$elem, $tagname = '', $prefix = '')
    {
        $nextElem = $elem->getFirstChild();
        $node = null;
        if ($prefix != '') {
            $tagname = $prefix . ':' . $tagname;
        }
        while ($nextElem) {
            if (($nextElem->getNodeType() == XML_ELEMENT_NODE)) { // only node elements
                if ($tagname != '') {
                    if (strtolower($nextElem->getNodeName()) == $tagname) {
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
        if ($node != null) {
            $elemValue = $node->getFirstChild();
            if ($elemValue != null) {
                return $elemValue->getContent();
            }
        }

        return null;
    }

    /**
     * Return the node requested with tag equal to $tagname.
     *
     *  @param &$elem reference to current element
     *  @param $tagname the tag of desired element
     *
     *  @return the searched element
     */
    public function getNodeElement(&$elem, $tagname = '', $prefix = '')
    {
        $nextElem = $elem->getFirstChild();
        $node = null;
        if ($prefix != '') {
            $tagname = $prefix . ':' . $tagname;
        }

        while ($nextElem) {
            if (($nextElem->getNodeType() == XML_ELEMENT_NODE)) { // only node elements
                if ($tagname != '') {
                    if (strtolower($nextElem->getNodeName()) == $tagname) {
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
     * Set the error.
     */
    public function setError($errCode, $errText)
    {
        $this->errCode = $errCode;
        $this->errText = $errText;
    }
}
