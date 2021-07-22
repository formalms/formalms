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

var user_profile_server_url;
var user_profile_image_url;
var user_profile_lang;

function setup_user_profile(passed_server_url, image_url) {

	user_profile_server_url = passed_server_url;
	user_profile_image_url = image_url;
}

function ask_a_friend(id_user) {

	var w = new Window();
	w.id 		= "window_ask_friend";
	w.width 	= 500;

	w.title 	= user_profile_lang._TITLE_ASK_A_FRIEND;

	var forms = document.createElement('form');
	forms.id = 'form_ask_message';
	forms.method = 'post';
	forms.action = 'javascript: void(0)';
	forms.onsubmit = function () { send_ask_friend(id_user); return false; }
	w.form = forms;

	var content = '<label for="message_request">' + user_profile_lang._WRITE_ASK_A_FRIEND + '</label><br/>'
		+ '<textarea id="message_request" name="message_request" cols="52" rows="7">'
		+ '</textarea>';
	w.content = content;

	var butt = "";
	butt += '<input type="submit" value="' + user_profile_lang._SEND + '" onclick="destroyWindow(\'' + w.id + '\');" /> ';
	butt += '<input type="button" value="' + user_profile_lang._UNDO + '" onclick="destroyWindow(\'' + w.id + '\');" />';
	w.buttons = butt;

	w.show();
}

function send_ask_friend(id_user) {

	NoticeMsg.loading();
	var data = "&op=send_ask_friend"
		+ "&id_friend=" + id_user
		+ "&message_request=" + encodeURI( YAHOO.util.Dom.get('message_request').value );
    var objAjax = YAHOO.util.Connect.asyncRequest('POST', user_profile_server_url+data, {
	    	success: send_ask_friend_callback
	});
}

function send_ask_friend_callback(objReq) {

	NoticeMsg.endLoading();
	try {
		result = YAHOO.lang.JSON.parse(objReq.responseText);
	} catch (e) { return; }
	NoticeMsg.display( ( result.re ? user_profile_lang._ASK_FRIEND_SEND : user_profile_lang._ASK_FRIEND_FAIL ), null, ( result.re ? '' : 'error' ), 30000 );
	if(result.re == true) {

		YAHOO.util.Dom.get('profile_friend_request').innerHTML = '<img src="'+user_profile_image_url+'profile/add_to_friend_disabled.png" alt="" />';
	}
}

function send_a_message(id_user) {
	var w = new Window();
	w.id 		= "window_ask_friend";
	w.width 	= 600;

	var forms = document.createElement('form');
	forms.id = 'form_a_message';
	forms.method = 'post';
	forms.action = 'javascript: void(0)';
	forms.onsubmit = function () { send_message(id_user); return false; }

	var content = '<div class="form_a_message">'
		+ '<label for="message_subject">' + user_profile_lang._SUBJECT + '</label>'
		+ '<input type="text" id="message_subject" name="message_subject" maxlength="255" /><br />'

		+ '<label for="message_text">' + user_profile_lang._MESSAGE_TEXT + '</label><br />'
		+ '<textarea id="message_text" name="message_text" rows="7">'
		+ '</textarea>'
		+ '</div>';

	var butt="";
	butt += '<input type="submit" value="'+user_profile_lang._SEND+'" onclick="destroyWindow(\'' + w.id + '\');" /> ';
	butt += '<input type="button" value="'+user_profile_lang._UNDO+'" onclick="destroyWindow(\'' + w.id + '\');" />';

	w.title 	= user_profile_lang._SUBJECT;
	w.content 	= content;
	w.form 		= forms;
	w.buttons 	= butt;
	w.show();
}

function send_message(id_user) {

	var data = "&op=send_message"
		+ "&send_to=" + id_user
		+ "&message_subject=" + encodeURI( YAHOO.util.Dom.get('message_subject').value )
		+ "&message_text=" + encodeURI( YAHOO.util.Dom.get('message_text').value );
	var objAjax = new Ajax.Request(
        	user_profile_server_url,
        	{method: 'post', parameters: data, onComplete: send_message_callback}
    );
	NoticeMsg.loading();
}

function send_message_callback(objReq) {
	try {
		result = YAHOO.lang.JSON.parse(objReq.responseText);
	} catch (e) { return; }
	NoticeMsg.display( ( result.re ? user_profile_lang._OPERATION_SUCCESSFUL : user_profile_lang._OPERATION_FAILURE ), null, ( result.re ? '' : 'error' ), 30000 );
	NoticeMsg.endLoading();
}
