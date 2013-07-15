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

var menu_obj = new Array();
var menu_id = new Array();

function expand_menu(a_open, css_close_class, css_open_class) {
	
	for(var i = 0; i < menu_obj.length; i++) {
		
		if(menu_obj[i].id == a_open && menu_obj[i].style.display == 'none') {
		
			menu_obj[i].parentNode.className = css_open_class;
			
			//Effect.BlindDown(menu_obj[i], {duration: 0.7});
			YAHOO.Animation.BlindIn(menu_obj[i]);
		} else if(menu_obj[i].style.display != 'none' && menu_obj[i].id != a_open) {
		
			menu_obj[i].parentNode.className = '';
			//Effect.BlindUp(menu_obj[i], {duration: 0.7});
			YAHOO.Animation.BlindOut(menu_obj[i]);
		}
	}
	return false;
}

function setMenuList(array_list) {
	
	menu_id = array_list;
	for(var i = 0; i < array_list.length; i++) {
		
		var menu = document.getElementById(array_list[i]);
		if(menu != null) menu_obj[i] = menu;
	}
}
