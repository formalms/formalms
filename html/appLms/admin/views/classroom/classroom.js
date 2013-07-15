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

function numberWithZero(n)
{
	if(n > 9)
		return n;
	else
		return '0' + n;
}

var i;

function changeBeginHours()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('b_hours').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('b_hours_' + i).selectedIndex = selected;
}

function changeBeginMinutes()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('b_minutes').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('b_minutes_' + i).selectedIndex = selected;
}

function changeEndHours()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('e_hours').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('e_hours_' + i).selectedIndex = selected;
}

function changeEndMinutes()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('e_minutes').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('e_minutes_' + i).selectedIndex = selected;
}

function changeClassroom()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('classroom').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('classroom_' + i).selectedIndex = selected;
}

function controlMinScore(e)
{
	var score = YAHOO.util.Dom.get('score_min').value;

	if(score == '')
	{
		YAHOO.util.Event.preventDefault(e);
		alert(_MIN_SCORE_NOT_SET);
	}
}

function formSubmit()
{
	var form = YAHOO.util.Dom.get("presence_form");
	form.submit();
}