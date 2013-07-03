<div style="margin:1em;">
	<?php
	$lmstab = $this->widget('lms_tab', array(
		'active' => 'communication',
		'close' => false
	));

	echo Lang::t('_NO_CONTENT', 'games');

	// close the tab structure
	$lmstab->endWidget();
	?>
</div>