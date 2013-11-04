<?php Get::title(array(
	'index.php?r=alms/location/show' => Lang::t('_LOCATION', 'classroom'),
	'index.php?r=alms/location/show_classroom&amp;id_location='.( $_is_editing ? $info->location_id : $id_location ) => Lang::t('_CLASSROOM', 'classroom'),
	Lang::t($_is_editing ? '_MOD' : '_ADD', 'standard')
)); ?>
<div class="std_block">
<?php
	echo getBackUi('index.php?r=alms/location/show_classroom&amp;id_location='.( $_is_editing ? $info->location_id : $id_location ), Lang::t('_BACK'));

	echo Form::openForm('classroom_form', ($_is_editing ? 'index.php?r=alms/location/saveclassroom' : 'index.php?r=alms/location/insertclassroom') );

	echo Form::getTextfield(Lang::t('_NAME', 'classroom'), 'name', 'name', 255, $_is_editing ? $info->name : "");
	echo Form::getTextarea(Lang::t('_DESCRIPTION', 'classroom'), 'description', 'description', $_is_editing ? $info->description : "");
	if (!$_is_editing)echo Form::getHidden('id_location', 'id_location', (int)$id_location);
	if ($_is_editing)  echo Form::getHidden('id_classroom', 'id_classroom', $info->idClassroom);
	if ($_is_editing)  echo Form::getHidden('id_location', 'id_location', $info->location_id);
	echo Form::getTextfield(Lang::t('_BUILDING_ROOM', 'classroom'), 'room', 'room', 255, $_is_editing ? $info->room : "");
	echo Form::getTextfield(Lang::t('_CAPACITY', 'classroom'), 'capacity', 'capacity', 255, $_is_editing ? $info->capacity : "");
	echo Form::getTextfield(Lang::t('_RESPONSABLE', 'classroom'), 'responsable', 'responsable', 255, $_is_editing ? $info->responsable : "");
	echo Form::getTextfield(Lang::t('_STREET', 'classroom'), 'street', 'street', 255, $_is_editing ? $info->street : "");
	echo Form::getTextfield(Lang::t('_CITY', 'classroom'), 'city', 'city', 255, $_is_editing ? $info->city : "");
	echo Form::getTextfield(Lang::t('_STATE', 'classroom'), 'state', 'state', 255, $_is_editing ? $info->state : "");
	echo Form::getTextfield(Lang::t('_ZIP_CODE', 'classroom'), 'zip_code', 'zip_code', 255, $_is_editing ? $info->zip_code : "");
	echo Form::getTextfield(Lang::t('_PHONE', 'classroom'), 'phone', 'phone', 255, $_is_editing ? $info->phone : "");
	echo Form::getTextfield(Lang::t('_FAX', 'classroom'),'fax','fax', 255, $_is_editing ? $info->fax : "");
	echo Form::getTextarea(Lang::t('_DISPOSITION', 'classroom'), 'disposition', 'disposition', $_is_editing ? $info->disposition : "");
	echo Form::getTextarea(Lang::t('_INSTRUMENT', 'classroom'), 'instrument', 'instrument', $_is_editing ? $info->instrument : "");
	echo Form::getTextarea(Lang::t('_AVAILABLE_INSTRUMENT', 'classroom'),'available_instrument', 'available_instrument',  $_is_editing ? $info->available_instrument : "");
	echo Form::getTextarea(Lang::t('_NOTES', 'classroom'), 'note', 'note', $_is_editing ? $info->note : "");
	
	echo Form::openButtonSpace();
	echo $_is_editing ? Form::getButton('save', 'save', Lang::t('_SAVE', 'classroom')) :  Form::getButton('save', 'save', Lang::t('_SAVE', 'classroom'));
	echo Form::getButton('undo', 'undo', Lang::t('_UNDO', 'classroom'));
	echo Form::closeButtonSpace();

	echo Form::closeForm();

?>
</div>

