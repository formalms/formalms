<?php echo getTitleArea(Lang::t('_ATTENDANCE', 'admin_date')) ?>
<div class="std_block">
	<?php echo	Form::openForm('presence_form', 'index.php?r=lms/presence/presence')
				.Form::openElementSpace()
				.Form::getDropdown(Lang::t('_SELECT_EDITION', 'admin_date'), 'id_date', 'id_date', $date_for_dropdown, $model->getIdDate())
				.Form::closeElementSpace()
				.Form::openElementSpace()
				.($test_type == 1 ? Form::getTextfield(Lang::t('_MIN_SCORE', 'admin_date'), 'score_min', 'score_min', 255, '') : '')
				.$tb
				.Form::closeElementSpace()
				.Form::openButtonSpace()
				.Form::getButton('save', 'save', Lang::t('_SAVE', 'admin_date'))
				.Form::closeElementSpace()
				.Form::closeForm(); ?>
</div>

<script type="text/javascript">
	var _MIN_SCORE_NOT_SET = "'.$lang->def('_MIN_SCORE_NOT_SET').'";
	YAHOO.util.Event.addListener("save", "click", controlMinScore);
	YAHOO.util.Event.addListener("id_date", "change", formSubmit);

	function formSubmit()
	{
		var form = YAHOO.util.Dom.get("presence_form");
		form.submit();
	}
	function controlMinScore()
	{
		var score = YAHOO.util.Dom.get('score_min').value;

		if(score == '')
		{
			YAHOO.util.Event.preventDefault(e);
			alert('<?php echo Lang::t('_MIN_SCORE_NOT_SET', 'admin_date') ?>');
		}
	}
</script>