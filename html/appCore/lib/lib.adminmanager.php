<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version  $Id:$
 */
class AdminManager
{
    /** the database istance */
    public $db = null;
    /** the connection to database */
    public $dbconn = false;
    /** the tables prefix */
    public $prefix = false;

    public function getAdminTreeTable()
    {
        return '%adm_admin_tree';
    }

    public function _executeQuery($query)
    {
        if ($this->dbconn === null) {
            $rs = $this->db->query($query);
        } else {
            $rs = $this->db->query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _executeInsert($query)
    {
        if ($this->dbconn === null) {
            if (!sql_query($query)) {
                return false;
            }
        } else {
            if (!sql_query($query, $this->dbconn)) {
                return false;
            }
        }
        if ($this->dbconn === null) {
            return $this->db->insert_id();
        } else {
            return $this->db->insert_id($this->dbconn);
        }
    }

    /**
     * constructor.
     *
     * @param mixed $dbconn the connection to database or FALSE to use default connection
     * @param mixed $prefix the prefix of the database or FLASE to use default prefix
     */
    public function AdminManager($dbconn = false, $prefix = false)
    {
        $this->db = DbConn::getInstance();
        $this->dbconn = ($dbconn === false) ? $GLOBALS['dbConn'] : $dbconn;
        $this->prefix = ($prefix === false) ? $GLOBALS['prefix_fw'] : $prefix;
    }

    public function getAdminTree($adminidst)
    {
        $query = 'SELECT idst FROM ' . AdminManager::getAdminTreeTable()
                . " WHERE idstAdmin = '" . (int) $adminidst . "'";
        $rs = $this->_executeQuery($query);
        $result = [];
        if ($this->db->num_rows($rs) > 0) {
            while (list($idstTree) = $this->db->fetch_row($rs)) {
                $result[] = $idstTree;
            }

            return $result;
        } else {
            return $result;
        }
    }

    /**
     * add an admin to a node of org tree.
     *
     * @param int $treeidst  the idst of the tree to add
     * @param int $adminidst the security token of the administrator
     */
    public function addAdminTree($treeidst, $adminidst)
    {
        $query = 'INSERT INTO ' . AdminManager::getAdminTreeTable()
                . ' (idst, idstAdmin) VALUES '
                . " ('" . $treeidst . "','" . $adminidst . "')";

        $this->_executeQuery($query);
    }

    /**
     * remove an admin from a node of org tree.
     *
     * @param int $treeidst  the idst of the tree to add
     * @param int $adminidst the security token of the administrator
     */
    public function removeAdminTree($treeidst, $adminidst)
    {
        $query = 'DELETE FROM ' . AdminManager::getAdminTreeTable()
                . " WHERE idst = '" . $treeidst . "'"
                . "   AND idstAdmin = '" . $adminidst . "'";
        $this->_executeQuery($query);
    }

    public function &getAdminPermission($adminidst)
    {
        $acl_manager = &Docebo::user()->getAclManager();
        $permission = $acl_manager->getRolesContainer($adminidst, true);

        return $permission;
    }

    public function &fromRolePathToIdst($base_path, $module_tokens, $flip = false)
    {
        $acl_man = &Docebo::user()->getAclManager();
        $map = [];
        foreach ($module_tokens as $k => $token) {
            $code = $token['code'];
            $role_info = $acl_man->getRole(false, $base_path . '/' . $code);
            if ($role_info === false) {
                $id_role = $acl_man->registerRole($base_path . '/' . $code, '');
            } else {
                $id_role = $role_info[ACL_INFO_IDST];
            }
            if ($flip === false) {
                $map[$code] = $id_role;
            } else {
                $map[$id_role] = $code;
            }
        }

        return $map;
    }

    public function &modulePermissionAsToken($all_admin_permission, $all_module_idst)
    {
        $token = [];
        foreach ($all_module_idst as $code => $idst) {
            if (isset($all_admin_permission[$idst])) {
                $token[$code] = $idst;
            }
        }

        return $token;
    }

    public function &convertTokenToIdst($token_to_convert, $map_convert, $flip = false)
    {
        $acl_man = &Docebo::user()->getAclManager();
        $map = [];
        foreach ($token_to_convert as $code => $v) {
            $id_role = $map_convert[$code];
            if ($flip === false) {
                $map[$code] = $id_role;
            } else {
                $map[$id_role] = $code;
            }
        }

        return $map;
    }

    public function addRoleToAdmin($token_to_add, $adminidst)
    {
        $acl_manager = &Docebo::user()->getAclManager();
        $re = true;
        foreach ($token_to_add as $code => $idst_role) {
            $re &= $acl_manager->addToRole($idst_role, $adminidst);
        }

        return $re;
    }

    public function delRoleToAdmin($token_to_remove, $adminidst)
    {
        $acl_manager = &Docebo::user()->getAclManager();
        $re = true;
        foreach ($token_to_remove as $code => $idst_role) {
            $re &= $acl_manager->removeFromRole($idst_role, $adminidst);
        }

        return $re;
    }
}
