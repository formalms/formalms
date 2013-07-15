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

var fw_d_server_url = '';
var opened_profile = 0;

function setup_directory(passed_server_url) {
	fw_d_server_url = passed_server_url;
}

function getUserProfile(id_user) {

	if(opened_profile != id_user) {

		var data = "op=getuserprofile&id_user=" + id_user;
		var objAjax = YAHOO.util.Connect.asyncRequest('POST', 'ajax.adm_server.php?plf=framework&mn=directory&'+data, {
	    	success: getUserProfile_complete
	    });
    }
}

function closeUserProfile(id_user) {
	
	if(id_user != 0) {
		
		var row  = YAHOO.util.Dom.get('user_row_' + id_user);
		row.parentNode.deleteRow( row.rowIndex );
		YAHOO.util.Dom.setStyle('pw_less_usersel_' + id_user, 'display', 'none');
		YAHOO.util.Dom.setStyle('pw_more_usersel_' + id_user, 'display', 'inline');
	}
	if(opened_profile == opened_profile) opened_profile = 0;
}

function getUserProfile_complete(objReq) {
	
	try {
		parsed = YAHOO.lang.JSON.parse(objReq.responseText);
	} catch (e) { return; }
	
	var row  = YAHOO.util.Dom.get('user_row_' + parsed.id_user);
	if(opened_profile != 0) closeUserProfile(opened_profile);
	
	var new_row 		= row.parentNode.insertRow(row.rowIndex);
	opened_profile 		= parsed.id_user;
	YAHOO.util.Dom.setStyle('pw_less_usersel_' + opened_profile, 'display', 'inline');
	YAHOO.util.Dom.setStyle('pw_more_usersel_' + opened_profile, 'display', 'none');
	
	var new_cell 		= new_row.insertCell(0);
	new_cell.colSpan 	= row.cells.length;
	new_cell.innerHTML 	= parsed.content;
	new_cell.focus();
} 
