<div style="margin:1em;">
	<?php
    $this->widget('lms_tab', [
        'active' => 'calendar',
    ]);
    ?>
</div>
<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView();
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ALL'); ?>',
	    dataSrc: 'ajax.server.php?r=calendar/all',
	    cacheData: true,
	    active: true
	});
	tabView.addTab(mytab, 0);
	<?php if ($this->isTabActive('course')) { ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COURSES'); ?>',
	    dataSrc: 'ajax.server.php?r=calendar/course',
	    cacheData: true
	});
	tabView.addTab(mytab, 1);
	<?php } ?>

	<?php if ($this->isTabActive('communication')) { ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMMUNICATIONS'); ?>',
	    dataSrc: 'ajax.server.php?r=calendar/communication',
	    cacheData: true
	});
	tabView.addTab(mytab, 2);
	<?php } ?>

	<?php if ($this->isTabActive('videoconference')) { ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_VIDEOCONFERENCE'); ?>',
	    dataSrc: 'ajax.server.php?r=calendar/videoconference',
	    cacheData: true
	});
	tabView.addTab(mytab, 3);
	<?php } ?>

	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first')
</script>