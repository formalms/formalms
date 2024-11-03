<?php
$str_search = Lang::t("_SEARCH", 'standard');
$str_elearning = Lang::t("_COURSE_TYPE_ELEARNING", 'course');
$str_classroom = Lang::t("_CLASSROOM_COURSE", 'cart');
$str_all = Lang::t("_ALL_COURSE_TYPE", 'course');




?>

<nav class="forma-quick-search-form filterBar quick_search_form navbar<?php echo isset($css_class) && $css_class != '' ? ' ' . $css_class : ''; ?>" aria-label="<?php Lang::t('_COURSE_SEARCH_BAR', 'menu_course') ?>">
    <div>
        <?php echo $common_options; ?>
    </div>
    <div class="filterBar__legacyContainer">
        <div class="filterBar__advanced collapse navbar-collapse" id="filter-container">
            <div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">


                <div class="filterBar__mainsearch navbar-form form-group">
                    <fieldset>
                        <legend for="course_search_filter_cat" class="screenreader"><?php echo Lang::t('_CATEGORY_SELECTED', 'course') ?></legend>
                        <label class="screenreader" for="course_search_filter_cat"><?php echo Lang::t('_CATEGORY_SELECTED', 'course') ?></label>
                        <?php echo $select_category ? $select_category : ''; ?>

                        <legend for="course_search_filter_type" class="screenreader"><?php echo Lang::t('_COURSE_TYPE_SELECTION', 'course') ?></legend>
                        <label class="screenreader" for="course_search_filter_type"><?php echo Lang::t('_COURSE_TYPE_SELECTION', 'course') ?></label>
                        <?php echo $select_course_type ? $select_course_type : ''; ?>

                        <legend for="course_search_filter_year" class="screenreader"><?php echo Lang::t('_YEAR_SELECTION', 'course') ?></legend>
                        <label class="screenreader" for="course_search_filter_year"><?php echo Lang::t('_YEAR_SELECTION', 'course') ?></label>
                        <?php echo $select_year ? $select_year : ''; ?>

                        <?php echo isset($_label_list) ? $_label_list : ''; ?>
                    </fieldset>
                    <script>
                        var this_user = '<?php echo \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(); ?>';
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
                    

                    <div class="filterBar__searchAndReset input-group">
                        <a href='#' class="filterBar__search" id='<?php echo $id; ?>_filter_set1'><?php echo Lang::t('_FILTER_APPLY', 'standard'); ?></a>
                        <a href='#' class="filterBar__reset" id='<?php echo $id; ?>_filter_reset'><?php echo Lang::t('_FILTER_RESET', 'standard'); ?></a>
                    </div>


                    <div class="filterBar__searchInput input-group">
                        <label for="course_search_filter_text">
                            <span class="screenreader"><?php echo Lang::t('_SEARCH_COURSE', 'menu_course'); ?></span>
                        </label>
                        <?php echo Form::getInputTextfield('form-control', $id . '_filter_text', 'filter_text', $filter_text, '', 255, 'equired data-toggle="popover" data-content="' . Lang::t('_INSERT', 'standard') . ' ' . strtolower(Lang::t('_COURSE_NAME', 'standard')) . '" placeholder=' . $str_search); ?>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default course-icon-position" id="<?php echo $id . '_filter_set2'; ?>"
                                    name="filter_set" title="<?php echo Lang::t('_SEARCH', 'standard'); ?>">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </div>
                    </div><!-- filterBar__searchInput -->

                </div><!-- filterBar__mainsearch -->



            </div>
        </div>





        <?php if ($inline_filters) { ?>
          <div class="filterBar__buttons navbar-extra"><?php echo $inline_filters; ?></div>
        <?php } ?>





        <div class="filterBar__mobile navbar-header">
            <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#filter-container">
                <span class="filter-label filter-open">
                    <?php echo Lang::t('_FILTER_TAB_OPEN', 'standard'); ?>
                </span>
                <span class="filter-label filter-closed">
                    <?php echo Lang::t('_FILTER_TAB_CLOSE', 'standard'); ?>
                </span>
            </button>
        </div>



    </div>
</nav>



<?php if ($js_callback_set || $js_callback_reset) { ?>
    <script type="text/javascript">

            <?php if ($js_callback_set) { ?>

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
            <?php } ?>

            <?php if ($js_callback_reset) { ?>
             $("#<?php echo $id; ?>_filter_reset").click(
                function(e) {
                    e.preventDefault();
                    <?php echo $js_callback_reset; ?>();
             }); 
            <?php } ?>

        

        
        
    </script>
<?php } ?>