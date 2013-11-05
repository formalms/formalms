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

var optdata, rc_signs=['<','<=','=','>=','>'], rc_flag=['yes','no'];
var rc_auto_inc=0;

function rc_init(e) {
  optdata = YAHOO.lang.JSON.parse(optdata_JSON);
  
  //initialize rc_auto_inc and existent filters in php
	rc_init_filters();  
}


function rc_init_filters() {
	var i, j, div, t = document.getElementById('rc_filter_list'); //the main div
	var spn, temp, is_row=false; 
	//for(var i=0; i<rc_initial_filters.length; i++) { //for each competence id
	
	for (i in rc_initial_filters) {
		
		temp=rc_initial_filters[i];
		is_row = (temp.sign || temp.flag);
		
		if (!is_row) {
			for (j in temp) {
				div = document.createElement('div');
				div.id='rc_filter_'+rc_auto_inc;
				div.style.padding='2px 0px';
				var sel=rc_create_selection(rc_auto_inc, i);
				YAHOO.util.Event.addListener(sel.id,'change',rc_create_filter);
				
				div.appendChild(sel);
				div.appendChild(rc_get_init_filter_content(rc_auto_inc, i, temp[j] ));
				div.innerHTML+='<a href="javascript:rc_removefilter('+rc_auto_inc+');">'+rc_remove_filter+'</a>';
				
				t.appendChild(div);
				rc_auto_inc++;
			}
		} else {
			div = document.createElement('div');
			div.id='rc_filter_'+rc_auto_inc;
			div.style.padding='2px 0px';
			var sel=rc_create_selection(rc_auto_inc, i);
			YAHOO.util.Event.addListener(sel.id,'change',rc_create_filter);
			
			div.appendChild(sel);
			div.appendChild(rc_get_init_filter_content(rc_auto_inc, i, temp ));
			div.innerHTML+='<a href="javascript:rc_removefilter('+rc_auto_inc+');">'+rc_remove_filter+'</a>';
			t.appendChild(div);
			rc_auto_inc++;
		}
		
	}
}

function rc_get_init_filter_content(index, idval, values) {
	var t; //alert(values.value+' - '+values.sign);
	t=document.createElement('span');
	t.id='rc_filter_params_'+index;
	t.style.margin=' 0px 10px';
  var type=rc_get_option_type(idval);
  switch (type) {
    case 'flag': {
		var sel=rc_get_selection(rc_flag, false, values.flag);
		sel.name='rc_filter['+idval+'][flag]';
		t.appendChild(sel);
    } break;
    
    case 'score': {
		var sel = rc_get_selection(rc_signs, true, values.sign);
		var val = document.createElement('input');
		t.appendChild(sel);
		t.appendChild(val);

		sel.name='rc_filter['+idval+']['+index+'][sign]';
		val.type='text';
		val.className='align_right';
		val.name='rc_filter['+idval+']['+index+'][value]';
		val.setAttribute("value", values.value);
    } break;
    
    default: { }  
  }
  return t;
}


function rc_get_option_type(id) {
  var temp;
  for (var i=0; i<optdata.length; i++) {
    temp=optdata[i]['rows'];
    for (var j=0; j<temp.length; j++) {
      if (temp[j]['id']==id) return temp[j]['type'];
    }
  }
  return false;
}

function rc_create_filter(e) {
	rc_filter_content(this);
}

function rc_filter_content(sel) {
  var index=sel.id.split('_')[3];
  var idval=sel.value;
  var t=document.getElementById('rc_filter_params_'+index);
  rc_remove_childs(t);
  var type=rc_get_option_type(idval);
  switch (type) {
    case 'flag': {
      var sel=rc_get_selection(rc_flag,false);
      sel.name='rc_filter['+idval+'][flag]';
      t.appendChild(sel);
    } break;
    
    case 'score': {
      var sel=rc_get_selection(rc_signs,true);
      sel.name='rc_filter['+idval+']['+index+'][sign]';
      var val=document.createElement('input');
      val.type='text';
      val.className='align_right';
      val.value='0';
      val.style.width='4em';
      val.name='rc_filter['+idval+']['+index+'][value]';
      t.appendChild(sel);
      t.appendChild(val);
    } break;
    
    default: { }  
  }
}

function rc_addfilter() {
  var t = document.getElementById('rc_filter_list'); //the main div
  var div = document.createElement('div');
  div.id='rc_filter_'+rc_auto_inc;
  div.style.padding='2px 0px';
  
  var sel=rc_create_selection(rc_auto_inc);
  YAHOO.util.Event.addListener(sel.id,'change',rc_create_filter );
  div.appendChild(sel);
  
  div.innerHTML+='<span id="rc_filter_params_'+rc_auto_inc+'" style="margin: 0px 10px;"></span><a href="javascript:rc_removefilter('+rc_auto_inc+');">'+rc_remove_filter+'</a>';
  t.appendChild(div);
  
  rc_auto_inc++;
}


function rc_resetfilters() {
	document.getElementById('rc_filter_list').innerHTML = '';
}


function rc_removefilter(i) {
  var t = document.getElementById('rc_filter_'+i);
  t.parentNode.removeChild(t);
}


function rc_create_selection(index, selected) {
  var sel = document.createElement('select');
  sel.id = 'rc_filter_idcomp_'+index; 
  sel.name = 'rc_filter_idcomp[]';
    
  var optgroup, option, opttext, str, counter=0, sel_index=false;
  
  option = document.createElement('option');
  option.value = 0;
  opttext = document.createTextNode(rc_sel_opt_0);
  option.appendChild(opttext);
  sel.appendChild(option);
  
  for (x=0; x<optdata.length; x++) {
    optgroup = document.createElement('optgroup');
    optgroup.label = optdata[x]['name'];  
    for (y=0; y<optdata[x]['rows'].length; y++) {
      option = document.createElement('option');
      option.value = optdata[x]['rows'][y]['id'];
      if (option.value==selected) {
	  	sel_index=counter;
	  	option.selected = true;
			option.setAttribute("selected", "selected");
		}
      str = optdata[x]['rows'][y]['name'];
      switch (optdata[x]['rows'][y]['type']) {
        case 'flag': { str+='  (flag)'; }break;
        case 'score': { str+='  (score)'; } break;
      }
      opttext = document.createTextNode(str);
      option.appendChild(opttext);
      optgroup.appendChild(option);
      counter++;
    }
    sel.appendChild(optgroup);
  }

	if (sel_index!==false) sel.selectedIndex=sel_index;

  return sel;
}

function rc_get_selection(a,useindexes,selval) {
  var opt, sel_index=false, sel=document.createElement('select');
  
  for (var x=0; x<a.length; x++) {
    opt=document.createElement('option');
    opt.value=(useindexes ? x : a[x]);
    
    if (opt.value==selval) {
    	sel_index = x;
		opt.selected = true;
		opt.setAttribute("selected", "selected");
	}
	opttext = document.createTextNode(a[x]);
    opt.appendChild(opttext);
    sel.appendChild(opt);
	if (sel_index!==false) sel.selectedIndex=sel_index;
  }
  return sel;
}

function rc_remove_childs(el) {
  if (el.hasChildNodes()) {
    while (el.childNodes.length>=1) {
      el.removeChild(el.firstChild);       
    } 
  }
}


/*
function rc_restorefilters() {
	var sel;
	for (var i=0; i<prev_filters.length; i++) {
		sel=rc_create_selection(prev_filters[i].index, prev_filters[i].option);
		
	}

}
*/