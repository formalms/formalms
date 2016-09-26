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
 * Costruttore del ui player standard
 * cioe' dell'oggetto che gestisce l'interfaccia del player
 **/
function StdUIPlayer() {
	this.cntNAvigation = null;		// elemento contenente la progress
	this.cntProgressBar = null;		// elemento contenente la progress
	this.cntPrevBtn = null;			// elemento contenente il bottone prev
	this.cntNextBtn = null; 		// elemento contenente il bottone di next
	this.cntTitleCP = null;			// elemento contenente il titolo del content package
	this.cntTitleSCO = null;		// elemento contenente il titolo dello sco attualmente in esecuzione
	this.cntTree = null; 			// elemento contenente l'albero di navigazione
	this.cntScoContent = null;		// elemento contenente la window con lo sco
	this.cntSco = null;				// window che contiene gli sco
	this.cntSeparator = null;		// elemento che comanda il show/hide dell'albero

	this.scormPlayer = null;		// scorm player

	this.showTree = true;
}

/**
 * imposta uno dei contenitori degli elementi delle interfacce
 * @param String cntName nome del contenitore ('ProgressBar','PrevBtn',...)
 * @param HTMLElement cntElem elemento contenitore
 **/
StdUIPlayer.prototype.setContainer = function( cntName, cntElem ) {
	this['cnt'+cntName] = cntElem;
	if( cntName == 'Separator' ) {
		//this.cntSeparator.onclick = showhidetree;
	}
}

/**
 * imposta lo scormPlayer
 **/
StdUIPlayer.prototype.setScormPlayer = function( scormPlayer ) {
	this.scormPlayer = scormPlayer;
	this.scormPlayer.addListener( 'StdUIPlayer', this );
}

StdUIPlayer.prototype.scormPlayerActionPerformer = function( evType, evValue) {
	switch( evType ) {
		case 'Initialize':
			if( playerConfig.autoplay == '1' ) {
				// set next scoid
				var currScoId = this.scormPlayer.getCurrScoId();
				var nextScoid = this.scormPlayer.getNextScoId(currScoId);
				this.scormPlayer.setNextToPlay(nextScoid, this.cntSco);
			}
		break;
		case 'Finish':
			StdUIPlayer.refresh();
			if(this.scormPlayer.singleSco()) window.close_player = true;
			this.scormPlayer.play(null, this.cntSco);
		break;
		case 'BeforeScoLoad':
			var elem = this.cntTree.ownerDocument.getElementById(evValue);

			if(elem.className.indexOf('RunningSco') == -1) { elem.className += ' RunningSco' };
		break;
	}
}

/**
 * svuota un elemento di tutti i sui contenuti
 **/
StdUIPlayer.prototype._removeContents = function( elem ) {
	elem.innerHTML = "";
}

/**
 * disegna la progress bar
 **/
StdUIPlayer.prototype.drawProgressBar = function() {
	return;
	var doc = this.cntProgressBar.ownerDocument;
	var progress = this.scormPlayer.getProgress();
	this._removeContents( this.cntProgressBar );
	this.cntProgressBar.appendChild(doc.createTextNode('Progress: ' + progress.completed + '/' + progress.all) );
	var divProgress = doc.createElement('div');
	divProgress.className = 'scorm_progressbarstat';
	this.cntProgressBar.appendChild(divProgress);
	var w = (220 - progress.all)/progress.all;
	var posrel = 2;
	var brick = null;
	for( var i = 0; i < progress.completed; i++, posrel += w+1 ) {
		brick = doc.createElement('div');
		brick.className = 'scorm_complete';
		brick.style.width = w + 'px';
		brick.style.left = posrel + 'px';
		divProgress.appendChild(brick);
	}
	for( ; i < progress.all; i++, posrel += w+1 ) {
		brick = doc.createElement('div');
		brick.className = 'scorm_incomplete';
		brick.style.width = w + 'px';
		brick.style.left = posrel + 'px';
		divProgress.appendChild(brick);
	}
}

/**
 * disegna il titolo
 **/
StdUIPlayer.prototype.drawTitleCP = function() {
	return;
	var doc = this.cntTitleCP.ownerDocument;
	this._removeContents( this.cntTitleCP );
//	this.cntTitleCP.appendChild( doc.createTextNode(this.scormPlayer.getTitleCP()) );
	this.cntTitleCP.innerHTML = this.scormPlayer.getTitleCP();
}

/**
 * disegna i bottoni di navigazione
 **/
StdUIPlayer.prototype.drawNavigation = function() {
	return;
	var navi = "";
	if(prevExist()) {

		navi +="<div id=\"prevblocklink\" style=\"display:block; float: left;\">\n"
			+"			<a id=\"prevsco\" href=\"#\" onClick=\"playprevclick(); return false;\">"
			+"				<img class=\"imgnav\" id=\"imgprev\" src=\""+playerConfig.imagesPath+"../scorm/bt_sx.gif\" width=\"32\" alt=\"Back\" />"
			+"			</a>\n"
			+"			<span id=\"prevlink\"></span>"
			+"		</div>\n";
	} else {

		navi +="<div id=\"prevblocklink\" style=\"display:block; float: left;\">\n"
			+"			"
			+"				<img class=\"imgnav\" id=\"imgprev\" src=\""+playerConfig.imagesPath+"../blank.png\" width=\"32\" alt=\"Back\" />"
			+"			\n"
			+"			<span id=\"prevlink\"></span>"
			+"		</div>\n";
	}
	if(nextExist()) {

		navi +="		<div id=\"nextblocklink\" style=\"display:block; float: left;\">\n"
			+"			<a id=\"nextsco\" href=\"#\" onClick=\"playnextclick(); return false;\">"
			+"				<img class=\"imgnav\" id=\"imgnext\" src=\""+playerConfig.imagesPath+"../scorm/bt_dx.gif\" width=\"32\" alt=\"Next\" />"
			+"			</a>\n"
			+"			<span id=\"nextlink\"></span>\n"
			+"		</div>\n";
	} else {

		navi +="<div id=\"prevblocklink\" style=\"display:block; float: left;\">\n"
			+"			"
			+"				<img class=\"imgnav\" id=\"imgprev\" src=\""+playerConfig.imagesPath+"../blank.png\" width=\"32\" alt=\"Next\" />"
			+"			\n"
			+"			<span id=\"prevlink\"></span>"
			+"		</div>\n";
	}
	this.cntNavigation.innerHTML = navi;
}
/**
 * disegna il bottone di prev
 **/
StdUIPlayer.prototype.drawPrevBtn = function() {
}

/**
 * disegna il tree
 **/
StdUIPlayer.prototype.drawTree = function() {
	var doc = this.cntTree.ownerDocument;
	var tc = document.getElementById('TreeContainer');

	if(!tc) {

		this.cntTree.divContainer = doc.createElement('div');
		this.cntTree.divContainer.id = "TreeContainer";
		this.cntTree.appendChild(this.cntTree.divContainer);
	} else {

		this._removeContents( tc );
	}
	this.scormPlayer.parseTree(this);
}

/* ============ interfaccia per ScormPlayer.parseTree =============== */
StdUIPlayer.prototype.startItem = function( objItem, level ) {
	if( level == 0 ) return;
	var doc = this.cntTree.ownerDocument;
	var div = doc.createElement('div');
	div.className = objItem.isLeaf?'TreeRowClass':'TreeRowFolder';

	if( objItem.resource) var elem = doc.createElement('a');
	else  var elem = doc.createElement('span');

	if( objItem.resource && (objItem.status == 'completed' || objItem.status == 'passed') ) {
		var imgStatus = doc.createElement('img');
		imgStatus.className = 'icoStatus icoCompleted';
		imgStatus.title = objItem.status;
		imgStatus.alt = objItem.status;
		imgStatus.src = playerConfig.imagesPath + 'completed.gif';
		elem.appendChild(imgStatus);
	} else if( objItem.resource && (objItem.status == 'incomplete' || objItem.status == 'failed') ) {
		var imgStatus = doc.createElement('img');
		imgStatus.className = 'icoStatus icoIncomplete';
		imgStatus.title = objItem.status;
		imgStatus.alt = objItem.status;
		imgStatus.src = playerConfig.imagesPath + 'incomplete.gif';
		elem.appendChild(imgStatus);
	} else if( objItem.resource && objItem.prerequisites == "" ) {
		var imgStatus = doc.createElement('img');
		imgStatus.className = 'icoStatus icoLoked';
		imgStatus.title = 'locked';
		imgStatus.alt = 'locked';
		imgStatus.src = playerConfig.imagesPath + 'loked.gif';
		elem.appendChild(imgStatus);
	}
	//elem.appendChild(doc.createTextNode(objItem.title));
	elem.innerHTML = elem.innerHTML + objItem.title;
	elem.id = objItem.id;
	if( objItem.resource) {
		elem.href = '#';
		elem.onclick = treeonclick;
	}
	div.appendChild(elem);

	this.cntTree.divContainer.appendChild(div);
}
StdUIPlayer.prototype.stopItem = function( objItem, level ) {
}

StdUIPlayer.prototype.treeonclick = function( id ) {

	if(disable_chapter_change) return;
	this.scormPlayer.play( id, this.cntSco );
}

StdUIPlayer.prototype.showhidetree = function() {
	if( this.showTree ) {
		this.cntTree.className = 'treecontent_hiddentree '+ playerConfig.playertemplate +'_menu';
		this.cntScoContent.className = 'scocontent_hiddentree';
		this.cntSeparator.className = 'separator_hiddentree';
		this.showTree = false;
		var a = this.cntSeparator.getElementsByTagName('img');
		a[0].src = playerConfig.imagesPath + '../scorm/bt_dx.png';
	} else {
		this.cntTree.className = 'treecontent '+ playerConfig.playertemplate +'_menu';
		this.cntScoContent.className = 'scocontent';
		this.cntSeparator.className = 'separator';
		var a = this.cntSeparator.getElementsByTagName('img');
		a[0].src = playerConfig.imagesPath + '../scorm/bt_sx.png';
		this.showTree = true;
	}
}

StdUIPlayer.prototype.closePlayer = function() {

	// Forzo LMSFinish per bug Lectora
	if(playerConfig.scormVersion == '1.3') {
		window.API_1484_11.LMSCommit("", /*FIX 17052016*/ function(){
			window.API_1484_11.LMSFinish("");
		});
	}else {
		window.API.LMSCommit("", /*FIX 17052016*/ function(){
			window.API.LMSFinish("");
		});
	}
	this.scormPlayer.play( null, this.cntSco );
//	window.location.href = playerConfig.backurl;
	window.close_player = true;
}

/**
 * Static function for player initialization
 **/
StdUIPlayer.initialize = function() {
	/* create UI Player */

	window.close_player = false;

	window.uiPlayer = new StdUIPlayer();
	window.uiPlayer.setContainer('Tree', document.getElementById(playerConfig.idElemTree));
	//window.uiPlayer.setContainer('TitleCP', document.getElementById(playerConfig.idElemTitleCP));
	//window.uiPlayer.setContainer('Navigation', document.getElementById(playerConfig.idElemNavigation));
	window.uiPlayer.setContainer('ScoContent', document.getElementById(playerConfig.idElemScoContent));
	window.uiPlayer.setContainer('Sco', window.frames[playerConfig.idElemSco]);
	//window.uiPlayer.setContainer('ProgressBar', document.getElementById(playerConfig.idElemProgress));
	window.uiPlayer.setContainer('Separator', document.getElementById(playerConfig.idElemSeparator));

	/* create Scorm API */
	var scormapi = new ScormApiUI( 	playerConfig.host,
									playerConfig.lms_url,
									playerConfig.scormserviceid,
									playerConfig.idUser,
									playerConfig.idReference,
									playerConfig.idscorm_organization,
									playerConfig.scormVersion,
									playerConfig.environment);
	/* hook for scorm 1.3 */
	if(playerConfig.scormVersion == '1.3')  window.API_1484_11 = scormapi;
	else window.API = scormapi;

	scormapi.useWaitDialog( !(playerConfig.useWaitDialog=='off') );
	/* create ScormPlayer */
	window.scormPlayer = new ScormPlayer();
	window.scormPlayer.setPath( playerConfig.lms_url.substring(0, playerConfig.lms_url.lastIndexOf( "/" )),
								playerConfig.lms_base_url );
	window.scormPlayer.setAPI( scormapi );

	StdUIPlayer.refresh();

	if( playerConfig.startFromChapter != false ) {

		if(window.scormPlayer.getScoName(playerConfig.startFromChapter) != null)
			window.scormPlayer.play(playerConfig.startFromChapter, window.uiPlayer.cntSco );
		window.scormPlayer.play(null,window.uiPlayer.cntSco );
	} else if( playerConfig.autoplay == '1' ) {
		// set next scoid
		var scoId = this.scormPlayer.getNextIncompleteScoId();
		window.scormPlayer.play(scoId,window.uiPlayer.cntSco );
	} else {
		window.scormPlayer.play(null,window.uiPlayer.cntSco );
	}
	this.showTree = playerConfig.showTree;
	if(!this.showTree) this.showhidetree();

//	setTimeout("keepalive()", 15*60*1000);
	idtmo = window.setTimeout("keepalive()", playerConfig.keepalivetmo*1000); // 15*60*1000
}

keepalive = function () {
	new Ajax.Request('./modules/scorm/keep_alive.php?sessonly=1', {method: 'get', requestHeaders: {
		"X-Signature":playerConfig.auth_request
	}});
//	setTimeout("keepalive()",  15*60*1000);
	idtmo = window.setTimeout("keepalive()",  playerConfig.keepalivetmo*1000); // 15*60*1000
}

trackUnloadOnLms = function() {

	var ajax = new Ajax.Request('./modules/scorm/keep_alive.php', {method: 'get', requestHeaders: {
		"X-Signature":playerConfig.auth_request
	}});
}

StdUIPlayer.refresh = function() {
	var now = new Date();
	loadFromXml(playerConfig.xmlTreeUrl + '&time=' + now.getTime() ,StdUIPlayer.initialize2);
}

StdUIPlayer.initialize2 = function(xtree) {
	window.scormPlayer.setTree( xtree );

	window.uiPlayer.setScormPlayer( window.scormPlayer );
	window.uiPlayer.drawTree();
	window.uiPlayer.drawProgressBar();
	window.uiPlayer.drawTitleCP();
	window.uiPlayer.drawNavigation();
}

function showhidetree() {
	window.uiPlayer.showhidetree();
}

function treeonclick() {
	window.uiPlayer.treeonclick( this.id );
}

function closeScormPlayer() {
	window.uiPlayer.closePlayer();
}

function nextExist() {
	return window.scormPlayer.nextScoExists();
}

function playnextclick() {
	window.scormPlayer.playNextSco();
}

function prevExist() {
	return window.scormPlayer.prevScoExists();
}
function playprevclick() {
	window.scormPlayer.playPrevSco();
}
