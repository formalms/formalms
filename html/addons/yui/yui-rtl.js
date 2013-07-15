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

YAHOO.widget.MenuBarRtl = function(p_oObject, p_oConfig) {
	p_oConfig.submenualignment = ['tr','br'];
    YAHOO.widget.MenuBarRtl.superclass.constructor.call(this, p_oObject, p_oConfig);

};

YAHOO.lang.extend(YAHOO.widget.MenuBarRtl, YAHOO.widget.MenuBar, {

	init: function(p_oElement, p_oConfig) {
		YAHOO.widget.MenuBarRtl.superclass.init.call(this, p_oElement, p_oConfig);

		this.subscribe('itemAdded', function (p_sType, p_aArgs) {
			var oMenuItem = p_aArgs[0];
			oMenuItem.parent.cfg.setProperty('submenualignment', ['tr','tl']);
		});
	},
	_onKeyDown: function(p_sType, p_aArgs, p_oMenuBar) {
		/*
		var oEvent = p_aArgs[0],
			oItem = p_aArgs[1],
			oSubmenu,
			oItemCfg,
			oNextItem;

		if(oItem && !oItem.cfg.getProperty("disabled")) {

			oItemCfg = oItem.cfg;
			switch(oEvent.keyCode) {

				case 37:    // Left arrow
				case 39:    // Right arrow

					if(oItem == this.activeItem && !oItemCfg.getProperty("selected")) {
						oItemCfg.setProperty("selected", true);
					} else {
						oNextItem = (oEvent.keyCode == 39) ?
							oItem.getPreviousEnabledSibling() :
							oItem.getNextEnabledSibling();
						if(oNextItem) {
							this.clearActiveItem();
							oNextItem.cfg.setProperty("selected", true);
							oSubmenu = oNextItem.cfg.getProperty("submenu");
							if(oSubmenu) {
								oSubmenu.show();
								oSubmenu.setInitialFocus();
							} else {
								oNextItem.focus();
							}
						}
					}
					Event.preventDefault(oEvent);
					return;
				break;
			}
		}*/
		YAHOO.widget.MenuBarRtl.superclass._onKeyDown.call(this, p_sType, p_aArgs, p_oMenuBar);
	}
});