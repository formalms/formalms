<?php
echo getTitleArea(array(
	'index.php?modname=coursepath&amp;op=pathlist&amp;of_platform=lms' => Lang::t('_COURSEPATH', 'coursepath'),
	'index.php?r='.$this->link.'/show_coursepath&id_path='.(int)$id_path => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$path_name,
	Lang::t('_CHOOSE_EDITION', 'subscribe')
));
?>
<div class="std_block">
<?php

echo Form::openForm('choose_level_editions_form', 'index.php?r='.$this->link.'/choose_editions_coursepath_action');

echo Form::getHidden('courses_list', 'courses_list', implode(",", $courses_list));
echo Form::getHidden('users_to_add', 'users_to_add', implode(",", $users_to_add));
echo Form::getHidden('users_to_del', 'users_to_del', implode(",", $users_to_del));
echo Form::getHidden('id_path', 'id_path', $id_path);

//header css
$_array_style = array(
	'course_name' => '',
	'dropdown' => 'image'
);

//editions table
if (!empty($editions_list)) {
	$_array_header = array(
		'course_name' => Lang::t('_COURSE', 'course'),
		'dropdown' => Lang::t('_EDITIONS', 'subscribe')
	);

	$_array_content = array();
	foreach ($editions_list as $_info) {
		$_array_content[] = array(
			'course_name' => $_info['label'],
			'dropdown' => Form::getInputDropdown(
					'dropdown',
					'editions_'.$_info['id_course'],
					'editions['.$_info['id_course'].']',
					$_info['list'],
					false,
					''
				)
		);
	}

	$this->widget('table', array(
		'id'			=> 'editions_table',
		'styles'	=> $_array_style,
		'header'	=> $_array_header,
		'data'		=> $_array_content,
		'summary'	=> Lang::t('_CHOOSE_EDITIONS', 'subscribe'),
		'caption'	=> false//Lang::t('', 'subscribe')
	));
}


//classrooms table
if (!empty($classrooms_list)) {
	$_array_header = array(
		'course_name' => Lang::t('_COURSE', 'course'),
		'dropdown' => Lang::t('_CLASSROOMS', 'subscribe')
	);

	$_array_content = array();
	foreach ($classrooms_list as $_info) {
		$_array_content[] = array(
			'course_name' => $_info['label'],
			'dropdown' => Form::getInputDropdown(
					'dropdown',
					'classrooms_'.$_info['id_course'],
					'classrooms['.$_info['id_course'].']',
					$_info['list'],
					false,
					''
				)
		);
	}

	$this->widget('table', array(
		'id'			=> 'classrooms_table',
		'styles'	=> $_array_style,
		'header'	=> $_array_header,
		'data'		=> $_array_content,
		'summary'	=> Lang::t('_CHOOSE_CLASSROOMS', 'subscribe'),
		'caption'	=> false//Lang::t('', 'subscribe')
	));
}

echo Form::openButtonSpace();
echo Form::getButton('subscribe', 'subscribe', Lang::t('_SUBSCRIBE', 'subscribe'));
echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'subscribe'));
echo Form::closeElementSpace();
echo Form::closeForm();

?>
</div>