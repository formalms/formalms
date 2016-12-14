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

/**
 * @package  admin-library
 * @subpackage user
 * @version 	$Id:$
 */
 
require_once($GLOBALS['where_framework'].'/modules/org_chart/tree.org_chart.php');

define("ORG_CHART_NORMAL", 1);
define("ORG_CHART_WITH_DESCENDANTS", 2);

class OrgChartManager {

	var $tree_db	= false;
	var $tree_view 	= false;

	function OrgChartManager() {
		
		$this->tree_db 		= new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
		$this->tree_view 	= new TreeView_OrgView($this->tree_db, 'organization_chart', Get::sett('title_organigram_chart'));
	}

	function getFolderFormIdst(&$arr_idst) {
		
		$acl_man 	=& Docebo::user()->getAclManager();
		$groups_id = $acl_man->getGroupsId($arr_idst);
		
		$folder_name = $this->tree_db->getFoldersCurrTranslationDoubleCheck($groups_id);
		
		$branch_name = array();
		while(list($id, $groupid) = each($groups_id)) {
			
			$id_dir = explode('_', $groupid);
			$branch_name[$id]['name'] = $folder_name[$id_dir[1]];
			$branch_name[$id]['type_of_folder'] = ( $id_dir[0] == '/oc' ? ORG_CHART_NORMAL : ORG_CHART_WITH_DESCENDANTS );
		}
		return $branch_name;
	}
	
	function getAllGroupIdFolder() {
		
		return $this->tree_db->getAllGroupST();
	}
	
}

?>
