/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

var CustomerHelpShowPopUp = function() {

	var is_loaded = false;
	var callback = function(o) { //"this" is the popup
		var res = YAHOO.lang.JSON.parse(o.responseText);
		if (res.success) {
			dialog.destroy(); //this.destroy();
		} else {
			if (res.message) YAHOO.util.Dom.get("customer_help_error_message").innerHTML = res.message;
		}
	};

	var clickYes = function() { if (is_loaded) this.submit(); }
	var clickNo = function() { this.destroy(); }

	var dialog = new YAHOO.widget.Dialog("customer_help_popup", {
		width: "600px",
		fixedcenter: true,
		visible: false,
		draggable: true,
		close: true,
		constraintoviewport: true,
		modal: true,
		hideaftersubmit: false,
		buttons: [{
			text: LANG.get('_CONFIRM'),
			handler: clickYes,
			isDefault:true
		}, {
			text: LANG.get('_UNDO'),
			handler: clickNo
		}]
	} );

	dialog.setHeader(LANG.get('_DLG_TITLE'));
	dialog.setBody('<div class="align_center"><span>'+LANG.get('_LOADING')+'</span>:&nbsp;<img src="'+ICON_LOADING+'" /></div>');

	dialog.hideEvent.subscribe(function(e, args) {
		YAHOO.util.Event.stopEvent(args[0]);
		this.destroy();
	}, dialog);

	dialog.callback.success = callback;
	dialog.callback.failure  = function() {};

	dialog.render(document.body);
	dialog.show();

	YAHOO.util.Connect.asyncRequest("POST", CUSTOMER_HELP_AJAX_URL, {
		success: function(o) {
			var res = YAHOO.lang.JSON.parse(o.responseText);
			if (res.success) {
				this.setBody(res.body);
				this.center();
				is_loaded = true;
			}
		},
		failure: function() {},
		scope: dialog
	}, '');

}

//YAHOO.util.Event.addListener("customer_help", "click", CustomerHelpShowPopUp);