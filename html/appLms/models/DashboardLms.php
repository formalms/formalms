<?php

defined("IN_FORMA") or die('Direct access is forbidden.');

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
 * Class DashboardLms
 */
class DashboardLms extends Model
{
    /** @var bool|DbConn */
    private $db;

    /** @var DashboardsettingsAdm */
    private $dashboardSettingsModel;

    private $enabledBlocks;

    private $layouts;

    public function  __construct() {

        parent::__construct();
        $this->db = DbConn::getInstance();
        $this->dashboardSettingsModel = new DashboardsettingsAdm();
    }

    /**
     * @return mixed
     */
    public function getEnabledBlocks($dashboardId = false)
    {
        $data = [];
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->dashboardSettingsModel->getEnabledBlocks())) {
            $data = $this->dashboardSettingsModel->getEnabledBlocks()[$dashboardId];
        }
        return $data;
    }

    public function getBlocksViewData($dashboardId = false)
    {
        $data = [];
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->dashboardSettingsModel->getEnabledBlocks())) {
            /** @var DashboardBlockLms $enabledBlock */
            foreach ($this->dashboardSettingsModel->getEnabledBlocks()[$dashboardId] as $enabledBlock) {
                if ($enabledBlock->isEnabled()) {
                    $data[] = $enabledBlock->getViewData();
                }
            }
        }

        return $data;
    }

    /**
     * @param string $block
     * @return bool|DashboardBlockLms
     */
    public function getRegisteredBlock($dashboardId,$block)
    {
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->dashboardSettingsModel->getEnabledBlocks())) {
            foreach ($this->dashboardSettingsModel->getEnabledBlocks()[$dashboardId] as $enabledBlock) {

                if (get_class($enabledBlock) === $block) {
                    return $enabledBlock;
                }
            }
        }
        return null;
    }

    public function getDefaultLayout(){
        /** @var DashboardLayoutLms $layout */
        foreach ($this->dashboardSettingsModel->getLayouts() as $layout){
            if ($layout->isDefault()){
                return $layout;
            }
        }
        return false;
    }
    
    
    // check if current user has access to dashboard-id
    public function currentCanAccessObj($dashboardId) {
        $vett_cache = [];
            
        $query = "SELECT id_dashboard, idst_list FROM dashboard_permission where id_dashboard=".$dashboardId;

       
        $re_query = $this->db->query($query);
        
        while(list($id_dashboard, $idst_list) = sql_fetch_row($re_query)) {            
            $vett_cache[$id_dashboard] = unserialize($idst_list);
        }
                   
        $user_assigned = Docebo::user()->getArrSt();
 
        if(isset($vett_cache[$dashboardId])) {
            if($vett_cache[$dashboardId] == '' || empty($vett_cache[$dashboardId])) return true;
            
            $intersect = array_intersect($user_assigned, $vett_cache[$dashboardId]);
        } else {
            return true;
        }
        
        return !empty($intersect);
    }     
    
    
    public function getListLayout(){
     
        $query = "select id,name from dashboard_layouts";
        $re_query = $this->db->query($query);
        $out = [];
        while(list($id_dashboard, $name) = sql_fetch_row($re_query)) {
            
            $out[$id_dashboard] = $name;
        }        
        
        return $out;
    } 
    
}
