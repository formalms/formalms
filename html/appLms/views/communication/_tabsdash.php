<div class="middlearea_container">
	<?php
	$this->widget('lms_tab', array(
		'active' => 'communication'
	));
	?>
</div>
<script type="text/javascript">

	var lb = new LightBox();
	var tabView = new YAHOO.widget.TabView();
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_UNREAD'); ?>',
	    dataSrc: 'ajax.server.php?r=communication/new',
	    cacheData: true,
	    active: true
	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 0);
	<?php if($this->isTabActive('history')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_HISTORY'); ?>',
	    dataSrc: 'ajax.server.php?r=communication/history',
	    cacheData: true
	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 1);
	<?php endif; ?>
	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first')

</script>
