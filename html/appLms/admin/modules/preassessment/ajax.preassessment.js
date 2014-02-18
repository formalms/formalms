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

var _PA = new Array();

var addmod_window, del_window;

function setup_assessment(passed_server_url) {
	
	var data = "op=getLang";
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', 'ajax.adm_server.php?plf=lms&mn=preassessment&'+data, {
    	success: setup_assessment_complete
    });
}

/*function setup_assessment_rules(passed_server_url) {
  YAHOO.util.Event.addListener('add_rule', 'click', function(e) { show_addmod(); }); 
}*/

function setup_assessment_complete(o) {
	try {
		_PA = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { return; }	
	
	  
  addmod_window=new Window('addmod_preassessment', {
		dynamic: true,
		action:'ajax.adm_server.php?plf=lms&mn=preassessment&op=modrule',
		content_request:'ajax.adm_server.php?plf=lms&mn=preassessment&op=modrule',
		width: "600px",
		title: _PA._NEW_RULE,
		close_button: true,
		modal: true,
		buttons: [
			{text: _PA._CONFIRM, handler:function() {this.submit(); } },
			{text: _PA._UNDO, handler:function() { this.hide(); } }
		] });
}

function show_addmod() {
	addmod_window.show();
}

function add_rule_mask(id_assessment, usedef) {
	
	var data = "op=modruleform&id_assessment=" + id_assessment + "&usedef=" + usedef;
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', 'ajax.adm_server.php?plf=lms&mn=preassessment&'+data, {
    	success: add_rule_mask_callback
    });
}


function mod_rule_mask(id_assessment, id_rule) {
	
	var data = "op=modruleform&id_assessment=" + id_assessment + "&id_rule=" + id_rule;
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', 'ajax.adm_server.php?plf=lms&mn=preassessment&'+data, {
    	success: mod_rule_mask_callback
    });
}

function add_rule_mask_callback(ObjReq) {
	
	var name = "window_add_rule";
	var title = _PA._NEW_RULE;
	
	var str = ObjReq.responseText;
	
	var but = '<input type="submit" id="addrule" name="addrule" value="' + _PA._CONFIRM + '" /> ';
	but += '<input type="button" value="' + _PA._UNDO + '" onclick="destroyWindow(\'' + name +'\');" />';
	
	var forms = document.createElement('form');
	forms.method = 'post';
	forms.action = 'index.php?modname=preassessment&op=saverule';
	
	var w = new Window(name, {
		width : 700,
		//height : 'auto',
		title : title,
		content : str,
		dosubmit : true
	});
	w.buttons = but;
	w.form = forms;
	w.show();
}

function mod_rule_mask_callback(ObjReq) {
	
	var name = "window_add_rule";
	var title = _PA._NEW_RULE;
	
	var forms = document.createElement('form');
	forms.method = 'post';
	forms.action = 'index.php?modname=preassessment&op=saverule';
	
	var str = ObjReq.responseText;
	
	var but = '<input type="submit" id="modrule" name="modrule" value="' + _PA._CONFIRM + '" /> ';
	but += '<input type="button" value="' + _PA._UNDO + '" onclick="destroyWindow(\'' + name +'\');" />';
	
	var w = new Window(name, {
		id : name,
		width : 600,
		title : title,
		content : str,
		dosubmit : true 
	});
	w.buttons = but;
	w.form = forms;
	w.show();
}

function rule_type_change(rule_type, id_score_type_one, id_score_type_two) {

	var YD = YAHOO.util.Dom;
	var elem = YD.get(rule_type).selectedIndex;
	if(elem == 0) {
		YD.get(id_score_type_one).disabled = false;
		YD.get(id_score_type_two).disabled = true;
	}
	if(elem == 1) {
		YD.get(id_score_type_one).disabled = false;
		YD.get(id_score_type_two).disabled = true;
	}
	if(elem == 2) {
		YD.get(id_score_type_one).disabled = false;
		YD.get(id_score_type_two).disabled = false;
	}
	if(elem == 3) {
		YD.get(id_score_type_one).disabled = true;
		YD.get(id_score_type_two).disabled = true;
	}
}


//delete

function del_pre_assessment(id, assess_name) {

	var butt="";
	butt += '<input type="hidden" id="id_assessment" name="id_assessment" value="' + id +'" />';
	butt += '<input type="submit" value="'+_PA._YES+'" /> ';
	butt += '<input type="button" value="'+_PA._NO+'" onclick="destroyWindow(\'window_del_assess\');"; return false;" />';
	
	var forms = document.createElement('form');
	forms.method = 'post';
	forms.action = 'index.php?modname=preassessment&op=delassessment';
	
	var w = new Window("window_del_assess", {
		width : 400,
		//height : 125,
		title : _PA._DEL,
		content : _PA._AREYOUSURE + ':&nbsp;<b>' + assess_name + '</b> ?',
		dosubmit : true
	});
	w.form 		= forms;
	w.buttons 	= butt;
	w.show();	
	
}


function del_assessment_rule(id, rule_name) {

	var name = "window_del_rule";
	var title = _PA._DEL;
	
	var str="";
	str += _PA._AREYOUSURE_RULE + '<br /><b>' + rule_name + '</b>';
	var butt="";
	//butt += '<form method="post" action="index.php?modname=preassessment&amp;op=delrule">';
	butt += '<input type="hidden" id="id_rule" name="id_rule" value="' + id +'" />';
	butt += '<input type="submit" value="'+_PA._YES+'" /> ';
	butt += '<input type="button" value="'+_PA._NO+'" onclick="destroyWindow(\'window_del_rule\');"; return false;" />';
	//butt += '</form>';
	
	
	var forms = document.createElement('form');
	forms.method = 'post';
	forms.action = 'index.php?modname=preassessment&op=delrule';
	
	var w = new Window(name, {
		width : 400,
		height : 125,
		title : title,
		content : str,
		dosubmit : true
	});
	w.form = forms;
	w.buttons = butt;
	w.show();
}