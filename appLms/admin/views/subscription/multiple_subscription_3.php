<br />

<?php
$title = array(	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
				Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'));

echo getTitleArea($title);
?>

<div class="std_block">

<?php
	$array_style = array(	'userid' => '',
							'fullname' =>  '',
							'administrator' => 'image',
							'instructor' => 'image',
							'mentor' => 'image',
							'tutor' => 'image',
							'student' => 'image',
							'ghost' => 'image',
							'guest' => 'image',
							'undo' => 'image');

	$array_header = array(	'userid' => Lang::t('_USERNAME', 'subscribe'),
							'fullname' =>  Lang::t('_FULLNAME', 'subscribe'),
							'administrator' => '<a href="javascript:SelAll(\'7\');">'.$model->level[7].'</a>',
							'instructor' => '<a href="javascript:SelAll(\'6\');">'.$model->level[6].'</a>',
							'mentor' => '<a href="javascript:SelAll(\'5\');">'.$model->level[5].'</a>',
							'tutor' => '<a href="javascript:SelAll(\'4\');">'.$model->level[4].'</a>',
							'student' => '<a href="javascript:SelAll(\'3\');">'.$model->level[3].'</a>',
							'ghost' => '<a href="javascript:SelAll(\'2\');">'.$model->level[2].'</a>',
							'guest' => '<a href="javascript:SelAll(\'1\');">'.$model->level[1].'</a>',
							'undo' => '<a href="javascript:SelAll(\'0\');">'.Lang::t('_UNDO', 'subscribe').'</a>');

	$array_content = array();

	echo	Form::openForm('chose_level_form', 'index.php?r='.$this->link.'/multiplesubscription')
			.Form::getHidden('course_selection', 'course_selection', $course_selection)
			.Form::getHidden('user_selection', 'user_selection', $user_selection)
			.Form::getHidden('edition_selected', 'edition_selected', $edition_selected)
			.Form::getHidden('step', 'step', '3');

	foreach($model->data as $id_user => $user_info)
	{
		$array_content[] = array(	'userid' => substr($user_info[ACL_INFO_USERID], 1),
									'fullname' => $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME],
									'administrator' => Form::getInputRadio('user_level_sel_'.$id_user.'_7', 'user_level_sel['.$id_user.']', 7, false, ''),
									'instructor' => Form::getInputRadio('user_level_sel_'.$id_user.'_6', 'user_level_sel['.$id_user.']', 6, false, ''),
									'mentor' => Form::getInputRadio('user_level_sel_'.$id_user.'_5', 'user_level_sel['.$id_user.']', 5, false, ''),
									'tutor' => Form::getInputRadio('user_level_sel_'.$id_user.'_4', 'user_level_sel['.$id_user.']', 4, false, ''),
									'student' => Form::getInputRadio('user_level_sel_'.$id_user.'_3', 'user_level_sel['.$id_user.']', 3, true, ''),
									'ghost' => Form::getInputRadio('user_level_sel_'.$id_user.'_2', 'user_level_sel['.$id_user.']', 2, false, ''),
									'guest' => Form::getInputRadio('user_level_sel_'.$id_user.'_1', 'user_level_sel['.$id_user.']', 1, false, ''),
									'undo' => Form::getInputRadio('user_level_sel_'.$id_user.'_0', 'user_level_sel['.$id_user.']', 0, false, ''));
	}

	$this->widget(	'table', array(
					'id'			=> 'subscribed_table',
					'styles' => $array_style,
					'header' => $array_header,
					'data' => $array_content,
					'summary' => Lang::t('_CHOSE_LEVEL', 'subscribe'),
					'caption' => Lang::t('_CHOSE_LEVEL', 'subscribe')
				));

	echo	Form::openButtonSpace()
			.Form::getButton('back', 'back', Lang::t('_PREV', 'course'))
			.Form::getButton('next', 'next', Lang::t('_SUBSCRIBE', 'subscribe'))
			.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'subscribe'))
			.Form::closeElementSpace()
			.Form::closeForm()
			.'<script>
'.$model->js_user.');
function SelAll (lvl)
{
        var nb;
        ne = elementi.length;
        mod = document.getElementById(\'levelselection\');
        for (var i=0;i<ne;i++)
        {
                elem = \'user_level_sel_\'+elementi[i]+\'_\'+lvl;
                var e = document.getElementById(elem);
                e.checked = 1;
        }
}
</script>';
?>

</div>