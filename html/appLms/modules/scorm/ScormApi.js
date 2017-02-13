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

/*
 * @module ScormApi.js
 * javascript SCORM API 1.2/1.3
 * @author Emanuele Sandri
 * @date	09/11/2007
 * @version $Id: ScormApi.js 1002 2007-03-24 11:55:51Z fabio $
 */

 /**
  * class ScormAPI
  * @param baseurl the url to be used to contact the soap interface
  *	@param serviceid the service id to map correct service (hostname:port#SOAPLMS)
  *	@param userid the id of the user
  *	@param scormid id of scorm object
  */
 function ScormApi(host, baseurl, serviceid, idUser, idReference, idscorm_organization, scormVersion, environment) {
 	this.id = "";
	this.idscorm_item = "";
	this.idUser = idUser || "";
	this.idReference = idReference || "";
	this.environment = environment || "";
	this.idscorm_organization = idscorm_organization || "";
	this.scormVersion = scormVersion||'';
	this.version = '1.0';

	this.xmldoc = null;
	this.errorCode = "0";
	this.diagnostic = "";
	this.setErrorCodes;
	this.baseurl = baseurl || "";
	this.host = host || "";
	this.serviceid = serviceid || "";
	this.initialize_cb = null;
	this.finish_cb = null;
	this.getvalue_cb = null;
	this.setvalue_cb = null;
	this.commit_cb = null;
	this.transmission_start_cb = null;
	this.transmission_end_cb = null;
	this.dbgLevel = 0;
	this.dbgOut = null;
	this.initialized = false;
	this.finish_launched = false;
	this.basepath = this.baseurl.substring(0, this.baseurl.lastIndexOf( "/" ));

	this.scoStatus = ScormApi.UNINITIALIZED;
	this.toUseWD = true; 					// use wait dialog

	if( this.host != '' )
		loadFromXml( this.basepath + '/scormItemTrackData-'+this.scormVersion+'.xml', null, this );

 }

ScormApi.prototype.setScormVersion = function( version ) {
	this.scormVersion = version;
}

ScormApi.prototype.feelThePresence = function(  ) {
	alert('Yes executor ?');
	return true;
}

ScormApi.UNINITIALIZED = 0;
ScormApi.INITIALIZED = 1;
ScormApi.FINISHED = 2;

ScormApi.prototype.useWaitDialog = function( toUse ) {
	this.toUseWD = toUse;
}

 ScormApi.prototype.setXmlDocument = function( xmldoc ) {
 	this.tomTemplate = xmldoc;
 }

 ScormApi.prototype.setTom = function( tom ) {
 	this.tom = CreateXmlDocument();
 	var rootelem = importAllNode( this.tom, this.tomTemplate.documentElement, true );
 	this.tom.appendChild( rootelem );
	// trasforma il tracciamento dell'utente nel tracciamento template
 	// esegue la navigazione in tutti i nodi di tom e per ogni valore trovato
 	// lo imposta in this.tom
 	rootelem = tom.selectSingleNode('//cmi');
 	this.parseXML( rootelem, '/' , this, this.setTomParam );
 }

 ScormApi.prototype.setTomParam = function( elem, basequery ) {
 	var index = elem.getAttribute('index');
 	var item = elem.getAttribute('item');
	var query = basequery + elem.tagName;
	var elemContainer = this.tom.selectSingleNode(query);
	var value = elem.hasChildNodes()?elem.firstChild.nodeValue:'';

 	if( index != null && index.isInteger() ) {
 		elemContainer.setAttribute('isset','1');
 		// elemento da creare nel this.tom
 		var elemIndex = this.tom.selectSingleNode(query + '/index' );
 		// clono index in un index_entry
		var index_entry = this.tom.createElement('index_entry');
 		elemContainer.appendChild(index_entry);
 		index_entry.setAttribute( "index", index );
		index_entry.setAttribute( "item", "no" );
 		for( var iChild = 0; iChild < elemIndex.childNodes.length; iChild++ ) {
			index_entry.appendChild(elemIndex.childNodes.item(iChild).cloneNode(true));
		}
 		index_entry.setAttribute('isset','1');
		basequery = query + '/index_entry[@index="' + index + '"]/';
		return basequery;
 	} else if( item == 'yes') {
 		elemContainer.setAttribute('isset','1');
 		if( elemContainer.hasChildNodes() )
			elemContainer.firstChild.nodeValue = value;
		else
			elemContainer.appendChild( this.tom.createTextNode(value) );
		basequery = query + '/';
 	} else {
 		elemContainer.setAttribute('isset','1');
 		basequery = query + '/';
 	}
 	return basequery;
 }

 ScormApi.prototype.parseXML = function( elem, basequery, obj, func ) {
 	basequery = func.call( obj, elem, basequery );
 	if( basequery === false ) return;
 	var childs = elem.childNodes;
 	for( var i = 0; i < childs.length; i++ ) {
 		if( childs.item(i).nodeType == 1 ) // solo sugli elementi
	 		this.parseXML( childs.item(i), basequery, obj, func );
 	}
 }

 ScormApi.prototype.getStrTom = function() {
 	this.track = CreateXmlDocument();
 	var rootelem = this.track.createElement('trackobj');
 	this.track.appendChild(rootelem);

 	this.parseXML( this.tom.documentElement, '/trackobj' , this, this.setTrack );

 	var tmpelem = this.track.createElement('remove');
 	var idUser = this.track.createElement('idUser');
 	var idReference = this.track.createElement('idReference');
 	var environment = this.track.createElement('environment');
 	var idscorm_item = this.track.createElement('idscorm_item');

	idUser.appendChild(this.track.createTextNode(this.idUser));
	idReference.appendChild(this.track.createTextNode(this.idReference));
	environment.appendChild(this.track.createTextNode(this.environment));
	idscorm_item.appendChild(this.track.createTextNode(this.idscorm_item));

	tmpelem.appendChild(idUser);
	tmpelem.appendChild(idReference);
	tmpelem.appendChild(environment);
	tmpelem.appendChild(idscorm_item);

 	rootelem.appendChild(tmpelem);

 	return SerializeXML( this.track );
 }

 ScormApi.prototype.setTrack = function( elem, basequery ) {
	var isset = elem.getAttribute('isset');
	if( isset == null || isset == '0' ) return false;
  	var index = elem.getAttribute('index');
 	var item = elem.getAttribute('item');
	var query = basequery;
	var elemContainer = this.track.selectSingleNode(query);
	var value = elem.hasChildNodes()?elem.firstChild.nodeValue:'';

 	if( index != null && index == 'yes' ) {
 		// do nothing!
 	} else if( index != null && index.isInteger() ) {
 		var te = this.track.createElement( elem.parentNode.tagName );
 		elemContainer.appendChild(te);
 		te.setAttribute('index',index);
 		te.setAttribute('item',item);
 		basequery += '/' + te.tagName + '[@index="' + index + '"]';
 	} else if( item == 'yes' ) {
 		var te = importAllNode(this.track, elem, false);
 		elemContainer.appendChild(te);
		if( value != null )
 			te.appendChild(this.track.createTextNode(value));
 		basequery += '/' + te.tagName;
 	} else {
 		var te = importAllNode(this.track, elem, false);
 		elemContainer.appendChild(te);
 		basequery += '/' + te.tagName;
 	}
 	return basequery;
 }

 ScormApi.prototype.setIdscorm_item = function( idscorm_item ) {
 	this.idscorm_item = idscorm_item;
 }

ScormApi.prototype.getIdscorm_item = function() {
 	return this.idscorm_item;
}

 ScormApi.prototype.setIdscorm_organization = function( idscorm_organization ) {
 	this.idscorm_organization = idscorm_organization;
 }
 /*
  * This function indicates to the API Adapter that the SCO is
  * going to communicate with the LMS. It allows the LMS to handle LMS
  * specific initialization issues. It is a requirement of the SCO that
  * it call this function before calling any other API functions.
  */
ScormApi.prototype.LMSInitialize = function( param ) {
	this.initialized = true;
	this.finish_launched = false;
 	// @todo: test the connection with LMS and the prerequisites
	if( this.dbgLevel > 0 ) {
		this.dbgPrint( '+LMSInitialize( "' + param + '" );' );
		this.dbgPrint( '-LMSInitialize:"true"' );
	}
	this.commonLMSInitialize();
	if( this.initialize_cb != null ) {
		try {
			this.initialize_cb(this);
		} catch( ex ) {};
	}
	return new String("true");
}


ScormApi.prototype.LMSFinish = function( param ) {
	this.initialized = false;
	if(this.finish_launched) return new String("true");
	// nothing to do
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSFinish("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();
	result = this.commonLMSFinish();
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSFinish:"' + result + '"' );

	if( this.finish_cb != null )
		this.finish_cb( this );

	this.finish_launched = true;
	return result;
}

ScormApi.prototype.LMSCommit = function( param , /*FIX 17052016*/ callback) {
 	// nothing to do
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSCommit("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

 	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();
	result = this.commonLMSCommit("", /*FIX 17052016*/ callback);
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSCommit:"' + result + '"' );

	if( this.commit_cb != null )
		this.commit_cb( this );
	return result;
}

ScormApi.prototype.LMSGetLastError = function() {
	if( this.dbgLevel > 0 ) {
		this.dbgPrint( '+LMSGetLastError();' );
		this.dbgPrint( '-LMSGetLastError:"' + this.errorCode + '"' );
	}
	return this.errorCode;
}

ScormApi.prototype.LMSGetErrorString = function( ecode ) {
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSGetErrorString("' + ecode + '");' );

	var result = "";
	result = DSCORM_getErrText(ecode);

	if( this.dbgLevel > 0 ) {
		this.dbgPrint( '-LMSGetErrorString:diagnostic"' + this.diagnostic + '"' );
		this.dbgPrint( '-LMSGetErrorString:"' + result + '"' );
	}
	return result;
}

ScormApi.prototype.LMSGetDiagnostic = function( ecode ) {
	if( this.dbgLevel > 0 ) {
		this.dbgPrint( '+LMSGetDiagnostic("' + ecode + '");' );
		this.dbgPrint( '+LMSGetDiagnostic:"' + this.diagnostic + '"' );
	}
 	return this.diagnostic;
}

ScormApi.prototype.LMSGetValue = function( param ) {
	if(this.initialized == false) {
        this.setError("122");
		return new String("");
	}
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSGetValue("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();
	result = this.commonLMSGetValue( param );
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSGetValue:"' + result + '"' );
	if(result == null) result = "";
	//yuiLogMsg( " LMSGetValue( "+param+") return " +result  );
	return result;
}

ScormApi.prototype.LMSSetValue = function( param, data ) {
	//yuiLogMsg( "LMSSetValue( "+param+", "+data+")"  );

	if(this.initialized == false) {
        this.setError("132");
		return new String("false");
	}
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSSetValue("' + param + '", "' + data + '");' );

	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();
	result = this.commonLMSSetValue( param, data );
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSSetValue:"' + result + '"' );

	if(result == null) return new String("false");
	else return new String("true");
}

// =========== Private functions ============================
ScormApi.prototype.commonLMSInitialize = function() {

	strSoap = '<?xml version="1.0" encoding="utf-8"?>'
			+ '<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/"'
			+ ' xmlns:enc="http://schemas.xmlsoap.org/soap/encoding/"'
			+ ' env:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"'
			+ ' xmlns:xs="http://www.w3.org/1999/XMLSchema"'
			+ ' xmlns:xsi="http://www.w3.org/1999/XMLSchema-instance">'
			+	'<env:Header/>'
			+	'<env:Body>'
			+		'<a0:Initialize xmlns:a0="' + this.serviceid + '">'
			+			'<idUser xsi:type="xs:string">' + this.idUser + '</idUser>'
			+			'<idReference xsi:type="xs:string">' + this.idReference + '</idReference>'
			+			'<environment xsi:type="xs:string">' + this.environment + '</environment>'
			+			'<idscorm_item xsi:type="xs:string">' + this.idscorm_item + '</idscorm_item>'
			+		'</a0:Initialize>'
			+	'</env:Body>'
			+ '</env:Envelope>';
	var ajxreq = new Ajax.Request(
       	this.baseurl+'?op=Initialize',
       	{	method: 'post',
			asynchronous:false,
			postBody: strSoap,
			requestHeaders: {
				'Man':"POST " + this.baseurl + " HTTP/1.1",
				'Host':this.host,
				"Content-type":"text/xml; charset=utf-8",
				"SOAPAction":this.serviceid + "Initialize",
				"X-Signature":playerConfig.auth_request
			}
		}
    );

	if( ajxreq.transport.status == 200 ) {
		try {
            // detecting IE
            if(window.ActiveXObject || "ActiveXObject" in window) {  
                var xmldom = new ActiveXObject("Msxml2.DOMDocument.6.0"); 
                xmldom.async = false;
                xmldom.loadXML(ajxreq.transport.responseText);
                xmldom.setProperty("SelectionLanguage", "XPath"); 
                this.setTom(xmldom);
			} else {
                this.setTom( ajxreq.transport.responseXML );
            }   
			this.scoStatus = ScormApi.INITIALIZED;
		} catch (ex) {
			w = window.open('#', 'debug');
			w.document.open();
			w.document.write( ajxreq.transport.responseText );
			w.document.close();
   			alert( "XML exception: "+ex );
			alert( "XML error: "+ajxreq.transport.responseText );
		}
	} else {
		alert( "Server failure: "+ajxreq.transport.status );
		this.setError("101");
		this.diagnostic = ajxreq.transport.responseText;
		alert( "Failure diagnostic: "+this.diagnostic );
	}
	return new String("true");
}

ScormApi.prototype.commonLMSFinish = function() {

	strSoap = this.getStrTom();
	var ajxreq = new Ajax.Request(
       	this.baseurl+'?op=Finish',
       	{	method: 'post',
			asynchronous:false,
			postBody: strSoap,
			requestHeaders: {
				'Man':"POST " + this.baseurl + " HTTP/1.1",
				'Host':this.host,
				"Content-type":"text/xml; charset=utf-8",
				"X-Signature":playerConfig.auth_request
			}
		}
    );

	if( ajxreq.transport.status == 200 ) {
		try {
			var xmldoc = ajxreq.transport.responseXML;
			var status = xmldoc.getElementsByTagName("status").item(0).firstChild;
			var errorCode = xmldoc.getElementsByTagName("error").item(0).firstChild;
			var errorText = xmldoc.getElementsByTagName("errorString").item(0).firstChild;
			if( status.nodeValue == "success" ) {
				return "true";
			} else {
				this.setError(errorCode == null ? "102":errorCode.nodeValue);
				this.diagnostic = errorText == null ? "":errorText.nodeValue;
				return "false";
			}
			this.scoStatus = ScormApi.UNINITIALIZED;
		} catch (ex) {
			w = window.open('#', 'debug');
			w.document.open();
			w.document.write( ajxreq.transport.responseText );
			w.document.close();
   			alert( " Finish: "+ex );
			return "false"
		}
	} else {
		this.setError("101");
		this.diagnostic = ajxreq.transport.responseText;
		return "false"
	}
	return new String("true");
}

ScormApi.prototype.commonLMSGetValue = function( param ) {
	var pi = new ScormParamInfo();
	pi.Initialize( param, this.tom, ScormCache.DSCORM_METHOD_GET, null, this.scormVersion );
	var err = pi.getErrorCode();
	if( err != "0" ) {
		this.setError(err);
		return "";
	} else {
		var result = pi.getParam( param );
		err = pi.getErrorCode();
		if( err != "0" ) {
			this.setError(err);
			return "";
		} else {
			return result;
		}
	}
}

ScormApi.prototype.commonLMSSetValue = function( param, value ) {
	var pi = new ScormParamInfo();
	pi.Initialize( param, this.tom, ScormCache.DSCORM_METHOD_SET, value, this.scormVersion );
	var err = pi.getErrorCode();
	if(err != "0") {
		this.setError(err);
		return null;
	} else {
		var result = pi.setParam(value);
		err = pi.getErrorCode();
		if( err != "0" ) {
			this.setError(err);
			return null;
		} else {
			return result;
		}
	}
}

ScormApi.prototype.commonLMSCommit = function( param , /*FIX 17052016*/ callback ) {
    /*if( this['xmlhttp'] == null ) {
		this.xmlhttp = this.CreateXmlHttpRequest();
	}
	var xmlhttp = this.xmlhttp;

	xmlhttp.open("POST", this.baseurl + '?op=Commit', false);
	xmlhttp.setRequestHeader("Man", "POST " + this.baseurl + " HTTP/1.1");
	xmlhttp.setRequestHeader("Host", this.host );
	xmlhttp.setRequestHeader("Content-Type", "text/xml; charset=utf-8");
	xmlhttp.setRequestHeader("SOAPAction", this.serviceid + "Commit" );

	strSoap = this.getStrTom();

	xmlhttp.send(strSoap);*/

	strSoap = this.getStrTom();

	var ajxreq = new Ajax.Request(
       	this.baseurl+'?op=Commit',
       	{	method: 'post',
			asynchronous:false,
			postBody: strSoap,
			requestHeaders: {
				'Man':"POST " + this.baseurl + " HTTP/1.1",
				'Host':this.host,
				"Content-type":"text/xml; charset=utf-8",
				"SOAPAction":this.serviceid + "Commit" ,
				"X-Signature":playerConfig.auth_request
			}
		}
    );

	if( ajxreq.transport.status == 200 ) {
		try {
			var xmldoc = ajxreq.transport.responseXML;
			var status = xmldoc.getElementsByTagName("status").item(0).firstChild;
			var errorCode = xmldoc.getElementsByTagName("error").item(0).firstChild;
			var errorText = xmldoc.getElementsByTagName("errorString").item(0).firstChild;
			if( status.nodeValue == "success" ) {
        /*FIX 17052016*/
        if (callback) {
          callback();
        }
				return "true";
			} else {
				this.setError(errorCode == null ? "102":errorCode.nodeValue);
				this.diagnostic = errorText == null ? "":errorText.nodeValue;
				return "false";
			}
		} catch (ex) {
			w = window.open('#', 'debug');
			w.document.open();
			w.document.write( ajxreq.transport.responseText );
			w.document.close();
   			alert( " Commit failure: "+ex );
			return "false"
		}
	} else {
		this.setError("101");
		this.diagnostic = ajxreq.transport.responseText;
		return "false"
	}
	return new String("true");
}

ScormApi.prototype.CreateXmlHttpRequest = function() {
	// try first IE
	var xmlhttp = false;
	var actXArray = new Array( 	"MSXML4.XmlHttp", "MSXML3.XmlHttp", "MSXML2.XmlHttp",
								"MSXML.XmlHttp", "Microsoft.XmlHttp" );
	var created = false;

	try {
		xmlhttp = new XMLHttpRequest();
	} catch (e) {
		xmlhttp=false;
	}
	if (!xmlhttp) {

		for( var i = 0; i < actXArray.length && !created; i++ ) {
			try {
				xmlhttp=new ActiveXObject(actXArray[i]);
				created = true;
			} catch (e) {
				// do nothing
			}
		}
	}
	if( !xmlhttp )
		alert( "This browser don't support required functionalities for scorm" );
	return xmlhttp;
}

 /**
  *	@internal
  * Set the error code
  * @param string ecode error code
  * @return null
  */
 ScormApi.prototype.setError = function(ecode) {
 	this.errorCode = ecode;
 }

  /**
  *	@internal
  * Reset the error code
  * @return null
  */
 ScormApi.prototype.resetError = function() {
 	this.errorCode = "0";
 }

 /**
  * @interal
  *	Error table
  */
 ScormApi.prototype.SCORM_STATUS_SUCCESS = "Success";
 ScormApi.prototype.SCORM_STATUS_ERROR = "Error";
 ScormApi.prototype.errorTable = new Object();
 ScormApi.prototype.errorTable["0"] = "No error";
 ScormApi.prototype.errorTable["101"] = "General exception";
 ScormApi.prototype.errorTable["201"] = "Illegal initialization";
 ScormApi.prototype.errorTable["202"] = "Elemet cannot have children";
 ScormApi.prototype.errorTable["203"] = "Elemet not an array - cannot have count";
 ScormApi.prototype.errorTable["301"] = "Not initialized";
 ScormApi.prototype.errorTable["401"] = "Not implemented error";
 ScormApi.prototype.errorTable["402"] = "Invalid set value, element is a keyword";
 ScormApi.prototype.errorTable["403"] = "Element is read only";
 ScormApi.prototype.errorTable["404"] = "Element is write only";
 ScormApi.prototype.errorTable["405"] = "Incorrect data type";

 /**
  *	@internal
  * Scorm LMS path
  */
 ScormApi.prototype.LMSUrl = "";


 /**
  * @internal
  * Debug print out function
  */
ScormApi.prototype.dbgPrint = function( text ) {
	var doc = this.dbgOut.ownerDocument;
	var outelem = doc.createTextNode('[' + this.userid + ',' + this.scoid + ',' + this.idscormpackage + '] ' + text);
	var crelem = doc.createElement("BR");
	this.dbgOut.appendChild(outelem);
	this.dbgOut.appendChild(crelem);
}

function ScormApiUI( host, baseurl, serviceid, idUser, idReference, idscorm_organization, scormVersion, environment ) {
	this.base = ScormApi;
	this.base(host, baseurl, serviceid, idUser, idReference, idscorm_organization, scormVersion, environment);
	this.slEventCap = 0;
	this.sStyle = "dialogHeight:100px;dialogWidth:150px";
}

ScormApiUI.prototype = new ScormApi;

ScormApiUI.prototype.LMSInitialize = function( param ) {

	if(this.initialized != false) {
        this.setError("103");
		return new String("false");
	}
	if(param !== "") {
        this.setError("201");
		return new String("false");
	}
	this.initialized = false;

	// nothing to do
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSInitialize("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();

	if( window.document.all && this.toUseWD) {	//IE
		// compute page position
		var args = new Object();
		args.sapi = this;
		args.func = "Initialize";
		args.param = param;
		result = showModalDialog( this.basepath + "/dialog.php", args, this.sStyle );
	} else {
		slStopEvents();
		result = this.commonLMSInitialize();
		window.setTimeout( slStartEvents, 500 );
	}
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSInitialize:"' + result + '"' );

	if( this.initialize_cb != null )
		this.initialize_cb( param );

	this.initialized = true;
	this.finish_launched = false;

	// loaded, we canrelease the event block
	relwin();
	return String(result);
}

ScormApiUI.prototype.LMSFinish = function( param ) {
	this.initialized = false;
	if(this.finish_launched) return new String("true");
	// nothing to do
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSFinish("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();

	if( window.document.all && this.toUseWD) {	//IE
		// compute page position
		var args = new Object();
		args.sapi = this;
		args.func = "Finish";
		args.param = param;
		result = showModalDialog( this.basepath + "/dialog.php", args, this.sStyle );
	} else {
		slStopEvents();
		result = this.commonLMSFinish();
		window.setTimeout( slStartEvents, 500 );
	}
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSFinish:"' + result + '"' );

	if( this.finish_cb != null )
		this.finish_cb( param );
	this.finish_launched = true;
	return result;
}

ScormApiUI.prototype.LMSCommit = function( param ) {
	if(this.initialized == false) {
        this.setError("142");
		return new String("false");
	}
	if(param !== "") {
        this.setError("201");
		return new String("false");
	}

 	// nothing to do
	if( this.dbgLevel > 0 )
		this.dbgPrint( '+LMSCommit("' + param + '"); [' + this.idUser + ',' + this.idReference + ',' + this.idscorm_item + ']' );

 	this.resetError();
	var result = "";
	if( this.transmission_start_cb != null )
		this.transmission_start_cb();

	if( window.document.all && this.toUseWD ) {	//IE
		// compute page position
		var args = new Object();
		args.sapi = this;
		args.func = "Commit";
		args.param = param;
		result = showModalDialog( this.basepath + "/dialog.php", args, this.sStyle );
	} else {
		slStopEvents();
		result = this.commonLMSCommit();
		window.setTimeout(slStartEvents, 500);
	}
	if( this.transmission_end_cb != null )
		this.transmission_end_cb();

	if( this.dbgLevel > 0 )
		this.dbgPrint( '-LMSCommit:"' + result + '"' );

	if( this.commit_cb != null )
		this.commit_cb( param );
	return result;
}

// =========== SCORM 1.3 API wrapper ========================
ScormApiUI.prototype.Initialize = function(param){
	return this.LMSInitialize(param);
}

ScormApiUI.prototype.GetLastError = function() {
	return this.LMSGetLastError();
}

ScormApiUI.prototype.GetErrorString = function(ecode){
	return this.LMSGetErrorString(ecode);
}

ScormApiUI.prototype.GetDiagnostic = function(ecode){
	return this.LMSGetDiagnostic(ecode);
}

ScormApiUI.prototype.Terminate = function( param ){
	return this.LMSFinish(param);
}

ScormApiUI.prototype.GetValue = function( param ){
	return this.LMSGetValue(param);
}

ScormApiUI.prototype.SetValue = function(param, data){
	return this.LMSSetValue(param, data);
}

ScormApiUI.prototype.Commit = function(param){
	return this.LMSCommit(param);
}

function eventCanceller(ev) {
	if (!ev) var ev = window.event;
	if( ev.stopPropagation ) {
		ev.preventDefault(); //DOM
		ev.stopPropagation();
	} else {
		ev.cancelBubble = true; //IE
	}
	return false;
}

var slEventCap = 0;
var capped_win = null;
var disable_chapter_change = false;

// capture some window's events
function capwin() {
	disable_chapter_change = true;
	setTimeout("relwin()", 4000);
	/*
	return;
	//alert(w.document.anchors );
	if( w.attachEvent ) { //IE 5+

		for(var i = 0,l = w.document.anchors.length; i < l; i++) {
			var a = w.document.anchors[i];

			a.attachEvent( "onclick",eventCanceller );
			a.attachEvent( "onmousedown",eventCanceller );
			a.attachEvent( "onfocus",eventCanceller );
		}
	} else {

		// geko
		w.addEventListener("click", eventCanceller, true);
		w.addEventListener("mousedown", eventCanceller, true);
		w.addEventListener("focus", eventCanceller, true);
	}
	*/
}

// release the captured events
function relwin() {

	disable_chapter_change = false;
	/*
	try {
		if( w.attachEvent ) { //IE 5+
			w.detachEvent( "onclick", eventCanceller );
			w.detachEvent( "onmousedown", eventCanceller );
			w.detachEvent( "onfocus", eventCanceller );
		} else {
			w.removeEventListener("focus", eventCanceller, true);
			w.removeEventListener("mousedown", eventCanceller, true);
			w.removeEventListener("click", eventCanceller, true);
		}
	} catch( ex ) {
		// do nothing
	}*/
}


function slStopEvents() {

	slEventCap++;
	if( slEventCap > 1 )
		return;
	capwin();
}

function slStartEvents() {

	slEventCap--;
	if( slEventCap > 0 )
		return;
	//relwin();
}

function loadFromXml(xml_file, renderer, obj_ref) {

	// detecting IE
    if(window.ActiveXObject || "ActiveXObject" in window)  {  

		var xmlhttp = new Ajax.Request(xml_file, {
				method: 'get',
				asynchronous:false
			}
	    );
		if( xmlhttp.transport.status == 200 ) {

            var xmldom = new ActiveXObject("Msxml2.DOMDocument.6.0"); 
			xmldom.async = false;
			xmldom.loadXML(xmlhttp.transport.responseText);
            xmldom.setProperty("SelectionLanguage", "XPath"); 
            

			if( xmldom.parseError.errorCode != 0 ) {
				alert( "xml parser error:"
						+ "\n code = " + xmldom.parseError.errorCode
						+ "\n reason = " + xmldom.parseError.reason
						+ "\n line = " + xmldom.parseError.line
						+ "\n srcText = " + xmldom.parseError.srcText
						+ "\n xml_file = " + xmlhttp.transport.responseText );
				return false;
			}


			if( renderer == null ) obj_ref.setXmlDocument(xmldom);
			else renderer(xmldom);

			return true;
		}

	} else {

		// load with xmlhttp, ema like this way ...
	/*		xmlhttp = new XMLHttpRequest();
		xmlhttp.open("GET", xml_file, false);
		xmlhttp.send(null);*/
		var xmlhttp = new Ajax.Request(xml_file, {
				method: 'get',
				asynchronous:false
			}
	    );
		if( xmlhttp.transport.status == 200 ) {
			try {

				if( renderer == null ) obj_ref.setXmlDocument(xmlhttp.transport.responseXML);
				else renderer(xmlhttp.transport.responseXML);
			} catch (ex) {

				alert( 'Load from xml:'+xmlhttp.transport.responseText );
			}
		} else {
			alert( 'Load from xml:'+xmlhttp.transport.responseText );
		}
	}
  	return false;
}

function CreateXmlDocument() {
	var xdoc;
	 
    if(window.ActiveXObject || "ActiveXObject" in window)  {  
        var xdoc = new ActiveXObject("Msxml2.DOMDocument.6.0"); 
	} else if( document.implementation && document.implementation.createDocument ) {
		xdoc = document.implementation.createDocument("", "", null);
	} else {
		xdoc = false;
	}
	return xdoc;
}

function SerializeXML( domxml ) {
	try {
		var serializer = new XMLSerializer();
		return serializer.serializeToString(domxml);
	} catch(ex) {
		return domxml.xml;
	}
}

/**
 * function importAllNode - IE don't implemente the W3C standard DOMDocument method
 * importNode. We use this function to walk around the MS hole.
 * @param Document xmldoc targhet of the import
 * @param Node node the node to copy in xmldoc
 * @param boolean bImportChildren if it's true the function import oNode and all
 *								it's childrens and descendants
 * @return Node the imported node in the xmldoc context
 **/
function importAllNode(xmldoc, oNode, bImportChildren){

	// If IE
    if(window.ActiveXObject || "ActiveXObject" in window)  {  
		var oNew;

		if(oNode.nodeType == 1){
			oNew = xmldoc.createElement(oNode.nodeName);
			for(var i = 0; i < oNode.attributes.length; i++){
				oNew.setAttribute(oNode.attributes[i].name, oNode.attributes[i].value);
			}
		} else if(oNode.nodeType == 3){
			oNew = xmldoc.createTextNode(oNode.nodeValue);
		}

		if(bImportChildren && oNode.hasChildNodes()){
			for(var oChild = oNode.firstChild; oChild; oChild = oChild.nextSibling){
				oNew.appendChild(importAllNode(xmldoc, oChild, true));
			}
		}

		return oNew;
	} else {
		return xmldoc.importNode(oNode, bImportChildren);
	}
}
