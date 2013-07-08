
<?php

Util::widget('kbcategorize', array(
		'original_name'=>$original_name,
		'back_url'=>'index.php?r=alms/kb/add&amp;type='.$r_type,
		'form_url'=>'index.php?r=alms/kb/categorize&amp;type='.$r_type.'&amp;env='.$r_env.'&amp;id='.$r_item_id.'&amp;title='.$original_name,
		'r_item_id'=>$r_item_id,
		'r_type'=>$r_type,
		'r_env'=>$r_env,
	));

?>