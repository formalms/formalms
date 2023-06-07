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

defined('IN_FORMA') or exit('Direct access is forbidden.');

if (\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
    exit("You can't access");
}

require_once _base_ . '/lib/lib.form.php';
require_once _lms_ . '/lib/lib.report.php';
require_once _lms_ . '/admin/modules/report/report_schedule.php';

define('_REPORT_SESSION', 'report_tempdata');
define('_RS_ID', 'id_report');
define('_RS_ROWS_FILTER', 'rows_filter');
define('_RS_COLS_FILTER', 'columns_filter');
define('_RS_COLS_CATEGORY', 'columns_filter_category');

function _encode($data)
{
    return serialize($data);
} //{ return urlencode(Util::serialize($data)); }
function _decode($data)
{
    return unserialize($data);
} //{ return Util::unserialize(urldecode($data)); }

function unload_filter($temp = false)
{
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $session->set('report', []);
    if ($temp) {
        $session->set(_REPORT_SESSION, []);
    }
    if ($session->has('report_update')) {
        $session->remove('report_update');
    }

    $session->set('report_saved', false);
    $session->set('report_saved_data', ['id' => '', 'name' => '']);
    $session->save();
}

function load_filter($id, $tempdata = false, $update = false)
{
    if ($id == false) {
        return;
    }
    checkReport($id);
    require_once _lms_ . '/lib/lib.report.php';

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    $row = sql_fetch_assoc(sql_query("SELECT * FROM %lms_report_filter WHERE id_filter=$id"));
    $temp = unserialize($row['filter_data']);
    if ($tempdata) {
        $session->set(_REPORT_SESSION, $temp);
    }
    $session->set('report', $temp);

    $session->set('report_saved', true);
    $session->set('report_saved_data', ['id' => $id, 'name' => getReportNameById($id)]);

    if ($update) {
        $session->set('report_update', $id);
    } else {
        $session->set('report_update', false);
    }
    $session->save();
}

function openreport($idrep = false)
{
    $lang = FormaLanguage::createInstance('report');

    if ($idrep != false && $idrep > 0) {
        $id_report = $idrep;
    } else {
        $id_report = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get(_REPORT_SESSION)['id_report'];

        if ($id_report != false && $idrep > 0) {
            load_filter($idrep, true, false);
        }
    }
    $query_report = "
	 SELECT class_name, file_name, report_name
	 FROM %lms_report
	 WHERE id_report = '" . $id_report . "'";
    $re_report = sql_query($query_report);

    if (sql_num_rows($re_report) == 0) {
        reportlist();

        return;
    }

    list($class_name, $file_name, $report_name) = sql_fetch_row($re_report);
    //when file name set use old style
    if ($file_name) {
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/admin/modules/report/' . $file_name);

        $obj_report = new $class_name($id_report);
    } else {
        $pg = new PluginManager('Report');
        $obj_report = $pg->get_plugin($class_name, [$id_report]);
    }

    return $obj_report;
}

function get_update_info()
{
    $output = '';

    return $output;
}

//******************************************************************************

$lang = FormaLanguage::createInstance('report');

define('_REP_KEY_NAME', 'name');
define('_REP_KEY_CREATOR', 'creator');
define('_REP_KEY_CREATION', 'creation');
define('_REP_KEY_PUBLIC', 'public');
define('_REP_KEY_OPEN', 'open');
define('_REP_KEY_MOD', 'mod');
define('_REP_KEY_SCHED', 'sched');
define('_REP_KEY_REM', 'rem');

function get_report_table($url = '')
{
    checkPerm('view');
    $can_mod = checkPerm('mod', true);
    $can_schedule = checkPerm('schedule', true);

    require_once _base_ . '/lib/lib.table.php';
    require_once _base_ . '/lib/lib.form.php';

    $acl_man = \FormaLms\lib\Forma::getAclManager();;
    $level = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst());

    $lang = FormaLanguage::createInstance('report');
    $output = '';

    $is_admin = (($level == ADMIN_GROUP_GODADMIN || $level == ADMIN_GROUP_ADMIN) ? true : false);

    if ($level == ADMIN_GROUP_GODADMIN || $can_mod) {//if ($can_mod) {
        cout('<script type="text/javascript">
		var _FAILURE = "error";
		var ajax_path = "' . FormaLms\lib\Get::rel_path('lms') . '/ajax.adm_server.php?mn=report&plf=lms";

		function public_report(o, id_rep) {
			o.disabled=true; //no more operations allowed on the checkbox while ajaxing

			var val_el=document.getElementById("enable_value_"+id_rep);
			var value=val_el.value;

			var data = "&op=public_rep&id="+id_rep+"&val="+value;
			var objAjax = YAHOO.util.Connect.asyncRequest("POST", ajax_path+data, {
			success:function(t) {
				var temp=o.src;
				if (value==1)	{ o.src=temp.replace("unpublish.png", "publish.png"); val_el.value=0; }
				if (value==0)	{ o.src=temp.replace("publish.png", "unpublish.png"); val_el.value=1; }
					o.disabled=false;
				},
			failure:function(t) {
					o.disabled=false;
					alert(_FAILURE); //...
				} });
			}

			function setReportFilter() {
				var el = document.createElement("INPUT");
				el.type = "hidden";
				el.name = "search";
				el.value = "1";
				var form = YAHOO.util.Dom.get("report_searchbox_form");
				if (form) {
					form.appendChild(el);
					form.submit();
				}
			}
		</script>', 'page_head');
    }

    //filter by author
    YuiLib::load();
    $current_user = $acl_man->getUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst(), false);

    //dropdown data arrays
    $authors = [
        0 => '(' . $lang->def('_ALL') . ')', //recycle text key
        $current_user[ACL_INFO_IDST] => $acl_man->relativeId($current_user[ACL_INFO_USERID]),
    ];
    $query = 'SELECT u.idst, u.userid FROM %lms_report_filter as r JOIN %adm_user as u ON (r.author=u.idst) WHERE u.idst<>' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() . ' ORDER BY u.userid';
    $res = sql_query($query);
    while ($row = sql_fetch_assoc($res)) {
        $authors[$row['idst']] = $acl_man->relativeId($row['userid']);
    }

    $arr_report_types = [
        0 => '(' . $lang->def('_ALL') . ')',
    ];

    //initializa session variable for filters
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $reportAdminFilter = $session->get('report_admin_filter');
    if (!isset($reportAdminFilter)) {
        $reportAdminFilter = [
            'author' => 0, //array_key_exists(\FormaLms\lib\FormaUser::getCurrentUser()->getIdst(), $authors) ? \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() : 0,
            'name' => '',
            'type' => 0,
        ];
    }

    if (FormaLms\lib\Get::req('search', DOTY_MIXED, false) !== false) {
        $reportAdminFilter['author'] = FormaLms\lib\Get::req('filter_author', DOTY_INT, (int) $reportAdminFilter['author']);
        $reportAdminFilter['name'] = FormaLms\lib\Get::req('filter_name', DOTY_STRING, $reportAdminFilter['name']);
        $reportAdminFilter['type'] = FormaLms\lib\Get::req('filter_type', DOTY_INT, (int) $reportAdminFilter['type']);
    }

    if (FormaLms\lib\Get::req('reset', DOTY_MIXED, false) !== false) {
        $reportAdminFilter['author'] = 0;
        $reportAdminFilter['name'] = '';
        $reportAdminFilter['type'] = 0;
    }
    $session->set('report_admin_filter', $reportAdminFilter);
    $session->save();

    $dropdown_onclick = 'onchange="javascript:setReportFilter();"';

    $output .= Form::openForm('report_searchbox_form', 'index.php?modname=report&op=reportlist&of_platform=lms', false, 'POST');
    $output .= Form::getHidden('op', 'op', 'reportlist');
    $output .= Form::getHidden('modname', 'modname', 'report');

    if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() === ADMIN_GROUP_GODADMIN) {
        $output .= '<div class="quick_search_form">
			<div>
				<div class="simple_search_box" id="report_searchbox_simple_filter_options" style="display: block;">'
            . Form::getInputDropdown('dropdown', 'report_searchbox_filter_author', 'filter_author', $authors, $reportAdminFilter['author'], $dropdown_onclick)
            . '&nbsp;&nbsp;&nbsp;'
            . Form::getInputTextfield('search_t', 'report_searchbox_filter_name', 'filter_name', $reportAdminFilter['name'], '', 255, '')
            . Form::getButton('report_searchbox_filter_set', 'search', Lang::t('_SEARCH', 'standard'), 'search_b')
            . Form::getButton('report_searchbox_filter_reset', 'reset', Lang::t('_RESET', 'standard'), 'reset_b')
            . '</div>
			</div>
		</div>';
    }
    $output .= Form::closeForm();

    //end filter

    //compose search query
    $qconds = [];
    $query = 'SELECT t1.*, t2.userid FROM %lms_report_filter as t1 LEFT JOIN %adm_user as t2 ON t1.author=t2.idst ';
    switch ($level) {
        case ADMIN_GROUP_GODADMIN:
            if ($reportAdminFilter['author'] > 0) {
                $qconds[] = ' t1.author = ' . $reportAdminFilter['author'] . ' ';
            }
            break;
        case ADMIN_GROUP_ADMIN:
        case ADMIN_GROUP_USER:
        default:
            if ($reportAdminFilter['author'] > 0) {
                $qconds[] = ' ( t1.author = ' . $reportAdminFilter['author'] . ' AND t1.is_public = 1 ) ';
            } else {
                $qconds[] = ' ( t1.author = ' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() . ' OR t1.is_public = 1 ) ';
            }

            break;
    }

    if (trim($reportAdminFilter['name']) != '') {
        $qconds[] = " t1.filter_name LIKE '%" . $reportAdminFilter['name'] . "%' ";
    }

    if (trim($reportAdminFilter['type']) > 0) {
        //$qconds[] = " t1.filter_name LIKE '".SESSION['report_admin_filter']['name']."' ";
    }

    if (!empty($qconds)) {
        $query .= ' WHERE ' . implode(' AND ', $qconds);
    }

    //end query

    $tb = new Table(FormaLms\lib\Get::sett('visu_course'));
    $tb->initNavBar('ini', 'button');
    $col_type = ['', '', 'align_center', 'image', 'image', 'img-cell', 'img-cell', 'image']; //,'image','image');

    if (FormaLms\lib\Get::sett('use_immediate_report') == 'on') {
        $col_content = [
            $lang->def('_NAME'),
            $lang->def('_TAB_REP_CREATOR'),
            $lang->def('_CREATION_DATE'),
            $lang->def('_TAB_REP_PUBLIC'),
            '<img src="' . getPathImage() . 'standard/view.png" alt="' . $lang->def('_VIEW') . '" title="' . $lang->def('_VIEW') . '" />',
            '<img src="' . getPathImage() . 'standard/msg_unread.png" alt="' . $lang->def('_SEND_EMAIL') . '" title="' . $lang->def('_SEND_EMAIL') . '" />',
            '<span class="ico-sprite subs_csv" title="' . Lang::t('_EXPORT_CSV', 'report') . '"><span>' . Lang::t('_EXPORT_CSV', 'report') . '</span></span>',
            '<span class="ico-sprite subs_xls" title="' . Lang::t('_EXPORT_XLS', 'report') . '"><span>' . Lang::t('_EXPORT_XLS', 'report') . '</span></span>',
        ];
    } else {
        $col_content = [
            $lang->def('_NAME'),
            $lang->def('_TAB_REP_CREATOR'),
            $lang->def('_CREATION_DATE'),
            $lang->def('_TAB_REP_PUBLIC'),
            '<img src="' . getPathImage() . 'standard/view.png" alt="' . $lang->def('_VIEW') . '" title="' . $lang->def('_VIEW') . '" />',
            '<span class="ico-sprite subs_csv" title="' . Lang::t('_EXPORT_CSV', 'report') . '"><span>' . Lang::t('_EXPORT_CSV', 'report') . '</span></span>',
            '<span class="ico-sprite subs_xls" title="' . Lang::t('_EXPORT_XLS', 'report') . '"><span>' . Lang::t('_EXPORT_XLS', 'report') . '</span></span>',
        ];
    }

    if ($level == ADMIN_GROUP_GODADMIN || $can_schedule) {
        $col_content[] = '<img src="' . getPathImage() . 'standard/wait_alarm.png" alt="' . $lang->def('_SCHEDULE') . '" title="' . $lang->def('_SCHEDULE') . '" />';
    }

    if ($level == ADMIN_GROUP_GODADMIN || $can_mod) {
        $col_type[] = 'image';
        $col_type[] = 'image';
        $col_content[] = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" title="' . $lang->def('_MOD') . '" />';
        $col_content[] = '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . '" title="' . $lang->def('_DEL') . '" />';
    }

    $tb->setColsStyle($col_type);
    $tb->addHead($col_content);

    if ($res = sql_query($query)) {
        foreach ($res as $row) {
            $id = $row['id_filter'];
            $opn_link =
                '<a href="index.php?modname=report&amp;op=show_results&amp;idrep=' . $id . '" ' . //'.$url.'&amp;action=open&amp;idrep='.$id.'" '.
                ' title="' . $lang->def('_VIEW') . '">' .
                '<img src="' . getPathImage() . 'standard/view.png" alt="' . $lang->def('_VIEW') . '" />' .
                '</a>';
            $sch_link =
                '<a href="index.php?modname=report&amp;op=schedulelist&amp;idrep=' . $id . '" ' .
                ' title="' . $lang->def('_SCHEDULE') . '">' .
                '<img src="' . getPathImage() . 'standard/wait_alarm.png" alt="' . $lang->def('_SCHEDULE') . '" />' .
                '</a>';
            $mod_link =
                '<a href="' . $url . '&amp;action=modify&amp;idrep=' . $id . '" ' .
                ' title="' . $lang->def('_MOD') . '">' .
                '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />' .
                '</a>';
            $rem_link =
                '<a href="' . $url . '&amp;action=delete&amp;idrep=' . $id . '" ' .
                ' title="' . $lang->def('_DEL') . ' : ' . ($row['author'] == 0 ? $lang->def($row['filter_name']) : $row['filter_name']) . '">' .
                '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . '" />'; //.

            $can_public = ($can_mod ? true : ($level == ADMIN_GROUP_GODADMIN && $row['author'] == \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() ? true : false));
            $public = '<image ' . ($can_public ? 'class="handover"' : '') . ' src="' . getPathImage('lms') . 'standard/' .
                ($row['is_public'] == 1 ? '' : 'un') . 'publish.png' . '" ' .
                ($level == ADMIN_GROUP_GODADMIN || $can_mod ? 'onclick="public_report(this, ' . $row['id_filter'] . ');" ' : '') . ' />' .
                '<input type="hidden" id="enable_value_' . $row['id_filter'] . '" ' .
                'value="' . ($row['is_public'] == 1 ? '0' : '1') . '" />';

            $export_url = 'index.php?modname=report&op=show_results&idrep=' . (int) $id;
            $export_link_csv = '<a class="ico-sprite subs_csv" href="' . $export_url . '&dl=csv" title="' . Lang::t('_EXPORT_CSV', 'report') . '"><span></span>' . Lang::t('_EXPORT_CSV', 'report') . '</a>';
            $export_link_xls = '<a class="ico-sprite subs_xls" href="' . $export_url . '&dl=xls" title="' . Lang::t('_EXPORT_XLS', 'report') . '"><span></span>' . Lang::t('_EXPORT_XLS', 'report') . '</a>';

            if (FormaLms\lib\Get::sett('use_immediate_report') == 'on') {
                //Check if user has already a send mail request for current report
                $user_id = \FormaLms\lib\FormaUser::getCurrentUser()->getId();
                $qry = "
				    SELECT * FROM %lms_report_schedule schedules
				    JOIN %lms_report_schedule_recipient recipients ON recipients.id_report_schedule = schedules.id_report_schedule AND recipients.id_user = $user_id
				    WHERE schedules.period LIKE '%now%'
				    AND schedules.id_report_filter=$id
				    AND schedules.enabled = 1
			    ";
                $background_task_search = sql_query($qry);
                if ($background_task_search->num_rows > 0) {
                    $background_execution_link = '<img src="' . getPathImage() . 'standard/move.png" alt="' . $lang->def('_EXECUTING') . '" title="' . $lang->def('_EXECUTING') . '" />';
                } else {
                    $background_execution_link = '<a href="index.php?modname=report&op=reportlist&action=send_email&idrep=' . (int) $id . '"><img src="' . getPathImage() . 'standard/msg_unread.png" alt="' . $lang->def('_SEND_EMAIL') . '" title="' . $lang->def('_SEND_EMAIL') . '" /></a>';
                }
            }

            $_name = ($row['author'] == 0 ? $lang->def($row['filter_name']) : $row['filter_name']);
            if (trim($reportAdminFilter['name']) != '') {
                $_name = Layout::highlight($_name, $reportAdminFilter['name']);
            }

            if (FormaLms\lib\Get::sett('use_immediate_report') == 'on') {
                $tb_content = [
                    _REP_KEY_NAME => $_name,
                    _REP_KEY_CREATOR => ($row['author'] == 0 ? '<div class="align_center">-</div>' : $acl_man->relativeId($row['userid'])),
                    _REP_KEY_CREATION => Format::date($row['creation_date']),
                    _REP_KEY_PUBLIC => $public, //$row['report_name'],
                    _REP_KEY_OPEN => $opn_link,
                    $background_execution_link,
                    $export_link_csv,
                    $export_link_xls, /*,
                    _REP_KEY_MOD    => $mod_link,
                    _REP_KEY_REM    => $rem_link*/
                ];
            } else {
                $tb_content = [
                    _REP_KEY_NAME => $_name,
                    _REP_KEY_CREATOR => ($row['author'] == 0 ? '<div class="align_center">-</div>' : $acl_man->relativeId($row['userid'])),
                    _REP_KEY_CREATION => Format::date($row['creation_date']),
                    _REP_KEY_PUBLIC => $public, //$row['report_name'],
                    _REP_KEY_OPEN => $opn_link,
                    $export_link_csv,
                    $export_link_xls, /*,
                    _REP_KEY_MOD    => $mod_link,
                    _REP_KEY_REM    => $rem_link*/
                ];
            }

            if ($level == ADMIN_GROUP_GODADMIN || $can_schedule) {
                $tb_content[_REP_KEY_SCHED] = $sch_link;
            }

            if ($level == ADMIN_GROUP_GODADMIN || $can_mod) {
                if ($row['author'] == \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() || $can_mod) {
                    $tb_content[_REP_KEY_MOD] = $mod_link;
                    $tb_content[_REP_KEY_REM] = $rem_link;
                } else {
                    $tb_content[_REP_KEY_MOD] = '&nbsp;';
                    $tb_content[_REP_KEY_REM] = '&nbsp;';
                }
            }
            $tb->addBody($tb_content);
        }
    }

    if ($level == ADMIN_GROUP_GODADMIN || $can_mod) {//if ($can_mod) {
        $tb->addActionAdd('
			<a href="index.php?modname=report&amp;op=report_category">' .
            '<img src="' . getPathImage() . 'standard/add.png" ' .
            'title="' . $lang->def('_NEW') . '" /> ' .
            $lang->def('_NEW') . '</a>');
    }

    $output .= $tb->getTable();

    require_once _base_ . '/lib/lib.dialog.php';
    setupHrefDialogBox('a[href*=delete]');

    return $output;
}

//step functions

function reportlist()
{
    checkPerm('view');

    require_once _lms_ . '/admin/modules/report/class.report.php'; //reportbox class
    require_once _lms_ . '/admin/modules/report/report_schedule.php';
    require_once _base_ . '/lib/lib.form.php';
    require_once _lms_ . '/lib/lib.report.php';

    if ($action = FormaLms\lib\Get::req('action', DOTY_STRING, false)) {
        switch ($action) {
            case 'sched_rem':
                report_delete_schedulation(FormaLms\lib\Get::req('id_sched', DOTY_INT, false));

                break;
        }
    }

    unload_filter(true);

    $lang = FormaLanguage::createInstance('report');

    $error = FormaLms\lib\Get::req('err', DOTY_STRING, false);
    switch ($error) {
        case 'plugin':
            cout(getErrorUi($lang->def('_ERROR_NOTEXISTS')));

            break;
    }

    cout(getTitleArea($lang->def('_REPORT'), 'report'));
    cout('<div class="std_block">');
    //cout(get_report_steplist($step_index));

    switch (FormaLms\lib\Get::req('saverep', DOTY_STRING, false)) {
        case 'true':
            cout(getResultUi($lang->def('_SAVE_REPORT_OK')));
            break;
        case 'false':
            cout(getErrorUi($lang->def('_SAVE_REPORT_FAIL')));
            break;
    }

    switch (FormaLms\lib\Get::req('modrep', DOTY_STRING, false)) {
        case 'true':
            cout(getResultUi($lang->def('_OPERATION_SUCCESSFUL')));
            break;
        case 'false':
            cout(getErrorUi($lang->def('_MOD_REPORT_FAIL')));
            break;
    }

    cout(get_report_table('index.php?modname=report&op=report_open_filter'));

    cout('</div>', 'content'); //std_block div
}

function report_category()
{
    checkPerm('mod');

    require_once _lms_ . '/admin/modules/report/class.report.php'; //reportbox class
    require_once _lms_ . '/lib/lib.report.php';
    //require_once('report_categories.php');
    load_categories();

    $lang = FormaLanguage::createInstance('report');

    $step_index = 0;
    cout(getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            $lang->def('_NEW'),
        ], 'report')
        . '<div class="std_block">');

    $error = FormaLms\lib\Get::req('err', DOTY_STRING, false);
    switch ($error) {
        case 'noname':
            cout(getErrorUi($lang->def('_ERROR_NONAME')));

            break;
    }

    $temp = [];
    foreach ($GLOBALS['report_categories'] as $key => $value) {
        $temp[$key] = $lang->def($value);
    }

    cout(Form::openForm('repcat_form', 'index.php?modname=report&op=report_rows_filter') .
        Form::getHidden('set_category', 'set_category', 1) .
        Form::getTextField(
            $lang->def('_NAME'), //$label_name,
            'report_name',
            'report_name',
            '200') .
        Form::getDropDown($lang->def('_SELECT_REPORT_CATEGORY'), '', 'id_report', $temp) .

        Form::openButtonSpace() .
        Form::getButton('cat_forward', 'cat_forward', $lang->def('_NEXT')) .
        Form::getButton('cat_undo', 'cat_undo', $lang->def('_UNDO')) .
        Form::closeButtonSpace() .
        Form::closeForm(), 'content');

    cout('</div>', 'content');
}

function report_rows_filter()
{
    checkPerm('mod');

    if (FormaLms\lib\Get::req('cat_undo', DOTY_MIXED, false)) {
        Util::jump_to('index.php?modname=report&op=reportlist');
    }

    $lang = FormaLanguage::createInstance('report');
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $reportTempData = $session->get(_REPORT_SESSION);

    if (FormaLms\lib\Get::req('set_category', DOTY_INT, 0) == 1) {
        if (FormaLms\lib\Get::req('report_name', DOTY_STRING, '') == '') {
            Util::jump_to('index.php?modname=report&op=report_category&err=noname');
        }
        $reportTempData['id_report'] = FormaLms\lib\Get::req('id_report', DOTY_ALPHANUM, false);
        $reportTempData['report_name'] = FormaLms\lib\Get::req('report_name', DOTY_STRING, false);
        $session->set(_REPORT_SESSION, $reportTempData);
        $session->save();
    }   

    $selectionType = (isset($_POST) && array_key_exists('selection_type', $_POST)) ? $_POST['selection_type'] : null;
    
    $obj_report = openreport();
    $obj_report->back_url = 'index.php?modname=report&op=report_category';
    $obj_report->jump_url = 'index.php?modname=report&op=report_rows_filter';
    $obj_report->next_url = 'index.php?modname=report&op=report_sel_columns';

    if((int) $obj_report->id_report === 2) {
        ob_end_clean();
        return Util::jump_to('index.php?r=adm/userselector/show&showSelectAll=true&instance=reportuser&id='.$obj_report->id_report);
    }

    if((int) $obj_report->id_report === 5 && $selectionType) {

        $reportTempData = $session->get(_REPORT_SESSION);
        $reportTempData['rows_filter']['selection_type'] = $selectionType;
        $session->set(_REPORT_SESSION, $reportTempData);
        $session->save();
        if($selectionType == 'users') {
            $tabFilters = '&tab_filters[]=user';
        }

        if($selectionType == 'groups') {
            $tabFilters = '&tab_filters[]=group&tab_filters[]=org&tab_filters[]=role';
        }
        ob_end_clean();
        return Util::jump_to('index.php?r=adm/userselector/show&instance=reportuser&id='.$obj_report->id_report.$tabFilters);
    }

    $page_title = getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'),
            $lang->def('_REPORT_SEL_ROWS'),
        ], 'report')
        . '<div class="std_block">';

    if ($obj_report->usestandardtitle_rows) {
        cout($page_title . '<div class="std_block">');
    } else {
        //this is used just to pass std title string to object functions, who may use it
        $obj_report->page_title = $page_title;
    }

    $obj_report->get_rows_filter();

    if ($obj_report->usestandardtitle_rows) {
        cout('</div>'); //close title area
    }
}

function report_sel_columns()
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';

    $lang = FormaLanguage::createInstance('report');
    $obj_report = openreport();
    $temp = $obj_report->get_columns_categories();

    cout(getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'), //$obj_report->report_name,
            'index.php?modname=report&amp;op=report_rows_filter' => $lang->def('_REPORT_SEL_ROWS'),
            $lang->def('_REPORT_SEL_COLUMNS'),
        ])
        . '<div class="std_block">'
        . Form::openForm('choose_category_form', 'index.php?modname=report&op=report_columns_filter&of_platform=lms'), 'content');
    $i = 1;
    foreach ($temp as $key => $value) {
        cout(Form::getRadio($i . ') ' . $value, 'sel_columns_' . $key, 'columns_filter', $key, ($i == 1)), 'content');
        ++$i;
    }
    cout(Form::openButtonSpace() .
        Form::getButton('sel_rep_columns_button', false, $lang->def('_CONFIRM'), false, '', true, true) .
        Form::closeButtonSpace() .
        Form::closeForm() .
        '</div>', 'content');
}

function report_columns_filter()
{
    checkPerm('mod');

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $reportTempData = $session->get(_REPORT_SESSION);
    if (isset($_POST['columns_filter'])) {
        $reportTempData['columns_filter_category'] = $_POST['columns_filter'];
        $session->set(_REPORT_SESSION, $reportTempData);
        $session->save();
    }

    $lang = FormaLanguage::createInstance('report');

    $obj_report = openreport();
    $obj_report->back_url = 'index.php?modname=report&op=report_sel_columns';
    $obj_report->jump_url = 'index.php?modname=report&op=report_columns_filter';
    $obj_report->next_url = 'index.php?modname=report&op=report_save';
    $obj_report->get_columns_filter($reportTempData['columns_filter_category']);

    if((int) $obj_report->id_report === 4 && $reportTempData['columns_filter_category'] == 'users') {
        ob_end_clean();
        return Util::jump_to('index.php?r=adm/userselector/show&showSelectAll=true&instance=reportuser&id='.$obj_report->id_report);
    }

    //page title
    $page_title = getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            'index.php?modname=report&amp;op=report_category' => $lang->def('_NEW'),
            'index.php?modname=report&amp;op=report_rows_filter' => $lang->def('_REPORT_SEL_ROWS'),
            'index.php?modname=report&amp;op=report_sel_columns' => $lang->def('_REPORT_SEL_COLUMNS'),
            $lang->def('_REPORT_COLUMNS'),
        ])
        . '<div class="std_block">';
    //.  	getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content');

    if ($obj_report->useStandardTitle_Columns()) {
        cout($page_title);
        cout(Form::openForm('report_columns_form', $obj_report->jump_url));
    } else {
        //this is used just to pass std title string to object functions, who may use it
        $obj_report->page_title = $page_title;
    }



    if ($obj_report->useStandardTitle_Columns()) {
        cout(
            Form::openButtonSpace()
            . Form::getBreakRow()
            . Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
            . Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
            . Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
            . Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report'))
            . Form::closeButtonSpace()
            . Form::closeForm());
        cout('</div>'); //close std_block div
    }
}

function report_save_filter()
{
    checkPerm('mod');

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $reportTempData = $session->get(_REPORT_SESSION);
    $report_id = $reportTempData['id_report'];
    $report_name = $reportTempData['report_name'];
    $nosave = FormaLms\lib\Get::req('nosave', DOTY_INT, 0);
    $show = FormaLms\lib\Get::req('show', DOTY_INT, 0);
    $idrep = FormaLms\lib\Get::req('modid', DOTY_INT, false);

    if ($nosave > 0) {
        Util::jump_to('index.php?modname=report&op=show_results&nosave=1' . ($idrep ? '&modid=' . $idrep : ''));
    }

    if ($session->get('report_update') !== null || $idrep) {
        $save_ok = report_update($idrep, $report_name, $reportTempData);
        if ($show) {
            Util::jump_to('index.php?modname=report&op=show_results&idrep=' . $idrep);
        } else {
            Util::jump_to('index.php?modname=report&op=reportlist&modrep=' . ($save_ok ? 'true' : 'false'));
        }
    } else {
        $save_ok = report_save($report_id, $report_name, $reportTempData);
        if ($show) {
            Util::jump_to('index.php?modname=report&op=show_results&idrep=' . $save_ok);
        } else {
            Util::jump_to('index.php?modname=report&op=reportlist&saverep=' . ($save_ok ? 'true' : 'false'));
        }
    }
}

function setup_report_js()
{
    YuiLib::load([
        'animation' => 'animation-min.js',
        'dragdrop' => 'dragdrop-min.js',
        'button' => 'button-min.js',
        'container' => 'container-min.js',
        'my_window' => 'windows.js',
    ], [
        'container/assets/skins/sam' => 'container.css',
        'button/assets/skins/sam' => 'button.css',
    ]);
    Util::get_js(FormaLms\lib\Get::rel_path('lms') . '/admin/modules/report/ajax.report.js', true, true);
}

function report_show_results($idrep = false)
{
    require_once _base_ . '/lib/lib.form.php';
    require_once _base_ . '/lib/lib.download.php';

    //import yui pop-up stuff
    setup_report_js();
    $filter_data = '';
    $lang = FormaLanguage::createInstance('report');
    $start_url = 'index.php?modname=report&op=reportlist';
    $download = FormaLms\lib\Get::req('dl', DOTY_STRING, false);
    $no_download = FormaLms\lib\Get::req('no_show_repdownload', DOTY_INT, 0);
    $nosave = FormaLms\lib\Get::req('nosave', DOTY_INT, 0);

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if ($idrep == false) {
        if (empty($session->get(_REPORT_SESSION))) {
            $reportTempData = $session->get('report');
        } else {
            $reportTempData = $session->get(_REPORT_SESSION);
        }
        $id_report = $reportTempData['id_report'];
        $res = sql_query('SELECT class_name, file_name FROM %lms_report WHERE id_report=' . $id_report . ' AND enabled=1');
        $author = 0;
        $filter_name = $reportTempData['report_name'];
        //['columns_filter_category']
        if ($res && (sql_num_rows($res) > 0)) {
            list($class_name, $file_name) = sql_fetch_row($res);
            if ($file_name) {
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/admin/modules/report/' . $file_name);
                $obj_report = new $class_name($idrep);
            } else {
                $pg = new PluginManager('Report');
                $obj_report = $pg->get_plugin($class_name, [$idrep]);
            }
        } else {
            reportlist();
        }
    } else {
        checkReport($idrep);
        /// find main class report filename and report info
        $query_report = "
		SELECT r.class_name, r.file_name, r.report_name, f.filter_name, f.filter_data, f.author
		FROM %lms_report AS r
			JOIN %lms_report_filter AS f
			ON ( r.id_report = f.id_report )
		WHERE f.id_filter = '" . $idrep . "'";
        $re_report = sql_query($query_report);

        if (sql_num_rows($re_report) == 0) {
            reportlist();

            return;
        }

        // create the report object
        list($class_name, $file_name, $report_name, $filter_name, $filter_data, $author) = sql_fetch_row($re_report);
        //when file name set use old style
        if ($file_name) {
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/admin/modules/report/' . $file_name);
            $obj_report = new $class_name($idrep);
        } else {
            $pg = new PluginManager('Report');
            $obj_report = $pg->get_plugin($class_name, [$idrep]);
        }
    }

    $obj_report->back_url = $start_url;
    $obj_report->jump_url = 'index.php?modname=report&op=show_results&idrep=' . $idrep;

    if ($author == 0) {
        $filter_name = ($filter_name ? $lang->def($filter_name) : '');
    }

    $data = _decode($filter_data);

    if ($download != false) {
        $export_filename = 'report_' . $filter_name . '_' . date('d_m_Y');
        switch ($download) {
            case 'htm':
                sendStrAsFile($obj_report->getHTML($data['columns_filter_category'], $data), $export_filename . '.html');
                break;
            case 'csv':
                sendStrAsFile($obj_report->getCSV($data['columns_filter_category'], $data), $export_filename . '.csv');
                break;
            case 'xls':
                sendStrAsFile($obj_report->getXLS($data['columns_filter_category'], $data), $export_filename . '.xls');
                break;
        }
    }

    cout(getTitleArea([$start_url => $lang->def('_REPORT'), $filter_name], 'report')
        . '<div class="std_block">'
        . getBackUi($start_url, $lang->def('_BACK_TO_LIST'), 'content'));

    if ($nosave > 0) {
        $mod_id = FormaLms\lib\Get::req('modid', DOTY_INT, false);
        cout('<br/>' . getBackUi('index.php?modname=report&op=report_save' . ($mod_id ? '&modid=' . $mod_id : ''), $lang->def('_SAVE_AND_BACK_TO_LIST')));
    }
    if ($no_download <= 0) {
        cout('<p class="export_list">' .
            '<a class="ico-wt-sprite subs_htm" href="' . $obj_report->jump_url . '&amp;dl=htm"><span>' . $lang->def('_EXPORT_HTML') . '</span></a>&nbsp;' .
            '<a class="ico-wt-sprite subs_csv" href="' . $obj_report->jump_url . '&amp;dl=csv"><span>' . $lang->def('_EXPORT_CSV') . '</span></a>&nbsp;' .
            '<a class="ico-wt-sprite subs_xls" href="' . $obj_report->jump_url . '&amp;dl=xls"><span>' . $lang->def('_EXPORT_XLS') . '</span></a>' .
            '</p>' .
            //'<div class="nofloat"></div>'.
            '<br/>');
    }

    // css -----------------------------------------------------------
    cout('<link href="' . getPathTemplate('lms') . 'style/report/style_report_user.css" rel="stylesheet" type="text/css" />' . "\n", 'page_head');

    $query_update = "UPDATE %lms_report_filter SET views = views+1 WHERE id_filter = '" . $idrep . "'";
    $re_update = sql_query($query_update);

    
    cout(Form::openForm('user_report_columns_courses', $obj_report->jump_url));
    cout($obj_report->show_results(is_array($data) ? $data['columns_filter_category'] : null, $data));
    cout(Form::closeForm() . '</div>');
}

function report_open_filter()
{
    require_once _lms_ . '/lib/lib.report.php';

    $url = 'index.php?modname=report&op=reportlist';
    $filter_id = FormaLms\lib\Get::req('idrep', DOTY_INT, false);
    $action = FormaLms\lib\Get::req('action', DOTY_STRING, '');
    if (!$filter_id) {
        Util::jump_to($url);

        return false;
    }

    switch ($action) {
        case 'schedule':
            load_filter($filter_id, true);
            Util::jump_to('index.php?modname=report&op=report_schedule');

            break;

        case 'open':
            load_filter($filter_id, true);
            Util::jump_to('index.php?modname=report&op=show_results');

            break;

        case 'modify':
            load_filter($filter_id, true, true); //will load it after the Util::jump_to
            Util::jump_to('index.php?modname=report&op=modify_name&modid=' . $filter_id);

            break;

        case 'delete':
            //delete filter from list and DB, than reload page
            if (report_delete_filter($filter_id)) {
                $success = '&fdel=1&idrep=' . $filter_id;
            } else {
                $success = '&fdel=0&idrep=' . $filter_id;
            }
            Util::jump_to($url . $success);

            break;

        default:
            Util::jump_to($url);
    }
}

function schedulelist()
{
    checkPerm('schedule');

    require_once _lms_ . '/admin/modules/report/report_schedule.php';
    require_once _base_ . '/lib/lib.form.php';
    require_once _lms_ . '/lib/lib.report.php';

    //import yui pop-up stuff
    setup_report_js();

    if ($action = FormaLms\lib\Get::req('action', DOTY_STRING, false)) {
        switch ($action) {
            case 'sched_rem':
                report_delete_schedulation(FormaLms\lib\Get::req('id_sched', DOTY_INT, false));

                break;
        }
    }

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    if ($session->has('schedule_tempdata')) {
        $session->remove('schedule_tempdata');
    }
    if ($session->has('schedule_update')) {
        $session->remove('schedule_update');
    }
    $session->save();

    require_once _base_ . '/lib/lib.form.php';
    $lang = FormaLanguage::createInstance('report');

    $idrep = FormaLms\lib\Get::req('idrep', DOTY_INT, false);
    cout(getTitleArea([
        'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
        $lang->def('_SCHEDULE') . '"<b>' . getReportNameById($idrep) . '</b>"', ]));

    cout('<div class="std_block">');
    cout('<p><span class="glyphicon glyphicon-warning-sign"></span> ' . $lang->def('_WARNING_REPORT') . '<p><hr>');
    cout(get_schedulations_table($idrep));

    cout('</div>', 'content'); //std_block div
}

//******************************************************************************

function report_modify_name()
{
    checkPerm('mod');

    require_once _lms_ . '/admin/modules/report/class.report.php'; //reportbox class
    require_once _lms_ . '/lib/lib.report.php';
    //require_once('report_categories.php');
    load_categories();

    $lang = FormaLanguage::createInstance('report');

    $idrep = FormaLms\lib\Get::req('modid', DOTY_INT, false);
    //if (!idrep) Util::jump_to(initial page ... )

    $page_title = getTitleArea([
        'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
        $lang->def('_MOD'),
    ], 'report'/*, $lang->def('_ALT_REPORT')*/);
    cout($page_title . '<div class="std_block">');

    $info = get_update_info();
    //if($info) cout( getInfoUi($info) );

    $box = new ReportBox('report_modify_name');

    $box->title = $lang->def('_MOD');
    $box->description = false;

    $box->body =
        Form::openForm('repcat_form', 'index.php?modname=report&op=modify_rows&modid=' . $idrep) .
        Form::getHidden('mod_name', 'mod_name', 1);

    $box->body .= Form::getTextField(
        $lang->def('_NAME'), //$label_name,
        'report_name',
        'report_name',
        '200', getReportNameById($idrep));

    $box->body .=
        //Form::closeElementSpace().
        Form::openButtonSpace() .
        Form::getButton('', '', $lang->def('_NEXT')) .
        Form::closeButtonSpace() .
        Form::closeForm();

    cout($box->get());

    cout('</div>', 'content');
}

function report_modify_rows()
{
    checkPerm('mod');

    $lang = FormaLanguage::createInstance('report');
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    $reportTempData = $session->get(_REPORT_SESSION);

    $idrep = FormaLms\lib\Get::req('modid', DOTY_INT, false);

    if (FormaLms\lib\Get::req('mod_name', DOTY_INT, 0) == 1) {
        $reportTempData['report_name'] = FormaLms\lib\Get::req('report_name', DOTY_STRING, false);
        $session->set(_REPORT_SESSION, $reportTempData);
        $session->save();
    }

    $obj_report = openreport();
    $obj_report->back_url = 'index.php?modname=report&op=modify_name&modid=' . $idrep;
    $obj_report->jump_url = 'index.php?modname=report&op=modify_rows&modid=' . $idrep;
    $obj_report->next_url = 'index.php?modname=report&op=modify_cols&modid=' . $idrep;

    $page_title = getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            'index.php?modname=report&op=modify_name&modid=' . $idrep => $lang->def('_MOD'),
            $lang->def('_REPORT_MOD_ROWS'),
        ], 'report')
        . '<div class="std_block">';

    /*$info = get_update_info();
    if($info) getInfoUi($info) );*/

    if ($obj_report->usestandardtitle_rows) {
        cout($page_title . '<div class="std_block">'); //.getBackUi($obj_report->back_url, $lang->def('_BACK'), 'content'));
        $info = get_update_info();
        if ($info) {
            cout(getInfoUi($info));
        }
        //cout(Form::openForm('user_report_rows_courses_mod', $obj_report->jump_url));
    } else {
        //this is just used to pass std title string to object functions, who may use it
        $obj_report->page_title = $page_title;
    }

    $obj_report->get_rows_filter();

    if ($obj_report->usestandardtitle_rows) {
        //cout(Form::closeForm());
        cout('</div>'); //close title area
    }
}

function report_modify_columns()
{
    checkPerm('mod');

    require_once _base_ . '/lib/lib.form.php';
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    $reportTempData = $session->get(_REPORT_SESSION);
    if (isset($_POST['columns_filter'])) {
        $reportTempData['columns_filter_category'] = $_POST['columns_filter'];
        $session->set(_REPORT_SESSION, $reportTempData);
        $session->save();
    }

    $idrep = FormaLms\lib\Get::req('modid', DOTY_INT, false);
    $lang = FormaLanguage::createInstance('report');

    $obj_report = openreport();

    $obj_report->back_url = 'index.php?modname=report&op=modify_rows&modid=' . $idrep;
    $obj_report->jump_url = 'index.php?modname=report&op=modify_cols&modid=' . $idrep;
    $obj_report->next_url = 'index.php?modname=report&op=report_save&modid=' . $idrep;

    //page title
    $page_title = getTitleArea([
            'index.php?modname=report&amp;op=reportlist' => $lang->def('_REPORT'),
            'index.php?modname=report&op=modify_name&modid=' . $idrep => $lang->def('_MOD'),
            'index.php?modname=report&op=modify_rows&modid=' . $idrep => $lang->def('_REPORT_MOD_ROWS'),
            $lang->def('_REPORT_MOD_COLUMNS'),
        ])
        . '<div class="std_block">';

    /*$info = get_update_info();
    if($info) cout( getInfoUi($info) );*/

    if ($obj_report->useStandardTitle_Columns()) {
        cout($page_title);
        $info = get_update_info();
        if ($info) {
            cout(getInfoUi($info));
        }
        cout(Form::openForm('user_report_columns_courses_mod', $obj_report->jump_url));
    } else {
        //this is used just to pass std title string to object functions, who may use it
        $obj_report->page_title = $page_title;
    }

    $obj_report->get_columns_filter($reportTempData['columns_filter_category']);

    if ($obj_report->useStandardTitle_Columns()) {
        cout(Form::openButtonSpace());
        cout(
            Form::getBreakRow()
            . Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
            . Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK', 'report'))
            . Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW', 'report'))
            . Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO', 'report'))
        );
        cout(Form::closeButtonSpace());
        cout(Form::closeForm());
        cout('</div>'); //close std_block div
    }
}

function send_email($idrep)
{
    //Verifica se esiste una pianificazione one shot attiva per l'utente
    $user_id = \FormaLms\lib\FormaUser::getCurrentUser()->getId();
    $qry = "	SELECT * FROM %lms_report_schedule schedules
				JOIN %lms_report_schedule_recipient recipients ON recipients.id_report_schedule = schedules.id_report_schedule AND recipients.id_user = $user_id
				WHERE schedules.period LIKE '%now%'
				AND schedules.id_report_filter=$idrep
				AND schedules.enabled = 1
			";
    $background_task_search = sql_query($qry);
    if ($background_task_search->num_rows == 0) {
        $recipients = [$user_id];
        report_save_schedulation($idrep, 'Send report email', 'now,', '', $recipients);
    }

    Util::jump_to('index.php?modname=report&op=reportlist');
}

// switch
function reportDispatch($op)
{
    if (isset($_POST['save_showed'])) {
        $op = 'report_schedule';
    }

    switch ($op) {
        case 'reportlist':
            if (isset($_GET['action']) && isset($_GET['idrep']) && $_GET['action'] == 'send_email') {
                send_email($_GET['idrep']);
            }
            reportlist();
            break;

        case 'report_category':
            report_category();
            break;

        case 'report_rows_filter':
            report_rows_filter();
            break;

        case 'report_sel_columns':
            report_sel_columns();
            break;

        case 'report_columns_filter':
            report_columns_filter();
            break;

        case 'report_save':
            if (FormaLms\lib\Get::req('nosave', DOTY_INT, 0) > 0) {
                report_show_results(false);
            }
            report_save_filter();

            break;

        case 'show_results':
            report_show_results(FormaLms\lib\Get::req('idrep', DOTY_INT, false));

            break;

        case 'modify_name':
            report_modify_name();

            break;

        case 'modify_rows':
            report_modify_rows();

            break;

        case 'modify_cols':
            report_modify_columns();

            break;

        case 'sched_mod':
            require_once _lms_ . '/admin/modules/report/report_schedule.php';
            modify_schedulation();

            break;

        case 'report_open_filter':
            report_open_filter();

            break;

        case 'report_schedule':
            require_once _lms_ . '/admin/modules/report/report_schedule.php';
            schedule_report();

            break;

        case 'schedulelist':
            schedulelist();

            break;
    } // end switch
}
