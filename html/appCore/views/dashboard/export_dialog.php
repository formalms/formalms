<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

$_header = Lang::t('_CHOOSE_EXPORT_FORMAT', 'dashboard');
$_body = '';

$_body .= Form::openForm('export_report_dialog_form', 'index.php?modname=report&op=show_results&of_platform=lms&idrep=' . (int) $id_report);
$_body .= Form::getHidden('idrep', 'idrep', $id_report);
$_body .= Form::getRadioSet(
    Lang::t('_FORMAT', 'standard'),
    'report_format',
    'dl',
    [
        Lang::t('_EXPORT_HTML', 'report') => 'htm',
        Lang::t('_EXPORT_CSV', 'report') => 'csv',
        Lang::t('_EXPORT_XLS', 'report') => 'xls',
    ],
   'htm'
);
$_body .= Form::closeForm();

if (isset($json)) {
    $output = [
        'success' => true,
        'header' => $_header,
        'body' => $_body,
    ];
    echo $json->encode($output);
} else {
}
