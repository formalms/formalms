

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