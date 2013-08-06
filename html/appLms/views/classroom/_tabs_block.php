<div class="yui-t6">
	<div class="yui-b">
		<?php
		$this->widget('lms_block', array(
			'zone' => 'right',
			'link' => 'elearning/show',
			'block_list' => $block_list
		));
		?>
	</div>
	<div id="yui-main">
		<div class="yui-b">

			<div class="middlearea_container">
				<?php
				/*
				$this->widget('lms_tab', array(
					'active' => 'classroom'
				));
				*/
				$w = $this->widget('lms_tab', array(
     			'active' => 'classroom',
     			'close' => false
    		));

    		// draw search
				$_model = new ClassroomLms();
				$_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year',
					$_model->getFilterYears(Docebo::user()->getIdst()), 0, '');

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

<?php
$prop =array(
	'id' => 'self_unsubscribe_dialog',
	'dynamicContent' => true,
	'ajaxUrl' => 'this.href',
	'dynamicAjaxUrl' => true,
	'callEvents' => array()
);
$this->widget('dialog', $prop);
?>

<script type="text/javascript">
  var tabView = new YAHOO.widget.TabView();

	function unsubscribeClick() {
		var nodes = YAHOO.util.Selector.query('a[id^=self_unsubscribe_link_]');
		YAHOO.util.Event.on(nodes, 'click', function (e) {
			YAHOO.util.Event.preventDefault(e);
			CreateDialog("self_unsubscribe_dialog", {
				width: "700px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: false,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				confirmOnly: true,
				callback: function() {
					this.destroy();
				}
			}).call(this, e);
		});
	}

	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ALL_OPEN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=classroom/all&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addClass('first');
	mytab.addListener('contentChange', function() {
		unsubscribeClick();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab);

	<?php if($this->isTabActive('new')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_NEW', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=classroom/new&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		unsubscribeClick();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	<?php if($this->isTabActive('inprogress')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_USER_STATUS_BEGIN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=classroom/inprogress&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		unsubscribeClick();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	<?php if($this->isTabActive('completed')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMPLETED', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=classroom/completed&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		unsubscribeClick();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab);
	<?php endif; ?>

	tabView.appendTo('tab_content');
	tabView.set('activeIndex', 0);

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