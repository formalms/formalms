<div style="margin:1em;">
	<?php
	$this->widget('lms_tab', array(
		'active' => 'home'
	));
	?>
	
</div>
<script type="text/javascript">
	document.getElementById('tab_content').innerHTML = '<?php echo addslashes($_content); ?>';
</script>