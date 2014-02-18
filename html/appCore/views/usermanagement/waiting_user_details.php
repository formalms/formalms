<?php

$body = "";
$body .= $fields->playFieldsForUser($id_user, false, true);

if (isset($json)) {
	$output = array(
		'success' => true,
		'header' => $title,
		'body' => $body
	);
	echo $this->json->encode($output);
} else {
	echo getTitleArea($title);
	echo '<div class="std_block">';
	echo $body;
	echo '</div>';
}

?>