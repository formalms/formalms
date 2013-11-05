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

if (!YAHOO.dialogConstants) {
	YAHOO.namespace("dialogConstants");
	YAHOO.dialogConstants = {
		_CONFIRM: "Ok",
		_UNDO: "Cancel",
		_CLOSE: "Close",
		_LOADING: "Loading",
		_ERROR: "Error",
		_CONNECTION_ERROR: "Server error",
		_loadingIcon: "",
		_smallLoadingIcon: "",
		setProperties: function(o) {
			var x, name;
			for (x in o) this.setProperty(x, o[x]);
		},
		setProperty: function(name, value) {
			var pname = '_' + name;
			/*if (pname in this) */this[pname] = value;
		},
		getProperty: function(name) {
			return this[name] ? this[name] : null;
		}
	};
}

var WriteDialogMessage = function(oDialog, message) {
	var mElement = YAHOO.util.Dom.get(oDialog.id+"_dialog_message");
	if (mElement) mElement.innerHTML = message || "";
}

var ResetDialogMessage = function(oDialog) {
	WriteDialogMessage(oDialog, "");
}


var CreateDialog = function(id, oParams) {
	if (!id) return false;
	YAHOO.namespace("dialogs."+id);

	YAHOO.dialogs[id] = function(e) {
		if (e) YAHOO.util.Event.preventDefault(e);

		var consts = YAHOO.dialogConstants;
		var confirm = oParams.directSubmit 
		? function() {
			this.form.submit();
			if (oParams.hideaftersubmit) this.destroy();
		}
		: function() {
			setLoadingMsg.call(this);
			if (oParams.beforeSubmitEvent) oParams.beforeSubmitEvent.call(this);
			this.submit();
		};
		var undo = function() {
			this.hide();
		};

		var buttons = [];
		if (oParams.confirmOnly) {
			buttons.push({
				text: consts._CLOSE,
				handler: undo
			});
		} else {
			buttons.push({
				text: consts._CONFIRM,
				handler: confirm,
				isDefault: true
			});
			buttons.push({
				text: consts._UNDO,
				handler: undo
			});
		}

		var setLoadingMsg = function() {
			var i, el = document.createElement("DIV"), butts = this.getButtons();
			el.innerHTML = '<div id="dialog_loading_msg_'+id+'" class="dialog_loading_msg">'
			+'<span>'+consts._LOADING+' ...</span></div>';
			this.footer.appendChild(el);
			for (i=0; i<butts.length; i++)
				butts[i].set("disabled", true);
		};

		var unsetLoadingMsg = function() {
			var i, el = YAHOO.util.Dom.get("dialog_loading_msg_"+id), butts = this.getButtons();
			if (el) el.parentNode.removeChild(el);
			if (butts)
				for (i=0; i<butts.length; i++)
					butts[i].set("disabled", false);
		}

		var oConf = {};

		if (oParams.width) oConf.width = oParams.width;
		if (oParams.height) oConf.height = oParams.height;

		oConf.fixedcenter = (oParams.fixedcenter ? oParams.fixedcenter : false);
		oConf.constraintoviewport = (oParams.constraintoviewport ? oParams.constraintoviewport : false);
		oConf.visible = (oParams.visible ? oParams.visible : false);
		oConf.modal = (oParams.modal ? oParams.modal : false);
		oConf.draggable = (oParams.draggable ? oParams.draggable : false);
		oConf.hideaftersubmit = (oParams.hideaftersubmit ? oParams.hideaftersubmit : false);
		oConf.close = (oParams.close ? oParams.close : true);
		oConf.buttons = buttons;

		var oDialog = new YAHOO.widget.Dialog(id, oConf);

		//set dialog events
		if (oParams.renderEvent) oDialog.renderEvent.subscribe(oParams.renderEvent);
		if (oParams.destroyEvent) oDialog.destroyEvent.subscribe(oParams.destroyEvent);

		if (oParams.submitEvent) oDialog.submitEvent.subscribe(oParams.submitEvent);
		if (oParams.beforeSubmitEvent) oDialog.beforeSubmitEvent.subscribe(oParams.beforeSubmitEvent);
		if (oParams.beforeRenderEvent) oDialog.beforeRenderEvent.subscribe(oParams.beforeRenderEvent);

		oDialog.hideEvent.subscribe(function(e) {
			YAHOO.util.Event.stopEvent(e);
			this.destroy();
		}, oDialog);

		var writeMessage = function(message) {
			var mElement = YAHOO.util.Dom.get(id+"_dialog_message");
			if (mElement) mElement.innerHTML = message;
		};


		var getUIFeedback = function(message, type) { // TODO: move this somewhere else?
			return '<p class="pcontainer-feedback"><span class="ico-sprite fd_'+type+'"><span></span></span>&nbsp;'+message+'</p>';
		};


		oDialog.callback = {
			success: function(oResponse) {
				var o = YAHOO.lang.JSON.parse(oResponse.responseText);
				unsetLoadingMsg.call(this);
				if (o.success) {
					if (oParams.callback) oParams.callback.call(this, o);
				} else {
					writeMessage(o.message ? o.message : consts._ERROR);
					if (o.force_page_refresh) {
						window.location.reload();
					}
				}
			},
			upload: function(oResponse) {
				//var responseText =oResponse.responseText.replace(/^<pre[^>]*>/, '').replace(/<\/pre>$/, '');
				var responseText =oResponse.responseText;
				var o = YAHOO.lang.JSON.parse(responseText);
				if (o.message && o.feedback_type) {
					o.message =getUIFeedback(o.message, o.feedback_type);
				}
				unsetLoadingMsg.call(this);
				if (o.success) {
					if (oParams.upload) oParams.upload.call(this, o);
				} else {
					writeMessage(o.message ? o.message : consts._ERROR);
					if (o.force_page_refresh) {
						window.location.reload();
					}
				}
			},
			failure: function() {
				writeMessage(consts._CONNECTION_ERROR);
				unsetLoadingMsg.call(this);
			},
			scope: oDialog
		};

		// if (oParams.upload) oDialog.callback.upload = oParams.upload;

		var centerAtLoad = true; //TO DO: make it optional

		var messageDiv = '<div id="'+id+'_dialog_message"></div>';
		if (oParams.isDynamic) {
			oDialog.setHeader(consts._LOADING + '...');
			oDialog.setBody(messageDiv+'<div class=""><img src="'+consts._loadingIcon+'" alt="'+consts._LOADING+'" /></div>');
			var ajaxUrl = (YAHOO.lang.isFunction(oParams.ajaxUrl) ? oParams.ajaxUrl() : oParams.ajaxUrl);
			var postdata = (oParams.dynamicPostData
				? (YAHOO.lang.isFunction(oParams.dynamicPostData) ? oParams.dynamicPostData() : ""+oParams.dynamicPostData)
				: "");
			YAHOO.util.Connect.asyncRequest("POST", ajaxUrl, {
				success: function(oResponse) {
					var o = YAHOO.lang.JSON.parse(oResponse.responseText);
					this.setHeader(o.header ? '<span>'+o.header+'</span>' : "");
					this.setBody(messageDiv + (o.body ? o.body : ""));
					if (centerAtLoad) this.center();//if (oConf.fixedcenter) this.center();
					//manage some "magic" parameters for input to be initialized with scripts
					if (o.__date_inputs) {
						var i, a;
						for (i=0; i<o.__date_inputs.length; i++) {
							a = o.__date_inputs[i];
							YAHOO.dateInput.setCalendar(a[0], a[1], a[2]);
						}
					}
					//generic script, if specified (not recommended, because of the evil eval)
					if (o.script) eval(o.script);
				},
				failure: function() {
					writeMessage(consts._CONNECTION_ERROR);
				},
				scope: oDialog
			}, postdata);
		} else {
			var content = "";
			if (oParams.body) {
				if (YAHOO.lang.isFunction(oParams.body))
					content = oParams.body.call(this);
				else
					content = oParams.body;
			}
			oDialog.setHeader(oParams.header ? '<span>'+oParams.header+'</span>' : ' ');
			oDialog.setBody(messageDiv + content);
		}

		oDialog.render(document.body);
		if (centerAtLoad) { oDialog.center(); }
		oDialog.show();

		YAHOO.namespace("dialog_obj."+id);
		YAHOO.dialog_obj[id] = oDialog;
	};

	return YAHOO.dialogs[id];
}


/**
 * returns the dialog object
 *
 * can be used like this:
 * getDialog('my_dialog').setHeader('hello');
 * getDialog('my_dialog').subscribe('beforeSubmit', function () { my_editor.saveHTML(); });
 */
function getDialog(dialog_name) {
	YAHOO.namespace("dialog_obj."+dialog_name);
	return YAHOO.dialog_obj[dialog_name];
}