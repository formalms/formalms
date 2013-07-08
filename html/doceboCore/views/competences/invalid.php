<?php
//just print an error message with back urls
$back_ui = getBackUi($back_url, Lang::t('_BACK', 'standard'));
echo $back_ui.'<br><p>'.$message.'</p></br>'.$back_ui;
?>