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

require_once _base_ . '/lib/lib.aclmanager.php';

/**
 * Acl common tasks class
 * This class is only for check permissions
 * To manage ACLs we must use DoceboACLManager.
 *
 * NOTE: ST means Security Token
 *
 * @category ACL
 *
 * @author   Emanuele Sandri <esandri @ tiscali . it>
 *
 * @version  $Id: lib.acl.php 852 2006-12-16 14:04:44Z giovanni $
 */
class DoceboACL
{
    /** Instance of DoceboACLManager */
    public $aclManager = null;

    /**
     * constructor.
     *
     * @param mixed $dbconn the connection to database or FALSE to use default connection
     * @param mixed $prefix the prefix of the database or FLASE to use default prefix
     */
    public function __construct($dbconn = false, $prefix = false)
    {
        $this->aclManager = new DoceboACLManager($dbconn, $prefix);
    }

    public function getACLManager()
    {
        return $this->aclManager;
    }

    /* NOTE: functions for retrieve single security token */
    /**
     * get security token of an user.
     *
     * @param string $userid id of the user
     *
     * @return mixed security token associated to user or FALSE
     **/
    public function getUserST($userid)
    {
        $arr = $this->aclManager->getUser(false, $userid);
        if ($arr === false) {
            return false;
        }

        return $arr[ACL_INFO_IDST];
    }

    /**
     * get security token of a group.
     *
     * @param string $groupid id of the group
     *
     * @return mixed security token associated to groups or FALSE
     **/
    public function getGroupST($groupid)
    {
        $arr = $this->aclManager->getGroup(false, $groupid);
        if ($arr === false) {
            return false;
        }

        return $arr[ACL_INFO_IDST];
    }

    /**
     * get security token of a role.
     *
     * @param string $roleid id of the role
     *
     * @return mixed security token associated to role or FALSE
     **/
    public function getRoleST($roleid)
    {
        $arr = $this->aclManager->getRole(false, $roleid);
        if ($arr === false) {
            return false;
        }

        return $arr[ACL_INFO_IDST];
    }

    /* NOTE: functions for retrieve all security token */
    /**
     * get all security token of a user.
     *
     * @param string $userid id of the user
     * @param string $filter (Optional). Filter to applay.
     *
     * @return mixed array of security tokens associated to the user or FALSE
     **/
    public function getUserAllST($userid, $filter = '', $user_idst = false)
    {
        if ($user_idst == false) {
            $user_idst = $this->getUserST($userid);
        }

        $arrST = [$user_idst];
        $arrRoles = [];
        $new_st = [$user_idst];
        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->aclManager->getGroupsAllContainer($new_st, $filter);

            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        $new_st = $arrST;
        $loop_check = 0;

        do {
            ++$loop_check;
            $new_st = $this->aclManager->getRolesAllContainer($new_st);

            if (!empty($new_st)) {
                $arrRoles = array_merge($arrRoles, array_diff($new_st, $arrRoles));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        $arrST = array_merge($arrST, $arrRoles);

        return $arrST;
    }

    /**
     * get all security token of a group.
     *
     * @param string $groupid id of the group
     * @param string $filter  (Optional). Filter to applay.
     *
     * @return mixed array of security tokens associated to the group or FALSE
     **/
    public function getGroupAllST($groupid, $filter = '')
    {
        $group_idst = $this->getGroupST($groupid);

        $arrST = [$group_idst];
        $arrRoles = [];
        $new_st = [$group_idst];
        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->aclManager->getGroupsAllContainer($new_st, $filter);

            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        $new_st = $arrST;
        $loop_check = 0;

        do {
            ++$loop_check;
            $new_st = $this->aclManager->getRolesAllContainer($new_st);

            if (!empty($new_st)) {
                $arrRoles = array_merge($arrRoles, array_diff($new_st, $arrRoles));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        $arrST = array_merge($arrST, $arrRoles);

        return $arrST;
    }

    /* NOTE: functions for retrieve groups */
    /**
     * get all groups of a idst.
     *
     * @param int    $idst   idst
     * @param string $filter (Optional). Filter to applay.
     *
     * @return mixed array of groups of the user or FALSE
     **/
    public function getSTGroupsST($idst, $filter = '')
    {
        /*$arrST = $this->aclManager->getGroupsContainer( $idst, $filter );
        // search in groups
        $count = 0;
        while( $count < count( $arrST ) ) {
            $idST = $arrST[$count];
            $arrResult = $this->aclManager->getGroupsContainer( $idST, $filter );
            $arrST = array_merge( $arrST, array_diff($arrResult, $arrST ));
            $count++;
        }*/
        $arrST = [$idst];
        $arrRoles = [];
        $new_st = [$idst];
        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->aclManager->getGroupsAllContainer($new_st, $filter);

            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        return $arrST;
    }

    /**
     * get all groups of an array of idst.
     *
     * @param int    $arr_idst idst
     * @param string $filter   (Optional). Filter to applay.
     *
     * @return mixed array of groups of the user or FALSE
     **/
    public function getArrSTGroupsST($arr_idst, $filter = '')
    {
        $arrST = array_values($arr_idst);
        $arrRoles = [];
        $new_st = array_values($arr_idst);
        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->aclManager->getGroupsAllContainer($new_st, $filter);

            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        return $arrST;
    }

    /**
     * get all groups of a idst_user.
     *
     * @param int    $idst_user idst
     * @param string $filter    (Optional). Filter to applay.
     *
     * @return mixed array of groups of the user or FALSE
     **/
    public function getUserGroupsST($idst_user, $filter = '')
    {
        return $this->getSTGroupsST($idst_user, $filter);
    }

    /**
     * get all groups of a idst_group.
     *
     * @param int    $idst_group idst
     * @param string $filter     (Optional). Filter to applay.
     *
     * @return mixed array of groups of the user or FALSE
     **/
    public function getGroupGroupsST($idst_group, $filter = '')
    {
        return $this->getSTGroupsST($idst_group, $filter);
    }

    /**
     * get all groups of a group.
     *
     * @param int $idst idst of the group
     *
     * @return mixed array of groups of the group or FALSE
     **/
    public function getGroupGDescendants($idst)
    {
        return $this->aclManager->getGroupGDescendants($idst);
    }

    /**
     * get all descendant users of a group.
     *
     * @param int $idst idst of the group
     *
     * @return mixed array of users of the group or FALSE
     **/
    public function getGroupUDescendants($idst)
    {
        return $this->aclManager->getGroupUDescendants($idst);
    }

    /* NOTE: functions for retrieve roles */
    /**
     * get all roles of a user.
     *
     * @param string $userid id of the user
     *
     * @return mixed array of roles of the user or FALSE
     **/
    public function getUserRoles($userid)
    {
    }

    /**
     * get all roles of a group.
     *
     * @param string $groupid id of the group
     *
     * @return mixed array of roles of the group or FALSE
     **/
    public function getGroupRoles($groupid)
    {
    }

    /* NOTE: functions to test match with single security token */
    public function _searchMatch($idst, $idstMatch)
    {
        if (in_array($idst, $idstMatch)) {
            return true;
        }

        $arrST = [$idst];
        $arrRoles = [];
        $new_st = [$idst];
        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->aclManager->getGroupsAllContainer($new_st, '');
            if (count(array_intersect($idstMatch, $new_st)) > 0) {
                return true;
            }

            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        $new_st = $arrST;
        $loop_check = 0;

        do {
            ++$loop_check;
            $new_st = $this->aclManager->getRolesAllContainer($new_st);
            if (count(array_intersect($idstMatch, $new_st)) > 0) {
                return true;
            }

            if (!empty($new_st)) {
                $arrRoles = array_merge($arrRoles, array_diff($new_st, $arrRoles));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        return false;
    }

    /**
     * match a user with a security token.
     *
     * @param string $userid id of the user
     * @param int    $st     security token to match
     *
     * @return bool TRUE if match, FALSE otherwise
     **/
    public function matchUserST($userid, $st)
    {
        $idst = $this->getUserST($userid);

        return $this->_searchMatch($idst, [$st]);
    }

    /**
     * match a group with a security token.
     *
     * @param string $groupid id of the group
     * @param int    $st      security token to match
     *
     * @return bool TRUE if match, FALSE otherwise
     **/
    public function matchGroupST($groupid, $st)
    {
        $idst = $this->getGroupST($groupid);

        return $this->_searchMatch($idst, [$st]);
    }

    /* NOTE: functions to test match with multiple security token */
    /**
     * match a user with an array of security token.
     *
     * @param string $userid id of the user
     * @param array  $st     array of security token to match
     *
     * @return bool TRUE if match, FALSE otherwise
     **/
    public function matchUserSTArray($userid, $st)
    {
        $idst = $this->getUserST($userid);

        return $this->_searchMatch($idst, $st);
    }

    /**
     * match a group with an array of security token.
     *
     * @param string $groupid id of the group
     * @param int    $st      array of security token to match
     *
     * @return bool TRUE if match, FALSE otherwise
     **/
    public function matchGroupSTArray($groupid, $st)
    {
        $idst = $this->getGroupST($groupid);

        return $this->_searchMatch($idst, $st);
    }
}
