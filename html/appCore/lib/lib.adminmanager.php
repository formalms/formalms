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
 * @package admin-core
 * @subpackage user
 * @version  $Id:$
 */
 
class AdminManager {

	/** the database istance */
	var $db = null;
	/** the connection to database */
	var $dbconn = FALSE;	
	/** the tables prefix */
	var $prefix = FALSE;

	function getAdminTreeTable() { return '%adm_admin_tree'; }

	function _executeQuery( $query ) {
		if( $this->dbconn === NULL )
			$rs = $this->db->query( $query );
		else
			$rs = $this->db->query( $query, $this->dbconn );
		return $rs;
	}
	
	function _executeInsert( $query ) {
		if( $this->dbconn === NULL ) {
			if( !sql_query( $query ) ) 
				return FALSE;
		} else {
			if( !sql_query( $query, $this->dbconn ) )
				return FALSE;
		}
		if( $this->dbconn === NULL )
			return $this->db->insert_id();
		else
			return $this->db->insert_id($this->dbconn);
	}
	/**
	 * constructor
	 * @param mixed $dbconn the connection to database or FALSE to use default connection
	 * @param mixed $prefix the prefix of the database or FLASE to use default prefix
	 */
	function AdminManager( $dbconn = FALSE, $prefix = FALSE ) {
		$this->db = DbConn::getInstance();
		$this->dbconn = ($dbconn === FALSE)?$GLOBALS['dbConn']:$dbconn;
		$this->prefix = ($prefix === FALSE)?$GLOBALS['prefix_fw']:$prefix;
	}
	
	function getAdminTree( $adminidst ) {
		$query = "SELECT idst FROM ".AdminManager::getAdminTreeTable()
				." WHERE idstAdmin = '".(int)$adminidst."'";
		$rs = $this->_executeQuery( $query );
		$result = array();
		if( $this->db->num_rows( $rs ) > 0 ) {
			while(list($idstTree) = $this->db->fetch_row($rs)) {
				$result[] = $idstTree;
			}
			return $result;
		} else
			return $result;
	}

	/**
	 * add an admin to a node of org tree
	 * @param int $treeidst the idst of the tree to add
	 * @param int $adminidst the security token of the administrator
	 */
	function addAdminTree( $treeidst, $adminidst ) {
		$query = "INSERT INTO ".AdminManager::getAdminTreeTable()
				." (idst, idstAdmin) VALUES "
				." ('".$treeidst."','".$adminidst."')";

		$this->_executeQuery( $query );
	}
	
		
	/**
	 * remove an admin from a node of org tree
	 * @param int $treeidst the idst of the tree to add
	 * @param int $adminidst the security token of the administrator
	 */
	function removeAdminTree( $treeidst, $adminidst ) {
		$query = "DELETE FROM ".AdminManager::getAdminTreeTable()
				." WHERE idst = '".$treeidst."'"
				."   AND idstAdmin = '".$adminidst."'";
		$this->_executeQuery( $query );
	}
	
	
	function &getAdminPermission($adminidst) {
		
		$acl_manager 	=& Docebo::user()->getAclManager();
		$permission 	= $acl_manager->getRolesContainer($adminidst, true);
		return $permission;
	}
	
	function &fromRolePathToIdst($base_path, $module_tokens, $flip = false) {
		
		$acl_man =& Docebo::user()->getAclManager();
		$map = array();
		foreach($module_tokens as $k => $token ) {
			
			$code 		= $token['code'];
			$role_info 	= $acl_man->getRole(FALSE, $base_path.'/'.$code);
			if($role_info === FALSE) {
				$id_role = $acl_man->registerRole($base_path.'/'.$code, '');
			} else {
				$id_role = $role_info[ACL_INFO_IDST];
			}
			if($flip === false) $map[$code]	= $id_role;
			else $map[$id_role] = $code;
		}
		return $map;
	}
	
	function &modulePermissionAsToken($all_admin_permission, $all_module_idst) {
		
		$token = array();
		foreach($all_module_idst as $code => $idst ) {
			
			if(isset($all_admin_permission[$idst])) {
				
				$token[$code] = $idst;
			}
		}
		return $token;
	}
	
	
	function &convertTokenToIdst($token_to_convert, $map_convert, $flip = false) {
		
		$acl_man =& Docebo::user()->getAclManager();
		$map = array();
		foreach($token_to_convert as $code => $v ) {
			
			$id_role = $map_convert[$code];
			if($flip === false) $map[$code]	= $id_role;
			else $map[$id_role] = $code;
		}
		return $map;
	}
	
	function addRoleToAdmin($token_to_add, $adminidst) {
		
		$acl_manager =& Docebo::user()->getAclManager();
		$re = true;
		foreach($token_to_add as $code => $idst_role ) {
			
			$re &= $acl_manager->addToRole($idst_role, $adminidst);
		}
		return $re;
	}
	
	function delRoleToAdmin($token_to_remove, $adminidst) {
		
		$acl_manager =& Docebo::user()->getAclManager();
		$re = true;
		foreach($token_to_remove as $code => $idst_role ) {
			
			$re &= $acl_manager->removeFromRole($idst_role, $adminidst);
		}
		return $re;
	}
}

?>