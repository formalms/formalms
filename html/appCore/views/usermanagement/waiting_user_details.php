<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

$body = '';
$body .= $fields->playFieldsForUser($id_user, false, true);

if (isset($json)) {
    $output = [
        'success' => true,
        'header' => $title,
        'body' => $body,
    ];
    echo $this->json->encode($output);
} else {
    echo getTitleArea($title);
    echo '<div class="std_block">';
    echo $body;
    echo '</div>';
}
