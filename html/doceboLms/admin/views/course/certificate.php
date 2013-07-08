<?php

echo getTitleArea(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course').' : '.$course_name
));

?>
<div class="std_block">
<?php
	echo	Form::openForm('certificate_form', 'index.php?r='.$base_link_course.'/certificate')
			.Form::getHidden('id_course', 'id_course', $id_course)
			.Form::getTextfield(Lang::t('_EX_CERT_POINT_REQUIRED', 'certificate'), 'point_required', 'point_required', 255, $point_required)
			.$tb->getTable()
			.Form::openButtonSpace()
			.Form::getButton('assign', 'assign', Lang::t('_SAVE'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO'))
			.Form::closeButtonSpace()
			.Form::closeForm();
?>
</div>