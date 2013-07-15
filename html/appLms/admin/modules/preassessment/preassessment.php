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

/**
 * @package
 * @subpackage
 * @category
 * @author Fabio Pirovano
 * @version $Id:$
 * @since 3.5
 *
 * ( editor = Eclipse 3.2.0[phpeclipse,subclipse,WTP], tabwidth = 4 )
 */

define("_SUCCESS_save","_OPERATION_SUCCESSFUL");
define("_SUCCESS_delete","_OPERATION_SUCCESSFUL");//"_SUCCESS_DEL_PRE_ASSESSMENT");
define("_SUCCESS_assign","_OPERATION_SUCCESSFUL");
define("_SUCCESS_rule","_OPERATION_SUCCESSFUL");

define("_FAIL_delete","_ERROR_DEL_PRE_ASSESSMENT");
define("_FAIL_assign","_OPERATION_FAILURE");
define("_FAIL_rule","_OPERATION_FAILURE");

function assesmentlist(&$url) {
	checkPerm('view');

	$mod_perm 	= checkPerm('mod', true);
	$subs_perm 	= checkPerm('subscribe', true);

	$lang =& DoceboLanguage::createInstance('preassessment');

	YuiLib::load(array(
		'animation' 		=> 'animation-min.js',
		'dragdrop' 			=> 'dragdrop-min.js',
		'button' 			=> 'button-min.js',
		'container' 		=> 'container-min.js',
		'my_window' 		=> 'windows.js'
	), array(
		'container/assets/skins/sam' => 'container.css',
		'button/assets/skins/sam' => 'button.css'
	));
	// addJs($GLOBALS['where_lms_relative'].'/admin/modules/preassessment/', 'ajax.preassessment.js');

	$GLOBALS['page']->add('<script type="text/javascript">'
		.' setup_assessment(); '
		.'</script>', 'page_head');

	// recover assessment
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();

	$assessment = $assess_man->getAllAssessment();

	// --- get subscribed users
	$user_tot =array();
	$db =DbConn::getInstance();
	$assessment_id_arr =array_keys($assessment);
	if (!empty($assessment_id_arr)) {
		$qtxt ="SELECT id_assessment, COUNT(*) as tot_user, type_of
			FROM %lms_assessment_user
			WHERE id_assessment IN (".implode(",", $assessment_id_arr).")
			GROUP BY id_assessment, type_of";

		$q =$db->query($qtxt);

		while($row=$db->fetch_assoc($q)) {
			$assessment_id =$row['id_assessment'];
			$type =$row['type_of'];
			$user_tot[$assessment_id][$type]=$row['tot_user'];
		}
	}

	// --- check if the course has at least one test..
	if (!empty($assessment_id_arr)) {
		$course_test =array();
		$qtxt ="SELECT idCourse, COUNT(idCourse) as tot_test
			FROM %lms_organization
			WHERE idCourse IN (".implode(",", $assessment_id_arr).")
			AND objectType='test'
			GROUP BY idCourse";

		$q =$db->query($qtxt);

		while($row=$db->fetch_assoc($q)) {
			$assessment_id =$row['idCourse'];
			$course_test[$assessment_id]=$row['tot_test'];
		}
	}


	// recover new type one
	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(0, $lang->def('_CAPTION_PREASSESSMENT'), $lang->def('_CAPTION_PREASSESSMENT'));

	// table header --------------------------------------------------------------------------------------------
	$cont_h = array($lang->def('_CODE'), $lang->def('_NAME'));
	$type_h = array('image nowrap', '');
	if($mod_perm) {
		$cont_h[] = $cont[] ='<span class="ico-wt-sprite subs_course" title="'.Lang::t('_MOD', 'preassessment').'"><span>'
				.Lang::t('_MOD', 'preassessment').'</span></span>';
		$type_h[] = 'image';
	}
	if($subs_perm) {
		$cont_h[] = $cont[] ='<span class="ico-wt-sprite subs_admin" title="'.Lang::t('_ASSIGN_ADMIN', 'preassessment').'"><span>'
				.Lang::t('_ASSIGN_ADMIN', 'preassessment').'</span></span>';
		$cont_h[] = $cont[] ='<span class="ico-sprite subs_users" title="'.Lang::t('_ASSIGN_USERS', 'preassessment').'"><span>'
				.Lang::t('_ASSIGN_USERS', 'preassessment').'</span></span>';
		$type_h[] = 'image';
		$type_h[] = 'image';
	}
	if($mod_perm) {
		$cont_h[] = $cont[] ='<span class="ico-sprite subs_mod" title="'.Lang::t('_MOD', 'preassessment').'"><span>'
				.Lang::t('_MOD', 'preassessment').'</span></span>';
		$cont_h[] = $cont[] ='<span class="ico-sprite subs_del" title="'.Lang::t('_DEL', 'preassessment').'"><span>'
				.Lang::t('_DEL', 'preassessment').'</span></span>';
		$type_h[] = 'image';
		$type_h[] = 'image';
	}

	$tb->addHead($cont_h, $type_h);
	while(list($id, $info) = each($assessment)) {

		$cont = array($info['code'], $info['name']);

		if($mod_perm) {

			$ico =(isset($course_test[$id]) && $course_test[$id] > 0 ? 'subs_course' : 'fd_notice');
			$cont[] ='<a class="ico-wt-sprite '.$ico.'" title="'.Lang::t('_MOD', 'preassessment').'" href="'.
				$url->getUrl('op=manageassessment&id_assess='.$id.'&load=1').'"><span>'
				.Lang::t('_MOD', 'preassessment').'</span></a>';

		}
		if($subs_perm) {
			$contx[] = '<a href="'.$url->getUrl('op=modassessadmin&id_assess='.$id.'&load=1').'" title="'.$lang->def('_ASSIGN_ADMIN_TITLE').' : '.strip_tags($info['name']).'">'
				.$lang->def('_ASSIGN_ADMIN_TITLE')
				.'</a>';

			$ico =(isset($user_tot[$id]['admin']) && $user_tot[$id]['admin'] > 0 ? 'subs_admin' : 'fd_notice');
			$cont[] ='<a class="ico-wt-sprite '.$ico.'" title="'.Lang::t('_ASSIGN_ADMIN_TITLE', 'preassessment').' : '.strip_tags($info['name']).'" href="'.
				$url->getUrl('op=modassessadmin&id_assess='.$id.'&load=1').'"><span>'
				.Lang::t('_ASSIGN_ADMIN', 'preassessment').'</span></a>';

			$ico =(isset($user_tot[$id]['user']) && $user_tot[$id]['user'] > 0 ? 'subs_users' : 'fd_notice');
			$cont[] ='<a class="ico-sprite '.$ico.'" title="'.Lang::t('_ASSIGN_USERS', 'preassessment').' : '.strip_tags($info['name']).'" href="'.
				$url->getUrl('op=modassessuser&id_assess='.$id.'&load=1').'"><span>'
				.Lang::t('_ASSIGN_USERS', 'preassessment').'</span></a>';
		}
		if($mod_perm) {
			$cont[] = '<a class="ico-sprite subs_mod" href="'.$url->getUrl('op=modassessment&id_assess='.$id).'" title="'.$lang->def('_MOD_TITLE').'">'
				.'<span>'.$lang->def('_MOD').'</span>'
				.'</a>';
			$cont[] = '<a class="ico-sprite subs_del" href="'.$url->getUrl('op=delassessment&id_assess='.$id).'" title="'.$lang->def('_DEL').':&nbsp;'.$info['code'].'">'
				.'<span>'.$lang->def('_DEL').'</span>'
				.'</a>';
		}
		$tb->addBody($cont);
	}
	if($mod_perm) {

		$tb->addActionAdd(
			'<a class="ico-wt-sprite subs_add" href="'.$url->getUrl('op=modassessment').'"><span>'.$lang->def('_ADD_ASSESSMENT').'</span></a>'
		);
	}
	$GLOBALS['page']->add(
		getTitleArea($lang->def('_ASSESSMENT'), 'preassessment')
		.'<div class="std_block">'
	, 'content');

	if(isset($_GET['result'])) $GLOBALS['page']->add(guiResultStatus($lang, $_GET['result']), 'content');

	$GLOBALS['page']->add($tb->getTable(), 'content');

	$GLOBALS['page']->add('</div>', 'content');

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delassessment]');
}

function modassessment(&$url) {
	checkPerm('mod');

	$id_assessment = importVar('id_assess', true, 0);

	$lang =& DoceboLanguage::createInstance('preassessment');

	require_once(_base_.'/lib/lib.form.php');
	$form = new Form();

	// instance assessment =================================================
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();

	// intest page =========================================================
	$GLOBALS['page']->add(
		getTitleArea(array( $url->getUrl() => $lang->def('_ASSESSMENT'), $lang->def('_ADD_ASSESSMENT')), 'preassessment')
		.'<div class="std_block">'
	, 'content');

	// save param ==========================================================
	if(isset($_POST['save'])) {

		$assessment_data = array(	'code' => importVar('code'),
									'name' => importVar('name'),
									'description' => importVar('description'));

		if(trim($assessment_data['name']) == '') $GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_INSERT_NAME')), 'content');
		elseif(!$assess_man->saveAssessment($id_assessment, $assessment_data)) $GLOBALS['page']->add(getErrorUi($lang->def('_ERROR_SAVE')), 'content');
		else Util::jump_to($url->getUrl('result=ok_save'));
	}

	// load init data ========================================================
	if($id_assessment == 0)  $assessment = array('code' => '', 'name' => '', 'description' => '');
	else $assessment = $assess_man->getAssessment($id_assessment);

	// write form ===========================================================

	$GLOBALS['page']->add(
		$form->openForm('addpreassessment', $url->getUrl('op=modassessment'))

		.$form->openElementSpace()
		.$form->getHidden('id_assess', 'id_assess', $id_assessment)
		.$form->getTextfield(	$lang->def('_CODE'),
								'code',
								'code',
								255,
								importVar('code', false, $assessment['code']) )

		.$form->getTextfield(	$lang->def('_NAME'),
								'name',
								'name',
								 255,
								importVar('name', false, $assessment['name']))

		.$form->getTextarea(	$lang->def('_DESCRIPTION'),
								'description',
								'description',
								importVar('description', false, $assessment['description'], true) )
		.$form->closeElementSpace()

		.$form->openButtonSpace()
		.$form->getButton('save', 'save', $lang->def('_SAVE'))
		.$form->getButton('undo', 'undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()

		.$form->closeForm()
	, 'content');

	$GLOBALS['page']->add('</div>', 'content');
}

function delassessment(&$url) {
	checkPerm('mod');

	$id_assessment = importVar('id_assess', true, 0);
	if($id_assessment == 0) Util::jump_to($url->getUrl('result=ok_delete'));

	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();

	if($assess_man->deleteAssessment($id_assessment)) Util::jump_to($url->getUrl('result=ok_delete'));
	else Util::jump_to($url->getUrl('result=err_delete'));
}

function modassessuser(&$url) {
	checkPerm('subscribe');

	$id_assessment = importVar('id_assess', true, 0);
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();
	$info = $assess_man->getAssessment($id_assessment);

	$lang =& DoceboLanguage::createInstance('preassessment');

	require_once(_base_.'/lib/lib.userselector.php');
	require_once(_base_.'/lib/lib.form.php');

	// instance assessment =================================================

	$user_select = new UserSelector();
	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = TRUE;

	if(isset($_POST['okselector']))
	{
		$acl_man = Docebo::user()->getAclManager();
		$selected = $user_select->getSelection($_POST);
		$selected =$acl_man->getAllUsersFromSelection($selected);
		if(!$assess_man->updateAssessmentUser($id_assessment, USER_ASSES_TYPE_USER, $selected)) Util::jump_to($url->getUrl('result=error_assign'));
		else Util::jump_to($url->getUrl('result=ok_assign'));
	}
	if(isset($_GET['load'])) {

		$user_select->requested_tab = PEOPLEVIEW_TAB;
		$selected = $assess_man->getAssessmentUser($id_assessment);
		$user_select->resetSelection($selected);
	}
	$user_select->addFormInfo(Form::getHidden('id_assess', 'id_assess', $id_assessment));

	$user_select->loadSelector($url->getUrl('op=modassessuser'),
			array( $url->getUrl() => $lang->def('_ASSESSMENT'),
				$lang->def('_ASSIGN_USERS').': '.strip_tags($info['name']) ),
			false,
			true);
}

function modassessadmin(&$url) {
	checkPerm('subscribe');

	$id_assessment = importVar('id_assess', true, 0);
	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();
	$info = $assess_man->getAssessment($id_assessment);

	$lang =& DoceboLanguage::createInstance('preassessment');

	require_once(_base_.'/lib/lib.userselector.php');
	require_once(_base_.'/lib/lib.form.php');

	// instance assessment =================================================

	$user_select = new UserSelector();
	$user_select->show_user_selector = TRUE;
	$user_select->show_group_selector = TRUE;
	$user_select->show_orgchart_selector = TRUE;

	if(isset($_POST['okselector'])) {

		$selected = $user_select->getSelection($_POST);
		if(!$assess_man->updateAssessmentUser($id_assessment, USER_ASSES_TYPE_ADMIN, $selected)) Util::jump_to($url->getUrl('result=error_assign'));
		else Util::jump_to($url->getUrl('result=ok_assign'));
	}
	if(isset($_GET['load'])) {

		$user_select->requested_tab = PEOPLEVIEW_TAB;
		$selected = $assess_man->getAssessmentAdministrator($id_assessment);
		$user_select->resetSelection($selected);
	}
	$user_select->addFormInfo(Form::getHidden('id_assess', 'id_assess', $id_assessment));

	$user_select->setPageTitle(getTitleArea(array(
		$url->getUrl() => $lang->def('_ASSESSMENT'),
		$lang->def('_ASSIGN_ADMIN_TITLE').': '.strip_tags($info['name']))
	, 'preassessment'));
	$user_select->loadSelector($url->getUrl('op=modassessadmin'),
			false,
			false,
			true);
}

// =========================================================================== //
// Managing the rules
// =========================================================================== //

function modrule(&$url) {
	checkPerm('mod');

	YuiLib::load(array(
		'animation' 		=> 'animation-min.js',
		'dragdrop' 			=> 'dragdrop-min.js',
		'button' 			=> 'button-min.js',
		'container' 		=> 'container-min.js',
		'my_window' 		=> 'windows.js'
	), array(
		'container/assets/skins/sam' => 'container.css',
		'button/assets/skins/sam' => 'button.css'
	));
	addJs($GLOBALS['where_lms_relative'].'/admin/modules/preassessment/', 'ajax.preassessment.js');

	$id_assessment = importVar('id_assess', true, 0);

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	$course_man =  new Man_Course();

	require_once($GLOBALS['where_lms'].'/lib/lib.coursepath.php');
	$coursepath_man =  new CoursePath_Manager();

	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();
	$rule_man = new AssessmentRule();

	$lang =& DoceboLanguage::createInstance('preassessment');

	// recover assessment
	$assessment = $assess_man->getAssessment($id_assessment);
	$rule_list = $rule_man->getAllRule($id_assessment);

	// recover new type one
	require_once(_base_.'/lib/lib.table.php');
	$tb = new Table(0, $lang->def('_CAPTION_PREASSESSMENT_RULE'), $lang->def('_SUMMARY_PREASSESSMENT_RULE'));

	// table header --------------------------------------------------------------------------------------------
	$cont_h = array(
		$lang->def('_RULE_TEXT'),
		$lang->def('_RULE_ACTION'),
		'<img src="'.getPathImage('lms').'standard/modelem.png" alt="'.$lang->def('_ALT_ADD_EFFECT').'" />',
		'<img src="'.getPathImage('lms').'standard/edit.png" alt="'.$lang->def('_MOD').'" />',
		'<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_DEL').'" />'
	);
	$type_h = array('nowrap', '', 'image', 'image', 'image');

	$usedef = 1;
	$tb->addHead($cont_h, $type_h);
	while($rule = $rule_man->fetch_row($rule_list)) {

		$id = $rule[RULE_ID];
		if($rule[RULE_TYPE] == RULE_DEFAULT) $usedef = 0;
		$rule_name = $rule_man->resolveRuleTypePhrase($lang, $rule);
		$cont = array($rule_name);
		$effect = '';

		$effects = $rule_man->parseEffects($rule[RULE_EFFECT]);
		if(count($effects['course']) > 0) {
			$effect .= $lang->def('_COURSES').': '.$course_man->listCourseName($effects['course']);
		}
		if(count($effects['coursepath']) > 0) {
			if(count($effects['course']) > 0) $effect .= '<br/>';
			$path_list =& $coursepath_man->getNames($effects['coursepath']);
			$effect .= $lang->def('_COURSEPATH').': '.implode(', ', $path_list);
		}
		if((count($effects['course']) == 0) && (count($effects['coursepath']) == 0)) $effect = $lang->def('_DO_NOTHING');

		$cont[] = $effect;
		$cont[] = '<a href="'.$url->getUrl('op=assignrule&id_rule='.$id.'&load=1').'" title=""'.$lang->def('_ADD_EFFECT').'">'
			.'<img src="'.getPathImage('lms').'standard/modelem.png" alt="'.$lang->def('_ALT_ADD_EFFECT').'" />'
			.'</a>';
		$cont[] = '<a href="'.$url->getUrl('op=modrulet&id_rule='.$id).'" title="'.$lang->def('_MOD_TITLE').'"'
      .'" onclick="mod_rule_mask(\''.$id_assessment.'\', \''.$id.'\'); return false;"'
      .'>'
			.'<img src="'.getPathImage('lms').'standard/edit.png" alt="'.$lang->def('_MOD').'" />'
			.'</a>';
		$cont[] = '<a href="'.$url->getUrl('op=delrule&id_rule='.$id).'" title="'.$lang->def('_DEL').': '.$rule_name.'"'
      .'	onclick="del_assessment_rule(\''.$id.'\', \''.strip_tags($rule_name).'\'); return false;"'
      .'>'
			.'<img src="'.getPathImage('lms').'standard/delete.png" alt="'.$lang->def('_DEL').'" />'
			.'</a>';

		$tb->addBody($cont);

	}

	require_once(_base_.'/lib/lib.dialog.php');
	setupHrefDialogBox('a[href*=delrule]');

	$tb->addActionAdd(
		'<a class="new_element_link" '
		.' 	onclick="add_rule_mask(\''.$id_assessment.'\', \''.$usedef.'\'); return false;"'
		.' id="add_rule" '
		.'href="#" '//.'	href="'.$url->getUrl('op=modrule&id_assess='.$id_assessment).'"'.
		.'>'.$lang->def('_ADD_RULE').'</a>'
	);

	$GLOBALS['page']->add('<script type="text/javascript">'
    .'YAHOO.util.Event.onDOMReady( function(e) {'
		  .' setup_assessment(); '
		  .' YAHOO.util.Event.addListener("add_rule", "click", function(e) { add_rule_mask(\''.$id_assessment.'\', \''.$usedef.'\'); });'
		  .'});'
		.'</script>', 'page_head');

	$GLOBALS['page']->add(
		getTitleArea(	array($url->getUrl() => $lang->def('_ASSESSMENT'), $lang->def('_RULES').': '.$assessment['name']), 'preassessment')
		.'<div class="std_block">'
		.getInfoUi($lang->def('_RULE_OVERLAPPED'))
	, 'content');

	if(isset($_GET['result'])) $GLOBALS['page']->add(guiResultStatus($lang, $_GET['result']), 'content');

	$GLOBALS['page']->add($tb->getTable(), 'content');

	$GLOBALS['page']->add(
		getBackUi($url->getUrl(), $lang->def('_BACK'))
		.'</div>', 'content');
}

function assignrule(&$url) {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

	$id_rule = importVar('id_rule', true, 0);

	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$assess_man = new AssessmentList();
	$rule_man = new AssessmentRule();

	$lang =& DoceboLanguage::createInstance('preassessment');

	// recover assessment
	$rule = $rule_man->getRule($id_rule);
	$id_assessment = $rule[RULE_ID_ASSESS];

	$assess = $assess_man->getAssessment($id_assessment);

	$sel = new Course_Manager();
	$sel->show_catalogue_selector = false;
	$sel->setLink($url->getUrl('op=assignrule'));

	if(isset($_POST['cancel_assign'])) Util::jump_to($url->getUrl('&op=modrule&id_assess='.$id_assessment));
	if(isset($_GET['load'])) {

		$effects = $rule_man->parseEffects($rule[RULE_EFFECT]);
		if(isset($_GET['load'])) {
			$sel->resetCourseSelection($effects['course']);
			$sel->resetCoursePathSelection($effects['coursepath']);
		}
	}
	if(isset($_POST['save_selection'])) {

		$re = true;
		$course = $sel->getCourseSelection($_POST);
		$coursepath = $sel->getCoursePathSelection($_POST);
		$re = $rule_man->setEffects($id_rule, $course, $coursepath);
		Util::jump_to( $url->getUrl('&op=modrule&id_assess='.$id_assessment.'&result='.( $re ? 'ok_rule' : 'err_rule' )) );
	}

	$rule_name = $rule_man->resolveRuleTypePhrase($lang, $rule);

	$GLOBALS['page']->addStart(
		getTitleArea(	array($url->getUrl() => $lang->def('_ASSESSMENT'),
			$url->getUrl('&op=modrule&id_assess='.$id_assessment) => $lang->def('_RULES').': '.$assess['name'],
			'"'.$rule_name.'"'), 'preassessment')
		.'<div class="std_block">'
		.Form::openForm('man_assign_rule', $url->getUrl('op=assignrule') )
		.Form::getHidden('id_rule', 'id_rule', $id_rule)
		, 'content' );

	$GLOBALS['page']->addEnd(
		Form::openButtonSpace()
		.Form::getButton('save_selection', 'save_selection', $lang->def('_SAVE'))
		.Form::getButton('cancel_assign', 'cancel_assign', $lang->def('_UNDO'))
		.Form::closeButtonSpace()
		.Form::closeForm()
		.'</div>'
		, 'content' );

	$sel->loadSelector();
}

function saverule(&$url) {
	checkPerm('mod');

	$id_assessment = importVar('id_assessment', true, 0);
	$id_rule = importVar('id_rule', true, 0);

	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$rule_man = new AssessmentRule();

	$data = array('id_assessment' => $id_assessment, 'rule_type' => $_POST['rule_type']);
	switch($_POST['rule_type']) {
		case RULE_DEFAULT 	: $data['rule_setting'] = '';break;
		case RULE_GREATER 	: $data['rule_setting'] = $rule_man->compressRule($_POST['rule_type'], array($_POST['score_type_one']));break;
		case RULE_LESSER 	: $data['rule_setting'] = $rule_man->compressRule($_POST['rule_type'], array($_POST['score_type_one']));break;
		case RULE_BETWEEN 	: $data['rule_setting'] = $rule_man->compressRule($_POST['rule_type'], array($_POST['score_type_one'], $_POST['score_type_two']));break;
	}
	$id_rule = $rule_man->saveRule($id_rule, $data);

	if($id_rule) Util::jump_to($url->getUrl('&op=modrule&id_assess='.$id_assessment.'&result=ok_ins_rule'));
	else Util::jump_to($url->getUrl('&op=modrule&id_assess='.$id_assessment.'&result=err_ins_rule'));
}

function delrule(&$url) {
	checkPerm('mod');

	$id_rule = importVar('id_rule', true, 0);

	require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
	$rule_man = new AssessmentRule();
	$rule = $rule_man->getRule($id_rule);

	if($rule_man->deleteRule($id_rule)) Util::jump_to($url->getUrl('&op=modrule&id_assess='.$rule[RULE_ID_ASSESS].'&result=ok_delete'));
	else Util::jump_to($url->getUrl('&op=modrule&id_assess='.$rule[RULE_ID_ASSESS].'&result=err_delete'));
}

// =========================================================================== //
// Dispatch actions
// =========================================================================== //

function preAssessmentDispatch($op) {

	Util::get_js(Get::rel_path('lms').'/admin/modules/preassessment/ajax.preassessment.js', true, true);

	require_once(_base_.'/lib/lib.urlmanager.php');
	$url =& UrlManager::getInstance();
	$url->setStdQuery('modname=preassessment&op=assesmentlist');

	if(isset($_POST['undo'])) $op = 'assesmentlist';
	if(isset($_POST['cancelselector'])) $op = 'assesmentlist';

	switch($op) {
		case "assesmentlist" : {
			assesmentlist($url);
		};break;
		case "modassessment" : {
			modassessment($url);
		};break;
		case "delassessment" : {
			delassessment($url);
		};break;
		// user management ----------------------------------------
		case "modassessuser" : {
			modassessuser($url);
		};break;
		case "modassessadmin" : {
			modassessadmin($url);
		};break;
		// manage management --------------------------------------
		case "manageassessment" : {
			checkPerm('mod');

			$id_assessment = importVar('id_assess', true, 0);

			require_once($GLOBALS['where_lms'].'/lib/lib.preassessment.php');
			$assess_man = new AssessmentList();

			if(!$assess_man->addAssessmentUser($id_assessment, USER_ASSES_TYPE_ADMIN, array(getLogUserId()))) {

				Util::jump_to($url->getUrl('result=error_assign'));
			} else {

				require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
				$subs_man = new CourseSubscribe_Management();
				$subs_man->multipleUserSubscribe(	getLogUserId(),
											array($id_assessment),
											6);

				Docebo::user()->loadUserSectionST();
				Docebo::user()->SaveInSession();

				Util::jump_to($GLOBALS['where_lms_relative'].'/index.php?modname=course&op=aula&idCourse='.$id_assessment.'&from_admin=1');

			}
		};break;
		// rule management ----------------------------------------
		case "modrule" : {
			modrule($url);
		};break;
		case "assignrule" : {
			assignrule($url);
		};break;
		case "saverule" : {
			saverule($url);
		};break;
		case "delrule" : {
			delrule($url);
		};break;
	}
}

?>