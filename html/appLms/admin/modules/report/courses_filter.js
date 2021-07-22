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

var seldata, filter_cases, courses_auto_inc=0, courses_signs=['<','<=','=','>=','>'], courses_flag=['yes','no'];
var cals=new Array();

function courses_init(e) {
  //seldata = YAHOO.lang.JSON.parse(seldata_JSON);
  seldata=seldata_JSON;
  filter_cases = YAHOO.lang.JSON.parse(filter_cases_JSON);
  
  //...
  courses_auto_inc = document.getElementById('inc_counter').value;
  courses_selector_init();
	/*YAHOO.util.Event.addListener('all_courses', 'change', hide_courses_selector);
	YAHOO.util.Event.addListener('sel_courses', 'change', show_courses_selector);*/
}

function courses_selector_init() {
	YAHOO.util.Event.addListener('all_courses', 'click', hide_courses_selector);
	YAHOO.util.Event.addListener('sel_courses', 'click', show_courses_selector);
}

function show_courses_selector(e) {
	var sel=YAHOO.util.Dom.get('selector_container');//document.getElementById('selector_container');
	/*if (document.getElementById('sel_courses').checked) {
		sel.style.display='block';
	} else {
		sel.style.display='none';
	}*/
	sel.style.display='block';
	document.getElementById('csel_foot').innerHTML=courses_count;
}

function hide_courses_selector(e) {
	var sel=YAHOO.util.Dom.get('selector_container');//document.getElementById('selector_container');
	/*if (document.getElementById('all_courses').checked) {
		sel.style.display='none';
	} else {
		sel.style.display='block';
	}*/
	sel.style.display='none';
	document.getElementById('csel_foot').innerHTML=courses_all;
}

function courses_increment() {
	courses_auto_inc++;
	document.getElementById('inc_counter').value = courses_auto_inc;
}

function courses_get_option_type(id) {
  for (var i=0; i<seldata.length; i++) {
    if (id==seldata[i].key)
    	return seldata[i].type;
  }
  return false;
}

function courses_create_filter(e) {
  //alert(this.id);
  var index=this.id.split('_')[3];
  var idval=this.value;
  var t=document.getElementById('courses_filter_params_'+index);
  courses_remove_childs(t);
  var type=courses_get_option_type(idval);
  switch (type) {
    case filter_cases._FILTER_DATE: {
      var sel=courses_get_selection(courses_signs,false);
      sel.name='courses_filter[i'+index+'][sign]';
      t.appendChild(sel);

			var val=document.createElement('input');
      val.type='text';
      val.className='align_right';
      val.value='0';
      val.style.width='7em';
      val.id='courses_filter_'+index+'_value';
      val.name='courses_filter[i'+index+'][value]';
      t.appendChild(val);

      YAHOO.dateInput.setCalendar( "courses_filter_"+index+"_value", "", course_date_token);

			var inc=document.createElement('input');
      inc.type='hidden';
      inc.name='courses_filter[i'+index+'][option]';
      inc.value=document.getElementById('courses_filter_sel_'+index).value;//idval;
      t.appendChild(inc);
    } break;
    
    case filter_cases._FILTER_INTEGER: {
      var sel=courses_get_selection(courses_signs,false);
      sel.name='courses_filter[i'+index+'][sign]';
      var val=document.createElement('input');
      val.type='text';
      val.className='align_right';
      val.value='0';
      val.style.width='9em';
      val.name='courses_filter[i'+index+'][value]';
      var inc=document.createElement('input');
      inc.type='hidden';
      inc.name='courses_filter[i'+index+'][option]';
      inc.value=document.getElementById('courses_filter_sel_'+index).value;//idval;
      t.appendChild(sel);
      t.appendChild(val);
      t.appendChild(inc);
    } break;
    
    default: { }  
  }
}

function courses_addfilter() {
  var t = document.getElementById('courses_filter_list'); //the main div
  var div = document.createElement('div');
  div.id='courses_filter_'+courses_auto_inc;
  //div.style.padding='2px 0px';
  
  var sel=courses_create_selection(courses_auto_inc);
  YAHOO.util.Event.addListener(sel.id,'change',courses_create_filter );
  div.appendChild(sel);
  
  div.innerHTML+='<span id="courses_filter_params_'+courses_auto_inc+'"></span><a href="javascript:courses_removefilter('+courses_auto_inc+');">'+courses_remove_filter+'</a>';
  t.appendChild(div);
  
  courses_increment();//courses_auto_inc++;
}


function courses_resetfilters() {
	document.getElementById('courses_filter_list').innerHTML = '';
}


function courses_removefilter(i) {
  var t = document.getElementById('courses_filter_'+i);
  t.parentNode.removeChild(t);
}


function courses_create_selection(index) {
  var sel = document.createElement('select');
  sel.id = 'courses_filter_sel_'+index; 
  sel.name = 'courses_filter_sel[i'+index+']';
    
  var option, opttext, str;
  
  option = document.createElement('option');
  option.value = 0;
  opttext = document.createTextNode(courses_sel_opt_0);
  option.appendChild(opttext);
  sel.appendChild(option);
  
	for (x=0; x<seldata.length; x++) {
		option = document.createElement('option');
    option.value = seldata[x].key;
    opttext = document.createTextNode(seldata[x].label);
    option.appendChild(opttext);
    sel.appendChild(option);
	}

  return sel;
}

function courses_get_selection(a,useindexes) {
  var opt, sel=document.createElement('select');
  for (var x=0; x<a.length; x++) {
    opt=document.createElement('option');
    opt.value=(useindexes ? x : a[x]);
    opttext = document.createTextNode(a[x]);
    opt.appendChild(opttext);
    sel.appendChild(opt);
  }
  return sel;
}

function courses_remove_childs(el) {
  if (el.hasChildNodes()) {
    while (el.childNodes.length>=1) {
      el.removeChild(el.firstChild);       
    } 
  }
}