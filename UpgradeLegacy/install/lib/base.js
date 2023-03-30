
var validating =false;
var orig_btn_color ='';
var orig_btn_back ='';

YAHOO.util.Event.onDOMReady(function() {

	orig_btn_color =YAHOO.util.Dom.get('btn_next').style.color;
	orig_btn_back =YAHOO.util.Dom.get('btn_next').style.background;

});


function labelColor(item_id, color, back_color) {
	var item =YAHOO.util.Dom.get(item_id).parentNode;
	item.childNodes[0].style.color=color;
	if (back_color != null) {
		YAHOO.util.Dom.get(item_id).style.backgroundColor =back_color;
	}
}


function setInputError(item_id, do_set) {
	if (do_set == null || do_set == true) {
		labelColor(item_id, '#F00', '#FFF0F0');
	}
	else if (do_set == false) {
		labelColor(item_id, '#000', '#FFF');
	}
}


function validateInput(item_arr, op) {

	var callback = {
		success: function(o) {
			var res =YAHOO.lang.JSON.parse(o.responseText);
			validating =false;
			YAHOO.util.Dom.get('loading_box').style.visibility ='hidden';
			disableBtnNext(!res['success']);

			if (res['success']) {
                hideWarnMsg();
                if (res['msg']!=""){
                    setWarnMsg(res['msg']);
				}
			}
			else {
				setWarnMsg(res['msg']);
			}

			for (i in res['err']) {
				setInputError(res['err'][i]);
			}
			for (i in res['ok']) {
				setInputError(res['ok'][i], false);
			}			
		},
		error: function(o) { validating =false; }
	};

	var sUrl ='index.php?ajax_validate=1';
	var param ='step='+current_step;
	var val ='';

	if (op != null && op != '') {
		param+='&op='+op;
	}

	for (i in item_arr) {
		val =YAHOO.util.Dom.get(item_arr[i]).value;
		param+='&'+item_arr[i]+'='+escape(val);
	}

	if (!validating) {
		validating =true;
		disableBtnNext(true);
		YAHOO.util.Dom.get('loading_box').style.visibility ='visible';
		YAHOO.util.Connect.asyncRequest('POST', sUrl, callback, param);
	}

}


function hideWarnMsg() {
	YAHOO.util.Dom.get('warn_msg_box').style.visibility ='hidden';
}


function setWarnMsg(txt) {
	YAHOO.util.Dom.get('warn_txt').innerHTML =txt;
	YAHOO.util.Dom.get('loading_box').style.visibility ='hidden';
	YAHOO.util.Dom.get('warn_msg_box').style.visibility ='visible';
}


function disableBtnNext(do_disable) {
	if (!validating) {
		YAHOO.util.Dom.get('btn_next').style.background =(do_disable ? '#AAA' : orig_btn_back);
		YAHOO.util.Dom.get('btn_next').style.color =(do_disable ? '#FFF' : orig_btn_color);
	}
	YAHOO.util.Dom.get('btn_next').disabled =do_disable;
}