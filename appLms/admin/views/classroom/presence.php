<?php Get::title(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	'index.php?r='.$base_link_classroom.'/classroom&id_course='.$model->getIdCourse() => Lang::t('_CLASSROOM', 'course'),
	Lang::t('_ATTENDANCE', 'course').' : '.$course_name
)); ?>
<div class="std_block">

<?php
	YuiLib::load();
	
	/* Inserimento pulsante per esportazione della lista in excel */
	echo	Form::openForm('excel_form', 'index.php?r='.$base_link_classroom.'/export&id_course='.$model->getIdCourse().'&amp;id_date='.$model->getIdDate())
			.Form::getButton('export', 'export', Lang::t('_EXPORT_XLS', 'report'))
			.Form::closeForm();

	echo	Form::openForm('presence_form', 'index.php?r='.$base_link_classroom.'/presence&id_course='.$model->getIdCourse().'&amp;id_date='.$model->getIdDate())
			.Form::openElementSpace()
			.($model->getTestType() == 1 ? Form::getTextfield(Lang::t('_MIN_SCORE', 'course'), 'score_min', 'score_min', 255, '') : '')
			.$model->getPresenceTable()
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('save', 'save', Lang::t('_SAVE', 'course'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'course'))
			.Form::closeElementSpace()
			.Form::closeForm();
?>

<script type="text/javascript">
var save = 0;
function addSave(e){save = 1;}
function toggleSave(e){save = 0;}
function controlMinScore(e){
	var score = YAHOO.util.Dom.get('score_min').value;
	if(score == '' && save == 1){
		YAHOO.util.Event.preventDefault(e);
		alert("<?php Lang::t('_MIN_SCORE_NOT_SET', 'course') ?>");
	}
}

YAHOO.util.Event.addListener("presence_form", "submit", controlMinScore);
YAHOO.util.Event.addListener("save-button", "click", addSave);
YAHOO.util.Event.addListener("undo-button", "click", toggleSave);

function checkAllDay(id_day)
{
	var days = YAHOO.util.Selector.query('input[id*=_' + id_day + '_]');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = true;
}

function unCheckAllDay(id_day)
{
	var days = YAHOO.util.Selector.query('input[id*=_' + id_day + '_]');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = false;
}

function checkAllUser(id_user)
{
	var days = YAHOO.util.Selector.query('input[id*=_' + id_user + ']');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = true;
}

function unCheckAllUser(id_user)
{
	var days = YAHOO.util.Selector.query('input[id*=_' + id_user + ']');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = false;
}
</script>

</div>