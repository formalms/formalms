<style type=text/css>

.popover.primary {
    border-color:#337ab7;
}
.popover.primary>.arrow {
    border-top-color:#337ab7;
}
.popover.primary>.popover-title {
    color:#fff;
    background-color:#337ab7;
    border-color:#337ab7;
}
.popover.success {
    border-color:#d6e9c6;
}
.popover.success>.arrow {
    border-top-color:#d6e9c6;
}
.popover.success>.popover-title {
    color:#3c763d;
    background-color:#dff0d8;
    border-color:#d6e9c6;
}
.popover.info {
    border-color:#bce8f1;
}
.popover.info>.arrow {
    border-top-color:#bce8f1;
}
.popover.info>.popover-title {
    color:#31708f;
    background-color:#d9edf7;
    border-color:#bce8f1;
}
.popover.warning {
    border-color:#faebcc;
}
.popover.warning>.arrow {
    border-top-color:#faebcc;
}
.popover.warning>.popover-title {
    color:#8a6d3b;
    background-color:#fcf8e3;
    border-color:#faebcc;
}
.popover.danger {
    border-color:#ebccd1;
}
.popover.danger>.arrow {
    border-top-color:#ebccd1;
}
.popover.danger>.popover-title {
    color:#a94442;
    background-color:#f2dede;
    border-color:#ebccd1;
}

</style>


     




<div class="quick_search_form navbar<?php echo isset($css_class) && $css_class != "" ? " ".$css_class : ""; ?>">
	<div>
		<?php if ($common_options): ?>
			<div class="common_options">
				<?php echo $common_options; ?>
			</div>
		<?php endif; ?>
        
		<div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">
	
			<?php $str_search = Lang::t("_SEARCH", 'standard'); ?>
            <?php $str_elearning = Lang::t("_COURSE_TYPE_ELEARNING", 'course'); ?>
            <?php $str_classroom = Lang::t("_CLASSROOM_COURSE", 'cart'); ?>
            <?php $str_all = Lang::t("_ALL", 'standard'); ?>            
            
			<div class="navbar-form form-group">
				<!-- <span class="navbar-text">Filtra:</span> -->

                
 <style type="text/css">
    .bootstrap-select:not([class*="col-"]):not([class*="form-control"]):not(.input-group-btn) {
        width: 150px;
    }
    
    .bootstrap-select.btn-group .dropdown-menu {
        min-width: 250px;    
        
    }
    
 </style>               
 
 
   <?php echo $list_category ? $list_category : ""; ?>
      
                <select id="course_search_filter_type"  name="filter_type" class="selectpicker"  data-width="150px"   data-selected-text-format="count > 1" data-width="150px"  data-actions-box="true">
                    <option value="all"><?php echo $str_all; ?></option> 
                    <option value="elearning" selected="selected"><?php echo $str_elearning; ?></option>
                    <option value="classroom"><?php echo $str_classroom; ?></option>                   
                </select>

                
                <?php echo $auxiliary_filter ? $auxiliary_filter : ""; ?>
                                
                

 <script>
        $('.selectpicker').selectpicker({
            selectAllText: '<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>',
            deselectAllText: '<span class="glyphicon glyphicon-remove" aria-hidden="true"></span>'     ,
            noneSelectedText:'<?php echo Lang::t('_SELECTED', 'course'); ?>'  ,
            countSelectedText: '{0} selezioni'  
        }); 
   </script>

    

				<div class="input-group">
					<?php echo Form::getInputTextfield("form-control", $id."_filter_text", "filter_text", $filter_text, '', 255, 'equired data-toggle="popover" data-content="'.Lang::t('_INSERT', 'standard')." ".strtolower (Lang::t('_COURSE_NAME', 'standard')) .'" placeholder='.$str_search ); ?>
					<div class="input-group-btn">
						<button type="submit" class="btn btn-default" id="<?php echo $id."_filter_set"; ?>" name="filter_set" title="<?php echo Lang::t('_SEARCH', 'standard'); ?>">
							<span class="glyphicon glyphicon-search"></span>
						</button>
					</div>
				</div>
			</div>
		</div>    
		  
		<?php /*
		<a id="<?php echo $id; ?>_advanced_search" class="advanced_search" href="javascript:;"><?php echo Lang::t("_ADVANCED_SEARCH", 'standard'); ?></a>
		<div id="advanced_search_options" class="advanced_search_options" style="display:<?php echo $advanced_filter_active ? 'block' : 'none'; ?>;">
			 <?php echo $advanced_filter_content; ?>
		</div>
		*/ ?>
    </div>
    
</div>

<?php if ($js_callback_set || $js_callback_reset): ?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		var E = YAHOO.util.Event, D = YAHOO.util.Dom;

		<?php if ($js_callback_set): ?>
		E.addListener("<?php echo $id; ?>_filter_text", "keypress", function(e) {
			switch (E.getCharCode(e)) {
				case 13: {
					E.preventDefault(e);
					<?php echo $js_callback_set; ?>.call(this);
				} break;
			}
		});

		E.addListener("<?php echo $id; ?>_filter_set", "click", function(e) {
			E.preventDefault(e);
			<?php echo $js_callback_set; ?>.call(D.get("<?php echo $id; ?>_filter_text"));
		});
		<?php endif; ?>

		<?php if ($js_callback_reset): ?>
		E.addListener("<?php echo $id; ?>_filter_reset", "click", function(e) {
			E.preventDefault(e);
			<?php echo $js_callback_reset; ?>.call(D.get("<?php echo $id; ?>_filter_text"));
		});
		<?php endif; ?>
	});
</script>
<?php endif; ?>




 <script type="text/javascript">
      
      $(document).ready(function(){

//minimum 8 characters
var bad = /(?=.{8,}).*/;
//Alpha Numeric plus minimum 8
var good = /^(?=\S*?[a-z])(?=\S*?[0-9])\S{8,}$/;
//Must contain at least one upper case letter, one lower case letter and (one number OR one special char).
var better = /^(?=\S*?[A-Z])(?=\S*?[a-z])((?=\S*?[0-9])|(?=\S*?[^\w\*]))\S{8,}$/;
//Must contain at least one upper case letter, one lower case letter and (one number AND one special char).
var best = /^(?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9])(?=\S*?[^\w\*])\S{8,}$/;

$('#password').on('keyup', function () {
    var password = $(this);
    var pass = password.val();
    var passLabel = $('[for="password"]');
    var stength = 'Weak';
    var pclass = 'danger';
    if (best.test(pass) == true) {
        stength = 'Very Strong';
        pclass = 'success';
    } else if (better.test(pass) == true) {
        stength = 'Strong';
        pclass = 'warning';
    } else if (good.test(pass) == true) {
        stength = 'Almost Strong';
        pclass = 'warning';
    } else if (bad.test(pass) == true) {
        stength = 'Weak';
    } else {
        stength = 'Very Weak';
    }

    var popover = password.attr('data-content', stength).data('bs.popover');
    popover.setContent();
    popover.$tip.addClass(popover.options.placement).removeClass('danger success info warning primary').addClass(pclass);

});

$('input[data-toggle="popover"]').popover({
    placement: 'top',
    trigger: 'focus'
});

})
      
      </script>