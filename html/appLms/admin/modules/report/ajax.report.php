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

if (Docebo::user()->isAnonymous()) {
    exit('You can\'t access');
}

function _encode(&$data)
{
    return serialize($data);
} //{ return urlencode(Util::serialize($data)); }
function _decode(&$data)
{
    return unserialize($data);
} //{ return Util::unserialize(urldecode($data)); }

$rep_cat = FormaLms\lib\Get::req('rep_cat', DOTY_ALPHANUM, false);

switch ($rep_cat) {
    case 'competences':
        //include('ajax.report_competences.php');
     break;

    default:
$op = FormaLms\lib\Get::req('op', DOTY_ALPHANUM, '');
switch ($op) {
    case 'save_filter_window':
        require_once _base_ . '/lib/lib.form.php';
        $lang = &DoceboLanguage::createInstance('report', 'framework');

        $output = [];
        $output['title'] = $lang->def('_SAVE_REPORT_TITLE');

        $output['content'] = //'nome filtro:<input type="text" name="filter_name" value="" />';
            Form::getTextfield(
                'Nome del filtro: ', //$label_name,
                'filter_name', //$id,
                'filter_name', //$name,
                '200', '') . Form::getHidden('filter_op', 'op', 'save_filter');

        $output['button_ok'] = $lang->def('_SAVE');
        $output['button_undo'] = $lang->def('_UNDO');

        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'show_recipients_window':
        require_once _lms_ . '/lib/lib.report.php';
        $lang = &DoceboLanguage::createInstance('report', 'framework');
        $output = [
            'success' => true,
            'header' => Lang::t('_RECIPIENTS', 'standard'),
            'body' => '',
        ];

        $id_sched = FormaLms\lib\Get::req('idsched', DOTY_INT, false);

        if ($id_sched > 0) {
            $tables = [];
            $records = get_schedule_recipients($id_sched, true);

            foreach ($records as $type => $list) {
                switch ($type) {
                    case 'users':
                        if (!empty($list)) {
                            $tb = new Table();
                            $tb->addHead([Lang::t('_USERNAME', 'standard'), Lang::t('_FULLNAME', 'standard')], ['', '']);
                            foreach ($list as $key => $value) {
                                $tb->addBody([
                                    Docebo::aclm()->relativeId($value->name),
                                    trim($value->value1 . ' ' . $value->value2),
                                ]);
                            }
                            $tables[] = $tb->getTable();
                            unset($tb);
                        }
                    break;

                    case 'groups':
                        if (!empty($list)) {
                            $tb = new Table();
                            $tb->addHead([Lang::t('_GROUPUSER_groupid', 'organization_chart')], ['']);
                            foreach ($list as $key => $value) {
                                $tb->addBody([
                                    Docebo::aclm()->relativeId($value->name),
                                ]);
                            }
                            $tables[] = $tb->getTable();
                            unset($tb);
                        }
                    break;

                    case 'folders':
                        if (!empty($list)) {
                            $tb = new Table();
                            $tb->addHead([Lang::t('_ORGFOLDERNAME', 'storage')], ['']);
                            foreach ($list as $key => $value) {
                                $is_descendants = strpos($obj->value1, 'ocd') !== false;

                                $tb->addBody([
                                    $value->name . ($is_descendants ? ' (+ ' . Lang::t('_INHERIT', 'standard') . ')' : ''),
                                ]);
                            }
                            $tables[] = $tb->getTable();
                            unset($tb);
                        }
                    break;

                    case 'fncroles':
                        if (!empty($list)) {
                            $tb = new Table();
                            $tb->addHead([Lang::t('_FNCROLE', 'fncroles')], ['']);
                            foreach ($list as $key => $value) {
                                $tb->addBody([
                                    $value->name,
                                ]);
                            }
                            $tables[] = $tb->getTable();
                            unset($tb);
                        }
                    break;
                }
            }
        }

        if (!empty($tables)) {
            $output['body'] = implode('<br />', $tables);
        } else {
            $output['body'] = Lang::t('_NO_VALUE', 'user_managment');
        }

        //$output['button_close'] = $lang->def('_CLOSE');
        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'save_filter':
        $output = [];
        $filter_data = FormaLms\lib\Get::req('filter_data', DOTY_ALPHANUM, ''); //warning: check urlencode-serialize etc.
        $data = urldecode($filter_data); //put serialized data in DB

        $name = FormaLms\lib\Get::req('filter_name', DOTY_ALPHANUM, '');

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $report = $session->get('report');
        $query = 'INSERT INTO %lms_report_filter ' .
            '(id_report, author, creation_date, filter_data, filter_name) VALUES ' .
            '(' . $report['id_report'] . ', ' . Docebo::user()->getIdst() . ', NOW(), ' .
            " '" . addslashes(serialize($report)) . "', '$name')";

        if (!$output['success'] = sql_query($query)) {
            $output['error'] = sql_error();
        } else {
            //if query is ok, I got the inserted ID and I put in session, telling the system I'm using it
            $row = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            $session->set('report_saved', $row[0]);
            $session->save();
        }
        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'delete_filter':
        $output = [];
        $filter_id = FormaLms\lib\Get::req('filter_id', DOTY_ALPHANUM, '');
        if (sql_query("DELETE FROM %lms_report_filter WHERE id_filter=$filter_id")) {
            $output['success'] = true;
        } else {
            $output['success'] = false;
        }
        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'sched_enable':
        $output = [];
        $success = false;
        $message = '';
        $id_sched = FormaLms\lib\Get::req('id', DOTY_INT, false);
        $value = FormaLms\lib\Get::req('val', DOTY_INT, -1);
        if ($value >= 0 && $id_sched !== false) {
            $query = "UPDATE %lms_report_schedule SET enabled=$value " .
                "WHERE id_report_schedule=$id_sched";
            $success = sql_query($query);
        }
        $output['success'] = $success;
        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'public_rep':
        $output = [];
        $success = false;
        $message = '';
        $id_rep = FormaLms\lib\Get::req('id', DOTY_INT, false);
        $value = FormaLms\lib\Get::req('val', DOTY_INT, -1);
        if ($value >= 0 && $id_rep !== false) {
            $query = "UPDATE %lms_report_filter SET is_public=$value " .
                "WHERE id_filter=$id_rep";
            $success = sql_query($query);
        }
                $output['success'] = $success;
        $json = new Services_JSON();
        aout($json->encode($output));
     break;
    default:
        break;
}

 break;
}
