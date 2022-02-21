<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class MenuManager {
	
	var $acl;
	
	var $acl_man;
	
	function MenuManager() {
		
		$this->acl		=& Docebo::user()->getAcl();
		$this->acl_man	=& Docebo::user()->getAclManager();
	}
	
	function addPerm($groupid, $roleid) {
		
		$group 		= $this->acl_man->getGroup(false, $groupid);
		$idst_group	= $group[ACL_INFO_IDST];
		$role 		= $this->acl_man->getRole(false, $roleid);
		$id_role 	= $role[ACL_INFO_IDST];
		$this->acl_man->addToRole($id_role, $idst_group);
	}
	
	function removePerm($groupid, $roleid) {
	
		$group 		= $this->acl_man->getGroup(false, $groupid);
		$idst_group	= $group[ACL_INFO_IDST];
		$role 		= $this->acl_man->getRole(false, $roleid);
		$id_role 	= $role[ACL_INFO_IDST];
		$this->acl_man->removeFromRole($id_role, $idst_group);
	}
}


?>