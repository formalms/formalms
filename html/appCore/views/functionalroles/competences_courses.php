<?php
/*
 * Variables requested
 *  - $courses_info = hierarchical array with courses by competences;
 *  - $competences_info = detail for competences;
 *  - $language = the lang_code to use
 *  - $title = dialog/page title;
 *  - $json = Serci
 */

echo getTitleArea($title);
?>
<div class="std_block">
<?php

echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard'));

$html = "";
require_once(_base_.'/lib/lib.table.php');
$table = new Table();
$label_h = array(
	Lang::t('_NAME', 'competences'),
	Lang::t('_TYPE', 'standard'),
	Lang::t('_REQUIRED_SCORE', 'fncroles'),
	Lang::t('_EXPIRATION_DAYS', 'fncroles'),
	Lang::t('_COURSE', 'course'),
	Lang::t('_SCORE', 'fncroles')
);
$style_h = array(
	'',
	'img-cell',
	'img-cell',
	'img-cell',
	'',
	'img-cell'
);

$table->addHead($label_h, $style_h);

$language = getLanguage();

$cmodel = new CompetencesAdm();
$_types = $cmodel->getCompetenceTypes();
$_typologies = $cmodel->getCompetenceTypologies();

$rs_counter = 1;
foreach ($competences_info as $id_competence => $cinfo) {
	$courses = isset($courses_info[$id_competence]) ? $courses_info[$id_competence] : array();

	$cinfo_content_1 = $cinfo->langs[$language]['name'];
	$cinfo_content_2 = $_types[$cinfo->type];
	$cinfo_content_3 = ($cinfo->type=='score' ? $cinfo->role_score : '-');
	$cinfo_content_4 = ($cinfo->role_expiration > 0 ? $cinfo->role_expiration.' '.Lang::t('_DAYS', 'standard') : Lang::t('_NEVER', 'standard'));

	$num_courses = count($courses);
	if ($num_courses > 0) {

		$first = true;
		
		foreach ($courses as $course) {
			$line = array();
			if ($first) {
				$line[] = array('rowspan' => count($courses), 'value' => $cinfo_content_1, 'style' => 'yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
				$line[] = array('rowspan' => count($courses), 'value' => $cinfo_content_2, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
				$line[] = array('rowspan' => count($courses), 'value' => $cinfo_content_3, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
				$line[] = array('rowspan' => count($courses), 'value' => $cinfo_content_4, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
				$rs_counter++;
			}
			$line[] = $course['name'];
			$line[] = array('style' => 'img-cell', 'value' => ($cinfo->type == 'score' ? '<b>'.(int)$course['score'].'</b>' : '-'));
			$table->addBody($line, ($first ? 'borded-top' : false));
			$first = false;
		}

	} else {
		$line = array();
		$line[] = array('rowspan' => 1, 'value' => $cinfo_content_1, 'style' => 'yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
		$line[] = array('rowspan' => 1, 'value' => $cinfo_content_2, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
		$line[] = array('rowspan' => 1, 'value' => $cinfo_content_3, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
		$line[] = array('rowspan' => 1, 'value' => $cinfo_content_4, 'style' => 'img-cell yui-dt-rs-'.($rs_counter%2 > 0 ? 'odd' : 'even'));
		$line[] = '<i>('.Lang::t('_NO_COURSES_FOR_COMPETENCE', 'fncroles').')</i>';
		$line[] = array('style' => 'img-cell', 'value' => '<span class="ico-sprite fd_notice"><span></span></span>');
		$table->addBody($line, 'borded-top');
		$rs_counter++;
	}
}

echo '<br />';
echo $table->getTable();
echo '<br />';

?>
<?php echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard')); ?>
</div>