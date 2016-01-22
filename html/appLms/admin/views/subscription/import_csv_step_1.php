<br />

<?php
$_subs_url = '&id_course='.(int)$id_course.($id_edition ? '&id_edition='.(int)$id_edition : '').($id_date ? '&id_date='.(int)$id_date : '');
$title = array(	'index.php?r='.$this->link.'/show&amp;id_course='.$model->getIdCourse() => Lang::t('_COURSE', 'course'),
				'index.php?r='.$this->link.'/show'.$_subs_url => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name,
				Lang::t('_IMPORT', 'course'));

echo	getTitleArea($title)
		.'<div class="std_block">'
		.Form::openForm('import_course_users', 'index.php?r='.$this->link.'/import_csv&id_course='.$model->getIdCourse().'&id_edition='.$model->getIdEdition().'&id_date='.$model->getIdDate(), false, false, 'multipart/form-data')
		.Form::openElementSpace()
		.Form::getFilefield( Lang::t('_IMPORT_FILE', 'subscribe'), 'file_import', 'file_import')
		.Form::getCheckbox( Lang::t('_IMPORT_HEADER', 'subscribe'), 'import_first_row_header', 'import_first_row_header', 'true', false )
		.Form::getHidden('import_separator', 'import_separator', ',')
		.Form::getHidden('import_charset', 'import_charset', 'UTF-8')
		.Form::getHidden('step', 'step', '1')
		.Form::closeElementSpace()
		.Form::openButtonSpace()
		.Form::getButton('next', 'next', Lang::t('_NEXT', 'subscription'))
		.Form::getButton('undo', 'undo', Lang::t('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>';
?>