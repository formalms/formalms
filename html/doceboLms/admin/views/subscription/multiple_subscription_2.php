<?php
	$title = array(	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
				Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'));

	echo	getTitleArea($title)
			.'<div class="std_block">'
			.Form::openForm('course_selection_form', 'index.php?r='.$this->link.'/multiplesubscription')
			.Form::getHidden('id_cat', 'id_cat', $id_cat)
			.Form::getHidden('step', 'step', '2')
			.Form::getHidden('user_selection', 'user_selection', $user_selection)
			.$course_selector->loadCourseSelector(true)
			.Form::openButtonSpace()
			.Form::getButton('back', 'back', Lang::t('_PREV', 'course'))
			.Form::getButton('next', 'next', Lang::t('_NEXT', 'course'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>';
?>