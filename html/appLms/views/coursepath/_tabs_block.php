    <div class="row">
        <div class="col-md-12">
                 <?php
                $w = $this->widget('lms_tab', array(
                    'active' => 'coursepath',
                    'close' => false
                ));
               

                $this->widget('tablefilter', array(
                    'id' => 'course_search',
                    'filter_text' => "",
                    'js_callback_set' => 'course_search_callback_set',
                    'js_callback_reset' => 'course_search_callback_reset',
                     'inline_filters' => $inline_filters,
                    'css_class' => 'nav'
                ));




                ?>
         </div>

    </div>


 <!-- DIV CONTENT COURSE-LIST  -->       
<div  class="col-md-12" id="div_course">
    <br><p align="center"><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>
</div>
 

<script type="text/javascript">

    var posting = course_search_callback_set()


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

        
        var json_status = $('.js-label-menu-filter.selected').attr('data-value');

        $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path() ?>images/standard/loadbar.gif'></p>");
        var posting = $.get( 'ajax.server.php?r=coursepath/all&rnd=<?php echo time(); ?>&filter_text=' + ft + '&filter_status=' + json_status, {}

        );
                  
        posting.done(function(responseText){
            $("#div_course").html(responseText);
        });
    }

    function course_search_callback_reset() {

    }
</script>