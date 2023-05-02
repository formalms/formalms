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

class CommunicationAlmsController extends AlmsController
{
    /** @var CommunicationAlms */
    protected $model = null;
    protected $json = null;
    protected $permissions = null;
    public $data;

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';

        $this->model = new CommunicationAlms();
        $this->json = new Services_JSON();
        $this->permissions = [
            'view' => checkPerm('view', true, 'communication', 'lms'),
            'add' => checkPerm('mod', true, 'communication', 'lms'),
            'mod' => checkPerm('mod', true, 'communication', 'lms'),
            'del' => checkPerm('mod', true, 'communication', 'lms'),
            'subscribe' => checkPerm('subscribe', true, 'course', 'lms'),
            'add_category' => checkPerm('mod', true, 'communication', 'lms'),
            'mod_category' => checkPerm('mod', true, 'communication', 'lms'),
            'del_category' => checkPerm('mod', true, 'communication', 'lms'),
        ];
    }

    protected function _getSessionValue($index, $default = false)
    {
        if (!$this->session->has('communication')) {
            $this->session->set('communication', []);
            $this->session->save();
        }

        $communication = $this->session->get('communication');

        return $communication[$index] ?? $default;
    }

    protected function _setSessionValue($index, $value)
    {
        $this->session->set('communication', [$index => $value]);
        $this->session->save();
    }

    protected function _getMessage($code)
    {
        $message = '';
        switch ($code) {
            case 'no permission': $message = '';
            break;
        }

        return $message;
    }

    public function show()
    {
        require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');
        if (isset($_GET['error'])) {
            UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
        }
        if (isset($_GET['success'])) {
            UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'communication'));
        }

        $sort = false;

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 100));
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');

        $idCategory = FormaLms\lib\Get::req('categoryId', DOTY_INT, 0);
        $filter = FormaLms\lib\Get::req('filter', DOTY_MIXED, false);

        switch ($dir) {
            case 'desc':
                $dir = 'desc';
                break;
            default:
                $dir = 'asc';
                break;
        }

        $communicationList = $this->model->findAll($startIndex, $results, $sort, $dir, $filter, $idCategory);

        foreach ($communicationList as $i => $communication) {
            $communicationList[$i]['editUrl'] = 'index.php?r=alms/communication/edit&idComm=' . $communication['id_comm'];
            $communicationList[$i]['usersUrl'] = 'index.php?r=adm/userselector/show&id=' . $communication['id_comm'] . '&instance=communication&load=1';
            $communicationList[$i]['deleteUrl'] = 'ajax.adm_server.php?r=alms/communication/delete&idComm=' . $communication['id_comm'];
            $communicationList[$i]['description'] = (strlen(strip_tags($communication['description'])) > 150) ? substr(strip_tags($communication['description']), 0, 150) . '...' : strip_tags($communication['description']);
        }

        $langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
        $langCode = Lang::get();

        $categoryCount = $this->model->getCategoryTotal();

        $this->render('show', [
                                'communicationList' => array_values($communicationList),
                                'langs' => array_keys($langs),
                                'langCode' => $langCode,
                                'permissions' => $this->permissions,
                                'categoryCount' => $categoryCount,
                            ]);
    }

    public function getlist()
    {
        $id_category = FormaLms\lib\Get::req('id_category', DOTY_INT, 0);
        $show_descendants = FormaLms\lib\Get::req('descendants', DOTY_INT, 0) > 0;

        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'title');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        $filter = ['text' => $filter_text];

        $total_comm = $this->model->total($filter, $id_category, $show_descendants);
        $array_comm = $this->model->findAll($start_index, $results, $sort, $dir, $filter, $id_category, $show_descendants);

        $comm_id_arr = [];
        foreach ($array_comm as $key => $value) {
            $type = $value['type_of'];
            if ($type == 'file') {
                $comm_id_arr[] = $value['id_comm'];
            }
        }

        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();
        $categorized_file_items = $kbres->getCategorizedResources($comm_id_arr, 'file', 'communication', true);
        $categorized_file_items_id = (!empty($categorized_file_items) ? array_keys($categorized_file_items) : []);

        $list = [];
        foreach ($array_comm as $key => $value) {
            $array_comm[$key]['id'] = $value['id_comm'];
            if ($filter_text) {
                $array_comm[$key]['title'] = highlightText($value['title'], $filter_text);
                $array_comm[$key]['description'] = highlightText($value['description'], $filter_text);
            }
            $array_comm[$key]['publish_date'] = Format::date($value['publish_date'], 'date');
            $type = $array_comm[$key]['type_of'];
            if ($type == 'file' || $type == 'scorm') {
                if ($type == 'scorm' || in_array($value['id_comm'], $categorized_file_items_id)) {
                    $array_comm[$key]['categorize'] = '<a class="ico-sprite subs_categorize" title="' . Lang::t('_CATEGORIZE', 'kb') . '"
						href="index.php?r=alms/communication/categorize&id_comm=' . $value['id_comm'] . '"><span>'
                        . Lang::t('_CATEGORIZE', 'kb') . '</span></a>';
                } else {
                    $array_comm[$key]['categorize'] = '<a class="ico-sprite fd_notice" title="' . Lang::t('_NOT_CATEGORIZED', 'kb') . '"
						href="index.php?r=alms/communication/categorize&id_comm=' . $value['id_comm'] . '"><span>'
                        . Lang::t('_NOT_CATEGORIZED', 'kb') . '</span></a>';
                }
            } else {
                $array_comm[$key]['categorize'] = '';
            }
            if ($value['access_entity']) {
                $array_comm[$key]['user'] = '<a class="ico-sprite subs_user" title="' . Lang::t('_ASSIGN_USERS', 'communication') . '"
					href="index.php?r=alms/communication/mod_user&id_comm=' . $value['id_comm'] . '&load=1"><span>'
                    . Lang::t('_ASSIGN_USERS', 'communication') . '</span></a>';
            } else {
                $array_comm[$key]['user'] = '<a class="ico-sprite fd_notice" title="' . Lang::t('_NO_USER_SELECTED', 'communication') . '"
					href="index.php?r=alms/communication/mod_user&id_comm=' . $value['id_comm'] . '&load=1"><span>'
                    . Lang::t('_ASSIGN_USERS', 'communication') . '</span></a>';
            }
            $array_comm[$key]['edit'] = '<a class="ico-sprite subs_mod" href="index.php?r=alms/communication/edit&id_comm=' . $value['id_comm'] . '"><span>'
                . Lang::t('_MOD', 'communication') . '</span></a>';
            $array_comm[$key]['del'] = 'ajax.adm_server.php?r=alms/communication/del&id_comm=' . $value['id_comm'];
        }

        $result = [
            'totalRecords' => $total_comm,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($array_comm),
            'records' => $array_comm,
        ];

        $this->data = $this->json->encode($result);
        echo $this->data;
    }

    protected function add($data = false)
    {
        if (!$this->permissions['add']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        if ($this->model->getCategoryTotal() == 0) {
            Util::jump_to('index.php?r=alms/communication/show');

            return;
        }

        $langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
        $langCode = Lang::get();

        $categoriesDropdownData = $this->model->getCategoryDropdown($langCode);

        require_once _base_ . '/lib/lib.form.php';
        if (!$data) {
            $data = [
                'title' => '',
                'description' => '',
                'publish_date' => Format::date(date('Y-m-d'), 'date'),
                'type_of' => 'none',
                'id_course' => 0,
                'id_category' => FormaLms\lib\Get::req('id', DOTY_INT, 0),
            ];
        }

        $types = [
            Lang::t('_NONE', 'communication') => 'none',
            Lang::t('_LONAME_item', 'storage') => 'file',
            Lang::t('_LONAME_scormorg', 'storage') => 'scorm',
        ];

        $authentic_request = Util::getSignature();
        $this->render('add', [
            'data' => $data,
            'langs' => array_keys($langs),
            'langCode' => $langCode,
            'course_name' => '',
            'authentic_request' => $authentic_request,
            'categoriesDropdownData' => $categoriesDropdownData,
            'types' => $types,
        ]);
    }

    protected function insert()
    {
        if (!$this->permissions['add']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        if (FormaLms\lib\Get::req('undo', DOTY_MIXED, false) !== false) {
            Util::jump_to('index.php?r=alms/communication/show');
        }

        $data = [];

        $data['publish_date'] = FormaLms\lib\Get::req('publish_date', DOTY_MIXED, Format::date(date('Y-m-d'), 'date'));

        $data['type_of'] = FormaLms\lib\Get::req('type_of', DOTY_STRING, '');
        $data['publish_date'] = Format::dateDb($data['publish_date'], 'date');
        $data['id_category'] = FormaLms\lib\Get::req('id_category', DOTY_INT, 0);
        $data['id_course'] = FormaLms\lib\Get::req('idCourse', DOTY_STRING, 0);

        $titles = FormaLms\lib\Get::req('title', DOTY_MIXED, []);
        $descriptions = FormaLms\lib\Get::req('description', DOTY_MIXED, []);

        //validate inputs
        if (is_array($titles)) {
            //prepare langs array
            $lang_codes = \FormaLms\lib\Forma::langManager()->getAllLangcode();
            foreach ($lang_codes as $lang_code) {
                $data['langs'][$lang_code] = [
                    'title' => (isset($titles[$lang_code]) ? $titles[$lang_code] : ''),
                    'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : ''),
                ];
            }
        }

        $id_comm = $this->model->save($data);
        if (!$id_comm) {
            UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
            $this->add($data);
        } elseif ($data['type_of'] != 'none') {
            Util::jump_to('index.php?r=alms/communication/add_obj&id_comm=' . $id_comm);
        } else {
            Util::jump_to('index.php?r=alms/communication/show&success=1');
        }
    }

    protected function add_obj()
    {
        if (!$this->permissions['add']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $data = $this->model->findByPk($id_comm);
        $back_url = 'index.php?r=alms/communication/insert_obj&id_comm=' . $id_comm;

        switch ($data['type_of']) {
            case 'file' :
                require_once _lms_ . '/class.module/learning.item.php';
                $l_obj = new Learning_Item();
                $l_obj->create($back_url);

                break;
            case 'scorm' :
                require_once _lms_ . '/class.module/learning.scorm.php';
                $l_obj = new Learning_ScormOrg();
                $l_obj->create($back_url);

                break;
            case 'none' :
            default:
                Util::jump_to('index.php?r=alms/communication/show');

                break;
        }
    }

    protected function insert_obj()
    {
        if (!$this->permissions['add']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $data['id_comm'] = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $data['id_resource'] = FormaLms\lib\Get::req('id_lo', DOTY_INT, 0);
        if (!$data['id_resource']) {
            $tmpReq = FormaLms\lib\Get::req('id_los', DOTY_MIXED, 0);
            $data['id_resource'] = explode(',', $tmpReq)[0];
        }
        $create_result = FormaLms\lib\Get::req('create_result', DOTY_INT, 0);
        if ($create_result >= 1) {
            if ($this->model->save($data)) {
                $data = $this->model->findByPk($data['id_comm']);
                if ($data['type_of'] == 'file' || $data['type_of'] == 'scorm') { // Save resource as uncategorized
                    require_once _lms_ . '/lib/lib.kbres.php';
                    $kbres = new KbRes();
                    $kbres->saveUncategorizedResource(
                        $data['title'],
                        $data['id_resource'],
                        $data['type_of'],
                        'communication',
                        $data['id_comm']
                    );
                }
                Util::jump_to('index.php?r=alms/communication/show&success=1');
            }
        }
        // destroy the empty game
        $this->model->delByPk($data['id_comm']);
        Util::jump_to('index.php?r=alms/communication/show&error=1');
    }

    protected function edit()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        require_once _base_ . '/lib/lib.form.php';

        $idComm = FormaLms\lib\Get::req('idComm', DOTY_INT, 0);
        $data = $this->model->findByPk($idComm);

  
        $data['publish_date'] = Format::date($data['publish_date'], 'date');

        $course_model = new CourseAlms();
        $cinfo = $course_model->getCourseModDetails($data['id_course']);
        $courseName = /*($cinfo['code'] ? "[".$cinfo['code']."] " : "").*/ $cinfo['name'];
        YuiLib::load('autocomplete');
        $langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
        $langCode = Lang::get();


        $langsMapped = array_map(fn ($value): array => [$value['lang_code'] => [
            'title' => $value['title'],
            'description' => $value['description'],
        ]], $data['langs']);
 
        $data['langs'] = array_merge(...$langsMapped);
        //controllo che ci siano almeno un tile e una descrizione di fallback
        if (!count($data['langs']) || !in_array($langCode, array_keys($data['langs']))) {
            $tmpLang['title'] = $data['title'];
            $tmpLang['description'] = $data['description'];
            $data['langs'][$langCode] = $tmpLang;
        }

        $categoriesDropdownData = $this->model->getCategoryDropdown($langCode);
        $authentic_request = Util::getSignature();
        $this->render('edit', [
            'data' => $data,
            'idCourse' => $data['id_course'],
            'courseName' => $courseName,
            'idComm' => $idComm,
            'formUrl' => $data['type_of'] == 'none' ? 'index.php?r=alms/communication/update' : 'index.php?r=alms/communication/mod_obj&id_comm=' . $data['id_comm'],
            'langs' => array_keys($langs),
            'langCode' => $langCode,
            'authentic_request' => $authentic_request,
            'categoriesDropdownData' => $categoriesDropdownData,
        ]);
    }

    protected function update()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        if (FormaLms\lib\Get::req('undo', DOTY_MIXED, false) !== false) {
            Util::jump_to('index.php?r=alms/communication/show');
        }

        $data = [];
        $data['id_comm'] = FormaLms\lib\Get::req('id_comm', DOTY_MIXED, '');

        $data['publish_date'] = FormaLms\lib\Get::req('publish_date', DOTY_MIXED, Format::date(date('Y-m-d'), 'date'));

        $data['type_of'] = FormaLms\lib\Get::req('type_of', DOTY_STRING, 'none');
        $data['id_course'] = FormaLms\lib\Get::req('idCourse', DOTY_INT, 0);

        $data['publish_date'] = Format::dateDb($data['publish_date'], 'date');

        $titles = FormaLms\lib\Get::req('title', DOTY_MIXED, []);
        $descriptions = FormaLms\lib\Get::req('description', DOTY_MIXED, []);

        //validate inputs
        if (is_array($titles)) {
            $data['langs'] = [];
            //prepare langs array
            $lang_codes = \FormaLms\lib\Forma::langManager()->getAllLangcode();
            foreach ($lang_codes as $lang_code) {
                $data['langs'][$lang_code] = [
                    'title' => (isset($titles[$lang_code]) ? $titles[$lang_code] : ''),
                    'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : ''),
                ];
            }
        }

        $id_comm = $this->model->save($data);
        if (!$id_comm) {
            UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
            $this->add($data);
        } elseif ($data['type_of'] != 'none') {
            Util::jump_to('index.php?r=alms/communication/mod_obj&id_comm=' . $id_comm);
        } else {
            Util::jump_to('index.php?r=alms/communication/show&success=1');
        }
    }

    protected function mod_obj()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $data = $this->model->findByPk($id_comm);
        $titles = FormaLms\lib\Get::req('title', DOTY_MIXED, []);
        $descriptions = FormaLms\lib\Get::req('description', DOTY_MIXED, []);

        //validate inputs
        if (is_array($titles)) {
            $data['langs'] = [];
            //prepare langs array
            $lang_codes = \FormaLms\lib\Forma::langManager()->getAllLangcode();
            foreach ($lang_codes as $lang_code) {
                $data['langs'][$lang_code] = [
                    'title' => (isset($titles[$lang_code]) ? $titles[$lang_code] : ''),
                    'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : ''),
                ];
            }
        }

        $result = $this->model->save($data);

        if ($result) {
            $back_url = 'index.php?r=alms/communication/update_obj&id_comm=' . $id_comm;

            switch ($data['type_of']) {
                case 'file' :
                    require_once _lms_ . '/class.module/learning.item.php';
                    $l_obj = new Learning_Item();
                    $l_obj->edit($data['id_resource'], $back_url);

                    break;
                case 'scorm' :
                    //cannot be modified
                    Util::jump_to('index.php?r=alms/communication/show');

                    break;
                case 'none' :
                default:
                    Util::jump_to('index.php?r=alms/communication/show');

                    break;
            }
        } else {
            Util::jump_to('index.php?r=alms/communication/show&error=1');
        }
    }

    protected function update_obj()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $data['id_comm'] = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        $data['id_resource'] = FormaLms\lib\Get::req('id_lo', DOTY_INT, 0);
        if (!$data['id_resource']) {
            $tmpReq = FormaLms\lib\Get::req('id_los', DOTY_MIXED, 0);
            $data['id_resource'] = explode(',', $tmpReq)[0];
        }
        $mod_result = FormaLms\lib\Get::req('mod_result', DOTY_INT, 0);

        if ($mod_result >= 1) {
            if ($this->model->save($data)) {
                Util::jump_to('index.php?r=alms/communication/show&success=1');
            }
        }
        Util::jump_to('index.php?r=alms/communication/show&error=1');
    }

    protected function delete()
    {
        if (!$this->permissions['del']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $idComm = FormaLms\lib\Get::req('idComm', DOTY_INT, 0);
        $data = $this->model->findByPk($idComm);

        if ($data['id_resource']) {
            switch ($data['type_of']) {
                case 'file' :
                    require_once _lms_ . '/class.module/learning.item.php';
                    $l_obj = new Learning_Item();
                    $re = $l_obj->del($data['id_resource']);

                    break;
                case 'scorm' :
                    require_once _lms_ . '/class.module/learning.scorm.php';
                    $l_obj = new Learning_ScormOrg();
                    $re = $l_obj->del($data['id_resource']);

                    break;
                case 'none' :
                default:
                    $re = true;

                    break;
            }
        } else {
            $re = true;
        }
        if ($re) {
            $output['success'] = $this->model->delByPk($idComm);
            if ($output['success'] && ($data['type_of'] == 'file' || $data['type_of'] == 'scorm')) {
                require_once _lms_ . '/lib/lib.kbres.php';
                $kbres = new KbRes();
                $kbres->deleteResourceFromItem($data['id_resource'], $data['type_of'], 'communication');
            }
        } else {
            $output['success'] = false;
        }

        echo $this->json->encode($output);
    }

    /**
     * Modify and save the users that can see a communication.
     */
    protected function mod_user()
    {
        if (!$this->permissions['subscribe']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        // undo selected
        if (isset($_POST['cancelselector'])) {
            Util::jump_to('index.php?r=alms/communication/show');
        }

        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        // instance of the user selector
        require_once _adm_ . '/class.module/class.directory.php';
        $user_selector = new UserSelector();
        $user_selector->show_user_selector = true;
        $user_selector->show_group_selector = true;
        $user_selector->show_orgchart_selector = true;
        $user_selector->show_orgchart_simple_selector = false;
        // save new setting
        if (isset($_POST['okselector'])) {
            //compute new selection
            $old_selection = $this->model->accessList($id_comm); //print_r($old_selection);
            $new_selection = $user_selector->getSelection($_POST); /*print_r($_POST);*/ //print_r($new_selection); die();
            //save
            if ($this->model->updateAccessList($id_comm, $old_selection, $new_selection)) {
                Util::jump_to('index.php?r=alms/communication/show&success=1');
            } else {
                Util::jump_to('index.php?r=alms/communication/show&error=1');
            }
        }
        // load saved actions
        if (isset($_GET['load'])) {
            $selection = $this->model->accessList($id_comm);
            $user_selector->resetSelection($selection);
        }
        // render the user selector
        $this->render('mod_user', [
            'id_comm' => $id_comm,
            'user_selector' => $user_selector,
        ]);
    }

    public function categorize()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);
        //$r_data =

        require_once _lms_ . '/lib/lib.kbres.php';
        $kbres = new KbRes();

        if ($id_comm > 0) {
            $data = $this->model->findByPk($id_comm);
            $r_data = $kbres->getResourceFromItem($data['id_resource'], $data['type_of'], 'communication');
        }

        if (isset($_POST['subcategorize_switch'])) {
            $cat_sub_items = FormaLms\lib\Get::pReq('subcategorize_switch', DOTY_INT);
            $res_id = (int) $r_data['res_id'];
            $r_env_parent_id = (int) $r_data['r_env_parent_id'];

            $kbres->saveResourceSubCategorizePref($res_id, $cat_sub_items);

            Util::jump_to('index.php?r=alms/communication/categorize&amp;id_comm=' . $r_env_parent_id);
            exit();
        } else {
            if (isset($_POST['org_categorize_save'])) {
                $res_id = FormaLms\lib\Get::req('res_id', DOTY_INT, 0);
                $name = FormaLms\lib\Get::req('r_name', DOTY_STRING, '');
                $original_name = ''; // won't update this field
                $desc = FormaLms\lib\Get::req('r_desc', DOTY_STRING, '');
                $r_item_id = FormaLms\lib\Get::req('r_item_id', DOTY_INT, 0);
                $type = FormaLms\lib\Get::req('r_type', DOTY_STRING, '');
                $env = FormaLms\lib\Get::req('r_env', DOTY_STRING, '');
                $env_parent_id = FormaLms\lib\Get::req('r_env_parent_id', DOTY_INT, 0);
                $param = FormaLms\lib\Get::req('r_param', DOTY_STRING, '');
                $alt_desc = '';
                $lang_id = FormaLms\lib\Get::req('r_lang', DOTY_INT, '');
                $lang_arr = \FormaLms\lib\Forma::langManager()->getAllLangCode();
                $lang = $lang_arr[$lang_id];
                $force_visible = FormaLms\lib\Get::req('force_visible', DOTY_INT, 0);
                $is_mobile = FormaLms\lib\Get::req('is_mobile', DOTY_INT, 0);
                $folders = FormaLms\lib\Get::req('h_selected_folders', DOTY_STRING, '');
                $json_tags = Util::strip_slashes(FormaLms\lib\Get::req('tag_list', DOTY_STRING, '[]'));

                $res_id = $kbres->saveResource(
                    $res_id,
                    $name,
                    $original_name,
                    $desc,
                    $r_item_id,
                    $type,
                    $env,
                    $env_parent_id,
                    $param,
                    $alt_desc,
                    $lang,
                    $force_visible,
                    $is_mobile,
                    $folders,
                    $json_tags
                );

                Util::jump_to('index.php?r=alms/communication/show');
            } else {
                if (isset($_POST['org_categorize_cancel'])) {
                    Util::jump_to('index.php?r=alms/communication/show');
                } else {
                    if ($data['type_of'] == 'scorm' && $r_data && $r_data['sub_categorize'] == 1) {
                        $this->categorize_sco($id_comm, $data);
                    } /* else if ($data['type_of'] == 'scorm' && $r_data && $r_data['sub_categorize'] == -1) {
            $this->subcategorize_ask($id_comm, $data, $r_data);
        } */
                    else {
                        $data = $this->model->findByPk($id_comm);
                        $data['item_id'] = $id_comm;

                        $this->render('categorize', [
                            'id_comm' => $id_comm,
                            'data' => $data,
                            'r_param' => '',
                            'back_url' => 'index.php?r=alms/communication/show',
                            'form_url' => 'index.php?r=alms/communication/categorize&amp;id_comm=' . $id_comm,
                        ]);
                    }
                }
            }
        }
    }

    public function categorize_sco($id_comm, $data)
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getMessage('no permission'),
                'back_url' => 'index.php?r=alms/communication/show',
            ]);

            return;
        }

        $sco_id = FormaLms\lib\Get::req('sco_id', DOTY_INT, 0);

        if ($sco_id > 0) {
            $qtxt = 'SELECT idscorm_item, title, identifierref FROM
				' . $GLOBALS['prefix_lms'] . "_scorm_items WHERE idscorm_item='" . (int) $sco_id . "'
				AND idscorm_organization='" . (int) $data['id_resource'] . "'";
            $q = sql_query($qtxt);

            $row = sql_fetch_assoc($q);

            $sco_data = [];
            $sco_data['item_id'] = $sco_id;
            $sco_data['title'] = $row['title'];
            $sco_data['type_of'] = 'scoitem';
            $sco_data['id_resource'] = $sco_id;
            $this->render('categorize', [
                'id_comm' => $id_comm,
                'data' => $sco_data,
                'r_param' => 'chapter=' . $row['identifierref'],
                'back_url' => 'index.php?r=alms/communication/categorize&amp;id_comm=' . $id_comm,
                //'form_url'=>'index.php?r=alms/communication/save_sco_categorize',
                'form_url' => 'index.php?r=alms/communication/categorize&amp;id_comm=' . $id_comm,
            ]);
        } else {
            $this->render('sco_table', [
                'id_comm' => $id_comm,
                'id_resource' => $data['id_resource'],
                'comm_data' => $data,
            ]);
        }
    }

    public function save_sco_categorize()
    {
        $id_comm = FormaLms\lib\Get::req('id_comm', DOTY_INT, 0);

        if (isset($_POST['org_categorize_cancel'])) {
            Util::jump_to('index.php?r=alms/communication/categorize&id_comm=' . $id_comm);
        } else {
            $this->categorize();
        }
    }

    //--- TREE TASKS AND FUNCTIONS -----------------------------------------------

    protected function _getNodeActions($node)
    {
        if (!is_array($node)) {
            return false;
        } //unrecognized type for node data
        $actions = [];
        $id_action = $node['id'];
        $is_root = ($id_action == 0);

        //permissions
        $can_mod = $this->permissions['mod_category'];
        $can_del = $this->permissions['del_category'];

        //rename action
        if ($can_mod) {
            $actions[] = [
                'id' => 'mod_' . $id_action,
                'command' => 'modify',
                'icon' => 'standard/edit.png',
                'alt' => Lang::t('_MOD', 'standard'),
            ];
        }

        //delete action
        if ($can_del) {
            if (isset($node['is_leaf']) && $node['count_objects'] <= 0 && !$is_root) {
                $actions[] = [
                    'id' => 'del_' . $id_action,
                    'command' => 'delete',
                    'icon' => 'standard/delete.png',
                    'alt' => Lang::t('_DEL', 'standard'),
                ];
            } else {
                $actions[] = [
                    'id' => 'del_' . $id_action,
                    'command' => false,
                    'icon' => 'blank.png',
                ];
            }
        }

        return $actions;
    }

    protected function _assignActions(&$nodes)
    {
        if (!is_array($nodes)) {
            return;
        }
        for ($i = 0; $i < count($nodes); ++$i) {
            $nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
            if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
                $this->_assignActions($nodes[$i]['children']);
            }
        }
    }

    public function gettreedataTask()
    {
        $command = FormaLms\lib\Get::req('command', DOTY_ALPHANUM, '');

        switch ($command) {
            case 'expand':
                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, 0);
                $initial = (FormaLms\lib\Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

                if ($initial) {
                    //get selected category from session and set the expanded tree
                    $node_id = $this->_getSessionValue('selected_node', 0);
                    $nodes = $this->model->getInitialCategories($node_id, false);

                    //set nodes action recursively
                    $this->_assignActions($nodes);

                    //set output
                    if (is_array($nodes)) {
                        $output = [
                            'success' => true,
                            'nodes' => $nodes,
                            'initial' => $initial,
                        ];
                    } else {
                        $output = ['success' => false];
                    }
                } else {
                    //extract node data
                    $nodes = $this->model->getCategories($node_id);

                    //if request is invalid, return error message ...
                    if (!is_array($nodes)) {
                        echo $this->json->encode(['success' => false]);

                        return;
                    }

                    //create actions for every node
                    for ($i = 0; $i < count($nodes); ++$i) {
                        $nodes[$i]['options'] = $this->_getNodeActions($nodes[$i]);
                    }
                    //set output
                    $output = [
                        'success' => true,
                        'nodes' => $nodes,
                        'initial' => $initial,
                    ];
                }
                echo $this->json->encode($output);
                break;

            case 'set_selected_node':
                $this->_setSessionValue('selected_node', FormaLms\lib\Get::req('node_id', DOTY_INT, 0));
                break;

            case 'delete':
                //check permissions
                if (!$this->permissions['mod']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $output = ['success' => false];
                $id = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                if ($id > 0) {
                    $output['success'] = $this->model->deleteCategory($id);
                }
                echo $this->json->encode($output);
                break;

            case 'movefolder':
                //check permissions
                if (!$this->permissions['mod']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $this->move_categoryTask();
                break;
        }
    }

    public function editCategory()
    {
        //check permissions
        if (!$this->permissions['mod']) {
            UIFeedback::error($this->_getMessage('no permission'));

            return;
        }

        $idCategory = FormaLms\lib\Get::req('id', DOTY_INT, -1);
        if ($idCategory <= 0) {
            UIFeedback::error($this->_getMessage('invalid category'));

            return;
        }

        //retrieve category info (name and description
        $info = $this->model->getCategoryInfo($idCategory);

        $langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
        $langCode = Lang::get();

        $categoriesDropdownData = $this->model->getCategoryDropdown($langCode, true);

        $this->render('edit_category', [
            'title' => Lang::t('_MOD', 'communication'),
            'idCategory' => $idCategory,
            'idParent' => $info->id_parent,
            'categoryLangs' => $info->langs,
            'langs' => array_keys($langs),
            'categoriesDropdownData' => $categoriesDropdownData,
            'langCode' => $langCode,
        ]);
    }

    public function addCategoryActionTask()
    {
        $parentLabel = '--';
        //check permissions
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        //set up the data to insert into DB
        $idParent = FormaLms\lib\Get::req('id_category', DOTY_INT, -1);
        $names = FormaLms\lib\Get::req('name', DOTY_MIXED, []);
        $descriptions = FormaLms\lib\Get::req('description', DOTY_MIXED, []);
        $langs = [];

        //validate inputs
        if (is_array($names)) {
            //prepare langs array
            $lang_codes = \FormaLms\lib\Forma::langManager()->getAllLangcode();
            foreach ($lang_codes as $lang_code) {
                $langs[$lang_code] = [
                    'name' => (isset($names[$lang_code]) ? $names[$lang_code] : ''),
                    'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : ''),
                ];
            }
        }

        //insert data in the DB
        $res = $this->model->createCategory($idParent, $langs);

        if ($idParent) {
            $parentLabel = $this->model->getCategory($idParent)[1];
        }

        if ($res) {
            $filterUrl = 'index.php?r=alms/communication/show&categoryId=' . $res;
            $editUrl = 'index.php?r=alms/communication/editCategory&id=' . $res;
            $deleteUrl = 'ajax.adm_server.php?r=alms/communication/deleteCategory';
            //return node data to add in the treeview of the page
            $nodedata = [
                'id' => $res,
                'label' => $this->model->getCategoryName($res, Lang::get()),
                'parentLabel' => $parentLabel,
                'countObjects' => 0,
                'filterUrl' => $filterUrl,
                'editUrl' => $editUrl,
                'deleteUrl' => $deleteUrl,
            ];
            $nodedata['options'] = $this->_getNodeActions($nodedata);
            $output = [
                'success' => true,
                'node' => $nodedata,
                'id_parent' => $idParent,
            ];
        } else {
            $output = [
                'success' => false,
                'message' => UIFeedback::perror($this->_getMessage('create category')),
            ];
        }
        echo $this->json->encode($output);
    }

    public function updateCategory()
    {
        //check permissions
        if (!$this->permissions['mod']) {
            UIFeedback::error($this->_getMessage('no permission'));

            return;
        }

        //set up the data to insert into DB
        $idCategory = FormaLms\lib\Get::req('idCategory', DOTY_INT, -1);

        if ($idCategory < 0) {
            UIFeedback::error($this->_getMessage('invalid category'));

            return;
        }
        $names = FormaLms\lib\Get::req('name', DOTY_MIXED, []);
        $descriptions = FormaLms\lib\Get::req('description', DOTY_MIXED, []);

        $idParent = FormaLms\lib\Get::req('id_parent', DOTY_INT, 0);
        $langs = [];

        //validate inputs
        if (is_array($names)) {
            //prepare langs array
            $lang_codes = \FormaLms\lib\Forma::langManager()->getAllLangcode();
            foreach ($lang_codes as $lang_code) {
                $langs[$lang_code] = [
                    'name' => (isset($names[$lang_code]) ? $names[$lang_code] : ''),
                    'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : ''),
                ];
            }
        }

        //insert data in the DB
        $res = $this->model->updateCategory($idCategory, $idParent, $langs);

        Util::jump_to('index.php?r=alms/communication/showCategories&success=1');
    }

    public function move_categoryTask()
    {
        //check permissions
        if (!$this->permissions['mod']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        $src = FormaLms\lib\Get::req('src', DOTY_INT, -1);
        $dest = FormaLms\lib\Get::req('dest', DOTY_INT, -1);

        $output = [];

        if ($src <= 0 || $dest < 0) {
            $output['success'] = false;
            $output['message'] = UIFeedback::perror($this->_getMessage('invalid category'));
            echo $this->json->encode($output);

            return;
        }

        $res = $this->model->moveCategory($src, $dest);
        $output['success'] = $res ? true : false;
        if (!$res) {
            $output['message'] = UIFeedback::perror($this->_getMessage('move category'));
        }
        echo $this->json->encode($output);
    }

    public function showCategories()
    {
        require_once \FormaLms\lib\Forma::inc(_lib_ . '/formatable/include.php');

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 100));
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, 'id');

        switch ($dir) {
            case 'desc':
                $dir = 'desc';
                break;
            default:
                $dir = 'asc';
                break;
        }

        $categoriesList = $this->model->getCategoryList($startIndex, $results, $sort, $dir);
        foreach ($categoriesList as $i => $category) {
            $categoriesList[$i]['filterUrl'] = 'index.php?r=alms/communication/show&categoryId=' . $category['id'];
            $categoriesList[$i]['editUrl'] = 'index.php?r=alms/communication/editCategory&id=' . $category['id'];
            $categoriesList[$i]['deleteUrl'] = 'ajax.adm_server.php?r=alms/communication/deleteCategory';
        }

        $langs = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
        $langCode = Lang::get();

        $categoriesDropdownData = $this->model->getCategoryDropdown($langCode, true);

        $this->render('show_categories', [
                                            'categoriesList' => array_values($categoriesList),
                                            'langs' => array_keys($langs),
                                            'langCode' => $langCode,
                                            'categoriesDropdownData' => $categoriesDropdownData,
                                            'permissions' => $this->permissions,
                                        ]);
    }

    public function deleteCategoryTask()
    {
        $idCategory = FormaLms\lib\Get::req('idCategory', DOTY_INT, 0);

        $output = $this->model->deleteCategory($idCategory);

        echo json_encode($output);
    }

    //----------------------------------------------------------------------------
}
