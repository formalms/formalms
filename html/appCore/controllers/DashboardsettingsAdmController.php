<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

/**
 * Class DashboardsettingsAdmController
 */
Class DashboardsettingsAdmController extends AdmController
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

        $this->permissions = array(
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
        );
    }


    //----------------------------------------------------------------------------


    public function show()
    {
        //render view
        $this->render('show', [
            'ajaxUrl' => 'ajax.adm_server.php?r=adm/dashboardsettings/save',
            'installedBlocks' => $this->model->getInstalledBlocksCommonViewData(),
            'enabledBlocks' => $this->model->getEnabledBlocksCommonViewData(),
            'templatePath' => getPathTemplate()
        ]);

    }

    public function save()
    {
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
            $this->model->resetOldSettings();

            foreach ($requestSettings as $data) {

                $block = $data['block'];
                $setting = $data['settings'];

                $this->model->saveBlockSetting($block, $setting);
            }
        }

        echo $this->json->encode($response);
    }

    public function uploadFile()
    {
        $response = ['status' => 200];

        $block = Get::pReq('block', DOTY_MIXED);
        $field = Get::pReq('field', DOTY_MIXED);

        $exist = DashboardBlockForm::fieldExist($block, $field);
        if (!$exist) {
            $response['status'] = 400;
            $response['error'] = Lang::t('_FIELD_NOT_EXIST', 'dashboardsetting');
        } else {

            $path = '/appLms/dashboard';

            if (!is_dir(_base_ . '/files' . $path . '/')) {
                $sts = mkdir(_base_ . '/files' . $path);
            }

            if ($_FILES[$field]['size'] == 0 && $_FILES[$field]['error'] == 0) {
                $response['status'] = 400;
                $response['error'] = Lang::t('_FIELD_NOT_EXIST', 'dashboardsetting');
            }
            else {

                $savefile = mt_rand(0, 100) . '_' . time() . '_' . $_FILES[$field]['name'];

                if (!file_exists($GLOBALS['where_files_relative'] . $path . $savefile)) {
                    sl_open_fileoperations();

                    if (!sl_upload($_FILES[$field]['tmp_name'], $path . $savefile)) {
                        sl_close_fileoperations();
                    }

                    sl_close_fileoperations();

                    $response['file'] = $GLOBALS['where_files_relative'] . $path . $savefile;
                }
                else {
                    $response['status'] = 400;
                    $response['error'] = Lang::t('_FILE_ALREADY_EXIST', 'dashboardsetting');
                }
            }
        }
        echo $this->json->encode($response);
    }
}


?>
