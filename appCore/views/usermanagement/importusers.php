<?php
switch ($step) {
	//step 1 : choose file and reading options
	case 1: {
		echo getTitleArea(Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'), 'directory_group');
		echo '<div class="std_block">';
		
		echo Form::getFormHeader(Lang::t('_ASSIGN_USERS', 'admin_directory'));
		echo Form::openForm('directory_importgroupuser', 'index.php?r='. $this->link.'/importusers', false, false, 'multipart/form-data');
		//echo Form::getHidden('id', 'id', $id_org);
		echo Form::getHidden('step', 'step', 2);
		echo Form::openElementSpace();
		echo Form::getFilefield(Lang::t('_GROUP_USER_IMPORT_FILE', 'admin_directory'), 'file_import', 'file_import');
		//echo Form::getTextfield(Lang::t('_GROUP_USER_IMPORT_SEPARATOR', 'admin_directory'), 'import_separator', 'import_separator', 1, ',');
		echo Form::getRadioSet(Lang::t('_GROUP_USER_IMPORT_SEPARATOR', 'admin_directory'), 'import_separator', 'import_separator', array(
			Lang::t('_AUTODETECT', 'standard') => 'auto',
			'<b>,</b>' => 'comma',
			'<b>;</b>' => 'dotcomma',
			Lang::t('_MANUAL', 'standard').':&nbsp;'.Form::getInputTextfield('', 'import_separator_manual', 'import_separator_manual', "", "", 255) => 'manual'
		), 'auto');
		echo Form::getCheckbox(Lang::t('_GROUP_USER_IMPORT_HEADER', 'admin_directory'), 'import_first_row_header', 'import_first_row_header', 'true', true);
		echo Form::getTextfield(Lang::t('_GROUP_USER_IMPORT_CHARSET', 'admin_directory'), 'import_charset', 'import_charset', 20, 'UTF-8');
		echo Form::getDropdown(Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'), 'id', 'id', $orgchart_list, $id_org);

		echo Form::getRadioSet(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'),
			'pwd_force_change_policy',
			'pwd_force_change_policy',
			array(
				Lang::t('_NO', 'standard') => 'false',
				Lang::t('_YES', 'standard') => 'true',
				Lang::t('_SERVERINFO', 'configuration') => 'by_setting',
				Lang::t('_DO_NOTHING', 'preassessment') => 'do_nothing'
			),
			'do_nothing'
		);

		echo Form::closeElementSpace();
		echo Form::openButtonSpace();
		echo Form::getButton('import_groupuser_2', 'import_groupuser_2', Lang::t('_NEXT', 'standard'));
		echo Form::getButton('import_groupcancel', 'import_groupcancel', Lang::t('_UNDO', 'standard'));
		echo Form::closeButtonSpace();
		echo Form::closeForm();

		echo '</div>';
	} break;

	//step 2 : set columns
	case 2: {
		echo getTitleArea(Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'), 'directory_group');
		echo '<div class="std_block">';
		echo Form::openForm('directory_importgroupuser', 'index.php?r='. $this->link.'/importusers', false, false, 'multipart/form-data');
		echo Form::openElementSpace();
		echo Form::getHidden('id', 'id', $id_org);
		echo Form::getHidden('step', 'step', 3);

		echo Form::getCheckbox(Lang::t('_SEND_ALERT', 'subscribe'), 'send_alert', 'send_alert', 1);
		echo Form::getCheckbox(Lang::t('_TASK_INSERTED', 'iotask'), 'insert_update', 'insert_update', 1);
		
		echo $UIMap;
		echo Form::getHidden('filename', 'filename', $filename);
		echo Form::getHidden('import_first_row_header', 'import_first_row_header', ($first_row_header ? 'true' : 'false'));
		echo Form::getHidden('import_separator', 'import_separator', $separator);
		echo Form::getHidden('import_charset', 'import_charset', $import_charset);
		echo Form::getHidden('pwd_force_change_policy', 'pwd_force_change_policy', $pwd_force_change_policy);

		echo Form::closeElementSpace();
		echo Form::openButtonSpace();
		echo Form::getButton('next_importusers_3', 'next_importusers_3', Lang::t('_NEXT', 'standard'));
		echo Form::getButton('import_groupcancel', 'import_groupcancel', Lang::t('_UNDO', 'standard'));
		echo Form::closeButtonSpace();

		echo Form::closeForm();
		echo '</div>';
	} break;

	//step 3 : do the import
	case 3: {
		// print total processed rows
		echo getTitleArea(Lang::t('_ORG_CHART_IMPORT_USERS', 'organization_chart'), 'directory_group');
		echo '<div class="std_block">';
		echo $backUi;
		echo $resultUi;
		echo $table;
		echo $backUi;
		echo '</div>';
	} break;
}
?>
