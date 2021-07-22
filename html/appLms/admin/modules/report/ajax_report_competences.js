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

var optdata, rc_auto_inc=0, rc_signs=['<','<=','=','>=','>'], rc_flag=['yes','no'];
//rc_signs={l:'<',le:'<=',e:'=',ge:'>=',g:'>'}

function rc_init(e) {
  optdata = YAHOO.lang.JSON.parse(optdata_JSON);
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
  //alert(this.id);
  var index=this.id.split('_')[3];
  var idval=this.value;
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

function rc_removefilter(i) {
  var t = document.getElementById('rc_filter_'+i);
  t.parentNode.removeChild(t);
}


function rc_create_selection(index) {
  var sel = document.createElement('select');
  sel.id = 'rc_filter_idcomp_'+index; 
  sel.name = 'rc_filter_idcomp[]';
    
  var optgroup, option, opttext, str;
  
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
      str = optdata[x]['rows'][y]['name'];
      switch (optdata[x]['rows'][y]['type']) {
        case 'flag': break;
        case 'score': { str+='  ('+optdata[x]['rows'][y]['score_min']+'/'+optdata[x]['rows'][y]['score_max']+')'; } break;
      }
      opttext = document.createTextNode(str);
      option.appendChild(opttext);
      optgroup.appendChild(option);
    }
    sel.appendChild(optgroup);
  }

  return sel;
}

function rc_get_selection(a,useindexes) {
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

function rc_remove_childs(el) {
  if (el.hasChildNodes()) {
    while (el.childNodes.length>=1) {
      el.removeChild(el.firstChild);       
    } 
  }
}