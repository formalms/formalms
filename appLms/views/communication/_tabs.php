<div class="middlearea_container">
	<?php
	$lmstab = $this->widget('lms_tab', array(
		'active' => 'communication',
		'close' => false
	));
	?>
	<div class="nested_tab" id="tab_content">
		<div id="global_conf" class="yui-navset yui-navset-top">
			<ul class="yui-nav">
				<?php if($show_unread_tab) : ?>
				<li class="first <?php echo ($active_tab == 'unread' ? 'selected' : ''); ?>">
					<a href="index.php?r=lms/communication/show">
						<em><?php echo Lang::t('_UNREAD', 'communication') ?></em>
					</a>
				</li>
				<?php endif;
				if($show_history_tab) : ?>
				<li class="<?php echo (!$show_unread_tab ? 'first ' : ''); ?><?php echo ($active_tab == 'history' ? 'selected' : ''); ?>">
					<a href="index.php?r=lms/communication/showhistory">
						<em><?php echo Lang::t('_HISTORY', 'communication') ?></em>
					</a>
				</li>
				<?php endif; ?>
			</ul>
			<div class="yui-content">
				<?php
				// the tab are open in the content filder, now i can put my contents here easily
				$columns = array(
					array('key' => 'title', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
					array('key' => 'publish_date', 'label' => Lang::t('_DATE', 'standard'), 'sortable' => true),
					array('key' => 'description', 'label' => Lang::t('_TEXTOF')),
					array('key' => 'play', 'label' => Lang::t('_PLAY', 'standard')),
				);

				$params = array(
					'id'			=> 'communication',
					'ajaxUrl'		=> 'ajax.server.php?r=communication/'.$ajax_action,
					'rowsPerPage'	=> Get::sett('visuItem', 25),
					'startIndex'	=> 0,
					'results'		=> Get::sett('visuItem', 25),
					'sort'			=> 'title',
					'dir'			=> 'asc',
					'columns'		=> $columns,
					'fields' => array('id_comm', 'title', 'publish_date', 'description', 'play'),
					'events' => array(
						'postRenderEvent' =>  "function() { lb.init(); }"
					)
				);

				$this->widget('table', $params);
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