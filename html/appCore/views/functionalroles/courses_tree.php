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

$html = '';

//die(print_r($competences_info, true)."\n\n\n\n".print_r($courses_info, true));
$html .= '<div class="folder_tree">';
$html .= '<div id="fncrole_show_courses"><ul>';
foreach ($competences_info as $id_competence => $cinfo) {
    $courses = isset($courses_info[$id_competence]) ? $courses_info[$id_competence] : [];

    $html .= '<li class="expanded">';
    $html .= $cinfo->langs[$language]['name'];

    if (count($courses) > 0) {
        $html .= '<ul>';

        foreach ($courses as $course) {
            $html .= '<li>';
            $html .= $course['name'] . '; ';
            $html .= ($cinfo->type == 'score' ? (int) $course['score'] : '-');
            $html .= '</li>';
        }

        $html .= '</ul>';
    } else {
        $html .= '<ul><li>' . Lang::t('_NO_COURSES_FOR_COMPETENCE', 'fncroles') . '</li></ul>';
    }
    $html .= '</li>';
}
$html .= '</ul></div>';
$html .= '</div>';

if ($json) {
    $output = [
        'success' => true,
        'header' => $title,
        'body' => $html,
    ];
    echo $json->encode($output);
} else {
    echo getTitle($title);
    echo '<div class="std_block">';
    echo $html;
    echo '</div>';
}
