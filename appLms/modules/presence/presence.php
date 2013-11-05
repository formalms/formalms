<?php

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

if(!defined('IN_FORMA')) die('You cannot access this file directly');

require_once($GLOBALS['where_lms'].'/lib/lib.date.php');

function presence()
{
	checkPerm('view');

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	YuiLib::load();
	Util::get_js(Get::rel_path('lms').'/admin/views/classroom/classroom.js', true, true);

	$id_date = Get::req('id_date', DOTY_INT, 0);
	
	$lang =& DoceboLanguage::CreateInstance('admin_date', 'lms');
	$date_man = new DateManager();
	
	$user_date = $date_man->getUserDateForCourse(getLogUserId(), $_SESSION['idCourse']);
	$date_info = $date_man->getDateInfoForPublicPresence($user_date);

	foreach($date_info as $info_date)
		$date_for_dropdown[$info_date['id_date']] = $info_date['code'].' - '.$info_date['name'].' ('.Format::date($info_date['date_begin'], 'date').')';

	if($id_date == 0)
		$id_date = (isset($date_info[0]['id_date']) ? $date_info[0]['id_date'] : 0);

	cout(	getTitleArea(Lang::t('_ATTENDANCE'))
			.'<div class="std_block">', 'content');

	if(isset($_POST['save'])) {
		require_once($GLOBALS['where_lms'].'/lib/lib.date.php');

		$date_man = new DateManager();

		$id_date = Get::req('id_date', DOTY_INT, 0);
		$score_min = Get::req('score_min', DOTY_INT, 0);

		$user = $date_man->getUserForPresence($id_date);
		$day = $date_man->getDateDay($id_date);
		$test_type = $date_man->getTestType($id_date);

		foreach($user as $id_user => $user_info)
		{
			$user[$id_user]['score'] = Get::req('score_'.$id_user, DOTY_INT, 0);
			$user[$id_user]['note'] = Get::req('note_'.$id_user, DOTY_MIXED, '');
			$user[$id_user]['day_presence'] = array();

			for($i = 0; $i < count($day); $i++)
				$user[$id_user]['day_presence'][$day[$i]['id_day']] = Get::req('date_'.$day[$i]['id_day'].'_'.$id_user, DOTY_INT, 0);
		}

		if($date_man->insDatePresence($_SESSION['id_course_date'], $id_date, $user, $day, $score_min))
			UIFeedback::info(Lang::t('_ATTENDANCE_SAVED', 'admin_date'));
		else
			UIFeedback::error(Lang::t('_ATTENDANCE_SAVED_ERROR', 'admin_date'));
	}

	if($id_date == 0) {
		cout(Lang::t('_NO_CONTENT', 'admin_date'), 'content');
	} else {
		$user = $date_man->getUserForPresence($id_date);
		$day = $date_man->getDateDay($id_date);
		$test_type = $date_man->getTestType($id_date);
		$user_presence = $date_man->getUserPresenceForDate($id_date);

		$tb = new Table(0, Lang::t('_ATTENDANCE', 'admin_date'), Lang::t('_ATTENDANCE', 'admin_date'));

		$cont_h = array(	Lang::t('_USERNAME', 'admin_date'),
							Lang::t('_FULLNAME', 'admin_date'));

		$type_h = array('', '');

		foreach($day as $id_day => $day_info)
		{
			$cont_h[] = Format::date($day_info['date_begin'], 'date').'<br />'
						.'<a href="javascript:;" onClick="checkAllDay('.$id_day.')">'.Get::img('standard/checkall.png', Lang::t('_CHECK_ALL_DAY', 'presence').'</a>')
						.' '
						.'<a href="javascript:;" onClick="unCheckAllDay('.$id_day.')">'.Get::img('standard/uncheckall.png', Lang::t('_UNCHECK_ALL_DAY', 'presence').'</a>');
			$type_h[] = 'align_center';
		}

		$cont_h[] = '';
		$type_h[] = 'img-cell';

		if($test_type == _DATE_TEST_TYPE_PAPER)
		{
			$cont_h[] = Lang::t('_SCORE', 'admin_date');
			$type_h[] = 'align_center';
		}

		$cont_h[] = Lang::t('_NOTES', 'admin_date');
		$type_h[] = 'align_center';

		$tb->setColsStyle($type_h);
		$tb->addHead($cont_h);

		cout(	Form::openForm('presence_form', 'index.php?modname=presence&amp;op=presence')
				.Form::openElementSpace()
				.Form::getDropdown(Lang::t('_SELECT_EDITION', 'admin_date'), 'id_date', 'id_date', $date_for_dropdown, $id_date)
				.Form::closeElementSpace()
				.Form::openElementSpace()
				.($test_type == 1 ? Form::getTextfield(Lang::t('_MIN_SCORE', 'admin_date'), 'score_min', 'score_min', 255, '') : ''), 'content');

		$array_user_id = array();

		foreach($user as $id_user => $user_info)
		{
			reset($day);

			$array_user_id[] = $id_user;

			$cont = array();

			$cont[] = $user_info['userid'];
			$cont[] = $user_info['lastname'].' '.$user_info['firstname'];

			foreach($day as $id_day => $day_info)
			{
				if(isset($user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]) && $user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]['presence'] == 1)
					$presence = true;
				elseif(isset($user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]) && $user_presence[$id_user][substr($day_info['date_begin'], 0, 10)]['presence'] == 0)
					$presence = false;
				else
					$presence = false;

				$cont[] = Form::getInputCheckbox('date_'.$id_day.'_'.$id_user, 'date_'.$id_day.'_'.$id_user, 1, $presence, false);
			}

			$cont[] =	'<a href="javascript:;" onClick="checkAllUser('.$id_user.')">'.Get::img('standard/checkall.png', Lang::t('_CHECK_ALL_USER', 'presence').'</a>')
						.'<br />'
						.'<a href="javascript:;" onClick="unCheckAllUser('.$id_user.')">'.Get::img('standard/uncheckall.png', Lang::t('_UNCHECK_ALL_USER', 'presence').'</a>');

			if($test_type == _DATE_TEST_TYPE_PAPER)
			{
				if(isset($user_presence[$id_user]['0000-00-00']) && $user_presence[$id_user]['0000-00-00']['presence'] == 1)
					$passed = true;
				else
					$passed = false;

				$cont[] = Form::getTextfield('', 'score_'.$id_user, 'score_'.$id_user, 255, (isset($user_presence[$id_user]['0000-00-00']['score']) ? $user_presence[$id_user]['0000-00-00']['score'] : '0'));
			}

			$cont[] = Form::getSimpleTextarea('', 'note_'.$id_user, 'note_'.$id_user, (isset($user_presence[$id_user]['0000-00-00']['note']) ? $user_presence[$id_user]['0000-00-00']['note'] : ''), false, false, false, 2);

			$tb->addBody($cont);
		}

		cout(	$tb->getTable()
				.Form::closeElementSpace()
				.Form::openButtonSpace()
				.Form::getButton('save', 'save', Lang::t('_SAVE', 'admin_date'))
				.Form::closeElementSpace()
				.Form::closeForm()
				.'</div>', 'content');

		cout(	'<script type="text/javascript">'
				.'var _MIN_SCORE_NOT_SET = "'.Lang::t('_MIN_SCORE_NOT_SET', 'admin_date').'";'
				.'YAHOO.util.Event.addListener("save", "click", controlMinScore);'
				.'YAHOO.util.Event.addListener("id_date", "change", formSubmit);'
				.'function checkAllDay(id_day)
{
	var days = YAHOO.util.Selector.query(\'input[id*=_\' + id_day + \'_]\');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = true;
}

function unCheckAllDay(id_day)
{
	var days = YAHOO.util.Selector.query(\'input[id*=_\' + id_day + \'_]\');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = false;
}

function checkAllUser(id_user)
{
	var days = YAHOO.util.Selector.query(\'input[id*=_\' + id_user + \']\');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = true;
}

function unCheckAllUser(id_user)
{
	var days = YAHOO.util.Selector.query(\'input[id*=_\' + id_user + \']\');
	var i;

	for(i = 0; i < days.length; i++)
		days[i].checked = false;
}'
				.'</script>', 'content');
	}

	cout('</div>', 'content');
}

function dispatchPresence($op)
{
	switch($op)
	{
		case 'presence':
		default:
			presence();
		break;
	}
}
?>