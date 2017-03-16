<div class="row">
	
	<div class="col-md-12">
		<div class="">

			<div class="middlearea_container">
                      
				<?php

				$w = $this->widget('lms_tab', array(
	     			'active' => 'elearning',
	     			'close' => false
	    		));

	    	    // select years
				$_model = new ElearningLms();
				$_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year', $_model->getFilterYears(Docebo::user()->getIdst()), 0, '');
                $_auxiliary = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width="150px"  data-actions-box="true"', $_auxiliary);
                
                $_list_category = Form::getInputDropdown('', 'course_search_filter_cat', 'filter_cat', $_model->getListCategory(Docebo::user()->getIdst()), 0, '');
                $_list_category = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width="150px" multiple data-actions-box="true"', $_list_category);
                
				$this->widget('tablefilter', array(
					'id' => 'course_search',
					'filter_text' => "",
                    'list_category' => $_list_category,
					// 'auxiliary_filter' => Lang::t('_SEARCH', 'standard').":&nbsp;&nbsp;&nbsp;".$_auxiliary,
					'auxiliary_filter' => $_auxiliary,
					'js_callback_set' => 'course_search_callback_set',
					'js_callback_reset' => 'course_search_callback_reset',
					'css_class' => 'tabs_filter'
				));

				$w->endWidget();

				?>
                
			</div>

		</div>
	</div>
    
    <div class="col-md-4" >
    
        <?php
        $this->widget('lms_block', array(
            'zone' => 'right',
            'link' => 'elearning/show',
            'block_list' => $block_list
        ));
        ?>

    </div>
      
	<div class="nofloat"></div>
</div>



<script type="text/javascript">
	var lb = new LightBox();
	lb.back_url = 'index.php?r=elearning/show&sop=unregistercourse';
     var tabView = new YAHOO.widget.TabView();


	var mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_ALL_OPEN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=elearning/all&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
	// COMMENTATO
    tabView.addTab(mytab, 0);
         
    
	<?php if($this->isTabActive('new')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_NEW', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=elearning/new&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
    
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
    // COMMENTATO
	tabView.addTab(mytab, 1);
	<?php endif; ?>

	<?php if($this->isTabActive('inprogress')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_USER_STATUS_BEGIN', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=elearning/inprogress&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
    

	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
    
    // COMMENTATO
	tabView.addTab(mytab, 2);
	<?php endif; ?>

	<?php if($this->isTabActive('completed')): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_COMPLETED', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=elearning/completed&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
	
    // COMMENTATO
    tabView.addTab(mytab, 3);
	<?php endif; ?>

	<?php if($this->isTabActive('suggested') && false): ?>
	mytab = new YAHOO.widget.Tab({
	    label: '<?php echo Lang::t('_SUGGESTED', 'course'); ?>',
	    dataSrc: 'ajax.server.php?r=elearning/suggested&rnd=<?php echo time(); ?>',
	    cacheData: true,
	    loadMethod: "POST"
	});
	mytab.addListener('contentChange', function() {
		lb.init();
		this.set("cacheData", true);
	});
	tabView.addTab(mytab, 4);
	<?php endif; ?>

	tabView.appendTo('tab_content');
	tabView.getTab(0).addClass('first');
	tabView.set('activeIndex', 0);

    
    
    function getSelectedOptions(sel, fn) {
        var opts = [], opt;
        
        // loop through options in select list
        for (var i=0, len=sel.options.length; i<len; i++) {
            opt = sel.options[i];
            
            // check if selected
            if ( opt.selected ) {
                // add to array of option elements to return from this function
                opts.push(opt.value);
                
                // invoke optional callback function if provided
                if (fn) {
                    fn(opt);
                }
            }
        }
        
        // return array containing references to selected option elements
        return opts;
    }    
    
    
    
	function course_search_callback_set() {
		var i, tabs = tabView.get("tabs"), activeTab = tabView.get("activeTab");
		var ft = YAHOO.util.Dom.get("course_search_filter_text").value;
		//var fy = YAHOO.util.Dom.get("course_search_filter_year").value;
        
        // aggiunta filtro tipo corso 
        var ftype = YAHOO.util.Dom.get("course_search_filter_type");
        var opts = getSelectedOptions( ftype );
        var json_type = opts.toString();           
        
        
        var fcat = YAHOO.util.Dom.get("course_search_filter_cat");
        var opts = getSelectedOptions(fcat);
        var json_cat = opts.toString();      

         
        var fy = YAHOO.util.Dom.get("course_search_filter_year");
        var opts = getSelectedOptions(fy);
        var json_year = opts.toString();             
         
                 
		for (i=0; i<tabs.length; i++) {
			tabs[i].set("postData", "filter_text="+ft+"&filter_year=" + json_year + "&filter_type=" + json_type + "&filter_cat=" + json_cat);
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
