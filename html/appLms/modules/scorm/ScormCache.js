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

// JavaScript Document

ScormCache = {};

ScormCache.DSCORM_METHOD_GET = 0;
ScormCache.DSCORM_METHOD_SET = 1;

ScormCache.DSCORM_PI_READWRITE = 0;
ScormCache.DSCORM_PI_READONLY = 1;
ScormCache.DSCORM_PI_WRITEONLY = 2;

ScormCache.DSCORM_ERR_NOERROR = 0;
// general error
ScormCache.DSCORM_ERR_GEN_EXCEPTION = 101;
ScormCache.DSCORM_ERR_GEN_INITFAIL = 102;			// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_ALREADYINIT = 103;		// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_INSTTERMINATED = 104;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_TERM_FAILURE = 111;		// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_TERM_INIT = 112;		// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_TERM_TERM = 113;		// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_GET_BEFORE_INIT = 122;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_GET_AFTER_TERM = 123;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_SET_BEFORE_INIT = 132;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_SET_AFTER_TERM = 133;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_COMM_BEFORE_INIT = 142;	// not possible at server side in this impl.
ScormCache.DSCORM_ERR_GEN_COMM_AFTER_TERM = 143;	// not possible at server side in this impl.

// syntax error
ScormCache.DSCORM_ERR_SYN_INVALID_ARG = 201;

// RTS error
ScormCache.DSCORM_ERR_RTS_GET = 301;
ScormCache.DSCORM_ERR_RTS_SET = 351;
ScormCache.DSCORM_ERR_RTS_COMM = 391;

// RTS suberror
ScormCache.DSCORM_DIAG_NOERROR = 0;
ScormCache.DSCORM_DIAG_NOTHAVECHILDREN = 1;
ScormCache.DSCORM_DIAG_NOTHAVECOUNT = 2;
ScormCache.DSCORM_DIAG_COLLECTOUTOFORDER = 3;
ScormCache.DSCORM_DIAG_COLLECTOUTOFRANGE = 4;
ScormCache.DSCORM_DIAG_ELEMNOTSPECIFIED = 5;
ScormCache.DSCORM_DIAG_UNIDVIOLATION = 6;
ScormCache.DSCORM_DIAG_NOTHAVEVERSION = 7;

// data model error
ScormCache.DSCORM_ERR_DM_UNDEF = 401;
ScormCache.DSCORM_ERR_DM_NOTIMP = 402;
ScormCache.DSCORM_ERR_DM_NOTINIT = 403;
ScormCache.DSCORM_ERR_DM_READONLY = 404;
ScormCache.DSCORM_ERR_DM_WRITEONLY = 405;
ScormCache.DSCORM_ERR_DM_TYPEMISMATCH = 406;
ScormCache.DSCORM_ERR_DM_OUTOFRANGE = 407;
ScormCache.DSCORM_ERR_DM_DEPNOTINIT = 408;

function DSCORM_getErrText( code ) {
	switch( code ) {
		case ScormCache.DSCORM_ERR_NOERROR:
			return "No error";
		case ScormCache.DSCORM_ERR_GEN_EXCEPTION:
			return "General exception";
		case ScormCache.DSCORM_ERR_GEN_INITFAIL:
			return "General initialization failure";
		case ScormCache.DSCORM_ERR_GEN_ALREADYINIT:
			return "Already initialized";
		case ScormCache.DSCORM_ERR_GEN_INSTTERMINATED:
			return "Content instance terminated";
		case ScormCache.DSCORM_ERR_GEN_TERM_FAILURE:
			return "General termination failure";
		case ScormCache.DSCORM_ERR_GEN_TERM_INIT:
			return "Termination before initialization";
		case ScormCache.DSCORM_ERR_GEN_TERM_TERM:
			return "Termination after termination";
		case ScormCache.DSCORM_ERR_GEN_GET_BEFORE_INIT:
			return "Retrieve data before initialization";
		case ScormCache.DSCORM_ERR_GEN_GET_AFTER_TERM:
			return "Retrieve data after termination";
		case ScormCache.DSCORM_ERR_GEN_SET_BEFORE_INIT:
			return "Store data before initialization";
		case ScormCache.DSCORM_ERR_GEN_SET_AFTER_TERM:
			return "Store data after termination";
		case ScormCache.DSCORM_ERR_GEN_COMM_BEFORE_INIT:
			return "Commit before initialization";
		case ScormCache.DSCORM_ERR_GEN_COMM_AFTER_TERM:
			return "Commit after termination";
		
		case ScormCache.DSCORM_ERR_SYN_INVALID_ARG:
			return "General argument error";
		
		case ScormCache.DSCORM_ERR_RTS_GET:
			return "General get error";
		case ScormCache.DSCORM_ERR_RTS_SET:
			return "General set error";
		case ScormCache.DSCORM_ERR_RTS_COMM:
			return "General commit error";
			
		case ScormCache.DSCORM_ERR_DM_UNDEF:
			return "Undefined data model element";
		case ScormCache.DSCORM_ERR_DM_NOTIMP:
			return "Unimplemented data model element";
		case ScormCache.DSCORM_ERR_DM_NOTINIT:
			return "Data model element value not initialized";
		case ScormCache.DSCORM_ERR_DM_READONLY:
			return "Data model element is read only";
		case ScormCache.DSCORM_ERR_DM_WRITEONLY:
			return "Data model element is write only";
		case ScormCache.DSCORM_ERR_DM_TYPEMISMATCH:
			return "Data model element type mismatch";
		case ScormCache.DSCORM_ERR_DM_OUTOFRANGE:
			return "Data model element value out of range";
		case ScormCache.DSCORM_ERR_DM_DEPNOTINIT:
			return "Data model dependency not established";
			
		default:
			return "";
	}
}

function DSCORM_getDiagText( errCode, errSubcode ) { 
	switch( errSubcode ) {
		case ScormCache.DSCORM_DIAG_NOERROR:
			return DSCORM_getErrText( errCode );
		case ScormCache.DSCORM_DIAG_NOTHAVECHILDREN:
			return "Data model element does not have children";
		case ScormCache.DSCORM_DIAG_NOTHAVECOUNT:
			return "Data model element does not have count";
		case ScormCache.DSCORM_DIAG_COLLECTOUTOFORDER:
			return "Data model element collection set out of order";
		case ScormCache.DSCORM_DIAG_COLLECTOUTOFRANGE:
			return "Data model element collection set out of range";
		case ScormCache.DSCORM_DIAG_ELEMNOTSPECIFIED:
			return "Data model element not specified";
		case ScormCache.DSCORM_DIAG_UNIDVIOLATION:
			return "Unique identifier constraint violated";
		case ScormCache.DSCORM_DIAG_NOTHAVEVERSION:
			return "Smallest permitted maximum exceeded";
		default:
			return "Unknown diagnostic";
	}
}

function ScormParamInfo() {
	this.m_paramName = null;
	this.m_paramType = null;
	this.m_paramSubtype = null;
	this.m_access = null;
	this.m_isItem = false;
	this.m_nodeModel = null;
	this.m_paramRequested = null;
	this.m_dom = null;
	this.m_scormversion = null; 
	
	this.m_errCode = 0;
	this.m_errSubcode = 0;
}

String.prototype.trim = function () {
	return this.replace(/^\s+|\s+$/,'');
}	

String.prototype.isInteger = function() {
	var n = this.trim();
	return n.length > 0 && !(/[^0-9]/).test(n);
}

/** Traduce un percorso del Data Model di SCORM in query XPath
 * Utilizza solo gli elementi compresi tra startIndex ed endIndex 
 * estremi inclusi
 **/
ScormParamInfo.prototype.ComputeXPath = function( arrTokens, startIndex, endIndex ) { 
	var arrQuery = new Array();
	var iTok;

	for( iTok = startIndex; iTok <= endIndex; iTok++ ) {
		if( arrTokens[iTok].isInteger() )
			arrQuery.push('index_entry[@index="' + arrTokens[iTok] + '"]');
		else
			arrQuery.push(arrTokens[iTok]);
	}
			
	return arrQuery
}
	
	// Traduce un percorso del Data Model di SCORM in query XPath
	// Utilizza solo gli elementi compresi tra startIndex ed endIndex 
	// estremi inclusi
ScormParamInfo.prototype.StrComputeXPath = function( arrTokens, startIndex, endIndex ) {
	var iTok;
	var strQuery = '';
		
	for( iTok = startIndex; iTok <= endIndex; iTok++ ) {
		if( arrTokens[iTok].isInteger() ) 
			strQuery += '/index_entry[@index="' + arrTokens[iTok] + '"]';
		else
			strQuery += "/" + arrTokens[iTok];
	}
			
	return strQuery;
}
	
ScormParamInfo.prototype.Initialize = function( param, dom, method, value, scormVersion ) {
	this.m_dom = dom;
	this.m_paramRequested = param;
	this.m_scormversion = scormVersion;

	if( method == ScormCache.DSCORM_METHOD_GET ) 
		return this.InitializeGet( param );
	else
		return this.InitializeSet( param, value );
}
	
ScormParamInfo.prototype.InitializeGet = function( param ) {
	
	var arrTokens;
	var arrQuery;
	var strQuery;
	
	arrTokens = param.split('.');

	if( arrTokens[0] == 'adl' ) {
		this.m_errCode = ScormCache.DSCORM_ERR_DM_NOTIMP;
		this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
		return false;		
	}

	if( !param ) {
		this.m_errCode = ScormCache.DSCORM_ERR_RTS_GET;
		this.m_errSubCode = ScormCache.DSCORM_DIAG_COLLECTOUTOFORDER;
		return false;
	}
	if( arrTokens[arrTokens.length-1] == '_count' ) {
		// ===== handle _count initialization =============
		strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-2 );
		this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
		
		if( this.m_nodeModel.getAttribute("index") != "yes" ) {

			this.m_errCode = ScormCache.DSCORM_ERR_RTS_GET;
			this.m_errSubCode = ScormCache.DSCORM_DIAG_NOTHAVECOUNT;
			return false;
		} else {

			this.m_nodeModel = this.m_nodeModel.selectNodes( "index_entry" );
			this.m_paramName = "_count";
			this.m_paramType = "int";
			this.m_paramSubtype = "";
			this.m_isItem = true;
			this.m_access = ScormCache.DSCORM_PI_READONLY;
			this.m_errCode = ScormCache.DSCORM_ERR_NOERROR;
			this.m_errSubcode = ScormCache.DSCORM_DIAG_NOERROR;
			return true;
		}
	} else {
		strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-1 );
		this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
		if( this.m_nodeModel == null ) {
			if( arrTokens[arrTokens.length-1] == "_children" ) {

				this.m_errCode = ScormCache.DSCORM_ERR_RTS_GET;
				this.m_errSubCode = ScormCache.DSCORM_DIAG_NOTHAVECHILDREN;
				return false;
			} else {

				this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
				this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
				return false;
			}
		} else {

			this.m_paramName = this.m_nodeModel.nodeName;
			this.m_paramType = this.m_nodeModel.getAttribute("DataType");
			this.m_paramSubtype = this.m_nodeModel.getAttribute("DataSubtype");
			this.m_access = this.getAccessCode( this.m_nodeModel.getAttribute("SCOAccessibility") );
	
			var strIsItem = this.m_nodeModel.getAttribute("item");
			this.m_isItem = (strIsItem=='yes');

			this.m_errCode = ScormCache.DSCORM_ERR_NOERROR;
			this.m_errSubcode = ScormCache.DSCORM_DIAG_NOERROR;
			return true;
		}
	}
}
	
ScormParamInfo.prototype.InitializeSet = function( param, value ) {
	// Due possibilità:
	//	o c'e' gia' il tag, e allora si tratta semplicemente di settare il valore
	//	oppure non c'e' nel qual caso bisogna controllare che e' un nuovo elemento
	//	di un packed array, e se previsto che ci sia l'id corretto
	var arrTokens;
	var arrQuery;
	var strQuery;
	var newTree;
	var newIndex;
	var index_entry;
	var elem;

	arrTokens = param.split('.');
	
	if( !param ) {
		this.m_errCode = ScormCache.DSCORM_ERR_RTS_SET;
		this.m_errSubCode = ScormCache.DSCORM_DIAG_COLLECTOUTOFORDER;
		return false;
	}
	if( arrTokens[arrTokens.length-1] == "_count" || arrTokens[arrTokens.length-1] == "_children" ) {
		// --- ci togliamo subito dai piedi questi elementi readonly
		this.m_errCode = ScormCache.DSCORM_ERR_DM_READONLY;
		this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
		return false;
	}

	// cerchiamo l'elemento direttamente
	strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-1 );
	this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
	if( this.m_nodeModel == null ) {
		// creazione di un nuovo elemento? 
		// Ci sono una serie di condizioni da controllare
		// 1) Il path deve avere come penultimo elemento un numero
		if( !arrTokens[arrTokens.length-2].isInteger() ) {
			this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
			this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
			return false;			
		}
		
		newIndex = parseInt(arrTokens[arrTokens.length-2]);
		
		// 2) Deve esistere arrTokens(0)/arrTokens(1)/... /arrTokens(n-2)/index
		if( arrTokens.length > 3 ) {
			strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-3 ) + "/index/*";
			newTree = this.m_dom.selectNodes( strQuery );
			if( newTree.length == 0 ) { 
				this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
				this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
				return false;
			}
		}
		
		// 3) Non deve esistere arrTokens(0)/arrTokens(1)/... /arrTokens(n-1)
		strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-2 );
		this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
		if( !this.m_nodeModel == null ) {
			this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
			this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
			return false;
		}
		
		// 4) O newIndex = 0 oppure deve esistere arrTokens(0)/arrTokens(1)/... /(newIndex-1)
		if( newIndex > 0 ) {
			strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-3) + '/index_entry[@index="' + (newIndex-1) + '"]';
			this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
			if( this.m_nodeModel == null ) {
				this.m_errCode = ScormCache.DSCORM_ERR_RTS_SET;
				this.m_errSubCode = ScormCache.DSCORM_DIAG_COLLECTOUTOFORDER;
				return false;
			}
		}

		// 5) deve essere già stato imposta arrTokens(0)/arrTokens(1)/... /(newIndex-1)/id se non sti sta inserendo un id
		if( arrTokens[arrTokens.length-1].indexOf("id") == -1 && (
			arrTokens[arrTokens.length-3].indexOf("interactions") == 0
			|| arrTokens[arrTokens.length-3].indexOf("objectives") == 0  ) ) {
			strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-3) + '/index_entry[@index="' + (newIndex) + '"]/id';
			this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
			if( this.m_nodeModel == null ) {
				this.m_errCode = ScormCache.DSCORM_ERR_DM_NOTINIT;
				this.m_errSubCode = ScormCache.DSCORM_DIAG_NOERROR;
				return false;
			}
		}

		// -- trovato index da cui copiare.
		//    esecuzione copia 
		this.m_nodeModel = newTree.item(0).parentNode.parentNode;
		index_entry = this.m_dom.createElement("index_entry");
		index_entry.setAttribute( "index", newIndex );
		index_entry.setAttribute( "item", "no" );
		index_entry.setAttribute( "isset", "1" );
		for( var iChild = 0; iChild < newTree.length; iChild++ ) {
			index_entry.appendChild(newTree.item(iChild).cloneNode(true));
		}

		// -- attach newTree in the rigth location
		this.m_nodeModel.appendChild(index_entry);
		this.m_nodeModel.setAttribute( "isset", "1" );
		strQuery = this.StrComputeXPath( arrTokens, 0, arrTokens.length-1 );
		this.m_nodeModel = this.m_dom.selectSingleNode( strQuery );
	}

	this.m_paramName = this.m_nodeModel.nodeName;
	this.m_paramType = this.m_nodeModel.getAttribute("DataType");
	this.m_paramSubtype = this.m_nodeModel.getAttribute("DataSubtype");
	this.m_access = this.getAccessCode( this.m_nodeModel.getAttribute("SCOAccessibility") );

	var strIsItem = this.m_nodeModel.getAttribute("item");
	this.m_isItem = (strIsItem=='yes');

	this.m_errCode = ScormCache.DSCORM_ERR_NOERROR;
	this.m_errSubcode = ScormCache.DSCORM_DIAG_NOERROR;
	
	return true;
}
	
	
ScormParamInfo.prototype.getAccessCode = function( strCode ) { 
	switch (strCode) {
		case "readonly":
			return ScormCache.DSCORM_PI_READONLY;
		case "writeonly":
			return ScormCache.DSCORM_PI_WRITEONLY;
		case "readwrite":
			return ScormCache.DSCORM_PI_READWRITE;
	}
}
	
ScormParamInfo.prototype.isReadOnly = function() {
	return (this.m_access == ScormCache.DSCORM_PI_READONLY);
}

ScormParamInfo.prototype.isWriteOnly = function() {
	return (this.m_access == ScormCache.DSCORM_PI_WRITEONLY);
}

ScormParamInfo.prototype.isReadable = function() {
	return ((this.m_access == ScormCache.DSCORM_PI_READWRITE) || (this.m_access == ScormCache.DSCORM_PI_READONLY));
}
	
ScormParamInfo.prototype.isWritable = function() { 
	return ((this.m_access == ScormCache.DSCORM_PI_READWRITE) || (this.m_access == ScormCache.DSCORM_PI_WRITEONLY));
}

ScormParamInfo.prototype.isItem = function() {
	return this.m_isItem;
}

ScormParamInfo.prototype.getParamType = function() {
	return this.m_paramType;
}
	
ScormParamInfo.prototype.getParamSubtype = function() {
	return this.m_paramSubtype;
}
	
ScormParamInfo.prototype.getParamName = function() { 
	return this.m_paramName;
}
	
ScormParamInfo.prototype.getParamRequested = function() {
	return this.m_paramRequested;
}
	
ScormParamInfo.prototype.getParamValue = function() {
	if( this.m_paramName == "_count" ) {
		return this.m_nodeModel.length;
	} else {
		if( this.m_nodeModel.hasChildNodes() ) {
			return this.m_nodeModel.firstChild.nodeValue;
		} else {
			return null;
		}
	}
}

ScormParamInfo.prototype.setParamValue = function( value ) {
	if( this.m_nodeModel.hasChildNodes() )
		this.m_nodeModel.firstChild.nodeValue = value;
	else
		this.m_nodeModel.appendChild( this.m_dom.createTextNode(value) );
	this.m_nodeModel.setAttribute('isset', '1');
	return String("");
}

ScormParamInfo.prototype.getParam = function( param ) {
	
	var arrTokens = param.split('.');
	if( !this.isItem() ) {
		this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
		this.m_errText = 'The param requested isn\'t an item';
		return null;		
	}
	if( this.isReadable() ) {
		try{
			//check if is a runtime element that is reader before setted

			if( this.m_nodeModel != null
				&& this.m_nodeModel.getAttribute("isset") != "1"
				&& arrTokens[arrTokens.length-2].isInteger() == true ) {
				
				this.m_errCode = ScormCache.DSCORM_ERR_RTS_GET;
				this.m_errSubcode = ScormCache.DSCORM_DIAG_NOERROR;
			} /*else
			// can create problems with scorm 1.2 and some type of lo
			//check if is a native element that is reader before setted
			if( this.m_nodeModel != null
				&& this.m_nodeModel.getAttribute("isset") != "1"
				&& arrTokens[arrTokens.length-1] != "_children" 
				&& param != 'cmi.suspend_data'
				&& param != 'cmi._version') {

				this.m_errCode = ScormCache.DSCORM_ERR_DM_NOTINIT;
				this.m_errSubcode = ScormCache.DSCORM_DIAG_NOERROR;
			}*/
		}catch(e){}
		return this.getParamValue();
	} else {
		this.m_errCode = ScormCache.DSCORM_ERR_DM_WRITEONLY;
		this.m_errText = DSCORM_getErrText( ScormCache.DSCORM_ERR_DM_WRITEONLY );
		return null;
	}
}

ScormParamInfo.prototype.setParam = function( value ) {
	if( !this.isItem() ) {
		this.m_errCode = ScormCache.DSCORM_ERR_DM_UNDEF;
		this.m_errText = 'The param requested isn\'t an item';
		return null;		
	}

	if( this.isWritable() ) {
		var arrayTypes = this.getParamType().split(',');
		var isCorrectType = false;
		for( var i = 0; i < arrayTypes.length; i++ ) {
			if( checkScormType( arrayTypes[i], this.getParamSubtype(), value, this.m_scormversion ) ) {
				isCorrectType = true;
				break;
			}	
		}
		if( isCorrectType ) {
			return this.setParamValue( value );
		} else {
			this.m_errCode = ScormCache.DSCORM_ERR_DM_TYPEMISMATCH;
			this.m_txtCode = DSCORM_getErrText( ScormCache.DSCORM_ERR_DM_TYPEMISMATCH );
			return null;
		}
	} else {
		this.m_errCode = ScormCache.DSCORM_ERR_DM_READONLY;
		this.m_errText = DSCORM_getErrText( ScormCache.DSCORM_ERR_DM_READONLY );
		return null;
	}
}
	
ScormParamInfo.prototype.getErrorCode = function() {
	return this.m_errCode;
}
	
ScormParamInfo.prototype.getErrorSubCode = function() {
	return this.m_errSubCode;
}
