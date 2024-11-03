    <div class="row">
        <div class="col-md-12">
                 <?php
                $w = $this->widget('lms_tab', [
                    'active' => 'coursepath',
                    'close' => false,
                ]);

                $_model = new CoursepathLms();
                $statusFilters = $_model->getFilterStatusLearningPath(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());

                $html = '<ul class="nav nav-pills">';
                $html .= '<li class="selected js-label-menu-filter" data-value="' . $key . '">';

                foreach ($statusFilters as $key => $value) {
                    $html .= '<li class="js-label-menu-filter" data-value="' . $key . '">';
                    $html .= '<a class="icon--filter-' . $key . '" href="#" >' . $value . '</a>';
                    $html .= '</li>';
                }

                $html .= '</ul>';

                $inline_filters = $html;


                $this->widget('tablefilter', [
                    'id' => 'coursepath_search',
                    'filter_text' => '',
                    'js_callback_set' => 'coursepath_search_callback_set',
                    'js_callback_reset' => 'coursepath_search_callback_reset',
                    'inline_filters' => $inline_filters,
                    'css_class' => 'nav',
                ]);

                ?>
         </div>

    <div class="nofloat" ></div>


 <!-- DIV CONTENT COURSE-LIST  -->       
<div  class="col-md-12" id="div_course">
    <br><p align="center"><img src='<?php echo Layout::path(); ?>images/standard/loadbar.gif'></p>
</div>
 

<script type="text/javascript">

    var this_user = '<?php echo \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(); ?>';
    $(function(){
        v = getCookie(this_user+'.my_coursepath.year');
        if (v != '') {$("#coursepath_search_filter_year").selectpicker('val', v);}
        coursepath_search_callback_set()
    })

    $('.js-label-menu-filter').on('click', function () {
        $(this).addClass('selected').siblings().removeClass('selected');
        saveCurrentFilter();
        coursepath_search_callback_set();
    });
    
    function saveCurrentFilter(){
        var this_user = '<?php echo \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(); ?>'
        var cyear = $("#coursepath_search_filter_year").selectpicker().val();
        setCookie(this_user+'.my_coursepath.year',cyear,60,"/")        
        
    }

    function clearCurrentFilter(){
        var this_user = '<?php echo \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(); ?>'
        setCookie(this_user+'.my_coursepath.year',"",-3650,"/")
    }
    
    
    function coursepath_search_callback_set() {
        var ft = $("#coursepath_search_filter_text").val();
        var cyear = $("#coursepath_search_filter_year").selectpicker().val();
        var status = $('.js-label-menu-filter.selected').attr('data-value');


        $("#div_course").html("<br><p align='center'><img src='<?php echo Layout::path(); ?>images/standard/loadbar.gif'></p>");
        var posting = $.get( 'ajax.server.php',
                    {r: 'coursepath/all',
                     rnd:'<?php echo time(); ?>',
                     filter_status: status,
                     filter_text:ft,
                     filter_year:cyear 
                    }
        );
                  
        posting.done(function(responseText){
            $("#div_course").html(responseText);
        });
    }

    function coursepath_search_callback_reset() {
        clearCurrentFilter();
        $("#coursepath_search_filter_year").selectpicker('val', '0');
        $("#coursepath_search_filter_text").val("")
        coursepath_search_callback_set()
    }
    
    
    function expandCourses(id_path, type){

        $("#courses_"+id_path).css("display", "block");
        
        a = '<a class="no_decoration" href="javascript:;" onclick="collapseCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><STRONG><?php echo Lang::t('_COLLAPSE', 'coursepath'); ?></STRONG></span> <?php echo FormaLms\lib\Get::img('course/close.png', Lang::t('_COLLAPSE', 'coursepath')); ?></a>';
        
        $("#courses_link_" + type + "_" + id_path).html(a)

    }
    
    
    function collapseCourses(id_path, type){
        $("#courses_"+id_path).css("display", "none");

        a = '<a class="no_decoration" href="javascript:;" onclick="expandCourses(\'' + id_path + '\',\'' + type + '\');"><span class="expand_path_info"><strong><?php echo Lang::t('_EXPAND', 'coursepath'); ?></strong></span> <?php echo FormaLms\lib\Get::img('course/expand.png', Lang::t('_EXPAND', 'coursepath')); ?></a>';
        
        $("#courses_link_" + type + "_" + id_path).html(a)
        
    }
    
    
</script>