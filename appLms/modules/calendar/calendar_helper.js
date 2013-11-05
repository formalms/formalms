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

var serverUrl="ajax.server.php?plf=lms&mn=calendar&";
var calClass="lms";
var calEventClassDefault="lms";
var mode="edit";
var permissions=0;
var idSt0;

var cal;	
var CalEvents=new Array();
var cDate;
var oldLink = null;
var evDisabled=false;
var _TT = new Array();
var maxDays=31;

var eYear;
var eMonth;
var eDay;
var eId;

var evForm;


// code to change the active stylesheet
function setActiveStyleSheet(link, title) {
  var i, a, main;
  for(i=0; (a = document.getElementsByTagName("link")[i]); i++) {
    if(a.getAttribute("rel").indexOf("style") != -1 && a.getAttribute("title")) {
      a.disabled = true;
      if(a.getAttribute("title") == title) a.disabled = false;
    }
  }
  if (oldLink) oldLink.style.fontWeight = 'normal';
  oldLink = link;
  link.style.fontWeight = 'bold';
  return false;
}

var MINUTE = 60 * 1000;
var HOUR = 60 * MINUTE;
var DAY = 24 * HOUR;
var WEEK = 7 * DAY;

var ajax_failure = function(o) { alert('Unable to connect with the server.'); }

function openEvent (year,month,day,id) {
	if (mode=="view") {
		showEvent(id);
		return;
	}
	eYear=year;
	eMonth=month;
	eDay=day;
	eId=id;
	if (id) {
		var k=findIndex(id);
		var calEventClass=CalEvents[k].calEventClass;
		if (!CalEvents[k].editable) {
			showEvent(id);
			return;
		}
	} else {
		var calEventClass=calEventClassDefault;
	}	
		
	YAHOO.util.Dom.get('progress_msg').innerHTML=_TT._PLS_WAIT;
	
	disableCal();
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+'op=getForm&calEventClass='+calEventClass, {
    	success: editEvent,
    	failure: ajax_failure
    });
}

function showEvent(year,month,day,id) {
	if (evDisabled) return;
	
	eYear=year;
	eMonth=month;
	eDay=day;
	eId=id;
	if (id) {
		var k=findIndex(id);
		var calEventClass=CalEvents[k].calEventClass;
	} else {
		var calEventClass=calEventClassDefault;
	}
	
	YAHOO.util.Dom.get('progress_msg').innerHTML=_TT._PLS_WAIT;
	
	disableCal();
	
	var data="calEventClass="+calEventClass+"&op=getForm";
/*
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: viewEvent}
    );	*/
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: viewEvent,
    	failure: ajax_failure
    });
}

function editEvent (ObjReq) {
	
	var year=eYear;
	var month=eMonth;
	var day=eDay;
	var id=eId;
	
	if (id) var k=findIndex(id);

	YAHOO.util.Dom.get('progress_msg').innerHTML="";
	
/*	var evFormText = ObjReq.responseText;
	evForm = evFormText.evalJSON(true);*/
	try {
		evForm = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }
	
	//if (YAHOO.util.Dom.get('wEvent'))
		//destroyWindow('wEvent');
		
	var name="wEvent";
	var title=_TT._NEW_EVENT;
	if (id) title=_TT._MOD;
		
	var str='<table class="eventForm">';
	
	for (var i=0;i<evForm.form.length;i++) {
		var takeit=true;
		
		if (evForm.form[i].permissions && evForm.form[i].permissions!=permissions) takeit=false;
	
		if (takeit) {
		switch (evForm.form[i].type) {
			case "structure":
				switch (evForm.form[i].value) {
					case "row":
						str+="<tr>";
						break;
					case "/row":
						str+="</tr>";
						break;
						
					case "cell":
						str+="<td";
						if (evForm.form[i].field_class) str+=" class=\""+evForm.form[i].field_class+"\"";
						
						
						str+=">";
						break;
						
					case "/cell":
						str+="</td>";
						break;
				};
				break;
			
			case "label":
				if (evForm.form[i].field_class) str+="<span class=\""+evForm.form[i].field_class+"\"";
				if (evForm.form[i].translatevalue=='0') {
					str+=evForm.form[i].value;
				} else { 
					str+=eval("_TT."+evForm.form[i].value);
				}
				if (evForm.form[i].field_class) str+="</span>";
				break;
				
			case "text":
				var cValue="";
				if (id) cValue=unescape(eval("CalEvents["+k+"]."+evForm.form[i].id));
				str+='<input onfocus="this.select()" type="text" name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'" value="'+cValue+'"';
				
				if (evForm.form[i].style) str+=' style="'+evForm.form[i].style+'"';
				
				str+=' />';
				break;
			
			case "textarea":
				var cValue="";
				if (id) cValue=unescape(eval("CalEvents["+k+"]."+evForm.form[i].id));
				str+='<textarea onfocus="this.select()" name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'"';

				if (evForm.form[i].style) str+=' style="'+evForm.form[i].style+'"';
				
				str+='>'+cValue+'</textarea>';

				break;
			
			case "string":
				str+=evForm.form[i].value;
				break;
				
			case "day":
				var cValue=day;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getDayOptions(cValue);
				str+='</select>';
				break;
			
			case "month":
				var cValue=month;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id)-1;
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getMonthOptions(cValue);
				str+='</select>';
				break;
				
			case "year":
				var cValue=year;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getYearOptions(cValue);
				str+='</select>';
				break;
			
			case "hour":
				var cValue=9;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getHourOptions(cValue);
				str+='</select>';
				break;	
			
			case "min":
				var cValue=0;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getMinOptions(cValue);
				str+='</select>';
				break;	
				
			case "sec":
				var cValue=0;
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				str+=getSecOptions(cValue);
				str+='</select>';
				break;	
			
			case "select":
				var cValue="";
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<select name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'">';
				
				for (var j=0;j<evForm.form[i].value.length;j++) {
					if (evForm.form[i].key) {
						var optKey=evForm.form[i].key[j];
						var optLabel=evForm.form[i].value[j];
					} else {
						var optKey=evForm.form[i].value[j];
						var optLabel=evForm.form[i].value[j];
					}
					str+='<option value="'+optKey+'"';
					if (optKey==cValue) str+=' selected="selected"';
					str+=">";
					if (evForm.form[i].translatevalue=='1') {
						str+=eval("_TT."+optLabel);
					} else {
						str+=optLabel;
					}
					
					str+="</option>";
				}								
				str+='</select>';
				break;	
			
			case "checkbox":
				var cValue="";
				if (id) cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				str+='<input type="checkbox" name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'"';

				if (cValue=="on") str+=' checked="checked"'
				
				str+=' />';
				break;
				
			case "hidden":
				var cValue="";
				if (evForm.form[i].value) cValue=evForm.form[i].value;
				if (id) cValue=unescape(eval("CalEvents["+k+"]."+evForm.form[i].id));
				str+='<input type="hidden" name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'"';

				str+=' value="'+cValue+'"'
				
				str+=' />';
				break;
				
		} //end switch
		} //end check permissions
	
	} //end for

	
	str +='</td></tr></table>';
	
	var buttns=[];
	buttns[buttns.length] = { text:_TT._SAVE, handler: function(){ sendEvent();this.destroy(); } };
	if(id) buttns[buttns.length] = { text:_TT._DEL_EVENT, handler: function(){delEvent(this.mycal_id);this.destroy(); } };
	buttns[buttns.length] = { text: _TT._CLOSE, handler: function(){this.destroy();}, isDefault:true};
	var w = new YAHOO.widget.SimpleDialog("simpledialog_name", {
		fixedcenter: true,
		visible: true,
		close: true,
		modal: true,
		constraintoviewport: true,
		buttons: buttns
	} );
	w.mycal_id = id;
	w.setHeader(title);
	w.setBody(str+'<input type="hidden" name="idev" id="idev" value="'+id+'" />');
	w.render(document.body);
	
}

function viewEvent (ObjReq) {
	
	var id=eId;
	
	var k=findIndex(id);
	
	YAHOO.util.Dom.get('progress_msg').innerHTML="";
	/*
	var evFormText = ObjReq.responseText;
	evForm = evFormText.evalJSON(true);*/
	try {
		evForm = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }
	
	//if (YAHOO.util.Dom.get('wEvent'))
	//	destroyWindow('wEvent');
		
	var name="wEvent";
	var title=_TT._EVENT;
	
	var str='<table class="eventForm">';
	
	for (var i=0;i<evForm.form.length;i++) {
		var takeit=true;
		
		if (evForm.form[i].permissions && evForm.form[i].permissions!=permissions) takeit=false;
	
		if (takeit) {
		switch (evForm.form[i].type) {
			
			case "structure":
				switch (evForm.form[i].value) {
					case "row":
						str+="<tr>";
						break;
					case "/row":
						str+="</tr>";
						break;
						
					case "cell":
						str+="<td";
						if (evForm.form[i].field_class) str+=" class=\""+evForm.form[i].field_class+"\"";
						str+=">";
						break;
						
					case "/cell":
						str+="</td>";
						break;
				};
				break;
			
			case "label":
				if (evForm.form[i].field_class) str+="<span class=\""+evForm.form[i].field_class+"\"";
				if (evForm.form[i].translatevalue=='0') {
					str+=evForm.form[i].value;
				} else { 
					str+=eval("_TT."+evForm.form[i].value);
				}
				if (evForm.form[i].field_class) str+="</span>";
				break;
				
			case "text":
			case "textarea":
			case "day":
			case "month":
			case "year":
			case "hour":
			case "min":
			case "sec":
				cValue=unescape(eval("CalEvents["+k+"]."+evForm.form[i].id));
				str+=cValue;
				break;
			
			
			case "string":
				str+=evForm.form[i].value;
				break;
			
			case "select":
				cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				
				if (evForm.form[i].key) 
					for (var j=0;j<evForm.form[i].value.length;j++) 
						if (cValue==evForm.form[i].key[j]) cValue=evForm.form[i].value[j];
					
					
				if (evForm.form[i].translatevalue=='1') {
					if (cValue) str+=eval("_TT."+cValue);
				} else {
					str+=cValue;
				}

				break;	
			
			case "checkbox":
				cValue=eval("CalEvents["+k+"]."+evForm.form[i].id);
				
				str+='<input type="checkbox" disabled="disabled" name="'+evForm.form[i].id+'" id="'+evForm.form[i].id+'"';

				if (cValue=="on") str+=' checked="checked"'
				
				str+=' />';
				break;
				
			case "hidden":
				break;
			
		} //end switch
		} //end check permissions
	
	} //end for
	
	str +='</td></tr></table>';
	/*
	var buttns='';
	if (mode!="view") {
		buttns += '<input type="submit" value="'+_TT._MOD+'" onclick="openEvent('+eYear+','+eMonth+','+eDay+','+id+')" />';
	}
	buttns += '<input type="submit" value="'+_TT._CLOSE+'" onclick="enableCal();destroyWindow(\'wEvent\');return false;" />';
  */
	var buttns=[];
	buttns[buttns.length] = { text:_TT._MOD, handler: function(){
		openEvent(this.cal_data.eYear, this.cal_data.eMonth, this.cal_data.eDay, this.cal_data.id);
		this.destroy();
	} };
	buttns[buttns.length] = { text: _TT._CLOSE, handler: function(){this.destroy();}, isDefault:true};
	
	var w = new YAHOO.widget.SimpleDialog("simpledialog_"+name, {
		fixedcenter: true,
		visible: true,
		close: true,
		modal: true,
		constraintoviewport: true,
		buttons: buttns
	} );
	w.cal_data = {eYear:eYear, eMonth:eMonth, eDay:eDay, id:id};
	w.setHeader(title);
	w.setBody(str);
	w.render(document.body);
}


function getDayOptions(cDay) {
	var str0='';
	for (var i=1;i<=maxDays;i++) {
		str0 += '<option value="'+i+'"';
		if (i==cDay) str0 += ' selected="selected"';
		str0 += '>'+i+'</option>\n';
	}
	return str0;
}

function getMonthOptions(cMonth) {
	var str0='';
	for (var i=1;i<=12;i++) {
		str0 += '<option value="'+i+'"';
		if (i==cMonth+1) str0 += ' selected="selected"';
		str0 += '>'+i+'</option>\n';
	}
	return str0;
}

function getYearOptions(cYear) {
	var str0='';
	for (var i=cal.minYear;i<=cal.maxYear;i++) {
		str0 += '<option value="'+i+'"';
		if (i==cYear) str0 += ' selected="selected"';
		str0 += '>'+i+'</option>\n';
	}
	return str0;
}

function getHourOptions(cHour) {
	var str0='';
	for (var i=0;i<=23;i++) {
		var k=String("0"+i);
		if (k.length>2) k=k.substring(1,3);
		
		str0 += '<option value="'+k+'"';
		if (i==cHour) str0 += ' selected="selected"';
		str0 += '>'+k+'</option>\n';
	}
	return str0;
}

function getMinOptions(cMin) {
	var str0='';
	for (var i=0;i<=59;i++) {
		var k=String("0"+i);
		if (k.length>2) k=k.substring(1,3);
		
		str0 += '<option value="'+k+'"';
		if (i==cMin) str0 += ' selected="selected"';
		str0 += '>'+k+'</option>\n';
	}
	return str0;
}

function getSecOptions(cSec) {
	var str0='';
	for (var i=0;i<=59;i++) {
		var k=String("0"+i);
		if (k.length>2) k=k.substring(1,3);
		
		str0 += '<option value="'+k+'"';
		if (i==cSec) str0 += ' selected="selected"';
		str0 += '>'+k+'</option>\n';
	}
	return str0;
}

function sendEvent() {
	
	//checking and validating the given dates
	var d0=new Date();
	var d1=new Date();
	
	var mn0=YAHOO.util.Dom.get('start_month').value-1;
	var dy0=YAHOO.util.Dom.get('start_day').value;
	var yr0=YAHOO.util.Dom.get('start_year').value;
	d0.setMonth(mn0);
	d0.setFullYear(yr0);
	d0.setDate(dy0);
	
	var maxDays=d0.getMonthDays(mn0);
	
	if (dy0>maxDays) {
		alert(_OPERATION_FAILURE);
		return false;
	}
	
	var mn1=YAHOO.util.Dom.get('end_month').value-1;
	var dy1=YAHOO.util.Dom.get('end_day').value;
	var yr1=YAHOO.util.Dom.get('end_year').value;
	d1.setMonth(mn1);
	d1.setFullYear(yr1);
	d1.setDate(dy1);
	
	var maxDays=d1.getMonthDays(mn1);
	
	if (dy1>maxDays) {
		alert("Errore nella data di fine");
		//alert(_OPERATION_FAILURE);
		return false;
	}
	
	if (d0>d1) {
		alert("Errore nella impostazione delle date");
		//alert(_OPERATION_FAILURE);
		return false;
	}
	
	if ( YAHOO.util.Dom.get('start_month').value==YAHOO.util.Dom.get('end_month').value && YAHOO.util.Dom.get('start_day').value==YAHOO.util.Dom.get('end_day').value && YAHOO.util.Dom.get('start_year').value==YAHOO.util.Dom.get('end_year').value ) {
		var err=false;
		if ( YAHOO.util.Dom.get('start_hour').value>YAHOO.util.Dom.get('end_hour').value ) err=true;
		if ( YAHOO.util.Dom.get('start_hour').value==YAHOO.util.Dom.get('end_hour').value && YAHOO.util.Dom.get('start_min').value>YAHOO.util.Dom.get('end_min').value ) err=true;
		if ( YAHOO.util.Dom.get('start_hour').value==YAHOO.util.Dom.get('end_hour').value && YAHOO.util.Dom.get('start_min').value==YAHOO.util.Dom.get('end_min').value && YAHOO.util.Dom.get('start_sec').value>YAHOO.util.Dom.get('end_sec').value ) err=true;
		if (err) {
		alert("Errore nella impostazione delle date");
		//alert(_OPERATION_FAILURE);
		return false;
		}
	}
	/* --------------- */ 
	
	var id=YAHOO.util.Dom.get('idev').value;
	var k = findIndex(id);
	
	if (k==-1) {
		k=CalEvents.length;
		if (!k) k=0;
		CalEvents[k]=new Object();
		CalEvents[k].editable=1;
		CalEvents[k]._owner=idSt0;
		CalEvents[k].calEventClass=calEventClassDefault;
		
	}
	
	CalEvents[k].id=YAHOO.util.Dom.get('idev').value;
	if (!YAHOO.util.Dom.get('title').value) YAHOO.util.Dom.get('title').value=_TT._NOTITLE;
	
	var data="op=set";
	data += "&index="+k;
	data += "&id="+CalEvents[k].id;
	data += "&_owner="+CalEvents[k]._owner;
	data += "&calEventClass="+CalEvents[k].calEventClass;
	data +=buildDataQuery(k);
	
	YAHOO.util.Dom.get('progress_msg').innerHTML=_TT._PLS_WAIT;
	/*
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: setCompleted}
    );
	*/
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: setCompleted,
    	failure: ajax_failure
    });
	return true;
}

function buildDataQuery(k) {
	
	data='';
	
	for (var i=0;i<evForm.form.length;i++) {
		
		switch (evForm.form[i].type) {
			case "text":
			case "textarea":
			case "day":
			case "month":
			case "year":
			case "hour":
			case "min":
			case "sec":
			case "select":
			case "checkbox":
			case "hidden":
				
				var cValue=new String();
				
				if (evForm.form[i].permissions && evForm.form[i].permissions!=permissions) {
					cValue=evForm.form[i].defvalue;
				} else {
					if (evForm.form[i].type=="checkbox") {
						if (YAHOO.util.Dom.get(evForm.form[i].id).checked) {
							cValue="on";
						} else {
							cValue="";
						}
					} else {
						cValue=YAHOO.util.Dom.get(evForm.form[i].id).value;
					}
				}
				
				cValue=escape(cValue);
				eval("CalEvents["+k+"]."+evForm.form[i].id+"='"+cValue+"'");
				data+='&'+evForm.form[i].id+'='+cValue;
				break;	
				
			
				
		}
	}

	return data;
}

function setCompleted(ObjReq) {
	//var setResults = ObjReq.responseText;
	
	//var evData = setResults.evalJSON(true);
	try {
		evData = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }	
	YAHOO.util.Dom.get('progress_msg').innerHTML="";
	
	if(evData.error == undefined) {
		
		CalEvents[evData.index].id=evData.id;
	} else {
	
		NoticeMsg.display(evData.errormsg, 'displayCalendar', 'error');
		var k = evData.index;
		CalEvents[k]=new Object();
		
	}
	enableCal();
	
	cal.refresh();
	//destroyWindow('wEvent');
}


function delEvent(id) {

	//if (YAHOO.util.Dom.get('wEvent')) 
		//destroyWindow('wEvent');
		
//	if (YAHOO.util.Dom.get('wDelEvent')) 
		//destroyWindow('wDelEvent');
		
	var name="wDelEvent";
	var title=_TT._DEL_EVENT;
	
	disableCal();
	
   	var str="";
	str += '<br />'+_TT._AREYOUSURE+'<br /><br />';

	var buttns=[];
	buttns[buttns.length] = { text:_TT._YES, handler: function(){
		sendDelEvent(this.cal_data.id);
		this.destroy();
	} };
	buttns[buttns.length] = { text: _TT._NO, handler: function(){this.destroy();}, isDefault:true};

	var w = new YAHOO.widget.SimpleDialog("simpledialog_"+name, {
		fixedcenter: true,
		visible: true,
		close: true,
		modal: true,
		constraintoviewport: true,
		buttons: buttns
	} );
	w.cal_data = {id:id};
	w.setHeader(title);
	w.setBody(str);
	w.render(document.body);
}

function sendDelEvent(id) {
	YAHOO.util.Dom.get('progress_msg').innerHTML=_TT._PLS_WAIT;
	
	var k=findIndex(id);
	
	
	var data="op=del";
	data += "&index="+k;
	data += "&id="+CalEvents[k].id;
	data += "&_owner="+CalEvents[k]._owner;
	data += "&calEventClass="+CalEvents[k].calEventClass;
	//data +=buildDataQuery(k);
	/*
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: delCompleted}
    );*/
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: delCompleted,
    	failure: ajax_failure
    });
	
}

function delCompleted(ObjReq) {
	enableCal();
	//destroyWindow('wDelEvent');
	updateCalendar(cal.date);
}

function setToolTip(date) {
	return DayEvents(date)
}

function DayEvents(date) {
	var day=date.getDate();
	var month=date.getMonth()+1;
	var year=date.getFullYear();
	
	var date0=new Date(year,month,day);
	
	var EventLists=new Array();
	var k=0;
	for (var i=0;i<CalEvents.length;i++) {
		var date1=new Date(CalEvents[i].start_year,CalEvents[i].start_month,CalEvents[i].start_day);
		var date2=new Date(CalEvents[i].end_year,CalEvents[i].end_month,CalEvents[i].end_day);
		if (date0>=date1 && date0<=date2) {
			EventLists[k]=CalEvents[i];
			k++;
		}
	}
	
	return EventLists;
		
}

function showCalendar() {
   	var parent = YAHOO.util.Dom.get("displayCalendar");

  	cal.setDayEventsHandler(DayEvents);
  	cal.getDateToolTip = setToolTip;
  	cal.create(parent);
  	cal.show();
}


function setUpCalendar() {
	var data="op=getLang";
	/*var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: setUpComplete}
    );*/
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: setUpComplete,
    	failure: ajax_failure
    });
}

function setUpComplete(ObjReq) {
	//var langText = ObjReq.responseText;
	//_TT = langText.evalJSON(true);
	try {
		_TT = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }
	cal = new Calendar(1, null);	
	
	loadCalendar();
}

function loadCalendar() {
	
	cDate = new Date();
	var month=cDate.getMonth()+1;
	var year=cDate.getFullYear();
	
	//get calendar data for the today month
	var parent = YAHOO.util.Dom.get("displayCalendar");
	el=document.createElement("div");
	el.id="progress_msg";
	el.innerHTML=_TT._PLS_WAIT;
	parent.appendChild(el);
	
	var data="calClass="+calClass+"&op=get&month="+month+"&year="+year;
	if (calClass=='lms_classroom') {
		if (YAHOO.util.Dom.get('classroom_selected').value) data+="&classroom="+YAHOO.util.Dom.get('classroom_selected').value;
	}
	/*
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: CalendarLoaded}
    );*/
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: CalendarLoaded,
    	failure: ajax_failure
    });
}

function CalendarLoaded(ObjReq) {
	var parent = YAHOO.util.Dom.get("displayCalendar");
	//var calText = ObjReq.responseText;
	//alert(calText);
	//CalEvents = calText.evalJSON(true);
	try {
		CalEvents = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }
	if (!CalEvents.length) CalEvents=new Array();
    YAHOO.util.Dom.get('progress_msg').innerHTML="";
	
	showCalendar();
	
}

function updateCalendar(date) {
	cDate=date;
	YAHOO.util.Dom.get('progress_msg').innerHTML=_TT._PLS_WAIT;
	var month=cDate.getMonth()+1;
	var year=cDate.getFullYear();
	var data="calClass="+calClass+"&op=get&month="+month+"&year="+year;
	if (calClass=='lms_classroom') 
		if (YAHOO.util.Dom.get('classroom_selected').value) data+="&classroom="+YAHOO.util.Dom.get('classroom_selected').value;
		/*
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'post', parameters: data, onComplete: CalendarUpdated}
    );
    */	
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', serverUrl+data, {
    	success: CalendarUpdated,
    	failure: ajax_failure
    });
}

function CalendarUpdated(ObjReq) {
	var parent = YAHOO.util.Dom.get("displayCalendar");
	//var calText = ObjReq.responseText;
	//CalEvents = calText.evalJSON(true);
	try {
		CalEvents = YAHOO.lang.JSON.parse(ObjReq.responseText);
	} catch (e) { ajax_failure(null); }
	if (!CalEvents.length) CalEvents=new Array();
	YAHOO.util.Dom.get('progress_msg').innerHTML="";
	cal._init(cal.firstDayOfWeek, cDate);
	
}

function findIndex(id) {
	for (var k=0;k<CalEvents.length;k++) 
		if (CalEvents[k].id==id) return k;
	
	return -1;
}

function disableCal() {
	/*cal.table.style.filter="alpha(opacity=50);";
	cal.table.style.opacity=".5;";
	evDisabled=true;*/
}

function enableCal() {
/*	cal.table.style.filter="alpha(opacity=100);";
	cal.table.style.opacity="1;";
	evDisabled=false;*/
}

function setup_cal(passed_url,passed_class,passed_eventclass,passed_mode,passed_perm,passed_idst) {
	
	calClass=passed_class;
	calEventClassDefault=passed_eventclass;
	mode=passed_mode; //edit, view
	permissions=passed_perm;
	idSt0=passed_idst;
}


function setup_url(passed_url,passed_class,passed_eventclass) {
	serverUrl=passed_url;
	calClass=passed_class;
	calEventClassDefault=passed_eventclass;
}

function setup_mode(passed_mode,passed_perm,passed_idst) {
	mode=passed_mode; //edit, view
	permissions=passed_perm;
	idSt0=passed_idst;
}

YAHOO.util.Event.onDOMReady(setUpCalendar);