<div class="quick_search_form<?php echo isset($css_class) && $css_class != "" ? " ".$css_class : ""; ?>">
	<div>
		<?php if ($common_options): ?>
			<div class="common_options">
				<?php echo $common_options; ?>
			</div>
		<?php endif; ?>
		<div class="simple_search_box" id="<?php echo $id; ?>_simple_filter_options" style="display: block;">
			<?php
				echo $auxiliary_filter ? $auxiliary_filter."&nbsp;&nbsp;&nbsp;" : "";
				echo Form::getInputTextfield("search_t", $id."_filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton($id."_filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton($id."_filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
		<?php if ($advanced_filter_content): ?>
		<a id="<?php echo $id; ?>_advanced_search" class="advanced_search" href="javascript:;"><?php echo Lang::t("_ADVANCED_SEARCH", 'standard'); ?></a>
		<div id="advanced_search_options" class="advanced_search_options" style="display:<?php echo $advanced_filter_active ? 'block' : 'none'; ?>;">
			<?php echo $advanced_filter_content; ?>
		</div>
		<?php endif; ?>
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