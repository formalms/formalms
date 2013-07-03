
var prev_sel_emoticon =false;

YAHOO.util.Event.onContentReady("emoticon_menu_box", function () {

	//	Create a Button using an existing <input> and <select> element
	var oEmoticonButton = new YAHOO.widget.Button("emoticon_btn",
	{
		type: "menu",
		menu: "emoticons_menu"
	});


	//	"click" event listener for the second Button's Menu instance
	var onMenuClick = function (p_sType, p_aArgs) {

		var oEvent = p_aArgs[0],	//	DOM event

		oMenuItem = p_aArgs[1];	//	MenuItem instance that was the
		//	target of the event

		if (oMenuItem) {
			YAHOO.log("[MenuItem Properties] text: " +
				oMenuItem.cfg.getProperty("text") + ", value: " +
				oMenuItem.value);
			//alert(oMenuItem.index);
			var sel =YAHOO.util.Dom.get('emoticons');
			var btn =YAHOO.util.Dom.get('emoticon_btn-button');
			sel.selectedIndex =oMenuItem.index;
			btn.innerHTML ='<span></span>'+oMenuItem.cfg.getProperty("text");

			if (prev_sel_emoticon) {
				YAHOO.util.Dom.removeClass(btn.parentNode, prev_sel_emoticon);
			}
			YAHOO.util.Dom.addClass(btn.parentNode, 'emo-'+oMenuItem.value);			
			prev_sel_emoticon ='emo-'+oMenuItem.value;
		}

	};

	//	Add a "click" event listener for the Button's Menu
	oEmoticonButton.getMenu().subscribe("click", onMenuClick);
	oEmoticonButton.getMenu().subscribe("render", emoticonMenuRender);

	// Init button
	var sel =YAHOO.util.Dom.get('emoticons');
	var btn =YAHOO.util.Dom.get('emoticon_btn-button');
	YAHOO.util.Dom.addClass(btn.parentNode, 'emoticon');
	if (sel.value != '') {
		btn.innerHTML ='<span></span>'+btn.innerHTML;
		YAHOO.util.Dom.addClass(btn.parentNode, 'emo-'+sel.value);
		prev_sel_emoticon ='emo-'+sel.value;
	}

});


function emoticonMenuRender(e) {
	var nodes = YAHOO.util.Selector.query('#emoticon_menu_box ul li a');
	YAHOO.util.Dom.addClass(nodes, 'emoticon');	

	for (i in nodes) {
		var sel =YAHOO.util.Dom.get('emoticons');
		var val =sel.options[i].value;
		nodes[i].innerHTML ='<span>'+'</span>'+nodes[i].innerHTML;
		YAHOO.util.Dom.addClass(nodes[i], 'emo-'+val);
	}
}