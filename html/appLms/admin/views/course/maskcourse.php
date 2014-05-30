<?php
Get::title(array(
	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
	($id_course === false
		? Lang::t('_NEW_COURSE', 'course')
		: Lang::t('_MOD', 'course').': '.($course['code'] !== '' ? '['.$course['code'].'] ' : '').$course['name'])
));
?>
<div class="std_block">
	<?php
	echo Form::openForm('maskcourse_form', 'index.php?r='.$base_link_course.'/' . ($id_course === false ? 'newcourse' : 'modcourse'), false, 'post', 'multipart/form-data')
	. Form::getHidden('id_course', 'id_course', $id_course)
	. Form::openElementSpace()
	. ($id_course === false ? Form::getLineBox(Lang::t('_CATEGORY_SELECTED', 'course'), $name_category) .
			Form::getHidden('idCategory', 'idCategory', $_SESSION['course_category']['filter_status']['id_category']) : Form::getDropdown(Lang::t('_CATEGORY_SELECTED', 'course'), 'idCategory', 'idCategory', $model->getCategoryForDropdown(), $course['idCategory']))
	. Form::getTextfield(Lang::t('_CODE', 'course'), 'course_code', 'course_code', '50', $course['code'])
	. Form::getTextfield(Lang::t('_COURSE_NAME', 'course'), 'course_name', 'course_name', '255', $course['name']);

if ($course['course_type'] == 'classroom' && $has_editions_or_classrooms) {
	//this is a classroom course with editions
	echo Form::getLineBox(Lang::t('_COURSE_TYPE', 'course'), $course_type['classroom'])
		.Form::getHidden('course_type', 'course_type', 'classroom');
} elseif ($course['course_edition'] > 0  && $has_editions_or_classrooms) {
	//this is a classroom course with editions
	echo Form::getLineBox(Lang::t('_COURSE_TYPE', 'course'), $course_type['edition'])
		.Form::getHidden('course_type', 'course_type', 'edition');
} else {
	//echo Form::getDropdown(Lang::t('_COURSE_TYPE', 'course'), 'course_type', 'course_type', $course_type, $course['course_type']);
	echo Form::getDropdown(Lang::t('_COURSE_TYPE', 'course'), 'course_type', 'course_type', $course_type, $id_course === false ? 'elearning' : $course['course_type'] );
}
echo Form::getDropdown(Lang::t('_STATUS', 'course'), 'course_status', 'course_status', $status, $course['status'])
	. Form::getCheckbox(Lang::t('_DIRECT_PLAY', 'course'), 'direct_play', 'direct_play', '1', $course['direct_play'] == 1)
	// hiding the "show results" option - is not used (missing functions)
	//. Form::getCheckbox(Lang::t('_SHOW_RESULTS', 'course'), 'show_result', 'show_result', '1', $course['show_result'] == 1)
	. Form::getTextarea(Lang::t('_DESCRIPTION', 'course'), 'course_descr', 'course_descr', $course['description'])
	. ( $id_course !== false && $course['course_type'] != 'elearning' ? Form::getCheckbox(Lang::t('_CASCADE_MOD_ON_EDITION', 'course'), 'cascade_on_ed', 'cascade_on_ed', 1) : '' )
	. Form::closeElementSpace()
	. Form::openElementSpace()
	//-----------------------MISCELLANEOUS ---------------------------
	. Form::openCollasableFieldset(Lang::t('_DETAILS', 'course'))
	//-where-show-course----------------------------------------------
	. ( $id_course === false ? Form::getDropdown(Lang::t('_COURSE_MENU_TO_ASSIGN', 'course'), 'selected_menu', 'selected_menu', $menu_custom, $sel_custom) : '' )
	. Form::getDropdown(Lang::t('_COURSE_LANG_METHOD', 'course'), 'course_lang', 'course_lang', $array_lang, array_search($course['lang_code'], $array_lang))
	. Form::getDropdown(Lang::t('_DIFFICULTY', 'course'), 'course_difficult', 'course_difficult', $difficult_lang, $course['difficult'])
	. Form::getTextfield(Lang::t('_CREDITS', 'course'), 'credits', 'credits', '50', $course['credits'])
	. Form::getDropdown(Lang::t('_LABELS', 'label'), 'label', 'label', $label_model->getLabelFromDropdown(true), ($id_course === false ? false : $label_model->getCourseLabel($course['idCourse'])))
	//. (!$classroom ? Form::getCheckbox(Lang::t('_COURSE_EDITION', 'course'), 'course_edition_yes', 'course_edition', 1, $course['course_edition'] == 1) : '' )
	//. Form::getCloseCombo()
	. Form::getCloseFieldset()

			
	. Form::openCollasableFieldset(Lang::t('_COURSE_SUBSCRIPTION', 'course'))

	//-----------------------COURSE SUBSCRIPTION ---------------------
	. Form::getOpenCombo(Lang::t('_COURSE_SUBSRIBE', 'course'))
	. Form::getRadio(Lang::t('_COURSE_S_GODADMIN', 'course'), 'course_subs_godadmin', 'course_subs', '0', $course['subscribe_method'] == 0)
	. Form::getRadio(Lang::t('_COURSE_S_MODERATE', 'course'), 'course_subs_moderate', 'course_subs', '1', $course['subscribe_method'] == 1)
	. Form::getRadio(Lang::t('_COURSE_S_FREE', 'course'), 'course_subs_free', 'course_subs', '2', $course['subscribe_method'] == 2)
	. Form::getCloseCombo()

	. Form::getOpenCombo(Lang::t('_USER_CAN_SUBSCRIBE', 'course'))
	. Form::getRadio(Lang::t('_SUBSCRIPTION_CLOSED', 'course'), 'subscription_closed', 'can_subscribe', '0', $course['can_subscribe'] == 0)
	. Form::getRadio(Lang::t('_SUBSCRIPTION_OPEN', 'course'), 'subscription_open', 'can_subscribe', '1', $course['can_subscribe'] == 1)
	. Form::getRadio(Lang::t('_SUBSCRIPTION_IN_PERIOD', 'course') . ":", 'subscription_period', 'can_subscribe', '2', $course['can_subscribe'] == 2)
	. Form::getCloseCombo()

	. Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_BEGIN', 'course') . ":", 'sub_start_date', 'sub_start_date', $course['sub_start_date'])
	. Form::getDatefield(Lang::t('_SUBSCRIPTION_DATE_END', 'course') . ":", 'sub_end_date', 'sub_end_date', $course['sub_end_date'])

	. Form::getBreakRow()
	. Form::getOpenCombo(Lang::t('_USER_CAN_UNSUBSCRIBE', 'course'))
	. Form::getRadio(Lang::t('_COURSE_S_GODADMIN', 'course'), 'no_user_unsubscription', 'auto_unsubscribe', '0', $course['auto_unsubscribe'] == 0)
	. Form::getRadio(Lang::t('_COURSE_S_MODERATE', 'course'), 'moderated_user_unsubscription', 'auto_unsubscribe', '1', $course['auto_unsubscribe'] == 1)
	. Form::getRadio(Lang::t('_COURSE_S_FREE', 'course'), 'yes_user_unsubscription', 'auto_unsubscribe', '2', $course['auto_unsubscribe'] == 2)
	. Form::getCloseCombo()
	. Form::getDatefield(Lang::t('_UNSUBSCRIBE_DATE_LIMIT', 'course'), 'unsubscribe_date_limit', 'unsubscribe_date_limit', $unsubscribe_date_limit, FALSE, FALSE, '', '',
					Form::getInputCheckbox('use_unsubscribe_date_limit', 'use_unsubscribe_date_limit', 1, $use_unsubscribe_date_limit, '').' ' )
	
	. Form::getBreakRow()
	. Form::getTextfield(Lang::t('_COURSE_AUTOREGISTRATION_CODE', 'course'), 'course_autoregistration_code', 'course_autoregistration_code', '255', $course['autoregistration_code'])
	. Form::getCheckbox(Lang::t('_RANDOM_COURSE_AUTOREGISTRATION_CODE', 'course'), 'random_course_autoregistration_code', 'random_course_autoregistration_code', 0)
	. Form::getBreakRow()
	. Form::getCheckbox(Lang::t('_COURSE_SELL', 'course'), 'course_sell', 'course_sell', '1', $course['selling'] == 1)
	. Form::getTextfield(Lang::t('_COURSE_PRIZE', 'course'), 'course_prize', 'course_prize', '11', $course['prize'])
	. Form::getTextfield(Lang::t('_COURSE_ADVANCE', 'course'), 'advance', 'advance', '11', $course['advance'])
	. Form::getHidden('course_em', 'course_em', '0')

	. Form::getCloseFieldset()

	//-display-mode----------------------------------------------------
	. Form::openCollasableFieldset(Lang::t('_COURSE_DISPLAY_MODE', 'course'))

	//-where-show-course----------------------------------------------
	. Form::getOpenCombo(Lang::t('_WHERE_SHOW_COURSE', 'course'))
	. Form::getRadio(Lang::t('_SC_EVERYWHERE', 'course'), 'course_show_rules_every', 'course_show_rules', '0', $course['show_rules'] == 0)
	. Form::getRadio(Lang::t('_SC_ONLY_IN', 'course'), 'course_show_rules_only_in', 'course_show_rules', '1', $course['show_rules'] == 1)
	. Form::getRadio(Lang::t('_SC_ONLYINSC_USER', 'course'), 'course_show_rules_onlyinsc_user', 'course_show_rules', '2', $course['show_rules'] == 2)
	. Form::getCloseCombo()

	//-what-show------------------------------------------------------
	. Form::getOpenCombo(Lang::t('_WHAT_SHOW', 'course'))
	. Form::getCheckbox(Lang::t('_SHOW_PROGRESS', 'course'), 'course_progress', 'course_progress', '1', $course['show_progress'] == 1)
	. Form::getCheckbox(Lang::t('_SHOW_TIME', 'course'), 'course_time', 'course_time', '1', $course['show_time'] == 1)
	. Form::getCheckbox(Lang::t('_SHOW_ADVANCED_INFO', 'course'), 'course_advanced', 'course_advanced', '1', $course['show_extra_info'] == 1)
	. Form::getCloseCombo()
	. Form::getDropdown(Lang::t('_SHOW_WHOISONLINE', 'course'), 'show_who_online', 'show_who_online', $show_who_online, $course['show_who_online'])

	//-list-of-user---------------------------------------------------
	. Form::getOpenCombo(Lang::t('_SHOW_USER_OF_LEVEL', 'course'));

	while (list($level, $level_name) = each($levels)) {
		echo Form::getCheckbox($level_name, 'course_show_level_' . $level, 'course_show_level[' . $level . ']', $level, $course['level_show_user'] & (1 << $level));
	}
	echo Form::getCloseCombo()
	
	. Form::getOpenCombo(Lang::t('_COURSE_STATUS_CANNOT_ENTER', 'course'))
	. Form::getCheckbox(Lang::t('_USER_STATUS_SUBS', 'course'), 'user_status_' . _CUS_SUBSCRIBED, 'user_status[' . _CUS_SUBSCRIBED . ']', _CUS_SUBSCRIBED,
			$course['userStatusOp'] & (1 << _CUS_SUBSCRIBED))
	. Form::getCheckbox(Lang::t('_USER_STATUS_BEGIN', 'course'), 'user_status_' . _CUS_BEGIN, 'user_status[' . _CUS_BEGIN . ']', _CUS_BEGIN,
			$course['userStatusOp'] & (1 << _CUS_BEGIN))
	. Form::getCheckbox(Lang::t('_USER_STATUS_END', 'course'), 'user_status_' . _CUS_END, 'user_status[' . _CUS_END . ']', _CUS_END,
			$course['userStatusOp'] & (1 << _CUS_END))
	. Form::getCheckbox(Lang::t('_USER_STATUS_SUSPEND', 'course'), 'user_status_' . _CUS_SUSPEND, 'user_status[' . _CUS_SUSPEND . ']', _CUS_SUSPEND,
			$course['userStatusOp'] & (1 << _CUS_SUSPEND))
	. Form::getCloseCombo()
			
	. Form::getCloseFieldset()
	
	. Form::openCollasableFieldset(Lang::t('_COURSE_TIME_OPTION', 'course'))
	. Form::getDatefield(Lang::t('_DATE_BEGIN', 'course'), 'course_date_begin', 'course_date_begin', $course['date_begin'])
	. Form::getDatefield(Lang::t('_DATE_END', 'course'), 'course_date_end', 'course_date_end', $course['date_end'])
	. Form::getLineBox(
			'<label for="hour_begin_hour">' . Lang::t('_HOUR_BEGIN', 'course') . '</label>',
			Form::getInputDropdown('dropdown_nw', 'hour_begin_hour', 'hour_begin[hour]', $hours, $hb_sel, '')
			. ' : '
			. Form::getInputDropdown('dropdown_nw', 'hour_begin_quarter', 'hour_begin[quarter]', $quarter, $qe_sel, '')
	)
	. Form::getLineBox(
			'<label for="hour_end_hour">' . Lang::t('_HOUR_END', 'course') . '</label>',
			Form::getInputDropdown('dropdown_nw', 'hour_end_hour', 'hour_end[hour]', $hours, $he_sel, '')
			. ' : '
			. Form::getInputDropdown('dropdown_nw', 'hour_end_quarter', 'hour_end[quarter]', $quarter, $qe_sel, '')
	)
	. Form::getTextfield(Lang::t('_DAY_OF_VALIDITY', 'course'), 'course_day_of', 'course_day_of', '10', $course['valid_time'])
	. Form::getTextfield(Lang::t('_MEDIUM_TIME', 'course'), 'course_medium_time', 'course_medium_time', '10', $course['mediumTime'])
	. Form::getCloseFieldset()

	. Form::openCollasableFieldset(Lang::t('_COURSE_SPECIAL_OPTION', 'course'))
	. Form::getTextfield(Lang::t('_MIN_NUM_SUBSCRIBE', 'course'), 'min_num_subscribe', 'min_num_subscribe', '11', $course['min_num_subscribe'])
	. Form::getTextfield(Lang::t('_MAX_NUM_SUBSCRIBE', 'course'), 'max_num_subscribe', 'max_num_subscribe', '11', $course['max_num_subscribe'])
	. Form::getCheckbox(Lang::t('_ALLOW_OVERBOOKING', 'course'), 'allow_overbooking', 'allow_overbooking', '1', $course['allow_overbooking'] == 1)
	. Form::getTextfield(Lang::t('_COURSE_QUOTA', 'course'), 'course_quota', 'course_quota', '11', ($course['course_quota'] != COURSE_QUOTA_INHERIT ? $course['course_quota'] : 0))
	. Form::getCheckbox(Lang::t('_INHERIT_QUOTA', 'course'), 'inherit_quota', 'inherit_quota', '1', $course['course_quota'] == COURSE_QUOTA_INHERIT)
	. Form::getCloseFieldset()
	
	. Form::openCollasableFieldset(Lang::t('_DOCUMENT_UPLOAD', 'course'))
	//. Form::getExtendedFilefield(Lang::t('_USER_MATERIAL', 'course'), 'course_user_material', 'course_user_material', $course["img_material"])
	//. Form::getExtendedFilefield(Lang::t('_OTHER_USER_MATERIAL', 'course'), 'course_otheruser_material', 'course_otheruser_material', $course["img_othermaterial"])
	. Form::getTextfield(Lang::t('_SPONSOR_LINK', 'course'), 'course_sponsor_link', 'course_sponsor_link', '255', $course['linkSponsor'])
	. Form::getExtendedFilefield(Lang::t('_PATHSPONSOR', 'configuration'), 'course_sponsor_logo', 'course_sponsor_logo', $course["imgSponsor"])
	. Form::getExtendedFilefield(Lang::t('_COURSE_LOGO', 'course'), 'course_logo', 'course_logo', $course["img_course"])
	. Form::getExtendedFilefield(Lang::t('_COURSE_DEMO', 'course'), 'course_demo', 'course_demo', $course["course_demo"])
	. Form::getCheckbox(Lang::t('_USE_LOGO_IN_COURSELIST', 'course'), 'use_logo_in_courselist', 'use_logo_in_courselist', 1, $course["use_logo_in_courselist"])
	. Form::getCloseFieldset()
	. Form::closeElementSpace()
	. Form::openButtonSpace()
    .((($_REQUEST['r'] == 'alms/course/newcourse')  || ($_REQUEST['r'] == 'alms/course/modcourse' && $row[0] == 0))? Form::getCheckbox(Lang::t('_AUTO_SUBSCRIPTION'), 	'auto_subscription', 	'auto_subscription', 	'1', true ) : '' )            
	. Form::getButton('save', 'save', Lang::t('_SAVE'))
	. Form::getButton('undo', 'undo', Lang::t('_UNDO'))
	. Form::closeButtonSpace()
	. Form::closeForm();
	?>
</div>
<script type="text/javascript">
var D = YAHOO.util.Dom, E = YAHOO.util.Event;
E.onDOMReady(function() {
	var c = D.get("use_unsubscribe_date_limit"), d = D.get("unsubscribe_date_limit");
	E.addListener("no_user_unsubscription", "click", function(e) {
		var checked = this.checked;
		c.disabled = checked;
		d.disabled = checked;
	});
	E.addListener("moderated_user_unsubscription", "click", function(e) {
		var checked = this.checked;
		c.disabled = !checked;
		d.disabled = !checked;
	});
	E.addListener("yes_user_unsubscription", "click", function(e) {
		var checked = this.checked;
		c.disabled = !checked;
		d.disabled = !checked;
	});
});
</script>