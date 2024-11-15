<?php

use FormaLms\lib\Interfaces\Accessible;

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Class DashboardsettingsAdm.
 */
class DashboardsettingsAdm extends Model implements Accessible
{
    protected $db;

    protected $enabledBlocks = [];

    protected $installedBlocks;

    protected $layouts = [];

    public function __construct()
    {
        $this->db = \FormaLms\db\DbConn::getInstance();
        $this->loadLayouts();
        $this->loadInstalledBlocks();
        $this->loadEnabledBlocks();
        parent::__construct();
    }

    public function loadLayouts()
    {
        $query = 'SELECT `dashboard_layouts`.`id`, `name`, `caption`, `status`, `default`, `idst_list` 
                    FROM `dashboard_layouts` 
                    LEFT JOIN `dashboard_permission` ON `dashboard_layouts`.`id` =`dashboard_permission`.`id_dashboard` 
                    ORDER BY  `dashboard_layouts`.`default` DESC,  `dashboard_layouts`.`created_at` ASC';

        $result = sql_query($query) ?: [];

        foreach ($result as $layout) {

            /** @var DashboardLayoutLms $layoutObj */
            $layoutObj = new DashboardLayoutLms();
            $layoutObj->setId($layout['id']);
            $layoutObj->setName($layout['name']);
            $layoutObj->setCaption($layout['caption']);
            $layoutObj->setStatus($layout['status']);
            $layoutObj->setDefault($layout['default']);

            $permissionList = [];

            if (!empty($layout['idst_list'])) {
                $permissionList = unserialize($layout['idst_list'], ['allowed_classes' => ['array']]);
            }
            if (!empty($permissionList)) {
                $layoutObj->setPermissionList($permissionList);
            }

            $this->layouts[] = $layoutObj;
        }

        if (count($this->layouts) === 0) {
            $layout = [
                'name' => 'Default Layout',
                'caption' => 'Default Layout Caption',
                'status' => 'publish',
                'default' => true,
            ];

            $this->saveLayout($layout);

            $layoutObj = new DashboardLayoutLms();
            $layoutObj->setId('');
            $layoutObj->setName($layout['name']);
            $layoutObj->setCaption($layout['caption']);
            $layoutObj->setStatus($layout['status']);
            $layoutObj->setDefault($layout['default']);

            $this->layouts[] = $layoutObj;
        }
    }

    public function getLayout($id)
    {

        return array_filter(
            $this->layouts,
            function ($e) use (&$id) {
                return $e->getId() == $id;
            }
        );
    }

    public function loadEnabledBlocks()
    {
        $query_blocks = 'SELECT `id`, `block_class`, `block_config`, `position`, `dashboard_id` FROM `dashboard_block_config` ORDER BY `position` ASC';

        $result = $this->db->query($query_blocks);

        foreach ($result as $block) {
            if (file_exists(\FormaLms\lib\Forma::inc(_lms_ . '/models/' . $block['block_class'] . '.php'))) {
                /** @var DashboardBlockLms $blockObj */
                $blockObj = new $block['block_class']($block['block_config']);
                $blockObj->setOrder($block['position']);

                $this->enabledBlocks[$block['dashboard_id']][] = $blockObj;
            }
        }
    }

    public function loadInstalledBlocks()
    {
        $query_blocks = 'SELECT `id`, `block_class` FROM `dashboard_blocks`';

        $result = $this->db->query($query_blocks);

        foreach ($result as $block) {
            if (file_exists(\FormaLms\lib\Forma::inc(_lms_ . '/models/' . $block['block_class'] . '.php'))) {
                require_once \FormaLms\lib\Forma::inc(_lms_ . '/models/' . $block['block_class'] . '.php');
                /** @var DashboardBlockLms $blockObj */
                $blockObj = new $block['block_class']('');

                $this->installedBlocks[] = $blockObj;
            }
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

    public function getEnabledBlocksCommonViewData($dashboardId = false)
    {
        $data = [];
        if (false !== $dashboardId && array_key_exists($dashboardId, $this->enabledBlocks)) {
            /** @var DashboardBlockLms $enabledBlocks */
            foreach ($this->enabledBlocks[$dashboardId] as $enabledBlocks) {
                $data[] = $enabledBlocks->getSettingsCommonViewData();
            }
        }

        return $data;
    }

    public function getInstalledBlocksCommonViewData()
    {
        $data = [];
        /** @var DashboardBlockLms $installedBlock */
        foreach ($this->installedBlocks as $installedBlock) {
            $data[] = $installedBlock->getSettingsCommonViewData();
        }

        return $data;
    }

    public function resetOldSettings($dashboard)
    {
        $query_blocks = sprintf('DELETE FROM dashboard_block_config WHERE `dashboard_id` = %s;', $dashboard);

        $this->db->query($query_blocks);
    }

    public function saveLayout($layout)
    {

        $name = $layout['name'];
        $caption = $layout['caption'] ?: ' ';
        $status = $layout['status'];
        $default = ($layout['default'] === true || $layout['default'] === 1);

        $query = 'SELECT COUNT(*) AS count FROM `dashboard_layouts`';
        $res = $this->db->query($query);
        $res = sql_fetch_array($res);
        if ($res['count'] && $default === false) {
            $default = $res['count'] ? 0 : 1;
        }

        $sql = "INSERT INTO `dashboard_layouts` ( `name`, `caption`, `status`, `default`, `created_at`) 
            VALUES ( '" . addslashes($name) . "', '" . addslashes($caption) . "', '" . addslashes($status) . "', " . $default . ', CURRENT_TIMESTAMP)';

        return sql_query($sql);
    }

    public function editInlineLayout($data)
    {
        $query = 'UPDATE `dashboard_layouts` SET ' . $data['col'] . " = '" . addslashes($data['new_value']) . "' WHERE id = " . $data['id'];

        return $this->db->query($query);
    }

    public function delLayout($id_layout)
    {
        // delete permission
        $query = "DELETE FROM dashboard_permission WHERE id_dashboard = $id_layout";
        $this->db->query($query);

        $query = "DELETE FROM `dashboard_layouts` WHERE id = $id_layout";

        return $this->db->query($query);
    }

    public function defaultLayout($id_layout)
    {
        $query = 'UPDATE `dashboard_layouts` SET `default` = 0';
        $this->db->query($query);

        $query = "UPDATE `dashboard_layouts` SET `default` = 1, `status` = 'publish' WHERE id = $id_layout";

        return $this->db->query($query);
    }

    public function saveBlockSetting($block, $setting, $dashboard)
    {

        $config = [
            'type' => $setting['type'],
            'enabled' => $setting['enabled'],
            'enabledActions' => $setting['enabledActions'],
            'data' => $setting['data'],
        ];
        $insertQuery = sprintf("INSERT INTO `dashboard_block_config` ( `block_class`, `block_config`, `position`, `dashboard_id`, `created_at`) VALUES ( '%s' , '%s', '%s', '%s', CURRENT_TIMESTAMP)", $block, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_APOS), $setting['position'], $dashboard);


        $this->db->query($insertQuery);
    }

    /**
     * @return mixed
     */
    public function getLayouts()
    {
        return $this->layouts;
    }

    // check permission dashboard
    public function setObjIdstList($dashboardId, $idst_list)
    {
        $idst_list = serialize($idst_list);

        $query = 'SELECT id_dashboard FROM dashboard_permission WHERE id_dashboard = ' . $dashboardId;

        $exists = sql_num_rows($this->db->query($query));

        if (!$exists) {
            $query = 'INSERT INTO dashboard_permission ( id_dashboard, idst_list) VALUES ( ' . $dashboardId . ", '" . $idst_list . "' ) ";
        } else {
            $query = "UPDATE dashboard_permission  SET idst_list = '" . $idst_list . "'   WHERE id_dashboard = " . $dashboardId;
        }

        return $this->db->query($query);
    }

    // get user list permission of dashboard
    public function getObjIdstList($dashboardId)
    {
        $query = 'SELECT idst_list FROM dashboard_permission WHERE id_dashboard= ' . $dashboardId;

        $re_query = $this->db->query($query);
        if (!$re_query) {
            return false;
        }

        list($idst_list) = sql_fetch_row($re_query);

        if ($idst_list && is_string($idst_list)) {
            return unserialize(($idst_list));
        }

        return [];
    }

    public function getDefaultLayout()
    {
        foreach ($this->layouts as $layout) {
            if ($layout->isDefault()) {
                return $layout->getId();
            }
        }
        return 0;
    }

    public function getAccessList($resourceId): array
    {

        return $this->getObjIdstList($resourceId);
    }

    public function setAccessList($resourceId, array $selection): bool
    {

        return (bool)$this->setObjIdstList((int)$resourceId, $selection);

    }

}
