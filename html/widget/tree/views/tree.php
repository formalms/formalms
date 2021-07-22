<?php if (!$runtime) { ?>
<div class="folder_tree" id="<?php echo $id; ?>"></div>
<?php } ?>
<?php
if ($rel_action != "") echo '<div class="folder_action_space">'.$rel_action.'</div>';
?>
<script type="text/javascript">
var TreeView_<?php echo $id; ?>;

<?php if ($runtime) { ?>
YAHOO.namespace("runtimeWidgets");
YAHOO.runtimeWidgets["<?php echo $id; ?>"] = function(o) {
	var x, options = <?php echo $options; ?>;
	if (YAHOO.lang.isObject(o)) {
		for (x in o) options[x] = o[x];
	}
	TreeView_<?php echo $id; ?> = new <?php echo $treeClass; ?>("<?php echo $id; ?>", options);
}
<?php } else { ?>
YAHOO.util.Event.onDOMReady(function() {
	TreeView_<?php echo $id; ?> = new <?php echo $treeClass; ?>("<?php echo $id; ?>", <?php echo $options; ?>);
});
<?php } ?>
</script>