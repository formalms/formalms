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

var cart_summary = '';
var toggled_courses = new Array();
var current_table_cell='';
var id_course = false;

var server_location = '';

var ajax_failure = function(o) { alert('Unable to connect with the server.'); }

function show_cart_preview() {
	
	var cart =  YAHOO.util.Dom.get('the_cart');
	
	if(cart != undefined) {
		if(YAHOO.util.Dom.getStyle('the_cart', 'visibility') == 'visible') YAHOO.util.Dom.setStyle('the_cart', 'visibility', 'hidden');
		else YAHOO.util.Dom.setStyle('the_cart', 'visibility', 'visible');
		return;
	}
	cart = document.createElement('div');
	document.body.appendChild(cart);
	
	var pos = YAHOO.util.Dom.getXY('mo_shopping_cart');
	pos[1] += parseInt(YAHOO.util.Dom.getStyle('mo_shopping_cart', 'height'));
	YAHOO.util.Dom.setXY(cart, pos);
	
	cart.id = 'the_cart';
	cart.className = "shop_cart";
	
	cart.innerHTML = 'Loading ...';
	
	var objAjax = YAHOO.util.Connect.asyncRequest('GET', server_location+'ajax.server.php?mn=coursecatalogue&op=getCartSummary', {
    	success: print_cart,
    	failure: ajax_failure
    });
}

function print_cart(o) {

	try {
		parsed = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); }
	
	var cart =  YAHOO.util.Dom.get('the_cart');
	
	var str="";
	str += parsed.code ;
	str += '<form method="post" action="index.php?modname=coursecatalogue&op=go_cart">';
	str += parsed.button;
	str += '</form>';
	cart.innerHTML = str;
	
	var hide_cart = function(e) {
		var cart = YAHOO.util.Dom.get('the_cart');
		document.body.removeChild(cart);
	}
	YAHOO.util.Event.addListener('close_cart_command', "click", hide_cart);
}

function openWindowWithAction(id_c, action_to) {
	id_course=id_c;
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', server_location+'ajax.server.php?mn=coursecatalogue&op=' + action_to + '&id_course=' + id_c, {
    	success: openWindowWithAction_callback,
    	failure: ajax_failure,
    	argument: { id_course: id_c } 
    });
}

function openWindowWithAction_callback(o) {
	
	try {
		parsed = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); }
	
	var id_c = o.argument.id_course;
	
	var w = new Window();
	var new_form = document.createElement('form');
	new_form.method = 'post';
	new_form.action = parsed.next_op;
	
	w.id 		= parsed.id;
	w.width 	= ( parsed.width != undefined ? parsed.width : 600 );
	
	w.form 		= new_form;
	w.title 	= parsed.title;
	w.content 	= parsed.content;
	w.dosubmit 	= true;
	
	if(parsed.button != undefined) w.buttons = parsed.button;
	w.show();
}

function report_error(text) {
	
	var w = new Window();
	w.id 		= 'error';
	w.width 	= 600;
	w.title 	= 'Error reporting';
	w.content 	= text;
	w.dosubmit 	= true;
	w.show();	
}

function openComment(id_c) {
	id_course=id_c;
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', server_location+'ajax.server.php?mn=coursecatalogue&op=comment_it&id_course=' + id_c, {
    	success: openComment_callback,
    	failure: ajax_failure,
    	argument: { id_course: id_c } 
    });
}

function addajaxcomment() {
	
	var blu = 'ajax.server.php?mn=coursecatalogue&op=addnewcomment' 
		+ "&id_course=" + YAHOO.util.Dom.get('ajaxcomment_ext_key').value
		+ "&reply_to=" + YAHOO.util.Dom.get('ajaxcomment_reply_to').value;
	

	var objAjax = YAHOO.util.Connect.asyncRequest('POST', blu, {
	    	success: openComment_callback,
	    	failure: ajax_failure,
	    	argument: { id_course: YAHOO.util.Dom.get('ajaxcomment_ext_key').value } 
	    }, "text_of=" + YAHOO.util.Dom.get('ajaxcomment_textof').value);
}

function delComment(comment_id,id_c) {
	id_course=id_c;
	var objAjax = YAHOO.util.Connect.asyncRequest('POST', server_location+'ajax.server.php?mn=coursecatalogue&op=delcomment'
		+ "&comment_id=" + comment_id
		+ "&id_course=" + id_course, {
	    	success: openComment_callback,
	    	failure: ajax_failure,
	    	argument: { id_course: id_c } 
	    });
}

function openComment_callback(o) {
	try {
		parsed = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); }

	var id_c = o.argument.id_course;
	
	var w = new Window();
	
	w.id 		= 'course_commment_'+id_c;
	w.width 	= ( parsed.width != undefined ? parsed.width : 600 );
	
	w.title 	= parsed.title;
	w.content 	= parsed.content
	w.dosubmit 	= true;
	
	w.show();
}

function course_vote(id_course, evaluation) {
	
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', server_location+'ajax.server.php?mn=coursecatalogue&op=course_vote'
    	+ '&id_course=' + id_course 
    	+ "&evaluation=" + evaluation, {
	    	success: course_vote_callback,
	    	failure: ajax_failure 
	    });
}

function course_vote_callback(o) {
	
	try {
		parsed = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); }
	
	var score = YAHOO.util.Dom.get('course_score_' + parsed.id_course)	
	
	score.innerHTML = parsed.new_score;
	switch(parsed.evaluation) {
		case "bad" : {
			YAHOO.util.Dom.get('score_image_bad_' + parsed.id_course).src 	= parsed.path_image + 'bad_grey.png';
			YAHOO.util.Dom.get('score_image_good_' + parsed.id_course).src 	= parsed.path_image + 'good.png';
		};break;
		case "good" : {
			YAHOO.util.Dom.get('score_image_bad_' + parsed.id_course).src 	= parsed.path_image + 'bad.png';
			YAHOO.util.Dom.get('score_image_good_' + parsed.id_course).src 	= parsed.path_image + 'good_grey.png';
		};break;
	}
}


function course_dash(link, id_course, elem_id, normal_subs) {
	
	if(link.className == 'show_details_more') link.className = 'show_details_less';
	else link.className = 'show_details_more';
	
	if(YAHOO.util.Dom.get(elem_id).innerHTML != '') {
		
		YAHOO.Animation.BlindToggle(elem_id);
	} else {
		
	    var objAjax = YAHOO.util.Connect.asyncRequest('POST', server_location+'ajax.server.php?mn=coursecatalogue&op=getdashcourse'
	    	+ '&id_course=' + id_course 
	    	+ "&elem_id=" + elem_id
	    	+ "&normal_subs=" + (normal_subs ? 1 : 0), {
		    	success: course_dash_callback,
		    	failure: ajax_failure,
		    	argument: { id_course: id_course }
		    });
		YAHOO.util.Dom.get(elem_id).innerHTML = 'Loading ...';
	}
}

function course_dash_callback(o) {
	
	try {
		parsed = YAHOO.lang.JSON.parse(o.responseText);
	} catch (e) { ajax_failure(null); }
	
	YAHOO.util.Dom.get(parsed.elem_id).innerHTML = '';
	YAHOO.util.Dom.get(parsed.elem_id).innerHTML = parsed.content;
	YAHOO.util.Dom.get(parsed.elem_id).style.display = 'none';
	
	YAHOO.Animation.BlindToggle(parsed.elem_id);
	
	YAHOO.util.Dom.get('course_edition_' + o.id_course).style.display = 'none';
	YAHOO.util.Dom.get('course_edition_' + o.id_course + '_close').style.display = 'none';
	
}
