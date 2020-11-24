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

    private $enabledBlocks;

    private $installedBlocks;

    public function  __construct() {

        parent::__construct();
        $this->db = DbConn::getInstance();
        $this->loadBlocks();
    }

    private function loadBlocks()
    {
        $query_blocks = "SELECT `id`, `block_class`, `block_config`, `position`, `dashboard_id` FROM `dashboard_block_config` ORDER BY `position` ASC";

        $result = $this->db->query($query_blocks);

        while ($block = $this->db->fetch_assoc($result)) {
            /** @var DashboardBlockLms $blockObj */
            $blockObj = new $block['block_class']($block['block_config']);
            $blockObj->setOrder($block['position']);

            $this->enabledBlocks[$block['dashboard_id']] = $blockObj;
        }
    }

    /**
     * @return mixed
     */
    public function getEnabledBlocks()
    {
        return $this->enabledBlocks;
    }

    public function getBlocksViewData($dashboardId = false)
    {
        $data = [];
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->enabledBlocks)) {
            /** @var DashboardBlockLms $enabledBlock */
            foreach ($this->enabledBlocks[$dashboardId] as $enabledBlock) {
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
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->enabledBlocks)) {
            foreach ($this->enabledBlocks[$dashboardId] as $enabledBlock) {

                if (get_class($enabledBlock) === $block) {
                    return $enabledBlock;
                }
            }
        }
        return null;
    }
}
