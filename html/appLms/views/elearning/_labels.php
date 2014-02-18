<div class="yui-t6">
	<div class="yui-b">
		<?php
		$this->widget('lms_block', array(
			'zone' => 'right',
			'link' => 'elearning/show',
			'block_list' => $block_list
		));
		?>
	</div>
	<div id="yui-main">
		<div class="yui-b">

			<div style="margin:1em;">
				<?php
				$this->widget(
					'lms_tab', array(
					'active' => 'elearning',
					'close' => false));

				echo '<div>';

				foreach($label as $id_common_label => $label_info)
					echo	'<div class="label_container">'
								.'<a class="no_decoration" href="index.php?r=elearning/show&amp;id_common_label='.$id_common_label.'">'
									.'<span class="label_image_cont">'
										.'<img class="label_image" src="'.($label_info['image'] !== '' ? $GLOBALS['where_files_relative'].'/appLms/label/'.$label_info['image'] : Get::tmpl_path('base').'images/course/label_image.png').'" />'
									.'</span>'
									.'<span class="label_info_con">'
										.'<span class="label_title">'.$label_info['title'].'</span>'
										.($label_info['description'] !== '' ? '<br /><span id="label_description_'.$id_common_label.'" class="label_description" title="'.html_entity_decode($label_info['description']).'">'.$label_info['description'].'</span>' : '')
									.'</span>'
								.'</a>'
							.'</div>';
				?>
				</div><!-- Needed to close the label conteiner -->
				</div></div></div><!-- Needed to close the content of the widget manually -->
			</div>
		</div>
	</div>
	<div class="nofloat"></div>
	<div id="label_tooltip"></div>
</div>
<script type="text/javascript">
	var label_elements = YAHOO.util.Selector.query("span[id^=label_description_]");
	var label_tooltip = new YAHOO.widget.Tooltip("label_tooltip",{context: label_elements,width:'250px'});
</script>