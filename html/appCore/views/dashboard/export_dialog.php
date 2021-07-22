<?php

$_header = Lang::t('_CHOOSE_EXPORT_FORMAT', 'dashboard');
$_body = "";

$_body .= Form::openForm('export_report_dialog_form', 'index.php?modname=report&op=show_results&of_platform=lms&idrep='.(int)$id_report);
$_body .= Form::getHidden('idrep', 'idrep', $id_report);
$_body .= Form::getRadioSet(
	Lang::t('_FORMAT', 'standard'),
	'report_format',
	'dl',
	array(
		Lang::t('_EXPORT_HTML', 'report') => 'htm',
	    Lang::t('_EXPORT_CSV', 'report') => 'csv',
	    Lang::t('_EXPORT_XLS', 'report') => 'xls'
	),
   'htm'
);
$_body .= Form::closeForm();

if (isset($json)) {
	$output = array(
		'success' => true,
		'header' => $_header,
		'body' => $_body
	);
	echo $json->encode($output);
} else {
	

}

?>
