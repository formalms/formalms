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
}
