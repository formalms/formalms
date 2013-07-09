<div class="yui-t6">
	<div class="yui-b">
		<?php
		$this->widget('lms_block', array(
			'zone' => 'right',
			'link' => 'coursepath/show',
			'block_list' => $block_list
		));
		?>
	</div>
	<div id="yui-main">
		<div class="yui-b">

			<div style="margin:1em;">
				<?php
				$this->widget('lms_tab', array(
					'active' => 'coursepath'
				));
				?>
			</div>

		</div>
	</div>
	<div class="nofloat"></div>
</div>
<script type="text/javascript">
	var lb = new LightBox();
	lb.back_url = 'index.php?r=coursepath/show&sop=unregistercourse';
    var tabView = new YAHOO.widget.TabView();
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_USER_STATUS_BEGIN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=coursepath/startPath',
	    cacheData: true,
	    active: true
	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 0);
	<?php if($this->isTabActive('end')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMPLETED', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=coursepath/endPath',
	    cacheData: true
	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 1);
	<?php endif; ?>
	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first')

	function expandCourses(id_path)
	{
		var div = YAHOO.util.Dom.get('courses_' + id_path);
		var link = YAHOO.util.Dom.get('courses_link_' + id_path);

		div.style.display = 'block';
		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="collapseCourses(\'' + id_path + '\');"><span class="expand_path_info"><?php echo Lang::t('_COLLAPSE', 'coursepath'); ?></span> <?php echo Get::img('course/close.png', Lang::t('_COLLAPSE', 'coursepath')); ?></a>';
	}

	function collapseCourses(id_path)
	{
		var div = YAHOO.util.Dom.get('courses_' + id_path);
		var link = YAHOO.util.Dom.get('courses_link_' + id_path);

		div.style.display = 'none';
		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="expandCourses(\'' + id_path + '\');"><span class="expand_path_info"><?php echo Lang::t('_EXPAND', 'coursepath'); ?></span> <?php echo Get::img('course/expand.png', Lang::t('_EXPAND', 'coursepath')); ?></a>';
	}
</script>