<!--<div class="row">-->
<!--	<div class="col-md-12">-->
<!--		--><?php
//		$this->widget('lms_block', array(
//			'zone' => 'right',
//			'link' => 'coursepath/show',
//			'block_list' => $block_list
//		));
//		?>
<!--	</div>-->
<!--	<div id="yui-main">-->
<!--		<div class="yui-b">-->
<!---->
<!--			<div class="middlearea_container">-->
<!--				--><?php
//				$w = $this->widget('lms_tab', array(
//					'active' => 'coursepath',
//					'close' => false
//				));
//
//				// draw search
//				$_model = new ElearningLms();
//
//				$_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year',
//					$_model->getFilterYears(Docebo::user()->getIdst()), 0, 'style="display:none"');
//
//				$this->widget('tablefilter', array(
//					'id' => 'course_search',
//					'filter_text' => "",
//					'auxiliary_filter' => $_auxiliary,
//					'js_callback_set' => 'course_search_callback_set',
//					'js_callback_reset' => 'course_search_callback_reset',
//					'css_class' => 'tabs_filter'
//				));
//
//				$w->endWidget();
//
//
//				?>
<!--			</div>-->
<!---->
<!--		</div>-->
<!--	</div>-->
<!--	<div class="nofloat"></div>-->
<!--</div>-->



<?php
$_model = new ElearningLms();
$count = 0;
$statusFilters = $_model->getFilterStatusCourse(Docebo::user()->getIdst());

$html = '<ul class="nav nav-pills">';

while( list($key, $value) = each($statusFilters) ) {

	$html_code .= '	<option value="'.$key.'"'
		.((string)$key == (string)$selected ? ' selected="selected"' : '' )
		.'>'.$value.'</option>'."\n";

	if ($count === 0) {
		$html .= '<li class="selected js-label-menu-filter" data-value="' . $key . '">';
	} else {
		$html .= '<li class="js-label-menu-filter" data-value="' . $key . '">';
	}

	$html .= '<a class="icon--filter-' . $key . '" href="#" >' . $value . '</a>';
	$html .= '</li>';

	//                echo $html;

	$count++;
}

$html .= '</ul>';

$inline_filters = $html;

?>



<?php

$w = $this->widget('lms_tab', array(
	'active' => 'coursepath',
	'close' => false
));

$w->endWidget();

// select status course
$_model = new ElearningLms();

// select year
$_auxiliary = $_auxiliary. Form::getInputDropdown('', 'course_search_filter_year', 'filter_year', $_model->getFilterYears(Docebo::user()->getIdst()), 0, '');
$_auxiliary = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width=""  data-actions-box="true"', $_auxiliary);

$_list_category = Form::getInputDropdown('', 'course_search_filter_cat', 'filter_cat', $_model->getListCategory(Docebo::user()->getIdst()), 0, '');
$_list_category = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width="" multiple data-actions-box="true"', $_list_category);

$this->widget('tablefilter', array(
	'id' => 'course_search',
	'filter_text' => "",
	'list_category' => $_list_category,
	// 'auxiliary_filter' => Lang::t('_SEARCH', 'standard').":&nbsp;&nbsp;&nbsp;".$_auxiliary,
	'auxiliary_filter' => $_auxiliary,
	'inline_filters' => $inline_filters,
	'js_callback_set' => 'course_search_callback_set',
	'js_callback_reset' => 'course_search_callback_reset',
	'css_class' => 'nav'
));
?>

<!--			</div>-->


</div>

<!--
    <div class="col-md-4" >

        <?php
$this->widget('lms_block', array(
	'zone' => 'right',
	'link' => 'coursepath/show',
	'block_list' => $block_list
));
?>

    </div>
    -->

<div class="nofloat" ></div>








<div  class="col-md-12" id="div_course">
	<br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>
</div>






<script type="text/javascript">
	var lb = new LightBox();
	lb.back_url = 'index.php?r=coursepath/show&sop=unregistercourse';
	var tabView = new YAHOO.widget.TabView();

	<?php
	define("IS_AJAX", true);
	?>

	var posting = $.get('ajax.server.php?r=coursepath/all&rnd=<?php echo time(); ?>',
		{}
	);

	posting.done(function(responseText){
		$("#div_course").html(responseText);
	});

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

	$('.js-label-menu-filter').on('click', function () {
		$(this).addClass('selected').siblings().removeClass('selected');
		course_search_callback_set();
	});

	function course_search_callback_set() {
		var i;

		var ft = $("#course_search_filter_text").val();

		var json_type = $("#course_search_filter_type option:selected").val();

		var json_cat = $("#course_search_filter_cat option:selected").val();

		var json_year = $("#course_search_filter_year option:selected").val();


//        var json_status = $("#course_search_filter_status option:selected").val();
		var json_status = $('.js-label-menu-filter.selected').attr('data-value');

		$("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
		var posting = $.get( 'ajax.server.php?r=elearning/all&rnd=<?php echo time(); ?>&filter_text=' + ft + '&filter_type=' + json_type + '&filter_cat=' + json_cat + '&filter_status=' + json_status + '&filter_year=' + json_year, {}

		);

		posting.done(function(responseText){
			$("#div_course").html(responseText);
		});
	}

	function course_search_callback_reset() {

	}
</script>







<script type="text/javascript">
	//	var lb = new LightBox();
	//	lb.back_url = 'index.php?r=coursepath/show&sop=unregistercourse';
	//    var tabView = new YAHOO.widget.TabView();
	//
	//	var mytab = new YAHOO.widget.Tab({
	//	    label: '<?php //echo Lang::t('_ALL_OPEN', 'course'); ?>//',
	//	    dataSrc: 'ajax.server.php?r=coursepath/all&rnd=<?php //echo time(); ?>//',
	//	    cacheData: true,
	//	    loadMethod: "POST"
	//	});
	//	mytab.addListener('contentChange', function() {
	//		lb.init();
	//		this.set("cacheData", true);
	//	});
	//	tabView.addTab(mytab, 0);
	//
	//
	//	var mytab = new YAHOO.widget.Tab({
	//	    label: '<?php //echo Lang::t('_USER_STATUS_BEGIN', 'course'); ?>//',
	//	    dataSrc: 'ajax.server.php?r=coursepath/startPath',
	//	    cacheData: true,
	//	    active: true,
	//  	    loadMethod: "POST"
	//
	//	});
	//	mytab.addListener('contentChange', lb.init);
	//	tabView.addTab(mytab, 1);
	//	<?php //if($this->isTabActive('end')): ?>
	//	mytab = new YAHOO.widget.Tab({
	//	    label: '<?php //echo Lang::t('_COMPLETED', 'course'); ?>//',
	//	    dataSrc: 'ajax.server.php?r=coursepath/endPath',
	//	    cacheData: true,
	//  	    loadMethod: "POST"
	//
	//	});
	//	mytab.addListener('contentChange', lb.init);
	//	tabView.addTab(mytab, 2);
	//	<?php //endif; ?>
	//	tabView.appendTo('tab_content');
	//	tabView.getTab(0).addClass('first');
	//
	//	function expandCourses(id_path, type)
	//	{
	//		var div = YAHOO.util.Dom.get('courses_' + id_path);
	//		var link = YAHOO.util.Dom.get('courses_link_' + type + '_' + id_path);
	//
	//		div.style.display = 'block';
	//		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="collapseCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><?php //echo Lang::t('_COLLAPSE', 'coursepath'); ?>//</span> <?php //echo Get::img('course/close.png', Lang::t('_COLLAPSE', 'coursepath')); ?>//</a>';
	//	}
	//
	//	function collapseCourses(id_path, type)
	//	{
	//		var div = YAHOO.util.Dom.get('courses_' + id_path);
	//		var link = YAHOO.util.Dom.get('courses_link_' + type + '_' + id_path);
	//
	//		div.style.display = 'none';
	//		link.innerHTML = '<a class="no_decoration" href="javascript:;" onclick="expandCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><?php //echo Lang::t('_EXPAND', 'coursepath'); ?>//</span> <?php //echo Get::img('course/expand.png', Lang::t('_EXPAND', 'coursepath')); ?>//</a>';
	//	}
	//
	//	function course_search_callback_set() {
	//		var i, tabs = tabView.get("tabs"), activeTab = tabView.get("activeTab");
	//		var ft = YAHOO.util.Dom.get("course_search_filter_text").value;
	//		var fy = YAHOO.util.Dom.get("course_search_filter_year").value;
	//		for (i=0; i<tabs.length; i++) {
	//			tabs[i].set("postData", "filter_text="+ft+"&filter_year="+fy);
	//			tabs[i].set("cacheData", false);
	//		}
	//		tabView.selectTab( tabView.getTabIndex(activeTab) );
	//		activeTab.set("cacheData", false);
	//	}
	//
	//	function course_search_callback_reset() {
	//		var i, tabs = tabView.get("tabs"), activeTab = tabView.get("activeTab");
	//		YAHOO.util.Dom.get("course_search_filter_text").value = "";
	//		YAHOO.util.Dom.get("course_search_filter_year").selectedIndex = 0;
	//		for (i=0; i<tabs.length; i++) {
	//			tabs[i].set("postData", "");
	//			tabs[i].set("cacheData", false);
	//		}
	//		tabView.selectTab( tabView.getTabIndex(activeTab) );
	//		activeTab.set("cacheData", false);
	//	}

</script>