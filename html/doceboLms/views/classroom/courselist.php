<?php if( $use_label ) : ?>
<div style="position:absolute;right:1%;top:30px;">
	<a href="index.php?r=classroom/show&id_common_label=-2">
		<span>&lsaquo;&lsaquo;<?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
	</a>
</div>
<?php endif; ?>
<?php if( empty($courselist) ) : ?>

	<p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>

<?php endif; ?>

<?php $unsubscribe_call_arr =array(); ?>

<?php foreach( $courselist as $course ) : ?>

	<?php echo '<div class="dash-course '.
		($course['user_status'] < 1 ? 'status_subscribed' : 'status_begin').'">'; ?>

		<?php if($course['use_logo_in_courselist'] && $course['img_course']) : ?>
		<div class="logo_container">
			<img class="clogo"
				src="<?php echo $path_course.$course['img_course']; ?>"
				alt="<?php echo Util::purge($course['name']); ?>" />
		</div>
		<?php endif; ?>
		<?php if($course['use_logo_in_courselist'] && !$course['img_course']) : ?>
		<div class="logo_container">
			<img class="clogo cnologo"
				 src="<?php echo Get::tmpl_path().'images/course/course_nologo.png'; ?>"
				alt="<?php echo Util::purge($course['name']); ?>" />
		</div>
		<?php endif; ?>

		<div class="info_container">
		<h2>
			<?php if ($course['can_enter']['can']) { ?>
			<a href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>">
				<?php echo ( $course['lang_code'] != 'none' ? Get::img('language/'.strtolower($course['lang_code']).'.png', $course['lang_code']) : '' ); ?>
				<?php echo $keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']; ?>
			</a>
			<?php } else {
				echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
				echo ' '.($keyword != "" ? Layout::highlight($course['name'], $keyword) : $course['name']);
			}
			?>
		</h2>
		<p class="course_support_info">
			<?php
			echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''
				.Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>'));
			?>
		</p>
		<!-- p class="course_support_info" -->
			<?php
			/* echo Lang::t('_COURSE_INTRO', 'course', array(
				'[course_type]'		=> $course['course_type'],
				'[create_date]'		=> $course['create_date'],
				'[enrolled]'		=> $course['enrolled'],
				'[course_status]'	=> Lang::t($this->cstatus[$course['course_status']], 'course')
			));*/
			?> 
		<!-- /p -->
		<?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
			<p class="course_support_info">
				<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
			</p>
		<?php endif; ?>
		<p class="course_support_info">
			<?php if($course['code']) { ?><i style="font-size:.88em">[<?php echo $course['code']; ?>]</i><?php } ?>
		</p>

		<?php
			if (!empty($display_info) && isset($display_info[$course['idCourse']])) {
				echo '<p class="course_support_info">';
				echo '<ul class="action-list">';
				foreach ($display_info[$course['idCourse']] as $key => $info) {
					$_start_time = $info->start_date != "" && $info->start_date != "0000-00-00 00:00:00" ? Format::date($info->start_date, 'datetime') : "";
					$_end_time = $info->end_date != "" && $info->end_date != "0000-00-00 00:00:00" ? Format::date($info->end_date, 'datetime') : "";
					echo '<li style="width: 98%;">';//.($info->code != "" ? '['.$info->code.'] ' : "").$info->name.' '


					$start_date =$info->date_info['date_begin'];
					$end_date =$info->date_info['date_end'];
					$_start_time = $start_date != "" && $start_date != "0000-00-00 00:00:00" ? Format::date($start_date, 'datetime') : "";
					$_end_time = $end_date != "" && $end_date != "0000-00-00 00:00:00" ? Format::date($end_date, 'datetime') : "";

					echo '<b>'.Lang::t('_COURSE_BEGIN', 'certificate').'</b>: '.($_start_time ? $_start_time : "- ").'; '
						.'<b>'.Lang::t('_COURSE_END', 'certificate').'</b>: '.($_end_time ? $_end_time : "- ")."; ";


					echo ($info->date_info['location'] != "" ? '<b>'.Lang::t('_LOCATION', 'standard').'</b>: '.$info->date_info['location'] : "");


					$_text_key = property_exists($info, 'max_participants') && $info->max_participants > 0
						? '_COURSE_INTRO_WITH_MAX'
						: '_COURSE_INTRO';

					echo "<br />".Lang::t($_text_key, 'course', array(
						'[course_type]'		=> $course['course_type'],
						'[enrolled]'		=> $info->enrolled,
						'[max_subscribe]' => property_exists($info, 'max_participants') ? $info->max_participants : 0,
						'[course_status]'	=> ($info->overbooking > 0 && $info->max_participants > 0 && $info->students >= $info->max_participants)
							? Lang::t('_USER_STATUS_OVERBOOKING', 'subscribe')
							: $info->status,
					));


					echo '</li>';
				}
				echo '</ul>';
				echo '</p>';
			}
		?>

		<?php
			$smodel = new SubscriptionAlms();
			if ($smodel->isUserWaitingForSelfUnsubscribe(Docebo::user()->idst, $course['idCourse'])) {
				echo '<p style="padding:.4em">'.Lang::t('_UNSUBSCRIBE_REQUEST_WAITING_FOR_MODERATION', 'course').'</p>';
			} else {

				//auto unsubscribe management: create a link for the user in the course block
				$_can_unsubscribe = ($course['auto_unsubscribe']==1 || $course['auto_unsubscribe']==2);
				$_date_limit = $course['unsubscribe_date_limit'] != "" && $course['unsubscribe_date_limit'] != "0000-00-00 00:00:00"
					? $course['unsubscribe_date_limit']
					: FALSE;
				echo '<!-- '.print_r($course['auto_unsubscribe'], true).' -->';
				echo '<!-- '.print_r($course['unsubscribe_date_limit'], true).' -->';
				if ($_can_unsubscribe):
		?>
		<p class="course_support_info">
			<?php if ($_date_limit !== FALSE && $_date_limit <= date("Y-m-d H:i:s")) {
				echo '';
			} else {

				$unsubscribe_call_arr[]=$course['idCourse'];

				?>

			<?php if ($dm->checkHasValidUnsubscribePeriod($course['idCourse'], Docebo::user()->getIdSt())): ?>
			<a id="self_unsubscribe_link_<?php echo $course['idCourse']; ?> " href="ajax.server.php?r=classroom/self_unsubscribe_dialog&amp;id_course=<?php echo $course['idCourse']; ?>"
				 title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
				 <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
			</a>
			<?php
				if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
			?>
			<?php endif; ?>
			<?php } //endif ?>
		</p>
		<?php
				endif;
			}
			unset($smodel);
		?>
		</div><!-- info container -->
	</div>

<?php endforeach; ?>

