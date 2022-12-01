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

class DDomDocument extends DDomNode
{
    public function __construct($document = null)
    {
        parent::__construct($document);
    }

    public function &createNew($version)
    {
        $this->node = new DOMDocument($version);
        return $this;
    }

    public function loadXML($strXML)
    {
        $this->node = new DOMDocument();
        $this->node->loadXML($strXML);
        return $this;
    }

    public function saveXML()
    {
        return $this->node->saveXML();
    }

    public function isNULL()
    {
        return $this->node == null;
    }

    public function getDocumentElement()
    {
        return new DDomElement($this->node->documentElement);
    }

    public function createElement($name)
    {
        return new DDomElement($this->node->createElement($name));
    }

    public function createTextNode($text)
    {
        return new DDomTextNode($this->node->createTextNode($text));
    }
}

class DDomNode
{
    public $node = null;

    public function __construct(&$node)
    {
        $this->node = $node;
    }

    public function appendChild(&$node)
    {
        $this->_appendChild($this->node, $node->node);

        return $this;
    }

    public function _appendChild(&$parentnode, &$node)
    {
        $parentnode->appendChild($node);
        return $parentnode;
    }

    public function getOwnerDocument()
    {
        $node = $this->node->ownerDocument;
        $nodeObj = DDomNode::createDDomObject($node);

        return $nodeObj;
    }

    public function getChildNodes()
    {
        $listObj = null;
        $obj = $this->node->childNodes;
        if ($obj != null) {
            $listObj = new DDomNodeList($obj);
        }

        return $listObj;
    }

    public function getFirstChild()
    {
        $child = null;
        $nodeObj = null;
        $child = $this->node->firstChild;
        if ($child === null) {
            return null;
        }
        $nodeObj = $this->createDDomObject($child);
        return $nodeObj;
    }

    public function getNextSibling()
    {
        $sibling = null;
        $nodeObj = null;
        $sibling = $this->node->nextSibling;
        if ($sibling === null) {
            return null;
        }
        $nodeObj = $this->createDDomObject($sibling);
        return $nodeObj;
    }

    public function getParentNode()
    {
        $parent = null;
        $nodeObj = null;
        $parent = $this->node->parentNode;
        if ($parent === null) {
            return null;
        }
        $nodeObj = $this->createDDomObject($parent);
        return $nodeObj;
    }

    public function removeChild(&$node)
    {
        return $this->node->removeChild($node->node);
    }

    public function getNodeName()
    {
        return $this->_getNodeName($this->node);
    }

    public function _getNodeName(&$node)
    {
        return $node->nodeName;
    }

    public function getNodeValue()
    {
        return $this->_getNodeValue($this->node);
    }

    public function _getNodeValue(&$node)
    {
        return $node->nodeValue;
    }

    public function getNodeType()
    {
        return $this->_getNodeType($this->node);
    }

    public static function _getNodeType(&$node)
    {
        return $node->nodeType;
    }

    public function getContent()
    {
        return $this->_getContent($this->node);
    }

    public function _getContent(&$node)
    {
        return $node->textContent;
    }

    /** funzione astratta per creare oggetti da nodi **/
    public static function createDDomObject(&$node)
    {
        if ($node === null) {
            return $node;
        }
        $nodetype = DDomNode::_getNodeType($node);
        $nodeObj = new DDomNode($node);
        switch ($nodetype) {
            case XML_ELEMENT_NODE:
                $nodeObj = new DDomElement($node);
                break;
            case XML_ATTRIBUTE_NODE: /* not implemented */ break;
            case XML_TEXT_NODE:
                $nodeObj = new DDomTextNode($node);
                break;
            case XML_CDATA_SECTION_NODE: /* not implemented */ break;
            case XML_ENTITY_REF_NODE: /* not implemented */ break;
            case XML_ENTITY_NODE: /* not implemented */ break;
            case XML_PI_NODE: /* not implemented */ break;
            case XML_COMMENT_NODE: /* not implemented */ break;
            case XML_DOCUMENT_NODE:
                $nodeObj = new DDomDocument($node);
                break;
            case XML_DOCUMENT_TYPE_NODE: /* not implemented */ break;
            case XML_DOCUMENT_FRAG_NODE: /* not implemented */ break;
            case XML_NOTATION_NODE: /* not implemented */ break;
            case XML_HTML_DOCUMENT_NODE: /* not implemented */ break;
            case XML_DTD_NODE: /* not implemented */ break;
            case XML_ELEMENT_DECL_NODE: /* not implemented */ break;
            case XML_ATTRIBUTE_DECL_NODE: /* not implemented */ break;
            case XML_ENTITY_DECL_NODE: /* not implemented */ break;
            case XML_NAMESPACE_DECL_NODE: /* not implemented */ break;
        }

        return $nodeObj;
    }
}

class DDomTextNode extends DDomNode
{
    public function __construct(&$textnode)
    {
        parent::__construct($textnode);
    }
}

class DDomElement extends DDomNode
{
    public function __construct(&$element)
    {
        parent::__construct($element);
    }

    public function hasAttribute($name)
    {
        return $this->node->hasAttribute($name);
    }

    public function getAttribute($name)
    {
        return $this->node->getAttribute($name);
    }

    public function setAttribute($name, $value)
    {
        $this->node->setAttribute($name, $value);
        return true;
    }

    public function getTagName()
    {
        return $this->_getTagName($this->node);
    }

    public function _getTagName(&$node)
    {
        return $node->tagName;
    }

    public function getElementsByTagName($name)
    {
        $listObj = null;
        $obj = $this->node->getElementsByTagName($name);
        if ($obj != null) {
            $listObj = new DDomNodeList($obj);
        }
        return $listObj;
    }

    /*
     * Cerca tra gli elementi del nodo il primo elemento che ha tag $name
     * ed un attributo con nome $attribname e valore $attribvalue
     * se $attribname == "" && $attribvalue == "" non c'e' ricerca tra gli
     * attributi.
     * Torna l'elemento trovato o NULL.
     */
    public function getElementByNameAttrib($name, $attribname = '', $attribvalue = '')
    {
        $elem = $this->getFirstChild();
        while ($elem) {
            if (($elem->getNodeType() == XML_ELEMENT_NODE) && ($elem->getNodeName() == $name)) {
                if ($attribname != '') {
                    if ($elem->getAttribute($attribname) == $attribvalue) {
                        break;
                    }	// trovato
                } else {
                    break;
                } // trovato
            }
            $elem = $elem->getNextSibling();	// successivo elemento
        }

        return $elem;
    }
}

class DDomNodeList
{
    public $list = null;

    public function __construct(&$listNode)
    {
        $this->list = $listNode;
    }

    public function getLength()
    {
        return $this->list->length;
    }

    public function item($index)
    {
        $nodeObj = null;
        if ($index >= 0 && $index < $this->getLength()) {
                $nodeObj = DDomNode::createDDomObject($this->list->item($index));
        }
        return $nodeObj;
    }
}

class DDomXPath
{
    public $xpath = null;

    public function __construct($document)
    {
            $this->xpath = new DOMXPath($document->node);
    }

    public function registerNamespace($prefix, $namespaceURI)
    {
        return $this->xpath->registerNamespace($prefix, $namespaceURI);
    }

    public function &evaluate($expression, $contextnode = null)
    {
        $node = null;
        if ($contextnode === null) {
            $node = $this->xpath->evaluate($expression);
        } else {
            $node = $this->xpath->evaluate($expression, $contextnode->node);
        }
        $nodeObj = DomNode::createDDomObject($node);
        return $nodeObj;
    }

    public function query($expression, $contextnode = null)
    {
        $node = null;
        if ($contextnode === null) {
            $node = $this->xpath->query($expression);
        } else {
            $node = $this->xpath->query($expression, $contextnode->node);
        }
        return new DDomNodeList($node);
    }
}
