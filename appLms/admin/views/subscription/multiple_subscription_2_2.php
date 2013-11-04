<br />

<?php
$title = array(	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
				Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'));

echo getTitleArea($title);
?>

<div class="std_block">

<?php
	echo	Form::openForm('chose_level_form', 'index.php?r='.$this->link.'/multiplesubscription')
			.Form::getHidden('course_selection', 'course_selection', $course_selection)
			.Form::getHidden('user_selection', 'user_selection', $user_selection)
			.Form::getHidden('step', 'step', '3');

	$this->widget(	'table', array(
					'id'		=> 'edition_table',
					'styles'	=> $model->getEditionTableStyle(),
					'header'	=> $model->getEditionTableHeader(),
					'data'		=> $model->getEditionTableContent($courses),
					'summary'	=> Lang::t('_COURSE', 'course'),
					'caption'	=> Lang::t('_COURSE', 'course')
				));

	echo '<br /><br />';

	echo	Form::openButtonSpace()
			.Form::getButton('back', 'back', Lang::t('_PREV', 'course'))
			.Form::getButton('edition_selected', 'edition_selected', Lang::t('_NEXT', 'subscribe'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'subscribe'))
			.Form::closeElementSpace()
			.Form::closeForm();
?>

</div>