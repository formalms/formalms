

var counter = 1;

function addFile()
{
	var hidden = YAHOO.util.Dom.get('file_number');
	var div = YAHOO.util.Dom.get('file');
	
	counter++;
	
	hidden.value = counter;
	
	var new_div = document.createElement('div');
	new_div.id = 'div_file_' + hidden.value;
	new_div.innerHTML = '<div class="form_line_l"><p>' + 
						'<label class="floating" for="file_1">' + _FILE_TO_SEND + '</label></p>' +
						'<input type="file" class="fileupload" id="file_' + counter + '" name="file_' + counter + '" value="" alt="' + _FILE_TO_SEND + '" /> ' +
						_MAX + ' <a href="#" onclick="delFile(\'' + counter + '\'); return false;"><span id="rem_span">' + _DEL + '</span><a></div>';
	
	div.appendChild(new_div);
}

function delFile(id_file)
{
	var field_to_rem = YAHOO.util.Dom.get('div_file_' + id_file);
	field_to_rem.parentNode.removeChild(field_to_rem);
}