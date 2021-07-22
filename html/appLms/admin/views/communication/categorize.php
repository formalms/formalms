
<?php

Util::widget('kbcategorize', array(
	'original_name'=>$data['title'],
	'r_item_id'=>$data['id_resource'],
	'r_type'=>$data['type_of'],
	'r_env'=>'communication',
	'r_env_parent_id'=>$id_comm,
	'r_param'=>$r_param,
	'back_url'=>$back_url,
	'form_url'=>$form_url,
	));

?>