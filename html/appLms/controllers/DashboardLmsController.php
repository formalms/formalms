<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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
require_once(_base_ . '/lib/lib.json.php');

/**
 * Class DashboardLmsController
 */
class DashboardLmsController extends LmsController
{
    public $name = '';

    /** @var DashboardLms */
    private $model;

    /**
     * DashboardLmsController constructor.
     * @param $mvc_name
     */
    public function init()
    {
        $this->_mvc_name = "dashboard";
        $this->permissions = array(
            'view' => true,
            'mod' => true
        );
        /** @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->model = new DashboardLms();
    }

    public function show()
    {
        checkPerm('view', true, $this->_mvc_name);
        $defaultLayout = $this->model->getDefaultLayout();
        
        // manage permission template
        $idTemplate = $defaultLayout->getId();
        $listLayout = $this->model->getListLayout();
        foreach($listLayout as $key => $name){
            $check_perm = $this->model->currentCanAccessObj($key);    
            if($check_perm){
                $idTemplate = $key;                       
                  break; 
            }      
        }
        
        
        $blocks = [];
        $blockPaths = [];
        
        
        
        if ($defaultLayout) {
            $blocks = $this->model->getBlocksViewData($idTemplate);

            /** @var DashboardBlockLms $block */
            foreach ($this->model->getEnabledBlocks($idTemplate) as $block){
                $blockPaths[] = $block->getViewPath();
            }
        }
        
        
        

        $langModel = new LangAdm();
        $langCode = $langModel->getLanguage(Lang::get())->lang_browsercode;

        $this->render(
            'dashboard',
            [
                'blocks' => $blocks,
                'templatePath' => getPathTemplate(),
                'dashboardLayoutId' => $defaultLayout ? $defaultLayout->getId() : null,
                'lang' => $langCode,
            ],
            false,
            $blockPaths
        );
    }

    public function ajaxAction()
    {
        $result = ['status' => 200];
        $blockParameter = Get::pReq('block', DOTY_MIXED);
        $actionParameter = Get::pReq('blockAction', DOTY_MIXED);
        $dashboardLayoutIdParameter = Get::pReq('dashboardLayoutId', DOTY_MIXED);

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
        die();
    }
}
