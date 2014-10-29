<div class="yui-t6">
	<div class="yui-b">
		<?php
		$this->widget('lms_block', array(
			'zone' => 'right',
			'link' => 'coursepath/show',
			'block_list' => $block_list
		));
		?>
	</div>
	
	<!-- welcome page (main tab) -->
	<div id="yui-main">
		<div class="yui-b">

			<div style="margin:1em; float">
				<?php
				$this->widget('lms_tab', array(
					'active' => 'home'
				));
				?>
			</div>

		</div>
	</div>
	<div class="nofloat"></div>
</div>
<script type="text/javascript">
	document.getElementById('tab_content').innerHTML = '<?php echo addslashes($_content); ?>';
</script>