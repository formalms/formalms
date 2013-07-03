<?php
$_subs_url = '&id_course='.(int)$id_course.($id_edition ? '&id_edition='.(int)$id_edition : '').($id_date ? '&id_date='.(int)$id_date : '');
echo getTitleArea(array(
	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSES', 'admin_course_managment'),
	'index.php?r='.$this->link.'/show'.$_subs_url => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name,
	Lang::t('_SUBSCRIBE', 'subscribe').': '.Lang::t('_LEVELS', 'subscribe')
));
?>
<div class="std_block">
<ul class="style_none">
	<li><?php echo Lang::t('_COURSE', 'course').': <b>'.(trim($course_info['code']) ? '['.trim($course_info['code']).'] ' : '').$course_info['name'].'</b>'; ?></li>
	<li><?php echo Lang::t('_USER_STATUS_SUBS', 'subscribe').': <b>'.(int)$num_subscribed.'</b>'; ?></li>
</ul>
<?php
	$array_style = array(
		'userid' => '',
		'fullname' =>  '',
		'administrator' => 'image',
		'instructor' => 'image',
		'mentor' => 'image',
		'tutor' => 'image',
		'student' => 'image',
		'ghost' => 'image',
		'guest' => 'image',
		'undo' => 'image'
	);

	$array_header = array(
		'userid' => Lang::t('_USERNAME', 'subscribe'),
		'fullname' =>  Lang::t('_FULLNAME', 'subscribe'),
		'administrator' => '<a href="javascript:SelAll(\'7\');">'.$model->level[7].'</a>',
		'instructor' => '<a href="javascript:SelAll(\'6\');">'.$model->level[6].'</a>',
		'mentor' => '<a href="javascript:SelAll(\'5\');">'.$model->level[5].'</a>',
		'tutor' => '<a href="javascript:SelAll(\'4\');">'.$model->level[4].'</a>',
		'student' => '<a href="javascript:SelAll(\'3\');">'.$model->level[3].'</a>',
		'ghost' => '<a href="javascript:SelAll(\'2\');">'.$model->level[2].'</a>',
		'guest' => '<a href="javascript:SelAll(\'1\');">'.$model->level[1].'</a>',
		'undo' => '<a href="javascript:SelAll(\'0\');">'.Lang::t('_UNDO', 'subscribe').'</a>'
	);

	$array_content = array();

	echo Form::openForm('choose_level', 'index.php?r='.$this->link.'/ins&amp;id_course='.$model->getIdCourse().'&amp;id_edition='.$model->getIdEdition().'&amp;id_date='.$model->getIdDate())
		.Form::getHidden('send_alert', 'send_alert', $send_alert);


	if ($date_begin_validity) {
		echo Form::getLineBox(Lang::t('_DATE_BEGIN_VALIDITY', 'subscribe'), Format::date(substr($date_begin_validity, 0, 10), 'date'));
		echo Form::getHidden('set_date_begin_validity', 'set_date_begin_validity', $date_begin_validity);
	}

	if ($date_expire_validity) {
		echo Form::getLineBox(Lang::t('_DATE_EXPIRE_VALIDITY', 'subscribe'), Format::date(substr($date_expire_validity, 0, 10), 'date'));
		echo Form::getHidden('set_date_expire_validity', 'set_date_expire_validity', $date_expire_validity);
	}

	if (is_array($model->data)) {
		foreach($model->data as $id_user => $user_info)
		{
			$array_content[] = array(
				'userid' => substr($user_info[ACL_INFO_USERID], 1),
				'fullname' => $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME],
				'administrator' => Form::getInputRadio('user_level_sel_'.$id_user.'_7', 'user_level_sel['.$id_user.']', 7, false, ''),
				'instructor' => Form::getInputRadio('user_level_sel_'.$id_user.'_6', 'user_level_sel['.$id_user.']', 6, false, ''),
				'mentor' => Form::getInputRadio('user_level_sel_'.$id_user.'_5', 'user_level_sel['.$id_user.']', 5, false, ''),
				'tutor' => Form::getInputRadio('user_level_sel_'.$id_user.'_4', 'user_level_sel['.$id_user.']', 4, false, ''),
				'student' => Form::getInputRadio('user_level_sel_'.$id_user.'_3', 'user_level_sel['.$id_user.']', 3, true, ''),
				'ghost' => Form::getInputRadio('user_level_sel_'.$id_user.'_2', 'user_level_sel['.$id_user.']', 2, false, ''),
				'guest' => Form::getInputRadio('user_level_sel_'.$id_user.'_1', 'user_level_sel['.$id_user.']', 1, false, ''),
				'undo' => Form::getInputRadio('user_level_sel_'.$id_user.'_0', 'user_level_sel['.$id_user.']', 0, false, '')
			);
		}
	}
	
	$this->widget('table', array(
		'id'			=> 'subscribed_table',
		'styles'	=> $array_style,
		'header'	=> $array_header,
		'data'		=> $array_content,
		'summary'	=> Lang::t('_LEVELS', 'subscribe'),
		'caption'	=> false//Lang::t('_LEVELS', 'subscribe')
	));

	echo Form::openButtonSpace();
	echo Form::getButton('subscribe', 'subscribe', Lang::t('_SUBSCRIBE', 'subscribe'));
	echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'subscribe'));
	echo Form::closeElementSpace();
	echo Form::closeForm();
	
	echo '<script>'.$model->js_user.');
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