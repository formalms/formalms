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
 * @package  DoceboLms
 * @version  $Id: course.php 1002 2007-03-24 11:55:51Z fabio $
 * @category course
 */

require_once(_lms_.'/lib/lib.levels.php');
function statusNoEnter($perm, $status) { return ( $perm & (1 << $status) ); }


define("_SUCCESS_k", 			"_OPERATION_SUCCESSFUL");
define("_SUCCESS_course", 		"_OPERATION_SUCCESSFUL");
define("_SUCCESS_subs", 		"_OPERATION_SUCCESSFUL");
define("_SUCCESS_ins",			"_OPERATION_SUCCESSFUL");
define("_SUCCESS_unsub", 		"_OPERATION_SUCCESSFUL");

define("_FAIL_course", 			"_OPERATION_FAILURE");
define("_FAIL_courseedition", 	"_OPERATION_FAILURE");
define("_FAIL_coursemenu", 		"_OPERATION_FAILURE");
define("_FAIL_selempty", 		"_EMPTY_SELECTION");
define("_FAIL_sub", 			"_OPERATION_FAILURE");



function course() {
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_lms_.'/lib/lib.course.php');

	$GLOBALS['page']->setWorkingZone('content');
	
	$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');
	$db		=& DbConn::getInstance();

	Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true, true);

	// Setup the tree viewer class ----------------------------------------------
	require_once(_lms_.'/lib/folder_tree/class.category_tree.php');
	$treeView = new CategoryFolderTree('courses_categories', true);
	$treeView->useDOMReady = true; //to change
	$treeView->isGlobalVariable = true; //just for debug purpose
	$treeView->initLibraries();
	
	$print_tree = $treeView->get();

	// Setup the course table ---------------------------------------------------

	//$man_course = new Man_Course();
	//$course_list = $man_course->getCoursesRequest($table_status['startIndex'], Get::sett('visuItem'), $table_status['sort'], $table_status['dir']);
	//$db->num_rows($course_list),
	//$man_course->getCoursesCountFiltered(),

	if (!isset($_SESSION['course_category']['filter_status'])) {
		$_SESSION['course_category']['filter_status'] = array(
			'c_category'	=> 0,
			'c_filter'		=> '',
			'c_flatview'	=> false,
			'c_waiting'		=> false
		);
	}

	$filter_status =& $_SESSION['course_category']['filter_status'];

	if(isset($filter_status['c_flatview']) || isset($filter_status['c_waiting']))
		$open = true;
	else
		$open = false;

	if (!isset($_SESSION['course_category']['table_status'])) {
		$_SESSION['course_category']['table_status'] = array(
			'startIndex'	=> 0,
			'sort'			=> 'name',
			'dir'			=> 'asc'
		);
	}
	$table_status =& $_SESSION['course_category']['table_status'];

	// Setup the course table ---------------------------------------------------

	$table_config = array(
		'startIndex'	=> $table_status['startIndex'],
		'results'		=> Get::sett('visuItem'),
		'sort'			=> $table_status['sort'],
		'dir'			=> $table_status['dir'],
		'pageSize'		=> 0, //we use the dynamic loading, this data will be populated by the ajax request
		'totalRecords'	=> 0, //we use the dynamic loading, this data will be populated by the ajax request
		'recordsReturned' => array() //we use the dynamic loading, this data will be populated by the ajax request
	);

	require_once(_lms_.'/lib/table_view/class.coursetableview.php');

	$tableView = new CourseTableView("courses_list");
	$tableView->useDOMReady = true; //to change
	$tableView->isGlobalVariable = true; //just for debug purpose
	$tableView->initLibraries();
	$tableView->setInitialData($table_config);

	$print_table = $tableView->get();

	//Script for saving data from inline editor
	
	cout("\n".'<script type="text/javascript">
			function saveData(callback, newData) {
			var new_value = newData;
			var col = this.getColumn().key;
			var old_value =  this.value;
			var datatable = this.getDataTable();
			var idCourse = this.getRecord().getData("idCourse");

			var myCallback =
			{
				success: function(o)
				{
					var r = YAHOO.lang.JSON.parse(o.responseText);
					if (r.success)
					{
						callback(true, stripSlashes(r.new_value));
					}
					else
					{
						callback(true, stripSlashes(r.old_value));
					}
				},
				failure:
				{
				}
			}

			var post =	"idCourse=" + idCourse
						+"&col=" + col
						+"&new_value=" + new_value
						+"&old_value=" + old_value;

			var url = "ajax.adm_server.php?plf=lms&file=coursetableview&sf=table_view&command=updateField&";

			YAHOO.util.Connect.asyncRequest("POST", url, myCallback, post);
			}
		</script>');

	// Js needed for this page in order to work
	cout("\n".'<script type="text/javascript">
	//<!--
	course_tree_options = '.$print_tree['options'].';
	-->
	</script>', 'scripts');
	cout("\n".'<script type="text/javascript">
	//<!--
	course_table_options = '.$print_table['options'].';
	--></script>', 'scripts');
	cout("\n".'<script type="text/javascript" src="'.Get::rel_path('lms').'/admin/modules/course/course.js"></script>', 'scripts');

	cout(getTitleArea($lang->def('_COURSES'), 'course')
		.'<div class="std_block">'
		.( isset($_GET['result']) ? guiResultStatus($lang, $_GET['result']) : '' )
	, 'content');

	// Show tree
	cout('<h2>'.$lang->def('_CATEGORY').'</h2>'
		.$print_tree['html']
		.'<div class="folder_action_space">'
			.'<a id="add_folder_button" class="ico-wt-sprite subs_add" href="#"><span>'.$lang->def('_NEW_CATEGORY').'</span></a>'
		.'</div>'
	, 'content');

	// Show filter
	cout('<div class="quick_search_form">'

		.'<div class="common_options">'

		.Form::getInputCheckbox('c_flatview', 'c_flatview', '1', ($filter_status['c_flatview'] ? true : false), '')
			.' <label class="label_normal" for="c_flatview">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>'
			.'&nbsp;&nbsp;&nbsp;&nbsp;'
		.Form::getInputCheckbox('c_waiting', 'c_waiting', '1', ($filter_status['c_waiting'] ? true : false), '')
			.' <label class="label_normal" for="c_waiting">'.Lang::t('_WAITING_USERS', 'organization_chart').'</label>'

		.'</div>'
		.'<br />'
		.'<div>'


		.Form::openForm('course_filters', 'index.php?modname=course&amp;op=course_list')
		.Form::getInputTextfield( "search_t", "c_filter", "c_filter", $filter_status['c_filter'], '', 255, '' ) //TO DO: value from $_SESSION
		.Form::getButton( "c_filter_set", "c_filter_set", Lang::t('_SEARCH', 'standard'), "search_b")
		.Form::getButton( "c_filter_reset", "c_filter_reset", Lang::t('_RESET', 'standard'), "reset_b")
		/*
		.'</div>'
		.'<div>'
		.'<a class="advanced_search" href="javascript:;" onclick="( this.nextSibling.style.display != \'block\' ?  this.nextSibling.style.display = \'block\' : this.nextSibling.style.display = \'none\' );">'
		.$lang->def("_ADVANCED_SEARCH")
		.'</a>'
		.'<div class="overlay_menu" '.($open ? ' style="display: block;"' : ' style="display: none;"').'>'
		.Form::getCheckbox( $lang->def('_DIRECTORY_FILTER_FLATMODE'), 'c_flatview', 'c_flatview', '1',$filter_status['c_flatview'])
		.Form::getCheckbox( $lang->def('_WAITING_USERS'), 'c_waiting', 'c_waiting', '1',$filter_status['c_waiting'])
		.'</div>'
		*/
		.Form::closeForm()
		.'</div>'
		.'</div>'
	, 'content');

	// show course table
	cout(''.'<div class="table-container-over">'
			.(checkPerm('add', true, 'course', 'lms') ? '<a class="ico-wt-sprite subs_add" href="index.php?modname=course&amp;op=new_course"><span>'.$lang->def('_NEW_COURSE').'</span></a>' : '')
			.(checkPerm('subscribe', true, 'course', 'lms') ? '&nbsp;&nbsp;<a class="ico-wt-sprite subs_users" href="index.php?r=alms/subscription/multiplesubscription&amp;load=1"><span>'.Lang::t('_MULTIPLE_SUBSCRIPTION', 'course').'</span></a>' : '')
		.'</div>'
		.$print_table['html']
		.'<div class="table-container-below">'
			.(checkPerm('add', true, 'course', 'lms') ? '<a class="ico-wt-sprite subs_add" href="index.php?modname=course&amp;op=new_course"><span>'.$lang->def('_NEW_COURSE').'</span></a>' : '')
			.(checkPerm('subscribe', true, 'course', 'lms') ? '&nbsp;&nbsp;<a class="ico-wt-sprite subs_users" href="index.php?r=alms/subscription/multiplesubscription&amp;load=1"><span>'.Lang::t('_MULTIPLE_SUBSCRIPTION', 'course').'</span></a>' : '')
		.'</div>'
	, 'content');

	//set event listeners to filter elements
	cout('<script type="text/javascript">$E=YAHOO.util.Event; $E.onDOMReady( function(e) {
			$E.addListener("c_filter_set", "click", filterEvent);
			$E.addListener("c_filter_reset", "click", function() { YAHOO.util.Dom.get("c_filter").value=""; filterEvent; });
			$E.addListener("c_flatview", "click", filterEvent);
			$E.addListener("c_waiting", "click", filterEvent);
			$E.addListener("course_filters", "submit", function(ev) { filterEvent(ev); YAHOO.util.Event.preventDefault(ev); });
		});</script>', 'scripts');

	cout('</div>', 'content');
}

function manageCourseFile($new_file_id, $old_file, $path, $quota_available, $delete_old, $is_image = false) {

	$arr_new_file = ( isset($_FILES[$new_file_id]) && $_FILES[$new_file_id]['tmp_name'] != '' ? $_FILES[$new_file_id] : false);
	$return = array(	'filename' => $old_file,
						'new_size' => 0,
						'old_size' => 0,
						'error' => false,
						'quota_exceeded' => false);

	if(($delete_old || $arr_new_file !== false) && $old_file != '') {

		// the flag for file delete is checked or a new file was uploaded ---------------------
		$return['old_size'] = Get::file_size($GLOBALS['where_files_relative'].$path.$old_file);
		$quota_available -= $return['old_size'];
		sl_unlink($path.$old_file);
		$return['filename'] = '';
	}

	if(!empty($arr_new_file)) {

		// if present load the new file --------------------------------------------------------
		$filename = $new_file_id.'_'.mt_rand(0, 100).'_'.time().'_'.$arr_new_file['name'];
		if($is_image) {

			$re = createImageFromTmp(	$arr_new_file['tmp_name'],
										$path.$filename,
										$arr_new_file['name'],
										150,
										150,
										true );

			if($re < 0) $return['error'] = true;
			else {

				// after resize check size ------------------------------------------------------------
				$size = Get::file_size($GLOBALS['where_files_relative'].$path.$filename);
				if($quota_available != 0 && $size > $quota_available) {
					$return['quota_exceeded'] = true;
					sl_unlink($path.$filename);
				} else {
					$return['new_size'] = $size;
					$return['filename'] = $filename;
				}
			}
		} else {

			// check if the filesize don't exceed the quota ----------------------------------------
			$size = Get::file_size($arr_new_file['tmp_name']);

			if($quota_available != 0 && $size > $quota_available) $return['quota_exceeded'] = true;
			else {
				// save file ---------------------------------------------------------------------------
				if(!sl_upload($arr_new_file['tmp_name'], $path.$filename)) $return['error'] = true;
				else {
					$return['new_size'] = $size;
					$return['filename'] = $filename;
				}
			}
		}
	}
	return $return;
}

function maskModCourse(&$course, $new = false, $name_category = '') {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$form 	= new Form();

	$classroom = false;
	if(isset($_GET['type']) && $_GET['type'] === 'classroom')
		$classroom = true;

	//addAjaxJs();
	YuiLib::load();

	$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');

	$levels = CourseLevel::getLevels();

	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	//status of course -----------------------------------------------------
	$status = array(
		CST_PREPARATION => $lang->def('_CST_PREPARATION'),
		CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE'),
		CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED'),
		CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED'),
		CST_CANCELLED 	=> $lang->def('_CST_CANCELLED') );
	//difficult ------------------------------------------------------------
	$difficult_lang = array(
		'veryeasy' 		=> $lang->def('_DIFFICULT_VERYEASY'),
		'easy' 			=> $lang->def('_DIFFICULT_EASY'),
		'medium' 		=> $lang->def('_DIFFICULT_MEDIUM'),
		'difficult' 	=> $lang->def('_DIFFICULT_DIFFICULT'),
		'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'));
	//type of course -------------------------------------------------------
	$course_type= array (
		'elearning' 	=> $lang->def('_COURSE_TYPE_ELEARNING'),
		'blended' 		=> $lang->def('_COURSE_TYPE_BLENDED'),
		'classroom' 	=> $lang->def('_CLASSROOM'));
	// points policy -------------------------------------------------------
	$show_who_online = array(
		0				=> $lang->def('_DONT_SHOW'),
		_SHOW_COUNT 	=> $lang->def('_SHOW_COUNT'),
		_SHOW_INSTMSG 	=> $lang->def('_SHOW_INSTMSG')
	);

	if($new == true) {

		// menu availables -----------------------------------------------------
		$menu_custom = getAllCustom();
		list($sel_custom) = current($menu_custom);
		reset($menu_custom);
	}

	$out->add(
		$form->openElementSpace()
	);

	if($new == true)
		$out->add($form->getLineBox($lang->def('_CATEGORY_SELECTED'), $name_category));
	else
		$out->add($form->getDropdown($lang->def('_CATGORY'), 'idCategory', 'idCategory', getCategoryForDropdown(), $course['idCategory']));

	require_once(_lms_.'/admin/models/LabelAlms.php');
	$label_model = new LabelAlms();

	$out->add(
		$form->getTextfield($lang->def('_CODE'), 		'course_code', 		'course_code', 		'50', 	$course['code'])
		.$form->getTextfield($lang->def('_COURSE_NAME'), 		'course_name', 		'course_name', 		'255', 	$course['name'])

		.$form->getDropdown($lang->def('_COURSE_LANG_METHOD'), 	'course_lang', 		'course_lang', 		$array_lang, 		array_search($course['lang_code'], $array_lang) )
		.$form->getDropdown($lang->def('_DIFFICULTY'), 	'course_difficult', 'course_difficult', $difficult_lang, 	$course['difficult'] )
		.($classroom ? $form->getHidden('course_type', 'course_type', 'classroom') : $form->getDropdown($lang->def('_COURSE_TYPE'), 		'course_type', 		'course_type', 		$course_type, 		$course['course_type'] ))
		.$form->getDropdown($lang->def('_STATUS'), 		'course_status', 	'course_status', 	$status, 			$course['status'] )
		.$form->getDropdown($lang->def('_LABEL'), 'label', 'label', $label_model->getLabelFromDropdown(true), ($new ? false : $label_model->getCourseLabel($course['idCourse'])))
		.($classroom ? '' : $form->getCheckbox($lang->def('_COURSE_EDITION'), 		'course_edition_yes', 'course_edition', 1, $course['course_edition'] == 1 ))

		.( $new == true
			? $form->getDropdown($lang->def('_COURSE_MENU_TO_ASSIGN'), 'selected_menu', 'selected_menu', $menu_custom, $sel_custom )
			: '' )

		.$form->getCheckbox($lang->def('_DIRECT_PLAY'), 	'direct_play', 	'direct_play', 	'1', $course['direct_play'] == 1 )

		.$form->getCheckbox($lang->def('_SHOW_RESULTS'), 	'show_result', 	'show_result', 	'1', $course['show_result'] == 1 )

		.$form->getTextarea($lang->def('_DESCRIPTION'), 'course_descr', 		'course_descr', 	$course['description'])

		.'<div class="align_center">'
			.str_replace('[down]', '',
				$lang->def('_COURSE_MORE_OPTION'))
		.'</div>'

		.( !$new && !$classroom
			? $form->getCheckbox($lang->def('_CASCADE_MOD_ON_EDITION'), 'cascade_on_ed', 'cascade_on_ed', 1)
			: '' )

		.$form->closeElementSpace()

		.$form->openElementSpace()

		.$form->getOpenFieldset($lang->def('_COURSE_SUBSCRIPTION'))

		//-----------------------------------------------------------------
		.$form->getOpenCombo($lang->def('_USER_CAN_SUBSCRIBE'))
		.$form->getRadio($lang->def('_SUBSCRIPTION_CLOSED'), 		'subscription_closed', 	'can_subscribe', '0', $course['can_subscribe'] == 0 )
		.$form->getRadio($lang->def('_SUBSCRIPTION_OPEN'), 			'subscription_open', 	'can_subscribe', '1', $course['can_subscribe'] == 1 )
		.$form->getRadio($lang->def('_SUBSCRIPTION_IN_PERIOD').":", 'subscription_period', 	'can_subscribe', '2', $course['can_subscribe'] == 2 )
		.$form->getCloseCombo()

		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_BEGIN').":", 	'sub_start_date', 	'sub_start_date', 	$course['sub_start_date'] )
		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_END').":", 		'sub_end_date', 	'sub_end_date', 	$course['sub_end_date'] )
		.$form->getBreakRow()

		.$form->getTextfield($lang->def('_COURSE_AUTOREGISTRATION_CODE'), 'course_autoregistration_code', 'course_autoregistration_code', '255', $course['autoregistration_code'])
		.$form->getCheckbox($lang->def('_RANDOM_COURSE_AUTOREGISTRATION_CODE'), 'random_course_autoregistration_code', 'random_course_autoregistration_code', 0)
		.$form->getCloseFieldset()

		//-display-mode----------------------------------------------------

		.$form->getOpenFieldset($lang->def('_COURSE_DISPLAY_MODE'))

		//-where-show-course----------------------------------------------
		.$form->getOpenCombo($lang->def('_WHERE_SHOW_COURSE'))
		.$form->getRadio($lang->def('_SC_EVERYWHERE'), 			'course_show_rules_every', 			'course_show_rules', '0', $course['show_rules'] == 0 )
		.$form->getRadio($lang->def('_SC_ONLY_IN'), 			'course_show_rules_only_in', 		'course_show_rules', '1', $course['show_rules'] == 1 )
		.$form->getRadio($lang->def('_SC_ONLYINSC_USER'), 		'course_show_rules_onlyinsc_user', 	'course_show_rules', '2', $course['show_rules'] == 2 )
		.$form->getCloseCombo()

		//-what-show------------------------------------------------------
		.$form->getOpenCombo($lang->def('_WHAT_SHOW'))
		.$form->getCheckbox($lang->def('_SHOW_PROGRESS'), 		'course_progress', 	'course_progress', 	'1', $course['show_progress'] == 1 )
		.$form->getCheckbox($lang->def('_SHOW_TIME'), 			'course_time', 		'course_time', 		'1', $course['show_time'] == 1 )

		.$form->getCheckbox($lang->def('_SHOW_ADVANCED_INFO'), 	'course_advanced', 	'course_advanced', 	'1', $course['show_extra_info'] == 1 )
		.$form->getCloseCombo()

		.$form->getDropdown($lang->def('_SHOW_WHOISONLINE'),	 'show_who_online', 	'show_who_online', 	$show_who_online, $course['show_who_online'] )

		//-list-of-user---------------------------------------------------
		.$form->getOpenCombo($lang->def('_SHOW_USER_OF_LEVEL'))
	, 'content');
	
	while(list($level, $level_name) = each($levels)) {

		$out->add($form->getCheckbox($level_name, 'course_show_level_'.$level, 'course_show_level['.$level.']', $level, $course['level_show_user'] & (1 << $level) ));
	}
	$out->add(
		$form->getCloseCombo()

		.$form->getCloseFieldset()

		//-user-interaction--------------------------------------------------

		.$form->getOpenFieldset($lang->def('_USER_INTERACTION_OPTION'))

		//-subscribe-method-----------------------------------------------
		.$form->getOpenCombo($lang->def('_COURSE_SUBSRIBE'))
		.$form->getRadio($lang->def('_COURSE_S_GODADMIN'), 		'course_subs_godadmin', 'course_subs', 	'0', 	$course['subscribe_method'] == 0 )
		.$form->getRadio($lang->def('_COURSE_S_MODERATE'), 		'course_subs_moderate', 'course_subs', 	'1', 	$course['subscribe_method'] == 1 )
		.$form->getRadio($lang->def('_COURSE_S_FREE'), 			'course_subs_free', 	'course_subs', 	'2', 	$course['subscribe_method'] == 2 )
		.$form->getCloseCombo()
	);

	$pl_man =& PlatformManager::CreateInstance();
	if($pl_man->isLoaded('ecom') || 1) {

		$out->add(
			$form->getCheckbox($lang->def('_COURSE_SELL'), 		'course_sell', 			'course_sell', 	'1', 	$course['selling'] == 1 )
			.$form->getTextfield($lang->def('_COURSE_PRIZE'), 		'course_prize', 		'course_prize', '11', 	$course['prize'])
			.$form->getTextfield($lang->def('_COURSE_ADVANCE'), 	'advance', 				'advance', 		'11', 	$course['advance'])
		);
	} else {
		$out->add(
			$form->getHidden('course_prize', 		'course_prize', '0')
			.$form->getHidden('advance', 				'advance', 	'0')
		);
	}

		// mode for course end--------------------------------------------
	$out->add(
		$form->getHidden('course_em', 'course_em', '0')

		//status that can enter------------------------------------------
		.$form->getOpenCombo($lang->def('_COURSE_STATUS_CANNOT_ENTER'))
		.$form->getCheckbox($lang->def('_USER_STATUS_SUBS'), 	'user_status_'._CUS_SUBSCRIBED, 'user_status['._CUS_SUBSCRIBED.']', _CUS_SUBSCRIBED,
			$course['userStatusOp'] & (1 << _CUS_SUBSCRIBED))
		.$form->getCheckbox($lang->def('_USER_STATUS_BEGIN'), 	'user_status_'._CUS_BEGIN, 		'user_status['._CUS_BEGIN.']', 		_CUS_BEGIN,
			$course['userStatusOp'] & (1 << _CUS_BEGIN))
		.$form->getCheckbox($lang->def('_USER_STATUS_END'), 	'user_status_'._CUS_END, 		'user_status['._CUS_END.']', 		_CUS_END,
			$course['userStatusOp'] & (1 << _CUS_END))
		.$form->getCheckbox($lang->def('_USER_STATUS_SUSPEND'), 'user_status_'._CUS_SUSPEND, 	'user_status['._CUS_SUSPEND.']',	 _CUS_SUSPEND,
			$course['userStatusOp'] & (1 << _CUS_SUSPEND) )
		.$form->getCloseCombo()

		.$form->getCloseFieldset());

	//-expiration---------------------------------------------------------
	$hours = array('-1' => '- -', '0' =>'00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
					'10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
					'20', '21', '22', '23' );
	$quarter = array('-1' => '- -', '00' => '00', '15' => '15', '30' => '30', '45' => '45');

	if($course['hour_begin'] != '-1') {
		$hb_sel = (int)substr($course['hour_begin'], 0, 2);
		$qb_sel = substr($course['hour_begin'], 3, 2);
	} else $hb_sel = $qb_sel = '-1';

	if($course['hour_end'] != '-1') {
		$he_sel = (int)substr($course['hour_end'], 0, 2);
		$qe_sel = substr($course['hour_end'], 3, 2);
	} else $he_sel = $qe_sel = '-1';
	/*
	$out->add(
		'<script type="text/javascript">'
		."
		alert(cal_course_date_begin);
		cal_course_date_begin.onUpdate = function() {
			var new_date = \$F('course_date_begin');
			\$('course_date_end').value = new_date;
		}


		"
		.'</script>'
	, 'footer');*/

	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_TIME_OPTION'))
		.$form->getDatefield($lang->def('_DATE_BEGIN'), 		'course_date_begin', 	'course_date_begin', 	$course['date_begin'] )
		.$form->getDatefield($lang->def('_DATE_END'), 			'course_date_end', 		'course_date_end', 		$course['date_end'] )

		.$form->getLineBox(
			'<label for="hour_begin_hour">'.$lang->def('_HOUR_BEGIN').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_begin_hour', 'hour_begin[hour]', $hours, $hb_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_begin_quarter', 'hour_begin[quarter]', $quarter, $qe_sel, '')
		)

		.$form->getLineBox(
			'<label for="hour_end_hour">'.$lang->def('_HOUR_END').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_end_hour', 'hour_end[hour]', $hours, $he_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_end_quarter', 'hour_end[quarter]', $quarter, $qe_sel, '')
		)

		.$form->getTextfield($lang->def('_DAY_OF_VALIDITY'), 	'course_day_of', 		'course_day_of', 		'10', $course['valid_time'])
		.$form->getTextfield($lang->def('_MEDIUM_TIME'), 		'course_medium_time', 	'course_medium_time', 	'10', $course['mediumTime'])
		.$form->getCloseFieldset());

	//sponsor-and-logo----------------------------------------------------
	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_SPECIAL_OPTION'))
		
		.$form->getTextfield($lang->def('_MIN_NUM_SUBSCRIBE'), 	'min_num_subscribe', 	'min_num_subscribe', 	'11',
			$course['min_num_subscribe'])
		.$form->getTextfield($lang->def('_MAX_NUM_SUBSCRIBE'), 	'max_num_subscribe', 	'max_num_subscribe', 	'11',
			$course['max_num_subscribe'])
		.$form->getCheckbox($lang->def('_ALLOW_OVERBOOKING'), 	'allow_overbooking', 	'allow_overbooking', 	'1',
			$course['allow_overbooking'] == 1)
		.$form->getTextfield($lang->def('_COURSE_QUOTA'), 		'course_quota', 		'course_quota', 		'11',
			($course['course_quota'] != COURSE_QUOTA_INHERIT ? $course['course_quota'] : 0))
		.$form->getCheckbox($lang->def('_INHERIT_QUOTA'), 		'inherit_quota', 		'inherit_quota', 		'1',
			$course['course_quota'] == COURSE_QUOTA_INHERIT)

		.$form->getCloseFieldset()

		.$form->getOpenFieldset($lang->def('_DOCUMENT_UPLOAD'))
	);

	if($new == true) {

		$out->add(
			$form->getFilefield($lang->def('_USER_MATERIAL'), 'course_user_material', 'course_user_material')
			.$form->getFilefield($lang->def('_OTHER_USER_MATERIAL'), 'course_otheruser_material', 'course_otheruser_material')

			.$form->getTextfield($lang->def('_SPONSOR_LINK'), 'course_sponsor_link', 'course_sponsor_link', '255', $course['linkSponsor'])

			.$form->getFilefield($lang->def('_SPONSOR_LOGO'), 'course_sponsor_logo', 'course_sponsor_logo')
			.$form->getFilefield($lang->def('_COURSE_LOGO'), 'course_logo', 'course_logo')
			.$form->getFilefield($lang->def('_COURSE_DEMO'), 'course_demo', 'course_demo')

		);
	} else {

		$out->add(
			$form->getExtendedFilefield($lang->def('_USER_MATERIAL'), 'course_user_material', 'course_user_material', $course["img_material"])
			.$form->getExtendedFilefield($lang->def('_OTHER_USER_MATERIAL'),'course_otheruser_material', 'course_otheruser_material', $course["img_othermaterial"])

			.$form->getTextfield($lang->def('_SPONSOR_LINK'), 'course_sponsor_link', 'course_sponsor_link', '255', $course['linkSponsor'])

			.$form->getExtendedFilefield($lang->def('_SPONSOR_LOGO'),'course_sponsor_logo', 'course_sponsor_logo', $course["imgSponsor"])
			.$form->getExtendedFilefield($lang->def('_COURSE_LOGO'),'course_logo', 'course_logo', $course["img_course"])
			.$form->getExtendedFilefield($lang->def('_COURSE_DEMO'),'course_demo', 'course_demo', $course["course_demo"])
		);
	}
	$out->add(

		$form->getCheckbox($lang->def('_USE_LOGO_IN_COURSELIST'), 'use_logo_in_courselist', 'use_logo_in_courselist', 1, $course["use_logo_in_courselist"])

		.$form->getCloseFieldset()
		.$form->closeElementSpace()
	);
}

function getCategoryForDropdown()
{
	$query =	"SELECT idCategory, path, lev"
				." FROM %lms_category"
				." ORDER BY iLeft";

	$result = sql_query($query);
	$res = array('0' => 'root');

	while(list($id_cat, $path, $level) = sql_fetch_row($result))
	{
		$name = end(explode('/', $path));

		for($i = 0; $i < $level; $i++)
			$name = '&nbsp;&nbsp;'.$name;
		
		$res[$id_cat] = $name;
	}

	return $res;
}

function addCourse() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	require_once(dirname(__FILE__).'/../category/category.php');
	require_once(dirname(__FILE__).'/../category/tree.category.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');

	require_once($GLOBALS['where_lms'].'/lib/category/class.categorytree.php');

	$form 	= new Form();
	$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');

	// tree for categories ------------------------------------------------
	$categoryDb 	= new CategoryTree();
	$id_category 	= (isset($_SESSION['course_category']['filter_status']['c_category']) ? (int)$_SESSION['course_category']['filter_status']['c_category'] : 0);
	$name_category	= $categoryDb->getFolderById($id_category);
	$name_category	= end(explode("/", $name_category->path));
	
	// -------------------------------------------------------------------

	$course = array(
		'autoregistration_code' => '',
		'code' 				=> '',
		'name' 				=> '',
		'lang_code' 		=> getLanguage(),
		'difficult' 		=> 'medium',
		'course_type' 		=> 'elearning',
		'status' 			=> CST_EFFECTIVE,
		'course_edition' 	=> 0,
		'description' 		=> '',
		'can_subscribe' 	=> 1,
		'sub_start_date' 	=> '',
		'sub_end_date' 		=> '',
		'show_rules' 		=> 0,
		'show_progress' 	=> 1,
		'show_time' 		=> 1,
		'show_who_online' 	=> 1,
		'show_extra_info' 	=> 0,
		'level_show_user' 	=> 0,
		'subscribe_method' 	=> 2,
		'selling' 			=> 0,
		'prize' 			=> '',
		'advance' 			=> '',
		'permCloseLO' 		=> 0,
		'userStatusOp' 		=> (1 << _CUS_SUSPEND),
		'direct_play'		=> 0,

		'date_begin' 		=> '',
		'date_end' 			=> '',
		'hour_begin' 		=> '-1',
		'hour_end' 			=> '-1',

		'valid_time' 		=> '0',
		'mediumTime' 		=> '0',
		//'policy_point' 		=> 'nopoints',
		'min_num_subscribe' => '0',
		'max_num_subscribe' => '0',
		'allow_overbooking' => '',
		'course_quota' 		=> '',
		'show_result' 		=> '0',

		'linkSponsor' 		=> 'http://',


		'use_logo_in_courselist' => '1'
	);

	// -------------------------------------------------------------------

	$title_area = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE'),
		$lang->def('_NEW_COURSE')
	);

	cout(

		getTitleArea($title_area, 'course')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=course&amp;op=course_list', $lang->def('_BACK') )

		.$form->getFormHeader($lang->def('_NEW_COURSE'))
		.$form->openForm('course_creation', 'index.php?modname=course&amp;op=add_course', false, false, 'multipart/form-data')

		.$form->getHidden('idCategory', 'idCategory', $id_category)

	, 'content');

	maskModCourse($course, true, $name_category);

	$GLOBALS['page']->add(
		$form->openButtonSpace()
		.$form->getButton('course_create', 'course_create', $lang->def('_CREATE'))
		.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()

		.$form->closeForm()
		.'</div>'
	, 'content');
}

function insCourse() {

	require_once(_base_.'/lib/lib.upload.php');
	require_once(_base_.'/lib/lib.multimedia.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');

	if($_POST['course_type'] === 'classroom')
		$url = 'index.php?r=alms/classroom/show&result=';
	else
		$url = 'index.php?modname=course&op=course_list&result=';

	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	$acl_man 		=& Docebo::user()->getAclManager();

	$id_custom = importVar('selected_menu');

	// calc quota limit
	$quota = $_POST['course_quota'];
	if(isset($_POST['inherit_quota'])) {
		$quota = Get::sett('course_quota');
		$_POST['course_quota'] = COURSE_QUOTA_INHERIT;
	}
	$quota = $quota * 1024 * 1024;

	$path = Get::sett('pathcourse');
	$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

	if($_POST['course_name'] == '') $_POST['course_name'] = Lang::t('_NO_NAME', 'course', 'lms');

	// restriction on course status ------------------------------------------
	$user_status = 0;
	if(isset($_POST['user_status'])) {
		while(list($status) = each($_POST['user_status'])) $user_status |= (1 << $status);
	}

	// level that will be showed in the course --------------------------------
	$show_level = 0;
	if(isset($_POST['course_show_level'])) {
		while(list($lv) = each($_POST['course_show_level'])) $show_level |= (1 << $lv);
	}

	// save the file uploaded -------------------------------------------------
	$file_sponsor 		= '';
	$file_logo 			= '';
	$file_material 		= '';
	$file_othermaterial = '';
	$file_demo 			= '';

	$error 				= false;
	$quota_exceeded 	= false;
	$total_file_size 	= 0;

	if(is_array($_FILES) && !empty($_FILES)) sl_open_fileoperations();
	// load user material ---------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_user_material',
									'',
									$path,
									($quota != 0 ? $quota - $total_file_size : false),
									false );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_material		= $arr_file['filename'];
	$total_file_size 	= $total_file_size + $arr_file['new_size'];

	// course otheruser material -------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_otheruser_material',
									'',
									$path,
									($quota != 0 ? $quota - $total_file_size : false),
									false );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_othermaterial	= $arr_file['filename'];
	$total_file_size 	= $total_file_size + $arr_file['new_size'];

	// course demo-----------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_demo',
									'',
									$path,
									($quota != 0 ? $quota - $total_file_size : false),
									false );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_demo			= $arr_file['filename'];
	$total_file_size 	= $total_file_size + $arr_file['new_size'];

	// course sponsor---------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_sponsor_logo',
									'',
									$path,
									($quota != 0 ? $quota - $total_file_size : false),
									false,
									true );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_sponsor		= $arr_file['filename'];
	$total_file_size 	= $total_file_size + $arr_file['new_size'];

	// course logo-----------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_logo',
									'',
									$path,
									($quota != 0 ? $quota - $total_file_size : false),
									false,
									true );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_logo			= $arr_file['filename'];
	$total_file_size 	= $total_file_size + $arr_file['new_size'];

	// ----------------------------------------------------------------------------------------------
	sl_close_fileoperations();


	if ($_POST["can_subscribe"] == "2")  {
		$sub_start_date = Format::dateDb($_POST["sub_start_date"], "date");
		$sub_end_date 	= Format::dateDb($_POST["sub_end_date"], "date");
	}

	$date_begin	= Format::dateDb($_POST['course_date_begin'], "date");
	$date_end 	= Format::dateDb($_POST['course_date_end'], "date");

	// insert the course in database -----------------------------------------------------------
	$hour_begin = '-1';
	$hour_end = '-1';
	if($_POST['hour_begin']['hour'] != '-1') {

		$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
		if($_POST['hour_begin']['quarter'] == '-1') $hour_begin .= ':00';
		else $hour_begin .= ':'.$_POST['hour_begin']['quarter'];
	}

	if($_POST['hour_end']['hour'] != '-1') {

		$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
		if($_POST['hour_end']['quarter'] == '-1') $hour_end .= ':00';
		else $hour_end .= ':'.$_POST['hour_end']['quarter'];
	}

	$query_course = "
	INSERT INTO ".$GLOBALS['prefix_lms']."_course
	SET idCategory 			= '".( isset($_POST['idCategory']) ? $_POST['idCategory'] : 0 )."',
		code 				= '".$_POST['course_code']."',
		name 				= '".$_POST['course_name']."',
		description 		= '".$_POST['course_descr']."',
		lang_code 			= '".$array_lang[$_POST['course_lang']]."',
		status 				= '".(int)$_POST['course_status']."',
		level_show_user 	= '".$show_level."',
		subscribe_method 	= '".(int)$_POST['course_subs']."',

		create_date			= '".date("Y-m-d H:i:s")."',

		linkSponsor 		= '".$_POST['course_sponsor_link']."',
		imgSponsor 			= '".$file_sponsor."',
		img_course 			= '".$file_logo."',
		img_material 		= '".$file_material."',
		img_othermaterial 	= '".$file_othermaterial."',
		course_demo 		= '".$file_demo."',

		mediumTime 			= '".$_POST['course_medium_time']."',
		permCloseLO 		= '".$_POST['course_em']."',
		userStatusOp 		= '".$user_status."',
		difficult 			= '".$_POST['course_difficult']."',

		show_progress 		= '".( isset($_POST['course_progress']) ? 1 : 0 )."',
		show_time 			= '".( isset($_POST['course_time']) ? 1 : 0 )."',

		show_who_online		= '".$_POST['show_who_online']."',

		show_extra_info 	= '".( isset($_POST['course_advanced']) ? 1 : 0 )."',
		show_rules 			= '".(int)$_POST['course_show_rules']."',

		direct_play 		= '".( isset($_POST['direct_play']) ? 1 : 0 )."',

		date_begin 			= '".$date_begin."',
		date_end 			= '".$date_end."',
		hour_begin 			= '".$hour_begin."',
		hour_end 			= '".$hour_end."',

		valid_time 			= '".(int)$_POST['course_day_of']."',

		min_num_subscribe 	= '".(int)$_POST['min_num_subscribe']."',
		max_num_subscribe 	= '".(int)$_POST['max_num_subscribe']."',
		selling 			= '".( isset($_POST['course_sell']) ? '1' : '0' )."',
		prize 				= '".$_POST['course_prize']."',

		course_type 		= '".$_POST['course_type']."',

		course_edition 		= '".( isset($_POST['course_edition']) ? 1 : 0) ."',

		course_quota 		= '".$_POST['course_quota']."',
		used_space			= '".$total_file_size."',
		allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
		can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
		sub_start_date 		= ".( $_POST["can_subscribe"] == '2' ? "'".$sub_start_date."'" : 'NULL' ).",
		sub_end_date 		= ".( $_POST["can_subscribe"] == '2' ? "'".$sub_end_date."'" : 'NULL' ).",

		advance 			= '".$_POST['advance']."',
		show_result 		= '".( isset($_POST["show_result"]) ? 1 : 0 )."',

		use_logo_in_courselist = '".( isset($_POST['use_logo_in_courselist']) ? '1' : '0' )."'";

	if (isset($_POST['random_course_autoregistration_code']))
	{
		$control = 1;
		$str = '';

		while ($control)
		{
			for($i = 0; $i < 10; $i++)
			{
				$seed = mt_rand(0, 10);
					if($seed > 5)
						$str .= mt_rand(0, 9);
					else
						$str .= chr(mt_rand(65, 90));

			}

			$control_query = "SELECT COUNT(*)" .
							" ".$GLOBALS['prefix_lms']."_course" .
							" WHERE autoregistration_code = '".$str."'";

			$control_result = sql_query($control_query);
			list($result) = sql_fetch_row($control_result);
			$control = $result;
		}

		$query_course .= ", autoregistration_code = '".$str."'";
	}
	else
		$query_course .= ", autoregistration_code = '".$_POST['course_autoregistration_code']."'";

	if(!sql_query($query_course)) {

		// course save failed, delete uploaded file

		if($file_sponsor != '') 	sl_unlink($path.$file_sponsor);
		if($file_logo != '') 		sl_unlink($path.$file_logo);
		if($file_material != '') 	sl_unlink($path.$file_material);
		if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
		if($file_demo != '') 		sl_unlink($path.$file_demo);

		Util::jump_to($url.'err_course');
	}

	// recover the id of the course inserted --------------------------------------------
	list($id_course) = sql_fetch_row(sql_query("SELECT LAST_INSERT_ID()"));

	require_once(_lms_.'/admin/models/LabelAlms.php');
	$label_model = new LabelAlms();

	$label = Get::req('label', DOTY_INT, 0);

	$label_model->associateLabelToCourse($label, $id_course);

	// add this corse to the pool of course visible by the user that have create it -----
	if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN)
	{
		require_once(_base_.'/lib/lib.preference.php');
		$adminManager = new AdminPreference();
		$adminManager->addAdminCourse($id_course, Docebo::user()->getIdSt());
	}

	//if the scs exist create a room ----------------------------------------------------
	if($GLOBALS['where_scs'] !== false) {

		require_once($GLOBALS['where_scs'].'/lib/lib.room.php');

		$rules = array(
					'room_name' => $_POST['course_name'],
					'room_type' => 'course',
					'id_source' => $id_course );
		//$admin_rules = getAdminRules();
		//$rules = array_merge($rules, $admin_rules);
		$re = insertRoom($rules);
	}
	$course_idst =& DoceboCourse::createCourseLevel($id_course);

	// create the course menu -----------------------------------------------------------
	if(!cerateCourseMenuFromCustom($id_custom, $id_course, $course_idst)) {

		Util::jump_to($url.'err_coursemenu');
	}

	/*
	// send alert -------------------------------------------------------------------------------
	require_once($GLOBALS['where_framework'] . '/lib/lib.eventmanager.php');

	$msg_composer = new EventMessageComposer();

	$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
	$msg_composer->setBodyLangText('email', '_ALERT_TEXT', array(	'[url]' => Get::sett('url'),
																	'[course_code]' => $_POST['course_code'],
																	'[course]' => $_POST['course_name'] ) );

	$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', array(	'[url]' => Get::sett('url'),
																	'[course_code]' => $_POST['course_code'],
																	'[course]' => $_POST['course_name'] ) );

	require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
	$course_man = new Man_Course();
	$recipients = $course_man->getIdUserOfLevel($id_course);
	createNewAlert(	'CoursePropModified',
					'course',
					'add',
					'1',
					'Inserted course '.$_POST['course_name'],
					$recipients,
					$msg_composer );
	*/
	Util::jump_to($url.( $error ? 'err_course' : 'ok_course' ).( $quota_exceeded ? '&limit_reach=1' : '' ));
}

function modCourse() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.tab.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	require_once(dirname(__FILE__).'/../category/category.php');
	require_once(dirname(__FILE__).'/../category/tree.category.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');

	$form 	= new Form();
	$lang 	=& DoceboLanguage::CreateInstance('course', 'lms');
	$out 	=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$levels = CourseLevel::getLevels();
	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	$form 	= new Form();
	$out->setWorkingZone('content');
	if (isset($_POST['mod_course'])) {
		list($id_course) = each($_POST['mod_course']);
	} else {
		$id_course = Get::req('idCourse', DOTY_INT, 0);
	}

	// load from post the setting for the selected tab
	// retrive course info
	$query_course = "
	SELECT idCourse,idCategory, code, name, description, lang_code, status, level_show_user, subscribe_method,
		linkSponsor, mediumTime, permCloseLO, userStatusOp, difficult,
		show_progress, show_time,

		show_who_online,

		show_extra_info, show_rules, date_begin, date_end, hour_begin, hour_end, sub_start_date, sub_end_date, valid_time,
		min_num_subscribe, max_num_subscribe, max_sms_budget,selling,prize,course_type,policy_point,point_to_all,course_edition,
		imgSponsor, img_course, img_material, img_othermaterial, course_demo, course_quota, allow_overbooking,
		can_subscribe, advance, autoregistration_code, direct_play, show_result

		, use_logo_in_courselist
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = '".(int)$id_course."'";

	$course = mysql_fetch_assoc(sql_query($query_course));
	
	$course['date_begin'] 	= Format::date($course['date_begin'], 'date');
	$course['date_end'] 	= Format::date($course['date_end'], 'date');
	$course['sub_start_date'] = Format::date($course['sub_start_date'], 'date');
	$course['sub_end_date'] = Format::date($course['sub_end_date'], 'date');

	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';
	$lang_code = array_search($course['lang_code'], $array_lang);


	// set page title ------------------------------------------------------------------
	$title_area = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE'),
		$lang->def('_MOD').' : '.$course['name']
	);

	// print opern form ----------------------------------------------------------------
	$out->add(
		getTitleArea($title_area, 'configuration')
		.'<div class="std_block">'
		.getBackUi( 'index.php?modname=course&amp;op=course_list', $lang->def('_BACK') )

		.$form->openForm('upd_course', 'index.php?modname=course&amp;op=upd_course', false, false, 'multipart/form-data')

		.$form->getHidden('mod_course_'.$id_course, 'mod_course['.$id_course.']', $id_course)
	, 'content');
	maskModCourse($course, false);
	
	$GLOBALS['page']->add(
		$form->openButtonSpace()
		.$form->getButton('update_course', 'upd_course', $lang->def('_SAVE'))
		.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))

		.$form->closeButtonSpace()
		.$form->closeForm()
		.'</div>'
	, 'content');
}

function courseUpdate() {

	require_once(_base_.'/lib/lib.upload.php');
	require_once(_base_.'/lib/lib.multimedia.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');

	$array_lang	 	= Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	$acl_man 		=& Docebo::user()->getAclManager();

	if($_POST['course_type'] === 'classroom')
		$url = 'index.php?r=alms/classroom/show&result=';
	else
		$url = 'index.php?modname=course&op=course_list&result=';

	if (isset($_POST['mod_course'])) {
		list($id_course) = each($_POST['mod_course']);
	} else {
		list($id_course) = $_GET['mod_course'];
	}

	require_once(_lms_.'/admin/models/LabelAlms.php');
	$label_model = new LabelAlms();

	$label = Get::req('label', DOTY_INT, 0);

	$label_model->associateLabelToCourse($label, $id_course);

	// calc quota limit
	$quota = $_POST['course_quota'];
	if(isset($_POST['inherit_quota'])) {
		$quota = Get::sett('course_quota');
		$_POST['course_quota'] = COURSE_QUOTA_INHERIT;
	}
	$quota = $quota*1024*1024;

	$course_man = new DoceboCourse($id_course);
	$used = $course_man->getUsedSpace();

	if($_POST['course_name'] == '') $_POST['course_name'] = Lang::t('_NO_NAME', 'course', 'lms');

	// restriction on course status ------------------------------------------
	$user_status = 0;
	if(isset($_POST['user_status'])) {
		while(list($status) = each($_POST['user_status'])) $user_status |= (1 << $status);
	}

	// level that will be showed in the course --------------------------------
	$show_level = 0;
	if(isset($_POST['course_show_level'])) {
		while(list($lv) = each($_POST['course_show_level'])) $show_level |= (1 << $lv);
	}

	// save the file uploaded -------------------------------------------------

	$error 			= false;
	$quota_exceeded = false;

	$path = Get::sett('pathcourse');
	$path = '/appLms/'.Get::sett('pathcourse').( substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

	$old_file_size 	= 0;
	if ((is_array($_FILES) && !empty($_FILES)) || (is_array($_POST["file_to_del"]))) sl_open_fileoperations();

	// load user material ---------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_user_material',
									$_POST["old_course_user_material"],
									$path,
									($quota != 0 ? $quota - $used : false),
									isset($_POST['file_to_del']['course_user_material']) );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_material		= $arr_file['filename'];
	$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
	$old_file_size 		+= $arr_file['old_size'];

	// course otheruser material -------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_otheruser_material',
									$_POST["old_course_otheruser_material"],
									$path,
									($quota != 0 ? $quota - $used : false),
									isset($_POST['file_to_del']['course_otheruser_material']) );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_othermaterial	= $arr_file['filename'];
	$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
	$old_file_size 		+= $arr_file['old_size'];

	// course demo-----------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_demo',
									$_POST["old_course_demo"],
									$path,
									($quota != 0 ? $quota - $used : false),
									isset($_POST['file_to_del']['course_demo']) );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_demo			= $arr_file['filename'];
	$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
	$old_file_size 		+= $arr_file['old_size'];
	// course sponsor---------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_sponsor_logo',
									$_POST["old_course_sponsor_logo"],
									$path,
									($quota != 0 ? $quota - $used : false),
									isset($_POST['file_to_del']['course_sponsor_logo']),
									true );
	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_sponsor		= $arr_file['filename'];
	$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
	$old_file_size 		+= $arr_file['old_size'];
	// course logo-----------------------------------------------------------------------------------
	$arr_file = manageCourseFile(	'course_logo',
									$_POST["old_course_logo"],
									$path,
									($quota != 0 ? $quota - $used : false),
									isset($_POST['file_to_del']['course_logo']),
									true );


	$error 				|= $arr_file['error'];
	$quota_exceeded 	|= $arr_file['quota_exceeded'];
	$file_logo			= $arr_file['filename'];
	$used 				= $used + ($arr_file['new_size'] - $arr_file['old_size']);
	$old_file_size 		+= $arr_file['old_size'];
	// ----------------------------------------------------------------------------------------------
	sl_close_fileoperations();

	$date_begin	= Format::dateDb($_POST['course_date_begin'], "date");
	$date_end 	= Format::dateDb($_POST['course_date_end'], "date");

	if ($_POST["can_subscribe"] == "2") {
		$sub_start_date = Format::dateDb($_POST["sub_start_date"], "date");
		$sub_end_date 	= Format::dateDb($_POST["sub_end_date"], "date");
	}

	$hour_begin = '-1';
	$hour_end = '-1';
	if($_POST['hour_begin']['hour'] != '-1') {

		$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
		if($_POST['hour_begin']['quarter'] == '-1') $hour_begin .= ':00';
		else $hour_begin .= ':'.$_POST['hour_begin']['quarter'];
	}

	if($_POST['hour_end']['hour'] != '-1') {

		$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
		if($_POST['hour_end']['quarter'] == '-1') $hour_end .= ':00';
		else $hour_end .= ':'.$_POST['hour_end']['quarter'];
	}

	// update database ----------------------------------------------------
	$query_course = "
	UPDATE ".$GLOBALS['prefix_lms']."_course
	SET code 				= '".$_POST['course_code']."',
		name 				= '".$_POST['course_name']."',
		description 		= '".$_POST['course_descr']."',
		lang_code 			= '".$array_lang[$_POST['course_lang']]."',
		status 				= '".(int)$_POST['course_status']."',
		level_show_user 	= '".$show_level."',
		subscribe_method 	= '".(int)$_POST['course_subs']."',
		idCategory			= '".(int)$_POST['idCategory']."',

		linkSponsor 		= '".$_POST['course_sponsor_link']."',

		imgSponsor 			= '".$file_sponsor."',
		img_course 			= '".$file_logo."',
		img_material 		= '".$file_material."',
		img_othermaterial 	= '".$file_othermaterial."',
		course_demo 		= '".$file_demo."',

		mediumTime 			= '".$_POST['course_medium_time']."',
		permCloseLO 		= '".$_POST['course_em']."',
		userStatusOp 		= '".$user_status."',
		difficult 			= '".$_POST['course_difficult']."',

		show_progress 		= '".( isset($_POST['course_progress']) ? 1 : 0 )."',
		show_time 			= '".( isset($_POST['course_time']) ? 1 : 0 )."',

		show_who_online		= '".$_POST['show_who_online']."',

		show_extra_info 	= '".( isset($_POST['course_advanced']) ? 1 : 0 )."',
		show_rules 			= '".(int)$_POST['course_show_rules']."',

		direct_play 		= '".( isset($_POST['direct_play']) ? 1 : 0 )."',

		date_begin 			= '".$date_begin."',
		date_end 			= '".$date_end."',
		hour_begin 			= '".$hour_begin."',
		hour_end 			= '".$hour_end."',

		valid_time 			= '".(int)$_POST['course_day_of']."',

		min_num_subscribe 	= '".(int)$_POST['min_num_subscribe']."',
		max_num_subscribe 	= '".(int)$_POST['max_num_subscribe']."',

		course_type 		= '".$_POST['course_type']."',
		point_to_all 		= '".( isset($_POST['point_to_all']) ? $_POST['point_to_all'] : 0 )."',
		course_edition 		= '".( isset($_POST['course_edition']) ? $_POST['course_edition'] : 0 )."',
		selling 			= '".( isset($_POST['course_sell']) ? 1 : 0 )."',
		prize 				= '".( isset($_POST['course_prize']) ? $_POST['course_prize'] : 0 )."',
		policy_point 		= '".$_POST['policy_point']."',

		course_quota 		= '".$_POST['course_quota']."',

		allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
		can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
		sub_start_date 		= ".( $_POST["can_subscribe"] == "2" ? "'".$sub_start_date."'" : 'NULL' ).",
		sub_end_date 		= ".( $_POST["can_subscribe"] == "2" ? "'".$sub_end_date."'" : 'NULL' ).",

		advance 			= '".$_POST['advance']."',
		show_result 		= '".( isset($_POST['show_result']) ? 1 : 0 )."',


		use_logo_in_courselist = '".( isset($_POST['use_logo_in_courselist']) ? '1' : '0' )."'";

		if (isset($_POST['random_course_autoregistration_code']))
		{
			$control = 1;
			$str = '';

			while ($control)
			{
				for($i = 0; $i < 10; $i++)
				{
					$seed = mt_rand(0, 10);
					if($seed > 5)
						$str .= mt_rand(0, 9);
					else
						$str .= chr(mt_rand(65, 90));
				}

				$control_query = "SELECT COUNT(*)" .
								" ".$GLOBALS['prefix_lms']."_course" .
								" WHERE autoregistration_code = '".$str."'" .
								" AND idCourse <> '".$id_course."'";

				$control_result = sql_query($control_query);
				list($result) = sql_fetch_row($control_result);
				$control = $result;
			}

			$query_course .= ", autoregistration_code = '".$str."'";
		}
		else
			$query_course .= ", autoregistration_code = '".$_POST['course_autoregistration_code']."'";

	$query_course .= " WHERE idCourse = '".$id_course."'";

	if(!sql_query($query_course)) {

if($file_sponsor != '') 	sl_unlink($path.$file_sponsor);
		if($file_logo != '') 		sl_unlink($path.$file_logo);
		if($file_material != '') 	sl_unlink($path.$file_material);
		if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
		if($file_demo != '') 		sl_unlink($path.$file_demo);

		$course_man->subFileToUsedSpace(false, $old_file_size);
		Util::jump_to($url.'err_course');
	}

	// Let's update the classroom occupation schedule if course type is classroom -------
	if (hasClassroom($_POST["general_course_type"])) {
		$old_date_begin=$_POST["old_date_begin"];
		$old_date_end=$_POST["old_date_end"];
		updateCourseTimtable($id_course, FALSE, $date_begin, $date_end, $old_date_begin, $old_date_end);
	}

	// cascade modify on all the edition of thi course ---------------------------------
	if(isset($_POST['cascade_on_ed'])) {

		$query_editon = "
		UPDATE ".$GLOBALS['prefix_lms']."_course_edition
		SET code 			= '".$_POST['course_code']."',
			name 			= '".$_POST['course_name']."',
			description 	= '".$_POST['course_descr']."',
			edition_type 	= '".$_POST['course_type']."',
			status 			= '".$_POST['course_status']."'
		WHERE idCourse = '".$id_course."'";
		sql_query($query_editon);
	}
	Util::jump_to($url.'ok_course'.( $quota_exceeded ? '&limit_reach=1' : '' ));
}

function courseDelete() {

	if(isset($_POST['confirm_del_course'])) {

		$is_ok = removeCourse($_POST['id_course']);

		Util::jump_to('index.php?modname=course&op=course_list&course_category_status='.importVar('course_category_status')
			.'&result='.( $is_ok ? 'ok_course' : 'err_course' ));
	} else {
		//require_once(_i18n_.'/lib.lang.php');
		require_once(_base_.'/lib/lib.form.php');
		$lang 		=& DoceboLanguage::CreateInstance('course', 'lms');
		$out 		=& $GLOBALS['page'];

		list($id_course) = each($_POST['del_course']);
		$query_course = "
		SELECT code, name
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE idCourse = '".$id_course."'";
		list($code, $name) = sql_fetch_row(sql_query($query_course));

		$out->add(
			getTitleArea($lang->def('_COURSE'), 'course')
			.'<div class="std_block">'
			.Form::openForm('course_del', 'index.php?modname=course&amp;op=del_course')
			.Form::getHidden('id_course', 'id_course', $id_course)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span class="text_bold">'.$lang->def('_CODE').' : </span>'.$code.'<br />'
							.'<span class="text_bold">'.$lang->def('_COURSE_NAME').' : </span>'.$name,
							false,
							'confirm_del_course['.$id_course.']',
							'course_undo')
			.Form::closeForm()
			.'</div>', 'content' );
	}
}

function removeCourse($id_course) {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.upload.php');

	$acl_man	=& Docebo::user()->getAclManager();
	$course_man = new Man_Course();

	//remove course subscribed------------------------------------------

	$levels =& $course_man->getCourseIdstGroupLevel($id_course);
	foreach($levels as $lv => $idst) {

		$acl_man->deleteGroup($idst);
	}
	$alluser = getIDGroupAlluser($id_course);
	$acl_man->deleteGroup($alluser);
	$course_man->removeCourseRole($id_course);
	$course_man->removeCourseMenu($id_course);

	if(!sql_query("
	DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idCourse = '$id_course'")) return false;

	//remove course-----------------------------------------------------
	$query_course = "
	SELECT imgSponsor, img_course, img_material, img_othermaterial, course_demo
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = '".$id_course."'";
	list($file_sponsor, $file_logo, $file_material, $file_othermaterial, $file_demo) = sql_fetch_row(sql_query($query_course));

	require_once(_base_.'/lib/lib.upload.php');

	$path = '/appLms/'.Get::sett('pathcourse');
	if( substr($path, -1) != '/' && substr($path, -1) != '\\') $path .= '/';
	sl_open_fileoperations();
	if($file_sponsor != '') 	sl_unlink($path.$file_sponsor);
	if($file_logo != '') 		sl_unlink($path.$file_logo);
	if($file_material != '') 	sl_unlink($path.$file_material);
	if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
	if($file_demo != '') 		sl_unlink($path.$file_demo);
	sl_close_fileoperations();

	//if the scs exist create a room
	if($GLOBALS['where_scs'] !== false) {

		require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
		$re = deleteRoom(false, 'course', $id_course);
	}

	if(!sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_course WHERE idCourse = '$id_course'")) return false;

	return true;
}

function newCourseEdition() {
	checkPerm('mod');

	if(isset($_POST["course_id"])) {

		newCourseEditionForm($_POST["course_id"]);
	} else {

		require_once(_base_.'/lib/lib.form.php');

		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		$form = new Form();

		$with_edition_arr 	= getCoursesWithEditionArr();
		$array_lang 		= Docebo::langManager()->getAllLangCode();
		$array_lang[] = 'none';

		$title_area = array(
			'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE'),
			$lang->def('_EDITIONS')." '".$_POST['course_name']."'"
		);

		$GLOBALS['page']->add(
			getTitleArea($title_area, 'course_edition')
			.'<div class="std_block">'

			.getBackUi( 'index.php?modname=course&op=course_list', $lang->def('_BACK') )

			.$form->getFormHeader($lang->def('_EDITIONS')." '".$_POST['course_name']."'")

			.$form->openForm('course_edition_creation', 'index.php?modname=course&amp;op=add_course_edition', false, false, 'multipart/form-data')

			.$form->openElementSpace()
			.$form->getDropdown($lang->def("_COURSE"), "course_id", "course_id", $with_edition_arr)
			.$form->closeElementSpace()

			.$form->openButtonSpace()
			.$form->getButton('course_create', 'course_create', $lang->def('_CREATE'))
			.$form->getButton('course_undo_edition', 'course_undo_edition', $lang->def('_UNDO'))
			.$form->closeButtonSpace()

			.$form->closeForm()
			.'</div>'
		, 'content');
	}
}

function newCourseEditionForm($course_id) {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::CreateInstance('course', 'lms');
	$form = new Form();

	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	// possibile course status
	$course_status = array(
		CST_PREPARATION => $lang->def('_CST_PREPARATION'),
		CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE'),
		CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED'),
		CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED'),
		CST_CANCELLED 	=> $lang->def('_CST_CANCELLED')
	);

	//type of edition
	$edition_type= array (
		'elearning' => $lang->def('_COURSE_TYPE_ELEARNING'),
		'blended' => $lang->def('_COURSE_TYPE_BLENDED'),
		'classroom'=> $lang->def('_CLASSROOM')
	);

	$query_course = "
	SELECT 	code, name, description, lang_code, status, level_show_user, subscribe_method,
		linkSponsor, mediumTime, permCloseLO, userStatusOp, difficult,
		show_progress, show_time, show_extra_info, show_rules, date_begin, date_end, hour_begin, hour_end, valid_time,
		min_num_subscribe, max_num_subscribe, max_sms_budget, selling, prize, advance,
		course_type, policy_point, point_to_all, course_edition
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = '".$course_id."'";
	$course = mysql_fetch_assoc(sql_query($query_course));

	$GLOBALS['page']->add(
		getTitleArea($lang->def('_EDITIONS')." '".$course["name"]."'", 'course_edition')
		.'<div class="std_block">'

		.getBackUi( 'index.php?modname=course&op=course_list', $lang->def('_BACK') )

		.$form->getFormHeader($lang->def('_EDITIONS')." '".$course["name"]."'")

		.$form->openForm('course_edition_creation', 'index.php?modname=course&amp;op=add_course_edition', false, false, 'multipart/form-data')

		.$form->openElementSpace()
		.$form->getHidden('course_id', 'course_id', $course_id)
		.$form->getTextfield($lang->def('_CODE'), 'course_edition_code', 'course_edition_code', '50', $course["code"])
		.$form->getTextfield($lang->def('_COURSE_NAME'), 'course_edition_name', 'course_edition_name', '255', $course["name"])

		// mode for course end ---------------------------------------------
		.$form->getDropdown($lang->def('_STATUS'), 'course_edition_status', 'course_edition_status', $course_status, $course['status'] )
		.$form->getTextarea($lang->def('_DESCRIPTION'), 'course_edition_descr', 'course_edition_descr', $course['description'])
		.$form->getDropdown($lang->def('_COURSE_TYPE'), 'edition_type', 'edition_type', $edition_type, $course["course_type"] )
	, 'content');

	$GLOBALS['page']->add(
		$form->getOpenFieldset($lang->def('_COURSE_SUBSCRIPTION'))

		//-----------------------------------------------------------------
		.$form->getOpenCombo($lang->def('_USER_CAN_SUBSCRIBE'))
		.$form->getRadio($lang->def('_SUBSCRIPTION_CLOSED'), 'subscription_closed', 'can_subscribe', '0', TRUE )
		.$form->getRadio($lang->def('_SUBSCRIPTION_OPEN'), 'subscription_open', 'can_subscribe', '1', FALSE)
		.$form->getRadio($lang->def('_SUBSCRIPTION_IN_PERIOD').":", 'subscription_period', 'can_subscribe', '2', FALSE)
		.$form->getCloseCombo()

		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_BEGIN').":", 'sub_start_date', 'sub_start_date', "")
		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_END').":", 'sub_end_date', 'sub_end_date', "")
		.$form->getCloseFieldset()
	, 'content');

	$GLOBALS['page']->add(
		$form->getOpenFieldset($lang->def('_COURSE_SPECIAL_OPTION'))
		.$form->getTextfield($lang->def('_COURSE_PRIZE'), 		'edition_price', 		'edition_price', 		11, $course["prize"])
		.$form->getTextfield($lang->def('_COURSE_ADVANCE'), 	'edition_advance', 		'edition_advance', 		11, $course['advance'])
		// max number of user that can be subscribed
		.$form->getTextfield($lang->def('_MIN_NUM_SUBSCRIBE'), 	'min_num_subscribe', 	'min_num_subscribe', 	11, $course["min_num_subscribe"])
		.$form->getTextfield($lang->def('_MAX_NUM_SUBSCRIBE'), 	'max_num_subscribe', 	'max_num_subscribe', 	11, $course["max_num_subscribe"])
		.$form->getCheckbox($lang->def('_ALLOW_OVERBOOKING'), 	'allow_overbooking', 	'allow_overbooking', 	1)
		.$form->getCloseFieldset()
	, 'content');

	$hours = array('-1' => '- -', '0' =>'00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
					'10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
					'20', '21', '22', '23' );
	$quarter = array('-1' => '- -', '00' => '00', '15' => '15', '30' => '30', '45' => '45');

	if($course['hour_begin'] != '-1') {
		$hb_sel = (int)substr($course['hour_begin'], 0, 2);
		$qb_sel = substr($course['hour_begin'], 3, 2);
	} else $hb_sel = $qb_sel = '-1';

	if($course['hour_end'] != '-1') {
		$he_sel = (int)substr($course['hour_end'], 0, 2);
		$qe_sel = substr($course['hour_end'], 3, 2);
	} else $he_sel = $qe_sel = '-1';

	$GLOBALS['page']->add(
		$form->getDatefield($lang->def('_DATE_BEGIN'), 	'course_edition_date_begin', 	'course_edition_date_begin',
			Format::date($course["date_begin"]))
		.$form->getDatefield($lang->def('_DATE_END'), 		'course_edition_date_end', 		'course_edition_date_end',
			Format::date($course["date_end"]))


		.$form->getLineBox(
			'<label for="hour_begin_hour">'.$lang->def('_HOUR_BEGIN').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_begin_hour', 'hour_begin[hour]', $hours, $hb_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_begin_quarter', 'hour_begin[quarter]', $quarter, $qe_sel, '')
		)

		.$form->getLineBox(
			'<label for="hour_end_hour">'.$lang->def('_HOUR_END').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_end_hour', 'hour_end[hour]', $hours, $he_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_end_quarter', 'hour_end[quarter]', $quarter, $qe_sel, '')
		)

	, 'content');

	$GLOBALS['page']->add(
		$form->getOpenFieldset($lang->def('_DOCUMENT_UPLOAD'))
		.$form->getFilefield($lang->def('_USER_MATERIAL'), 			'course_edition_user_material', 		'course_edition_user_material')
		.$form->getFilefield($lang->def('_OTHER_USER_MATERIAL'), 	'course_edition_otheruser_material', 	'course_edition_otheruser_material')
		.$form->getCloseFieldset()

		.$form->closeElementSpace()

		.$form->openButtonSpace()

		.$form->getButton('course_create', 'course_create', $lang->def('_CREATE'))
		.$form->getButton('course_undo_edition', 'course_undo_edition', $lang->def('_UNDO'))

		.$form->closeButtonSpace()

		.$form->closeForm()
		.'</div>'
	, 'content');
}

function insCourseEdition() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.upload.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.multimedia.php');

	$array_lang	 = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';

	$id_course = $_POST['course_id'];

	if($_POST['course_edition_name'] == '')
		 $_POST['course_edition_name'] = Lang::t('_NO_NAME', 'course', 'lms');

	$path = '/appLms/'.Get::sett('pathcourse');
	if(substr($path, -1) != '/' && substr($path, -1) != '\\') $path = $path.'/';

	$file_sponsor 	= '';
	$file_logo 		= '';
	$file_material 	= '';
	$file_othermaterial = '';
	$error 			= 0;
	$show_level 	= 0;
	$user_status 	= 0;

	if(isset($_POST['user_status'])) {
		while(list($status) = each($_POST['user_status'])) $user_status |= (1 << $status);
	}
	if(isset($_POST['course_edition_show_level'])) {
		while(list($lv) = each($_POST['course_edition_show_level'])) $show_level |= (1 << $lv);
	}

	sl_open_fileoperations();
	if($_FILES['course_edition_user_material']['tmp_name'] != '') {

		$file_material = 'edition_user_material_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_user_material']['name'];
		$re = createImageFromTmp(	$_FILES['course_edition_user_material']['tmp_name'],
									$path.$file_material,
									$_FILES['course_edition_user_material']['name'],
									150,
									150,
									true );
		if(!$re) {
			$error = 1;
			$file_material = '';
		}
	}
	if($_FILES['course_edition_otheruser_material']['tmp_name'] != '') {

		$file_othermaterial = 'edition_otheruser_material_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_otheruser_material']['name'];
		$re = createImageFromTmp(	$_FILES['course_edition_otheruser_material']['tmp_name'],
									$path.$file_othermaterial,
									$_FILES['course_edition_otheruser_material']['name'],
									150,
									150,
									true );
		if(!$re) {
			$error = 1;
			$file_othermaterial = '';
		}
	}
	if($_FILES['course_edition_sponsor_logo']['tmp_name'] != '') {

		$file_sponsor = 'edition_sponsor_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_sponsor_logo']['name'];
		$re = createImageFromTmp(	$_FILES['course_edition_sponsor_logo']['tmp_name'],
									$path.$file_sponsor,
									$_FILES['course_edition_sponsor_logo']['name'],
									150,
									150,
									true );
		if(!$re) {
			$error = 1;
			$file_sponsor = '';
		}
	}
	if($_FILES['course_edition_logo']['tmp_name'] != '') {

		$file_logo = 'edition_logo_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_logo']['name'];
		$re = createImageFromTmp(	$_FILES['course_edition_logo']['tmp_name'],
									$path.$file_logo,
									$_FILES['course_edition_logo']['name'],
									150,
									150,
									true );
		if(!$re) {
			$error = 1;
			$file_sponsor = '';
		}
	}
	sl_close_fileoperations();

	// if subsribe gap is defined with the date -------------------------------
	if ($_POST["can_subscribe"] != "2") {
		$sub_start_date = "NULL";
		$sub_end_date = "NULL";
	} else {
		$sub_start_date = "'".Format::dateDb($_POST["sub_start_date"], "date")."'";
		$sub_end_date = "'".Format::dateDb($_POST["sub_end_date"], "date")."'";
	}

	// insert the course in database -----------------------------------------------------------
	$hour_begin = '-1';
	$hour_end = '-1';
	if($_POST['hour_begin']['hour'] != '-1') {

		$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
		if($_POST['hour_begin']['quarter'] == '-1') $hour_begin .= ':00';
		else $hour_begin .= ':'.$_POST['hour_begin']['quarter'];
	}

	if($_POST['hour_end']['hour'] != '-1') {

		$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
		if($_POST['hour_end']['quarter'] == '-1') $hour_end .= ':00';
		else $hour_end .= ':'.$_POST['hour_end']['quarter'];
	}


	$query_course_edition = "
		INSERT INTO ".$GLOBALS['prefix_lms']."_course_edition
		SET idCourse 			= '".$id_course."',
			code 				= '".$_POST['course_edition_code']."',
			name 				= '".$_POST['course_edition_name']."',
			description 		= '".$_POST['course_edition_descr']."',
			status 				= '".(int)$_POST['course_edition_status']."',

			date_begin 			= '".Format::dateDb($_POST['course_edition_date_begin'],'date')."',
			date_end 			= '".Format::dateDb($_POST['course_edition_date_end'],'date')."',
			hour_begin 			= '".$hour_begin."',
			hour_end 			= '".$hour_end."',

			img_material 		= '".$file_material."',
			img_othermaterial 	= '".$file_othermaterial."',

			min_num_subscribe 	= '".(int)$_POST["min_num_subscribe"]."',
			max_num_subscribe 	= '".(int)$_POST["max_num_subscribe"]."',
			price 				= '".$_POST["edition_price"]."',
			advance 			= '".$_POST["edition_advance"]."',

			edition_type 		= '".$_POST["edition_type"]."',
			allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
			can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
			sub_start_date 		= ".$sub_start_date.",
			sub_end_date 		= ".$sub_end_date."";

	if(!sql_query($query_course_edition)) {

		$error = 1;
		if($file_sponsor != '') sl_unlink($path.$file_sponsor);
		if($file_logo != '') sl_unlink($path.$file_logo);
		if($file_material != '') sl_unlink($path.$file_material);
		if($file_othermaterial != '') sl_unlink($path.$file_othermaterial);
		Util::jump_to('index.php?modname=course&op=course_list&result=err_course');
	} else {

		$edition_id = sql_insert_id();

		$acl_manager =& Docebo::user()->getAclManager();
		$group = '/lms/course_edition/'.$edition_id.'/subscribed';
		$group_idst =$acl_manager->getGroupST($group);

		if ($group_idst === FALSE) {
			$group_idst =$acl_manager->registerGroup($group, 'all the user of a course edition', true, "course");
		}

		// send alert ---------------------------------------------------------------------------
		require_once(_base_.'/lib/lib.eventmanager.php');

		$msg_composer = new EventMessageComposer();

		$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
		$msg_composer->setBodyLangText('email', '_ALERT_TEXT', array(	'[url]' => Get::sett('url'),
			'[course_code]' => $_POST['course_edition_code'],
			'[course]' => $_POST['course_edition_name'] ) );

		$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', array(	'[url]' => Get::sett('url'),
			'[course_code]' => $_POST['course_edition_code'],
			'[course]' => $_POST['course_edition_name'] ) );

		require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
		$course_man = new Man_Course();
		$recipients = $course_man->getIdUserOfLevel($id_course);
		createNewAlert(	'CoursePropModified',
			'course',
			'add',
			'1',
			'Inserted course '.$_POST['course_name'],
			$recipients,
			$msg_composer );
		Util::jump_to('index.php?modname=course&op=course_list&result=ok_course');
	}


}

function modCourseEdition() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.tab.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');

	$lang	=& DoceboLanguage::createInstance('course', 'lms');
	$form 	= new Form();
	$out	=& $GLOBALS['page'];
	$out->setWorkingZone('content');

	$course_status = array(
		CST_PREPARATION => $lang->def('_CST_PREPARATION'),
		CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE'),
		CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED'),
		CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED'),
		CST_CANCELLED 	=> $lang->def('_CST_CANCELLED')
	);

	//type of edition
	$edition_type= array (
		'elearning' => $lang->def('_COURSE_TYPE_ELEARNING'),
		'blended' => $lang->def('_COURSE_TYPE_BLENDED'),
		'classroom'=> $lang->def('_CLASSROOM')
	);

	list($id_course_edition) = each($_POST['mod_course_edition']);

	$query_course_edition = "
	SELECT *
	FROM ".$GLOBALS['prefix_lms']."_course_edition
	WHERE idCourseEdition = '".$id_course_edition."'";
	$course_edition = mysql_fetch_assoc(sql_query($query_course_edition));

	// set page title
	$title_area 	= array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE'),
		$lang->def('_MOD').' : '.$course_edition['name']
	);
	$date_begin 	= Format::date($course_edition['date_begin'],'date');
	$date_end 		= Format::date($course_edition['date_end'],'date');

	$out->add(
		getTitleArea($title_area, 'configuration')
		.'<div class="std_block">'

		.$form->openForm('upd_course', 'index.php?modname=course&amp;op=upd_course', false, false, 'multipart/form-data')

		//also print the hidden id course
		.$form->getHidden('mod_course_edition'.$id_course_edition, 'mod_course_edition['.$id_course_edition.']', $id_course_edition)

		// print course name hidden
		.$form->getHidden("course_id", "course_id", $course_edition["idCourse"])
		.$form->getHidden("old_date_begin", "old_date_begin", $course_edition['date_begin'])
		.$form->getHidden("old_date_end", "old_date_end", $course_edition['date_end'])
	);
	$out->add(
		$form->openElementSpace()
		.$form->getTextfield($lang->def('_CODE'), 	'course_edition_code', 		'course_edition_code', 	'50', 	$course_edition['code'])
		.$form->getTextfield($lang->def('_COURSE_NAME'), 	'course_edition_name', 		'course_edition_name', 	'255', 	$course_edition['name'])
		.$form->getDropdown($lang->def('_STATUS'), 	'course_edition_status', 	'course_edition_status', 		$course_status, 	$course_edition['status'] )
		.$form->getDropdown($lang->def('_COURSE_TYPE'), 	'edition_type', 			'edition_type', 				$edition_type, 		$course_edition['edition_type'] )
		.$form->getTextarea($lang->def('_DESCRIPTION'), 'course_edition_descr', 	'course_edition_descr', 		$course_edition['description'])
	);
	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_SUBSCRIPTION'))

		.$form->getOpenCombo($lang->def('_USER_CAN_SUBSCRIBE'))
		.$form->getRadio($lang->def('_SUBSCRIPTION_CLOSED'), 		'subscription_closed', 	'can_subscribe', '0', ($course_edition['can_subscribe'] == 0) )
		.$form->getRadio($lang->def('_SUBSCRIPTION_OPEN'), 			'subscription_open', 	'can_subscribe', '1', ($course_edition['can_subscribe'] == 1) )
		.$form->getRadio($lang->def('_SUBSCRIPTION_IN_PERIOD').":", 'subscription_period', 	'can_subscribe', '2', ($course_edition['can_subscribe'] == 2) )
		.$form->getCloseCombo()
		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_BEGIN').":", 	'sub_start_date', 	'sub_start_date',
			Format::date($course_edition['sub_start_date'], "date"))
		.$form->getDatefield($lang->def('_SUBSCRIPTION_DATE_END').":", 		'sub_end_date', 	'sub_end_date',
			Format::date($course_edition['sub_end_date'], "date"))
		.$form->getCloseFieldset()
	);
	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_SPECIAL_OPTION'))
		.$form->getTextfield($lang->def('_COURSE_PRIZE'), 		'edition_price', 		'edition_price', 	11, 	$course_edition["price"])
		.$form->getTextfield($lang->def('_COURSE_ADVANCE'), 	'edition_advance', 		'edition_advance', 	11, 	$course_edition['advance'])
		.$form->getTextfield($lang->def('_MIN_NUM_SUBSCRIBE'), 	'min_num_subscribe', 	'min_num_subscribe', 11, 	$course_edition["min_num_subscribe"])
		.$form->getTextfield($lang->def('_MAX_NUM_SUBSCRIBE'), 	'max_num_subscribe', 	'max_num_subscribe', 11, 	$course_edition["max_num_subscribe"])
		.$form->getCheckbox($lang->def('_ALLOW_OVERBOOKING'), 	'allow_overbooking', 	'allow_overbooking', 1, 	$course_edition["allow_overbooking"])
		.$form->getCloseFieldset()
	);

	$hours = array('-1' => '- -', '0' =>'00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
					'10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
					'20', '21', '22', '23' );
	$quarter = array('-1' => '- -', '00' => '00', '15' => '15', '30' => '30', '45' => '45');

	if($course_edition['hour_begin'] != '-1') {
		$hb_sel = (int)substr($course_edition['hour_begin'], 0, 2);
		$qb_sel = substr($course_edition['hour_begin'], 3, 2);
	} else $hb_sel = $qb_sel = '-1';

	if($course_edition['hour_end'] != '-1') {
		$he_sel = (int)substr($course_edition['hour_end'], 0, 2);
		$qe_sel = substr($course_edition['hour_end'], 3, 2);
	} else $he_sel = $qe_sel = '-1';

	$out->add(
		$form->getOpenFieldset($lang->def('_COURSE_TIME_OPTION'))
		.$form->getDatefield($lang->def('_DATE_BEGIN'), 'course_edition_date_begin', 	'course_edition_date_begin', 	$date_begin)
		.$form->getDatefield($lang->def('_DATE_END'), 	'course_edition_date_end', 		'course_edition_date_end', 		$date_end)


		.$form->getLineBox(
			'<label for="hour_begin_hour">'.$lang->def('_HOUR_BEGIN').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_begin_hour', 'hour_begin[hour]', $hours, $hb_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_begin_quarter', 'hour_begin[quarter]', $quarter, $qb_sel, '')
		)

		.$form->getLineBox(
			'<label for="hour_end_hour">'.$lang->def('_HOUR_END').'</label>',
			$form->getInputDropdown('dropdown_nw', 'hour_end_hour', 'hour_end[hour]', $hours, $he_sel, '')
			.' : '
			.$form->getInputDropdown('dropdown_nw', 'hour_end_quarter', 'hour_end[quarter]', $quarter, $qe_sel, '')
		)


		.$form->getCloseFieldset()
	);
	$out->add(
		$form->getOpenFieldset($lang->def('_DOCUMENT_UPLOAD'))
		.$form->getExtendedFilefield(	$lang->def('_USER_MATERIAL'),
		 								'course_edition_material',
										'course_edition_material',
										$course_edition["img_material"] )
		.$form->getExtendedFilefield(	$lang->def('_OTHER_USER_MATERIAL'),
		 								'course_edition_othermaterial',
										'course_edition_othermaterial',
										$course_edition["img_othermaterial"] )
		.$form->getCloseFieldset()
		.$form->closeElementSpace()
	);
	$out->add(
		$form->openButtonSpace()
		.$form->getButton('course_edition_modify', 'course_edition_modify', $lang->def('_SAVE'))
		.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()

		.$form->closeForm()
		.'</div>'
	);
}

function confirmModCourseEdition () {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.upload.php');
	require_once(_base_.'/lib/lib.multimedia.php');

	$array_lang = Docebo::langManager()->getAllLangCode();
	$array_lang[] = 'none';
	list($id_course_edition) =  each($_POST['mod_course_edition']);

	$path = '/appLms/'.Get::sett('pathcourse');
	if(substr($path, -1) != '/' && substr($path, -1) != '\\') { $path = $path.'/'; }

	$error 					= 0;
	$show_level 			= 0;
	$file_edition_material 	= '';
	$file_edition_othermaterial = '';

	// manage file  upload -----------------------------------------
	if((is_array($_FILES) && !empty($_FILES)) || (is_array($_POST["file_to_del"]))) sl_open_fileoperations();

	if (is_array($_POST["file_to_del"])) {
		foreach($_POST["file_to_del"] as $field_id => $old_file) {

			sl_unlink($path.$old_file);
		}
	}

	if(isset($_FILES['course_edition_material']) && $_FILES['course_edition_material']['tmp_name'] != '') {

		// delete old file
		if((isset($_POST["old_course_edition_material"])) && (!empty($_POST["old_course_edition_material"]))) {

			sl_unlink($path.$_POST["old_course_edition_material"]);
		}
		// upload new file
		$file_edition_material = 'usermaterial_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_material']['name'];
		if(!sl_upload($_FILES['course_edition_material']['tmp_name'], $path.$file_edition_material)) {

			$error = true;
			$file_edition_material = '';
		}
	} elseif(!isset($_POST["file_to_del"]["course_edition_material"])) {

		// new not loaded use old file
		$file_edition_material = (isset($_POST["old_course_edition_material"]) ? $_POST["old_course_edition_material"] : "" );
	}

	if(isset($_FILES['course_edition_othermaterial']) && $_FILES['course_edition_othermaterial']['tmp_name'] != '') {

		// delete old file
		if((isset($_POST["old_course_edition_othermaterial"])) && (!empty($_POST["old_course_edition_othermaterial"]))) {

			sl_unlink($path.$_POST["old_course_edition_othermaterial"]);
		}
		// upload new file
		$file_edition_othermaterial = 'otherusermaterial_'.mt_rand(0, 100).'_'.time().'_'.$_FILES['course_edition_othermaterial']['name'];
		if(!sl_upload($_FILES['course_edition_othermaterial']['tmp_name'], $path.$file_edition_othermaterial)) {

			$error = true;
			$file_edition_othermaterial = '';
		}
	} else if(!isset($_POST["file_to_del"]["course_edition_othermaterial"])) {

		// new not loaded use old file
		$file_edition_othermaterial=(isset($_POST["old_course_edition_othermaterial"]) ? $_POST["old_course_edition_othermaterial"] : "");
	}
	sl_close_fileoperations();

	// save mod in db ---------------------------------------
	if ($_POST["can_subscribe"] != "2") {
		$sub_start_date = "NULL";
		$sub_end_date 	= "NULL";
	} else {
		$sub_start_date = "'".Format::dateDb($_POST["sub_start_date"], "date")."'";
		$sub_end_date 	= "'".Format::dateDb($_POST["sub_end_date"], "date")."'";
	}

	$date_begin = Format::dateDb($_POST['course_edition_date_begin'],'date');
	$date_end = Format::dateDb($_POST['course_edition_date_end'],'date');

	$hour_begin = '-1';
	$hour_end = '-1';
	if($_POST['hour_begin']['hour'] != '-1') {

		$hour_begin = ( strlen($_POST['hour_begin']['hour']) == 1 ? '0'.$_POST['hour_begin']['hour'] : $_POST['hour_begin']['hour'] );
		if($_POST['hour_begin']['quarter'] == '-1') $hour_begin .= ':00';
		else $hour_begin .= ':'.$_POST['hour_begin']['quarter'];
	}

	if($_POST['hour_end']['hour'] != '-1') {

		$hour_end = ( strlen($_POST['hour_end']['hour']) == 1 ? '0'.$_POST['hour_end']['hour'] : $_POST['hour_end']['hour'] );
		if($_POST['hour_end']['quarter'] == '-1') $hour_end .= ':00';
		else $hour_end .= ':'.$_POST['hour_end']['quarter'];
	}


	$query_course_edition = "
	UPDATE ".$GLOBALS['prefix_lms']."_course_edition
	SET code 				= '".$_POST['course_edition_code']."',
		name 				= '".$_POST['course_edition_name']."',
		description 		= '".$_POST['course_edition_descr']."',
		status 				= '".(int)$_POST['course_edition_status']."',

		img_material 		='".$file_edition_material."',
		img_othermaterial 	='".$file_edition_othermaterial."',

		date_begin 			= '".$date_begin."',
		date_end 			= '".$date_end."',
		hour_begin 			= '".$hour_begin."',
		hour_end 			= '".$hour_end."',

		min_num_subscribe 	= '".(int)$_POST["min_num_subscribe"]."',
		max_num_subscribe 	= '".(int)$_POST["max_num_subscribe"]."',
		price 				= '".$_POST["edition_price"]."',
		advance 			= '".$_POST["edition_advance"]."',

		edition_type 		= '".$_POST["edition_type"]."',
		allow_overbooking 	= '".( isset($_POST["allow_overbooking"]) ? 1 : 0 )."',
		can_subscribe 		= '".(int)$_POST["can_subscribe"]."',
		sub_start_date 		= ".$sub_start_date.",
		sub_end_date 		= ".$sub_end_date."

	WHERE idCourseEdition = '".$id_course_edition."'";
	if(!sql_query($query_course_edition)) {

		$error = 1;
		if($file_edition_material != '') sl_unlink($path.$file_edition_material);
		if($file_edition_othermaterial != '') sl_unlink($path.$file_edition_othermaterial);
	} else {

		$acl_manager =& Docebo::user()->getAclManager();
		$group = '/lms/course_edition/'.$id_course_edition.'/subscribed';
		$group_idst =$acl_manager->getGroupST($group);
		if ($group_idst === FALSE) {
			$group_idst = $acl_manager->registerGroup($group, 'all the user of a course edition', true, "course");
		}
		// -- Let's update the classroom occupation schedule if course type is classroom ----
		if(hasClassroom($_POST["edition_type"])) {

			$old_date_begin = $_POST["old_date_begin"];
			$old_date_end 	= $_POST["old_date_end"];
			updateCourseTimtable($_POST["course_id"], $id_course_edition, $date_begin, $date_end, $old_date_begin, $old_date_end);
		}
		// ----------------------------------------------------------------------------------
	}
	Util::jump_to('index.php?modname=course&op=course_list&course_category_status='.importVar('course_category_status').'&result=ok_course');
}

function courseEditionDelete() {
	checkPerm('mod');
	if(isset($_POST['confirm_del_edition_course'])) {

		$is_ok = removeCourseEdition($_POST['id_course_edition']);
		Util::jump_to('index.php?modname=course&op=course_list&course_category_status='.importVar('course_category_status')
			.'&result='.( $is_ok ? 'ok_course' : 'fail_course' ));
	} else {
		//require_once(_i18n_.'/lib.lang.php');
		require_once(_base_.'/lib/lib.form.php');

		$lang 		=& DoceboLanguage::CreateInstance('course', 'lms');

		list($id_course_edition) = each($_POST['del_course_edition']);

		$query_course = "
		SELECT code, name
		FROM ".$GLOBALS['prefix_lms']."_course_edition
		WHERE idCourseEdition = '".$id_course_edition."'";
		list($code, $name) = sql_fetch_row(sql_query($query_course));

		$title_area 	= array(
			'index.php?modname=course&amp;op=course_list' => $lang->def('_COURSE'),
			$lang->def('_COURSE_EDITION')
		);

		$GLOBALS['page']->add(
			getTitleArea($title_area, 'course')
			.'<div class="std_block">'
			.Form::openForm('course_edition_del', 'index.php?modname=course&amp;op=del_course')
			.Form::getHidden('id_course_edition', 'id_course_edition', $id_course_edition)
			.getDeleteUi(	$lang->def('_AREYOUSURE'),
							'<span class="text_bold">'.$lang->def('_CODE').' : </span>'.$code.'<br />'
							.'<span class="text_bold">'.$lang->def('_COURSE_NAME').' : </span>'.$name,
							false,
							'confirm_del_edition_course['.$id_course_edition.']',
							'course_undo')
			.Form::closeForm()
			.'</div>'
		, 'content' );
	}
}


function removeCourseEdition($id_course_edition) {

	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
	$query_course_edition = "
	SELECT img_material,img_othermaterial,idCourse
	FROM ".$GLOBALS['prefix_lms']."_course_edition
	WHERE idCourseEdition = '".$id_course_edition."'";
	$res=sql_query($query_course_edition);

	$course_ed_info=mysql_fetch_array($res);

	$old_material=$course_ed_info["img_material"];
	$old_othermaterial=$course_ed_info["img_othermaterial"];
	$id_course=$course_ed_info["idCourse"];


	require_once(_base_.'/lib/lib.upload.php');

	$path = '/appLms/'.Get::sett('pathcourse');
	if(substr($path, -1) != '/' && substr($path, -1) != '\\') {
		$path = $path.'/';
	}

	sl_open_fileoperations();

	if($old_material != '')
	if(!sl_unlink($path.$old_material)) return false;
	if($old_othermaterial != '')
	if(!sl_unlink($path.$old_othermaterial)) return false;
	sl_close_fileoperations();

	if(!sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_course_edition WHERE idCourseEdition = '$id_course_edition'")) return false;

	$query_control = "SELECT idUser"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE edition_id <> 0"
					." AND edition_id <> $id_course_edition"
					." AND idUser IN "
					." ("
						." SELECT idUser"
						." FROM ".$GLOBALS['prefix_lms']."_courseuser"
						." WHERE edition_id = '".$id_course_edition."'"
					." )";

	$result_control = sql_fetch_row($query_control);

	$array_user_to_delete = array();
	$array_idst_group = array();

	if (mysql_num_rows($result_control))
	{
		$array_user = array();

		while (list($id_user) = sql_fetch_row($result_control))
			$array_user[] = $id_user;

		$query_user_to_delet = "SELECT idUser"
							." FROM ".$GLOBALS['prefix_lms']."_courseuser"
							." WHERE edition_id = '".$id_course_edition."'"
							." AND idUser NOT IN (".implode(', ', $array_user).")";

		$result_user_to_delet = sql_query($query_user_to_delet);

		while(list($id_user) = sql_fetch_row($result_user_to_delet))
			$array_user_to_delete[] = $id_user;
	}
	else
	{
		$query_user = " SELECT idUser"
					." FROM ".$GLOBALS['prefix_lms']."_courseuser"
					." WHERE edition_id = '".$id_course_edition."'";

		$result_user = sql_query($query_user);

		while (list($id_user) = sql_fetch_row($result_user))
			$array_user_to_delete[] = $id_user;
	}

	$query_group = "SELECT idst"
					." FROM ".$GLOBALS['prefix_fw']."_group"
					." WHERE groupid LIKE '%/lms/course/".$id_course."/%'";

	$result_group = sql_query($query_group);

	while (list($id_group) = sql_fetch_row($result_group))
		$array_idst_group[] = $id_group;

	$query_delete = "DELETE FROM ".$GLOBALS['prefix_fw']."_group_member"
					." WHERE idst IN (".implode(', ', $array_idst_group).")"
					." AND idstMember IN (".implode(', ', $array_user_to_delete).")";

	if(!sql_query($query_delete)) return false;

	if(!sql_query("DELETE FROM ".$GLOBALS['prefix_lms']."_courseuser WHERE edition_id = '$id_course_edition' AND idCourse = '$id_course'")) return false;


	$acl_manager =& Docebo::user()->getAclManager();
	$group ='/lms/course_edition/'.$id_course_edition.'/subscribed';
	$group_idst =$acl_manager->getGroupST($group);
	$acl_manager->deleteGroup($group_idst);

	return true;
}

function assignMenu() {
	checkPerm('mod');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_lms_.'/lib/lib.course.php');

	if(isset($_POST['assign']))
	{
		$id_course = importVar('id_course', true, 0);
		$id_custom = importVar('selected_menu', true, 0);

		$query_course = "SELECT course_type FROM ".$GLOBALS['prefix_lms']."_course WHERE idCourse = '".$id_course."'";
		list($course_type) = sql_fetch_row(sql_query($query_course));
		
		if($course_type === 'classroom')
			$url = 'index.php?r=alms/classroom/show&result=';
		else
			$url = 'index.php?modname=course&op=course_list&result=';
		
		require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		$acl_man	=& Docebo::user()->getAclManager();
		$course_man = new Man_Course();

		$levels =& $course_man->getCourseIdstGroupLevel($id_course);
		if(empty($levels) || implode('', $levels) == '') $levels =& DoceboCourse::createCourseLevel($id_course);

		$course_man->removeCourseRole($id_course);
		$course_man->removeCourseMenu($id_course);
		$course_idst =& $course_man->getCourseIdstGroupLevel($id_course);

		$result = cerateCourseMenuFromCustom($id_custom, $id_course, $course_idst);

		if($_SESSION['idCourse'] == $id_course)
		{
			$query =	"SELECT module.idModule, main.idMain
						FROM ( ".$GLOBALS['prefix_lms']."_menucourse_main AS main JOIN
						".$GLOBALS['prefix_lms']."_menucourse_under AS un ) JOIN
						".$GLOBALS['prefix_lms']."_module AS module
						WHERE main.idMain = un.idMain AND un.idModule = module.idModule
						AND main.idCourse = '".(int)$_SESSION['idCourse']."'
						AND un.idCourse = '".(int)$_SESSION['idCourse']."'
						ORDER BY main.sequence, un.sequence
						LIMIT 0,1";

			list($id_module, $id_main) = sql_fetch_row(sql_query($query));

			$_SESSION['current_main_menu'] = $id_main;
			$_SESSION['sel_module_id'] = $id_module;

			//loading related ST
			Docebo::user()->loadUserSectionST('/lms/course/public/');
			Docebo::user()->SaveInSession();
		}

		Util::jump_to($url.( $result ? 'ok_course' : 'fail_course' ));

	} else {

		$lang =& DoceboLanguage::CreateInstance('course', 'lms');

		//list($id_course) = each($_POST['assign_menu_course']);
		$id_course = importVar('id_course', true, 0);
		require_once(_base_.'/lib/lib.form.php');

		require_once($GLOBALS['where_lms'].'/admin/modules/category/category.php');
		require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');

		$form = new Form();
		$menu_custom = getAllCustom();
		$sel_custom = key($menu_custom);
		reset($menu_custom);
		
		$query_course = "SELECT course_type FROM ".$GLOBALS['prefix_lms']."_course WHERE idCourse = '".$id_course."'";
		list($course_type) = sql_fetch_row(sql_query($query_course));
		
		if($course_type === 'classroom')
			$url = 'index.php?r=alms/classroom/show';
		else
			$url = 'index.php?modname=course&op=course_list';
		
		$title_area 	= array(
			$url => $lang->def('_COURSE'),
			$lang->def('_ASSIGN_MENU')
		);
		
		$GLOBALS['page']->setWorkingZone('content');
		$GLOBALS['page']->add(
			getTitleArea($title_area, 'course')
			.'<div class="std_block">'

			.$form->openForm('course_creation', 'index.php?modname=course&amp;op=assignMenu')
			.$form->openElementSpace()
			.$form->getHidden('id_course', 'id_course', $id_course)
			.$form->getDropdown($lang->def('_COURSE_MENU_TO_ASSIGN'), 'selected_menu', 'selected_menu', $menu_custom, $sel_custom )

			.$form->closeElementSpace()

			.$form->openButtonSpace()
			.$form->getButton('assign', 'assign', $lang->def('_ASSIGN_USERS'))
			.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
			.$form->closeButtonSpace()
			.$form->closeForm()
		.'</div>');
	}
}

function move_course() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/admin/modules/category/category.php');
	require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');

	require_once($GLOBALS['where_lms'].'/lib/category/class.categorytree.php');

	$out=& $GLOBALS['page'];
	$lang=& DoceboLanguage::createInstance('course', 'lms');

	$out->add(getTitleArea(array($lang->def('_COURSE'), $lang->def('_MOVE')), 'course')
	.'<div class="std_block">');

	if( isset($_POST["move_course"]) ) list($id_course) = each($_POST['move_course']);
	else $id_course = importVar('id_course', true, 0);

	$categoryDb = new CategoryTree();//new TreeDb_CatDb($GLOBALS['prefix_lms'].'_category');
	$treeView = new TreeView_CatView($categoryDb, 'course_category', $lang->def('_CATEGORY'));

	if(isset($_POST[$treeView->_getCancelId()])) Util::jump_to('index.php?modname=course&op=course_list');

	$treeView->parsePositionData($_POST, $_POST, $_POST);
	$treeView->show_action = false;

	if( isset($_POST[$treeView->_getFolderNameId()]) ) $folderid = $_POST[$treeView->_getFolderNameId()];
	else $folderid = $treeView->getSelectedFolderId();

	$folder = $treeView->tdb->getFolderById( $treeView->getSelectedFolderId() );
	$out->add('<form method="post" action="index.php?modname=course&amp;op=move_course">'
		.'<input type="hidden" id="authentic_request_mc" name="authentic_request" value="'.Util::getSignature().'" />'
		.'<input type="hidden" id="id_course" name="id_course" value="'.$id_course.'" />'
		.'<input type="hidden" id="folderid" name="'.$treeView->_getFolderNameId().'" value="'.$folderid.'" />');
	$out->add('<input type="hidden" name="folder_id" value="'.$treeView->getSelectedFolderId().'" />');
	$out->add('<input type="hidden" name="id_course" value="'.$id_course.'" />');
	$out->add('<div>'.$treeView->getFolderPrintName($folder).'</div>');
	$out->add($treeView->load());
	$out->add(' <img src="'.$treeView->_getMoveImage().'" alt="'.$treeView->_getMoveAlt().'" /> '
	.'<input type="submit" class="TreeViewAction" value="'.$lang->def("_MOVE").'"'
	.' name="move_course_here" id="move'.$id_course.'" />');
	$out->add(' <img src="'.$treeView->_getCancelImage().'" alt="'.$treeView->_getCancelAlt().'" /> '
	.'<input type="submit" class="TreeViewAction" value="'.$treeView->_getCancelLabel().'"'
	.' name="'.$treeView->_getCancelId().'" id="'.$treeView->_getCancelId().'" />');

	$out->add('</form>'
	.'</div>');
}

function move_course_upd() {
	checkPerm('mod');

	require_once($GLOBALS['where_lms'].'/admin/modules/category/category.php');
	require_once($GLOBALS['where_lms'].'/admin/modules/category/tree.category.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');

	require_once($GLOBALS['where_lms'].'/lib/category/class.categorytree.php');

	$id_course = importVar('id_course', true, 0);

	$categoryDb = new CategoryTree();//new TreeDb_CatDb($GLOBALS['prefix_lms'].'_category');
	$treeView = new TreeView_CatView($categoryDb, 'course_category', '');
	$treeView->parsePositionData($_POST, $_POST, $_POST);

	$error = 0;
	$query_course = "
	UPDATE ".$GLOBALS['prefix_lms']."_course
	SET idCategory = '".$treeView->getSelectedFolderId()."'
	WHERE idCourse = '".$id_course."'";
	if(!sql_query($query_course)) {

		$error = 1;
	}
	Util::jump_to('index.php?modname=course&op=course_list&course_category_status='.importVar('course_category_status')
		.'&result='.( $error ? 'ok_course' : 'fail_course' ));
}
/*
function courseCertifications() {

	if ((isset($_GET["id_course"])) && (!empty($_GET["id_course"]))) {
		$id_course=(int)$_GET["id_course"];
	}
	else
		return FALSE;

	// print form for certificate content -----------------------------------------------
	// print hidden field for general, point, edition -----------------------------------
	require_once(_base_.'/lib/lib.table.php');
	require_once(_base_.'/lib/lib.form.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');
	$lang =& DoceboLanguage::CreateInstance('course', 'lms');
	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$form = new Form();

	$url="index.php?modname=course&amp;op=upd_certifications&amp;id_course=".$id_course;
	$out->add($form->openForm("main_form", $url));
	//$out->add($form->openElementSpace());

	$general=getOtherTab('general');
	$out->add($general);
	$point=getOtherTab('point');
	$out->add($point);
	$tb	= new Table(Get::sett('visuItem'), $lang->def('_TITLE_CERTIFICATE_TO_COURSE'), $lang->def('_TITLE_CERTIFICATE_TO_COURSE'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink("index.php?modname=course&amp;op=certifications&amp;id_course=".$id_course);
	$ini=$tb->getSelectedElement();
	//search query of certificates
	$query_certificate = "
	SELECT id_certificate, name, description
	FROM ".$GLOBALS['prefix_lms']."_certificate
	ORDER BY name
	LIMIT $ini,".Get::sett('visuItem');

	// search certificates assigned -----------------------------------------------------
	$query_certificate_assigned="
	SELECT certificates
	FROM ".$GLOBALS['prefix_lms']."_course
	where idCourse= ".$id_course."";
	list($assigned_certificate) = sql_fetch_row(sql_query($query_certificate_assigned));

	$assigned_certificate=explode(',',$assigned_certificate);
	$query_certificate_tot = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_certificate";
	$re_certificate = sql_query($query_certificate);
	list($tot_certificate) = sql_fetch_row(sql_query($query_certificate_tot));
	$type_h = array('image', 'news_short_td');
	$cont_h	= array(
	$lang->def('_TITLE'),
	$lang->def('_DESCRIPTION')
	);
	$cont_h[] = '';
	$type_h[] = 'image';
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);
	$certificate_to_course=array();
	while(list($idCert, $name, $descr) = sql_fetch_row($re_certificate)) {
		$cont = array(
		$name,
		$descr
		);
		$certificate_val=0;
		$certificate_assigned=0;
		foreach($assigned_certificate as $key => $certificate_assigned){
			if ($certificate_assigned==$idCert) {
				$certificate_val=$assigned_certificate;
			}
		}
		$cont[] = $form->getCheckbox('',
		'certificate_to_course',
		'certificate_to_course['.$idCert.']',
		$idCert,
		$certificate_val) ;
		$tb->addBody($cont);
	}
	$out->add(getTitleArea($lang->def('_TITLE_CERTIFICATE_TO_COURSE'), 'certificate', $lang->def('_TITLE_CERTIFICATE_TO_COURSE'))
	.'<div class="std_block">'	);
	$out->add($tb->getTable().$tb->getNavBar($ini, $tot_certificate).'</div>');


	//$out->add($form->closeElementSpace());

	$out->add($form->openButtonSpace()
	.$form->getButton('save', 'save', $lang->def('_SAVE'))
	.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO')));
	$out->add(
	$form->closeButtonSpace()
	.$form->closeForm());

}


function updateCertifications() {

	$id_course = importVar('id_course', false, 0);

	$certificates='';
	if(is_string($_POST['certificate_to_course']) )
	{
		$certificates=unserialize(urldecode($_POST['certificate_to_course']));
		$certificates=implode(',',$certificates);
	} else if(isset($_POST['certificate_to_course'])) $certificates=implode(',',$_POST['certificate_to_course']);


	$qtxt ="UPDATE ".$GLOBALS["prefix_lms"]."_course ";
	$qtxt.="SET certificates='".$certificates."' ";
	$qtxt.="WHERE idCourse='".$id_course."' LIMIT 1";

	$q=sql_query($qtxt);

	Util::jump_to('index.php?modname=course&op=course_list&result=ok_course');
}
*/
function classroomToCourse() {

	require_once(_base_.'/lib/lib.form.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	if(isset($_POST['classroom_to_course'])) list($idCourse) = each($_POST['classroom_to_course']);
	else $idCourse = importVar('idCourse', true, 0);

	$of_loc = importVar('of_loc', false, '');
	$of_name = importVar('of_name', false, '');

	$query_course_name="
	SELECT name
	FROM ".$GLOBALS['prefix_lms']."_course
	WHERE idCourse = $idCourse ";
	list($course_name)= sql_fetch_row(sql_query($query_course_name));

	$checked_class = checkAvailableClass($idCourse);

	$out =& $GLOBALS['page'];
	$out->setWorkingZone('content');
	$form = new Form();

	$lang =& DoceboLanguage::CreateInstance('course', 'lms');

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_CLASSROOMTOCOURSE_CAPTION'), $lang->def('_CLASSROOMTOCOURSE_CAPTION'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink('index.php?modname=course&amp;op=classroom_to_course&amp;idCourse='.$idCourse.'&amp;of_loc='.$of_loc.'&amp;of_name='.$of_name);

	$ini = $tb->getSelectedElement();

	$classroom_order = "l.location, c.name ";
	if($of_loc == 'loc') 	$classroom_order = "l.location, c.name ";
	if($of_loc == 'locd') 	$classroom_order = "l.location DESC, c.name ";
	if($of_name == 'name') 	$classroom_order = "c.name, l.location ";
	if($of_name == 'namec') $classroom_order = "c.name DESC, l.location ";
	if($of_loc == '' && $of_name == '') $of_loc = 'loc';

	//search query of classrooms ----------------------------------------------
	$query_classroom = "
	SELECT c.idClassroom, c.name, c.description, l.location
	FROM ".$GLOBALS['prefix_lms']."_classroom AS c
		JOIN ".$GLOBALS['prefix_lms']."_class_location AS l
	WHERE l.location_id = c.location_id
	ORDER BY ".$classroom_order."
	LIMIT $ini,".Get::sett('visuItem');
	$re_classroom = sql_query($query_classroom);

	$query_classroom_tot = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_classroom";
	list($tot_classroom) = sql_fetch_row(sql_query($query_classroom_tot));

	// search classrooms assigned ---------------------------------------------
	$query_class_assigned = "
	SELECT classrooms
	FROM ".$GLOBALS['prefix_lms']."_course
	where idCourse= ".$idCourse."";
	list($assigned_classroom) = sql_fetch_row(sql_query($query_class_assigned));

	// table intestation
	$type_h = array('', '', '', 'image');
	$cont_h	= array(
		'<a href="'."index.php?modname=course&amp;op=classroom_to_course&amp;idCourse=$idCourse&amp;of_loc="
			.( $of_loc != 'locd' ? 'loc' : 'locd' ).'">'


		.( $of_loc == 'loc'
			? '<img src="'.getPathImage().'/standard/1downarrow.png" alt="'.$lang->def('_DEF_DOWN').'" />'
			: ( $of_loc == 'locd'
				? '<img src="'.getPathImage().'/standard/1uparrow.png" alt="'.$lang->def('_DEF_UP').'" />'
				:  '<img src="'.getPathImage().'/standard/sort.png" alt="'.$lang->def('_DEF_SORT').'" />' ) )
		.$lang->def('_LOCATION').'</a>',

		'<a href="'."index.php?modname=course&amp;op=classroom_to_course&amp;idCourse=$idCourse&amp;of_name="
			.( $of_name != 'named' ? 'name' : 'named' ).'">'

		.( $of_name == 'name'
			? '<img src="'.getPathImage().'/standard/1downarrow.png" alt="'.$lang->def('_DEF_DOWN').'" />'
			: ( $of_name == 'named'
				? '<img src="'.getPathImage().'/standard/1uparrow.png" alt="'.$lang->def('_DEF_UP').'" />'
				:  '<img src="'.getPathImage().'/standard/sort.png" alt="'.$lang->def('_DEF_SORT').'" />' ) )
		.$lang->def('_CLASSROOM', 'course').'</a>',

		$lang->def('_STATUS'),
		$lang->def('_USETHIS')
	);
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	$class_room_to_edition=array();
	while(list($idClassroom, $name, $descr, $location) = sql_fetch_row($re_classroom)) {

		$cont = array(
			'<label for="class_room_to_course_'.$idClassroom.'">'.$location.'</label>',
			'<label for="class_room_to_course_'.$idClassroom.'">'.$name.'</label>'
		);

		if(isset($checked_class[$idClassroom])) $cont[] = $lang->def('_CLASSROOM_OCCUPATED_YES');
		else $cont[] = '';

		$cont[] = $form->getRadio('',
								'class_room_to_course_'.$idClassroom.'',
								'class_room_to_course',
								$idClassroom,
								$assigned_classroom == $idClassroom ) ;
		$tb->addBody($cont);
	}
	$page_title = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_CLASSROOM'),
		$course_name
	);

	$GLOBALS['page']->add(
		getTitleArea($page_title, 'classroomtocourse', $lang->def('_CLASSROOM'))
		.'<div class="std_block">'

		.($checked_class !== false
			? getResultUi($lang->def('_CLASSROOM_OCCUPATED'))
			: ''
		)
		.getBackUi( 'index.php?modname=course&amp;op=course_list', $lang->def('_BACK') )

		.$form->openForm('assignClassroom', 'index.php?modname=course&amp;op=assignClassroom', false, false, 'multipart/form-data')
		.$form->getHidden('idCourse', 'idCourse', $idCourse)

		.$tb->getTable()
		.$tb->getNavBar($ini, $tot_classroom)

		.$form->openButtonSpace()
		.$form->getButton( 'assignClassroom' ,'assignClassroom',$lang->def('_SAVE'))
		.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm()
		.'</div>'
	, 'content');
}

function assignClassroom() {
	$err = FALSE;

	$idCourse = $_POST['idCourse'];

	// -- timetable setup ------------------------------------------------
	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt = new TimeTable();

	$resource 		= "classroom";
	$consumer 		= "course";
	$consumer_id 	= $idCourse;
	// -------------------------------------------------------------------

	if(isset($_POST['class_room_to_course'])) {

		$saved_room = $_POST['class_room_to_course'];

		// -- Adding info to the timetable -----------------------------------

		$qtxt ="
		SELECT date_begin, date_end
		FROM ".$GLOBALS["prefix_lms"]."_course
		WHERE idCourse='".(int)$idCourse."'";
		$q = sql_query($qtxt);

		if(!$q || !mysql_num_rows($q)) {

			Util::jump_to('index.php?modname=course&amp;op=course_list&result=fail_course');
		}

		list($start_date, $end_date) = sql_fetch_row($q);

		$save_ok=$tt->saveEvent(FALSE,
							$start_date,
							$end_date,
							$start_date,
							$end_date,
							$resource,
							$saved_room,
							$consumer,
							$consumer_id );

	} else {
		$saved_room = "";
	}


	// -- Removin old info from the timetable ----------------------------

	if($saved_room != '') {
		$exclude_resource_id=$saved_room;
	} else {
		$exclude_resource_id=FALSE;
	}

	$tt->deleteAllConsumerEventsForResource($resource, $consumer, $consumer_id, $exclude_resource_id);
	// -------------------------------------------------------------------

	$query=	"
	UPDATE ".$GLOBALS['prefix_lms']."_course
	SET classrooms = '$saved_room'
	WHERE idCourse = '$idCourse'";
	$re = sql_query($query);

	Util::jump_to('index.php?modname=course&amp;op=course_list&result='.( $re ? 'ok_course' : 'err_course' ));
}


function classroomToEdition() {

	require_once(_base_.'/lib/lib.form.php');
	//require_once(_i18n_.'/lib.lang.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

	if(isset($_POST['classroom_to_edition'])) list($edition_id) = each($_POST['classroom_to_edition']);
	else $edition_id = importVar('edition_id', true, 0);

	$of_loc = importVar('of_loc', false, '');
	$of_name = importVar('of_name', false, '');

	$form = new Form();

	$query_course_name = "SELECT idCourse, name
	FROM ".$GLOBALS['prefix_lms']."_course_edition
	WHERE idCourseEdition = '".$edition_id."'";
	list($idCourse, $edition_name) = sql_fetch_row(sql_query($query_course_name));

	$lang =& DoceboLanguage::CreateInstance('course', 'lms');

	$tb	= new Table(Get::sett('visuItem'), $lang->def('_CLASSROOMTOCOURSE_CAPTION'), $lang->def('_CLASSROOMTOCOURSE_CAPTION'));
	$tb->initNavBar('ini', 'link');
	$tb->setLink("index.php?modname=course&amp;op=classroom_to_edition&amp;edition_id=$edition_id".'&amp;of_loc='.$of_loc.'&amp;of_name='.$of_name);

	$ini = $tb->getSelectedElement();
	$checked_class = checkAvailableClass($idCourse, $edition_id);

	$classroom_order = "l.location, c.name ";
	if($of_loc == 'loc') 	$classroom_order = "l.location, c.name ";
	if($of_loc == 'locd') 	$classroom_order = "l.location DESC, c.name ";
	if($of_name == 'name') 	$classroom_order = "c.name, l.location ";
	if($of_name == 'namec') $classroom_order = "c.name DESC, l.location ";
	if($of_loc == '' && $of_name == '') $of_loc = 'loc';

	//search query of classrooms ---------------------------------
	$query_classroom = "
	SELECT c.idClassroom, c.name, c.description, l.location
	FROM ".$GLOBALS['prefix_lms']."_classroom AS c
		JOIN ".$GLOBALS['prefix_lms']."_class_location AS l
	WHERE l.location_id = c.location_id
	ORDER BY ".$classroom_order."
	LIMIT $ini,".Get::sett('visuItem');
	$re_classroom = sql_query($query_classroom);

	// search classrooms assigned --------------------------------
	$query_class_assigned="
	SELECT classrooms
	FROM ".$GLOBALS['prefix_lms']."_course_edition
	where idCourseEdition= ".$edition_id."";
	list($assigned_classroom) = sql_fetch_row(sql_query($query_class_assigned));

	$query_classroom_tot = "
	SELECT COUNT(*)
	FROM ".$GLOBALS['prefix_lms']."_classroom ";
	list($tot_classroom) = sql_fetch_row(sql_query($query_classroom_tot));

	// table intestation
	$type_h = array('', '', '', 'image');
	$cont_h	= array(
		'<a href="'."index.php?modname=course&amp;op=classroom_to_edition&amp;edition_id=".$edition_id."&amp;of_loc="
			.( $of_loc == 'loc' ? 'locd' : 'loc' ).'">'

		.( $of_loc == 'loc'
			? '<img src="'.getPathImage().'/standard/1downarrow.png" alt="'.$lang->def('_DEF_DOWN').'" />'
			: ( $of_loc == 'locd'
				? '<img src="'.getPathImage().'/standard/1uparrow.png" alt="'.$lang->def('_DEF_UP').'" />'
				:  '<img src="'.getPathImage().'/standard/sort.png" alt="'.$lang->def('_DEF_SORT').'" />' ) )
		.$lang->def('_LOCATION').'</a>',

		'<a href="'."index.php?modname=course&amp;op=classroom_to_edition&amp;edition_id=".$edition_id."&amp;of_name="
			.( $of_name == 'name' ? 'named' : 'name' ).'">'

		.( $of_name == 'name'
			? '<img src="'.getPathImage().'/standard/1downarrow.png" alt="'.$lang->def('_DEF_DOWN').'" />'
			: ( $of_name == 'named'
				? '<img src="'.getPathImage().'/standard/1uparrow.png" alt="'.$lang->def('_DEF_UP').'" />'
				:  '<img src="'.getPathImage().'/standard/sort.png" alt="'.$lang->def('_DEF_SORT').'" />' ) )
		.$lang->def('_CLASSROOM', 'course').'</a>',

		$lang->def('_STATUS'),
		$lang->def('_USETHIS')
	);
	$tb->setColsStyle($type_h);
	$tb->addHead($cont_h);

	$class_room_to_edition = array();
	while(list($idClassroom, $name, $descr, $location) = sql_fetch_row($re_classroom)) {

		$cont = array(
			'<label for="class_room_to_edition_'.$idClassroom.'">'.$location.'</label>',
			'<label for="class_room_to_edition_'.$idClassroom.'">'.$name.'</label>'
		);

		if(isset($checked_class[$idClassroom])) $cont[] = $lang->def('_CLASSROOM_OCCUPATED_YES');
		else $cont[] = '';

		$cont[] = $form->getRadio('',
								'class_room_to_edition_'.$idClassroom.'',
								'class_room_to_edition',
								$idClassroom,
								$assigned_classroom == $idClassroom ) ;
		$tb->addBody($cont);
	}

	$page_title = array(
		'index.php?modname=course&amp;op=course_list' => $lang->def('_CLASSROOM'),
		$edition_name
	);

	$GLOBALS['page']->add(
		getTitleArea($page_title, 'classroomtocourse', $lang->def('_CLASSROOM'))
		.'<div class="std_block">'
		.($checked_class !== false
			? getResultUi($lang->def('_CLASSROOM_OCCUPATED'))
			: ''
		)

		.getBackUi( 'index.php?modname=course&amp;op=course_list', $lang->def('_BACK') )

		.$form->openForm('assignEditionClassroom', 'index.php?modname=course&amp;op=assignEditionClassroom', false, false, 'multipart/form-data')

		.$form->getHidden('edition_id', 'edition_id', $edition_id)
		.$form->getHidden('idCourse', 'idCourse', $idCourse)

		.$tb->getTable()
		.$tb->getNavBar($ini, $tot_classroom)

		.$form->openButtonSpace()
		.$form->getButton('assignEditionClassroom' ,'assignEditionClassroom', $lang->def('_SAVE'))
		.$form->getButton('course_undo', 'course_undo', $lang->def('_UNDO'))
		.$form->closeButtonSpace()
		.$form->closeForm()

		.'</div>'
	, 'content');
}

function assignClassroomToEdition() {

	$err 				= FALSE;
	$idCourse 			= importVar('idCourse', true, 0);
	$idCourseEdition 	= importVar('edition_id', true, 0);

	// -- timetable setup ------------------------------------------------
	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt = new TimeTable();

	$resource 		= "classroom";
	$consumer 		= "course_edition";
	$consumer_id 	= $idCourseEdition;
	// -------------------------------------------------------------------

	if(isset($_POST['class_room_to_edition'])) {

		$saved_room = $_POST["class_room_to_edition"];

		// -- Adding info to the timetable -----------------------------------
		$qtxt = "SELECT date_begin, date_end "
				."FROM ".$GLOBALS["prefix_lms"]."_course_edition "
				."WHERE idCourseEdition = '".(int)$idCourseEdition."'";
		$q = sql_query($qtxt);

		if(!$q || !mysql_num_rows($q)) {
			Util::jump_to('index.php?modname=course&amp;op=course_list&result=fail_course');
		}

		list($start_date, $end_date) = sql_fetch_row($q);
		$save_ok = $tt->saveEvent(FALSE,
						$start_date,
						$end_date,
						$start_date,
						$end_date,
						$resource,
						$saved_room,
						$consumer,
						$consumer_id);

	} else {

		$saved_room = '';
	}

	// -- Removin old info from the timetable ----------------------------
	if($saved_room != '') {
		$exclude_resource_id = $saved_room;
	} else {
		$exclude_resource_id = FALSE;
	}

	$tt->deleteAllConsumerEventsForResource($resource, $consumer, $consumer_id, $exclude_resource_id);
	// -------------------------------------------------------------------

	$query = "UPDATE ".$GLOBALS['prefix_lms']."_course_edition
	SET classrooms = '$saved_room'
	WHERE idCourseEdition = $idCourseEdition";
	$err = sql_query($query);

	Util::jump_to('index.php?modname=course&amp;op=course_list&result='.($err === FALSE ? "ok_course" : "fail_course"));
}

// requires array of class assigned to course
/**
 * This function check if classrooms to be occupated are available or not
 *
 * @param int $idCourse
 * @return array $course_class or false if classrooms are available
 */
function checkAvailableClass ($idCourse, $edition_id=FALSE) {

	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();

	$resource="classroom";

	if ($edition_id === FALSE) {
		$consumer="course";
		$consumer_id=$idCourse;

		$qtxt ="SELECT date_begin, date_end FROM ".$GLOBALS["prefix_lms"]."_course ";
		$qtxt.="WHERE idCourse='".(int)$idCourse."'";
	}
	else {
		$consumer="course_edition";
		$consumer_id=$edition_id;

		$qtxt ="SELECT date_begin, date_end FROM ".$GLOBALS["prefix_lms"]."_course_edition ";
		$qtxt.="WHERE idCourseEdition='".(int)$edition_id."'";
	}

	$q=sql_query($qtxt);

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_assoc($q);

		$start_date=$row["date_begin"];
		$end_date=$row["date_end"];
	}
	else {
		return FALSE;
	}


	// Occupied resources
	$in_use=$tt->getResourcesInUse($resource, $start_date, $end_date);

	// Classroom Resources used by current consumer
	$consumer_resources=$tt->getConsumerResources($consumer, $consumer_id, $start_date, $end_date, $resource);

	foreach($consumer_resources as $val) {

		$tmp_id=$val["resource_id"];
		if (in_array($tmp_id, $in_use)) {
			unset($in_use[$tmp_id]);
		}
	}

	if (empty($in_use))
		$in_use=FALSE;

	return $in_use;
}

function getCoursesWithEditionArr($flat, $id_category, $id_categories) {

	$qtxt ="SELECT idCourse, name FROM ".$GLOBALS["prefix_lms"]."_course ";
	$qtxt.="WHERE course_edition='1' AND ";
	$qtxt.="idCategory IN ( ".( !$flat ? $id_category  : implode(",", $id_categories) )." ) ";
	$qtxt.="ORDER BY name";

	$q=sql_query($qtxt);

	$with_edition_arr=array();
	if (($q) && (mysql_num_rows($q) > 0)) {
		while($row=mysql_fetch_assoc($q)) {

			$id=$row["idCourse"];
			$with_edition_arr[$id]=$row["name"];

		}
	}

	return $with_edition_arr;
}

// return string of hidden for others tab
function getOtherTab($prefix) {
	require_once(_base_.'/lib/lib.form.php');
	$hidden='';
	$len_prefix = strlen($prefix);
	foreach ($_POST as $key => $value ) {
		if(substr($key,0,$len_prefix)== $prefix){
			if (is_array($value)){
				$value=urlencode(serialize($value));
			}
			$hidden.=Form::getHidden($key,$key,$value);

		}
	}
	return $hidden;
}


function updateCourseTimtable($course_id, $edition_id, $start_date, $end_date, $old_start_date=FALSE, $old_end_date=FALSE) {

	updateClassroomOccupation($course_id, $edition_id, $start_date, $end_date, $old_start_date, $old_end_date);
	updateUserOccupation($course_id, $edition_id, $start_date, $end_date);

}


function updateClassroomOccupation($course_id, $edition_id, $start_date, $end_date, $old_start_date=FALSE, $old_end_date=FALSE) {

	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();

	$resource="classroom";
	$course_id=(int)$course_id;

	if ($edition_id === FALSE) {
		$consumer="course";
		$consumer_id=$course_id;

		$qtxt ="SELECT classrooms FROM ".$GLOBALS["prefix_lms"]."_course ";
		$qtxt.="WHERE idCourse='".$course_id."'";
	}
	else {
		$consumer="course_edition";
		$consumer_id=(int)$edition_id;

		$qtxt ="SELECT classrooms FROM ".$GLOBALS["prefix_lms"]."_course_edition ";
		$qtxt.="WHERE idCourseEdition='".(int)$edition_id."'";
	}

	$q=sql_query($qtxt);

	if (($q) && (mysql_num_rows($q) > 0)) {
		$row=mysql_fetch_assoc($q);

		$classrooms=$row["classrooms"];
	}
	else {
		return FALSE;
	}
/*
	$classroom_arr=explode(",", $classrooms);
	$updated=array();

	foreach($classroom_arr as $resource_id) {
*/
		$save_ok=$tt->updateEvent(FALSE, $start_date, $end_date, $old_start_date, $old_end_date, $resource, $classrooms, $consumer, $consumer_id);
/*
		if ($save_ok) {
			$updated[]=$resource_id;
		}
	}


	if (empty($updated))
		$classrooms="";
	else
		$classrooms=implode(",", $updated);

	if ($edition_id === FALSE) {
		$qtxt ="UPDATE ".$GLOBALS['prefix_lms']."_course ";
		$qtxt.="SET classrooms='".$classrooms."' ";
		$qtxt.="WHERE idCourse='".(int)$course_id."'";
	}
	else {
		$qtxt ="UPDATE ".$GLOBALS['prefix_lms']."_course_edition ";
		$qtxt.="SET classrooms='".$classrooms."' ";
		$qtxt.="WHERE idCourseEdition='".(int)$edition_id."'";
	}
*/

//	$q=sql_query($qtxt);

	return $save_ok;
}


function updateUserOccupation($course_id, $edition_id, $start_date, $end_date) {

	require_once($GLOBALS["where_framework"]."/lib/resources/lib.timetable.php");
	$tt=new TimeTable();

	$consumer="user";
	$course_id=(int)$course_id;

	if ($edition_id > 0) {
		$resource="course_edition";
		$resource_id=(int)$edition_id;
	}
	else {
		$resource="course";
		$resource_id=$course_id;
	}

	$tt->updateEventDateByResource($resource, $resource_id, $start_date, $end_date);
}


function hasClassroom($type) {

	if (($type == "classroom") || ($type == "blended")) {
		$res=TRUE;
	}
	else {
		$res=FALSE;
	}

	return $res;
}

function courseDispatch($op) {

	if(isset($_POST['new_course'])) $op = 'new_course';
	if(isset($_POST['mod_course']) || isset($_GET['mod_course'])) $op = 'mod_course';

	if(isset($_POST['del_course'])) $op = 'del_course';
	if(isset($_POST['del_course_edition'])) $op = 'del_course_edition';
	if(isset($_POST['confirm_del_edition_course'])) $op='del_course_edition';
	if(isset($_POST['mod_course_edition'])) $op = 'mod_course_edition';
	if(isset($_POST['course_edition_modify'])) $op='confirm_mod_course_edition';
	if(isset($_POST['course_undo']))
	{
		$id_course = importVar('id_course', false, 0);
	
		$query_course = "SELECT course_type FROM ".$GLOBALS['prefix_lms']."_course WHERE idCourse = '".$id_course."'";
		list($course_type) = sql_fetch_row(sql_query($query_course));
		
		if($course_type === 'classroom')
			Util::jump_to('index.php?r=alms/classroom/show');
		else
			$op = 'course_list';
	}
	if(isset($_POST['course_undo_edition'])) $op = 'course_list';
	if(isset($_POST['assignClassroomToEd'])) $op = 'assignClassroomToEd';
	if(isset($_POST['assignClassroom'])) $op = 'assignClassroom';
	if(isset($_POST['classroom_to_course'])) $op = 'classroom_to_course';
	if(isset($_POST['classroom_to_edition'])) $op = 'classroom_to_edition';
	if(isset($_POST['classroom_to_course_ed'])) $op = 'classroom_to_course_ed';
	if(isset($_POST['checkAvailableClass'])) $op = 'classroom_to_course';
	if(isset($_POST['assign_menu_course'])) $op = 'assignMenu';
	if(isset($_POST['move_course'])) $op = 'move_course';
	if(isset($_POST['move_course_here'])) $op = 'move_course_upd';
	if(isset($_POST['undo'])) $op = 'course_list';
	if(isset($_POST['new_course_edition'])) $op = 'new_course_edition';
	if(isset($_POST['upd_course'])) $op = 'upd_course';


	if((isset($_GET['ini_hidden']) || isset($_POST['ini_hidden'])) && $op != 'course_list') {

		$_SESSION['course_category']['ini_status'] = importVar('ini_hidden', true, 0);
	}

	switch($op)
	{
		case "course_list" : {
			course();
		};break;
		case "new_course" : {
			addCourse();
		};break;
		case "add_course" : {
			insCourse();
		};break;
		case "del_course" : {
			courseDelete();
		};break;
		case "add_course_edition" : {
			insCourseEdition();
		};break;
		case "mod_course" : {
			modCourse();
		};break;
		case "new_course_edition" : {
			newCourseEdition();
		};break;
		case "mod_course_edition" : {
			modCourseEdition();
		};break;
		case "confirm_mod_course_edition" : {
			confirmModCourseEdition();
		};break;
		case "upd_course" : {
			courseUpdate();
		};break;
		case "move_course" : {
			move_course();
		};break;
		case "move_course_upd" : {
			move_course_upd();
		};break;
		case "del_course_edition" : {
			courseEditionDelete();
		};break;
		case "assignMenu" : {
			assignMenu();
		};break;


		case "classroom_to_course" : {
			classroomToCourse();
		};break;
		case "classroom_to_edition" : {
			classroomToEdition();
		};break;
		case "assignClassroomToEd" :
		case "assignEditionClassroom" : {
			assignClassroomToEdition();
		};break;
		case "assignClassroom" : {
			assignClassroom();
		};break;


		/*case "certifications": {
			require_once($GLOBALS["where_lms"]."/admin/modules/certificate/course.certificate.php");
			courseCertifications(true);
		} break;
		case "upd_certifications": {
			require_once($GLOBALS["where_lms"]."/admin/modules/certificate/course.certificate.php");
			updateCertifications();
		} break;*/

	}
}

?>