<div class="middlearea_container">
	<?php
	$this->widget('lms_tab', array(
		'active' => 'catalog'
	));
	?>
</div>
<script type="text/javascript">
    var tabView = new YAHOO.widget.TabView();
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ALL'); ?>',
	    dataSrc: 'ajax.server.php?r=catalog/allCourse&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    active: true
	});
	tabView.addTab(mytab, 0);
	<?php if($this->isTabActive('new')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_NEW'); ?>',
	    dataSrc: 'ajax.server.php?r=catalog/newCourse&rnd=<?php echo time(); ?>',
	    cacheData: true
	});
	tabView.addTab(mytab, 1);
	<?php endif; ?>

	<?php if($this->isTabActive('elearning')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ELEARNING'); ?>',
	    dataSrc: 'ajax.server.php?r=catalog/elearningCourse&rnd=<?php echo time(); ?>',
	    cacheData: true
	});
	tabView.addTab(mytab, 2);
	<?php endif; ?>

	<?php if($this->isTabActive('classroom')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_CLASSROOM'); ?>',
	    dataSrc: 'ajax.server.php?r=catalog/classroomCourse&rnd=<?php echo time(); ?>',
	    cacheData: true
	});
	tabView.addTab(mytab, 3);
	<?php endif; ?>

	<?php /* if($this->isTabActive('calendar')):
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_CALENDAR'); ?>',
	    dataSrc: 'ajax.server.php?r=catalog/calendarCourse',
	    cacheData: true
	});
	tabView.addTab(mytab, 4);
	endif; */ ?>

	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first')
</script>