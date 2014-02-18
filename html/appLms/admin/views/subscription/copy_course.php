<br />

<?php
	$_subs_url = '&id_course='.(int)$id_course.($id_edition ? '&id_edition='.(int)$id_edition : '').($id_date ? '&id_date='.(int)$id_date : '');
	$title = array(	'index.php?r='.$this->link.'/show&amp;id_course='.$model->getIdCourse() => Lang::t('_COURSE', 'course'),
					'index.php?r='.$this->link.'/show'.$_subs_url => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name,
					Lang::t('_COPY_SUBSCRIPTION_TO_COURSE', 'course'));

	echo	getTitleArea($title)
			.'<div class="std_block">'
			.Form::openForm('course_selection_form', 'index.php?r='.$this->link.'/copy_course&amp;id_course='.$model->getIdCourse())
			.Form::getHidden('id_cat', 'id_cat', $id_cat)
			.Form::getHidden('users', 'users', $users)
			.Form::getHidden('move', 'move', $move)
			.$course_selector->loadCourseSelector(true)
			.Form::openButtonSpace();

	if($move)
		echo Form::getButton('copy', 'copy', Lang::t('_MOVE', 'subscribe'));
	else
		echo Form::getButton('copy', 'copy', Lang::t('_COPY', 'subscribe'));
		
	echo     Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'))
			.Form::closeButtonSpace()
			.Form::closeForm()
			.'</div>';
?>