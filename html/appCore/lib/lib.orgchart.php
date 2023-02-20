<?php

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
 * @version 	$Id:$
 */
require_once _adm_ . '/modules/org_chart/tree.org_chart.php';

define('ORG_CHART_NORMAL', 1);
define('ORG_CHART_WITH_DESCENDANTS', 2);

class OrgChartManager
{
    public $tree_db = false;
    public $tree_view = false;

    public function OrgChartManager()
    {
        $this->tree_db = new TreeDb_OrgDb($GLOBALS['prefix_fw'] . '_org_chart_tree');
        $this->tree_view = new TreeView_OrgView($this->tree_db, 'organization_chart', FormaLms\lib\Get::sett('title_organigram_chart'));
    }

    public function getFolderFormIdst(&$arr_idst)
    {
        $acl_man = &Docebo::user()->getAclManager();
        $groups_id = $acl_man->getGroupsId($arr_idst);

        $folder_name = $this->tree_db->getFoldersCurrTranslationDoubleCheck($groups_id);

        $branch_name = [];
        foreach ($groups_id as $id => $groupid) {
            $id_dir = explode('_', $groupid);
            $branch_name[$id]['name'] = $folder_name[$id_dir[1]];
            $branch_name[$id]['type_of_folder'] = ($id_dir[0] == '/oc' ? ORG_CHART_NORMAL : ORG_CHART_WITH_DESCENDANTS);
        }

        return $branch_name;
    }

    public function getAllGroupIdFolder()
    {
        return $this->tree_db->getAllGroupST();
    }
}
