<br />

<?php
$_subs_url = '&id_course='.(int)$id_course.($id_edition ? '&id_edition='.(int)$id_edition : '').($id_date ? '&id_date='.(int)$id_date : '');
$title = array(	'index.php?r='.$this->link.'/show&amp;id_course='.$this->model->getIdCourse() => Lang::t('_COURSE', 'course'),
				'index.php?r='.$this->link.'/show'.$_subs_url => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name,
				Lang::t('_IMPORT', 'course'));

echo	getTitleArea($title)
		.'<div class="std_block">'
		.$back_link
		.$table->getTable()
		.$back_link
		.'</div>';
?>