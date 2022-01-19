<div class="middlearea_container--margintop-small">
	<?php
	$lmstab = $this->widget('lms_tab', [
		'active' => 'communication',
		'close' => false
    ]);

	echo Lang::t('_NO_CONTENT', 'games');

	// close the tab structure
	$lmstab->endWidget();
	?>
</div>