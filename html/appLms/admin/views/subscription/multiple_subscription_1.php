<?php
$title = array(	'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
				Lang::t('_MULTIPLE_SUBSCRIPTION', 'course'));

$user_selector->loadSelector(	'index.php?r='.$this->link.'/multiplesubscription',
								$title,
								Lang::t('_CHOOSE_SUBSCRIBE', 'subscribe'),
								true);
?>