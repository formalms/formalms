<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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
		
		$branch_name = [];
    foreach($groups_id as $id => $groupid)
    {
			
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
