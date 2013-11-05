<div class="middlearea_container">
	<?php
	$lmstab = $this->widget('lms_tab', array(
		'active' => 'assessment',
		'close' => false
	));
	if(!$this->isTabActive('completed') && !$this->isTabActive('new')) {
		echo Lang::t('_NO_CONTENT', 'course');
	}
	$lmstab->endWidget();
	?>
</div>
<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView();
	<?php if($this->isTabActive('new')): ?>
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_NEW'); ?>',
	    dataSrc: 'ajax.server.php?r=assessment/new',
	    cacheData: true
	});
	tabView.addTab(mytab, 0);
	<?php endif; ?>

	<?php if($this->isTabActive('completed')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMPLETED'); ?>',
	    dataSrc: 'ajax.server.php?r=assessment/completed',
	    cacheData: true
	});
	tabView.addTab(mytab, 1);
	<?php endif; ?>
	
	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first');
	tabView.set('activeIndex', 0);
</script>