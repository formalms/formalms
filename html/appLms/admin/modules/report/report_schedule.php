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

defined('IN_FORMA') or exit('Direct access is forbidden.');

//users selector
function schedule_recipients($idrep)
{
    checkPerm('mod');

    $lang = &DoceboLanguage::createInstance('report', 'framework');

    if (FormaLms\lib\Get::req('schedule_undo', DOTY_MIXED, false)) {
        //$back_op = FormaLms\lib\Get::req()
        $back_url = 'index.php?modname=report&op=schedulelist&idrep=' . $idrep;
        Util::jump_to($back_url);
    }

    $back_url = 'index.php?modname=report&op=report_schedule&idrep=' . $idrep;
    $jump_url = 'index.php?modname=report&op=report_schedule&idrep=' . $idrep;
    $end_url = 'index.php?modname=report&op=schedulelist&idrep=' . $idrep;

    require_once _base_ . '/lib/lib.form.php';
    require_once _adm_ . '/lib/lib.directory.php';
    require_once _base_ . '/lib/lib.userselector.php';
    require_once _lms_ . '/lib/lib.report.php';
    //require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

    $aclManager = new DoceboACLManager();
    $user_select = new UserSelector();

    $lang = &DoceboLanguage::createInstance('report', 'framework');

    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    if (!isset($_POST['is_updating'])) {
            //save filter, if needed
        require_once _lms_ . '/lib/lib.report.php';
        //save schedulation data in session
        if (!$session->has('schedule_tempdata')) {
            $session->set('schedule_tempdata',[]);
            $session->save();
        }

        switch ($_POST['cron_radio']) {
                case 'day':
                    $sched_info = '';
                    $sched_time = $_POST['cron_daily_time'];
                    break;
                case 'now':
                    $sched_info = '';
                    $sched_time = '';
                    break;
                case 'week':
                    $sched_info = $_POST['cron_weekly'];
                    $sched_time = $_POST['cron_weekly_time'];
                    break;
                case 'month':
                    $sched_info = $_POST['cron_monthly'];
                    $sched_time = $_POST['cron_monthly_time'];
                    break;
                default:
                    $sched_info = '';
                    $sched_time = '';
                    break;
            }

        $scheduleTempData = $session->get('schedule_tempdata',[]);

        $scheduleTempData['name'] = $_POST['sched_name'];
        $scheduleTempData['period'] = $_POST['cron_radio'];
        $scheduleTempData['period_info'] = $sched_info;
        $scheduleTempData['time'] = $sched_time;
        $session->set('schedule_tempdata',$scheduleTempData);
        $session->save();

        $user_select->resetSelection($scheduleTempData['recipients']);
    }

    $save_schedule_failed = false;

    if (isset($_POST['cancelselector'])) {
            //Util::jump_to($back_url);
        Util::jump_to('index.php?modname=report&op=schedulelist&idrep=' . $idrep);
    } elseif (isset($_POST['okselector'])) {
        $scheduleTempData = $session->get('schedule_tempdata',[]);

        $entity_selected = $user_select->getSelection($_POST);

        //$_temp = $ref['recipients'];
        $_name = $scheduleTempData['name'];
        $_time = $scheduleTempData['time'];
        $_period = $scheduleTempData['period'] . ',' . $scheduleTempData['period_info'];

        //get current saved report ID from session (check if report is saved, otherwise -> error)

        if ($session->has('schedule_tempdata')) {
            $sched = report_update_schedulation($scheduleTempData, $_name, $_period, $_time, $entity_selected);
        } else {
            $id_report = $idrep; 
            $sched = report_save_schedulation($id_report, $_name, $_period, $_time, $entity_selected);
        }

        if ($sched !== false) {
            Util::jump_to($end_url);
        } else {
            $save_schedule_failed = true;
        }
    }

    $error_info = '';
    if ($save_schedule_failed) {
        //$page_title .= ''; //...
        //addforminfo
        $error_info = getErrorUi($lang->def('_OPERATION_FAILURE'));
    }

    //draw selector
    $user_select->show_user_selector = true;
    $user_select->show_group_selector = true;
    $user_select->show_orgchart_selector = true;
    $user_select->show_orgchart_simple_selector = false;

    cout(getTitleArea($lang->def('_SCHEDULE')), 'content'); //add beck url
    cout('<div class="std_block">', 'content');

    //$user_select->setPageTitle($page_title);
    $user_select->addFormInfo(
            getBackUi($back_url, $lang->def('_BACK'), 'content') .
            Form::getHidden('next_step', 'next_step', 'sched_setrecipients') .
            Form::getHidden('is_updating', 'is_updating', 1));
    $user_select->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
                false,
                true);

    cout('</div>', 'content');
}

//time and period of schedulation
function schedule_set($idrep, $checkperm = 'mod')
{
    if ($checkperm) {
        checkPerm($checkperm);
    }

    $lang = &DoceboLanguage::createInstance('report', 'framework');
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    //initialize session data for schedulation, if not updating
    $scheduleTempData = $session->get('schedule_tempdata');
    if (empty($scheduleTempData)) {
        $scheduleTempData = [
                'name' => '',
                'period' => 'day',
                'period_info' => '',
                'time' => '',
                'recipients' => [],
            ];
        $session->set('schedule_tempdata',$scheduleTempData);
        $session->save();
    }

    require_once _base_ . '/lib/lib.form.php';

    $jump_url = 'index.php?modname=report&op=report_schedule&idrep=' . $idrep;
    $back_url = 'index.php?modname=report&op=schedulelist&idrep=' . $idrep;

    $body = Form::openForm('report_schedule_time', $jump_url);

    $body .= //'nome filtro:<input type="text" name="filter_name" value="" />';
            Form::getTextfield(
                $lang->def('_SAVE_SCHED_NAME'), //$label_name,
                'sched_name',
                'sched_name',
                '200', $ref['name']) .
            Form::getHidden('next_step', 'next_step', 'sched_setrecipients');

    //create selections for crontab specification
    $month_days = [];
    for ($i = 1; $i <= 31; ++$i) {
        $month_days[$i] = $i; //TO DO : format with 2 digits filling with 0
    }

    $year_months = [];
    for ($i = 1; $i <= 12; ++$i) {
        $year_months[$i] = $i; //TO DO : format with 2 digits filling with 0
    }

    $lang_days = &DoceboLanguage::createInstance('calendar', 'lms');
    $week_days = [
            '0' => $lang_days->def('_SUNDAY'),
            '1' => $lang_days->def('_MONDAY'),
            '2' => $lang_days->def('_TUESDAY'),
            '3' => $lang_days->def('_WEDNESDAY'),
            '4' => $lang_days->def('_THURSDAY'),
            '5' => $lang_days->def('_FRIDAY'),
            '6' => $lang_days->def('_SATURDAY'),
        ];

    $body .=

            '<div class="form_line_l">' .
            Form::getInputRadio('cron_radio_1',
                'cron_radio',
                'day',
                ($ref['period'] == 'day' ? true : false),
                '') .
            ' <label class="label_normal" for="cron_radio_1">' . $lang->def('_REPORT_DAILY') . ', ' . $lang->def('_AT_HOUR') . '</label> ' .
            Form::getInputTimeSelectorField('', 'cron_daily_time', 'cron_daily_time', $ref['period'] == 'day' ? $ref['time'] : '00:00', '') .
            '</div>' .

            Form::getRadio($lang->def('_REPORT_NOW'), 'cron_radio_4', 'cron_radio', 'now', ($ref['period'] == 'now' ? true : false)) .

            '<div class="form_line_l">' .
            Form::getInputRadio('cron_radio_2',
                'cron_radio',
                'week',
                ($ref['period'] == 'week' ? true : false),
                '') .
            ' <label class="label_normal" for="cron_radio_2">' . $lang->def('_REPORT_WEEKLY') . '</label> ' .
            Form::getInputDropdown('', 'cron_weekly', 'cron_weekly', $week_days, ($ref['period'] == 'week' ? $ref['period_info'] : ''), '') .
            ' <label class="label_normal" for="cron_weekly_time">, ' . $lang->def('_AT_HOUR') . '</label> ' .
            Form::getInputTimeSelectorField('', 'cron_weekly_time', 'cron_weekly_time', $ref['period'] == 'week' ? $ref['time'] : '00:00', '') .
            '</div>' .

            '<div class="form_line_l">' .
            Form::getInputRadio('cron_radio_3',
                'cron_radio',
                'month',
                ($ref['period'] == 'month' ? true : false),
                '') .
            ' <label class="label_normal" for="cron_radio_3">' . $lang->def('_REPORT_MONTHLY') . '</label> ' .
            Form::getInputDropdown('', 'cron_monthly', 'cron_monthly', $month_days, ($ref['period'] == 'month' ? $ref['period_info'] : ''), '') .
            ' <label class="label_normal" for="cron_monthly_time">, ' . $lang->def('_AT_HOUR') . '</label> ' .
            Form::getInputTimeSelectorField('', 'cron_monthly_time', 'cron_monthly_time', $ref['period'] == 'month' ? $ref['time'] : '00:00', '') .
            '</div>' .

            Form::getHidden('idrep', 'idrep', $idrep);

    $body .=
            Form::openButtonSpace() .

            Form::getButton('', 'schedule_confirm', $lang->def('_NEXT')) .
            Form::getButton('', 'schedule_undo', $lang->def('_UNDO')) .

            form::closeButtonSpace();

    $body .= Form::closeForm();

    //output content
    cout(getTitleArea($lang->def('_SCHEDULE')));
    cout('<div class="std_block">');

    cout($body);

    cout('</div>'); //close std_block div
}

function modify_schedulation()
{
    checkPerm('mod');
    //preload schedulation data in session
    require_once _lms_ . '/lib/lib.report.php';
    $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

    if ($id_sched = FormaLms\lib\Get::req('id_sched', DOTY_INT, false)) {
        $qry = 'SELECT * FROM ' . $GLOBALS['prefix_lms'] . "_report_schedule WHERE id_report_schedule=$id_sched";
        $row = sql_fetch_assoc(sql_query($qry));

        $recipients = [];
        $qry = 'SELECT * FROM ' . $GLOBALS['prefix_lms'] . "_report_schedule_recipient WHERE id_report_schedule=$id_sched";
        $recs = sql_query($qry);
        while ($trow = sql_fetch_assoc($recs)) {
            $recipients[] = $trow['id_user'];
        }

        $period = explode(',', $row['period']);
        $session->set('schedule_update',$id_sched);
        $session->set('schedule_tempdata',[
            'name' => $row['name'],
            'period' => $period[0],
            'period_info' => $period[1],
            'time' => '',
            'recipients' => $recipients,
        ]);

        $rid = $row['id_report_filter'];
        $session->set('report_saved',true);
        $session->set('report_saved_data',['id' => $rid, 'name' => getReportNameById($rid)]);
        $session->save();

        schedule_report();
    } else {
        Util::jump_to('index.php?modname=report&op=schedulelist'); //if error jump to start page
    }
}

function schedule_report()
{
    $idrep = FormaLms\lib\Get::req('idrep', DOTY_INT, false);
    $step = FormaLms\lib\Get::req('next_step', DOTY_STRING, '');
    checkReport($idrep);
    if ($step == 'sched_setrecipients') {
        schedule_recipients($idrep);
    } else {
        schedule_set($idrep, null);
    }
}

define('_SCHED_KEY_NAME', 'name');
define('_SCHED_KEY_CREATOR', 'creator');
define('_SCHED_KEY_CREATION', 'creation');
define('_SCHED_KEY_REPORT', 'report');
define('_SCHED_KEY_PERIOD', 'period');
define('_SCHED_KEY_NUMUSER', 'numuser');
define('_SCHED_KEY_ENABLED', 'enabled');
define('_SCHED_KEY_MOD', '_modify');
define('_SCHED_KEY_REM', '_remove');

function get_period_text($period, $time)
{
    $output = '';

    $lang = &DoceboLanguage::createInstance('report', 'framework');
    $texts = [
        'day' => $lang->def('_REPORT_DAILY'),
        'now' => $lang->def('_REPORT_NOW'),
        'week' => $lang->def('_SCHED_TEXT_WEEK'),
        'month' => $lang->def('_REPORT_MONTHLY'),
    ];

    $lang_days = &DoceboLanguage::createInstance('calendar', 'lms');
    $week_days = [
        '0' => $lang_days->def('_SUNDAY'),
        '1' => $lang_days->def('_MONDAY'),
        '2' => $lang_days->def('_TUESDAY'),
        '3' => $lang_days->def('_WEDNESDAY'),
        '4' => $lang_days->def('_THURSDAY'),
        '5' => $lang_days->def('_FRIDAY'),
        '6' => $lang_days->def('_SATURDAY'),
    ];

    $parts = explode(',', $period);

    $output .= $texts[$parts[0]];

    if ($parts[0] == 'week') {
        $output .= ' ' . strtolower($week_days[$parts[1]]) . ', ' . $lang->def('_AT_HOUR') . ' ' . $time;
    }
    if ($parts[0] == 'month') {
        $output .= ' ' . $parts[1] . ', ' . $lang->def('_AT_HOUR') . ' ' . $time;
    }
    if ($parts[0] == 'day') {
        $output .= ', ' . $lang->def('_AT_HOUR') . ' ' . $time;
    }

    return $output;
}

//create box for operations on schedulations
function get_schedulations_table($idrep = false)
{
    checkPerm('view');
    $can_mod = checkPerm('mod', true);

    require_once _base_ . '/lib/lib.table.php';
    Util::get_js(FormaLms\lib\Get::rel_path('base') . '/widget/dialog/dialog.js', true, true);
    YuiLib::load('selector');

    $acl_man = &Docebo::user()->getACLManager();
    $level = Docebo::user()->getUserLevelId(getLogUserId());

    $admin_cond = '';
    switch ($level) {
        case ADMIN_GROUP_GODADMIN:;
        // no break
        case ADMIN_GROUP_ADMIN: break;
        case ADMIN_GROUP_USER:;
        // no break
        default: $admin_cond .= ' AND schedule.id_creator=' . getLogUserId(); break;
    }

    $query = 'SELECT schedule.*, user.userid as report_owner, report_filter.filter_name as report_name, ' .
        'COUNT(recipients.id_user) as num_users FROM ' .
        $GLOBALS['prefix_lms'] . '_report_schedule as schedule, ' .
        $GLOBALS['prefix_lms'] . '_report_filter as report_filter, ' . $GLOBALS['prefix_fw'] . '_user as user, ' .
        $GLOBALS['prefix_lms'] . '_report_schedule_recipient as recipients ' .
        'WHERE schedule.id_report_filter=report_filter.id_filter AND user.idst=schedule.id_creator ' .
        'AND recipients.id_report_schedule=schedule.id_report_schedule ' . $admin_cond . ' ' .
        ($idrep ? "AND schedule.id_report_filter=$idrep " : '') .
        'GROUP BY schedule.id_report_schedule';

    $lang = &DoceboLanguage::createInstance('report', 'framework');
    $output = '';

    $tb = new Table(FormaLms\lib\Get::sett('visu_course'));
    $tb->initNavBar('ini', 'button');
    $col_type = ['align_center', 'align_center', 'align_center', 'align_center', 'align_center', 'align_center']; //,'image','image');
    $col_content = [
        $lang->def('_NAME'),
        $lang->def('_TAB_REP_CREATOR'),
        $lang->def('_CREATION_DATE'),
        $lang->def('_SEND'),
        $lang->def('_RECIPIENTS'),
        $lang->def('_ACTIVE'), /*,
        '<img src="'.getPathImage().'standard/edit.png" alt="'.$lang->def('_ALT_SCHED_MOD', 'standard').'" title="'.$lang->def('_MOD').'" />',
        '<img src="'.getPathImage().'standard/delete.png" alt="'.$lang->def('_ALT_SCHED_DEL', 'standard').'" title="'.$lang->def('_DEL').'" />'*/
    ];

    if ($can_mod) {
        $col_type[] = 'image';
        $col_type[] = 'image';
        $col_content[] = '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD', 'standard') . '" title="' . $lang->def('_MOD') . '" />';
        $col_content[] = '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL', 'standard') . '" title="' . $lang->def('_DEL') . '" />';
    }

    $tb->setColsStyle($col_type);
    $tb->addHead($col_content);

    $res = sql_query($query);
    if ($res) {
        while ($row = sql_fetch_assoc($res)) {
            $id = $row['id_report_schedule'];
            $recipients_link = 'ajax.adm_server.php?mn=report&amp;plf=lms&amp;op=show_recipients_window&amp;idsched=' . $id;

            $mod_link =
                '<a href="index.php?modname=report&amp;op=sched_mod&amp;id_sched=' . $id . '&amp;idrep=' . $idrep . '" ' .
                ' title="' . $lang->def('_MOD') . '">' .
                '<img src="' . getPathImage() . 'standard/edit.png" alt="' . $lang->def('_MOD') . '" />' .
                '</a>';
            $rem_link =
                '<a href="index.php?modname=report&amp;op=schedulelist&amp;idrep=' . $idrep . '&amp;action=sched_rem&amp;id_sched=' . $id . '" ' .
                ' title="' . $lang->def('_DEL') . '">' .
                '<img src="' . getPathImage() . 'standard/delete.png" alt="' . $lang->def('_DEL') . '" />' .
                '</a>';
            $enabled =
                /*'<input type="checkbox" value="'.$id.'" '.
                ($row['enabled']==1 ? 'checked="checked "' : '').
                'onchange="enable_schedulation(this);" />';*/
                '<image class="handover" src="' . getPathImage('lms') . 'standard/' .
                ($row['enabled'] == 1 ? 'publish.png' : 'unpublish.png') . '" ' .
                'onclick="enable_schedulation(this, ' . $row['id_report_schedule'] . ');" />' .
                '<input type="hidden" id="enable_value_' . $row['id_report_schedule'] . '" ' .
                'value="' . ($row['enabled'] == 1 ? '0' : '1') . '" />';
            $num_users = '<a href="' . $recipients_link . '" title="' . $lang->def('_RECIPIENTS') . '" ' .
                'class="" id="show_recipients_' . $id . '">' .
                $row['num_users'] . '</a>';
            $tb_content = [
                _SCHED_KEY_NAME => $row['name'],
                _SCHED_KEY_CREATOR => $acl_man->relativeId($row['report_owner']),
                _SCHED_KEY_CREATION => Format::date($row['creation_date']),
                _SCHED_KEY_PERIOD => get_period_text($row['period'], $row['time']),
                _SCHED_KEY_NUMUSER => $num_users,
                _SCHED_KEY_ENABLED => $enabled,
            ];

            if ($can_mod) {
                $tb_content[_SCHED_KEY_MOD] = $mod_link;
                $tb_content[_SCHED_KEY_REM] = $rem_link;
            }

            $tb->addBody($tb_content);
        }
    }

    $tb->addActionAdd('
		<a href="index.php?modname=report&amp;op=report_schedule&amp;idrep=' . $idrep . '">' .
    '<img src="' . getPathImage() . 'standard/add.png" ' .
        'title="' . $lang->def('_ADD') . '" /> ' .
    $lang->def('_ADD') . '</a>');

    $output .= $tb->getTable();

    $output .= '<script type="text/javascript">
		YAHOO.util.Event.onDOMReady(function() {
			var links = YAHOO.util.Selector.query("a[id^=show_recipients_]");
			YAHOO.util.Event.addListener(links, "click", function(e) {
				CreateDialog("show_recipients_dialog", {
					width: "600px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: false,
					constraintoviewport: false,
					draggable: true,
					hideaftersubmit: true,
					isDynamic: true,
					confirmOnly: true,
					ajaxUrl: this.href
				}).call(this, e);
			});
		});
	</script>';

    return $output;
}
