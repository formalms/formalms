<div class="quick_search_form navbar<?php echo isset($css_class) && $css_class != "" ? " " . $css_class : ""; ?> forma-quick-search-form">
    <?php if ($common_options): ?>
        <div class="common_options">
            <?php echo $common_options; ?>
        </div>
    <?php endif; ?>

    <nav class="navbar">
<!--            <div class="container-fluid">-->
        <div class="collapse navbar-collapse" id="filter-container">
            <div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">
                <?php $str_search = Lang::t("_SEARCH", 'standard'); ?>
                <?php $str_elearning = Lang::t("_COURSE_TYPE_ELEARNING", 'course'); ?>
                <?php $str_classroom = Lang::t("_CLASSROOM_COURSE", 'cart'); ?>
                <?php $str_all = Lang::t("_ALL", 'standard'); ?>

                <div class="navbar-form form-group">

                    <?php echo $list_category ? $list_category : ""; ?>

                    <select id="course_search_filter_type" name="filter_type" class="selectpicker" data-width=""
                            data-selected-text-format="count > 1" data-actions-box="true">
                        <option value="all"><?php echo $str_all; ?></option>
                        <option value="elearning" selected="selected"><?php echo $str_elearning; ?></option>
                        <option value="classroom"><?php echo $str_classroom; ?></option>
                    </select>

                    <?php echo $auxiliary_filter ? $auxiliary_filter : ""; ?>

                    <script>
                        $('.selectpicker').selectpicker({
                            countSelectedText: '{0} <?php echo Lang::t('_SELECTED', 'course'); ?>'
                        });
                        
                        var prev = ["0"];
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
                        <?php echo Form::getInputTextfield("form-control", $id . "_filter_text", "filter_text", $filter_text, '', 255, 'equired data-toggle="popover" data-content="' . Lang::t('_INSERT', 'standard') . " " . strtolower(Lang::t('_COURSE_NAME', 'standard')) . '" placeholder=' . $str_search); ?>
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-default" id="<?php echo $id . "_filter_set"; ?>"
                                    name="filter_set" title="<?php echo Lang::t('_SEARCH', 'standard'); ?>">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php echo $inline_filters ? $inline_filters : ''; ?>

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
        YAHOO.util.Event.onDOMReady(function () {
            var E = YAHOO.util.Event, D = YAHOO.util.Dom;

            <?php if ($js_callback_set): ?>
            E.addListener("<?php echo $id; ?>_filter_text", "keypress", function (e) {
                switch (E.getCharCode(e)) {
                    case 13: {
                        E.preventDefault(e);
                        <?php echo $js_callback_set; ?>.
                        call(this);
                    }
                        break;
                }
            });

            E.addListener("<?php echo $id; ?>_filter_set", "click", function (e) {
                E.preventDefault(e);
                <?php echo $js_callback_set; ?>.
                call(D.get("<?php echo $id; ?>_filter_text"));
            });
            <?php endif; ?>

            <?php if ($js_callback_reset): ?>
            E.addListener("<?php echo $id; ?>_filter_reset", "click", function (e) {
                E.preventDefault(e);
                <?php echo $js_callback_reset; ?>.
                call(D.get("<?php echo $id; ?>_filter_text"));
            });
            <?php endif; ?>
        });
    </script>
<?php endif; ?>