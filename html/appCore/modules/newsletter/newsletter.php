<?php

use FormaLms\lib\Domain\DomainHandler;

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

define('_ANY_LANG_CODE', '-any-');

function newsletter()
{
    //access control
    //-TP// funAdminAccess('OP');
    checkPerm('view');
    $linkAdd = '';
    $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
    $course = null;
    $idMainMenu = $request->query->has('id_main_sel') ? (int) $request->query->get('id_main_sel') : 0;

    if($idMainMenu) {
        $courseService = new \FormaLms\lib\Services\Courses\CourseService();

        $course = $courseService->getCourseFromMenu($idMainMenu);
    }
    
    
    require_once _base_ . '/lib/lib.form.php';

    $out = $GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = FormaLanguage::createInstance('admin_newsletter', 'framework');

    YuiLib::load();
    addJs($GLOBALS['where_framework_relative'] . '/modules/newsletter/', 'newsletter.js');

    $form = new Form();

    $out->add(getTitleArea($lang->def('_NEWSLETTER'), 'newsletter'));

    $p_size = intval(ini_get('post_max_size'));
    $u_size = intval(ini_get('upload_max_filesize'));
    $max_kb = ($p_size < $u_size ? $p_size : $u_size);
    $max = ' (Max. ' . $max_kb . ' Mb) ';

    $out->add('<script>'
        . 'var _DEL=\'' . $lang->def('_DEL') . '\';'
        . 'var _FILE_TO_SEND=\'' . $lang->def('_ATTACHMENT') . '\';'
        . 'var _MAX=\'' . $max . '\';'
        . '</script>');

    $out->add("<div class=\"std_block\">\n");

    $acl_manager = \FormaLms\lib\Forma::getAclManager();
    $user_info = $acl_manager->getUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), false);
    $myemail = $user_info[ACL_INFO_EMAIL];

    if ((isset($err)) && ($err != '')) {
        $out->add("<b><span class=\"fontRed\">$err</span><br />\n");
    }

    if(is_array($course)) {
        $linkAdd = '&amp;id_course='.$course['idCourse'];
    }

    $out->add($form->openForm('newsletter_form', 'index.php?modname=newsletter&amp;op=initsend'.$linkAdd, false, false, 'multipart/form-data'));
    $out->add($form->openElementSpace());

    $out->add($form->getTextfield($lang->def('_SENDER'), 'fromemail', 'fromemail', 255, $myemail));
    $out->add($form->getTextfield($lang->def('_SUBJECT'), 'sub', 'sub', 255, ''));
    $out->add($form->getTextarea($lang->def('_DESCRIPTION'), 'msg', 'msg', ''));

    $lang_list = \FormaLms\lib\Forma::langManager()->getAllLangCode();
    //array_unshift($lang_list, $lang->def("_DEFAULT"), $lang->def("_ALL"));
    $lang_list = [_ANY_LANG_CODE => $lang->def('_ALL')] + $lang_list;

    $out->add('<div id="file">'
        . $form->getHidden('file_number', 'file_number', '1')
        . '<div id="div_file_1">'
        . $form->getFilefield($lang->def('_ATTACHMENT'), 'file_1', 'file_1', '', '', '<a href="#" onclick="delFile(\'1\'); return false;"><span id="rem_span">' . $lang->def('_DEL') . '</span><a>')
        . '</div>'
        . '</div>'
        . '<br/><a href="#" onclick="addFile(); return false;"><span id="add_span">' . $lang->def('_MORE_ATTACHMENT') . '</span></a>');

    $out->add($form->getDropdown($lang->def('_LANGUAGE'), 'sel_lang', 'sel_lang', $lang_list));

    $out->add($form->getRadio($lang->def('_EMAIL'), 'send_type_email', 'send_type', 'email', true));
    $out->add($form->getRadio($lang->def('_SEND_SMS'), 'send_type_sms', 'send_type', 'sms', false));

    $out->add($form->closeElementSpace());
    $out->add($form->openButtonSpace());
    $out->add($form->getButton('send', 'send', $lang->def('_SEND')));
    $out->add($form->closeButtonSpace());
    $out->add($form->closeForm());

    $out->add("</div>\n");
}

function send_newsletter($send_id)
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.json.php';

    $json = new Services_JSON();

    $path = '/appCore/newsletter/';

    //access control
    $nl_sendpercycle = FormaLms\lib\Get::sett('nl_sendpercycle', 1);
    //-TP// funAdminAccess('OP');

    //@set_time_limit(60*15); // 15 minutes!

    $out = $GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = FormaLanguage::createInstance('admin_newsletter', 'framework');

    $out->add(getTitleArea($lang->def('_NEWSLETTER'), 'newsletter'));

    $out->add("<div class=\"std_block\">\n");

    $info = get_send_info($send_id);

    $sel_groups = $info['sel_groups'];
    $sel_lang = $info['sel_lang'];
    $tot = $info['tot'];

    $sub = $info['sub'];
    $msg = $info['msg'];

    $msg = str_replace('{site_base_url}', getSiteBaseUrl(), $msg);

    $fromemail = $info['fromemail'];
    $sender = DomainHandler::getInstance()->getMailerField('sender_mail_system');
    $file_array = $json->decode($info['file']);

    $attach = [];

    foreach ($file_array as $file) {
        $attach[] = _files_ . $path . $file;
    }

    $cycle = FormaLms\lib\Get::gReq('cycle', DOTY_INT, 0);

    // Items per cycle
    $ipc = $nl_sendpercycle;

    if (($cycle + 1) * $ipc < $tot) {
        $sendcomplete = 0;
    } else {
        $sendcomplete = 1;
    }

    $limit = $cycle * $ipc . ', ' . $ipc;
    $arr_st = getSendToIdst($send_id, $limit);
    $acl_manager = \FormaLms\lib\Forma::getAclManager();
    if ((!empty($sel_lang)) && ($sel_lang != _ANY_LANG_CODE)) {
        $user_info = $acl_manager->getUsersByLanguage($sel_lang, $arr_st);
    } else { // Send to all languages
        $user_info = $acl_manager->getUsers($arr_st);
    }

    $send_type = $info['send_type'];

    switch ($send_type) {
        case 'email':
                $tempemail = [];
                foreach ($user_info as $info) {
                    // Send the email: ------------------------------
                    $email = $info[ACL_INFO_EMAIL];

                    if ($email != '') {
                        $tempemail[] = $email;
                    }
                    // ----------------------------------------------
                }

                $mailer = FormaLms\lib\Mailer\FormaMailer::getInstance();

                $mailer->SendMail($tempemail, $sub, $msg, $sender, $attach,
                    [MAIL_REPLYTO => $fromemail, MAIL_SENDER_ACLNAME => false]);

            break;

        case 'sms':
                // Collect users sms numbers

                require_once _adm_ . '/lib/lib.field.php';

                $acl_man = \FormaLms\lib\Forma::getAclManager();;
                $field_man = new FieldList();

                $arr_sms_recipients = [];
                $send_to_field = FormaLms\lib\Get::sett('sms_cell_num_field');
                $users_sms = $field_man->showFieldForUserArr($arr_st, [$send_to_field]);
                $users_info = $acl_man->getUsers($arr_st);
                foreach ($users_info as $user_dett) {
                    // recover media setting
                    $idst_user = $user_dett[ACL_INFO_IDST];

                    if ($users_sms[$idst_user][$send_to_field] != '') {
                        $arr_sms_recipients[$idst_user] = $users_sms[$idst_user][$send_to_field];
                    }
                }

                require_once _adm_ . '/lib/lib.sms.php';
                $sms_manager = new SmsManager();
                $sms_manager->sendSms($msg, $arr_sms_recipients);

            break;
    }

    if ($sendcomplete) {
        require_once _base_ . '/lib/lib.upload.php';
        if (count($attach)) {
            foreach ($attach as $file) {
                sl_open_fileoperations();

                sl_unlink(str_replace(_files_, '', $file));

                sl_close_fileoperations();
            }
        }

        $url = 'index.php?modname=newsletter&op=complete';
        Util::jump_to($url);
    } else {
        $url = 'index.php?modname=newsletter&op=pause&ipc=' . $ipc . '&cycle=' . ($cycle + 1) . '&id_send=' . $send_id;
        Util::jump_to($url);
    }

    $out->add("</div><br />\n");

    $out->add("<form action=\"index.php?modname=newsletter&amp;op=newsletter\" method=\"post\">\n");
    $out->add("<div class=\"std_block\">\n"
        . '<input type="hidden" id="authentic_request_newsletter" name="authentic_request" value="' . Util::getSignature() . '" />');
    $out->add('<input class="button" type="submit" value="' . $lang->def('_BACK') . "\" />\n");
    $out->add("</div>\n");
    $out->add("</form>\n");
}

function getSendToIdst($id_send, $limit)
{
    checkPerm('view');

    $res = [];

    $qtxt = 'SELECT idst FROM ' . $GLOBALS['prefix_fw'] . "_newsletter_sendto WHERE id_send='" . (int) $id_send . "' LIMIT " . $limit;
    $q = sql_query($qtxt);

    if (($q) && (sql_num_rows($q) > 0)) {
        while ($row = sql_fetch_array($q)) {
            $res[] = $row['idst'];
        }
    }

    return $res;
}

function nl_pause()
{
    checkPerm('view');

    $delay = FormaLms\lib\Get::sett('nl_sendpause', 20);

    $out = &$GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = &FormaLanguage::createInstance('admin_newsletter', 'framework');

    $out->add(getTitleArea($lang->def('_NEWSLETTER'), 'newsletter'));

    $out->add("<div class=\"std_block\">\n");

    $cycle = (int) $_GET['cycle'];
    $ipc = (int) $_GET['ipc'];
    $id_send = (int) $_GET['id_send'];

    $out->add('<br />' . $lang->def('_SEND') . ': ' . ($cycle * $ipc) . ' - ' . ($cycle * $ipc + $ipc) . "<br />\n");

    $out->add('<br /><br />...' . $delay . ' ' . $lang->def('_SEC_OF_PAUSE') . "...\n");
    //Non chiudere la pagina finch&eacute; non compare la scritta \"Operazione completata\"
    $out->add('<br />' . $lang->def('_LOADING') . "<br /><br />\n");

    $out->add("</div>\n");

    $url = 'index.php?modname=newsletter&amp;op=send&amp;cycle=' . $cycle . '&amp;id_send=' . $id_send;
    $out->add('<meta http-equiv="refresh" content="' . $delay . ';url=' . $url . "\">\n", 'page_head');
}

function nl_sendcomplete()
{
    checkPerm('view');

    //-TP// funAdminAccess('OP');

    $out = $GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = FormaLanguage::createInstance('admin_newsletter', 'framework');

    $out->add(getTitleArea($lang->def('_NEWSLETTER'), 'newsletter'));

    $out->add("<div class=\"std_block\">\n");

    $out->add('<br /><b>' . $lang->def('_OPERATION_SUCCESSFUL') . "</b><br /><br />\n");

    $out->add("</div><br />\n");

    $out->add("<form action=\"index.php?modname=newsletter&amp;op=newsletter\" method=\"post\">\n");
    $out->add("<div class=\"std_block\">\n"
        . '<input type="hidden" id="authentic_request_newsletter" name="authentic_request" value="' . Util::getSignature() . '" />');
    $out->add('<input class="button" type="submit" value="' . $lang->def('_BACK') . "\" />\n");
    $out->add("</div>\n");
    $out->add("</form>\n");
}

function init_send()
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.upload.php';
    require_once _base_ . '/lib/lib.json.php';
    $request = \FormaLms\lib\Request\RequestManager::getInstance()->getRequest();
    $instance = 'newsletter';
    $json = new Services_JSON();

    $savefile = '';
    $max_file = FormaLms\lib\Get::req('file_number', DOTY_INT, 0);

    $savefile = [];
    for ($i = 1; $i <= $max_file; ++$i) {
        if (isset($_FILES['file_' . $i]) && $_FILES['file_' . $i]['error'] == 0) {
            //$savefile = rand(0,100).'_'.time().'_'.$_FILES['file']['name'];
            $savefile[] = $_FILES['file_' . $i]['name'];

            $path = '/appCore/newsletter/';

            sl_open_fileoperations();

            sl_upload($_FILES['file_' . $i]['tmp_name'], $path . $_FILES['file_' . $i]['name']);

            sl_close_fileoperations();
        }
    }

    $lang_list = \FormaLms\lib\Forma::langManager()->getAllLangCode();

    $sel_lang = importVar('sel_lang');
    if ($sel_lang > 0) {
        $lang_selected = $lang_list[$sel_lang];
    } elseif ($sel_lang === 0) { // Default language
        $lang_selected = Lang::get();
    } else {
        $lang_selected = $sel_lang;
    }

    $translate_table = getTranslateTable();

    $sub = translateChr($_POST['sub'], $translate_table, true);
    $msg = translateChr($_POST['msg'], $translate_table, true);
    $fromemail = $_POST['fromemail'];

    $send_type = $_POST['send_type'];

    // ..who said spring cleanings have to be done in spring??
    $qtxt = 'DELETE FROM %adm_newsletter WHERE stime < (DATE_SUB(NOW(), INTERVAL 1 DAY))';
    $q = sql_query($qtxt);

    $qtxt = 'DELETE FROM %adm_newsletter_sendto WHERE stime < (DATE_SUB(NOW(), INTERVAL 1 DAY))';
    $q = sql_query($qtxt);

    $qtxt = 'INSERT INTO %adm_newsletter (sub, msg, fromemail, language, send_type, stime, file) ';
    $qtxt .= "VALUES ('" . $sub . "', '" . $msg . "', '" . $fromemail . "', '" . $lang_selected . "', '" . $send_type . "', NOW(), '" . str_replace("'", "\'", $json->encode($savefile)) . "')";
    $q = sql_query($qtxt); //echo sql_error();

    $qtxt = 'SELECT LAST_INSERT_ID() as last_id FROM %adm_newsletter';
    $q = sql_query($qtxt);

    $row = sql_fetch_array($q);
    $last_id = $row['last_id'];

    $qtxt = 'UPDATE %adm_newsletter SET id_send="' . $last_id . '" WHERE id="' . $last_id . '"';
    $q = sql_query($qtxt);

    $idCourse = $request->query->has('id_course') ? (int) $request->query->get('id_course') : 0;


    if($idCourse) {
        $instance = 'newslettercourse';
    }

    //$url = 'index.php?modname=newsletter&amp;op=selsendto&amp;id_send=' . $last_id . '&load=1';
    $url = 'index.php?r=adm/userselector/show&amp;instance='.  $instance . '&amp;id=' . $last_id . '&load=1';
    Util::jump_to($url);
}

function get_send_info($send_id)
{
    $sel_lang = '';
    $send_type = 'email';
    $sel_groups = [];
    $res = [];

    $qtxt = 'SELECT * FROM ' . $GLOBALS['prefix_fw'] . "_newsletter WHERE id='" . $send_id . "'";
    $q = sql_query($qtxt); //echo $qtxt;

    if (($q) && (sql_num_rows($q) > 0)) {
        while ($row = sql_fetch_assoc($q)) {
            if ($sel_lang == '') {
                $sel_lang = $row['language'];
            }

            $tot = (int) $row['tot'];
            $sub = $row['sub'];
            $msg = $row['msg'];
            $fromemail = $row['fromemail'];
            if ($row['send_type'] != '') {
                $send_type = $row['send_type'];
            }
            $file = $row['file'];
        }
    }

    $res['sel_lang'] = $sel_lang;
    $res['sel_groups'] = $sel_groups;
    $res['tot'] = $tot;
    $res['sub'] = $sub;
    $res['msg'] = $msg;
    $res['fromemail'] = $fromemail;
    $res['send_type'] = $send_type;
    $res['file'] = $file;

    return $res;
}

function selSendTo()
{
    checkPerm('view');

    if ((isset($_GET['id_send'])) && ($_GET['id_send'] > 0)) {
        $id_send = $_GET['id_send'];
    } else {
        exit('Newsletter setup error.');
    }

    require_once _base_ . '/lib/lib.userselector.php';
    $mdir = new UserSelector();
    if (defined('IN_LMS')) {
        $mdir->learning_filter = 'course';
        $mdir->show_fncrole_selector = false;
    }

    if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
        require_once _base_ . '/lib/lib.preference.php';
        $adminManager = new AdminPreference();
        $admin_tree = $adminManager->getAdminTree(\FormaLms\lib\FormaUser::getCurrentUser()->getIdST());
        $admin_users = \FormaLms\lib\Forma::getAclManager()->getAllUsersFromSelection($admin_tree);

        $mdir->setUserFilter('user', $admin_users);
        $mdir->setUserFilter('group', $admin_tree);
    }

    $out = $GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = FormaLanguage::createInstance('admin_newsletter', 'framework');

    $back_url = 'index.php?modname=newsletter&amp;op=selsendto&amp;id_send=' . $id_send;

    if (isset($_POST['okselector'])) {
        $arr_selection = $mdir->getSelection($_POST);

    

        $newsletterService = new \FormaLms\lib\Services\Newsletters\NewsletterService(); 

        $newsletterService->setaccessList($id_send, $arr_selection);

    } elseif (isset($_POST['cancelselector'])) {
        $info = get_send_info($id_send);

        $file = $info['file'];

        $path = '/appCore/newsletter/';

        require_once _base_ . '/lib/lib.upload.php';
        if ($file != '') {
            sl_open_fileoperations();

            sl_unlink($path . $file);

            sl_close_fileoperations();
        }

        Util::jump_to('index.php?modname=newsletter&op=newsletter');
    } else {
        if (isset($_GET['load'])) {
            $mdir->resetSelection([]);
        }

        $url = 'index.php?modname=newsletter&amp;op=selsendto&amp;id_send=' . $id_send . '&amp;stayon=1';
        $mdir->show_user_selector = true;
        $mdir->show_group_selector = true;
        $mdir->show_orgchart_selector = true;
        $mdir->show_orgchart_simple_selector = false;

        $acl_manager = \FormaLms\lib\Forma::getAclManager();
        if (defined('IN_LMS')) {
            $id_course = (int) \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
            $arr_idstGroup = $acl_manager->getGroupsIdstFromBasePath('/lms/course/' . $id_course . '/subscribed/');
            $mdir->setUserFilter('group', $arr_idstGroup);
            $mdir->setGroupFilter('path', '/lms/course/' . $id_course . '/group');
            $mdir->show_orgchart_selector = false;
        }

        // Exclude anonymous user!
        $mdir->setUserFilter('exclude', [$acl_manager->getAnonymousId()]);

        $mdir->loadSelector($url,
            [Lang::t('_NEWSLETTER', 'admin_newsletter'), Lang::t('_RECIPIENTS', 'admin_newsletter')], '', true);
    }
}

function newsletterSummary($id_send)
{
    checkPerm('view');

    require_once _base_ . '/lib/lib.form.php';

    $out = $GLOBALS['page'];
    $out->setWorkingZone('content');
    $lang = FormaLanguage::createInstance('admin_newsletter', 'framework');

    $tot = (int) $_GET['tot'];
    $form = new Form();

    $out->add(getTitleArea($lang->def('_NEWSLETTER'), 'newsletter'));

    $out->add("<div class=\"std_block\">\n");

    $url = 'index.php?modname=newsletter&amp;op=send&amp;id_send=' . $id_send;

    $out->add($form->openForm('newsletter_form', $url));

    $txt = $lang->def('_NEWSLETTER_WILL_BE_SENT_TO');
    $txt = str_replace('[tot]', $tot, $txt);
    $out->add($form->getTextBox($txt));

    $out->add($form->openButtonSpace());
    $out->add($form->getButton('send', 'send', $lang->def('_SEND')));
    $out->add($form->closeButtonSpace());
    $out->add($form->closeForm());

    $out->add("</div>\n");
}

function add_to_array($arr, &$add_to)
{
    if (!is_array($add_to)) {
        $add_to = [];
    }

    if (!is_array($arr)) {
        return 0;
    }

    foreach ($arr as $key => $val) {
        if (!in_array($val, $add_to)) {
            $add_to[] = $val;
        }
    }
}

$op = importVar('op');
switch ($op) {
    case 'view':
    case 'newsletter':
            newsletter();

        break;

    case 'initsend':
            init_send();

        break;

    case 'selsendto':
            selSendTo();

        break;

    case 'summary':
            $id_send = (int) $_GET['id_send'];
            newsletterSummary($id_send);

        break;

    case 'send':
            $id_send = (int) $_GET['id_send'];
            send_newsletter($id_send);

        break;

    case 'pause':
            nl_pause();

        break;

    case 'complete':
        nl_sendcomplete();
}
