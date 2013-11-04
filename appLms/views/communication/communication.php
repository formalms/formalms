<?php if( empty($communications) ) : ?>

	<p><?php echo Lang::t('_NO_ENTRIES', 'standard'); ?></p>

<?php endif; ?>
<?php foreach( $communications as $comm ) : ?>

	<div class="list_block">
		<h2 class="heading" style="display: inline"><?php echo $comm['title']; ?></h2>
		<p class="action" style="display: inline">			
			<span class="related_date"><?php
			echo Lang::t('_PUBLISHED', 'communication').': '.Format::dateDistance($comm['publish_date']);
			?></span>
		</p>
		<p class="content">
			<?php echo ( $comm['description'] != '' ? $comm['description'] : '' ); ?>
		</p>
		<p class="action"><?php
			switch($comm['type_of']) {
				case "none" : {
					echo '<a class="ico-wt-sprite subs_unread" href="index.php?r=communication/play&amp;id_comm='.$comm['id_comm'].'"><span>'
					.Lang::t('_MARK_AS_READ', 'communication')
					.'</span></a>';
				};break;
				case "file" : {
					echo '<a class="ico-wt-sprite subs_download" href="index.php?r=communication/play&amp;id_comm='.$comm['id_comm'].'"><span>'
					.Lang::t('_DOWNLOAD', 'communication')
					.'</span></a>';
				};break;
				case "scorm" : {
					echo '<a class="ico-wt-sprite subs_play" rel="lightbox" href="index.php?r=communication/play&amp;id_comm='.$comm['id_comm'].'" title="'.$comm['title'].'"><span>'
					.Lang::t('_PLAY', 'communication')
					.'</span></a>';
				};break;
			}
			?>
		</p>
	</div>

<?php endforeach; ?>