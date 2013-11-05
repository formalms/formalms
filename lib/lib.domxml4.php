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
 * This package define classes for XML DOM compatibility with PHP < 5
 * In Docebo we use DOM API from PHP5 but you can use the platform also
 *	in PHP4. So in this package we redefine all the DOM API used in Docebo.
 * For do that we use domxml.
 *
 *
 * @package admin-library
 * @subpackage domxml
 * @version 	$Id: lib.domxml4.php 323 2006-05-10 16:35:25Z fabio $
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 */

/**
 * To avoid crash in overload module, we break the inheritance
 * So all class reimplements all methods and properties
 */

function soap__dbgOut1( $textOut, $level = 0 ) {
	return;
/*	if( $level < 1 )
		return;
	$fout = fopen("c:\\tmp\\soaperror.txt", "a");
	if( is_array($textOut) ) {
		fwrite($fout, print_r($textOut, true) );
	} else {
		fwrite($fout, "$textOut\n");
	}
	fflush($fout);
	fclose($fout);*/
}

function isSameObject( &$o1, &$o2 ) {
	if( version_compare(PHP_VERSION,"5.0.0",">=") )
		return $o1 === $o2;
	/* in php4 ... mmm ... use a trick */
	// 1. compare class
	if( get_class($o1) != get_class($o2) )
		return FALSE;
	// 2. is the same instance?
	$rand = rand()+1;
	$o1['compare_elem_'.$rand] = $rand;
	if( !isset( $o2['compare_elem_'.$rand] ) ) {
		unset( $o1['compare_elem_'.$rand] );
		return FALSE;
	}
	if( $o2['compare_elem_'.$rand] != $rand ) {
		unset( $o1['compare_elem_'.$rand] );
		return FALSE;
	}
	unset( $o1['compare_elem_'.$rand] );
	return TRUE;
}

function &createDoceboDOMObject( &$domObj, $secondChoice = FALSE ) {
	if( is_object($domObj) ) {
		//soap__dbgOut1( "createDoceboDOMObject ". get_class($domObj) );
		switch( strToLower(get_class($domObj)) ) {
			case "domattribute":
				$tmpObj =& new DoceboDOMAttr( $domObj );
			break;
			/*case "domcdata":
				$tmpObj =& new DoceboDOMCData( $domObj );
			break;
			case "domcomment":
				$tmpObj =& new DoceboDOMComment( $domObj );
			break;*/
			case "domdocument":
				$tmpObj =& new DoceboDOMDocument( $domObj );
			break;
			case "domdocumenttype":
				$tmpObj =& new DoceboDOMDocumentType( $domObj );
			break;
			case "domelement":
				$tmpObj =& new DoceboDOMElement( $domObj );
			break;
			/*case "domentity":
				$tmpObj =& new DoceboDOMEntity( $domObj );
			break;
			case "domentityreference":
				$tmpObj =& new DoceboDOMEntityReference( $domObj );
			break;*/
			case "domnode":
				$tmpObj =& new DoceboDOMNode( $domObj );
			break;
			/*case "domprocessinginstruction":
				$tmpObj =& new DoceboDOMProcessingInstruction( $domObj );
			break;*/
			case "domtext":
				$tmpObj =& new DoceboDOMText( $domObj );
			break;
			/*case "domparser":
				$tmpObj =& new DoceboDOMParser( $domObj );
			break;
			case "xpathcontext":
				$tmpObj =& new DoceboXPathContext( $domObj );
			break;*/
			default:
				$tmpObj =& $domObj;
			break;
		}
	} elseif( is_array($domObj) ) {
		if( $secondChoice )
			$tmpObj =& new DoceboDOMNamedNodeMap( $domObj );
		else
			$tmpObj =& new DoceboDOMNodeList( $domObj );
	} else {
		$tmpObj =& $domObj;
	}
	//soap__dbgOut1( "-createDoceboDOMObject return ". get_class($tmpObj) );
	return $tmpObj;
}

class DoceboDOMNode {
	var $nodeRef;

	/**
	 * Return the internal representation of object
	 * @return DOMNode the DOMNode associated a this object
	 **/
	function _getSelf() {
		return $this->nodeRef;
	}

	/**
	 * Return the DOM object
	 * @param mixed $obj
	 * @return reference to DOMXXX object
	 **/
	function getRef( $obj ) {
		if( strncasecmp(get_class($obj),"Docebo",6) == 0 ) {
			return $obj->_getSelf();
		} else {
			return $obj;
		}
	}

	/**
	 * Constructor from DOMNode
	 * @param DOMNode $node
	 **/
	function DoceboDOMNode( $node ) {
		$this->nodeRef = $node;
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		return _doceboNodePropertyGet( $this, $param, $value );
	}

	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		return _doceboNodePropertySet( $this, $param, $value );
	}

	/**
	 * To overload method call
	 **/
	/*function __call( $method, $params, &$return) {
		$tmpVal =& call_user_func_array ( array($this,$method), $params );
		if( is_object($tmpVal) ) {
			$return =& createDoceboDOMObject($tmpVal);
		} else {
			$return =& $tmpVal;
		}
		return TRUE;
	}*/

	function &appendChild( &$node ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->append_child( $this->getRef($node) );
		$return =& createDoceboDOMObject($result);
		return $return;
	}
	function &cloneNode( $deep = FALSE ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->clone_node( $deep );
		$return =& createDoceboDOMObject($result);
		return $return;
	}
	function hasAttributes() {
		$nodeRef = $this->nodeRef;
		return $nodeRef->has_attributes();
	}
	function hasChildNodes() {
		$nodeRef = $this->nodeRef;
		return $nodeRef->has_child_nodes();
	}
	function &insertBefore( &$newnode, $refnode = NULL ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->insert_child( $this->getRef($newnode), $this->getRef($refnode) );
		$return =& createDoceboDOMObject($result);
		return $return;
	}
	function isSameNode( &$node ) {
		return isSameObject($this,$node);
	}
	// string isSupported( string feature, string version )
	// string lookupNamespaceURI ( string prefix )
	// string lookupPrefix ( string namespaceURI )
	// void normalize( void )
	function &removeChild( &$oldnode ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->remove_child( $this->getRef($oldnode) );
		$return =& createDoceboDOMObject($result);
		return $return;
	}
	function &replaceChild( &$newnode, &$oldnode ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->replace_child( $this->getRef($oldnode), $this->getRef($newnode) );
		$return =& createDoceboDOMObject($result);
		return $return;
	}
}

class DoceboDOMText extends DoceboDOMNode {
	/**
	 * Constructor from DOMText
	 **/
	function DoceboDOMText( $domtext ) {
		$this->nodeRef = $domtext;
	}

	/**
	 * To overload param get
	**/
	function __get( $param, &$value ) {
		return _doceboNodePropertyGet( $this, $param, $value );
	}

	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		return _doceboNodePropertySet( $this, $param, $value );
	}
}

class DoceboDOMDocument extends DoceboDOMNode {
	/**
	 * Constructor from DOMDocument
	 **/
	function DoceboDOMDocument($version = '1.0' ) {
		if( is_object($version) ) {
			parent::DoceboDOMNode($version);
		} else {
			parent::DoceboDOMNode(domxml_new_doc($version));
		}
	}

	function &load( $filename, $options = FALSE) {
		$isStatic = !(isset($this) && get_class($this) == __CLASS__);
		if( $isStatic ) {
			$result =& createDoceboDOMObject( domxml_open_file( $filename ) );
			return $result;
		} else {
			$errors = array();
			$this->nodeRef = domxml_open_file( $filename,DOMXML_LOAD_PARSING ,$errors );
			if(!empty($errors)) echo '<pre>'.print_R($errors);
			return $this;
		}
	}

	function &loadXML( $source, $options = FALSE) {
		$isStatic = !(isset($this) && get_class($this) == __CLASS__);
		if( $isStatic ) {
			$result =& createDoceboDOMObject(  domxml_open_mem( $source ) );
			return $result;
		} else {
			$this->nodeRef = domxml_open_mem( $source );
			return $this;
		}
	}

	function save( $filename, $options = FALSE) {
		return $this->nodeRef->dump_file( $filename );
	}

	function saveXML( $node = NULL, $options = FALSE) {
		if( $node === NULL ) {
			$nodeRef = $this->nodeRef;
			return $nodeRef->dump_mem();
		} else {
			$nodeRef = $this->nodeRef;
			$domnode =& $this->getRef($node);
			return $nodeRef->dump_node($domnode);
		}
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		$retVal = false;
		$secondChoice = FALSE;
		$ret_bool = TRUE;
		switch( $param ) {
			case "docType":
				$retVal =& $this->nodeRef->doctype();
			break;
			case "documentElement":
				$retVal =& $this->nodeRef->document_element();
			break;
			default:
				return _doceboNodePropertyGet( $this, $param, $value );
		}
		$value = createDoceboDOMObject($retVal,$secondChoice);
		return $ret_bool;
	}

	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		return _doceboNodePropertySet( $this, $param, $value );
	}

	function &createAttribute($name) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->create_attribute( $name, "" );
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function &createCDATASection( $content ) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->create_cdata_section( $content );
	}

	function &createComment( $content ) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->create_comment( $content );
	}

	function &createElement( $name ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->create_element($name);
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function &createElementNS( $namespaceURI,$qualifiedName ) {
		list( $prefix, $tagname ) = explode( $qualifiedName, ":" );
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->create_element_ns($namespaceURI,$tagname,$prefix);
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function &createEntityReference( $content ) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->create_entity_reference( $content );
	}

	function &createProcessingInstruction( $target, $data) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->create_processing_instruction( $target, $data );
	}

	function &createTextNode( $content ) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->create_text_node( $content );
	}

	function &getElementById( $elementId ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->get_element_by_id($elementId);
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function &getElementsByTagName( $name ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->get_elements_by_tagname( $name );
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function xinclude() { return $this->nodeRef->xinclude(); }
}

class DoceboDOMDocumentType extends DoceboDOMNode {
	/**
	 * Constructor from DomDocumentType
	 * @param DomDocumentType $documentType
	 **/
	function DoceboDOMDocumentType( &$documentType ) {
		parent::DoceboDOMNode($documentType);
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		$retVal = FALSE;
		$secondChoice = FALSE;
		$ret_bool = TRUE;
		switch( $param ) {
			case "publicId":
				$retVal =& $this->nodeRef->public_id();
			break;
			case "systemId":
				$retVal =& $this->nodeRef->system_id();
			break;
			case "name":
				$retVal =& $this->nodeRef->name();
			break;
			case "entities":
				$retVal =& $this->nodeRef->entities();
			break;
			case "notations":
				$retVal =& $this->nodeRef->notations();
			break;
			case "internalSubset":
				$retVal =& $this->nodeRef->internal_subset();
			break;
			default:
				return _doceboNodePropertyGet( $this, $param, $value );
		}
		$value = createDoceboDOMObject($retVal,$secondChoice);
		return $ret_bool;
	}
	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		return _doceboNodePropertySet( $this, $param, $value );
	}
}

class DoceboDOMAttr extends DoceboDOMNode {
	/**
	 * Constructor from DOMAttr
	 * @param DOMAttribute $attr
	 **/
	function DoceboDOMAttr( &$attr ) {
		parent::DoceboDOMNode($attr);
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		$retVal = false;
		$secondChoice = FALSE;
		$ret_bool = TRUE;
		switch( $param ) {
			case "name":
				$value = $this->nodeRef->name();
				return $ret_bool;
			case "ownerElement":
				$retVal =& $this->nodeRef->parent_node();
			break;
			case "value":
				$value = $this->nodeRef->value();
				return $ret_bool;
			case "schemaTypeInfo":
			case "specified":
				$value = NULL;
				return $ret_bool;
			default:
				return _doceboNodePropertyGet( $this, $param, $value );
		}
		$value = createDoceboDOMObject($retVal,$secondChoice);
		return $ret_bool;
	}

	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		$retVal = false;
		$secondChoice = FALSE;
		$ret_bool = TRUE;
		switch( $param ) {
			case "value":
				$this->nodeRef->set_value( $value );
				return $ret_bool;
			default:
				return _doceboNodePropertySet( $this, $param, $value );
		}
	}
}

class DoceboDOMElement extends DoceboDOMNode {
	/**
	 * Constructor from DOMElement
	 * @param DOMElement $element
	 **/
	function DoceboDOMElement( &$element ) {
		parent::DoceboDOMNode($element);
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		soap__dbgOut1( "+DoceboDOMElement::__get( $param )" );
		$retVal = false;
		$secondChoice = FALSE;
		$ret_bool = TRUE;
		switch( $param ) {
			case "tagName":
				$nodeRef = $this->nodeRef;
				$value = $nodeRef->tagname();
				return $ret_bool;
			case "schemaTypeInfo":
				$value = NULL;
				return $ret_bool;
			case "textContent":
				$value = $this->nodeRef->get_content();
				return $ret_bool;
			default:
				$ret_bool = _doceboNodePropertyGet( $this, $param, $value );
				return $ret_bool;
		}
		$value = createDoceboDOMObject($retVal,$secondChoice);
		return $ret_bool;
	}

	/**
	 * To overload param set
	 **/
	function __set( $param, &$value ) {
		return _doceboNodePropertySet( $this, $param, $value );
	}

	function getAttribute($name) {
		$nodeRef = $this->nodeRef;
		$attrVal = $nodeRef->get_attribute($name);
		return $attrVal;
	}

	function &getAttributeNode($name) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->get_attribute_node( $name );
		$return =& createDoceboDOMObject( $result );
		return $return;
	}

	function getElementsByTagName( $name ) {
		$nodeRef = $this->nodeRef;
		$result =& $nodeRef->get_elements_by_tagname( $name );
		$return = createDoceboDOMObject( $result );
		return $return;
	}

	function hasAttribute($name) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->has_attribute( $name );
	}

	function removeAttribute($name) {
		$nodeRef = $this->nodeRef;
		return $nodeRef->remove_attribute($name);
	}

	function setAttribute( $name, $value) {
		$nodeRef = $this->nodeRef;
		$nodeRef->set_attribute( $name, $value );
		return TRUE;
	}
}

class DoceboDOMNodeList {
	var $arr_nodes = NULL;

	function DoceboDOMNodeList( &$arr ) {
		$this->arr_nodes =& $arr;
	}

	function item($index) {
		if( isset( $this->arr_nodes[$index] ) ) {
			$result = createDoceboDOMObject( $this->arr_nodes[$index] );
			return $result;
		} else {
			return NULL;
		}
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		switch( $param ) {
			case "length":
				$value = count( $this->arr_nodes );
			break;
			default:
				return FALSE;
		}
		return TRUE;
	}

}

class DoceboDOMNamedNodeMap {
	var $arr_nodes = NULL;

	function DoceboDOMNamedNodeMap( &$arr ) {
		$this->arr_nodes =& $arr;
	}

	function &item($index) {
		$node =& reset( $this->arr_nodes );
		for( $curr = 0; $curr < count($this->arr_nodes); $curr++ ) {
			if( $curr == $index ) {
				reset( $this->arr_nodes );
				return $node;
			}
			$node =& next($this->arr_nodes);
		}
		reset( $this->arr_nodes );
		$false_var = NULL;
		return $false_var;
	}

	function &getNamedItem( $name ) {
		if( isset( $this->arr_nodes[$name] ) )
			return $this->arr_nodes[$name];
		else {
			$false_var = NULL;
			return $false_var;
		}
	}

	/**
	 * To overload param get
	 **/
	function __get( $param, &$value ) {
		switch( $param ) {
			case "length":
				$value = count( $this->arr_nodes );
			break;
			default:
				return FALSE;
		}
		return TRUE;
	}
}

class DoceboDOMXPath {
	var $xpath = NULL;

	function DoceboDOMXPath( $document ) {
		$domDocument = DoceboDOMNode::getRef( $document );
		$this->xpath = $domDocument->xpath_new_context();
	}

	function registerNamespace( $prefix, $namespaceURI ) {
		$xpath = $this->xpath;
		return $xpath->xpath_register_ns( $prefix, $namespaceURI );
	}

	function evaluate( $expression, $contextnode = NULL) {
		$xpath = $this->xpath;
		if( $contextnode === NULL )
			return $xpath->xpath_eval($expression);
		else
			return $xpath->xpath_eval($expression, DoceboDOMNode::getRef( $contextnode));
		//return createDoceboDOMObject($result->nodeset, TRUE );
	}

	function query( $expression, $contextnode = NULL) {
		$xpath = $this->xpath;
		if( $contextnode === NULL )
			$result = $xpath->xpath_eval($expression);
		else
			$result = $xpath->xpath_eval($expression, DoceboDOMNode::getRef( $contextnode) );
		return createDoceboDOMObject($result->nodeset, FALSE );
	}

}

overload("DoceboDOMNode");
overload("DoceboDOMText");
overload("DoceboDOMDocument");
overload("DoceboDOMDocumentType");
overload("DoceboDOMAttr");
overload("DoceboDOMElement");
overload("DoceboDOMNodeList");
overload("DoceboDOMNamedNodeMap");

function _doceboNodePropertyGet( &$obj, $param, &$value ) {
	$ret_bool = TRUE;
	$secondChoice = FALSE;
	switch( $param ) {
		case "nodeName":
			$value = $obj->nodeRef->node_name();
			return $ret_bool;
		case "nodeValue":
			$value = $obj->nodeRef->node_value();
			return $ret_bool;
		case "nodeType":
			$value = $obj->nodeRef->node_type();
			return $ret_bool;
		case "parentNode":
			$retVal = $obj->nodeRef->parent_node();
		break;
		case "childNodes":
			$retVal = $obj->nodeRef->child_nodes();
		break;
		case "firstChild":
			$retVal = $obj->nodeRef->first_child();
		break;
		case "lastChild":
			$retVal = $obj->nodeRef->last_child();
		break;
		case "previousSibling":
			$retVal = $obj->nodeRef->previous_sibling();
		break;
		case "nextSibling":
			$retVal =$obj->nodeRef->next_sibling();
		break;
		case "attributes":
			$retVal = $obj->nodeRef->attributes();
			$secondChoice = TRUE;
		break;
		case "ownerDocument":
			$retVal = $obj->nodeRef->owner_document();
		break;
		case "prefix":
			$retVal = $obj->nodeRef->prefix();
		break;
		case "textContent":
			$value = $obj->nodeRef->get_content();
			return $ret_bool;
		default:
			$ret_bool = FALSE;
			return $ret_bool;
	}
	if( !is_object($retVal) && !is_array($retVal) ) {
		$value = $retVal;
		return $ret_bool;
	}

	$value = createDoceboDOMObject($retVal,$secondChoice);
	return $ret_bool;
}

function _doceboNodePropertySet( &$obj, $param, &$value) {
	$retVal = false;
	$secondChoice = FALSE;
	$ret_bool = TRUE;
	switch( $param ) {
		case "textContent":
			$obj->nodeRef->set_content($value);
		break;
		default:
			$ret_bool = FALSE;
			return $ret_bool;
	}
	return $ret_bool;
}

?>
