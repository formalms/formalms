<?php


$_style_table = ' style="border:none;border-collapse:collapse;padding:0px;margin:0px;"';
$_style_tr = ' style="border:none;padding:0px;margin:0px;"';
$_style_td = ' style="border:none;padding:0px;margin:0px;"';

$_open_table = '<table'.$_style_table.'><tbody>';
$_close_table = '</tbody></table>';

$_open_cell = '<td'.$_style_td.'>';
$_close_cell = '</td>';

$_open_row = '<tr'.$_style_tr.'>';
$_close_row = '</tr>';

$mask = "";

$mask .= Form::openForm('multimod_form', 'ajax.adm_server.php?r=adm/usermanagement/multimod_action');
$mask .= Form::getHidden('multimod_users', 'users', "");

$mask .= Lang::t('_USERS', 'standard').': <b>'.(int)$users_count.'</b>';

$mask .= $_open_table;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_firstname', 'multimod_sel[firstname]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getTextField(Lang::t('_FIRSTNAME', 'standard'), 'firstname', 'firstname', 50, $info['firstname']).'</div>';
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_lastname', 'multimod_sel[lastname]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getTextField(Lang::t('_LASTNAME', 'standard'), 'lastname', 'lastname', 50, $info['lastname']).'</div>';
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_email', 'multimod_sel[email]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getTextField(Lang::t('_EMAIL', 'standard'), 'email', 'email', 50, $info['email']).$_close_row;
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_password', 'multimod_sel[password]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getPassword(Lang::t('_NEW_PASSWORD', 'register'), 'new_password', 'new_password', 50, "");
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= "&nbsp;";//Form::getInputCheckbox('multimod_sel_pwd_confirm', 'multimod_sel[pwd_confirm]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'profile'), 'new_password_confirm', 'new_password_confirm', 50, "");
$mask .= $_close_cell.$_close_row;

//$mask .= $_open_row;
//$mask .= Form::getCheckBox(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'), 'force_changepwd', 'force_changepwd', 1, $force_change);
//$mask .= $_close_cell.$_open_cell;
//$mask .= Form::getDropdown(Lang::t('_LEVEL', 'admin_directory'), 'level', 'level', $levels, $info['level']);
//$mask .= $_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_level', 'multimod_sel[level]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getDropdown(Lang::t('_LEVEL', 'admin_directory'), 'level', 'level', $levels, $info['level']);
$mask .= $_close_cell.$_close_row;
/*
foreach ($modify_mask as $id_item => $item) {
	$mask .= $_open_row.$_open_cell;
	$mask .= Form::getInputCheckbox('multimod_selpref_'.$id_item, 'multimod_selpref['.$id_item.']', 1, false, "");
	$mask .= $_close_cell.$_open_cell;
	$mask .= $item;
	$mask .= $_close_cell.$_close_row;
}
*/
foreach ($fields_mask as $id_item => $item) {
	if (!in_array($id_item, $fields_to_exclude)) {
		$mask .= $_open_row.$_open_cell;
		$mask .= Form::getInputCheckbox('multimod_selfield_'.$id_item, 'multimod_selfield['.$id_item.']', 1, false, "");
		$mask .= $_close_cell.$_open_cell;
		$mask .= $item;
		$mask .= $_close_cell.$_close_row;
	}
}

/* $social =new Social();
if ($social->isActive('facebook')) {
	$mask .= $_open_row.$_open_cell;
	$mask .= Form::getInputCheckbox('multimod_sel_facebook_id', 'multimod_sel[facebook_id]', 1, false, "");
	$mask .= $_close_cell.$_open_cell;
	$mask .= Form::getTextField(Lang::t('_FACEBOOK_ID', 'standard'), 'facebook_id', 'facebook_id', 255, $info['facebook_id']);
	$mask .= $_close_cell.$_close_row;
}

if ($social->isActive('twitter')) {
	$mask .= $_open_row.$_open_cell;
	$mask .= Form::getInputCheckbox('multimod_sel_twitter_id', 'multimod_sel[twitter_id]', 1, false, "");
	$mask .= $_close_cell.$_open_cell;
	$mask .= Form::getTextField(Lang::t('_TWITTER_ID', 'standard'), 'twitter_id', 'twitter_id', 255, $info['twitter_id']);
	$mask .= $_close_cell.$_close_row;
}

if ($social->isActive('linkedin')) {
	$mask .= $_open_row.$_open_cell;
	$mask .= Form::getInputCheckbox('multimod_sel_linkedin_id', 'multimod_sel[linkedin_id]', 1, false, "");
	$mask .= $_close_cell.$_open_cell;
	$mask .= Form::getTextField(Lang::t('_LINKEDIN_ID', 'standard'), 'linkedin_id', 'linkedin_id', 255, $info['linkedin_id']);
	$mask .= $_close_cell.$_close_row;
}

if ($social->isActive('google')) {
	$mask .= $_open_row.$_open_cell;
	$mask .= Form::getInputCheckbox('multimod_sel_google_id', 'multimod_sel[google_id]', 1, false, "");
	$mask .= $_close_cell.$_open_cell;
	$mask .= Form::getTextField(Lang::t('_GOOGLE_ID', 'standard'), 'google_id', 'google_id', 255, $info['google_id']);
	$mask .= $_close_cell.$_close_row;
} */

$mask .= $_close_table;

$mask .= Form::closeForm();

if (isset($json)) {
	$output = array(
		'success' => true,
		'header' => $title,
		'body' => $mask
	);
	if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) $output['__date_inputs'] = $GLOBALS['date_inputs'];
	echo $this->json->encode($output);
} else {
	echo getTitleArea($title);
	echo '<div class="std_block">';
	echo $body;
	echo '</div>';
}
?>