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

var wObjList= new Array();

Window = function(win_id, params) {

	if(wObjList[this.id] != undefined) return wObjList[this.id];

	this.id=win_id;
	if(params == null) params = {};
	function param_default(pname, def, o) {
		if (typeof params[pname] == "undefined") { o[pname] = def; }
		else { o[pname] = params[pname]; }
	};

	param_default("title", 		null, this);
	param_default("content",	null, this);

	param_default("width", 		"500px", this);
	param_default("height", 	null, this);
	param_default("close_button", true, this);
	param_default("modal", true, this);

	param_default("form",		null, this);
	param_default("buttons",	'', this);
	param_default("oButtons",	null, this);

	param_default("ajax_req",	null, this);
	param_default("onSuccess",	null, this);
	param_default("onFailure",	null, this);
	param_default("argument",	null, this);
	param_default("dosubmit",	false, this);
};

Window.prototype.handleCancel = function() {
	this.cancel();
}
Window.prototype.handleSubmit = function() {
	this.submit();
}
Window.prototype.show = function() {

	if(!this.form && this.ajax_req) {
		this.form = document.createElement('form');
		this.form.method = 'post';
		this.form.action = this.ajax_req;
	}
	if(wObjList[this.id] == undefined) {
		var params = { fixedcenter: true,
			visible: false,
			draggable: true,
			close: this.close_button,
			modal: this.modal,
			zIndex: 9999,
			constraintoviewport: true
		};
		if(this.width) params.width = this.width+(YAHOO.lang.isNumber(this.width)?'px':'');
		if(this.height) params.height = this.height+(YAHOO.lang.isNumber(this.height)?'px':'');

		if(this.form && !this.dosubmit) {

			wObjList[this.id] = new YAHOO.widget.Dialog("simpledialog_"+this.id, params);
		}else {

			wObjList[this.id] = new YAHOO.widget.Panel("simpledialog_"+this.id, params);
		}
	}

	new_div = document.createElement('div');
	if(this.form != null) new_div.appendChild(this.form);
	mainc = document.createElement('div');

	if(this.buttons && !this.oButtons) mainc.innerHTML = this.content + '<div class="align_right">' + this.buttons + '</div>';
	else mainc.innerHTML = this.content;
	if(this.form != null) this.form.appendChild(mainc);
	else new_div.appendChild(mainc);

	wObjList[this.id].setHeader(this.title);
	wObjList[this.id].setBody(new_div.innerHTML);
	if(this.oButtons) {
		wObjList[this.id].cfg.queueProperty("buttons", this.oButtons);
	}
	if(this.onSuccess) wObjList[this.id].callback.success = this.onSuccess;
	if(this.onFailure) wObjList[this.id].callback.failure = this.onFailure;
	if(this.argument) wObjList[this.id].callback.argument = this.argument;

	if(document.body.style.position == 'relative')
		document.body.style.position = 'static';

	wObjList[this.id].render(document.body);
	wObjList[this.id].show();
}


function callCloseHandler(name) {
	w=wObjList[name];
	if (w && w.onClose) {
		w.onClose();
	}
}

function destroyWindow(name) {

	if(wObjList[name]) wObjList[name].hide();
}

function destroyWindowNoEffect(name) {

	if(wObjList[name]) wObjList[name].hide();
}

function browserWidth() {
   if (self.innerWidth) {
	return self.innerWidth;
   } else if (document.documentElement && document.documentElement.clientWidth) {
	return document.documentElement.clientWidth;
   } else if (document.body) {
	return document.body.clientWidth;
   }
   return 630;
}

function browserHeight() {
   if (self.innerWidth) {
	return self.innerHeight;
   } else if (document.documentElement && document.documentElement.clientWidth) {
	return document.documentElement.clientHeight;
   } else if (document.body) {
	return document.body.clientHeight;
   }
   return 470;
}

function getTop() {
	if (window.innerHeight)
	{
		  pos = window.pageYOffset
	}
	else if (document.documentElement && document.documentElement.scrollTop)
	{
		pos = document.documentElement.scrollTop
	}
	else if (document.body)
	{
		  pos = document.body.scrollTop
	}


	return pos;

}

function showMsg(str) {

	if (wObjList['wMsg']!=null) {
		var w0=$('wMsg');
		var el=document.getElementsByClassName("w_content",w0.parentNode);
		el[0].innerHTML=str;
	} else {

			var name="wMsg";
			var title="";

   			var w=new Window();
			w.top=getTop();
			w.id=name;
			w.width=450;
			w.height=200;
			w.title=title;
			w.content=str;
			w.show();

	}

}