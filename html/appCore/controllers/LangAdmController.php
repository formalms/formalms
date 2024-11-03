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

class LangAdmController extends AdmController
{
    public const IMPORT_TYPE_CORE = 'core';
    public const IMPORT_TYPE_FILE = 'file';

    protected $json;
    /** @var LangAdm */
    protected $model;

    protected $perm = [];

    public function init()
    {
        parent::init();

        $this->json = new Services_JSON();
        $this->model = new LangAdm();
        $this->perm = [
            'view' => checkPerm('view', true, 'lang', 'framework'),
            'mod' => checkPerm('mod', true, 'lang', 'framework'),
        ];
    }

    public function showTask()
    {
        require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 100));
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $sort = 'lang_code';
        switch ($dir) {
            case 'desc':
                    $dir = 'desc';

                break;
            default:
                    $dir = 'asc';

                break;
        }

        $lang_list = $this->model->getLangList($startIndex, $results, $sort, $dir);
        $total = $this->model->getLangTotal();
        foreach ($lang_list as $i => $lang) {
            $lang->lang_translate = 'index.php?r=adm/lang/list&amp;lang_code=' . $lang->lang_code;
            $lang->lang_export = 'index.php?r=adm/lang/export&amp;lang_code=' . $lang->lang_code;
            $lang->lang_diff = 'index.php?r=adm/lang/diff&amp;lang_code=' . $lang->lang_code;
            $lang->lang_mod = 'ajax.adm_server.php?r=adm/lang/mod&amp;lang_code=' . $lang->lang_code;
            $lang->lang_del = 'ajax.adm_server.php?r=adm/lang/del&amp;lang_code=' . $lang->lang_code;
            $lang_list[$i] = $lang;
        }

        $this->render('show', ['langList' => array_values($lang_list)]);
    }

    public function getlang()
    {
        $sortable = ['lang_code', 'lang_description', 'lang_direction', 'lang_stats'];

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, '');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        if (!in_array($sort, $sortable)) {
            $sort = 'lang_code';
        }
        switch ($dir) {
            case 'desc':
                    $dir = 'desc';

                break;
            default:
                    $dir = 'asc';

                break;
        }

        $lang_list = $this->model->getLangList($startIndex, $results, $sort, $dir);
        $total = $this->model->getLangTotal();
        foreach ($lang_list as $i => $lang) {
            $lang->lang_translate = 'index.php?r=adm/lang/list&amp;lang_code=' . $lang->lang_code;
            $lang->lang_export = 'index.php?r=adm/lang/export&amp;lang_code=' . $lang->lang_code;
            $lang->lang_diff = 'index.php?r=adm/lang/diff&amp;lang_code=' . $lang->lang_code;
            $lang->lang_mod = 'ajax.adm_server.php?r=adm/lang/mod&amp;lang_code=' . $lang->lang_code;
            $lang->lang_del = 'ajax.adm_server.php?r=adm/lang/del&amp;lang_code=' . $lang->lang_code;
            $lang_list[$i] = $lang;
        }

        $output = [
            'totalRecords' => $total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => 25,
            'results' => count($lang_list),
            'records' => array_values($lang_list),
        ];
        echo $this->json->encode($output);
    }

    public function addmask()
    {
        $lang = new stdClass();
        $lang->lang_code = '';
        $lang->lang_description = '';
        $lang->lang_direction = 'ltr';
        $lang->lang_browsercode = '';

        $this->render('lang_form', ['lang' => $lang]);

        $params = [
            'success' => true,
            'header' => Lang::t('_ADD', 'standard'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function insertlang()
    {
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, '');
        $lang_description = FormaLms\lib\Get::req('lang_description', DOTY_STRING, '');
        $lang_direction = FormaLms\lib\Get::req('lang_direction', DOTY_STRING, 'ltr');
        $lang_browsercode = FormaLms\lib\Get::req('lang_browsercode', DOTY_STRING, '');

        if ($lang_code == '') {
            $result = ['success' => false, 'message' => Lang::t('_NO_TITLE', 'standard')];
            echo $this->json->encode($result);

            return;
        }
        $re = $this->model->newLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);

        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    public function updatelang()
    {
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, '');
        $lang_description = FormaLms\lib\Get::req('lang_description', DOTY_STRING, '');
        $lang_direction = FormaLms\lib\Get::req('lang_direction', DOTY_STRING, 'ltr');
        $lang_browsercode = FormaLms\lib\Get::req('lang_browsercode', DOTY_STRING, '');

        $answ = $this->model->updateLanguage($lang_code, $lang_description, $lang_direction, $lang_browsercode);

        $result = [
            'success' => $answ,
            'message' => ($answ ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    public function mod()
    {
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, '');
        $lang = $this->model->getLanguage($lang_code);

        $this->render('edit_form', ['lang' => $lang]);
        $params = [
            'success' => true,
            'header' => Lang::t('_MOD', 'standard'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function savemask()
    {
        $lang = new stdClass();
        $lang->lang_code = '';
        $lang->lang_description = '';
        $lang->lang_direction = 'ltr';
        $lang->lang_browsercode = '';

        $this->render('lang_form', ['lang' => $lang]);

        $params = [
            'success' => true,
            'header' => Lang::t('_ADD', 'standard'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function delTask()
    {
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, '');

        $re = false;
        if ($lang_code != '') {
            $re = $this->model->delLanguage($lang_code);
        }
        $result = [
            'success' => $re,
            'message' => ($re ? '' : Lang::t('_OPERATION_FAILED', 'standard')),
        ];
        echo $this->json->encode($result);
    }

    public function exportTask()
    {
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, '');
        $text_items = FormaLms\lib\Get::req('text_items', DOTY_MIXED, []);

        $this->model->exportTranslation($lang_code, $text_items);
    }

    public function importTask()
    {
        $error = FormaLms\lib\Get::req('error', DOTY_INT, 0);
        if ($error) {
            UIFeedback::error(Lang::t('_ERROR_UPLOAD', 'standard'));
        }

        $this->render('import_mask', [
            'coreLangs' => $this->getFileSystemCoreLanguages(),
            'importTypes' => [
                Lang::t('_IMPORT_FROM_CORE', 'standard') => self::IMPORT_TYPE_CORE,
                Lang::t('_IMPORT_FROM_FILE', 'standard') => self::IMPORT_TYPE_FILE,
            ],
            'defaultType' => self::IMPORT_TYPE_CORE,
        ]);
    }

    public function doimportTask()
    {
        $importType = FormaLms\lib\Get::req('import_type', DOTY_STRING, false);
        $undo = FormaLms\lib\Get::req('undo', DOTY_STRING, false);
        $overwrite = (bool) FormaLms\lib\Get::req('overwrite', DOTY_INT, 0);
        $noadd_miss = (bool) FormaLms\lib\Get::req('noadd_miss', DOTY_INT, 0);
        $langFile = FormaLms\lib\Get::req('lang_id', DOTY_STRING, '');

        if (!empty($undo)) {
            Util::jump_to('index.php?r=adm/lang/show');
        }

        switch ($importType) {
            case self::IMPORT_TYPE_CORE:
                if (empty($langFile)) {
                    Util::jump_to('index.php?r=adm/lang/import&error=1');
                }

                $filePath = _base_ . '/xml_language/' . $langFile;
                break;
            case self::IMPORT_TYPE_FILE:
                $filePath = $_FILES['lang_file']['tmp_name'];
                break;
            default:
                Util::jump_to('index.php?r=adm/lang/import&error=1');
                break;
        }

        if (!is_file($filePath)) {
            Util::jump_to('index.php?r=adm/lang/import&error=2');
        }

        $langCode = $this->model->getFileLangCode($filePath);

        if ($overwrite) {
            $lang_list = $this->model->getLangList(0, 100, '', 'asc');
            if (array_key_exists($langCode, $lang_list)) {
                require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');

                $langList = $this->model->getAllForDiff($filePath, $langCode);

                if (count($langList) > 0) {
                    $data = [
                        'body' => array_values($langList),
                        'langCode' => $langCode,
                        'import_type' => $importType,
                    ];

                    $this->render('diff', $data);

                    return;
                }
            }
        }

        $re = $this->model->importTranslation($filePath, $overwrite, $noadd_miss);

        Util::jump_to('index.php?r=adm/lang/show');
    }

    public function inline_editTask()
    {
        $id_text = FormaLms\lib\Get::req('id_text', DOTY_INT, 0);

        if ($id_text <= 0) {
            echo $this->json->encode(['success' => false]);

            return;
        }

        //Update info
        $newValue = FormaLms\lib\Get::req('new_value', DOTY_MIXED, '');
        $oldValue = FormaLms\lib\Get::req('old_value', DOTY_MIXED, '');
        $column = FormaLms\lib\Get::req('col', DOTY_STRING, '');
        $language = FormaLms\lib\Get::req('language', DOTY_STRING, Lang::get());

        if ($newValue === $oldValue) {
            echo $this->json->encode(['success' => true]);
        } else {
            switch ($column) {
                case 'translation_text':
                        $res = $this->model->saveTranslation($id_text, $language, $newValue);
                        $output = ['success' => $res ? true : false];
                        if ($res) {
                            $output['new_value'] = stripslashes($newValue);
                        }
                        echo $this->json->encode($output);

                    break;

                default:
                        echo $this->json->encode(['success' => false]);

                    break;
            }
        }
    }

    public function listTask()
    {
        // YuiLib::load('table');
        require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_STRING, Lang::get());

        $module_list = $this->model->getModuleList();
        array_unshift($module_list, Lang::t('_ALL'));

        $plugins_ids = $this->model->getPluginsList();
        $plugins_ids[0] = Lang::t('_NONE');
        ksort($plugins_ids);

        $language_list_diff = $language_list = $this->model->getLangCodeList();
        array_unshift($language_list_diff, Lang::t('_NONE'));

        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'text_module');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $la_module = FormaLms\lib\Get::req('la_module', DOTY_ALPHANUM, false);
        $la_text = FormaLms\lib\Get::req('la_text', DOTY_MIXED, false);
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_ALPHANUM, false);
        $lang_code_diff = FormaLms\lib\Get::req('lang_code_diff', DOTY_ALPHANUM, false);
        $only_empty = FormaLms\lib\Get::req('only_empty', DOTY_MIXED, 0);
        $plugin_id = FormaLms\lib\Get::req('plugin_id', DOTY_INT, false);
        if ($only_empty === 'true') {
            $only_empty = true;
        } else {
            $only_empty = false;
        }

        $lang_list = $this->model->getAll(false, false, $la_module, $la_text, $lang_code, $lang_code_diff, $only_empty, $sort, $dir, $plugin_id);

        $this->render('list', [
            'lang_code' => $lang_code,
            'selected_language' => array_search(
                $lang_code,
                $language_list
            ),
            'selected_language_diff' => array_search(
                $lang_code_diff,
                $language_list
            ),
            'only_empty' => $only_empty,
            'module_list' => $module_list,
            'language_list' => $language_list,
            'language_list_diff' => $language_list_diff,
            'plugins_ids' => $plugins_ids,
            'data' => $lang_list,
        ]);
    }

    private function removeSearchRegex($searchString)
    {
        if (strpos($searchString, '^.*') !== false && strpos($searchString, '.*$') !== false) {
            $searchString = str_replace(['^.*', '.*$'], '%', $searchString);
        }
        if (strpos($searchString, '^') !== false && strpos($searchString, '$') !== false) {
            $searchString = str_replace(['^', '$'], ['%', ''], $searchString);
        }
        if (strpos($searchString, '^') !== false) {
            $searchString = str_replace(['^'], [''], $searchString);
            $searchString .= '%';
        }

        return $searchString;
    }

    public function getTask()
    {
        $start_index = FormaLms\lib\Get::req('start', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('length', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 250));
        $sort = $dir = null;
        if ($order = FormaLms\lib\Get::req('order', DOTY_MIXED)) {
            $sort = $order[0]['column'];
            $dir = $order[0]['dir'];
        }
        $lang_code = FormaLms\lib\Get::req('lang_code', DOTY_ALPHANUM, false);
        $lang_code_diff = FormaLms\lib\Get::req('lang_code_diff', DOTY_ALPHANUM, false);

        $search = FormaLms\lib\Get::req('search', DOTY_MIXED, false);

        $la_text = $this->removeSearchRegex($search['value']);

        $plugin_id = FormaLms\lib\Get::req('plugin_id', DOTY_INT, false);
        $search = [];
        $columns = FormaLms\lib\Get::req('columns', DOTY_MIXED, []);
        foreach ($columns as $column) {
            switch ($column['name']) {
                case 'plugin_name':
                    if (!empty($column['search']['value']) && $column['search']['value'] !== '^') {
                        $plugins = $this->model->getPluginsList();

                        foreach ($plugins as $id => $pluginName) {
                            if ($pluginName === $column['search']['value']) {
                                $plugin_id = $id;
                            }
                        }
                    }
                    break;
            }

            if (!empty($column['search']['value']) && $column['search']['value'] !== '^') {
                $search[$column['name']] = $this->removeSearchRegex($column['search']['value']);
            }
        }

        $only_empty = FormaLms\lib\Get::req('only_empty', DOTY_MIXED, 0);
        if ($only_empty === 'true') {
            $only_empty = true;
        } else {
            $only_empty = false;
        }

        $lang_list = $this->model->getAll($start_index, $results, $search, $la_text, $lang_code, $lang_code_diff, $only_empty, $sort, $dir, $plugin_id);

        $total_lang = $this->model->getCount($search, $la_text, $lang_code, $only_empty);

        $res = [
            'recordsTotal' => $total_lang,
            'recordsFiltered' => $total_lang,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($lang_list),
            'data' => $lang_list,
        ];

        echo $this->json->encode($res);
    }

    /**
     * Inline editor server, here we will save the new trasnslation.
     */
    public function saveDataTask()
    {
        $id_text = urldecode(FormaLms\lib\Get::req('id_text', DOTY_INT, 0));
        $lang_code = urldecode(FormaLms\lib\Get::req('lang_code', DOTY_MIXED, Lang::get()));
        $new_value = urldecode(FormaLms\lib\Get::req('new_value', DOTY_MIXED, ''));
        $old_value = urldecode(FormaLms\lib\Get::req('old_value', DOTY_MIXED, ''));

        $re = $this->model->saveTranslation($id_text, $lang_code, $new_value);
        $res = [
            'success' => $re,
            'new_value' => $new_value,
            'old_value' => $old_value,
        ];

        echo $this->json->encode($res);
    }

    public function translatemask()
    {
        $lang = new stdClass();
        $lang->lang_code = '';

        $this->render('translatemask', []);

        $params = [
            'success' => true,
            'header' => Lang::t('_TRANSLATELANG', 'admin_lang'),
            'body' => ob_get_clean(),
        ];
        @ob_start();
        echo $this->json->encode($params);
    }

    public function insertkey()
    {
        $lang_module = FormaLms\lib\Get::req('lang_module', DOTY_MIXED, '');
        $lang_key = FormaLms\lib\Get::req('lang_key', DOTY_MIXED, '');

        $id_text = $this->model->insertKey($lang_key, $lang_module, '');
        if (!$id_text) {
            $re = false;
        } else {
            $re = true;

            foreach ($_POST['translation'] as $lang_code => $translation) {
                if ($translation != '') {
                    $re &= $this->model->insertTranslation($id_text, $lang_code, $translation);
                }
            }
        }
        $output = [
            'success' => ($re ? true : false),
            'message' => ($re ? Lang::t('_OPERATION_SUCCESSFUL', 'admin_lang') : Lang::t('_OPERATION_FAILURE', 'admin_lang')),
        ];
        echo $this->json->encode($output);
    }

    public function resetKey()
    {
        $idText = FormaLms\lib\Get::req('id_text', DOTY_MIXED, '');
        $langModule = FormaLms\lib\Get::req('lang_module', DOTY_MIXED, '');
        $translation = FormaLms\lib\Get::req('translation', DOTY_MIXED, '');

        if (!empty($translation)) {
            $re = $this->model->updateTranslation($idText, $langModule, $translation);
        }

        $output = [
            'success' => ($re ? true : false),
            'message' => ($re ? Lang::t('_OPERATION_SUCCESSFUL', 'admin_lang') : Lang::t('_OPERATION_FAILURE', 'admin_lang')),
        ];
        echo $this->json->encode($output);
    }

    public function deleteKeyTask()
    {
        $id_text = FormaLms\lib\Get::req('id_text', DOTY_INT, 0);

        $re = $this->model->deleteKey($id_text);
        $res = [
            'success' => $re,
            'message' => ($re ? Lang::t('_OPERATION_SUCCESSFUL', 'admin_lang') : Lang::t('_UNABLE_TO_DELETE', 'standard')),
        ];

        echo $this->json->encode($res);
    }

    public function diffTask()
    {
        require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');

        $langCode = urldecode(FormaLms\lib\Get::req('lang_code', DOTY_MIXED, Lang::get()));
        $langFile = FormaLms\lib\Get::req('lang_file', DOTY_MIXED, '');

        if (empty($langFile)) {
            $langFile = _base_ . '/xml_language/' . $this->getLangFileNameFromName($langCode);
        }

        $langList = $this->model->getAllForDiff($langFile, $langCode);

        $data = [
            'body' => array_values($langList),
            'langCode' => $langCode,
        ];

        $this->render('diff', $data);
    }

    public function saveDiffTask()
    {
        $langKeys = FormaLms\lib\Get::req('langKeys', DOTY_MIXED, []);

        foreach ($langKeys as $langKey) {
            if (!empty($langKey['translation'])) {
                $re = $this->model->updateTranslation($langKey['idText'], $langKey['langCode'], $langKey['translation']);

                $output[] = [
                    'langKey' => $langKey,
                    'success' => ($re ? true : false),
                    'message' => ($re ? Lang::t('_OPERATION_SUCCESSFUL', 'admin_lang') : Lang::t('_OPERATION_FAILURE', 'admin_lang')),
                ];
            }
        }

        echo $this->json->encode($output);
    }

    private function getFileSystemCoreLanguages()
    {
        return Lang::getFileSystemCoreLanguages();
    }

    private function getLangNameFromFile($file)
    {
        return Lang::getLangNameFromFile($file);
    }

    private function getLangFileNameFromName($name)
    {
        return Lang::getLangFileNameFromName($name);
    }
}
