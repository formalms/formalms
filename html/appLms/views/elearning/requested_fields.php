<?php echo getTitleArea(Lang::t('_MYCOURSES_FIELDMASK_TITLE', 'catalogue')); ?>
<div class="std_block">
<?php

	$m_fields = $field_manager->getUserMandatoryFields($user_idst);

	echo '<p>'.Lang::t('_MYCOURSES_FIELDMASK_INTEST', 'catalogue').'</p>'
		.Form::openForm('request_compile', 'index.php?r=elearning/fields');

	foreach ($m_fields as $id_field => $m_field) {

		echo $field_manager->playFieldForUser($user_idst, $id_field, false, true);
	}
	echo Form::openButtonSpace()
		.Form::getButton('save', 'save', Lang::t('_SAVE'))
		.Form::closeButtonSpace()
		.Form::closeForm();
?>
</div>