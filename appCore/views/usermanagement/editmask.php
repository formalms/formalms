<?php
////build edit mask

echo Form::openForm($form_id, $form_url, false, false, 'multipart/form-data');
echo ($is_editing ? Form::getHidden('idst', 'idst', $idst) : '');

echo '<div id="create_user_main_container">';

if (!$is_editing) {
	echo '<div id="create_user_tabview" class="yui-navset">';
	echo '<ul class="yui-nav">';
	echo '<li class="selected"><a href="#create_user_tab1"><em>'.Lang::t('_DETAILS', 'profile').'</em></a></li>';
	echo '<li><a href="#create_user_tab2"><em>'.Lang::t('_ORG_CHART', 'organization_chart').'</em></a></li>';
	echo '</ul>';
	echo '<div class="yui-content">';
	echo '<div id="create_user_tab1">';
}

echo Form::getTextField(Lang::t('_USERNAME', 'standard'), 'username', 'username', 255, $info->userid);
echo Form::getTextField(Lang::t('_FIRSTNAME', 'standard'), 'firstname', 'firstname', 255, $info->firstname);
echo Form::getTextField(Lang::t('_LASTNAME', 'standard'), 'lastname', 'lastname', 255, $info->lastname);
echo Form::getTextField(Lang::t('_EMAIL', 'standard'), 'email', 'email', 255, $info->email);

if ($is_editing) {
	echo Form::getPassword(Lang::t('_NEW_PASSWORD', 'register'), 'new_password', 'new_password', 255, "");
	echo Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'register'), 'new_password_confirm', 'new_password_confirm', 255, "");
	echo Form::getCheckBox(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'), 'force_changepwd', 'force_changepwd', 1, $info->force_change > 0);
} else {
	echo Form::getPassword(Lang::t('_PASSWORD', 'standard'), 'password', 'password', 255, "");
	echo Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'register'), 'password_confirm', 'password_confirm', 255, "");
	echo Form::getCheckBox(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'), 'force_changepwd', 'force_changepwd', 1, false);
}

if (Docebo::user()->user_level == ADMIN_GROUP_GODADMIN) {
	echo Form::getDropdown(Lang::t('_LEVEL', 'admin_directory'), 'level', 'level', $levels, $info->level);
}

echo $modify_mask;
echo $fields_mask;

/* $social =new Social();
if ($social->isActive('facebook')) {
	echo Form::getTextField(Lang::t('_FACEBOOK_ID', 'standard'), 'facebook_id', 'facebook_id', 255, $info->facebook_id);
}
if ($social->isActive('twitter')) {
	echo Form::getTextField(Lang::t('_TWITTER_ID', 'standard'), 'twitter_id', 'twitter_id', 255, $info->twitter_id);
}
if ($social->isActive('linkedin')) {
	echo Form::getTextField(Lang::t('_LINKEDIN_ID', 'standard'), 'linkedin_id', 'linkedin_id', 255, $info->linkedin_id);
}
if ($social->isActive('google')) {
	echo Form::getTextField(Lang::t('_GOOGLE_ID', 'standard'), 'google_id', 'google_id', 255, $info->google_id);
} */

if (!$is_editing) {
	echo '</div>';
	echo '<div id="create_user_tab2">'; // class="little_table"

	echo Form::getHidden('orgchart_hidden_selection', 'orgchart_selection', '');
	echo '<div id="createuser_orgchart_tree" class="folder_tree"></div>';

	echo '</div>'; //close tab
	echo '</div>'; //close tabview
}

echo '</div>';

echo Form::closeForm();

?>