<div class="row">
	
	<div class="col-md-12">
	

<!--			<div class="middlearea_container" >-->
                      
				<?php

				$w = $this->widget('lms_tab', array(
	     			'active' => 'elearning',
	     			'close' => false
	    		));

				$w->endWidget();


                
                // select status course
                $_model = new ElearningLms();

//                $_auxiliary = Form::getInputDropdown('', 'course_search_filter_status', 'filter_status', $_model->getFilterStatusCourse(Docebo::user()->getIdst()),Lang::t('_ALL_OPEN', 'course'), '');
//                $_auxiliary = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 0" data-width=""  data-actions-box="true"', $_auxiliary);
                
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
            'link' => 'elearning/show',
            'block_list' => $block_list
        ));
        ?>

    </div>
    -->
      
	<div class="nofloat" ></div>
    
    
 <!-- DIV CONTENT COURSE-LIST  -->       
<div  class="col-md-12" id="div_course">
    <br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>
</div>
 
    




<script type="text/javascript">
	var lb = new LightBox();
	lb.back_url = 'index.php?r=elearning/show&sop=unregistercourse';
    var tabView = new YAHOO.widget.TabView();

    <?php 
    define("IS_AJAX", true);
    ?>

    var posting = $.get('ajax.server.php?r=elearning/all&rnd=<?php echo time(); ?>',
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
        var json_status = $('li.selected').attr('data-value');

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


