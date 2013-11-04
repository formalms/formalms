<?php echo getTitleArea($title_arr); ?>
<div class="std_block">
<?php

echo Form::openForm('sel_assign_score_form', 'index.php?r=adm/competences/mod_course_competences_action');

echo Form::getHidden('id_course', 'id_course', (int)$id_course);

echo $table->getTable();

echo Form::openbuttonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO'));
echo Form::closeButtonSpace();

echo Form::closeForm();

?>
</div>
<script type="text/javascript">
YAHOO.util.Event.addListener("set_score-button", "click", function(e) {
	var i, list = YAHOO.util.Selector.query('input[id^=score_assigned_]'), value = YAHOO.util.Dom.get("score_assigned").value;
	for (i=0; i<list.length; i++) {
		list[i].value = value;
	}
});

YAHOO.util.Event.addListener("reset_score-button", "click", function(e) {
	YAHOO.util.Dom.get("score_assigned").value = '0';
	var i, list = YAHOO.util.Selector.query('input[id^=score_assigned_]');
	for (i=0; i<list.length; i++) {
		list[i].value = '0';
	}
});
</script>