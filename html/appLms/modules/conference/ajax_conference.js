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

var serverUrl="./modules/conference/ajax.conference.php";
var oldIdp=null;
var pag;
var idF;
var idP;

function getMaxRoom() {
	
	var room_type=YAHOO.util.Dom.get('room_type').value;
	
	var data="op=getmaxroom&room_type="+room_type;
	var objAjax = new Ajax.Request(
        	serverUrl,
        	{method: 'get', parameters: data, onComplete: setMaxRoom}
    );	
}

function setMaxRoom (ObjReq) {
	var maxp = ObjReq.responseText;
	var actual_value=YAHOO.util.Dom.get('maxparticipants').value;
	changeDropDownValue(maxp,actual_value,YAHOO.util.Dom.get('maxparticipants'));
}


function changeDropDownValue(val_max,val,dropdown)
{
  for (var i=0;i<dropdown.options.length;i++) {
	  dropdown.options[i] = null;
  }
  
  for(var i=0;i<=val_max;i++) {
  	dropdown.options[i] =  new Option(i, i, false, false);
    if(dropdown.options[i].value == val) dropdown.selectedIndex = i;
  }
  
}



