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

var onClick = function(e)
{
	if(this.checked)
	{
		for(i = 0; i < unchecked.length; i++)
			if(unchecked[i] == this.id)
				unchecked.splice(i, 1);
		
		num_checked++;
	}
	else
	{
		unchecked.push(this.id);
		num_checked--;
	}
	
	if(num_checked == max_checked)
	{
		for(i = 0; i < unchecked.length; i++)
		{
			var element = YAHOO.util.Dom.get(unchecked[i]);
			element.disabled = true;
		}
	}
	else
	{
		for(i = 0; i < unchecked.length; i++)
		{
			var element = YAHOO.util.Dom.get(unchecked[i]);
			element.disabled = false;
		}
	}
}

function controlChecked()
{
	if(num_checked == max_checked)
	{
		for(i = 0; i < unchecked.length; i++)
		{
			var element = YAHOO.util.Dom.get(unchecked[i]);
			element.disabled = true;
		}
	}
}