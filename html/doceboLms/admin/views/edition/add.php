<br />

<?php
$title = array(	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
				'index.php?r='.$base_link_edition.'/show&id_course='.$model->getIdCourse() => Lang::t('_EDITIONS', 'course'),
				Lang::t('_ADD', 'course'));

echo getTitleArea($title);
?>

<div class="std_block">

<?php
echo	Form::openForm('add_edition_form', 'index.php?r='.$base_link_edition.'/add&amp;id_course='.$model->getIdCourse())
		.Form::openElementSpace()
		.Form::getTextfield(Lang::t('_CODE', 'course'), 'code', 'code', 255, $course_info['code'])
		.Form::getTextfield(Lang::t('_NAME', 'course'), 'name', 'name', 255, $course_info['name'])
		.Form::getTextarea(Lang::t('_DESCRIPTION', 'course'), 'description', 'description', $course_info['description'])
		.Form::getDropdown(Lang::t('_STATUS', 'course'), 'status', 'status', $model->getStatusForDropdown())
		.Form::getTextfield(Lang::t('_MAX_NUM_SUBSCRIBE', 'course'), 'max_par', 'max_par', 255)
		.Form::getTextfield(Lang::t('_MIN_NUM_SUBSCRIBE', 'course'), 'min_par', 'min_par', 255)
		.Form::getTextfield(Lang::t('_COURSE_PRIZE', 'course'), 'price', 'price', 255)
		.Form::getDatefield(Lang::t('_DATE_BEGIN', 'course'), 'date_begin', 'date_begin')
		.Form::getDatefield(Lang::t('_DATE_END', 'course'), 'date_end', 'date_end')
		.'<div class="form_line_l">'
		.'<p><label for="overbooking" class="floating">'.Lang::t('_ALLOW_OVERBOOKING', 'course').'</label></p>'
		.Form::getInputCheckbox('overbooking', 'overbooking', 1, false, false)
		.'</div>'
		.'<div class="form_line_l">'
		.'<p><label for="overbooking" class="floating">'.Lang::t('_SUBSCRIPTION_OPEN', 'course').'</label></p>'
		.Form::getInputCheckbox('can_subscribe', 'can_subscribe', 1, false, false)
		.'</div>'
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course'), 'sub_date_begin', 'sub_date_begin')
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_END', 'course'), 'sub_date_end', 'sub_date_end')
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('ins', 'ins', Lang::t('_SAVE', 'course'))
		.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'course'))
		.Form::closeButtonSpace()
		.Form::closeForm();
?>

</div>