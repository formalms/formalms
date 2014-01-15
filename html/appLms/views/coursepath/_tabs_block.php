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

			<div class="middlearea_container">
				<?php
				$w = $this->widget('lms_tab', array(
					'active' => 'coursepath',
					'close' => false
				));
				
				// draw search
				$_model = new ElearningLms();
		
				$_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year',
					$_model->getFilterYears(Docebo::user()->getIdst()), 0, 'style="display:none"');
				
				$this->widget('tablefilter', array(
					'id' => 'course_search',
					'filter_text' => "",
					'auxiliary_filter' => Lang::t('_SEARCH', 'standard').":&nbsp;&nbsp;&nbsp;".$_auxiliary,
					'js_callback_set' => 'course_search_callback_set',
					'js_callback_reset' => 'course_search_callback_reset',
					'css_class' => 'tabs_filter'
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
	lb.back_url = 'index.php?r=coursepath/show&sop=unregistercourse';
    var tabView = new YAHOO.widget.TabView();
    
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ALL_OPEN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=coursepath/all&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab, 0);
	
	
	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_USER_STATUS_BEGIN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=coursepath/startPath',
	    cacheData: true,
	    active: true,
  	    loadMethod: "POST"

	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 1);
	<?php if($this->isTabActive('end')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMPLETED', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=coursepath/endPath',
	    cacheData: true,
  	    loadMethod: "POST"

	});
	mytab.addListener('contentChange', lb.init);
	tabView.addTab(mytab, 2);
	<?php endif; ?>
	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first')

	function expandCourses(id_path, type)
	{
		var div = YAHOO.util.Dom.get('courses_' + id_path);
		var link = YAHOO.util.Dom.get('courses_link_' + type + '_' + id_path);

		div.style.display = 'block';
		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="collapseCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><?php echo Lang::t('_COLLAPSE', 'coursepath'); ?></span> <?php echo Get::img('course/close.png', Lang::t('_COLLAPSE', 'coursepath')); ?></a>';
	}

	function collapseCourses(id_path, type)
	{
		var div = YAHOO.util.Dom.get('courses_' + id_path);
		var link = YAHOO.util.Dom.get('courses_link_' + type + '_' + id_path);

		div.style.display = 'none';
		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="expandCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><?php echo Lang::t('_EXPAND', 'coursepath'); ?></span> <?php echo Get::img('course/expand.png', Lang::t('_EXPAND', 'coursepath')); ?></a>';
	}
	
	function course_search_callback_set() {
		var i, tabs = tabView.get("tabs"), activeTab = tabView.get("activeTab");
		var ft = YAHOO.util.Dom.get("course_search_filter_text").value;
		var fy = YAHOO.util.Dom.get("course_search_filter_year").value;
		for (i=0; i<tabs.length; i++) {
			tabs[i].set("postData", "filter_text="+ft+"&filter_year="+fy);
			tabs[i].set("cacheData", false);
		}
		tabView.selectTab( tabView.getTabIndex(activeTab) );
		activeTab.set("cacheData", false);
	}

	function course_search_callback_reset() {
		var i, tabs = tabView.get("tabs"), activeTab = tabView.get("activeTab");
		YAHOO.util.Dom.get("course_search_filter_text").value = "";
		YAHOO.util.Dom.get("course_search_filter_year").selectedIndex = 0;
		for (i=0; i<tabs.length; i++) {
			tabs[i].set("postData", "");
			tabs[i].set("cacheData", false);
		}
		tabView.selectTab( tabView.getTabIndex(activeTab) );
		activeTab.set("cacheData", false);
	}
	
</script>