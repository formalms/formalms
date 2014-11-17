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

//utils functions
function addSlashes(str) {
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\0/g,'\\0');
	return str;
}

function stripSlashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\\\/g,'\\');
	str=str.replace(/\\0/g,'\0');
	return str;
}

//check if an ancestor has a given style
function checkClassDomain(el, className) {
	if (YAHOO.util.Dom.hasClass(el, className)) {
		return true;
	} else {
		if (YAHOO.util.Dom.getAncestorByClassName(el, className)) return true;
	}
	return false;
}

/**
 * This function set all the checkbox of a specified form
 * @param 	string	form_name	the name of the form
 * @param 	string	check_name	the name of the checkbox i.e. check_name[34]
 * @param 	int		assign		the value to assign, if omitted the checbox value is inverted
 */
function checkall( form_name, check_name, assign ) {
	var form = document.forms[form_name];
	for (var i = 0; i < form.elements.length; i++) {
		if( form.elements[i].name.indexOf( check_name + "[" ) >= 0 )
			if( arguments.length > 2 )
				form.elements[i].checked = assign;
			else
				form.elements[i].checked = !form.elements[i].checked;
	}
}

/**
 * This function set all the checkbox of a specified form
 * @param 	string	form_name	the name of the form
 * @param 	string	check_name	the name of the checkbox from the end of the name i.e. [34]
 * @param 	int		assign		the value to assign, if omitted the checbox value is inverted
 */
function checkall_fromback( form_name, check_name, assign ) {
	var form = document.forms[form_name];
	for (var i = 0; i < form.elements.length; i++) {
		if( form.elements[i].name.indexOf( "]" + check_name  ) >= 0 )
			if( arguments.length > 2 )
				form.elements[i].checked = assign;
			else
				form.elements[i].checked = !form.elements[i].checked;
	}
}


function checkall_meta( form_name, check_name, assign ) {
	var form = document.forms[form_name];
	for (var i = 0; i < form.elements.length; i++) {
		if( form.elements[i].name.indexOf( "_" + check_name + "_" ) >= 0 )
			if( arguments.length > 2 )
				form.elements[i].checked = assign;
			else
				form.elements[i].checked = !form.elements[i].checked;
	}
}

function checkall_fromback_meta( form_name, check_name, assign ) {
	var form = document.forms[form_name];
	for (var i = 0; i < form.elements.length; i++) {
		if( form.elements[i].name.indexOf( "_" + check_name + "_"  ) >= 0 )
			if( arguments.length > 2 )
				form.elements[i].checked = assign;
			else
				form.elements[i].checked = !form.elements[i].checked;
	}
}

//client side text keys manager, text keys provided by server during initialization
LanguageManager = function(langs) {
	this.set(langs);
}

LanguageManager.prototype = {
	_oKeys: {},
	set: function(langs) {
		if (langs) this._oKeys = langs;
		if(gLangs) gLangs._oKeys = langs;
	},
	get: function(textKey) {
		if (this._oKeys && this._oKeys[textKey]) return this._oKeys[textKey]; else return textKey;
	}
}
var gLangs = new LanguageManager({});

function stringify(o) {
	var temp = '{';
	var fields = [];
	var def;
	for (x in o) {
		try {
			def = o[x].toString();
		} catch(e) {
			def = '[not valid]';
		}
		fields.push(x+': '+def);
	}
	temp += fields.join(', ');
	temp += '}';
	return temp;
}

LightBox = function() {}
LightBox.prototype = {
	back_url: false,
	overlay_light: null,
	max_width: false,
	max_height: false,
    oLangs: new LanguageManager(),
	init: function(oConfig) {
        if (typeof oConfig !== 'undefined'){
                this.oLangs.set(oConfig.langs || {_CLOSE: 'Close'});
        }
		if(this.overlay_light == null) {
			if  (YAHOO.env.ua.ipad == 0 ||  typeof YAHOO.env.ua.ipad == "undefined" || !YAHOO.env.ua.ipad) {
			this.overlay_light = new YAHOO.widget.Panel("overlay_lightbox", {
				xy:[5,5],
				visible:false,
				modal: true,
				draggable: false,
				close: false,
				zIndex:9000,
				width:(YAHOO.util.Dom.getViewportWidth()-30)+'px',
				height:(YAHOO.util.Dom.getViewportHeight()-16)+'px',
				effect:[{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.5}]
			} );
			} else {
				this.overlay_light = new YAHOO.widget.Panel("overlay_lightbox", {
					xy:[5,5],
					visible:false,
					modal: true,
					draggable: false,
					close: false,
					zIndex:9000,
					width:(YAHOO.util.Dom.getViewportWidth()-30+62)+'px',
					height:(YAHOO.util.Dom.getViewportHeight()-16+32)+'px',
					effect:[{effect:YAHOO.widget.ContainerEffect.FADE,duration:0.5}]
				} );
			}

			//this.overlay_light.setHeader('<h1 class="title_handler" id="title_handler">Title</h1>'
			this.overlay_light.setHeader(''
				+'<a class="close_handler" id="close_handler" href="#"><span>'+this.oLangs.get('_CLOSE')+'</span></a>');
			this.overlay_light.setBody('<iframe id="overlay_iframe" name="overlay_iframe" src="" height="100%" width="100%" frameborder="0"></iframe>');

			YAHOO.util.Event.on(window, "resize", function(e, obj) {

				if  (YAHOO.env.ua.ipad == 0 ||  typeof YAHOO.env.ua.ipad == "undefined" || !YAHOO.env.ua.ipad) {
					var new_w = (YAHOO.util.Dom.getViewportWidth()-16), new_h = (YAHOO.util.Dom.getViewportHeight()-16);
				} else {
					if (YAHOO.util.Dom.getViewportWidth() == 980){
						var new_w = (YAHOO.util.Dom.getViewportWidth()-16+48), new_h = (YAHOO.util.Dom.getViewportHeight()-16+32);
					} else {
						var new_w = (YAHOO.util.Dom.getViewportWidth()-16), new_h = (YAHOO.util.Dom.getViewportHeight()-16);
					}
				}
				obj.overlay_light.cfg.setProperty("width", ( obj.max_width != false && new_w > obj.max_width ? obj.max_width : new_w ) + "px");
				obj.overlay_light.cfg.setProperty("height", ( obj.max_height != false && new_h > obj.max_height ? obj.max_height : new_h ) + "px");
				obj.overlay_light.center();
			}, this );
			YAHOO.util.Event.on(window, "scroll", function(e, obj) {
				obj.overlay_light.center();
			}, this );

			this.overlay_light.render(document.body);

			YAHOO.util.Event.addListener("close_handler", "click", function(e, obj) {

				YAHOO.util.Event.preventDefault(e);
				window.onbeforeunload = null;
				try {
					window.frames['overlay_iframe'].uiPlayer.closePlayer(true, window);
				} catch(e) {
					window.overlay_iframe.uiPlayer.closePlayer(true, window);
				}
			}, this);

		}
		var nodes = YAHOO.util.Selector.query('a[rel^=lightbox]');
		YAHOO.util.Event.addListener(nodes, "click", function(e, obj) {
			YAHOO.util.Event.preventDefault(e);

			var arguments = this.rel.split(';');
			//search for width and height
			for(var i=0;i < arguments.length;i++) {
				var params = arguments[i].split('=');
				switch(params[0]) {
					case "width": {
						obj.max_width = params[1];
						obj.overlay_light.cfg.setProperty("width", params[1] + "px");
					};break;
					case "height": {
						obj.max_height = params[1];
						obj.overlay_light.cfg.setProperty("height", params[1] + "px");
					};break;
				}
				obj.overlay_light.center();
			}
			obj.overlay_light.show();
			window.onbeforeunload = function() { return "Exit ? Your progress will be saved."; } //'. Lang::t('_CONFIRM').'
			YAHOO.util.Dom.get('overlay_iframe').src = this.href;
			YAHOO.util.Dom.get('title_handler').innerHTML = ( this.title ? this.title : this.innerHTML );
		}, this );

	}
}