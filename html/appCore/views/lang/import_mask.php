<?php Get::title(array(
	'index.php?r=adm/lang/show' => Lang::t('_LANGUAGE', 'admin_lang'),
	Lang::t('_IMPORT', 'admin_lang')
)); ?>
<div class="std_block">
<?php
echo Form::openForm('import_lang', 'index.php?r=adm/lang/doimport', false, 'post', 'multipart/form-data')

	.Form::getFilefield(Lang::t('_FILE', 'admin_lang'),
						'lang_file',
						'lang_file')
	.Form::getCheckbox(Lang::t('_OVERWRITE_EXISTENT', 'admin_lang'),
						'overwrite',
						'overwrite',
						'1')
	.Form::getCheckbox(Lang::t('_DO_NOT_ADD_MISS', 'admin_lang'),
						'noadd_miss',
						'noadd_miss',
						'1')

	.Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'admin_lang'))
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'admin_lang'))
	.Form::closeButtonSpace()

	.Form::closeForm();
?>
</div>