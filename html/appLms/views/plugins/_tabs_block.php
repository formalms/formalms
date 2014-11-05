<div class="yui-t6">
	<div class="yui-b">
		<?php
		$this->widget('lms_block', array(
			'zone' => 'right',
			'link' => 'plugins/show',
			'block_list' => $block_list
		));
		?>
	</div>
	<div id="yui-main">
		<div class="yui-b">

			<div class="middlearea_container">
				<?php

				$w = $this->widget('lms_tab', array(
     			'active' => 'plugins',
     			'close' => false
    		));

    	

    		$w->endWidget();

				?>
			</div>

		</div>
	</div>

	<div class="nofloat"></div>
</div>
<script type="text/javascript">
	var lb = new LightBox();
	lb.back_url = 'index.php?r=plugins/show&sop=unregistercourse';
  var tabView = new YAHOO.widget.TabView();


	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ACTIVE_PLUGINS', 'plugins'); ?>',
	    dataSrc: 'ajax.server.php?r=plugins/active&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab, 0);

	
	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first');
	tabView.set('activeIndex', 0);

</script>