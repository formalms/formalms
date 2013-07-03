if (!YAHOO.dateInput) {
	YAHOO.namespace("competences_categories_selector");
	YAHOO.dateInput = {
		dateFormat: "'.$date_format.'",
		setCalendar: function(id, oConfig) {

			var categorySelect = function(t, args) {
				var date = args[0][0];
				YAHOO.util.Dom.get(this.id).value = getLocalDate(date[0], date[1], date[2]);
				this.container.hide();
			};


			var elSpan = document.createElement("SPAN");
			elSpan.id = "calendar_button_"+id;
			elSpan.className = "yui-button";
			elSpan.innerHTML = '<span class="first-child docebo_calendar"><button type="button"></button></span>';

			var elDiv = document.createElement("DIV");
			elDiv.id = "calendar_menu_"+id;
			elDiv.innerHTML = '<div id="calendar_container_'+id+'"></div>';

			insertAfter(elDiv, id);
			insertAfter(elSpan, id);

			var oMenu = new YAHOO.widget.Overlay("selector_menu_"+id, {visible: false});
			var oButton = new YAHOO.widget.Button("selector_button_"+id, {
				label: "   ",
				type: "menu",
				menu: oMenu
			});
			

		}
	};
}