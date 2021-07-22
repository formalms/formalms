<?php echo getTitleArea(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	Lang::t('_ASSIGN_MENU', 'course').' : '.$course_name
)); ?>
<div class="std_block">
<?php
	echo	Form::openForm('certificate_form', 'index.php?r='.$base_link_course.'/menu')
			.Form::getHidden('id_course', 'id_course', $id_course)
			.Form::openElementSpace()
			.Form::getDropdown(Lang::t('_COURSE_MENU_TO_ASSIGN', 'course'), 'selected_menu', 'selected_menu', $menu_custom, $sel_custom)
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.Form::getButton('assign', 'assign', Lang::t('_SAVE'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm();
?>
</div>