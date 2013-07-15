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

define('XMLV4',4);
define('XMLV5',5);

if( version_compare(phpversion(), "5.0.0") == -1 ) {
	$GLOBALS['xmlv'] = XMLV4;
} else {
	$GLOBALS['xmlv'] = XMLV5;
}

class DDomDocument extends DDomNode {
		
	function DDomDocument( $document = NULL ) {
		DDomNode::DDomNode( $document );
	}
	
	function &createNew( $version ) {
		if( $GLOBALS['xmlv'] == XMLV4 )
			$this->node = domxml_new_doc($version);
		else
			$this->node = new DOMDocument($version);
		return $this;
	}
	
	function loadXML( $strXML ) {
		if( $GLOBALS['xmlv'] == XMLV4 )
			$this->node = domxml_open_mem($strXML);
		else {
			$this->node = new DOMDocument();
			$this->node->loadXML($strXML);
		}
		return $this;
	}
	
	function saveXML() {
		if( $GLOBALS['xmlv'] == XMLV4 )
			return $this->node->dump_mem(true);
		else
			return $this->node->saveXML();
	}
	
	function isNULL() { return $this->node == NULL; }
	
	function getDocumentElement() {
		if( $GLOBALS['xmlv'] == XMLV4 )
			$docElement = new DDomElement( $this->node->document_element() );
		else
			$docElement = new DDomElement( $this->node->documentElement);
		return $docElement;
	}

	function createElement( $name ) {
		$elem = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 )
			$elem = new DDomElement( $this->node->create_element($name) );
		else
			$elem = new DDomElement( $this->node->createElement($name) );
		return $elem;
	}
	
	function createTextNode( $text ) {
		$textnode = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 )
			$textnode = new DDomTextNode( $this->node->create_text_node($text) );
		else
			$textnode = new DDomTextNode( $this->node->createTextNode($text) );
		return $textnode;
	}
}


class DDomNode {
	var $node = NULL;
	
	function DDomNode( &$node ) {
		$this->node = $node;
	}
	
	function appendChild( &$node ) {
		$this->_appendChild( $this->node, $node->node );
		return $this;
	}
	
	function _appendChild( &$parentnode, &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$parentnode->append_child($node);
		else
			$parentnode->appendChild($node);
		return $parentnode;
	}
	
	function getOwnerDocument() {
		$node = NULL;
		$nodeObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$node = $this->node->owner_document();
		else
			$node = $this->node->ownerDocument;
			
		$nodeObj = DDomNode::createDDomObject($node);
		return $nodeObj;
	}

	function getChildNodes() {
		$obj = NULL;
		$listObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$obj = $this->node->child_nodes();
		else
			$obj = $this->node->childNodes;
		if( $obj != NULL ) {
			$listObj = new DDomNodeList($obj);
		}
		return $listObj;
	}
	
	function getFirstChild() {
		$child = NULL;
		$nodeObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$child = $this->node->first_child();
		else
			$child = $this->node->firstChild;
		if( $child === NULL ) return NULL;
		$nodeObj = $this->createDDomObject( $child );
		return $nodeObj;
	}
	
	function getNextSibling() {
		$sibling = NULL;
		$nodeObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$sibling = $this->node->next_sibling();
		else
			$sibling = $this->node->nextSibling;
		if( $sibling === NULL ) 
			return NULL;
		$nodeObj = $this->createDDomObject( $sibling );
		return $nodeObj;
	}

	function getParentNode() {
		$parent = NULL;
		$nodeObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$parent = $this->node->parent_node();
		else
			$parent = $this->node->parentNode;
		if( $parent === NULL ) return NULL;
		$nodeObj = $this->createDDomObject( $parent );
		return $nodeObj;		
	}
	
	function removeChild( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) {
			return $this->node->remove_child( $node->node ); 
		} else {
			return $this->node->removeChild( $node->node ); 
		}
	}
	
	function getNodeName() {
		return $this->_getNodeName( $this->node );
	}

	function _getNodeName( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $node->node_name();
		else
			return $node->nodeName;		
	}

	function getNodeValue() {
		return $this->_getNodeValue( $this->node );
	}
	
	function _getNodeValue( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $node->node_value();
		else
			return $node->nodeValue;		
	}
	
	function getNodeType() {
		return $this->_getNodeType( $this->node );
	}

	function _getNodeType( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $node->node_type();
		else
			return $node->nodeType;		
	}

	function getContent() {
		return $this->_getContent( $this->node );
	}

	function _getContent( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $node->get_content();
		else
			return $node->textContent;			
	}

	/** funzione astratta per creare oggetti da nodi **/
	function createDDomObject( &$node ) {
		if( $node === NULL ) return $node;
		$nodetype = DDomNode::_getNodeType( $node );
		$nodeObj = new DDomNode($node);
		switch( $nodetype ) {
			case XML_ELEMENT_NODE:
				$nodeObj = new DDomElement( $node );
				break;
			case XML_ATTRIBUTE_NODE: /* not implemented */ break;
			case XML_TEXT_NODE:
				$nodeObj = new DDomTextNode( $node );
				break;
			case XML_CDATA_SECTION_NODE: /* not implemented */ break;
			case XML_ENTITY_REF_NODE: /* not implemented */ break;
			case XML_ENTITY_NODE: /* not implemented */ break;
			case XML_PI_NODE: /* not implemented */ break;
			case XML_COMMENT_NODE: /* not implemented */ break;
			case XML_DOCUMENT_NODE:
				$nodeObj = new DDomDocument( $node );
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

class DDomTextNode extends DDomNode {

	function DDomTextNode( &$textnode ) {
		DDomNode::DDomNode($textnode);
	}
	
}

class DDomElement extends DDomNode {
	function DDomElement( &$element ) {
		DDomNode::DDomNode($element);
	}	

	function hasAttribute( $name ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $this->node->has_attribute($name);
		else
			return $this->node->hasAttribute($name);
	}

	function getAttribute( $name ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $this->node->get_attribute($name);
		else
			return $this->node->getAttribute($name);
	}
	
	function setAttribute( $name, $value  ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$this->node->set_attribute($name, $value);
		else
			$this->node->setAttribute($name, $value);
		return TRUE;
	}
	
	function getTagName() {
		return $this->_getTagName( $this->node );
	}

	function _getTagName( &$node ) {
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			return $node->tagname();
		else
			return $node->tagName;		
	}
	
	function getElementsByTagName( $name ) {
		$obj = NULL;
		$listObj = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) 
			$obj = $this->node->get_elements_by_tagname($name);
		elseif($this->node)
			$obj = $this->node->getElementsByTagName($name);
		
		if( $obj != NULL ) {
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
	function getElementByNameAttrib( $name, $attribname = "", $attribvalue = ""){
		$elem = $this->getFirstChild();
		while( $elem ) {
			if( ($elem->getNodeType() == XML_ELEMENT_NODE) && ($elem->getNodeName() == $name) ) {
				if( $attribname != "" ) {
					if( $elem->getAttribute($attribname) == $attribvalue) 
						break;	// trovato
				} else 
					break; // trovato
			}
			$elem = $elem->getNextSibling();	// successivo elemento
		}
		return $elem;
	}
	
}

class DDomNodeList {
	var $list = NULL;
	function DDomNodeList( &$listNode ) {
		$this->list = $listNode;
	}
	
	function getLength() {
		if( $GLOBALS['xmlv'] == XMLV4 )
			if( is_array($this->list) )
				return count($this->list);
			else
				return count($this->list->nodeset);
		else
			return $this->list->length;		
	}
	
	function item( $index ) {
		$nodeObj = NULL;
		if( $index >= 0 && $index < $this->getLength() ) {
			if( $GLOBALS['xmlv'] == XMLV4 )
				if( is_array($this->list) )
					$nodeObj = DDomNode::createDDomObject($this->list[$index]);
				else 
					$nodeObj = DDomNode::createDDomObject($this->list->nodeset[$index]);
			else
				$nodeObj = DDomNode::createDDomObject($this->list->item($index));	
		} 
		return $nodeObj;
	}
}

class DDomXPath {
	var $xpath = NULL;
		
	function DDomXPath( $document ) {
		if( $GLOBALS['xmlv'] == XMLV4 )
			$this->xpath = $document->node->xpath_new_context();
		else
			$this->xpath = new DOMXPath($document->node);
	}
	
	function registerNamespace( $prefix, $namespaceURI ) {
		if( $GLOBALS['xmlv'] == XMLV4 )
			return $this->xpath->xpath_register_ns( $prefix, $namespaceURI );
		else
			return $this->xpath->registerNamespace( $prefix, $namespaceURI );
	}
	
	function &evaluate( $expression, $contextnode = NULL) {
		$node = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) {
			if( $contextnode === NULL ) 
				$node = $this->xpath->xpath_eval($expression);
			else
				$node = $this->xpath->xpath_eval($expression, $contextnode->node);
		} else {
			if( $contextnode === NULL ) 
				$node = $this->xpath->evaluate($expression);
			else
				$node = $this->xpath->evaluate($expression, $contextnode->node);			
		}
		$nodeObj = DomNode::createDDomObject($node);
		return $nodeObj;
	}

	function query( $expression, $contextnode = NULL) {
		$node = NULL;
		if( $GLOBALS['xmlv'] == XMLV4 ) {
			if( $contextnode === NULL ) 
				$node = $this->xpath->xpath_eval($expression);
			else
				$node = $this->xpath->xpath_eval($expression, $contextnode->node);
		} else {
			if( $contextnode === NULL ) 
				$node = $this->xpath->query($expression);
			else
				$node = $this->xpath->query($expression, $contextnode->node);			
		}
		$nodeObj = new DDomNodeList($node);
		return $nodeObj;
	}
	
}
