<?php

		echo getTitleArea(Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'), 'directory_group');
		echo '<div class="std_block">';

		echo Form::getFormHeader(Lang::t('_ASSIGN_USERS', 'admin_directory'));
		echo Form::openForm('directory_importgroupuser', 'index.php?r='. $this->link.'/importusers_step2', false, false, 'multipart/form-data');
		echo Form::getHidden('id_group', 'id_group', $id_group);
		echo Form::openElementSpace();
		echo Form::getFilefield(Lang::t('_GROUP_USER_IMPORT_FILE', 'admin_directory'), 'file_import', 'file_import');
		echo Form::getRadioSet(Lang::t('_GROUP_USER_IMPORT_SEPARATOR', 'admin_directory'), 'import_separator', 'import_separator', array(
			Lang::t('_AUTODETECT', 'standard') => 'auto',
			'<b>,</b>' => 'comma',
			'<b>;</b>' => 'dotcomma',
			Lang::t('_MANUAL', 'standard').':&nbsp;'.Form::getInputTextfield('', 'import_separator_manual', 'import_separator_manual', "", "", 255) => 'manual'
		), 'auto');
		echo Form::getCheckbox(Lang::t('_GROUP_USER_IMPORT_HEADER', 'admin_directory'), 'import_first_row_header', 'import_first_row_header', 'true', true);
		echo Form::getTextfield(Lang::t('_GROUP_USER_IMPORT_CHARSET', 'admin_directory'), 'import_charset', 'import_charset', 20, 'UTF-8');
		echo Form::closeElementSpace();
		echo Form::openButtonSpace();
		echo Form::getButton('import_groupuser_2', 'import_groupuser_2', Lang::t('_NEXT', 'standard'));
		echo Form::getButton('import_groupcancel', 'import_groupcancel', Lang::t('_UNDO', 'standard'));
		echo Form::closeButtonSpace();
		echo Form::closeForm();

		echo '</div>';



?>
