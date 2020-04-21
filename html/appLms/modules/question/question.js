var answer_count = [];


function configureMultiC()
{
	var answer = YAHOO.util.Selector.query('input[id^=quest_'+this+'_]');
	answer_count[this] = 0;
	for(var i=0; i < answer.length; i++)
	{
		if(answer[i].checked)
		{
			answer_count[this]++;
			YAHOO.util.Event.addListener(answer[i], "click", multiCSel);
		}
		else
			YAHOO.util.Event.addListener(answer[i], "click", multiCNotSel);
	}
	if(answer_count[this] == num_answer_control['_'+this])
	{
		var answer = YAHOO.util.Selector.query('input[id^=quest_'+this+'_]');
		for(var i=0; i < answer.length; i++)
		{
			if(!answer[i].checked)
				answer[i].disabled = true;
		}
	}
}

function multiCSel(e)
{
	YAHOO.util.Event.purgeElement(this);
	YAHOO.util.Event.addListener(this, "click", multiCNotSel);
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	if(answer_count[id_quest] == num_answer_control['_'+id_quest])
	{
		var answer = YAHOO.util.Selector.query('input[id^=quest_'+id_quest+'_]');
		for(var i=0; i < answer.length; i++)
		{
			if(!answer[i].checked)
				answer[i].disabled = false;
		}

	}
	answer_count[id_quest]--;
}

function multiCNotSel(e)
{
	YAHOO.util.Event.purgeElement(this);
	YAHOO.util.Event.addListener(this, "click", multiCSel);
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	answer_count[id_quest]++;
	if(answer_count[id_quest] == num_answer_control['_'+id_quest])
	{
		var answer = YAHOO.util.Selector.query('input[id^=quest_'+id_quest+'_]');
		for(var i=0; i < answer.length; i++)
		{
			if(!answer[i].checked)
				answer[i].disabled = true;
		}
	}
}

function configureSingleC()
{
	var answer = YAHOO.util.Selector.query('input[id^=quest_'+this+'_]');
	var to_purge = false;
	for(var i=0; i < answer.length; i++)
	{
		if(answer[i].checked)
		{
			to_purge = true;
		}
		else
			YAHOO.util.Event.addListener(answer[i], "click", singleCNotSel);
	}
	if(to_purge)
		for(var i=0; i < answer.length; i++)
			YAHOO.util.Event.purgeElement(answer[i]);
}

function singleCNotSel(e)
{
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	var answer = YAHOO.util.Selector.query('input[id^=quest_'+id_quest+'_]');
	for(var i=0; i < answer.length; i++)
		YAHOO.util.Event.purgeElement(answer[i]);
}

function configureTextE()
{
	var entry_text = YAHOO.util.Dom.get('quest_'+this);
	YAHOO.util.Event.addListener(entry_text, "change", TextEChange);
}

function TextEChange(e)
{
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	var entry_text = YAHOO.util.Dom.get('quest_'+id_quest);
	YAHOO.util.Event.purgeElement(entry_text);
}

function configureAss()
{
	var answer = YAHOO.util.Selector.query('select[id^=quest_'+this+'_]');
	answer_count[this] = 1;
	var control = false;
	for(var i=0; i < answer.length; i++)
	{
		if(answer[i].value == 0)
			control = true;
		YAHOO.util.Event.addListener(answer[i], "change", assControl);
	}
	if(!control)
	{
		answer_count[this]--;
	}
}

function assControl(e)
{
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	var control = false;
	var answer = YAHOO.util.Selector.query('select[id^=quest_'+id_quest+'_]');
	for(var i=0; i < answer.length; i++)
		if(answer[i].value == 0)
			control = true;
	if(!control && answer_count[id_quest] == 1)
	{
		answer_count[id_quest]--;
	}
	else if(control && answer_count[id_quest] == 0)
	{
		answer_count[id_quest]++;
	}
}