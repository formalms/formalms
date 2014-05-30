
/**
 * ScormPlayer l'oggetto che gestisce le informazioni dello stato
 * del player: tree, LO successivo, LO precedente etc...
 **/
function ScormPlayer() {
	this.xmlTree = null; 			// xml dell'albero
	this.api = null;				// api scorm
	this.listeners = new Object();	// lista dei listeners
	this.actionQueue = new Array(); // lista delle actions -- non usato ora
	this.nextScoId = null;			// id dello sco successivo
	this.basePath = '';				// il path da cui partire per cercare altri file
	this.blankPage = '/scorm_page_body.php';
	this.lmsBase = '';
}

ScormPlayer.prototype.onInitialize = function() {
	this._fireEvent( 'Initialize', this.api.getIdscorm_item() );
}

ScormPlayer.prototype.onFinish = function() {
	this._fireEvent( 'Finish', this.api.getIdscorm_item() );
}

ScormPlayer.prototype.onCommit = function() {
	this._fireEvent( 'Commit', this.api.getIdscorm_item() );
}

ScormPlayer.prototype.onGetValue = function() {
	this._fireEvent( 'GetValue', this.api.getIdscorm_item() );
}

ScormPlayer.prototype.onSetValue = function() {
	this._fireEvent( 'SetValue', this.api.getIdscorm_item() );
}


ScormPlayer.prototype.setTree = function( xmldoc ) {
	this.xmlTree = xmldoc;
}

ScormPlayer.prototype.setPath = function( basePath, lmsBase ) {
	this.basePath = basePath;
	this.lmsBase = lmsBase;
}

ScormPlayer.prototype.setAPI = function( api ) {
	this.api = api;

	// set call backs
	window._sp = this;
	this.api.initialize_cb = function() { window._sp.onInitialize(); };
	this.api.finish_cb = function() { window._sp.onFinish(); };
	this.api.commit_cb = function() { window._sp.onCommit(); };
	this.api.getValue_cb = function() { window._sp.onGetValue(); };
	this.api.setValue_cb = function() { window._sp.onSetValue(); };
}

/**
 * getTitleCP torna il titolo del content package
 **/
ScormPlayer.prototype.getTitleCP = function() {
	var title = this.xmlTree.selectSingleNode( "/item/title/text()");
	return title.nodeValue;
}

ScormPlayer.prototype.getPackage = function() {
	var sco_package = this.xmlTree.selectSingleNode( "/item/@package");
	return sco_package.nodeValue;
}

// return the discplay name of 'scoid'
ScormPlayer.prototype.getScoName = function( scoid ) {

	var item = this.xmlTree.selectSingleNode('//item[@id="'+scoid+'"]');
	if( item != null ) {
		return item.firstChild.firstChild.nodeValue;
	} else {
		return null;
	}
}

// get the scoid of the current sco played
ScormPlayer.prototype.getCurrScoId = function() {
	var item = this.xmlTree.selectSingleNode('//item[@uniqueid="'+this.api.getIdscorm_item()+'"]');
	if( item != null ) {
		return item.getAttribute('id');
	} else {
		return null;
	}
}

// fint the first sco of the organization
ScormPlayer.prototype.getFirstScoId = function() {
	var item = this.xmlTree.selectSingleNode('//item[@resource!=""]');
	if( item != null ) {
		return item.getAttribute('id');
	} else {
		return null;
	}
}

// find the last sco beforer the 'scoid' one
ScormPlayer.prototype.getPrevScoId = function( scoid ) {
	var item = this.xmlTree.selectSingleNode('//item[@id="'+scoid+'"]');
	item = item.selectNodes('preceding::item[@isLeaf="1"]');
	if( item.length > 0 ) {
		return item[item.length-1].getAttribute('id');
	} else {
		return null;
	}
}

// find the first sco after the 'scoid' one
ScormPlayer.prototype.getNextScoId = function( scoid ) {
	var item = this.xmlTree.selectSingleNode('//item[@id="'+scoid+'"]');
	item = item.selectSingleNode('following::item[@isLeaf="1"]');
	if( item != null ) {
		return item.getAttribute('id');
	} else {
		return null;
	}
}

// find the first incomplete or neverstarted sco after the 'scoid' one
ScormPlayer.prototype.getNextIncompleteScoId = function( scoid ) {
	var item = this.xmlTree.selectSingleNode('//item[@resource!="" and (@status="incomplete" or @status="neverstarted" or @status="not attempted" or @status="failed")]');
	if( item == null )
		item = this.xmlTree.selectSingleNode('//item[@resource!=""]');
	if( item != null ) {
		return item.getAttribute('id');
	} else {
		return null;
	}
}

/* = prev of current ======================== */

// check if there is a sco that precede the current ones
ScormPlayer.prototype.prevScoExists = function () {

	if(!this.nextScoId) var cur_sco = this.getCurrScoId();
	else var cur_sco = this.nextScoId;
	if(!cur_sco) return false;

	var prev_sco = this.getPrevScoId(cur_sco);
	if(prev_sco != null) return true;
	else return false;
}

// find the name of the sco that follow the current played
ScormPlayer.prototype.getPrevScoName = function( ) {

	if(!this.nextScoId) var cur_sco = this.getCurrScoId();
	else var cur_sco = this.nextScoId;
	if(!cur_sco) return false;

	var prev_sco = this.getPrevScoId(cur_sco);
	if(prev_sco == null) return false;

	return this.getScoName(prev_sco);
}

// play the sco that precede the current one
ScormPlayer.prototype.playPrevSco = function () {

	var cur_sco = this.getCurrScoId();
	if(!cur_sco) return false;
	var prev_sco = this.getPrevScoId(cur_sco);
	if(prev_sco != null) this.play( prev_sco, window.uiPlayer.cntSco );
}

/* = next of current ======================== */

// find the sco that follow the current played
ScormPlayer.prototype.nextScoExists = function () {

	if(!this.nextScoId) var cur_sco = this.getCurrScoId();
	else var cur_sco = this.nextScoId;
	if(!cur_sco) return true;

	var next_sco = this.getNextScoId(cur_sco);
	if(next_sco != null) return true;
	else return false;
}

// find the name of the sco that follow the current played
ScormPlayer.prototype.getNextScoName = function( ) {

	if(!this.nextScoId) var cur_sco = this.getCurrScoId();
	else var cur_sco = this.nextScoId;

	if(!cur_sco) var next_sco = this.getFirstScoId();
	else {
		var next_sco = this.getNextScoId(cur_sco);
		if(next_sco == null) return false;
	}
	return this.getScoName(next_sco);
}

// play the sco next to the current one
ScormPlayer.prototype.playNextSco = function () {

	var cur_sco = this.getCurrScoId();
	if(!cur_sco) var next_sco = this.getFirstScoId();
	else var next_sco = this.getNextScoId(cur_sco);
	if(next_sco != null) this.play( next_sco, window.uiPlayer.cntSco );
}

/**
 * getProgress torna un oggetto con le seguenti proprieta':
 *  - completed numero di completati/passati
 *	- incomplete numero di incompleti
 *	- notAttempted numero di non iniziati
 **/
ScormPlayer.prototype.getProgress = function() {
	var icompleted 	= this.xmlTree.selectNodes('//item[@status="completed" and @isLeaf="1"]');
	var ipassed 	= this.xmlTree.selectNodes('//item[@status="passed" and @isLeaf="1"]');
	var iall 		= this.xmlTree.selectNodes('//item[@isLeaf="1"]');
	return 	{	completed: (icompleted.length + ipassed.length),
				all: iall.length
			};
}

/**
 * parseTree esegue il parsing dell'xml che rappresenta il tree
 * e richiama i metodi dell'oggetto passato per ogni item trovato
 **/
ScormPlayer.prototype.parseTree = function( obj ) {
	var doc = this.xmlTree.documentElement;
	this._parseTree( this.xmlTree, 0, obj );
}

/**
 * Funzione interna per il parsing dell'albero
 * ricorsiva!
 **/
ScormPlayer.prototype._parseTree = function( node, level, obj ) {
	var items = node.childNodes;
	var item = null;
	var objItem = null;
	var titleList = null;
	for( var i = 0; i < items.length; i++ ) {
		item = items.item(i);
		if( item.tagName == 'item' ) {
			objItem = new Object();
			objItem.id = item.getAttribute('id');
			titleList = item.getElementsByTagName('title');
			objItem.title = titleList.item(0).firstChild.nodeValue;
			objItem.prerequisites = item.getAttribute('prerequisites');
			objItem.visited = item.getAttribute('visited');
			objItem.complete = item.getAttribute('complete');
			objItem.status = item.getAttribute('status');
			objItem.isLeaf = item.getAttribute('isLeaf');
			objItem.resource = item.getAttribute('resource');
			objItem.idscorm_item = item.getAttribute('uniqueid');
			obj.startItem( objItem, level );
			this._parseTree( item, level+1, obj );
			obj.stopItem( objItem, level );
		}
	}
}

/**
 * addListener aggiunge un oggetto alla lista dei listeners
 * un listener e' un oggetto che implementa il metodo
 * scormPlayerActionPerfomer( evType, evValue )
 **/
ScormPlayer.prototype.addListener = function( id, obj ) {
	this.listeners[id] = obj;
}

/**
 * removeListener rimuove un listener dalla lista dei listeners
 **/
ScormPlayer.prototype.removeListener = function( id ) {
	this.listeners[id] = null;
}

/**
 * _fireEvent e' il metodo privato utilizzato per lanciare degli eventi
 * ai listeners
 **/
ScormPlayer.prototype._fireEvent = function( evType, evValue ) {
	for( objid in this.listeners ) {
		this.listeners[objid].scormPlayerActionPerformer( evType, evValue);
	}
}

/**
 * playItem esegue il LO con l'id passato come parametro
 * nella window passata in win
 * Il play non pu� essere eseguito immediatamente se c'e' gi� uno sco caricato
 *  e che ha fatto l'initialize.Deve attendere che il precedente sco sia stato
 *  scaricato. Crea quindi una action e la pone nella coda delle actions. Tale
 *  coda viene elaborata quando arriva un evento di finish.
 *
 * @param String id id del LO da mandare in play
 * @param Object win window in cui caricare lo sco
 **/
ScormPlayer.prototype.play = function( id, win ) {
	if( id === null ) {
		win.location.replace( this.basePath + this.blankPage );
	} else {
		this.setNextToPlay( id, win );
		win.location.replace( this.basePath + this.blankPage );
	}
}

ScormPlayer.prototype.setNextToPlay = function( id, win ) {
	this.nextScoId = id;
	this.cntWin = win;
	if(!this.getCurrScoId()) window.uiPlayer.drawNavigation();
}

/**
 * Esegue lo sco successivo impostato nel membro nextScoId
 **/
ScormPlayer.prototype.playNext = function() {
	if( this.nextScoId === null ) {
		// do nothing -- this.cntWin.location.replace( '' );
	} else {
		var item = this.xmlTree.selectSingleNode('//item[@id="'+this.nextScoId+'"]');
		var prerequisites = item.getAttribute('prerequisites');
		if( prerequisites == "" ) {
			if( this.cntWin.msgPrereqNotSatisfied )
				this.cntWin.msgPrereqNotSatisfied(this.getScoName(this.nextScoId));
			return;
		}
		this.api.setIdscorm_item( item.getAttribute('uniqueid') );
		this.api.setIdscorm_organization( playerConfig.idscorm_organization );
		this.cntWin.location.replace(	this.lmsBase + '/index.php?modname=scorm&op=scoload'
								+ '&idReference=' + playerConfig.idReference
								+ '&environment=' + playerConfig.environment
								+ '&idUser=' + playerConfig.idUser
								+ '&idscorm_resource=' + item.getAttribute('resource')
								+ '&idscorm_item=' + item.getAttribute('uniqueid')
								+ '&idscorm_organization=' + playerConfig.idscorm_organization
								+ '&idscorm_package=' + this.getPackage() );

		this._fireEvent( 'BeforeScoLoad', this.nextScoId );
		this.nextScoId = null;
	}
}

ScormPlayer.prototype.singleSco = function() {

	var item = this.xmlTree.selectNodes('//item[@resource!=""]');
	if(item.length == 1) return true
	else return false
}

ScormPlayer.prototype.blankPageLoaded = function() {

	//if we are in a single sco environment we can close the player
	if(window.close_player) {
		window.top.onbeforeunload = null;
		/*var url = window.top.location.href;
		url = url.slice(0, url.lastIndexOf("/"));
		window.top.location.href = url + "/" + playerConfig.backurl;*/
		window.top.location.href = playerConfig.lms_base_url + "" + playerConfig.backurl;
	} else {
		this.playNext();
	}
}

ScormPlayer.prototype.addActionQueue = function( action ) {
	this.actionQueue.push(action);
}

ScormPlayer.prototype.processActionQueue = function() {
	var action = null;
	var func = null;
	var params = null;
	while( this.actionQueue.length > 0 ) {
		action = this.actionQueue.shift();
		func = action.func;
		params = action.params;
		func.apply(this, params);
	}
}

/* Special xpath */
if( document.implementation.hasFeature("XPath", "3.0") ) {
	// prototying the XMLDocument
	XMLDocument.prototype.selectNodes = function(cXPathString, xNode) {
		if( !xNode ) { xNode = this; }
		try {

			var oNSResolver = this.createNSResolver(this.documentElement)
		} catch(e) {
			alert( "Property not found: "+e);
		}
		var aItems = this.evaluate(	cXPathString, xNode, oNSResolver,
									XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null)
		var aResult = [];
		for( var i = 0; i < aItems.snapshotLength; i++) {
			aResult[i] = aItems.snapshotItem(i);
		}
		aResult.item = function( index ) {
			return this[index];
		}
		return aResult;
	}
	XMLDocument.prototype.selectSingleNode = function(cXPathString, xNode) {
		if( !xNode ) { xNode = this; }
		var xItems = this.selectNodes(cXPathString, xNode);
		if( xItems.length > 0 ) {
			return xItems[0];
		} else {
			return null;
		}
	}

	// define the previous functions 'selectNodes' and 'selectSingleNode'  as part of the document model
	// because with some browser ( IE >= 9.0  and CHROME 34.x ) the xml data tree is loaded
	// as a Document and not an XMLDocument
	// Verify if it's possible to cast the xml data tree returned by the ajax.Request as an XMLDocument

	Document.prototype.selectNodes = function(cXPathString, xNode) {
		if( !xNode ) { xNode = this; }
		try {

			var oNSResolver = this.createNSResolver(this.documentElement)
		} catch(e) {
			alert( "Property not found: "+e);
		}
		var aItems = this.evaluate(	cXPathString, xNode, oNSResolver,
									XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null)
		var aResult = [];
		for( var i = 0; i < aItems.snapshotLength; i++) {
			aResult[i] = aItems.snapshotItem(i);
		}
		aResult.item = function( index ) {
			return this[index];
		}
		return aResult;
	}

	Document.prototype.selectSingleNode = function(cXPathString, xNode) {
		if( !xNode ) { xNode = this; }
		var xItems = this.selectNodes(cXPathString, xNode);
		if( xItems.length > 0 ) {
			return xItems[0];
		} else {
			return null;
		}
	}

	// prototying the Element
	Element.prototype.selectNodes = function(cXPathString) {
		if(this.ownerDocument.selectNodes) {
			return this.ownerDocument.selectNodes(cXPathString, this);
		} else {
			throw "For XML Elements Only";
		}
	}
	Element.prototype.selectSingleNode = function(cXPathString) {
		if(this.ownerDocument.selectSingleNode) {
			return this.ownerDocument.selectSingleNode(cXPathString, this);
		} else{
			throw "For XML Elements Only";
		}
	}
}
