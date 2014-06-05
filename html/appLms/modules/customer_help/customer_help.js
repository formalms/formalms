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
                /** Client info **/
                
                flash_installed = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));
                if (flash_installed){
                    var version = getFlashVersion(); 
                    var vArr = version.split(',');
                    flash_installed = vArr[0];
                }
                screen_size = getScreenSize();
                document.getElementById('help_req_resolution').value = 'width: '+screen_size.width+' height: '+screen_size.height;
                document.getElementById('help_req_flash_installed').value = flash_installed;
			}
		},
		failure: function() {},
		scope: dialog
	}, '');
    
    var getScreenSize = function() {
        var mql = window.matchMedia("(orientation: portrait)");

        // If there are matches, we're in portrait
        if(mql.matches) {  
          // Portrait orientation
         return {width: screen.width, height: screen.height};
        } else {  
          // Landscape orientation
          return {width: screen.height, height: screen.width};
        }        
        
    }
    
    var getFlashVersion = function() {
        try {
            try {
                var axo = new ActiveXObject('ShockwaveFlash.ShockwaveFlash.6');
                try {
                    axo.AllowScriptAccess = 'always';
                }
                catch (e) {
                    return '6,0,0';
                }
            }

            catch (e) {
            }

            return new ActiveXObject('ShockwaveFlash.ShockwaveFlash').GetVariable('$version').replace(/\D+/g, ',').match(/^,?(.+),?$/)[1];

        } catch (e) {
            try {
                if (navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin) {
                    return (navigator.plugins["Shockwave Flash 2.0"] || navigator.plugins["Shockwave Flash"]).description.replace(/\D+/g, ",").match(/^,?(.+),?$/)[1];
                }
            } catch (e) {
            }
        }
        return '0,0,0';
    }

}

//YAHOO.util.Event.addListener("customer_help", "click", CustomerHelpShowPopUp);