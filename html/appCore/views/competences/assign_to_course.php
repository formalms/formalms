<?php echo getTitleArea($title_arr); ?>
<div class="std_block">
<?php
echo Form::openForm('course_sel_competences_form', 'index.php?r='.$base_link_competence.'/assign_to_course');
echo Form::getHidden('id_course', 'id_course', $id_course);
echo Form::getHidden('is_updating', 'is_updating', 1);

$this->widget('competenceselector', array(
	'id' => 'course_competences_selector',
	'selected_category' => 0,
	'filter_text' => "",
	'show_descendants' => false,
	'selection' => $selection
));

echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();
echo Form::closeForm();

?>
</div>