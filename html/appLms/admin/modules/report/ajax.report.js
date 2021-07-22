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

var ajax_failure = function(o) { alert('Unable to connect with the server.'); }
//var ajax_path = 'admin/modules/report/ajax.report.php';
ajax_path = 'ajax.adm_server.php?mn=report&plf=lms';

YAHOO.util.Event.addListener(window, 'load', function() {
	YAHOO.util.Event.addListener('save_filter', 'click', save_filter);
	YAHOO.util.Event.addListener('show_recipients', 'click', save_filter);
	//YAHOO.util.Event.addListener('schedule_filter', 'click', schedule_filter);
});


function save_complete(o) {
	//...
}

function schedule_complete(o) {
	//...
}

function save_window(o) {
	try {
		var re = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); return; }
	
	var w = new Window("window_save", {
		//height: 325,
			
		title: re.title,
		content: re.content,//+'<input type="hidden" name="filter_data" value="'+YAHOO.util.Dom.get('filter_data').value+'" />',
	
		ajax_req: ajax_path,
		onSuccess: save_complete,
		onFailure: ajax_failure
	});
	w.oButtons = [ { text:re.button_ok, handler:w.handleSubmit },
					{ text:re.button_undo, handler:w.handleCancel } ];
	w.show();
}

function save_filter(e) {
	var data = "&op=save_filter_window";
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', ajax_path+data, {
    	success: save_window
    });
}


//******************************************************************************

function schedule_filter(e) {
	var data = "&op=schedule_filter_window";
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', ajax_path+data, {
    	success: schedule_window
    });
}

function schedule_window(o) {
	try {
		var re = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); return; }
	
	var w = new Window("window_sched", {
		//height: 325,
			
		title: re.title,
		content: re.content,
	
		ajax_req: ajax_path,
		onSuccess: schedule_complete,
		onFailure: ajax_failure
	});
	w.oButtons = [ { text:re.button_ok, handler:w.handleSubmit },
					{ text:re.button_undo, handler:w.handleCancel } ];
	w.show();
}


//other functions
var _FAILURE = 'error';


function enable_schedulation(o, id_sched) {
	o.disabled=true; //no more operations allowed on the checkbox while ajaxing
	
	var val_el=document.getElementById('enable_value_'+id_sched);
	var value=val_el.value;
	
	var data = "&op=sched_enable&id="+id_sched+'&val='+value;
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', ajax_path+data, {
  	success:function(t) {
        var result = YAHOO.lang.JSON.parse(t.responseText);
        if (result.success){
            var temp=o.src;
            if (value==1)	{ o.src=temp.replace('unpublish.png', 'publish.png'); val_el.value=0; }
            if (value==0)	{ o.src=temp.replace('publish.png', 'unpublish.png'); val_el.value=1; }
			o.disabled=false;
		}else{
            var temp=o.src;
            if (value==0)	{ o.src=temp.replace('unpublish.png', 'publish.png'); val_el.value=0; }
            if (value==1)	{ o.src=temp.replace('publish.png', 'unpublish.png'); val_el.value=1; }
			o.disabled=false;
			alert(_FAILURE);
        }
    }, 
    failure:function(t) { 
			o.disabled=false;
			alert(_FAILURE); //... 
		} });
}