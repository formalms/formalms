<?php if( empty($courselist) ) : ?>

	<p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>

<?php endif; ?>
<?php foreach( $courselist as $course ) : ?>

	<div class="dash-course">

		<h2>
			<?php if ($course['can_enter']['can']) { ?>
			<a href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo ( $course['direct_play'] == 1 && $course['level'] <= 3 ? ' rel="lightbox"' :'' ); ?>>
				<?php echo ( $course['lang_code'] != 'none' ? Get::img('language/'.strtolower($course['lang_code']).'.png', $course['lang_code']) : '' ); ?>
				<?php echo $course['name']; ?>
			</a>
			<?php } else {
				echo Get::img('standard/locked.png', Lang::t('_'.strtoupper($course['can_enter']['reason']), 'standard'));
				echo ' '.$course['name'];
			}
			?>
		</h2>
		<p class="course_support_info">
			<?php
			echo Lang::t($this->ustatus[ $course['user_status'] ], 'course').''
				.Lang::t('_USER_LVL', 'course', array('[level]' => '<b>'.$this->levels[ $course['level'] ].'</b>'));
			?>
		</p>
		<p class="course_support_info">
			<?php
			echo Lang::t('_COURSE_INTRO', 'course', array(
				'[course_type]'		=> $course['course_type'],
				'[create_date]'		=> $course['create_date'],
				'[enrolled]'		=> $course['enrolled'],
				'[course_status]'	=> Lang::t($this->cstatus[$course['course_status']], 'course')
			));
			?>
		</p>
		<?php if(!empty($access['expiring_in']) && $access['expiring_in'] < 30) : ?>
			<p class="course_support_info">
				<?php echo Lang::t('_EXPIRING_IN', 'course', array('[expiring_in]' => $access['expiring_in'])); ?>
			</p>
		<?php endif; ?>
		<p class="course_support_info">
			<?php if($course['code']) { ?><i style="font-size:.88em">[<?php echo $course['code']; ?>]</i><?php } ?>
		</p>
	</div>

<?php endforeach; ?>