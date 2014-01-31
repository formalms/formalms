<?php if( $use_label ) : ?>
<div class="container-back">
	<a href="index.php?r=elearning/show&id_common_label=-2">
		<span>&lsaquo;&lsaquo; <?php echo Lang::t('_BACK_TO_LABEL', 'course') ?></span>
	</a>
</div>
<?php endif; ?>
<?php if( empty($courselist) ) : ?>

	<p><?php echo Lang::t('_NO_CONTENT', 'standard'); ?></p>

<?php endif; ?> 
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
			<a title="<?php echo Util::purge($course['name']); ?>" href="index.php?modname=course&amp;op=aula&amp;idCourse=<?php echo $course['idCourse']; ?>"<?php echo ( $course['direct_play'] == 1 && $course['level'] <= 3 && $course['first_lo_type'] == 'scormorg' ? ' rel="lightbox"' :'' ); ?>>
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
		<?php 
			$acl_man = Docebo::user()->getAclManager();
			$levels = CourseLevel::getLevels();
			
			while(list($num_lv, $name_level) = each($levels)) {
				if(CourseLevel::isTeacher($num_lv)) {
					
				}
				if($course['level_show_user'] & (1 << $num_lv)) {
					if(CourseLevel::isTeacher($num_lv)) {
						echo "&nbsp;" . $name_level . ":&nbsp;";
						$users =& $acl_man->getUsers( Man_Course::getIdUserOfLevel($course['idCourse'], $num_lv, $course['course_edition']) );
						if(!empty($users)) {
							$first = true;
							while(list($id_user, $user_info) = each($users)) {
								if($first) $first = false;
								else echo(', ');
//								echo '<a href="index.php?modname=course&amp;op=viewprofile&amp;id_user='.$id_user.'">' . $acl_man->getConvertedUserName($user_info) . '</a>';
								echo '<b>'.$acl_man->getConvertedUserName($user_info).'</b>';
							} // end while
						} // end if
					}
				} // end if
			} // end while
		?>
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
         <?php 
         if((!empty($course['can_enter']['expiring_in']) && $course['can_enter']['can']==false && $course['can_enter']['expiring_in'] <= 0) || ($course['can_enter']['expiring_in'] == 0 && $course['can_enter']['can']==false)) : ?>
            <p class="course_support_info">
                <?php echo Lang::t('_EXPIRED', 'course'); ?>
            </p>
        <?php endif; ?>    				
		<p class="course_support_info">
			<?php if($course['code']) { ?><i style="font-size:.88em">[<?php echo $keyword != "" ? Layout::highlight($course['code'], $keyword) : $course['code']; ?>]</i><?php } ?>
		</p>
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
				if ($_can_unsubscribe):
		?>
		<p class="course_support_info">
			<?php if ($_date_limit !== FALSE && $_date_limit < date("Y-m-d H:i:s")) {
				echo '';
			} else { ?>
			<a href="index.php?r=elearning/self_unsubscribe&amp;id_course=<?php echo $course['idCourse']; ?>&amp;back=<?php echo Get::req('r', DOTY_STRING, ""); ?>"
				 title="<?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>">
				 <?php echo Lang::t('_SELF_UNSUBSCRIBE', 'course'); ?>
			</a>
			<?php
				if ($_date_limit) echo '&nbsp;('.Lang::t('_UNTIL', 'standard').' '.Format::date(substr($_date_limit, 0, 10), 'date').')';
			?>
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