<?php

echo Form::openForm('add_location', 'ajax.adm_server.php?r=alms/location/insertLocation')

	.Form::getTextfield(
		Lang::t('_LOCATION', 'lms'),
		'location',
		'location',
		255,
		$location->location
	)

	.Form::closeForm();
