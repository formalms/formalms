<div class="quick_search_form navbar<?php echo isset($css_class) && $css_class != "" ? " " . $css_class : ""; ?> forma-quick-search-form">
    <?php if ($common_options): ?>
        <div class="common_options">
            <?php echo $common_options; ?>
        </div>
    <?php endif; ?>

    <nav class="navbar">
        <div class="collapse navbar-collapse" id="filter-container">
            <div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">
                <?php $str_search = Lang::t("_SEARCH", 'standard'); ?>

                <div class="navbar-form form-group">
                    <?php if ($auxiliary_filter ): ?>
                    <?php echo $auxiliary_filter ?>
                    <div class="input-group">
                        <a href='#' id='<?php echo $id; ?>_filter_set1'>Applica filtro</a><br><br>
                        <a href='#' id='<?php echo $id; ?>_filter_reset'>Azzera filtro</a>
                    </div>
                    <?php endif; ?>                    
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

            <?php if ($js_callback_set): ?>

            $("#<?php echo $id; ?>_filter_text").on('keydown', 
                function(e){
                    switch (e.which) {
                        case 13: {
                            e.preventDefault();
                            if (typeof saveCurrentFilter ==='function') saveCurrentFilter();                            
                            <?php echo $js_callback_set; ?>();
                        }
                        break;
                    }
            })
            
            $("#<?php echo $id; ?>_filter_set1, #<?php echo $id; ?>_filter_set2").click(
                function(e){
                    e.preventDefault();
                    if (typeof saveCurrentFilter ==='function') saveCurrentFilter();
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

    
    


