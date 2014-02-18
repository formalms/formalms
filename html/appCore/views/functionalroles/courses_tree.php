<?php
/*
 * Variables requested
 *  - $courses_info = hierarchical array with courses by competences;
 *  - $competences_info = detail for competences;
 *  - $language = the lang_code to use
 *  - $title = dialog/page title;
 *  - $json = Serci
 */

$html = "";


//die(print_r($competences_info, true)."\n\n\n\n".print_r($courses_info, true));
$html .= '<div class="folder_tree">';
$html .= '<div id="fncrole_show_courses"><ul>';
foreach ($competences_info as $id_competence => $cinfo) {
	$courses = isset($courses_info[$id_competence]) ? $courses_info[$id_competence] : array();
	
	$html .= '<li class="expanded">';
	$html .= $cinfo->langs[$language]['name'];

	if (count($courses) > 0) {

		$html .= '<ul>';
		
		foreach ($courses as $course) {
			$html .= '<li>';
			$html .= $course['name'].'; ';
			$html .= ($cinfo->type == 'score' ? (int)$course['score'] : '-');
			$html .= '</li>';
		}

		$html .= '</ul>';		
	} else {
		$html .= '<ul><li>'.Lang::t('_NO_COURSES_FOR_COMPETENCE', 'fncroles').'</li></ul>';
	}
	$html .= '</li>';
}
$html .= '</ul></div>';
$html .= '</div>';

if ($json) {
	$output = array(
		'success' => true,
		'header' => $title,
		'body' => $html
	);
	echo $json->encode($output);
} else {
	echo getTitle($title);
	echo '<div class="std_block">';
	echo $html;
	echo '</div>';
}


?>