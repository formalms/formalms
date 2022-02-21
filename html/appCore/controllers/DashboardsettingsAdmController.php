<?php defined("IN_FORMA") or die("Direct access is forbidden");


require_once(Forma::inc(_base_ . '/lib/lib.upload.php'));

/**
 * Class DashboardsettingsAdmController
 */
class DashboardsettingsAdmController extends AdmController
{

    /** @var DashboardsettingsAdm $model */
    protected $model;

    /** @var Services_JSON $json */
    protected $json;

    /** @var array $permissions */
    protected $permissions;

    /*
     * initialize the class
     */
    public function init()
    {
        parent::init();
        require_once(_base_ . '/lib/lib.json.php');
        $this->json = new Services_JSON();
        $this->model = new DashboardsettingsAdm();

        Util::get_js(Get::rel_path('base') . '/addons/tiny_mce/tinymce.min.js', true, true);
        Util::get_js(Get::rel_path('base') . '/addons/tiny_mce/forma.js', true, true);

        $this->permissions = [
            'view' => checkPerm('view', true, 'dashboard', 'framework'),
            'view_user' => checkPerm('view', true, 'usermanagement', 'framework'),
            'add_user' => checkPerm('add', true, 'usermanagement', 'framework'),
            'mod_user' => checkPerm('mod', true, 'usermanagement', 'framework'),
            'del_user' => checkPerm('del', true, 'usermanagement', 'framework'),
            'view_course' => checkPerm('view', true, 'course', 'lms'),
            'add_course' => checkPerm('add', true, 'course', 'lms'),
            'mod_course' => checkPerm('mod', true, 'course', 'lms'),
            'del_course' => checkPerm('del', true, 'course', 'lms'),
            'view_communications' => checkPerm('view', true, 'communication', 'lms'),
            'add_communications' => checkPerm('add', true, 'communication', 'lms'),
            'view_games' => checkPerm('view', true, 'games', 'lms'),
            'add_games' => checkPerm('add', true, 'games', 'lms'),
            'subscribe' => checkPerm('subscribe', true, 'course', 'lms'),
        ];
    }


    //----------------------------------------------------------------------------


    public function show()
    {
        require_once(Get::rel_path('lib') . '/formatable/formatable.php');
        Util::get_css(Get::rel_path('lib') . '/formatable/formatable.css', true, true);

        $data = [
            'ajaxUrl' => [
                'save' => 'ajax.adm_server.php?r=adm/dashboardsettings/save',
                'saveLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/saveLayout',
                'editInlineLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/editInlineLayout',
                'delLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/delLayout',
                'defaultLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/defaultLayout',
                'uploadFile' => 'ajax.adm_server.php?r=adm/dashboardsettings/uploadFile',
                'getLayouts' => 'ajax.adm_server.php?r=adm/dashboardsettings/getLayouts',
                'getBlockType' => 'ajax.adm_server.php?r=adm/dashboardsettings/getBlockTypeForm',
            ],
            'showUrl' => './index.php?r=adm/dashboardsettings/show',
            'editUrl' => './index.php?r=adm/dashboardsettings/edit',
            'permissionUrl' => './index.php?r=adm/dashboardsettings/permission',
            'cloneUrl' => './index.php?r=adm/dashboardsettings/clone',
            'templatePath' => getPathTemplate(),
        ];

        //render view
        $this->render('show', $data);
    }

    public function edit()
    {
        $dashboardId = Get::req('dashboard', DOTY_INT, false);
        $dashboard = $this->model->getLayout($dashboardId);

        $data = [
            'ajaxUrl' => [
                'save' => 'ajax.adm_server.php?r=adm/dashboardsettings/save',
                'saveLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/saveLayout',
                'cloneLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/cloneLayout',
                'editInlineLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/editInlineLayout',
                'delLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/delLayout',
                'defaultLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/defaultLayout',
                'uploadFile' => 'ajax.adm_server.php?r=adm/dashboardsettings/uploadFile',
                'getLayouts' => 'ajax.adm_server.php?r=adm/dashboardsettings/getLayouts',
                'getBlockType' => 'ajax.adm_server.php?r=adm/dashboardsettings/getBlockTypeForm',
            ],
            'showUrl' => './index.php?r=adm/dashboardsettings/show',
            'installedBlocks' => $this->model->getInstalledBlocksCommonViewData(),
            'enabledBlocks' => $this->model->getEnabledBlocksCommonViewData($dashboardId),
            'templatePath' => getPathTemplate(),
            'dashboard' => $dashboard,
            'dashboardId' => $dashboardId,
        ];

        
        //render view
        $this->render('edit', $data);
    }

    // add permission to layout
    public function permission(){
        $dashboardId = Get::req('dashboard', DOTY_INT, false);

        require_once(_base_ . '/lib/lib.userselector.php');
        require_once(_base_ . '/lib/lib.form.php');

        $man_ma = new Man_MiddleArea();
        $acl_manager = new DoceboACLManager();
        $user_select = new UserSelector();

        // tab of user selector
        $user_select->show_user_selector = TRUE;
        $user_select->show_group_selector = true;
        $user_select->show_orgchart_selector = true;
        $user_select->show_orgchart_simple_selector = false;
        $user_select->show_fncrole_selector = true;
        $user_select->multi_choice = true;


        $selected = $this->model->getObjIdstList($dashboardId);

        if (is_array($selected)) $user_select->resetSelection($selected);

        // cancell
        if(isset($_POST['cancelselector'])){

          Util::jump_to('index.php?r=adm/dashboardsettings/show');
        }

        // save
        if (isset($_POST['okselector'])) {

            $selected = $user_select->getSelection($_POST);

            $re = $this->model->setObjIdstList($dashboardId, $selected);

            Util::jump_to('index.php?r=adm/dashboardsettings/show&result=' . ($re ? 'ok' : 'err'));
        }

        // add field hidden id_dashboard
        $user_select->addFormInfo(Form::getHidden('dashboard', 'dashboard', $dashboardId));

         // view selector user
        $user_select->loadSelector('index.php?r=adm/dashboardsettings/permission',
             Lang::t('_VIEW_PERMISSION', 'standard'),
            false,
            true);

    }



    public function clone()
    {
        $dashboardId = Get::req('dashboard', DOTY_INT, false);
        $dashboard = $this->model->getLayout($dashboardId);

        $data = [
            'ajaxUrl' => [
                'cloneLayout' => 'ajax.adm_server.php?r=adm/dashboardsettings/cloneLayout',
            ],
            'dashboard' => $dashboard,
            'dashboardId' => $dashboardId,
        ];

        $res = [
            'data' => $data
        ];

        echo $this->json->encode($res);
        exit;
    }

    public function getLayouts()
    {
        $selectedDashboardId = Get::req('dashboard', DOTY_INT, false);
        $search = Get::req('search', DOTY_MIXED, false);
        $layouts = $this->model->getLayouts();
        $res = [];

        foreach ($layouts as $layout) {
            $layout = array_values((array)$layout);

            $keys = [
                'id',
                'name',
                'caption',
                'status',
                'default',
                'selected',
            ];

            $item = [];
            for ($i = 0; $i < count($keys) - 1; $i++) {
                $item[$keys[$i]] = $layout[$i];
                $item['selected'] = $layout[0] == $selectedDashboardId;
            }

            if (!$search['value'] || strpos($item['name'], $search['value']) !== false || strpos($item['caption'], $search['value']) !== false) {
                $res[] = $item;
            }
        }

        $response = [
            "data" => $res,
            "recordsFiltered" => count($res),
            "recordsTotal" => count($res),
        ];

        echo $this->json_response(200, $response);
        exit;
    }

    public function saveLayout()
    {
        $name = Get::pReq('name', DOTY_MIXED);
        $caption = Get::pReq('caption', DOTY_MIXED);
        $status = Get::pReq('status', DOTY_MIXED);
        $default = Get::pReq('default', DOTY_BOOL);

        $data = [
            'name' => $name,
            'caption' => $caption,
            'status' => $status,
            'default' => $default
        ];

        $response = [];

        // Validation
        $errors = [];
        if (!isset($data['name']) || !$data['name']) {
            $errors['name'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        }
        if (!isset($data['status']) || !$data['status']) {
            $errors['status'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        }
        if (!isset($data['default']) || !is_bool($data['default'])) {
            $errors['default'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        }

        if ($errors) {
            $status = 400;
            $response['errors'] = $errors;
        } else if (!$this->model->saveLayout($data)) {
            $response['errors'] = [];
        } else {
            $status = 200;
        }

        echo $this->json_response($status, $response);
        exit;
    }

    public function cloneLayout()
    {
        $id = Get::pReq('id', DOTY_INT);
        $name = Get::pReq('name', DOTY_MIXED);
        $caption = Get::pReq('caption', DOTY_MIXED);
        $status = Get::pReq('status', DOTY_MIXED);

        $dashboard = $this->model->getLayout($id);

        $data = [
            'name' => $name,
            'caption' => $caption,
            'status' => $status,
        ];

        $response = [];

        // Validation
        $errors = [];
        if (!isset($data['name']) || !$data['name']) {
            $errors['name'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        }
        if (!isset($data['status']) || !$data['status']) {
            $errors['status'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        }

        if ($errors) {
            $status = 400;
            $response['errors'] = $errors;
        } else if (!$res = $this->model->saveLayout($data)) {
            $status = 400;
            $response['name'] = Lang::t('_VALUE_IS_NOT_VALID', 'dashboardsetting');
        } else {
            $status = 200;
            $res = sql_query("SELECT id FROM `dashboard_layouts` ORDER BY id DESC LIMIT 1");
            $row = sql_fetch_object($res);
            $dashboard_new_id = $row->id;

            $sql = "INSERT INTO dashboard_block_config (block_class, block_config, position, dashboard_id)
            SELECT block_class, block_config, position, '$dashboard_new_id'
            FROM dashboard_block_config
            WHERE dashboard_id = $id";
            $response = sql_query($sql);
        }

        echo $this->json_response($status, $response);
        exit;
    }

    public function editInlineLayout()
    {
        $id = Get::pReq('id', DOTY_INT);
        $col = Get::pReq('col', DOTY_STRING);
        $new_value = Get::pReq('new_value', DOTY_STRING);

        $response = [];

        // Validation
        $errors = [];
        if (!isset($new_value) || !$new_value) {
            $errors[$col] = Lang::t('_VALUE_IS_REQUIRED', 'dashboardsetting');
        }

        if ($errors) {
            $status = 400;
            $response['errors'] = $errors;
        } else {
            $status = 200;
            $response = $this->model->editInlineLayout([
                'id' => $id,
                'col' => $col,
                'new_value' => $new_value,
            ]);
        }

        echo $this->json_response($status, $response);
        exit;
    }

    public function delLayout()
    {
        $status = 400;
        if ($response = $this->model->delLayout(Get::pReq('id_layout'))) {
            $status = 200;
        }

        echo $this->json_response($status, $response);
        exit;
    }

    public function defaultLayout()
    {
        $status = 400;
        if ($response = $this->model->defaultLayout(Get::pReq('id_layout'))) {
            $status = 200;
        }

        echo $this->json_response($status, $response);
        exit;
    }

    private function json_response($code = 200, $message = null)
    {
        header_remove();
        http_response_code($code);
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        header('Content-Type: application/json');

        $status = [
            200 => '200 OK',
            400 => '400 Bad Request',
            422 => 'Unprocessable Entity',
            500 => '500 Internal Server Error'
        ];
        // ok, validation error, or failure
        header('Status: ' . $status[$code]);

        return json_encode($message);
    }

    public function save()
    {
        $dashboard = Get::req('dashboard', DOTY_MIXED);
        $requestSettings = Get::pReq('settings', DOTY_MIXED);

        $response = ['status' => 200];
        foreach ($requestSettings as $data) {

            $block = $data['block'];
            $settings = $data['settings'];

            $valid = DashboardBlockForm::validate($block, $settings);

            if (!empty($valid)) {
                $response['status'] = 400;
                $response['errors'][] = ['block' => $block, 'settings' => $valid];
            }
        }

        if ($response['status'] === 200) {
            $this->model->resetOldSettings($dashboard);

            foreach ($requestSettings as $data) {

                $block = $data['block'];
                $setting = $data['settings'];

                $this->model->saveBlockSetting($block, $setting, $dashboard);
            }
        }

        echo $this->json->encode($response);
    }

    public function uploadFile()
    {
        $response = ['status' => 200];

        $block = Get::gReq('block', DOTY_MIXED);
        $field = Get::gReq('field', DOTY_MIXED);

        //print_r($_FILES);
        //die();

        $exist = DashboardBlockForm::fieldExist($block, $field);

        if (!$exist) {
            $response['status'] = 400;
            $response['error'] = Lang::t('_FIELD_NOT_EXIST', 'dashboardsetting');
        } else {

            $fieldName = 'file';  //DashboardBlockForm::getFieldName($block, $field);
            $path = '/appLms/dashboard';

            if (!is_dir(_base_ . '/files' . $path . '/')) {
                $sts = mkdir(_base_ . '/files' . $path);
            }

            if ($_FILES[$fieldName]['size'] == 0 && $_FILES[$fieldName]['error'] == 0) {
                $response['status'] = 400;
                $response['error'] = Lang::t('_FILE_NOT_VALID', 'dashboardsetting');
            } else {

                $savefile = mt_rand(0, 100) . '_' . time() . '_' . $_FILES[$fieldName]['name'];

                if (!file_exists($GLOBALS['where_files_relative'] . $path . '/' . $savefile)) {
                    sl_open_fileoperations();

                    if (!sl_upload($_FILES[$fieldName]['tmp_name'], $path . '/' . $savefile, $_FILES[$fieldName]['type'])) {
                        sl_close_fileoperations();
                    }

                    sl_close_fileoperations();

                    $response['file'] = $GLOBALS['where_files_relative'] . $path . '/' . $savefile;
                } else {
                    $response['status'] = 400;
                    $response['error'] = Lang::t('_FILE_ALREADY_EXIST', 'dashboardsetting');
                }
            }
        }
        echo $this->json->encode($response);
        die();
    }

    public function getBlockTypeForm()
    {
        $block = Get::req('block', DOTY_STRING, false);
        $index = Get::req('index', DOTY_INT, 99);
        $type = Get::req('type', DOTY_STRING, 'col-1');

        /** @var DashboardBlockLms $blockObj */
        $blockObj = new $block('');

        return $this->render('new-block-form', ['block' => $blockObj->getSettingsCommonViewData(), 'index' => $index, 'type' => $type]);
    }
}
