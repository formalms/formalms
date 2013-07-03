<?php echo getTitleArea($title); ?>
<div class="std_block">
<?php
echo Form::openForm('assign_users_to_competence_form', $form_url);
echo Form::getHidden('id_competence', 'id_competence', $id_competence);
echo $table->getTable();
echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();
echo Form::closeForm();
?>
</div>
<script type="text/javascript">

YAHOO.util.Event.onDOMReady(function() {
	var
		D = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		S = YAHOO.util.Selector;


	E.addListener("set_score-button", "click", function(e) {
		E.stopPropagation(e);
		E.preventDefault(e);
		var i, inputs = S.query('input[id^=assign_score_]'), value = parseInt(D.get("_score_").value);
		for (i=0; i<inputs.length; i++) {
			inputs[i].value = value;
		}
	});

	E.addListener("reset_score-button", "click", function(e) {
		E.stopPropagation(e);
		E.preventDefault(e);
		var i, inputs = S.query('input[id^=assign_score_]');
		for (i=0; i<inputs.length; i++) {
			inputs[i].value = "<?php echo (int)$score_std_value; ?>";
		}
	});

});
</script>