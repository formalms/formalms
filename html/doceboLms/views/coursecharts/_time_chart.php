<div>
	<h3><?php echo $title; ?></h3>
	<div class="align_center">
		<div style="width:90%;height:600px;margin:0 auto;" id="<?php echo $id; ?>"></div>
	</div>
	<script type="text/javascript">
		var Chart_<?php echo $id; ?> = createTimeChart("<?php echo $id; ?>", <?php echo $js_data; ?>);
	</script>
</div>
<br />