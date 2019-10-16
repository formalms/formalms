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

class DashboardSettingsAdm extends Model
{
    protected $db;

    protected $enabledBlocks;

    protected $installedBlocks;

    public function __construct()
    {
        $this->db = DbConn::getInstance();
        $this->loadInstalledBlocks();
        $this->loadEnabledBlocks();
    }

    public function loadEnabledBlocks()
    {
        $query_blocks = "SELECT `id`, `block_class`, `block_config`, `position` FROM `dashboard_block_config` ORDER BY `position` ASC";

        $result = $this->db->query($query_blocks);

        while ($block = $this->db->fetch_assoc($result)) {
            /** @var DashboardBlockLms $blockObj */
            $blockObj = new $block['block_class']($block['block_config']);
            $blockObj->setOrder($block['position']);

            $this->enabledBlocks[] = $blockObj;
        }
    }

    public function loadInstalledBlocks()
    {

        $query_blocks = "SELECT `id`, `block_class` FROM `dashboard_blocks`";

        $result = $this->db->query($query_blocks);

        while ($block = $this->db->fetch_assoc($result)) {
            /** @var DashboardBlockLms $blockObj */
            $blockObj = new $block['block_class']('');

            $this->installedBlocks[] = $blockObj;
        }
    }

    /**
     * @return mixed
     */
    public function getEnabledBlocks()
    {
        return $this->enabledBlocks;
    }

    /**
     * @return mixed
     */
    public function getInstalledBlocks()
    {
        return $this->installedBlocks;
    }

    public function getEnabledBlocksCommonViewData()
    {
        $data = [];
        /** @var DashboardBlockLms $enabledBlocks */
        foreach ($this->enabledBlocks as $enabledBlocks) {
            $data[] = $enabledBlocks->getCommonViewData();
        }

        return $data;
    }

    public function getInstalleddBlocksCommonViewData()
    {
        $data = [];
        /** @var DashboardBlockLms $enabledBlocks */
        foreach ($this->installedBlocks as $installedBlock) {
            $data[] = $installedBlock->getCommonViewData();
        }

        return $data;
    }
}
