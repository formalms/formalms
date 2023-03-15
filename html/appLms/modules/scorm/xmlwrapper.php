<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

define('XMLV4', 4);
define('XMLV5', 5);

if (version_compare(phpversion(), '5.0.0') == -1) {
    $GLOBALS['xmlv'] = XMLV4;
} else {
    $GLOBALS['xmlv'] = XMLV5;
}

class DDomDocument extends DDomNode
{
    public function DDomDocument($document = null)
    {
        DDomNode::DDomNode($document);
    }

    public function &createNew($version)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            $this->node = domxml_new_doc($version);
        } else {
            $this->node = new DOMDocument($version);
        }

        return $this;
    }

    public function loadXML($strXML)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            $this->node = domxml_open_mem($strXML);
        } else {
            $this->node = new DOMDocument();
            $this->node->loadXML($strXML);
        }

        return $this;
    }

    public function saveXML()
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $this->node->dump_mem(true);
        } else {
            return $this->node->saveXML();
        }
    }

    public function isNULL()
    {
        return $this->node == null;
    }

    public function getDocumentElement()
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            $docElement = new DDomElement($this->node->document_element());
        } else {
            $docElement = new DDomElement($this->node->documentElement);
        }

        return $docElement;
    }

    public function createElement($name)
    {
        $elem = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $elem = new DDomElement($this->node->create_element($name));
        } else {
            $elem = new DDomElement($this->node->createElement($name));
        }

        return $elem;
    }

    public function createTextNode($text)
    {
        $textnode = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $textnode = new DDomTextNode($this->node->create_text_node($text));
        } else {
            $textnode = new DDomTextNode($this->node->createTextNode($text));
        }

        return $textnode;
    }
}

class DDomNode
{
    public $node = null;

    public function DDomNode(&$node)
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
        if ($GLOBALS['xmlv'] == XMLV4) {
            $parentnode->append_child($node);
        } else {
            $parentnode->appendChild($node);
        }

        return $parentnode;
    }

    public function getOwnerDocument()
    {
        $node = null;
        $nodeObj = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $node = $this->node->owner_document();
        } else {
            $node = $this->node->ownerDocument;
        }

        $nodeObj = DDomNode::createDDomObject($node);

        return $nodeObj;
    }

    public function getChildNodes()
    {
        $obj = null;
        $listObj = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $obj = $this->node->child_nodes();
        } else {
            $obj = $this->node->childNodes;
        }
        if ($obj != null) {
            $listObj = new DDomNodeList($obj);
        }

        return $listObj;
    }

    public function getFirstChild()
    {
        $child = null;
        $nodeObj = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $child = $this->node->first_child();
        } else {
            $child = $this->node->firstChild;
        }
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
        if ($GLOBALS['xmlv'] == XMLV4) {
            $sibling = $this->node->next_sibling();
        } else {
            $sibling = $this->node->nextSibling;
        }
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
        if ($GLOBALS['xmlv'] == XMLV4) {
            $parent = $this->node->parent_node();
        } else {
            $parent = $this->node->parentNode;
        }
        if ($parent === null) {
            return null;
        }
        $nodeObj = $this->createDDomObject($parent);

        return $nodeObj;
    }

    public function removeChild(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $this->node->remove_child($node->node);
        } else {
            return $this->node->removeChild($node->node);
        }
    }

    public function getNodeName()
    {
        return $this->_getNodeName($this->node);
    }

    public function _getNodeName(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $node->node_name();
        } else {
            return $node->nodeName;
        }
    }

    public function getNodeValue()
    {
        return $this->_getNodeValue($this->node);
    }

    public function _getNodeValue(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $node->node_value();
        } else {
            return $node->nodeValue;
        }
    }

    public function getNodeType()
    {
        return $this->_getNodeType($this->node);
    }

    public function _getNodeType(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $node->node_type();
        } else {
            return $node->nodeType;
        }
    }

    public function getContent()
    {
        return $this->_getContent($this->node);
    }

    public function _getContent(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $node->get_content();
        } else {
            return $node->textContent;
        }
    }

    /** funzione astratta per creare oggetti da nodi **/
    public function createDDomObject(&$node)
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
    public function DDomTextNode(&$textnode)
    {
        DDomNode::DDomNode($textnode);
    }
}

class DDomElement extends DDomNode
{
    public function DDomElement(&$element)
    {
        DDomNode::DDomNode($element);
    }

    public function hasAttribute($name)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $this->node->has_attribute($name);
        } else {
            return $this->node->hasAttribute($name);
        }
    }

    public function getAttribute($name)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $this->node->get_attribute($name);
        } else {
            return $this->node->getAttribute($name);
        }
    }

    public function setAttribute($name, $value)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            $this->node->set_attribute($name, $value);
        } else {
            $this->node->setAttribute($name, $value);
        }

        return true;
    }

    public function getTagName()
    {
        return $this->_getTagName($this->node);
    }

    public function _getTagName(&$node)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $node->tagname();
        } else {
            return $node->tagName;
        }
    }

    public function getElementsByTagName($name)
    {
        $obj = null;
        $listObj = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            $obj = $this->node->get_elements_by_tagname($name);
        } elseif ($this->node) {
            $obj = $this->node->getElementsByTagName($name);
        }

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

    public function DDomNodeList(&$listNode)
    {
        $this->list = $listNode;
    }

    public function getLength()
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            if (is_array($this->list)) {
                return count($this->list);
            } else {
                return count($this->list->nodeset);
            }
        } else {
            return $this->list->length;
        }
    }

    public function item($index)
    {
        $nodeObj = null;
        if ($index >= 0 && $index < $this->getLength()) {
            if ($GLOBALS['xmlv'] == XMLV4) {
                if (is_array($this->list)) {
                    $nodeObj = DDomNode::createDDomObject($this->list[$index]);
                } else {
                    $nodeObj = DDomNode::createDDomObject($this->list->nodeset[$index]);
                }
            } else {
                $nodeObj = DDomNode::createDDomObject($this->list->item($index));
            }
        }

        return $nodeObj;
    }
}

class DDomXPath
{
    public $xpath = null;

    public function DDomXPath($document)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            $this->xpath = $document->node->xpath_new_context();
        } else {
            $this->xpath = new DOMXPath($document->node);
        }
    }

    public function registerNamespace($prefix, $namespaceURI)
    {
        if ($GLOBALS['xmlv'] == XMLV4) {
            return $this->xpath->xpath_register_ns($prefix, $namespaceURI);
        } else {
            return $this->xpath->registerNamespace($prefix, $namespaceURI);
        }
    }

    public function &evaluate($expression, $contextnode = null)
    {
        $node = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            if ($contextnode === null) {
                $node = $this->xpath->xpath_eval($expression);
            } else {
                $node = $this->xpath->xpath_eval($expression, $contextnode->node);
            }
        } else {
            if ($contextnode === null) {
                $node = $this->xpath->evaluate($expression);
            } else {
                $node = $this->xpath->evaluate($expression, $contextnode->node);
            }
        }
        $nodeObj = DomNode::createDDomObject($node);

        return $nodeObj;
    }

    public function query($expression, $contextnode = null)
    {
        $node = null;
        if ($GLOBALS['xmlv'] == XMLV4) {
            if ($contextnode === null) {
                $node = $this->xpath->xpath_eval($expression);
            } else {
                $node = $this->xpath->xpath_eval($expression, $contextnode->node);
            }
        } else {
            if ($contextnode === null) {
                $node = $this->xpath->query($expression);
            } else {
                $node = $this->xpath->query($expression, $contextnode->node);
            }
        }
        $nodeObj = new DDomNodeList($node);

        return $nodeObj;
    }
}
