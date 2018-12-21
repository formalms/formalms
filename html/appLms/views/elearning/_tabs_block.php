<div class="row">
	
	<div class="col-md-12">
	



                <?php
                
                
                $_model = new ElearningLms();
                $count = 0;
                $statusFilters = $_model->getFilterStatusCourse(Docebo::user()->getIdst());

                $html = '<ul class="nav nav-pills">';

                while( list($key, $value) = each($statusFilters) ) {

                    if ($count === 0) {
                        $html .= '<li class="selected js-label-menu-filter" data-value="' . $key . '">';
                    } else {
                        $html .= '<li class="js-label-menu-filter" data-value="' . $key . '">';
                    }

                    $html .= '<a class="icon--filter-' . $key . '" href="#" >' . $value . '</a>';
                    $html .= '</li>';
                    $count++;
                }
                $html .= '</ul>';                        

                
                if ($use_label) {
                     $html .= '<span style="float:right">'.Form::getInputLabelDropdown('course_search_label', 'filter_label', $label, $current_label).'</span>';                    
                    
                }



                $inline_filters = $html;

				$w = $this->widget('lms_tab', array(
	     			'active' => 'elearning',
	     			'close' => false
	    		));

                // select status course
                $_model = new ElearningLms();

                // select year
                $_auxiliary = Form::getInputDropdown('', 'course_search_filter_year', 'filter_year', $_model->getFilterYears(Docebo::user()->getIdst()), 0, '');
                $_auxiliary = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width=""  data-actions-box="true"', $_auxiliary);
                
                                                       
                $_list_category = Form::getInputDropdown('', 'course_search_filter_cat', 'filter_cat', $_model->getListCategory(Docebo::user()->getIdst(),false), 0, '');
                $_list_category = str_replace('class="form-control "', 'class="selectpicker"  data-selected-text-format="count > 1" data-width="" multiple data-actions-box="true"', $_list_category);

                $this->widget('coursefilter', array(
                    'id' => 'course_search',
                    'filter_text' => "",
                    'list_category' => $_list_category,
                    'auxiliary_filter' => $_auxiliary,
                    'inline_filters' => $inline_filters,
                    'js_callback_set' => 'course_search_callback_set',
                    'js_callback_reset' => 'course_search_callback_reset',
                    'css_class' => 'nav'
                ));
				?>
                

                


	
	</div>
    
      
  <div class="nofloat" ></div>
    
    
 <!-- DIV CONTENT COURSE-LIST  -->       
<div  class="col-md-12" id="div_course">
    <br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>
</div>
 

<script type="text/javascript">

    <?php if ($use_label): ?>
    $('#course_search_label').on('changed.bs.select', function (e) {
            var id_common_label =  $("#course_search_label").selectpicker().val();
            $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
            urlCall = 'ajax.server.php?r=elearning/show&id_common_label='+id_common_label 
            var posting = $.get( urlCall, {})
            posting.done(function(responseText){
                $("#div_course").html(responseText);
            });
    })
    <?php endif;?>    
    
    $('.js-label-menu-filter').on('click', function () {
        $(this).addClass('selected').siblings().removeClass('selected');
        saveCurrentFilter();
        course_search_callback_set();
    });
    
    function saveCurrentFilter(){
        var this_user = '<?php echo Docebo::user()->idst ?>'
        var ctype = $('#course_search_filter_type').selectpicker().val();
        setCookie(this_user+'.my_course.type',ctype,60,"/")
        var category = $('#course_search_filter_cat').selectpicker().val();
        setCookie(this_user+'.my_course.category',category,60,"/")
        var cyear = $("#course_search_filter_year").selectpicker().val();
        setCookie(this_user+'.my_course.year',cyear,60,"/")        
        
    }

    function clearCurrentFilter(){
        var this_user = '<?php echo Docebo::user()->idst ?>'
        prev = ["0"];
        setCookie(this_user+'.my_course.type',"",3650,"/")
        setCookie(this_user+'.my_course.category',"",-3650,"/")
        setCookie(this_user+'.my_course.year',"",-3650,"/")
    }
    
    
	function course_search_callback_set() {
        
        <?php if ($use_label): ?>
        var id_common_label =  $("#course_search_label").selectpicker().val();
        if(id_common_label == 0 ) {
            $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
            var posting = $.get( 'ajax.server.php?r=elearning/allLabel', {})
            posting.done(function(responseText){
                $("#div_course").html(responseText);
            }); 
            return   
        }
        <?php endif;?>
        var ft = $("#course_search_filter_text").val();

        var ctype = $("#course_search_filter_type").selectpicker().val();
        var category = $('#course_search_filter_cat').selectpicker().val();
        var cyear = $("#course_search_filter_year").selectpicker().val();
        var json_status = $('.js-label-menu-filter.selected').attr('data-value');

        $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
        var posting = $.get( 'ajax.server.php?r=elearning/all&rnd=<?php echo time(); ?>&filter_text=' + ft + '&filter_type=' + ctype + '&filter_cat=' + category + '&filter_status=' + json_status + '&filter_year=' + cyear, {}

        );
                  
        posting.done(function(responseText){
            $("#div_course").html(responseText);
        });
	}

	function course_search_callback_reset() {
        clearCurrentFilter();
        $("#course_search_filter_year").selectpicker('val', 0);
        $("#course_search_filter_type").selectpicker('val', 'all');
        $("#course_search_filter_cat").selectpicker('val', [0]);
        $("#course_search_filter_text").val("")
        course_search_callback_set()
	}
</script>


