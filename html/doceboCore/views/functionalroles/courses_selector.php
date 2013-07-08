<?php echo getTitleArea($title_arr); ?>
<div class="std_block">
<?php
echo Form::openForm('fncrole_sel_courses_form', 'index.php?r=adm/functionalroles/sel_courses');
echo Form::getHidden('id_fncrole', 'id_fncrole', $id_fncrole);
echo Form::getHidden('is_updating', 'is_updating', 1);

echo Form::openElementSpace();

echo $courses_selector->loadCourseSelector(true);

echo Form::closeElementSpace();
echo Form::openButtonSpace();
echo Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();
echo Form::closeForm();

?>
</div>