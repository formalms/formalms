<?php

//buffer variable
$output = '';

//set the edit mask in the buffer
$output .= Form::openForm('time_period_form', $url);

$output .= Form::getTextfield(Lang::t('_NAME', 'standard'), 'title', 'title', 255, isset($title) ? $title : '');
$output .= Form::getDatefield(Lang::t('_DATE_BEGIN', 'standard'), 'start_date', 'start_date', isset($start_date) ? Format::date($start_date, 'date') : '');
$output .= Form::getDatefield(Lang::t('_DATE_END', 'standard'), 'end_date', 'end_date', isset($end_date) ? Format::date($end_date, 'date') : '');
if (isset($id)) $output .= Form::getHidden('id', 'id', $id);

$output .=  Form::closeForm();

//if json is requested, format the output
if ($json) {
	$output = $json->encode($output);
}

//draw it
echo $output;

?>