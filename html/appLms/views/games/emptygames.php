<div >
	<?php
    $lmstab = $this->widget('lms_tab', [
        'active' => 'games',
        'close' => false,
    ]);

    echo Lang::t('_NO_CONTENT', 'games');

    // close the tab structure
    $lmstab->endWidget();
    ?>
</div>