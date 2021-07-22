
<?php foreach ($info as $ed): ?>

<?php if (
	$ed['unsubscribe_date_limit'] == '0000-00-00 00:00:00' ||
	$ed['unsubscribe_date_limit'] =='' ||
	strcmp(date('Y-m-d H:i:S'), $ed['unsubscribe_date_limit']) <= 0
): ?>

<div>
	<?php echo '<b>'.$ed['name'].'</b><br />'; ?>
	<?php
		echo '<b>'.Lang::t('_COURSE_BEGIN', 'certificate').'</b>: '.($ed['date_begin'] ? $ed['date_begin'] : "- ").'; '
			.'<b>'.Lang::t('_COURSE_END', 'certificate').'</b>: '.($ed['date_end'] ? $ed['date_end'] : "- ")."; ";
		echo ($ed['classroom'] != "" ? '<b>'.Lang::t('_LOCATION', 'standard').'</b>: '.$ed['classroom'] : "");
	?>
</div>

<?php if ($smodel->isUserWaitingForSelfUnsubscribe(Docebo::user()->idst, $id_course, false, $ed['id_date'])): ?>

<p style="padding:0.4em"><?php echo Lang::t('_UNSUBSCRIBE_REQUEST_WAITING_FOR_MODERATION', 'course'); ?></p>

<?php else: ?>

<a id="self_unsubscribe_link_<?php echo $id_course; ?> " 
	 href="index.php?r=elearning/self_unsubscribe&amp;id_course=<?php echo $id_course; ?>&amp;id_date=<?php echo $ed['id_date']; ?>&amp;back=lms/classroom/show"
	 title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
   <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
</a><br /><br />

<?php endif; ?>
<?php endif; ?>

<?php endforeach; ?>
