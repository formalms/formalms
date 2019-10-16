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


Class DashboardSettingsAdmController extends AdmController
{

    /** @var DashboardSettingsAdm $model */
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
        $this->model = new DashboardSettingsAdm();

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
            'installedBlocks' => $this->model->getInstalleddBlocksCommonViewData(),
            'enabledBlocks' => $this->model->getEnabledBlocksCommonViewData()
        ]);
    }
}


?>