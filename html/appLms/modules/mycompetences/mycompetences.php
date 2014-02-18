<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

function mycompetences(&$url) {
	checkPerm('view');

	$html = "";
	$html .= getTitleArea(Lang::t('_COMPETENCES'), 'competences');
	$html .= '<div class="std_block">';
	
	$cmodel = new CompetencesAdm();
	$fmodel = new FunctionalrolesAdm();
	$id_user = getLogUserId();

	$ucomps = $cmodel->getUserCompetences($id_user);
	$rcomps = $fmodel->getUserRequiredCompetences($id_user);

	$ucomps_info = $cmodel->getCompetencesInfo(array_keys($ucomps));
	$language = getLanguage();
	$_typologies = $cmodel->getCompetenceTypologies();
	$_types = $cmodel->getCompetenceTypes();
	$icon_actv = '<span class="ico-sprite subs_actv"><span>'.Lang::t('_COMPETENCE_OBTAINED', 'competences').'</span></span>';
	$icon_req = '<span class="ico-sprite subs_actv"><span>'.Lang::t('_MANDATORY', 'competences').'</span></span>';

	//*******************
	
	require_once(_base_.'/lib/lib.table.php');
	$table = new Table(Get::sett('visuItem'),Lang::t('_COMPETENCES'),Lang::t('_COMPETENCES'));
	
	$style_h = array('', '','image','image','image','image','image');
	$label_h = array(
		Lang::t('_NAME', 'competences'),
		Lang::t('_TYPOLOGY', 'competences'),
		Lang::t('_TYPE', 'standard'),
		Lang::t('_SCORE', 'competences'),
		Lang::t('_DATE_LAST_COMPLETE', 'subscribe'),
		Lang::t('_COMPETENCES_REQUIRED', 'competences')
	);
	
	$table->addHead($label_h, $style_h);

	foreach ($ucomps_info as $id_competence => $cinfo) {
		$line = array();

		$line[] = $cinfo->langs[$language]['name'];
		$line[] = $_typologies[$cinfo->typology];
		$line[] = $_types[$cinfo->type];
		$line[] = ($cinfo->type == 'score' ? '<b>'.$ucomps[$id_competence]->score_got.'</b>' : $icon_actv);
		$line[] = Format::date($ucomps[$id_competence]->last_assign_date, 'datetime');
		$line[] = array_key_exists($id_competence, $rcomps) ? $icon_req : '';

		$table->addBody($line);
	}

	$html .= $table->getTable();
	$html .= '</div>';

	$html .= Form::openForm('beck_url', 'index.php');
	$html .= Form::openButtonSpace();
	$html .= Form::getButton('close', 'close', Lang::t('_CLOSE', 'standard'));
	$html .= Form::closeButtonSpace();
	$html .= Form::closeform();
	
	cout($html, 'content');
}


// ================================================================================

function mycompetencesDispatch($op) {
	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance('competences');
	$url->setStdQuery('modname=mycompetences&op=mycompetences');
	
	switch($op) {
		
		case "mycompetences" :
		default : {
			mycompetences($url);
		}
	}
	
}

?>