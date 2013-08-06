<div class="middlearea_container">
	<?php
	$lmstab = $this->widget('lms_tab', array(
		'active' => 'games',
		'close' => false
	));
	?>
	<div class="nested_tab" id="tab_content">
		<div id="global_conf" class="yui-navset yui-navset-top">
			<ul class="yui-nav">
				<?php if($show_unread_tab) : ?>
				<li class="first <?php echo ($active_tab == 'unread' ? 'selected' : ''); ?>">
					<a href="index.php?r=lms/games/show">
						<em><?php echo Lang::t('_OPEN_COMPETITION', 'games') ?></em>
					</a>
				</li>
				<?php endif;
				if($show_history_tab) : ?>
				<li class="<?php echo (!$show_unread_tab ? 'first ' : ''); ?><?php echo ($active_tab == 'history' ? 'selected' : ''); ?>">
					<a href="index.php?r=lms/games/showhistory">
						<em><?php echo Lang::t('_HISTORY', 'games') ?></em>
					</a>
				</li>
				<?php endif; ?>
			</ul>
			<div class="yui-content">
				<?php
				switch($active_tab) {
					case "history" : {
						// the tab are open in the content filder, now i can put my contents here easily
						$columns = array(
							array('key' => 'title', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
							array('key' => 'start_date', 'label' => Lang::t('_START_DATE', 'standard'), 'sortable' => true, 'className' => 'image'),
							array('key' => 'end_date', 'label' => Lang::t('_DATE_END', 'standard'), 'sortable' => true, 'className' => 'image'),
							array('key' => 'description', 'label' => Lang::t('_TEXTOF')),
							array('key' => 'standings', 'label' => Lang::t('_STANDINGS', 'standard'), 'className' => 'image'),
						);

						$params = array(
							'id'			=> 'games',
							'ajaxUrl'		=> 'ajax.server.php?r=games/'.$ajax_action,
							'rowsPerPage'	=> Get::sett('visuItem', 25),
							'startIndex'	=> 0,
							'results'		=> Get::sett('visuItem', 25),
							'sort'			=> 'title',
							'dir'			=> 'asc',
							'columns'		=> $columns,
							'fields' => array('id_game', 'title', 'start_date', 'end_date', 'description', 'play', 'standings'),
							'events' => array(
								'postRenderEvent' =>  "function() { lb.init(); }"
							)
						);

						$this->widget('table', $params);
					};break;
					case "unread" :
					default: {
						// the tab are open in the content filder, now i can put my contents here easily
						$columns = array(
							array('key' => 'title', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
							array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard')),
							array('key' => 'start_date', 'label' => Lang::t('_START_DATE', 'standard'), 'sortable' => true, 'className' => 'image'),
							array('key' => 'end_date', 'label' => Lang::t('_DATE_END', 'standard'), 'sortable' => true, 'className' => 'image'),
							//array('key' => 'current_score', 'label' => Lang::t('_CURRENT_SCORE', 'games'), 'className' => 'image'),
							//array('key' => 'max_score', 'label' => Lang::t('_MAX_SCORE', 'games'), 'className' => 'image'),
							//array('key' => 'num_attempts', 'label' => Lang::t('_NUM_ATTEMPTS', 'games'), 'className' => 'image'),
							array('key' => 'play', 'label' => Lang::t('_PLAY', 'standard'), 'className' => 'image'),
							array('key' => 'standings', 'label' => Lang::t('_STANDINGS', 'standard'), 'className' => 'image'),
						);

						$params = array(
							'id'			=> 'games',
							'ajaxUrl'		=> 'ajax.server.php?r=games/'.$ajax_action,
							'rowsPerPage'	=> Get::sett('visuItem', 25),
							'startIndex'	=> 0,
							'results'		=> Get::sett('visuItem', 25),
							'sort'			=> 'title',
							'dir'			=> 'asc',
							'columns'		=> $columns,
							'fields' => array('id_game', 'title', 'start_date', 'end_date', 'description', 'play', 'standings'/*, 'current_score', 'max_score', 'num_attempts'*/),
							'events' => array(
								'postRenderEvent' =>  "function() { lb.init(); }"
							)
						);

						$this->widget('table', $params);
					};break;
				}
				?>
				<div class="nofloat"></div>
			</div>
		</div>
	</div>
	<?php
	// close the tab structure
	$lmstab->endWidget();
	?>
</div>
<script type="text/javascript">
	var lb = new LightBox();
</script>