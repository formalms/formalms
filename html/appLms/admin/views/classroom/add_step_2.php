<?php Get::title(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	'index.php?r='.$base_link_classroom.'/classroom&id_course='.$model->getIdCourse() => Lang::t('_CLASSROOM', 'course'),
	Lang::t('_ADD', 'course')
)); ?>
<div class="std_block">

<script type="text/javascript">

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

function changePBeginHours()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('pb_hours').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('pb_hours_' + i).selectedIndex = selected;
}

function changePBeginMinutes()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('pb_minutes').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('pb_minutes_' + i).selectedIndex = selected;
}

function changePEndHours()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('pe_hours').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('pe_hours_' + i).selectedIndex = selected;
}

function changePEndMinutes()
{
	i = 0;

	var selected = YAHOO.util.Dom.get('pe_minutes').selectedIndex;

	for(i; i < num_day;i++)
		YAHOO.util.Dom.get('pe_minutes_' + i).selectedIndex = selected;
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

</script>

<?php
	$data = $model->getDateInfoFromPost();

	echo	Form::openForm('add_date_2', 'index.php?r='.$base_link_classroom.'/addclassroom&amp;id_course='.$model->getIdCourse())
			.Form::getHidden('step', 'step', '3')
			.Form::getHidden('code', 'code', stripslashes($data['code']))
			.Form::getHidden('name', 'name', stripslashes($data['name']))
			.Form::getHidden('max_par', 'max_par', $data['max_par'])
			.Form::getHidden('price', 'price', $data['price'])
			.Form::getHidden('overbooking', 'overbooking', $data['overbooking'])
			.Form::getHidden('test', 'test', $data['test'])
			.Form::getHidden('status', 'status', $data['status'])
			.Form::getHidden('date_selected', 'date_selected', $data['date_selected'])
			.Form::getHidden('description', 'description', stripslashes($data['description']))
			.Form::getHidden('medium_time', 'medium_time', $data['medium_time'])
			.Form::getHidden('sub_start_date', 'sub_start_date', $data['sub_start_date'])
			.Form::getHidden('sub_end_date', 'sub_end_date', $data['sub_end_date'])
			.Form::getHidden('unsubscribe_date_limit', 'unsubscribe_date_limit', $data['unsubscribe_date_limit'])
			.Form::openElementSpace()
			.$model->getDayTable($data['array_day'])
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('back', 'back', Lang::t('_BACK', 'course'))
			.Form::getButton('save', 'save', Lang::t('_SAVE', 'course'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'course'))
			.Form::closeElementSpace()
			.Form::closeForm();
?>

</div>