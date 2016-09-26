<?php
YuiLib::load(array('animation' => 'my_animation', 'container' => 'container-min', 'container' => 'container_core-min'));
echo Util::get_js(Get::rel_path('lms') . '/views/catalog/catalog.js', true);

require_once(_lms_ . '/lib/lib.middlearea.php');
$ma = new Man_MiddleArea();
?>
<script type="text/javascript">
	YAHOO.util.Event.onDOMReady(function() {
		initialize("<?php echo Lang::t('_UNDO', 'standard'); ?>");
	});
</script>


