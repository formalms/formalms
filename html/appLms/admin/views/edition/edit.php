<br />

<?php
$title = array(	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
				'index.php?r='.$base_link_edition.'/show&id_course='.$model->getIdCourse() => Lang::t('_EDITIONS', 'course'),
				Lang::t('_MOD', 'course'));

echo getTitleArea($title);
?>

<div class="std_block">

<?php
echo	Form::openForm('add_edition_form', 'index.php?r='.$base_link_edition.'/edit&amp;id_course='.$model->getIdCourse().'&amp;id_edition='.$model->getIdEdition())
		.Form::openElementSpace()
		.Form::getTextfield(Lang::t('_CODE', 'course'), 'code', 'code', 255, $edition_info['code'])
		.Form::getTextfield(Lang::t('_NAME', 'course'), 'name', 'name', 255, $edition_info['name'])
		.Form::getTextarea(Lang::t('_DESCRIPTION', 'course'), 'description', 'description', $edition_info['description'])
		.Form::getDropdown(Lang::t('_STATUS', 'course'), 'status', 'status', $model->getStatusForDropdown(), $edition_info['status'])
		.Form::getTextfield(Lang::t('_MAX_NUM_SUBSCRIBE', 'course'), 'max_par', 'max_par', 255, $edition_info['max_num_subscribe'])
		.Form::getTextfield(Lang::t('_MIN_NUM_SUBSCRIBE', 'course'), 'min_par', 'min_par', 255, $edition_info['min_num_subscribe'])
		.Form::getTextfield(Lang::t('_COURSE_PRIZE', 'course'), 'price', 'price', 255, $edition_info['price'])
		.Form::getDatefield(Lang::t('_DATE_BEGIN', 'course'), 'date_begin', 'date_begin', ($edition_info['date_begin'] !== '' && $edition_info['date_begin'] !== '0000-00-00' ? Format::date($edition_info['date_begin'], 'date') : ''))
		.Form::getDatefield(Lang::t('_DATE_END', 'course'), 'date_end', 'date_end', ($edition_info['date_end'] !== '' && $edition_info['date_end'] !== '0000-00-00' ? Format::date($edition_info['date_end'], 'date') : ''))
		.'<div class="form_line_l">'
		.'<p><label for="overbooking" class="floating">'.Lang::t('_ALLOW_OVERBOOKING', 'course').'</label></p>'
		.Form::getInputCheckbox('overbooking', 'overbooking', 1, $edition_info['overbooking'], false)
		.'</div>'
		.'<div class="form_line_l">'
		.'<p><label for="overbooking" class="floating">'.Lang::t('_SUBSCRIPTION_OPEN', 'course').'</label></p>'
		.Form::getInputCheckbox('can_subscribe', 'can_subscribe', 1, $edition_info['can_subscribe'], false)
		.'</div>'
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course'), 'sub_date_begin', 'sub_date_begin', ($edition_info['sub_date_begin'] !== '' && $edition_info['sub_date_begin'] !== '0000-00-00' ? Format::date($edition_info['sub_date_begin'], 'date') : ''))
		.Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_END', 'course'), 'sub_date_end', 'sub_date_end', ($edition_info['sub_date_end'] !== '' && $edition_info['sub_date_end'] !== '0000-00-00' ? Format::date($edition_info['sub_date_end'], 'date') : ''))
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('mod', 'mod', Lang::t('_MOD', 'course'))
		.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'course'))
		.Form::closeButtonSpace()
		.Form::closeForm();
?>

</div>