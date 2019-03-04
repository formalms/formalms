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
$mask .= Form::getInputCheckbox('multimod_sel_password', 'multimod_sel[password]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getPassword(Lang::t('_NEW_PASSWORD', 'register'), 'new_password', 'new_password', 50, "");
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= "&nbsp;";
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getPassword(Lang::t('_RETYPE_PASSWORD', 'register'), 'new_password_confirm', 'new_password_confirm', 50, "");
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= "&nbsp;";
$mask .= $_close_cell.$_open_cell;
$mask .= "&nbsp;";
$mask .= Form::getInputCheckbox('multimod_sel_send_alert', 'multimod_sel[send_alert]', 1, false, "disabled=true");
$mask .= "&nbsp;"."<b>".Lang::t('_SEND_NEW_CREDENTIALS_ALERT', 'user_managment')."</b>";
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= "&nbsp;";
$mask .= $_close_cell.$_open_cell;
$mask .= "&nbsp;";
//$mask .= '<div class="up_name">'.''.'</div>';
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_force_change', 'multimod_sel[force_change]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= "&nbsp;"."<b>".Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory')."</b>";
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_link_reset_password', 'multimod_sel[link_reset_password]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= "&nbsp;"."<b>".Lang::t('_SEND_LINK_RESET_PASSWORD', 'register')."</b>";
$mask .= $_close_cell.$_close_row;

$mask .= $_open_row.$_open_cell;
$mask .= Form::getInputCheckbox('multimod_sel_level', 'multimod_sel[level]', 1, false, "");
$mask .= $_close_cell.$_open_cell;
$mask .= Form::getDropdown(Lang::t('_LEVEL', 'admin_directory'), 'level', 'level', $levels, $info['level']);
$mask .= $_close_cell.$_close_row;

foreach ($fields_mask as $id_item => $item) {
	if (!in_array($id_item, $fields_to_exclude)) {
		$mask .= $_open_row.$_open_cell;
		$mask .= Form::getInputCheckbox('multimod_selfield_'.$id_item, 'multimod_selfield['.$id_item.']', 1, false, "");
		$mask .= $_close_cell.$_open_cell;
		$mask .= $item;
		$mask .= $_close_cell.$_close_row;
	}
}


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