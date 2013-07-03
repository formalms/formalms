<?php Get::title(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	'index.php?r='.$base_link_classroom.'/classroom&id_course='.$model->getIdCourse() => Lang::t('_CLASSROOM', 'course'),
	Lang::t('_MOD', 'course').': '.$date_info['name'].' ('.Format::date($date_info['date_begin']).')'
));
if(isset($err_avail))
	echo UIFeedback::error(Lang::t($err_avail, 'course'));
?>
<div class="std_block">

<?php

echo	Form::openForm('add_date', 'index.php?r='.$base_link_classroom.'/modclassroom&amp;id_course='.$model->getIdCourse().'&amp;id_date='.$model->getIdDate())
		.Form::getHidden('step', 'step', '2')
		.Form::openElementSpace()
		.Form::getTextfield(Lang::t('_CODE', 'course'), 'code', 'code', 255, (isset($date_info['code']) ? $date_info['code'] : $_POST['code']))
		.Form::getTextfield(Lang::t('_NAME', 'course'), 'name', 'name', 255, (isset($date_info['name']) ? $date_info['name'] : $_POST['name']))
		.Form::getTextarea(Lang::t('_DESCRIPTION', 'course'), 'description', 'description', (isset($date_info['description']) ? stripslashes($date_info['description']) : stripslashes(stripslashes($_POST['description']))))
		.Form::getTextfield(Lang::t('_MEDIUM_TIME', 'course'), 'medium_time', 'medium_time', 255, (isset($date_info['medium_time']) ? $date_info['medium_time'] : $_POST['medium_time']))
		.Form::getTextfield(Lang::t('_MAX_NUM_SUBSCRIBE', 'course'), 'max_par', 'max_par', 255, (isset($date_info['max_par']) ? $date_info['max_par'] : $_POST['max_par']))
		.Form::getTextfield(Lang::t('_COURSE_PRIZE', 'course'), 'price', 'price', 255, (isset($date_info['price']) ? $date_info['price'] : $_POST['price']))
		.Form::getDropdown(Lang::t('_STATUS', 'course'), 'status', 'status', $model->getStatusForDropdown(), (isset($date_info['status']) ? $date_info['status'] : $_POST['status']))
		.Form::getDropdown(Lang::t('_FINAL_SCORE', 'course'), 'test', 'test', $model->getTestTypeForDropdown(), (isset($date_info['test_type']) ? $date_info['test_type'] : $_POST['test']))
		.'<div class="form_line_l">'
		.'<p><label for="overbooking" class="floating">'.Lang::t('_ALLOW_OVERBOOKING', 'course').'</label></p>'
		.Form::getInputCheckbox('overbooking', 'overbooking', 1, ((isset($date_info['overbooking']) && $date_info['overbooking'] == 1) || (isset($_POST['overbooking']) && $_POST['overbooking'] == 1) ? true : false), false)
		.'</div>'
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course'), 'sub_start_date', 'sub_start_date', (isset($date_info['sub_start_date']) ? ($date_info['sub_start_date'] === '0000-00-00' ? '' : Format::date($date_info['sub_start_date'], 'date')) : ($_POST['sub_start_date'] === '00-00-0000' ? '' : $_POST['sub_start_date'])))
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_END', 'course'), 'sub_end_date', 'sub_end_date', (isset($date_info['sub_end_date']) ? ($date_info['sub_end_date'] === '0000-00-00' ? '' : Format::date($date_info['sub_end_date'], 'date')) : ($_POST['sub_end_date'] === '00-00-0000' ? '' : $_POST['sub_end_date'])))
		.Form::getDatefield(Lang::t('_UNSUBSCRIBE_DATE_LIMIT', 'course'), 'unsubscribe_date_limit', 'unsubscribe_date_limit', (isset($date_info['unsubscribe_date_limit']) ? ($date_info['unsubscribe_date_limit'] === '0000-00-00' ? '' : Format::date($date_info['unsubscribe_date_limit'], 'date')) : ($_POST['unsubscribe_date_limit'] === '00-00-0000' ? '' : $_POST['unsubscribe_date_limit'])))
		.'<div id="date_container">';

?>

<div id="calendar_container" class="form_line_l"></div>

<?php

$date_string = '';
$start_mounth = '';

if(isset($_POST['date_selected']) && !empty($_POST['date_selected']))
{
	$array_day = explode(',', $_POST['date_selected']);

	$first = true;
	if(count($array_day) > 0)
	{
		for($i = 0; $i < count($array_day); $i++)
			if($first)
			{
				$first = false;
				$start_mounth = (int)substr($array_day[$i], 5, 2).'/'.substr($array_day[$i], 0, 4);
				$date_string .= (int)substr($array_day[$i], 5, 2).'/'.(int)substr($array_day[$i], 8, 2).'/'.substr($array_day[$i], 0, 4);
			}
			else
				$date_string .= ','.(int)substr($array_day[$i], 5, 2).'/'.(int)substr($array_day[$i], 8, 2).'/'.substr($array_day[$i], 0, 4);
	}
}
else
	$date_string = $model->getDateString();
?>
<script type="text/javascript">
function numberWithZero(n)
{
	if(n > 9)
		return n;
	else
		return '0' + n;
}

YAHOO.util.Event.onDOMReady(function()
{
	var calendar = new YAHOO.widget.CalendarGroup("calendar", "calendar_container", {
		MDY_YEAR_POSITION: 3,
		MD_DAY_POSITION: 1,
		MD_MONTH_POSITION: 2,
		start_weekday: 1,
		PAGES: 3,
		MULTI_SELECT: true
		<?php if ($start_mounth != '') echo ', pagedate: "'.$start_mounth.'"'; ?>
	});

	<?php if ($date_string != '') echo 'calendar.cfg.setProperty("selected", "'.$date_string.'", false);'; ?>

	calendar.render();
	YAHOO.util.Event.on("add_date", "submit", function() {
		var arrDates = calendar.getSelectedDates();
		var date_string = "";
		var first = true;
		for (var i = 0; i < arrDates.length; ++i)
			if (first) {
				first = false;
				date_string += arrDates[i].getFullYear() + "-" + numberWithZero((arrDates[i].getMonth() + 1))  + "-" +  numberWithZero(arrDates[i].getDate());
			} else
				date_string += "," + arrDates[i].getFullYear() + "-" + numberWithZero((arrDates[i].getMonth() + 1))  + "-" +  numberWithZero(arrDates[i].getDate());
		YAHOO.util.Dom.get("date_selected").value = date_string;
	});
});
</script>
<?php
echo	'</div>'
		.Form::getHidden('date_selected', 'date_selected', '')
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('next', 'next', Lang::t('_NEXT', 'course'))
		.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'course'))
		.Form::closeElementSpace()
		.Form::closeForm();

?>

</div>