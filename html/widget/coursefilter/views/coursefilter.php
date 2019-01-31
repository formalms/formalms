<?php 
$str_search = Lang::t("_SEARCH", 'standard'); 
$str_elearning = Lang::t("_COURSE_TYPE_ELEARNING", 'course'); 
$str_classroom = Lang::t("_CLASSROOM_COURSE", 'cart');
$str_all = Lang::t("_ALL_COURSE_TYPE", 'course');




?>

<div class="quick_search_form navbar<?php echo isset($css_class) && $css_class != "" ? " " . $css_class : ""; ?> forma-quick-search-form">
    <?php if ($common_options): ?>
        <div class="common_options">
            <?php echo $common_options; ?>
        </div>
    <?php endif; ?>

    <nav class="navbar">
        <div class="collapse navbar-collapse" id="filter-container">
            <div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">
                <div class="navbar-form form-group">

                    <?php echo $list_category ? $list_category : ""; ?>
                    <select id="course_search_filter_type" name="filter_type" class="selectpicker" data-width=""
                            data-selected-text-format="count > 1" data-actions-box="true">
                        <option value="all"><?php echo $str_all; ?></option>
                        <option value="elearning"><?php echo $str_elearning; ?></option>
                        <option value="classroom"><?php echo $str_classroom; ?></option>
                    </select>

                    <?php echo $auxiliary_filter ? $auxiliary_filter : ""; ?>
                    <?php echo $_label_list ? $_label_list : ""; ?>
                    
                    <script>
                        var this_user = '<?php echo Docebo::user()->idst ?>';
                        $(function(){
                            v = getCookie(this_user+'.my_course.type');
                            if (v != '') {$("#course_search_filter_type").selectpicker('val', v );}
                            v = getCookie(this_user+'.my_course.year');
                            if (v != '') {$("#course_search_filter_year").selectpicker('val', v);}
                            course_search_callback_set()
                        })
                        
                        $('#course_search_filter_cat').selectpicker({
                            countSelectedText: '{0} <?php echo Lang::t('_SELECTED', 'course'); ?>'
                        });

                        v = getCookie(this_user+'.my_course.category');
                        v = ( v !='')? v.split(','): ["0"]
                        $('#course_search_filter_cat').selectpicker('val',v) 
                        
                        var prev = v;
                        $('#course_search_filter_cat').on('changed.bs.select', function (e) {
                            if ($(this).val() == null)  {
                                // forcing all categories
                                 prev = ["0"];
                                $(this).selectpicker('val', ["0"]);                                
                            } else {
                                selected_value =$(this).val().indexOf("0");
                                prev_0 = prev.indexOf("0");
                                if (selected_value == 0 ) 
                                    if (prev_0 == -1 ) {
                                        //just clicked on  "All category", unselect all categories
                                        prev = ["0"]
                                        $(this).selectpicker('val', ["0"]);
                                    } else {
                                         // just selected a category different from "All category"
                                         new_val = $(this).val();
                                         new_val.shift();
                                         prev = new_val;
                                         $(this).selectpicker('val', new_val);
                                    }
                                }   
                        });    
                        
                    </script>
                    
                    <div class="input-group">
                        <a href='#' id='<?php echo $id; ?>_filter_set1'><?php echo Lang::t('_FILTER_APPLY', 'standard'); ?></a><br><br>
                        <a href='#' id='<?php echo $id; ?>_filter_reset'><?php echo Lang::t('_FILTER_RESET', 'report'); ?></a>
                    </div>


                    <div class="input-group">
                        <?php echo Form::getInputTextfield("form-control", $id . "_filter_text", "filter_text", $filter_text, '', 255, 'equired data-toggle="popover" data-content="' . Lang::t('_INSERT', 'standard') . " " . strtolower(Lang::t('_COURSE_NAME', 'standard')) . '" placeholder=' . $str_search); ?>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default" id="<?php echo $id . "_filter_set2"; ?>"
                                    name="filter_set" title="<?php echo Lang::t('_SEARCH', 'standard'); ?>">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($inline_filters) : ?>
          <div class="navbar-extra"><?php echo $inline_filters; ?></div>
        <?php endif ?>

        <div class="navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#filter-container">
                <span class="filter-label filter-open">
                    <?php echo Lang::t("_FILTER_TAB_OPEN", 'standard'); ?>
                </span>
                <span class="filter-label filter-closed">
                    <?php echo Lang::t("_FILTER_TAB_CLOSE", 'standard'); ?>
                </span>
            </button>
        </div>
    </nav>



</div>


<?php if ($js_callback_set || $js_callback_reset): ?>
    <script type="text/javascript">

            <?php if ($js_callback_set): ?>

            $("#<?php echo $id; ?>_filter_text").on('keydown', 
                function(e){
                    switch (e.which) {
                        case 13: {
                            e.preventDefault();
                            saveCurrentFilter();                            
                            <?php echo $js_callback_set; ?>();
                        }
                        break;
                    }
            })
            
            $("#<?php echo $id; ?>_filter_set1, #<?php echo $id; ?>_filter_set2").click(
                function(e){
                    e.preventDefault();
                    saveCurrentFilter();
                    <?php echo $js_callback_set; ?>();
            })
            <?php endif; ?>

            <?php if ($js_callback_reset): ?>
             $("#<?php echo $id; ?>_filter_reset").click(
                function(e) {
                    e.preventDefault();
                    <?php echo $js_callback_reset; ?>();
             }); 
            <?php endif; ?>

        

        
        
    </script>
<?php endif; ?>