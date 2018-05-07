var answer_count = [];

function controlTotQuestion()
{
	var info = YAHOO.util.Dom.get('answer_info');

	if(tot_question == 0)
	{
		$("#show_result").attr('disabled', false);
		/*if(YAHOO.buttonObjects.show_result)
			YAHOO.buttonObjects.show_result.set('disabled', false);*/
		if(info)
			info.style.display = 'none';
	}
	else
	{
		$("#show_result").attr('disabled', true);
		/*if(YAHOO.buttonObjects.show_result)
			YAHOO.buttonObjects.show_result.set('disabled', true);*/
		if(info)
			info.style.display = 'block';
	}
}

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
		tot_question--;
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
		tot_question++;
	}
	answer_count[id_quest]--;
	controlTotQuestion();
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
		tot_question--;
		controlTotQuestion();
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
			tot_question--;
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
	tot_question--;
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	var answer = YAHOO.util.Selector.query('input[id^=quest_'+id_quest+'_]');
	for(var i=0; i < answer.length; i++)
		YAHOO.util.Event.purgeElement(answer[i]);
	controlTotQuestion();
}

function configureTextE()
{
	var entry_text = YAHOO.util.Dom.get('quest_'+this);
	YAHOO.util.Event.addListener(entry_text, "change", TextEChange);
}

function TextEChange(e)
{
	tot_question--;
	var tmp = this.id.split("_");
	var id_quest = tmp[1];
	var entry_text = YAHOO.util.Dom.get('quest_'+id_quest);
	YAHOO.util.Event.purgeElement(entry_text);
	controlTotQuestion();
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
		tot_question--;
		controlTotQuestion();
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
		tot_question--;
	}
	else if(control && answer_count[id_quest] == 0)
	{
		answer_count[id_quest]++;
		tot_question++;
	}
	controlTotQuestion();
}