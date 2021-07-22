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

function _getAllCheckBoxes() {
	return YAHOO.util.Selector.query('input[id^=mail_]');
}

function selectAll() {
	var sel = _getAllCheckBoxes();
	for (var i=0; i<sel.length; i++) {
		sel[i].checked=true;
	}
}

function unselectAll() {
	var sel = _getAllCheckBoxes();
	for (var i=0; i<sel.length; i++) {
		sel[i].checked=false;
	}
}