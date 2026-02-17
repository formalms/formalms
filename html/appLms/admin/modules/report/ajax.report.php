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
    exit('You can\'t access');
}

/**
 * Encode report filter data to JSON format
 *
 * @param array $data The data to encode
 * @return string JSON encoded string
 *
 * @since 4.2.0 Changed from serialize() to json_encode() for better UTF-8 support and interoperability
 */
function _encode(&$data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

/**
 * Decode report filter data from JSON or serialized format (legacy support)
 *
 * @param string $jsonData JSON data (preferred)
 * @param string|null $serializedData Legacy serialized data (fallback, deprecated)
 * @return array|null Decoded data or null on failure
 *
 * @since 4.2.0 Added JSON support as primary format
 * @internal The $serializedData parameter is deprecated since 4.2.0 and will be removed in 5.0.0
 */
function _decode(&$jsonData, $serializedData = null)
{
    // If JSON data is available and valid, use it
    if (!empty($jsonData)) {
        $result = json_decode($jsonData, true);
        if ($result !== null || json_last_error() === JSON_ERROR_NONE) {
            return $result;
        }
    }

    // @deprecated Since 4.2.0 - Fallback to serialized data (legacy support), will be removed in 5.0.0
    if (!empty($serializedData)) {
        @trigger_error(
            'Reading report filter from serialized format (filter_data column) is deprecated since 4.2.0. ' .
            'Data will be auto-migrated to JSON format (filter_data_json column).',
            E_USER_DEPRECATED
        );
        $result = @unserialize($serializedData);
        if ($result !== false) {
            return $result;
        }
        // Attempt to repair corrupted byte counts (e.g. caused by UTF-8 encoding changes)
        $result = _unserialize_repair($serializedData);
        if ($result !== false && $result !== null) {
            return $result;
        }
    }

    // @deprecated Since 4.2.0 - Auto-detect serialized PHP in jsonData param (legacy support)
    if (!empty($jsonData) && preg_match('/^[aOsbidn]:/', $jsonData)) {
        @trigger_error(
            'Detected serialized PHP data in JSON parameter. This legacy format is deprecated since 4.2.0.',
            E_USER_DEPRECATED
        );
        $result = @unserialize($jsonData);
        if ($result !== false) {
            return $result;
        }
        // Attempt to repair corrupted byte counts
        $result = _unserialize_repair($jsonData);
        if ($result !== false && $result !== null) {
            return $result;
        }
    }

    return null;
}

/**
 * Attempt to repair and unserialize a corrupted PHP serialized string.
 * Fixes byte count mismatches caused by multibyte character encoding changes (e.g. Latin-1 to UTF-8).
 *
 * @param string $data The corrupted serialized string
 * @return mixed The unserialized data, or false on failure
 */
function _unserialize_repair($data)
{
    $repaired = preg_replace_callback(
        '/s:(\d+):"(.*?)";/s',
        function ($matches) {
            return 's:' . strlen($matches[2]) . ':"' . $matches[2] . '";';
        },
        $data
    );

    if ($repaired === null || $repaired === $data) {
        return false;
    }

    return @unserialize($repaired);
}

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
        $lang = &FormaLanguage::createInstance('report', 'framework');

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

        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
     break;

    case 'show_recipients_window':
        require_once _lms_ . '/lib/lib.report.php';
        $lang = &FormaLanguage::createInstance('report', 'framework');
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
                                    \FormaLms\lib\Forma::getAclManager()->relativeId($value->name),
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
                                    \FormaLms\lib\Forma::getAclManager()->relativeId($value->name),
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

        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
     break;

    case 'save_filter':
        $output = [];
        $filter_data = FormaLms\lib\Get::req('filter_data', DOTY_ALPHANUM, ''); //warning: check urlencode-serialize etc.
        $data = urldecode($filter_data); //put serialized data in DB

        $name = FormaLms\lib\Get::req('filter_name', DOTY_ALPHANUM, '');

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $report = $session->get('report');

        // @since 4.2.0 - Primary storage: JSON format in filter_data_json column
        $dataJson = addslashes(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        // @deprecated Since 4.2.0 - Legacy column for backward compatibility, will be removed in 5.0.0
        $dataSerialized = addslashes(serialize($report));

        $query = 'INSERT INTO %lms_report_filter ' .
            '(id_report, author, creation_date, filter_data, filter_data_json, filter_name) VALUES ' .
            '(' . $report['id_report'] . ', ' . \FormaLms\lib\FormaUser::getCurrentUser()->getIdst() . ', NOW(), ' .
            " '$dataSerialized', '$dataJson', '$name')";

        if (!$output['success'] = sql_query($query)) {
            $output['error'] = sql_error();
        } else {
            //if query is ok, I got the inserted ID and I put in session, telling the system I'm using it
            $row = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
            $session->set('report_saved', $row[0]);
            $session->save();
        }
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
     break;

    case 'delete_filter':
        $output = [];
        $filter_id = FormaLms\lib\Get::req('filter_id', DOTY_ALPHANUM, '');
        if (sql_query("DELETE FROM %lms_report_filter WHERE id_filter=$filter_id")) {
            $output['success'] = true;
        } else {
            $output['success'] = false;
        }
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
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
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
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
        aout(\FormaLms\lib\Serializer\FormaSerializer::getInstance()->encode($output,\FormaLms\lib\Serializer\FormaSerializer::FORMAT_JSON));
     break;
    default:
        break;
}

 break;
}
