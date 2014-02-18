<?php

echo Form::openForm('upd_location', 'ajax.adm_server.php?r=alms/location/updatelocation')

	.Form::gethidden('location_id','location_id',$location->location_id)
	
	.Form::getTextfield(
		Lang::t('_NEW', 'standard'),
		'location_new',
		'location_new',
		255,
		$location->location
	)
	.Form::closeForm();
