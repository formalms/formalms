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

require_once _base_ . '/lib/lib.json.php';

/**
 * Class DashboardLmsController.
 */
class DashboardLmsController extends LmsController
{
    public $name = '';

    /** @var DashboardLms */
    public $model;
    public Services_JSON $json;
    /**
     * @var array|true[]
     */
    public array $permissions;

    /**
     * DashboardLmsController constructor.
     *
     * @param $mvc_name
     */
    public function init()
    {
        $this->_mvc_name = 'dashboard';
        $this->permissions = [
            'view' => true,
            'mod' => true,
        ];
        /* @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->model = new DashboardLms();
    }

    public function show()
    {
        checkPerm('view', true, $this->_mvc_name);
       
        $blocks = [];
        $blockPaths = [];
        $defaultLayout = null;

        if (FormaLms\lib\Get::req('mycourses_tab', DOTY_STRING, null)) {
            $this->widget('lms_tab', [
                'active' => 'dashboard',
                'close' => false,
            ]);
        }

        $layouts = $this->model->getLayouts();
        /** @var DashboardLayoutLms $layout */
        foreach ($layouts as $layout) {
            if (!$layout->isDefault() && $layout->userCanAccess(\FormaLms\lib\FormaUser::getCurrentUser())) {
                $defaultLayout = $layout;
                break;
            }
        }

        if(!$defaultLayout) {
            $defaultLayout = $this->model->getDefaultLayout();
        }


        if ($defaultLayout) {
            $blocks = $this->model->getBlocksViewData($defaultLayout->getId());

            /** @var DashboardBlockLms $block */
            $enabledBlocks = $this->model->getEnabledBlocks($defaultLayout->getId());
            foreach ($enabledBlocks as $block) {
                $blockPaths[] = $block->getViewPath();
            }
        }

        $this->render(
            'dashboard',
            [
                'blocks' => $blocks,
                'templatePath' => getPathTemplate(),
                'dashboardLayoutId' => $defaultLayout ? $defaultLayout->getId() : null,
            ],
            false,
            $blockPaths
        );
    }

    public function ajaxAction()
    {
        $result = ['status' => 200];
        $result['response'] = [];
        $blockParameter = FormaLms\lib\Get::pReq('block', DOTY_MIXED);
        $actionParameter = FormaLms\lib\Get::pReq('blockAction', DOTY_MIXED);
        $dashboardLayoutIdParameter = FormaLms\lib\Get::pReq('dashboardLayoutId', DOTY_MIXED);

        $block = $this->model->getRegisteredBlock($dashboardLayoutIdParameter, $blockParameter);
        if (null !== $block) {
            if (method_exists($block, $actionParameter)) {
                $result['response'] = $block->$actionParameter();
            } else {
                $result['status'] = 400;
            }
        } else {
            $result['status'] = 400;
        }

        echo json_encode($result);
        exit();
    }
}
