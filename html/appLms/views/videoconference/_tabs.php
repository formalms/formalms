<div style="margin:1em;">
	<?php
	$this->widget('lms_tab', array(
		'active' => 'videoconference'
	));

	if(!$this->isTabActive('live') && !$this->isTabActive('planned') && !$this->isTabActive('history')) {
		echo Lang::t('_NO_CONTENT', 'course');
	}?>
</div>
<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView();
	<?php if($this->isTabActive('live')): ?>
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_VC_LIVE'); ?>',
	    dataSrc: 'ajax.server.php?r=videoconference/live',
	    cacheData: true
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	<?php if($this->isTabActive('planned')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_PLANNED'); ?>',
	    dataSrc: 'ajax.server.php?r=videoconference/planned',
	    cacheData: true
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	<?php if($this->isTabActive('history')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_HISTORY'); ?>',
	    dataSrc: 'ajax.server.php?r=videoconference/history',
	    cacheData: true
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first');
	tabView.set('activeIndex', 0);
</script>