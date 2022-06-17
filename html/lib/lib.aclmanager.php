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

define('ACL_SEPARATOR', '/');
define('ACL_TABLE_ST', '_st');
define('ACL_TABLE_User', '_user');
define('ACL_TABLE_Temp_User', '_user_temp');
define('ACL_TABLE_Group', '_group');
define('ACL_TABLE_Group_members', '_group_members');
define('ACL_TABLE_Group_user_waiting', '_group_user_waiting');
define('ACL_TABLE_Role', '_role');
define('ACL_TABLE_Role_members', '_role_members');

define('ACL_INFO_IDST', 0);
define('ACL_INFO_USERID', 1);
define('ACL_INFO_FIRSTNAME', 2);
define('ACL_INFO_LASTNAME', 3);
define('ACL_INFO_PASS', 4);
define('ACL_INFO_EMAIL', 5);
define('ACL_INFO_AVATAR', 6);
define('ACL_INFO_SIGNATURE', 7);
define('ACL_INFO_VALID', 10);
define('ACL_INFO_PWD_EXPIRE_AT', 11);
define('ACL_INFO_REGISTER_DATE', 12);
define('ACL_INFO_LASTENTER', 13);
define('ACL_INFO_FORCE_CHANGE', 14);
define('ACL_INFO_FACEBOOK_ID', 15);
define('ACL_INFO_TWITTER_ID', 16);
define('ACL_INFO_LINKEDIN_ID', 17);
define('ACL_INFO_GOOGLE_ID', 18);
define('ACL_INFO_PRIVACY_POLICY', 19);

define('ACL_INFO_GROUPID', 1);
define('ACL_INFO_GROUPDESCRIPTION', 2);
define('ACL_INFO_GROUPHIDDEN', 3);
define('ACL_INFO_GROUPTYPE', 4);

define('ACL_INFO_ROLEID', 1);
define('ACL_INFO_ROLEDESCRIPTION', 2);

define('ACL_INFO_SETTING_VALUE', 9);

define('ADMIN_GROUP_GODADMIN', '/framework/level/godadmin');
define('ADMIN_GROUP_ADMIN', '/framework/level/admin');
define('ADMIN_GROUP_USER', '/framework/level/user');

/**
 * Acl management tasks class.
 *
 * NOTE: ST means Security Token
 *
 * @category ACL
 *
 * @author   Emanuele Sandri <esandri@tiscali.it>
 *
 * @version  $Id: lib.aclmanager.php 1000 2007-03-23 16:03:43Z fabio $
 */
class DoceboACLManager
{
    /** the actual context for acl management */
    public $context = '';
    /** the connection to database */
    public $dbconn = false;
    /** the tables prefix */
    public $prefix = false;

    public $include_suspended = false;

    /**
     * @internal
     * return the st table name
     */
    public function _getTableST()
    {
        return $this->prefix . ACL_TABLE_ST;
    }

    /**
     * @internal
     * return the user table name
     */
    public function _getTableUser()
    {
        return $this->prefix . ACL_TABLE_User;
    }

    /**
     * @internal
     * return the temp user table name
     */
    public function _getTableTempUser()
    {
        return $this->prefix . ACL_TABLE_Temp_User;
    }

    /**
     * @internal
     * return the group table name
     */
    public function _getTableGroup()
    {
        return $this->prefix . ACL_TABLE_Group;
    }

    /**
     * @internal
     * return the group members table name
     */
    public function _getTableGroupMembers()
    {
        return $this->prefix . ACL_TABLE_Group_members;
    }

    /**
     * @internal
     * return the waiting user members of a group table name
     */
    public function _getTableUserOfGroupWaiting()
    {
        return $this->prefix . ACL_TABLE_Group_user_waiting;
    }

    /**
     * @internal
     * return the role table name
     */
    public function _getTableRole()
    {
        return $this->prefix . ACL_TABLE_Role;
    }

    /**
     * @internal
     * return the role members table name
     */
    public function _getTableRoleMembers()
    {
        return $this->prefix . ACL_TABLE_Role_members;
    }

    /**
     * @return string the name of the table with the users preferences
     *
     * @internal
     */
    public function _getTableSettingUser()
    {
        return $GLOBALS['prefix_fw'] . '_setting_user';
    }

    public function _executeQuery($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
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
            return sql_insert_id();
        } else {
            return sql_insert_id($this->dbconn);
        }
    }

    /**
     * return a new security token.
     */
    public function _createST()
    {
        $query = 'INSERT INTO ' . $this->_getTableST()
            . ' ( idst ) VALUES ( null )'; //TODO NO_Strict_MODE: to be confirmed

        return $this->_executeInsert($query);
    }

    /**
     * constructor.
     *
     * @param mixed $dbconn the connection to database or FALSE to use default connection
     * @param mixed $prefix the prefix of the database or FLASE to use default prefix
     */
    public function __construct($dbconn = false, $prefix = false)
    {
        $this->dbconn = ($dbconn === false) ? $GLOBALS['dbConn'] : $dbconn;
        $this->prefix = ($prefix === false) ? $GLOBALS['prefix_fw'] : $prefix;
        $this->context = ACL_SEPARATOR;
    }

    /**
     * set the actual context.
     *
     * @param string $context new actual context. assign ACL_SEPARATOR to set root
     */
    public function setContext($context)
    {
        if (substr($context, -1) == ACL_SEPARATOR) {
            $this->context = $context;
        } else {
            $this->context = $context . ACL_SEPARATOR;
        }
    }

    /**
     * get the actual context.
     * ACL_SEPARATOR for root, path with ACL_SEPARATOR at end.
     *
     * @return string actual context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * An userid/groupid/roleid can be absolute (if start with ACL_SEPARATOR charater) or
     * relative to actual DoceboACLManager context.
     * This function return always the absolute id of the given id. ( If it's
     *    an absolute id return it unchanged).
     *
     * @param string $id relative or absolute userid
     *
     * @return string absolute id
     */
    public function absoluteId($id)
    {
        if (strlen($id) == 0) {
            return $id;
        }
        if ($id[0] == ACL_SEPARATOR) {
            return $id;
        }

        return $this->context . $id;
    }

    /**
     * An userid/groupid/roleid can be absolute (if start with ACL_SEPARATOR charater) or
     * relative to actual DoceboACLManager context.
     * This function return always the relative id of the given id; normally
     *    remove the context from $id if it's absolute.
     * If it's an relative id return it unchanged.
     *
     * @param string $id relative or absolute userid
     *
     * @return string absolute id
     */
    public function relativeId($id)
    {
        if (empty($id)) {
            return $id;
        }
        if ($id[0] != ACL_SEPARATOR) {
            return $id;
        }
        $max = (strlen($this->context) < strlen($id)) ? strlen($this->context) : strlen($id);
        if (strncmp($this->context, $id, $max) == 0) {
            return substr($id, $max);
        } else {
            return $this->context . $id;
        }
    }

    /**
     * get security token of an user.
     *
     * @param string $userid id of the user
     *
     * @return mixed security token associated to user or FALSE
     **/
    public function getUserST($userid)
    {
        $query = 'SELECT idst '
            . ' FROM ' . $this->_getTableUser()
            . " WHERE userid = '" . $this->absoluteId($userid) . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            $result = sql_fetch_row($rs);

            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * get security token of an user.
     *
     * @param string $userid id of the user
     *
     * @return mixed security token associated to user or FALSE
     **/
    public function getUserid($idst)
    {
        $query = 'SELECT userid '
            . ' FROM ' . $this->_getTableUser()
            . " WHERE idst = '" . (int) $idst . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            $result = sql_fetch_row($rs);

            return $this->relativeId($result[0]);
        } else {
            return false;
        }
    }

    /**
     * get id of a temp user.
     *
     * @param string $userid id of the user
     *
     * @return mixed security token associated to user or FALSE
     **/
    public function getTempUserId($userid)
    {
        $query = 'SELECT idst '
            . ' FROM ' . $this->_getTableTempUser()
            . " WHERE userid = '" . $this->absoluteId($userid) . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            $result = sql_fetch_row($rs);

            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * get security token of an array of user.
     *
     * @param array $array_idst_user id of the user
     *
     * @return mixed some info associated to the users
     **/
    public function getArrUserST($array_idst_user, $flip = false)
    {
        $result = [];

        foreach ($array_idst_user as $index => $idst) {
            if (!is_numeric($idst)) {
                unset($array_idst_user[$index]);
            }
        }

        $inString = (implode(',', $array_idst_user) == '' ? 'NULL' : implode(',', $array_idst_user));

        $query = 'SELECT idst, userid'
            . ' FROM ' . $this->_getTableUser()
            . ' WHERE idst IN (' . $inString . ')';

        $rs = $this->_executeQuery($query);

        if (sql_num_rows($rs) > 0) {
            while (list($idst, $userid) = sql_fetch_row($rs)) {
                if ($flip) {
                    $result[$idst] = $userid;
                } else {
                    $result[$userid] = $idst;
                }
            }

            return $result;
        } else {
            return $result;
        }
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
        $query = 'SELECT idst'
            . ' FROM ' . $this->_getTableGroup()
            . " WHERE groupid = '" . $this->absoluteId($groupid) . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            $result = sql_fetch_row($rs);

            return $result[0];
        } else {
            return false;
        }
    }

    /**
     * get security token of an array of groups.
     *
     * @param array $arr_groupid id of the group
     *
     * @return array security tokens associated to groups or FALSE
     **/
    public function getArrGroupST($arr_groupid)
    {
        $result = [];

        if (!is_array($arr_groupid)) {
            return $result;
        }

        $query = 'SELECT idst, groupid'
            . ' FROM ' . $this->_getTableGroup()
            . " WHERE groupid IN ('" . implode("','", $arr_groupid) . "')";

        $rs = $this->_executeQuery($query);

        if (sql_num_rows($rs) > 0) {
            while (list($idst, $groupid) = sql_fetch_row($rs)) {
                $result[$groupid] = $idst;
            }

            return $result;
        } else {
            return $result;
        }
    }

    /**
     * get security token froma basepath of group.
     *
     * @param string $base_path the base path of the group
     * @param bool   $flip      if true the returned array is (idst => groupid)
     *
     * @return array security tokens associated to groups or FALSE (groupid => idst)
     **/
    public function getBasePathGroupST($base_path, $flip = false)
    {
        $query = 'SELECT idst, groupid'
            . ' FROM ' . $this->_getTableGroup()
            . " WHERE groupid LIKE '" . $base_path . "%'";

        $rs = $this->_executeQuery($query);
        $result = [];
        if (sql_num_rows($rs) > 0) {
            $list = [];
            while (list($idst, $groupid) = sql_fetch_row($rs)) {
                $list[$groupid] = $idst;
            }
            if (isset($list[ADMIN_GROUP_GODADMIN])) {
                $result[ADMIN_GROUP_GODADMIN] = $list[ADMIN_GROUP_GODADMIN];
            }
            if (isset($list[ADMIN_GROUP_ADMIN])) {
                $result[ADMIN_GROUP_ADMIN] = $list[ADMIN_GROUP_ADMIN];
            }
            if (isset($list[ADMIN_GROUP_USER])) {
                $result[ADMIN_GROUP_USER] = $list[ADMIN_GROUP_USER];
            }

            if ($flip) {
                $result = array_flip($list);
            } else {
                $result = $list;
            }
        }

        return $result;
    }

    /**
     * register a new user.
     *
     * @param string $userid           the userid, complete or relative to actual context
     * @param string $firstname        the first name
     * @param string $lastname         the lastname
     * @param string $pass             the password in clear text
     * @param string $email            the email address
     * @param string $avatar           the path of the avatar image
     * @param string $signature        the signature
     * @param bool   $alredy_encripted if the password is alredy encrypted
     * @param int    $idst             the idst for the new user
     *
     * @return int the security token
     */
    public function registerUser($userid, $firstname, $lastname,
                          $pass, $email, $avatar,
                          $signature, $alredy_encripted = false, $idst = false, $pwd_expire_at = '', $force_change = '',
                          $facebook_id = false, $twitter_id = false, $linkedin_id = false, $google_id = false)
    {

        if ($idst === false) {
            $idst = $this->_createST();
        }
        if ($idst == 0) {
            return false;
        }

        $userid = $this->absoluteId($userid);
        if (Forma\lib\Get::sett('pass_max_time_valid') != 0) {
            $pwd_expire_at = date('Y-m-d H:i:s', time() + Forma\lib\Get::sett('pass_max_time_valid') * 24 * 3600);
        }

        $userdata = [
            ACL_INFO_IDST => $idst,
            ACL_INFO_USERID => $userid,
            ACL_INFO_FIRSTNAME => $firstname,
            ACL_INFO_LASTNAME => $lastname,
            ACL_INFO_PASS => $pass,
            ACL_INFO_EMAIL => $email,
            ACL_INFO_AVATAR => $avatar,
            ACL_INFO_SIGNATURE => $signature,
            ACL_INFO_PWD_EXPIRE_AT => $pwd_expire_at,
            ACL_INFO_FORCE_CHANGE => $force_change,
            ACL_INFO_FACEBOOK_ID => $facebook_id,
            ACL_INFO_TWITTER_ID => $twitter_id,
            ACL_INFO_LINKEDIN_ID => $linkedin_id,
            ACL_INFO_GOOGLE_ID => $google_id,
        ];

        $data = Events::trigger('core.user.creating', [
            'userdata' => $userdata,
        ]);

        $userdata = $data['userdata'];

        $idst = $userdata[ACL_INFO_IDST];
        $userid = $userdata[ACL_INFO_USERID];
        $firstname = $userdata[ACL_INFO_FIRSTNAME];
        $lastname = $userdata[ACL_INFO_LASTNAME];
        $pass = $userdata[ACL_INFO_PASS];
        $email = $userdata[ACL_INFO_EMAIL];
        $avatar = $userdata[ACL_INFO_AVATAR];
        $signature = $userdata[ACL_INFO_SIGNATURE];
        $pwd_expire_at = $userdata[ACL_INFO_PWD_EXPIRE_AT];
        $force_change = $userdata[ACL_INFO_FORCE_CHANGE];
        $facebook_id = $userdata[ACL_INFO_FACEBOOK_ID];
        $twitter_id = $userdata[ACL_INFO_TWITTER_ID];
        $linkedin_id = $userdata[ACL_INFO_LINKEDIN_ID];
        $google_id = $userdata[ACL_INFO_GOOGLE_ID];

        $query = 'INSERT INTO ' . $this->_getTableUser()
            . ' (idst, userid, firstname, lastname, pass, email, avatar, signature, pwd_expire_at, '
            . '  register_date, '
            . ($force_change !== '' ? 'force_change, ' : '')
            . 'facebook_id, twitter_id, linkedin_id, google_id) '
            . ' VALUES ( "' . $idst . '", "' . $userid . '", "' . $firstname . '", "' . $lastname . '", '
            . ' "' . ($alredy_encripted === true ? $pass : $this->encrypt($pass)) . '", '
            . ' "' . $email . '", "' . $avatar . '", "' . $signature . '", "' . $pwd_expire_at . '", "' . date('Y-m-d H:i:s') . '", '
            . ($force_change !== '' ? ' "' . ((int) $force_change > 0 ? '1' : '0') . '", ' : '')
            . (!empty($facebook_id) ? ' "' . $facebook_id . ' "' : 'NULL') . ', '
            . (!empty($twitter_id) ? ' "' . $twitter_id . ' "' : 'NULL') . ', '
            . (!empty($linkedin_id) ? ' "' . $linkedin_id . ' "' : 'NULL') . ', '
            . (!empty($google_id) ? ' "' . $google_id . ' "' : 'NULL') . ' '
            . ')';


        if ($this->_executeQuery($query)) {
            $query_h = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_password_history ( idst_user, pwd_date, passw, changed_by ) '
                . 'VALUES ( ' . (int) $idst . ", '" . date('Y-m-d H:i:s') . "', '" . ($alredy_encripted === true ? $pass : $this->encrypt($pass)) . "', " . (int) getLogUserId() . '  )';
            $this->_executeQuery($query_h);

            Events::triggerDeprecated('core.user.registered', ['idst' => $idst]);

            $userdata = $this->getUser($idst, false);

            if ($userdata) {
                Events::trigger('core.user.created', ['idst' => $idst, 'userdata' => $userdata]);
            }

            return $idst;
        } else {


            return false;
        }
    }

    /**
     * register a new temporary user, user in self registration.
     *
     * @param string $userid      the userid, complete or relative to actual context
     * @param string $firstname   the first name
     * @param string $lastname    the lastname
     * @param string $pass        the password in clear text
     * @param string $email       the email address
     * @param string $random_code the random code
     * @param string $random_code the id of the admin that have create this user
     *
     * @return int the security token
     */
    public function registerTempUser($userid, $firstname, $lastname, $pass, $email, $random_code, $create_by_admin = 0,
                              $facebook_id = '', $twitter_id = '', $linkedin_id = '', $google_id = '', $avatar = '')
    {
        $idst = $this->_createST();
        $userid = $this->absoluteId($userid);
        $query = 'INSERT INTO ' . $this->_getTableTempUser()
            . ' ( idst, userid, firstname, lastname, pass, email, random_code, request_on, create_by_admin, confirmed, facebook_id, twitter_id, linkedin_id, google_id, avatar) '
            . "VALUES ( '" . $idst . "', '" . $userid . "', '" . $firstname . "', '" . $lastname . "', "
            . " '" . $this->encrypt($pass) . "', '" . $email . "', '" . $random_code . "', '" . date('Y-m-d H:i:s') . "', "
            . "	'" . $create_by_admin . "', '" . ($create_by_admin == 0 ? 0 : 1) . "',
				'" . $facebook_id . "', '" . $twitter_id . "', '" . $linkedin_id . "', '" . $google_id . "', '" . $avatar . "')";
        $this->_executeQuery($query);

        return $idst;
    }

    public function suspendUser($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'UPDATE ' . $this->_getTableUser()
            . " SET valid = '0'"
            . " WHERE idst = '" . $idst . "'";

        return $this->_executeQuery($query);
    }

    public function recoverUser($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }
        $query = 'UPDATE ' . $this->_getTableUser()
            . " SET valid = '1'"
            . " WHERE idst = '" . $idst . "'";

        return $this->_executeQuery($query);
    }

    public function getTempUserInfo($idst = false, $random_code = false)
    {
        $query = 'SELECT * '
            . ' FROM ' . $this->_getTableTempUser() . ' '
            . ' WHERE ' . ($idst !== false ? " idst = '" . $idst . "' " : '')
            . ($random_code !== false ? " random_code = '" . $random_code . "' " : '');
        $rs = $this->_executeQuery($query);

        return sql_fetch_assoc($rs);
    }

    /**
     * return the temp user info.
     *
     * @param string $email the email of the user
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature
     *               - FALSE if user is not found
     */
    public function getTempUserByEmail($email)
    {
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, random_code, request_on, create_by_admin '
            . ' FROM ' . $this->_getTableTempUser()
            . " WHERE email = '" . $email . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            return sql_fetch_assoc($rs);
        } else {
            return false;
        }
    }

    /**
     * return the temp users info.
     *
     * @param array $array_idst     the security token of the users to get if flase assume all user
     * @param bool  $only_confirmed if true only user that have confirmed is displayed
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature
     *               - FALSE if user is not found
     */
    public function &getTempUsers($array_idst = false, $only_confirmed = false)
    {
        if ($array_idst !== false) {
            if (!is_array($array_idst) || empty($array_idst)) {
                $false_var = false;

                return $false_var;
            } else {
                foreach ($array_idst as $index => $idst) {
                    if (!is_numeric($idst)) {
                        unset($array_idst[$index]);
                    }
                }
            }
        }

        $users_info = [];
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, random_code, request_on, create_by_admin '
            . ' FROM ' . $this->_getTableTempUser() . ''
            . ' WHERE 1 ';
        if ($array_idst !== false) {
            $query .= ' AND idst IN (' . implode(',', $array_idst) . ') ';
        }
        if ($only_confirmed !== false) {
            $query .= ' AND confirmed = 1 ';
        }
        $query .= ' ORDER BY lastname, firstname, userid';
        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            while ($info = sql_fetch_assoc($rs)) {
                $users_info[$info['idst']] = $info;
            }

            return $users_info;
        } else {
            $false_var = false;

            return $false_var;
        }
    }

    /**
     * @param bool $only_confirmed if true only user that have confirmed is displayed
     *
     * @return int the number of user that is waiting
     */
    public function getTempUserNumber($only_confirmed = true)
    {
        $query = 'SELECT COUNT(*)'
            . ' FROM ' . $this->_getTableTempUser() . ' ';
        if ($only_confirmed) {
            $query .= ' WHERE confirmed = 1';
        }
        $rs = $this->_executeQuery($query);
        list($num) = sql_fetch_row($rs);

        return $num;
    }

    /**
     * register confirmation about a temp user.
     *
     * @param int $idst the idst of the temp user
     */
    public function confirmTempUser($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'UPDATE ' . $this->_getTableTempUser()
            . ' SET confirmed = 1'
            . " WHERE idst = '" . $idst . "'";
        $rs = $this->_executeQuery($query);

        return $rs;
    }

    /**
     * register a new group.
     *
     * @param string $groupid          the groupid, complete or relative to actual context
     * @param string $description
     * @param bool   $hidden           optional
     * @param string $type             the type of the field optional
     * @param string $show_on_platform in which platform this group is available optional
     *
     * @return int the security token
     */
    public function registerGroup($groupid, $description, $hidden = false, $type = 'free', $show_on_platform = '')
    {
        $idst = $this->_createST();

        if ($idst === false) {
            return false;
        }

        $groupid = $this->absoluteId($groupid);
        $query = 'INSERT INTO ' . $this->_getTableGroup()
            . ' (idst, groupid, description, hidden, type, show_on_platform ) '
            . "VALUES ( '" . $idst . "', '" . $groupid . "', '" . $description . "' "
            . ",'" . (($hidden) ? 'true' : 'false') . "' "
            . ", '" . $type . "', '" . $show_on_platform . "' )";
        $rs = $this->_executeQuery($query);
        if ($rs === false) {
            return false;
        }

        return $idst;
    }

    /**
     * register a new role.
     *
     * @param string $roleid      the roleid, complete or relative to actual context
     * @param string $description
     * @param int    $idPlugin
     *
     * @return int the security token
     */
    public function registerRole($roleid, $description, $idPlugin = null)
    {
        if (is_null($idPlugin)) {
            $idPlugin = 'NULL';
        }

        $idst = $this->_createST();
        if ($idst === false) {
            echo sql_error();
        }
        $roleid = $this->absoluteId($roleid);
        $query = 'INSERT INTO ' . $this->_getTableRole()
            . ' (idst, roleid, description, idPlugin ) '
            . " VALUES ( '$idst', '$roleid', '$description', $idPlugin )";
        $rs = $this->_executeQuery($query);
        if ($rs === false) {
            echo sql_error();
        }

        return $idst;
    }

    /**
     * update a user.
     *
     * @param int    $idst      security token of user to update
     * @param string $userid    the userid, complete or relative to actual context
     * @param string $firstname the first name
     * @param string $lastname  the lastname
     * @param string $pass      the password in clear text
     * @param string $email     the email address
     * @param string $avatar    the path of the avatar image
     * @param string $signature the signature
     * @param string $lastenter the date of the user last login
     *
     * @return true if success, FALSE otherwise
     */
    public function updateUser($idst, $userid = false, $firstname = false, $lastname = false,
                        $pass = false, $email = false, $avatar = false,
                        $signature = false, $lastenter = false, $resume = false, $force_change = '',
                        $facebook_id = false, $twitter_id = false, $linkedin_id = false, $google_id = false)
    {
        $old_userdata = $this->getUser($idst, null);

        $new_userdata = [];
        if ($userid !== false) {
            $new_userdata[ACL_INFO_USERID] = $this->absoluteId($userid);
        }
        if ($firstname !== false) {
            $new_userdata[ACL_INFO_FIRSTNAME] = $firstname;
        }
        if ($lastname !== false) {
            $new_userdata[ACL_INFO_LASTNAME] = $lastname;
        }
        if ($pass !== false) {
            $new_userdata[ACL_INFO_PASS] = $pass;
            if (Forma\lib\Get::sett('pass_max_time_valid') != 0) {
                $new_userdata[ACL_INFO_PWD_EXPIRE_AT] = date('Y-m-d H:i:s', time() + Forma\lib\Get::sett('pass_max_time_valid') * 24 * 3600);
            }
        }
        if ($email !== false) {
            $new_userdata[ACL_INFO_EMAIL] = $email;
        }
        if ($avatar !== false) {
            $new_userdata[ACL_INFO_AVATAR] = $avatar;
        }
        if ($signature !== false) {
            $new_userdata[ACL_INFO_SIGNATURE] = $signature;
        }
        if ($lastenter !== false) {
            $new_userdata[ACL_INFO_LASTENTER] = $lastenter;
        }
        if ($resume) {
            $new_userdata[ACL_INFO_VALID] = true;
        }
        if ($force_change !== '') {
            $new_userdata[ACL_INFO_FORCE_CHANGE] = (int) $force_change > 0;
        }
        if ($facebook_id != false) {
            $new_userdata[ACL_INFO_FACEBOOK_ID] = $facebook_id;
        }
        if ($twitter_id != false) {
            $new_userdata[ACL_INFO_TWITTER_ID] = $twitter_id;
        }
        if ($linkedin_id != false) {
            $new_userdata[ACL_INFO_LINKEDIN_ID] = $linkedin_id;
        }
        if ($google_id != false) {
            $new_userdata[ACL_INFO_GOOGLE_ID] = $google_id;
        }

        $data = Events::trigger('core.user.updating', [
            'idst' => $idst,
            'old_userdata' => $old_userdata,
            'new_userdata' => $new_userdata,
        ]);

        $new_userdata = $data['new_userdata'];

        $arrSET = [];
        if (array_key_exists(ACL_INFO_USERID, $new_userdata)) {
            $arrSET['userid'] = $new_userdata[ACL_INFO_USERID];
        }
        if (array_key_exists(ACL_INFO_FIRSTNAME, $new_userdata)) {
            $arrSET['firstname'] = $new_userdata[ACL_INFO_FIRSTNAME];
        }
        if (array_key_exists(ACL_INFO_LASTNAME, $new_userdata)) {
            $arrSET['lastname'] = $new_userdata[ACL_INFO_LASTNAME];
        }
        if (array_key_exists(ACL_INFO_PASS, $new_userdata) && !Forma\lib\Get::cfg('demo_mode')) {
            $arrSET['pass'] = $this->encrypt($new_userdata[ACL_INFO_PASS]);
            if (array_key_exists(ACL_INFO_PWD_EXPIRE_AT, $new_userdata)) {
                $arrSET['pwd_expire_at'] = $new_userdata[ACL_INFO_PWD_EXPIRE_AT];
            }
        }
        if (array_key_exists(ACL_INFO_EMAIL, $new_userdata)) {
            $arrSET['email'] = $new_userdata[ACL_INFO_EMAIL];
        }
        if (array_key_exists(ACL_INFO_AVATAR, $new_userdata)) {
            $arrSET['avatar'] = $new_userdata[ACL_INFO_AVATAR];
        }
        if (array_key_exists(ACL_INFO_SIGNATURE, $new_userdata)) {
            $arrSET['signature'] = $new_userdata[ACL_INFO_SIGNATURE];
        }
        if (array_key_exists(ACL_INFO_LASTENTER, $new_userdata)) {
            $arrSET['lastenter'] = $new_userdata[ACL_INFO_LASTENTER];
        }
        if (array_key_exists(ACL_INFO_VALID, $new_userdata)) {
            $arrSET['valid'] = $new_userdata[ACL_INFO_VALID] ? '1' : '0';
        }
        if (array_key_exists(ACL_INFO_FORCE_CHANGE, $new_userdata)) {
            $arrSET['force_change'] = $new_userdata[ACL_INFO_FORCE_CHANGE] ? '1' : '0';
        }
        if (array_key_exists(ACL_INFO_FACEBOOK_ID, $new_userdata)) {
            $arrSET['facebook_id'] = $new_userdata[ACL_INFO_FACEBOOK_ID];
        }
        if (array_key_exists(ACL_INFO_TWITTER_ID, $new_userdata)) {
            $arrSET['twitter_id'] = $new_userdata[ACL_INFO_TWITTER_ID];
        }
        if (array_key_exists(ACL_INFO_LINKEDIN_ID, $new_userdata)) {
            $arrSET['linkedin_id'] = $new_userdata[ACL_INFO_LINKEDIN_ID];
        }
        if (array_key_exists(ACL_INFO_GOOGLE_ID, $new_userdata)) {
            $arrSET['google_id'] = $new_userdata[ACL_INFO_GOOGLE_ID];
        }

        $colon = '';
        $query = 'UPDATE ' . $this->_getTableUser() . ' SET ';
        foreach ($arrSET as $fieldName => $fieldValue) {
            $query .= $colon . $fieldName . "='" . $fieldValue . "'";
            $colon = ', ';
        }
        $query .= " WHERE idst = '" . (int) $idst . "'";
        $result = $this->_executeQuery($query);
        if ($result && $pass !== false) {
            $query_h = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_password_history ( idst_user, pwd_date, passw, changed_by ) '
                . 'VALUES ( ' . (int) $idst . ", '" . date('Y-m-d H:i:s') . "', '" . $this->encrypt($pass) . "'," . (int) getLogUserId() . '  )';
            $this->_executeQuery($query_h);
        }

        if ($result) {
            $new_userdata = $this->getUser($idst, null);
            Events::trigger('core.user.updated', [
                'idst' => $idst,
                'old_userdata' => $old_userdata,
                'new_userdata' => $new_userdata,
            ]);
        }

        return $result;
    }

    /**
     * update a group.
     *
     * @param int    $idst             security token of group to update
     * @param string $groupid          the groupid, complete or relative to actual context
     * @param string $description
     * @param bool   $hidden           optional
     * @param string $type             the type of the field optional
     * @param string $show_on_platform in which platform this group is available optional
     *
     * @return true if success, FALSE otherwise
     */
    public function updateGroup($idst, $groupid = false, $description = false, $hidden = null, $type = false, $show_on_platform = false)
    {
        $arrSET = [];
        if ($groupid !== false) {
            $arrSET['groupid'] = $this->absoluteId($groupid);
        }
        if ($description !== false) {
            $arrSET['description'] = $description;
        }
        if ($hidden !== null) {
            $arrSET['hidden'] = ($hidden) ? 'true' : 'false';
        }
        if ($type !== null) {
            $arrSET['type'] = $type;
        }
        if ($show_on_platform !== null) {
            $arrSET['show_on_platform'] = $show_on_platform;
        }
        $colon = '';
        $query = 'UPDATE ' . $this->_getTableGroup() . ' SET ';
        foreach ($arrSET as $fieldName => $fieldValue) {
            $query .= $colon . $fieldName . "='" . $fieldValue . "'";
            $colon = ', ';
        }
        $query .= " WHERE idst = '" . (int) $idst . "'";

        return $this->_executeQuery($query);
    }

    /**
     * update a role.
     *
     * @param int    $idst        security token of role to update
     * @param string $roleid      the roleid, complete or relative to actual context
     * @param string $description
     *
     * @return true if success, FALSE otherwise
     */
    public function updateRole($idst, $roleid = false, $description = false)
    {
        $arrSET = [];
        if ($roleid !== false) {
            $arrSET['roleid'] = $this->absoluteId($roleid);
        }
        if ($description !== false) {
            $arrSET['description'] = $description;
        }
        $colon = '';
        $query = 'UPDATE ' . $this->_getTableRole() . ' SET ';
        foreach ($arrSET as $fieldName => $fieldValue) {
            $query .= $colon . $fieldName . "='" . $fieldValue . "'";
            $colon = ', ';
        }
        $query .= " WHERE idst = '" . (int) $idst . "'";

        return $this->_executeQuery($query);
    }

    /**
     * delete a user.
     *
     * @param int $idst the security token of the user to delete
     *
     * @return true if success, FALSE otherwise
     */
    public function deleteUser($idst)
    {
        //if ($idst == Docebo::user()->getIdSt()) return FALSE;

        $userdata = $this->getUser($idst, null);

        Events::trigger('core.user.deleting', [
            'idst' => $idst,
            'userdata' => $userdata,
        ]);

        if (Forma\lib\Get::sett('register_deleted_user') == 'on') {
            $control = $this->insertIntoDeleteUserTable($idst);
        } else {
            $control = true;
        }//modname=directory&op=org_chart

        $result = false;

        if ($control) {
            $this->_removeAllFromGroup($idst);
            $this->_removeAllFromRole($idst);

            $query = 'DELETE FROM ' . $this->_getTableUser()
                . " WHERE idst = '" . (int) $idst . "'";

            $result = $this->_executeQuery($query);

            $query1 = 'DELETE FROM %lms_courseuser where idUser =' . $idst;

            $result = $this->_executeQuery($query1);

            // --- mod. 06-09-2010
            if ($result) {
                require_once _adm_ . '/lib/lib.field.php';
                $extra_field = new FieldList();
                //$extra_field->removeUserEntry($idst);
                $extra_field->quickRemoveUserEntry($idst);

                Events::trigger('core.user.deleted', [
                    'idst' => $idst,
                    'userdata' => $userdata,
                ]);
            }
            // ---
        }

        return $result;
    }

    public function _getTableUserDeleted()
    {
        return $GLOBALS['prefix_fw'] . '_deleted_user';
    }

    public function insertIntoDeleteUserTable($idst_user)
    {
        $query = 'SELECT *' .
            ' FROM ' . $this->_getTableUser() . '' .
            " WHERE idst = '" . $idst_user . "'";

        $result = sql_query($query);
        list($idst, $userid, $firstname, $lastname, $pass, $email, $avatar, $signature, $level, $lastenter, $valid, $pwd_expire_at, $register_date) = sql_fetch_row($result);

        $insert_query = 'INSERT INTO ' . $this->_getTableUserDeleted() . ' ' .
            ' (id_deletion, idst, userid, firstname, lastname, pass, email, avatar, signature, level, lastenter, valid, pwd_expire_at, register_date, deletion_date, deleted_by)' .
            " VALUES ('', '" . (int) $idst . "', '" . addslashes($userid) . "', '" . addslashes($firstname) . "', '" . addslashes($lastname) . "', '" . addslashes($pass) . "', '" . addslashes($email) . "', '" . addslashes($avatar) . "', '" . addslashes($signature) . "', '" . $level . "', '" . $lastenter . "', '" . $valid . "', '" . $pwd_expire_at . "', '" . $register_date . "', '" . date('Y-m-d H:i:s') . "','" . getLogUserId() . "')";

        $insert_result = sql_query($insert_query);

        return $insert_result;
    }

    /**
     * delete a temp user.
     *
     * @param int    $idst_single the idst of the temporary user
     * @param string $random_code the random_code of the temporary user
     * @param time   $time        delete request oldest than
     * @param time   $del_field   if is true delete also the field related to the user
     *
     * @return true if success, FALSE otherwise
     */
    public function deleteTempUser($idst_single = false, $random_code = false, $time = false, $del_field = true, $reset_code = trued)
    {
        require_once _adm_ . '/lib/lib.field.php';

        $idst_del = [];
        $result = true;
        if ($idst_single !== false) {
            $idst_del[] = $idst_single;
        } elseif ($random_code !== false) {
            $query_sel = 'SELECT idst '
                . ' FROM ' . $this->_getTableTempUser()
                . " WHERE random_code = '" . $random_code . "'";
            $re = $this->_executeQuery($query_sel);
            while (list($id) = sql_fetch_row($re)) {
                $idst_del[] = $id;
            }
        } elseif ($time !== false) {
            $query_sel = 'SELECT idst'
                . ' FROM ' . $this->_getTableTempUser()
                . " WHERE UNIX_TIMESTAMP(request_on) <= '" . $time . "'"
                . ' AND confirmed = 0';
            $re = $this->_executeQuery($query_sel);
            while (list($id) = sql_fetch_row($re)) {
                $idst_del[] = $id;
            }
        }
        // Remove all the finded entry
        foreach ($idst_del as $idst) {
            if ($del_field === true) {
                $this->_removeAllFromGroup($idst);
                $this->_removeAllFromRole($idst);
                $extra_field = new FieldList();
                $extra_field->quickRemoveUserEntry($idst);
                //remove also from courseuser if neeeded
                DbConn::getInstance()->query('DELETE FROM %lms_courseuser WHERE idUser = ' . (int) $idst . ' ');
            }
            $query = 'DELETE FROM ' . $this->_getTableTempUser()
                . " WHERE idst = '" . $idst . "'";
            $result &= $this->_executeQuery($query);
        }

        // Remove all the associated codes
        foreach ($idst_del as $idst) {
            require_once _adm_ . '/lib/lib.code.php';
            $code_manager = new CodeManager();

            if ($reset_code === true) {
                $code_manager->resetUserCode($idst);
            }
        }

        return $result;
    }

    public function deleteTempUsers($arr_idst)
    {
        if (is_numeric($arr_idst)) {
            $arr_idst = [$arr_idst];
        } //handle single user case
        if (!is_array($arr_idst)) {
            return false;
        } //invalid user data
        if (count($arr_idst) <= 0) {
            return true;
        } //0 users operation: always "successfull"

        $result = false;
        $query = 'DELETE FROM ' . $this->_getTableTempUser()
            . ' WHERE idst IN (' . implode(',', $arr_idst) . ')';
        $result = $this->_executeQuery($query);

        return $result ? true : false;
    }

    /**
     * delete a group.
     *
     * @param int $idst the security token of the group to delete
     *
     * @return true if success, FALSE otherwise
     */
    public function deleteGroup($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }
        $this->_removeAllFromRole($idst);
        $query = 'DELETE FROM ' . $this->prefix . '_rules_entity'
        . " WHERE id_entity = '" . $idst . "'";
        $this->_executeQuery($query);
        $query = 'DELETE FROM ' . $this->_getTableGroupMembers()
            . " WHERE idst = '" . $idst . "'"
            . "    OR idstMember = '" . $idst . "'";
        $this->_executeQuery($query);
        $query = 'DELETE FROM ' . $this->_getTableGroup()
            . " WHERE idst = '" . $idst . "'";

        return $this->_executeQuery($query);
    }

    /**
     * delete a role.
     *
     * @param int $idst the security token of the role to delete
     *
     * @return true if success, FALSE otherwise
     */
    public function deleteRole($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableRoleMembers()
            . " WHERE idst = '" . $idst . "'";
        $this->_executeQuery($query);
        $query = 'DELETE FROM ' . $this->_getTableRole()
            . " WHERE idst = '" . $idst . "'";

        return $this->_executeQuery($query);
    }

    /**
     * delete a role form a base path.
     *
     * @param int $idst the security token of the role to delete
     *
     * @return true if success, FALSE otherwise
     */
    public function deleteRoleFromPath($path)
    {
        $query = 'SELECT idst FROM ' . $this->_getTableRole()
            . " WHERE roleid LIKE '" . $path . "%'";
        $rs = $this->_executeQuery($query);
        while (list($idst) = sql_fetch_row($rs)) {
            $query = 'DELETE FROM ' . $this->_getTableRoleMembers()
                . " WHERE idst = '" . $idst . "'";
            $this->_executeQuery($query);
        }
        $query = 'DELETE FROM ' . $this->_getTableRole()
            . " WHERE roleid LIKE '" . $path . "%'";

        return $this->_executeQuery($query);
    }

    /**
     * return the user info.
     *
     * @param mixed  $idst   the security token of the user to get, FALSE if
     *                       $userid is assigned
     * @param string $userid the userid of the user to get, FALSE if
     *                       $idst is assigned
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature,
     *               level, lastenter, valid, pwd_expire_at, register_date
     *               - FALSE if user is not found
     */
    public function getUser($idst, $userid)
    {
        // ha tanti parametri in più rispetto alla vecchia installazione... (3)
        /*** dupplicate ***
         * $query = "SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,"
         * ." level, lastenter, valid, pwd_expire_at, register_date, lastenter, force_change,
         * facebook_id, twitter_id, linkedin_id, google_id, privacy_policy "
         * ." FROM ".$this->_getTableUser();
         ***/
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,'
            . ' level, lastenter, valid, pwd_expire_at, register_date, lastenter '
            . ', force_change, facebook_id, twitter_id, linkedin_id, google_id, privacy_policy '
            . ' FROM ' . $this->_getTableUser();

        if ($idst !== false) {
            $query .= " WHERE idst = '" . $idst . "'";
        } elseif ($userid !== false) {
            $query .= " WHERE userid = '" . $this->absoluteId($userid) . "'";
        } else {
            return false;
        }

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            return sql_fetch_row($rs);
        } else {
            return false;
        }
    }

    /**
     * @return int the anonymous idst
     */
    public function getAnonymousId()
    {
        $idst = $this->getUserST('/Anonymous');

        return $idst;
    }

    /**
     * @return int idst of the group that contains all user registered
     */
    public function getGroupRegisteredId()
    {
        $reg_st = $this->getGroupST('/oc_0');

        return $reg_st;
    }

    public function getUserName($idst_user = false, $user_id = false)
    {
        $user_info = $this->getUser($idst_user, $user_id);

        return $user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
            ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
            : $this->relativeId($user_info[ACL_INFO_USERID]);
    }

    public function getConvertedUserName($user_info)
    {
        return $user_info[ACL_INFO_LASTNAME] . $user_info[ACL_INFO_FIRSTNAME]
            ? $user_info[ACL_INFO_LASTNAME] . ' ' . $user_info[ACL_INFO_FIRSTNAME]
            : $this->relativeId($user_info[ACL_INFO_USERID]);
    }

    /**
     * return the user info.
     *
     * @param string $email the email of the user
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature
     *               - FALSE if user is not found
     */
    public function getUserByEmail($email)
    {
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,'
            . ' level, lastenter, valid, pwd_expire_at, register_date, lastenter'
            . ' FROM ' . $this->_getTableUser()
            . " WHERE email = '" . $email . "'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            return sql_fetch_row($rs);
        } else {
            return false;
        }
    }

    /**
     * return user info for the user that, given a specified
     * field, matches a specified value. if more than one
     * result is found and req_unique is true, it will return false.
     * (unique index required).
     *
     * @param string $field_name name of the field - ex: google_id
     * @param string $field_val  value of the field - ex: mymail@gmail.com
     * @param string $req_unique specify whether to return false if the result is
     *                           not unique
     *
     * @return mixed array with user informations with numeric keys or
     *               false if more than one row is found and req_unique == true
     */
    public function getUserInfoByField($field_name, $field_val, $req_unique = true)
    {
        $res = false;

        $fields = 'idst, userid, firstname, lastname, pass, email, avatar, signature,
			level, lastenter, valid, pwd_expire_at, register_date, lastenter, force_change,
			facebook_id, twitter_id, linkedin_id, google_id, privacy_policy ';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getTableUser() . ' ' .
            'WHERE ' . $field_name . " = '" . sql_escape_string($field_val) . "'";

        $q = sql_query($qtxt);
        if ($q) {
            $count = sql_num_rows($q);

            if ($count > 1 && $req_unique) {
                return false;
            } elseif ($count > 0) {
                $res = sql_fetch_row($q);
            }
        }

        return $res;
    }

    public function getUserLevelId($idst_user)
    {
        $arr_levels = $this->getAdminLevels();
        $arr_levels_idst = array_flip($arr_levels);

        $query = 'SELECT idst FROM ' . $this->_getTableGroupMembers()
            . " WHERE idstMember = '" . (int) $idst_user . "'"
            . "   AND idst IN ( '" . implode("','", $arr_levels) . "' )";
        $rs = $this->_executeQuery($query);

        if (!$rs || !sql_num_rows($rs)) {
            return false;
        }
        list($level) = sql_fetch_row($rs);

        return $arr_levels_idst[$level];
    }

    /**
     * return the user info.
     *
     * @param array $array_idst the security token of the users to get
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature
     *               - FALSE if user is not found
     */
    public function &getUsers($array_idst)
    {
        if (!is_array($array_idst) || empty($array_idst)) {
            $false_var = false;

            return $false_var;
        } else {
            foreach ($array_idst as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($array_idst[$index]);
                }
            }
        }

        $users_info = [];
        $query = 'SELECT idst, userid, firstname, lastname, pass, email, avatar, signature,'
            . ' level, lastenter, valid, pwd_expire_at, register_date, lastenter'
            . ' FROM ' . $this->_getTableUser()
            . ' WHERE idst IN (' . implode(',', $array_idst) . ') '
            . ' ORDER BY lastname, firstname, userid';
        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            while ($info = sql_fetch_row($rs)) {
                $users_info[$info[ACL_INFO_IDST]] = $info;
            }

            return $users_info;
        } else {
            $false_var = false;

            return $false_var;
        }
    }

    public function &getUsersMappedData($array_idst)
    {
        $responseUsers = [];
        $users = $this->getUsers($array_idst);
        if ($users) {
            foreach ($users as $user) {
                $responseUsers[] = $this->getUserMappedData($user);
            }
        }

        return $responseUsers;
    }

    public function &getUserMappedData($user)
    {
        $path = $GLOBALS['where_files_relative'] . '/appCore/' . Forma\lib\Get::sett('pathphoto');

        $acl_man = Docebo::user()->getAclManager();

        $responseUser['idst'] = $user[ACL_INFO_IDST];
        $responseUser['name'] = $acl_man->getConvertedUserName($user);
        $responseUser['username'] = $user[ACL_INFO_USERID];
        $responseUser['firstname'] = $user[ACL_INFO_FIRSTNAME];
        $responseUser['lastname'] = $user[ACL_INFO_LASTNAME];
        $responseUser['email'] = $user[ACL_INFO_EMAIL];
        $responseUser['avatar'] = !empty($user[ACL_INFO_AVATAR]) ? $path . $user[ACL_INFO_AVATAR] : '';
        $responseUser['biography'] = $user[ACL_INFO_SIGNATURE];
        $responseUser['profile'] = 'index.php?modname=course&amp;op=viewprofile&amp;id_user=' . $user[ACL_INFO_IDST];
        $responseUser['registerDate'] = $user[ACL_INFO_REGISTER_DATE];

        return $responseUser;
    }

    /**
     * @return all idst of users
     **/
    public function &getAllUsersIdst()
    {
        $query = 'SELECT idst '
            . ' FROM ' . $this->_getTableUser();
        $rs = $this->_executeQuery($query);

        $arr_idst = [];

        if (sql_num_rows($rs) > 0) {
            while ($info = sql_fetch_row($rs)) {
                $arr_idst[] = (int) $info[0];
            }

            return $arr_idst;
        } else {
            $false_var = false;

            return $false_var;
        }
    }

    /**
     * @param string $sett_name name of the parameter to search
     * @param array  $arr_idst  the security token of the users to search, (if false return all users)
     * @param bool   $get_null  if true will return also the null entries
     *
     *    used as an additional filter
     *
     * @return array with user idst and related value of the setting for the user
     */
    public function getSettingValueOfUsers($sett_name, $arr_idst = false, $get_null = false)
    {
        $query = '
		SELECT DISTINCT u.idst, us.value
		FROM ' . $this->_getTableUser() . ' AS u ';
        if ($get_null !== false) {
            $query .= ' LEFT JOIN ' . $this->_getTableSettingUser() . ' AS us '
                . " ON ( u.idst = us.id_user AND us.path_name = '" . $sett_name . "' ) "
                . ' WHERE 1 ';
        } else {
            $query .= ' JOIN ' . $this->_getTableSettingUser() . ' AS us '
                . " WHERE u.idst = us.id_user AND us.path_name = '" . $sett_name . "' ";
        }
        if (($arr_idst !== false) && is_array($arr_idst) && !empty($arr_idst)) {
            foreach ($arr_idst as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arr_idst[$index]);
                }
            }

            $query .= ' AND u.idst IN ( ' . implode(',', $arr_idst) . ' )';
        }

        $re_query = $this->_executeQuery($query);

        $users = [];
        if (!$re_query) {
            return $users;
        }
        while (list($idst_user, $value) = sql_fetch_row($re_query)) {
            $users[$idst_user] = $value;
        }

        return $users;
    }

    /**
     * @param string $sett_name name of the parameter to search
     * @param array  $arr_idst  the security token of the users to search, (if false return all users)
     * @param bool   $get_null  if true will return also the null entries
     *
     *    used as an additional filter
     *
     * @return array with user idst and related value of the setting for the user
     */
    public function getSettingValueAndInfoOfUsers($sett_name, $arr_idst = false, $get_null = false)
    {
        $query = '
		SELECT u.idst, u.userid, u.firstname, u.lastname, u.pass, u.email, u.avatar, u.signature , us.value
		FROM ' . $this->_getTableUser() . ' AS u ';
        if ($get_null !== false) {
            $query .= ' LEFT JOIN ' . $this->_getTableSettingUser() . ' AS us '
                . " ON ( u.idst = us.id_user AND us.path_name = '" . $sett_name . "' ) "
                . ' WHERE 1 ';
        } else {
            $query .= ' JOIN ' . $this->_getTableSettingUser() . ' AS u '
                . " WHERE u.idst = us.id_user AND us.path_name = '" . $sett_name . "' ";
        }
        if (($arr_idst !== false) && (is_array($arr_idst)) && !empty($arr_idst)) {
            foreach ($arr_idst as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arr_idst[$index]);
                }
            }

            $query .= ' AND u.idst IN ( ' . implode(',', $arr_idst) . ' )';
        }
        $re_query = $this->_executeQuery($query);

        $users = [];
        if (!$re_query) {
            return $users;
        }
        while ($value = sql_fetch_row($re_query)) {
            $idst_user = $value[ACL_INFO_IDST];
            $users[$idst_user] = $value;
        }

        return $users;
    }

    /**
     * @param string $pname    name of the parameter to search
     * @param string $pval     value of the parameter to search
     * @param bool   $get_null if true will return also the null entries
     * @param bool   $arr_idst the  optional security token of the users to be
     *                         used as an additional filter
     *
     * @return array with user idst of the found entries
     */
    public function getUsersBySetting($pname, $pval, $get_null = false, $arr_idst = false)
    {
        $and_where = '';

        $qtxt = 'SELECT t1.idst FROM ' . $this->_getTableUser() . ' as t1 ';
        if ($get_null) {
            $qtxt .= ' LEFT JOIN ' . $this->_getTableSettingUser() . ' as t2 '
                . " ON ( t1.idst = t2.id_user AND t2.path_name = '" . $pname . "' ) ";

            $and_where = " (t2.value = '" . $pval . "' OR t2.value IS NULL)";
        } else {
            $qtxt .= ', ' . $this->_getTableSettingUser() . ' as t2 ';
            $and_where = " t1.idst = t2.id_user AND t2.path_name = '" . $pname . "' AND t2.value = '" . $pval . "'";
        }

        if (($arr_idst !== false) && (is_array($arr_idst)) && (count($arr_idst) > 0)) {
            foreach ($arr_idst as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arr_idst[$index]);
                }
            }

            $qtxt .= ' WHERE t1.idst IN (' . implode(',', $arr_idst) . ')';
            $filter_by_idst = true;
        } else {
            $filter_by_idst = false;
        }

        if ($and_where != '') {
            $qtxt .= ' ' . ($filter_by_idst ? ' AND ' : ' WHERE ') . ' ' . $and_where;
        }

        $q = $this->_executeQuery($qtxt);

        $res = [];
        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_array($q)) {
                $res[] = $row['idst'];
            }
        }

        return $res;
    }

    /**
     * @param string $language   the language to use as filter
     * @param array  $array_idst the  optional security token of the users to be
     *                           used as an additional filter
     *
     * @return array with user idst of the found entries
     */
    public function getUsersIdstByLanguage($language, $array_idst = false)
    {
        require_once _base_ . '/lib/lib.platform.php';

        $plat_man = &PlatformManager::createInstance();

        if ($language == $plat_man->getLanguageForPlatform()) {
            $get_null = true;
        } // so we have also the users with the default language
        else {
            $get_null = false;
        }

        $lang_idst_arr = $this->getUsersBySetting('ui.language', $language, $get_null, $array_idst);

        return $lang_idst_arr;
    }

    /**
     * return the user info.
     *
     * @param string $language   the language to use as filter
     * @param array  $array_idst the  optional security token of the users to be
     *                           used as an additional filter
     *
     * @return mixed array with user informations with numeric keys:
     *               - idst, userid, firstname, lastname, pass, email, avatar, signature
     *               - FALSE if user is not found
     */
    public function getUsersByLanguage($language, $array_idst = false)
    {
        return $this->getUsers($this->getUsersIdstByLanguage($language, $array_idst));
    }

    /**
     * return the group info.
     *
     * @param mixed  $idst    the security token of the group to get, FALSE if
     *                        $groupid is assigned
     * @param string $groupid the groupid of the group to get, FALSE if
     *                        $idst is assigned
     *
     * @return mixed array with user informations:
     *               - idst, groupid, description, hidden
     *               - FALSE if user is not found
     */
    public function getGroup($idst, $groupid)
    {
        $query = 'SELECT idst, groupid, description, hidden, type, show_on_platform'
            . ' FROM ' . $this->_getTableGroup();
        if ($idst !== false && is_numeric($idst)) {
            $query .= " WHERE idst = '" . $idst . "'";
        } elseif ($groupid !== false) {
            $query .= " WHERE groupid = '" . $this->absoluteId($groupid) . "'";
        } else {
            return false;
        }
        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            $result = sql_fetch_row($rs);
            $result[ACL_INFO_GROUPHIDDEN] = ($result[ACL_INFO_GROUPHIDDEN] == 'true');

            return $result;
        } else {
            return false;
        }
    }

    /**
     * return the group info.
     *
     * @param array $arr_idst the security token of the groups to get
     *
     * @return mixed array with user informations:
     *               - idst, groupid, description, hidden, type
     */
    public function getGroups($arr_idst)
    {
        if (!is_array($arr_idst) || empty($arr_idst)) {
            return [];
        }

        foreach ($arr_idst as $index => $idst) {
            if (!is_numeric($idst)) {
                unset($arr_idst[$index]);
            }
        }

        $query = 'SELECT idst, groupid, description, hidden, type'
            . ' FROM ' . $this->_getTableGroup()
            . ' WHERE idst IN ( ' . implode(',', $arr_idst) . ' )'
            . ' ORDER BY groupid ';
        $rs = $this->_executeQuery($query);
        if ($rs && sql_num_rows($rs) > 0) {
            $arrGroups = [];
            while ($result = sql_fetch_row($rs)) {
                $result[ACL_INFO_GROUPHIDDEN] = ($result[ACL_INFO_GROUPHIDDEN] == 'true');
                $arrGroups[$result[ACL_INFO_IDST]] = $result;
            }

            return $arrGroups;
        } else {
            return false;
        }
    }

    /**
     * return the groupid of a given st.
     *
     * @param int $idst the array of security token
     *
     * @return int groupid, or false
     */
    public function getGroupId($idst)
    {
        if (is_numeric($idst)) {
            $query = ' SELECT groupid'
                . ' FROM ' . $this->_getTableGroup()
                . ' WHERE idst = ' . (int) $idst;
            $rs = $this->_executeQuery($query);
            list($groupid) = sql_fetch_row($rs);

            return $groupid;
        } else {
            return false;
        }
    }

    /**
     * return the groupid of given st.
     *
     * @param array $arr_idst the array of security tokens
     *
     * @return array with groupid
     */
    public function getGroupsId($arr_idst)
    {
        if (is_array($arr_idst) && count($arr_idst) > 0) {
            foreach ($arr_idst as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arr_idst[$index]);
                }
            }

            $query = ' SELECT idst, groupid'
                . ' FROM ' . $this->_getTableGroup()
                . ' WHERE idst IN (' . implode(',', $arr_idst) . ')';
            $rs = $this->_executeQuery($query);
            $arrGroups = [];
            while (list($idst, $groupid) = sql_fetch_row($rs)) {
                $arrGroups[$idst] = $groupid;
            }

            return $arrGroups;
        } else {
            return [];
        }
    }

    /**
     * return the groupid (possibile restrictions on test type).
     *
     * @param array  $arr_type   the type of group that you want
     * @param string $find_text  the result must contain, in groupid or description, the text
     * @param bool   $also_image include in the array the code for the type icon
     *
     * @return array with groupid
     */
    public function &getAllGroupsId($arr_type = false, $find_text = false, $also_image = true)
    {
        if ($also_image) {
            $lang = &DoceboLanguage::createInstance('admin_directory', 'framework');
        }

        $query = ' SELECT g.idst, g.groupid, g.description, g.type '
            . ' FROM ' . $this->_getTableGroup() . ' AS g'
            . " WHERE g.hidden = 'false'";
        if ($arr_type !== false) {
            $query .= ' AND ( 0 ';
            foreach ($arr_type as $k => $v) {
                $query .= " OR g.type = '" . $v . "' ";
            }
            $query .= ' ) ';
        }
        if ($find_text != false) {
            $query .= " AND ( g.groupid LIKE '%" . $find_text . "%' OR g.description LIKE '%" . $find_text . "%'  ) ";
        }
        $query .= ' ORDER BY g.groupid ';
        $rs = $this->_executeQuery($query);
        $arrGroups = [];
        while (list($idst, $groupid, $description, $type) = sql_fetch_row($rs)) {
            $arrGroups[$idst]['groupid'] = $this->relativeId($groupid);
            $arrGroups[$idst]['description'] = $description;
            $arrGroups[$idst]['type'] = $type;
            /*if($also_image) {
                $arrGroups[$idst]['type_ico'] = '<img src="'.getPathImage('fw').'/directory/group_'.$type.'.gif"'
                    .' alt="'.$lang->def('_DIRECTORY_GROUPTYPE_'.strtoupper($type).'_ALT').'"'
                    .' title="'.$lang->def('_DIRECTORY_GROUPTYPE_'.strtoupper($type)).'" />';
            }*/
        }

        return $arrGroups;
    }

    /**
     * return the groupid of the groups of a corrispondent base path.
     *
     * @param string $base_path  the array of security tokens
     * @param array  $group_type a filter for type
     *
     * @return array with idst of gourps
     */
    public function getGroupsIdstFromBasePath($base_path, $group_type = false)
    {
        $query = 'SELECT idst '
            . ' FROM ' . $this->_getTableGroup() . ' AS g '
            . " WHERE g.groupid LIKE '" . $base_path . "%' ";
        if ($group_type !== false && !empty($group_type)) {
            $query .= ' AND ( 0 ';
            foreach ($group_type as $k => $type) {
                $query .= " OR g.type = '" . $type . "' ";
            }
            $query .= ' ) ';
        }

        $rs = $this->_executeQuery($query);
        $arrGroups = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrGroups[$idst] = $idst;
        }

        return $arrGroups;
    }

    /**
     * @return array all idst of all groups
     **/
    public function &getAllGroupsIdst()
    {
        $query = 'SELECT idst '
            . '  FROM ' . $this->_getTableGroup() . ' as g'
            . " WHERE g.hidden = 'false'";
        $rs = $this->_executeQuery($query);

        $arr_idst = [];

        if (sql_num_rows($rs) > 0) {
            while ($info = sql_fetch_row($rs)) {
                $arr_idst[] = $info[0];
            }

            return $arr_idst;
        } else {
            $false_var = false;

            return $false_var;
        }
    }

    /**
     * return the information about the users in waiting for a specified group.
     *
     * @param array $idst a idst of a group
     *
     * @return array the waiting user information
     */
    public function &getWaitingUserForGroup($idst)
    {
        $query = 'SELECT idst_user
		FROM ' . $this->_getTableUserOfGroupWaiting() . "
		WHERE idst_group = '" . (int) $idst . "'";
        $rs = $this->_executeQuery($query);
        $idst_user = [];
        while (list($id) = sql_fetch_row($rs)) {
            $idst_user[] = $id;
        }
        $user_info = &$this->getUsers($idst_user);

        return $user_info;
    }

    /**
     * return the information about the users in waiting for a specified group.
     *
     * @param array $idst a idst of a group
     *
     * @return array the waiting user information
     */
    public function &getPendingGroupOfUser($idst)
    {
        $query = 'SELECT idst_group
		FROM ' . $this->_getTableUserOfGroupWaiting() . "
		WHERE idst_user = '" . (int) $idst . "'";
        $rs = $this->_executeQuery($query);
        $idst_user = [];
        while (list($id) = sql_fetch_row($rs)) {
            $idst_group[$id] = $id;
        }

        return $idst_group;
    }

    /**
     * return the role info.
     *
     * @param mixed  $idst   the security token of the role to get, FALSE if
     *                       $roleid is assigned
     * @param string $roleid the roleid of the role to get, FALSE if
     *                       $idst is assigned
     *
     * @return mixed array with user informations:
     *               - idst, roleid, description
     *               - FALSE if user is not found
     */
    public function getRole($idst, $roleid)
    {
        $query = 'SELECT idst, roleid, description'
            . ' FROM ' . $this->_getTableRole();
        if ($idst !== false) {
            $query .= " WHERE idst = '" . (int) $idst . "'";
        } elseif ($roleid !== false) {
            $query .= " WHERE roleid = '" . $this->absoluteId($roleid) . "'";
        } else {
            return false;
        }

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) > 0) {
            return sql_fetch_row($rs);
        } else {
            return false;
        }
    }

    public function getRoleFromBasePath($base_path)
    {
        $role_list = [];
        $query = 'SELECT idst, roleid'
            . ' FROM ' . $this->_getTableRole()
            . " WHERE roleid LIKE '" . $base_path . "%'";

        $rs = $this->_executeQuery($query);
        if (sql_num_rows($rs) == 0) {
            return $role_list;
        }
        while (list($idst, $roleid) = sql_fetch_row($rs)) {
            $role_list[$idst] = $roleid;
        }

        return $role_list;
    }

    public function getRoleFromArraySt($arr_st, $base_filter = false)
    {
        if (is_array($arr_st)) {
            foreach ($arr_st as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arr_st[$index]);
                }
            }
        }

        $role_list = [];
        $query = 'SELECT idst, roleid'
            . ' FROM ' . $this->_getTableRole()
            . ' WHERE idst IN ( ' . implode(',', $arr_st) . ' ) ';
        if ($base_filter !== false) {
            $query .= " AND roleid LIKE '" . $base_filter . "%' ";
        }

        $rs = $this->_executeQuery($query);

        if (sql_num_rows($rs) == 0) {
            return $role_list;
        }
        while (list($idst, $roleid) = sql_fetch_row($rs)) {
            $role_list[$idst] = $roleid;
        }

        return $role_list;
    }

    /**
     * add an idst or a list of idsts to a group.
     *
     * @param int    $idst       the security token of the group destination
     * @param int    $idstMember the idst (or a list of idst) of the user(s)/group(s) to insert in
     * @param string $filter     (Optional). The filter to applay to assiciation
     */
    public function addToGroup($idst, $idstMember, $filter = '')
    {
        if (($idst == 0) || ($idstMember == 0)) {
            return true;
        }

        $add_list = (is_numeric($idstMember) ? [$idstMember] : (is_array($idstMember) ? $idstMember : false));
        if (!is_array($add_list)) {
            return false;
        }

        $add_list = array_values($add_list);
        $values = [];
        for ($i = 0; $i < count($add_list); ++$i) {
            $member = (int) $add_list[$i];
            if ($member > 0) {
                $values[] = "('" . $idst . "','" . $member . "','" . $filter . "')";
            }
        }

        if (count($values) > 0) {
            $query = 'INSERT INTO ' . $this->_getTableGroupMembers()
                . ' (idst, idstMember, filter) VALUES '
                //." ('".$idst."','".$idstMember."','".$filter."')";
                . implode(',', $values);

            return $this->_executeQuery($query);

            Events::trigger('core.orgchart_user.assigned', ['idst' => $idst, 'idstMember' => $idstMember]);
        } else {
            return false;
        }
    }

    /**
     * return the information about the users in waiting for a specified group.
     *
     * @param array $idst a idst of a group
     *
     * @return array the waiting user information
     */
    public function addToWaitingGroup($idst, $idstMember)
    {
        if (($idst == 0) || ($idstMember == 0) || !is_numeric($idst) || !is_numeric($idstMember)) {
            return;
        }
        $query = 'INSERT INTO ' . $this->_getTableUserOfGroupWaiting() . ' '
            . ' ( idst_group, idst_user ) VALUES '
            . " ( '" . $idst . "','" . $idstMember . "' )";

        $this->_executeQuery($query);
    }

    /**
     * add an idst to a role.
     *
     * @param int $idst       the security token of the role destination
     * @param int $idstMember the idst of the user/group to insert in
     */
    public function addToRole($idst, $idstMember)
    {
        if (($idst == 0) || ($idstMember == 0) || !is_numeric($idst) || !is_numeric($idstMember)) {
            return;
        }
        $query = 'INSERT INTO ' . $this->_getTableRoleMembers()
            . ' (idst, idstMember) VALUES '
            . " ('" . $idst . "','" . $idstMember . "')";

        return $this->_executeQuery($query);
    }

    /**
     * remove an idst or a group of idsts from a group.
     *
     * @param int    $idst       the security token of the group destination
     * @param int    $idstMember the idst of the user/group to remove from
     * @param string $filter     (Optional). The filter to applay to assiciation
     */
    public function removeFromGroup($idst, $idstMember, $filter = '')
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }
        $del_list = (is_numeric($idstMember) ? [$idstMember] : $idstMember);

        if (!is_array($del_list)) {
            return false;
        }
        if (count($del_list) > 0) {
            $query = 'DELETE FROM ' . $this->_getTableGroupMembers()
                . " WHERE idst = '" . (int) $idst . "'"
                . '   AND idstMember IN (' . implode(',', $del_list) . ')'
                . "   AND filter = '" . $filter . "'";

            Events::trigger('core.orgchart_user.unassigned', ['idst' => $idst, 'idstMember' => $idstMember]);

            return $this->_executeQuery($query);
        } else {
            return true;
        }
    }

    /**
     * remove an idst from all the groups.
     *
     * @param int    $idstMember the idst of the user/group to remove from
     * @param string $filter     (Optional). The filter to applay to assiciation
     */
    public function removeFromAllGroup($idstMember, $filter = '')
    {
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableGroupMembers()
            . " WHERE idstMember = '" . (int) $idstMember . "'"
            . "   AND filter = '" . $filter . "'";

        return $this->_executeQuery($query);
    }

    /**
     * remove a waiting user of a group.
     *
     * @param int $idst_group the security token of the group destination
     * @param int $idst_user  the idst of the user to remove from
     */
    public function removeFromUserWaitingOfGroup($idst_group, $idst_user)
    {
        if (!is_numeric($idst_group)) {
            $idst_group = false;
        }
        if (!is_numeric($idst_user)) {
            $idst_user = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableUserOfGroupWaiting()
            . " WHERE idst_group = '" . (int) $idst_group . "'"
            . "   AND idst_user = '" . (int) $idst_user . "'";
        $this->_executeQuery($query);
    }

    /**
     * remove an idst from a role.
     *
     * @param int $idst       the security token of the group destination
     * @param int $idstMember the idst of the user/group to remove from
     */
    public function removeFromRole($idst, $idstMember)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableRoleMembers()
            . " WHERE idst = '" . $idst . "'"
            . "   AND idstMember = '" . (int) $idstMember . "'";
        $this->_executeQuery($query);
    }

    /**
     * remove an idst from all groups.
     *
     * @param int $idstMember the idst of the user/group to remove from
     */
    public function _removeAllFromGroup($idstMember)
    {
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableGroupMembers()
            . " WHERE idstMember = '" . (int) $idstMember . "'";
        $this->_executeQuery($query);
    }

    /**
     * remove an idst from all roles.
     *
     * @param int $idstMember the idst of the user/group to remove from
     */
    public function _removeAllFromRole($idstMember)
    {
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'DELETE FROM ' . $this->_getTableRoleMembers()
            . " WHERE idstMember = '" . (int) $idstMember . "'";
        $this->_executeQuery($query);
    }

    /**
     * search groups containing a security token.
     *
     * @param int    $idstMember the security token of the searched member
     * @param string $filter     (Optional). Filter to applay.
     *
     * @return array array of security token of groups that contains $idstMember
     */
    public function getGroupsContainer($idstMember, $filter = '')
    {
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'SELECT idst FROM ' . $this->_getTableGroupMembers()
            . " WHERE idstMember = '" . (int) $idstMember . "'"
            . "   AND filter = '" . $filter . "'";
        $rs = $this->_executeQuery($query);

        $arrGroups = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrGroups[] = $idst;
        }

        return $arrGroups;
    }

    /**
     * search groups containing a security token.
     *
     * @param int    $idstMember the security token of the searched member
     * @param string $filter     (Optional). Filter to applay.
     *
     * @return array array of security token of groups that contains $idstMember
     */
    public function getGroupsAllContainer($arrMember, $filter = '')
    {
        if (is_array($arrMember)) {
            foreach ($arrMember as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arrMember[$index]);
                }
            }
        }

        $query = 'SELECT idst FROM ' . $this->_getTableGroupMembers()
            . ' WHERE idstMember IN ( ' . implode(',', $arrMember) . ' )'
            . "   AND filter = '" . $filter . "'";
        $rs = $this->_executeQuery($query);

        $arrGroups = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrGroups[] = $idst;
        }

        return $arrGroups;
    }

    /**
     * search roles containing a security token.
     *
     * @param int  $idstMember the security token of the searched member
     * @param bool $flip       if true the key of the array returned are the idst
     *
     * @return array array of security token of roles that contains $idstMember
     */
    public function getRolesContainer($idstMember, $flip = false)
    {
        if (!is_numeric($idstMember)) {
            $idstMember = false;
        }

        $query = 'SELECT idst FROM ' . $this->_getTableRoleMembers()
            . " WHERE idstMember = '" . $idstMember . "'";
        $rs = $this->_executeQuery($query);
        $arrRoles = [];
        $i = 0;
        while (list($idst) = sql_fetch_row($rs)) {
            if ($flip === false) {
                $arrRoles[] = $idst;
            } else {
                $arrRoles[$idst] = $i++;
            }
        }

        return $arrRoles;
    }

    /**
     * search roles containing a security token.
     *
     * @param int  $idstMember the security token of the searched member
     * @param bool $flip       if true the key of the array returned are the idst
     *
     * @return array array of security token of roles that contains $idstMember
     */
    public function getRolesAllContainer($arrMember)
    {
        if (is_array($arrMember)) {
            foreach ($arrMember as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($arrMember[$index]);
                }
            }
        }

        $inString = (implode(',', $arrMember) == '' ? 'NULL' : implode(',', $arrMember));

        $query = 'SELECT idst FROM ' . $this->_getTableRoleMembers()
            . ' WHERE idstMember IN ( ' . $inString . ' )';
        $rs = $this->_executeQuery($query);
        $arrRoles = [];

        while (list($idst) = sql_fetch_row($rs)) {
            $arrRoles[] = $idst;
        }

        return $arrRoles;
    }

    /**
     * search members of a group.
     *
     * @param int    $idst   scurity token of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return array array of security token of members contained in group
     */
    public function getGroupMembers($idst, $filter = '')
    {
        return array_merge($this->getGroupUMembers($idst, $filter),
            $this->getGroupGMembers($idst, $filter));
    }

    public function getGroupUMembersNumber($idst, $filter = '')
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'SELECT COUNT(tgm.idstMember) '
            . '  FROM ' . $this->_getTableGroupMembers() . ' AS tgm'
            . '  LEFT JOIN ' . $this->_getTableUser() . ' AS tu'
            . '    ON (tgm.idstMember = tu.idst)'
            . ' WHERE tgm.idst = ' . $idst . ''
            . "   AND tgm.filter = '" . $filter . "'"
            . '   AND NOT ISNULL(tu.idst)';

        if (!$this->include_suspended) {
            if (Forma\lib\Get::req('modname', DOTY_ALPHANUM, '') !== 'directory') {
                $query .= " AND tu.valid = '1'";
            }
        }

        $rs = $this->_executeQuery($query);

        list($numer) = sql_fetch_row($rs);

        return (int) $numer;
    }

    /**
     * search users member of a group (or an array of groups).
     *
     * @param int    $idst   scurity token of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return array array of security token of users contained in group
     */
    public function getGroupUMembers($idst, $filter = '')
    {
        if (is_array($idst)) {
            foreach ($idst as $index => $vidst) {
                if (!is_numeric($vidst)) {
                    unset($idst[$index]);
                }
            }
        } elseif (!is_array($idst)) {
            $idst = [(int) $idst];
        }

        $inString = (implode(',', $idst) == '' ? 'NULL' : implode(',', $idst));

        $query = 'SELECT DISTINCT tgm.idstMember'
            . '  FROM ' . $this->_getTableGroupMembers() . ' AS tgm'
            . '  LEFT JOIN ' . $this->_getTableUser() . ' AS tu'
            . '    ON (tgm.idstMember = tu.idst)'
            . ' WHERE tgm.idst IN (' . $inString . ')'
            . "   AND tgm.filter = '" . $filter . "'"
            . '   AND NOT ISNULL(tu.idst)';

        if (!$this->include_suspended) {
            if (Forma\lib\Get::req('modname', DOTY_ALPHANUM, '') !== 'directory') {
                $query .= " AND tu.valid = '1'";
            }
        }

        $rs = $this->_executeQuery($query);
        $arrUsers = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrUsers[] = (int) $idst;
        }

        return $arrUsers;
    }

    /**
     * search groups member of a group.
     *
     * @param int    $idst   scurity token of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return array array of security token of groups contained in group
     */
    public function getGroupGMembers($idst, $filter = '')
    {
        if (is_array($idst)) {
            foreach ($idst as $index => $vidst) {
                if (!is_numeric($vidst)) {
                    unset($idst[$index]);
                }
            }
        } elseif (!is_array($idst)) {
            $idst = [(int) $idst];
        }

        $inString = (implode(',', $idst) == '' ? 'NULL' : implode(',', $idst));

        $query = 'SELECT tgm.idstMember'
            . '  FROM ' . $this->_getTableGroupMembers() . ' AS tgm'
            . '  LEFT JOIN ' . $this->_getTableGroup() . ' AS tg'
            . '    ON (tgm.idstMember = tg.idst)'
            . ' WHERE tgm.idst IN (' . $inString . ')'
            . "   AND tgm.filter = '" . $filter . "'"
            . '   AND NOT ISNULL(tg.idst)';
        $rs = $this->_executeQuery($query);
        $arrGroups = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrGroups[] = (int) $idst;
        }

        return $arrGroups;
    }

    /**
     * get all groups of a group.
     *
     * @param int    $idst   idst of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return mixed array of groups of the group or FALSE
     **/
    public function getGroupGDescendants($idst, $filter = '')
    {
        /*$arrST = $this->getGroupGMembers( $idst );
        // search in groups
        $count = 0;
        while( $count < count( $arrST ) ) {
            $idST = $arrST[$count];
            $arrResult = $this->getGroupGMembers( $idST, $filter );
            $arrST = array_merge( $arrST, array_diff($arrResult, $arrST ));
            $count++;
        }
        return $arrST;*/
        if (!is_array($idst)) {
            $arrST = [$idst];
            $new_st = [$idst];
        } else {
            $arrST = $idst;
            $new_st = $idst;
        }

        $loop_check = 0;
        do {
            ++$loop_check;
            $new_st = $this->getGroupGMembers($new_st, $filter);
            if (!empty($new_st)) {
                $arrST = array_merge($arrST, array_diff($new_st, $arrST));
            }
        } while (!empty($new_st) && ($loop_check < 50));

        return $arrST;
    }

    public function getAllGroupsFromSelection($array_idst)
    {
        $array_idst = $this->getGroupsFromMixedIdst($array_idst);

        $array_ocd = [];
        $array_oc = [];

        $query = 'SELECT idst'
            . ' FROM %adm_group'
            . " WHERE groupid = '/oc_0'";

        list($idst) = sql_fetch_row(sql_query($query));
        $array_oc[$idst] = $idst;

        $query = 'SELECT idst'
            . ' FROM %adm_group'
            . " WHERE groupid = '/ocd_0'";

        list($idst) = sql_fetch_row(sql_query($query));
        $array_ocd[$idst] = $idst;

        $query = 'SELECT idst_oc, idst_ocd'
            . ' FROM %adm_org_chart_tree';

        $result = sql_query($query);

        while (list($idst_oc, $idst_ocd) = sql_fetch_row($result)) {
            $array_ocd[$idst_ocd] = $idst_ocd;
            $array_oc[$idst_oc] = $idst_oc;
        }

        $res = [];

        foreach ($array_idst as $id_group) {
            if (isset($array_oc[$id_group])) {
                $res[$id_group] = $id_group;
            }
            if (isset($array_ocd[$id_group])) {
                $query = 'SELECT iLeft, iRight'
                    . ' FROM %adm_org_chart_tree'
                    . ' WHERE idst_ocd = ' . (int) $id_group;

                list($i_left, $i_right) = sql_fetch_row(sql_query($query));

                $query = 'SELECT idst_oc, idst_ocd'
                    . ' FROM %adm_org_chart_tree'
                    . ' WHERE iLeft >= ' . $i_left
                    . ' AND iRight <= ' . $i_right;

                $result = sql_query($query);
                while (list($idst_oc, $idst_ocd) = sql_fetch_row($result)) {
                    $res[$idst_oc] = $idst_oc;
                    $res[$idst_ocd] = $idst_ocd;
                }
            } else {
                $res[$id_group] = $id_group;

                $query = 'SELECT idstMember'
                    . ' FROM %adm_group_members'
                    . ' WHERE idst = ' . (int) $id_group
                    . ' AND idstMember IN'
                    . ' ('
                    . ' SELECT idst'
                    . ' FROM %adm_group'
                    . ' )';

                $result = sql_query($query);
                while (list($idst) = sql_fetch_row($result)) {
                    $tmp = $this->getAllGroupsFromSelection([$idst]);
                    foreach ($tmp as $idst_t) {
                        $res[$idst_t] = $idst_t;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * get all users of a group (search in subgroups).
     *
     * @param int    $idst   idst of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return mixed array of users of the group or FALSE
     **/
    public function getGroupAllUser($idst, $filter = '')
    {
//		return array_merge( $this->getGroupUMembers($idst,$filter),  $this->getGroupUDescendants($idst,$filter) );

        $arr_umembers = $this->getGroupUMembers($idst, $filter);
        $arr_udescend = $this->getGroupUDescendants($idst, $filter);

        return array_merge($arr_umembers, array_diff($arr_udescend, $arr_umembers));
    }

    /**
     * get all users members of a list of groups.
     *
     * @param array  $group_arr list of idst of the groups
     * @param string $filter    (Optional). Filter to apply.
     */
    public function getGroupListMembers($group_arr, $filter = '')
    {
        if (is_array($group_arr)) {
            foreach ($group_arr as $index => $idst) {
                if (!is_numeric($idst)) {
                    unset($group_arr[$index]);
                }
            }
        } elseif ((!is_array($group_arr)) || (count($group_arr) < 1)) {
            return false;
        }

        $query = 'SELECT DISTINCT tgm.idstMember'
            . '  FROM ' . $this->_getTableGroupMembers() . ' AS tgm'
            . '  LEFT JOIN ' . $this->_getTableUser() . ' AS tu'
            . '    ON (tgm.idstMember = tu.idst)'
            . ' WHERE tgm.idst IN (' . implode(',', $group_arr) . ') '
            . "   AND tgm.filter = '" . $filter . "'"
            . '   AND NOT ISNULL(tu.idst)';
        $rs = $this->_executeQuery($query);
        $arrUsers = [];
        while (list($idst) = sql_fetch_row($rs)) {
            $arrUsers[] = (int) $idst;
        }

        return $arrUsers;
    }

    /**
     * get all descendant users of a group.
     *
     * @param int    $idst   idst of the group
     * @param string $filter (Optional). Filter to applay.
     *
     * @return mixed array of users of the group or FALSE
     **/
    public function getGroupUDescendants($idst, $filter = '')
    {
        /*		$arrST = $this->getGroupGDescendants( $idst, $filter );

                // search in groups
                $arrUsers = array();
                $count = 0;
                while( $count < count( $arrST ) ) {
                    $idST = $arrST[$count];
                    $arrResult = $this->getGroupUMembers( $idST, $filter );
                    $arrUsers = array_merge( $arrUsers, array_diff($arrResult, $arrUsers ));
                    $count++;
                }
                return $arrUsers;
        */
        /**** removed ***
         * if (is_numeric($idst)) $idst = array($idst);
         * if (!is_array($idst)) return false;
         * if (empty($idst)) return array();
         *
         * $arrUsers = $idst;
         * $new_st = $idst;
         * $loop_check = 0;
         * do {
         * $loop_check++;
         * $new_st = $this->getGroupUMembers( $new_st, $filter );
         *
         * if(!empty($new_st)) $arrUsers = array_merge( $arrUsers, array_diff($new_st, $arrUsers ));
         * } while(!empty($new_st) && ($loop_check < 50));
         *** removed ***/
        $arrST = $this->getGroupGDescendants($idst, $filter);

        if (empty($arrST)) {
            return [];
        }

        $arrUsers = $this->getGroupUMembers($arrST, $filter);

        return $arrUsers;
    }

    /**
     * search groups that are members of a specified role.
     *
     * @param int $idst the security token of the role
     *
     * @return array array of security token of groups that are members of a specified role
     */
    public function getRoleGMembers($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        //note: this one should be changed to return only the groups;
        //use getRoleMembers for both users+groups
        $query = 'SELECT idstMember FROM ' . $this->_getTableRoleMembers()
            . " WHERE idst = '" . $idst . "'";
        $rs = $this->_executeQuery($query);
        $arrGroups = [];
        $i = 0;
        while (list($idstMember) = sql_fetch_row($rs)) {
            $arrGroups[] = (int) $idstMember;
        }

        return $arrGroups;
    }

    /**
     * @param int $idst the security token of the role
     *
     * @return array array of security token members of a specified role
     */
    public function getRoleMembers($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'SELECT idstMember FROM ' . $this->_getTableRoleMembers()
            . " WHERE idst = '" . $idst . "'";
        $rs = $this->_executeQuery($query);
        $res = [];
        $i = 0;
        while (list($idstMember) = sql_fetch_row($rs)) {
            $res[] = (int) $idstMember;
        }

        return $res;
    }

    public function getAllRoleMembers($idst)
    {
        if (!is_numeric($idst)) {
            $idst = false;
        }

        $query = 'SELECT idstMember FROM ' . $this->_getTableRoleMembers()
            . " WHERE idst = '" . $idst . "'";
        $rs = $this->_executeQuery($query);
        $res = [];
        $i = 0;
        while (list($idstMember) = sql_fetch_row($rs)) {
            $res[] = (int) $idstMember;
        }
        $guser = $this->getGroupAllUser($res);
        $res = array_merge($res, $guser);

        return $res;
    }

    /**
     * this function encrypt the given string.
     *
     * @param string $text          text to encrypt
     * @param int    $password_hash
     *
     * @return string encrypted text
     *
     * @internal param int $password_ash
     * @internal param bool $alg
     */
    public function encrypt($text)
    {
        $password = new Password($text);

        return $password->hash();
    }

    /**
     * @param $text
     * @param $hash
     * @param $idst
     *
     * @return bool|true
     *
     * @internal param $password
     */
    public function password_verify_update($text, $hash, $idst = false)
    {
        $password = new Password($text);
        switch ($password->verify($hash)) {
            case PASSWORD_INCORRECT:
                return false;
            case PASSWORD_CORRECT:
                return true;
            case PASSWORD_UPDATE:
                if ($idst) {
                    return $this->updateUser(
                        $idst,
                        false,
                        false,
                        false,
                        $text,
                        false,
                        false,
                        false
                    );
                } else {
                    return true;
                }
        }
    }

    public function getAdminLevels()
    {
        $list = $this->getBasePathGroupST('/framework/level/');

        $output = [];
        $output[ADMIN_GROUP_GODADMIN] = $list[ADMIN_GROUP_GODADMIN];
        $output[ADMIN_GROUP_ADMIN] = $list[ADMIN_GROUP_ADMIN];
        $output[ADMIN_GROUP_USER] = $list[ADMIN_GROUP_USER];

        return $output;
    }

    /**
     * this function return the idst from a group of userid.
     *
     * @param array $user_arr an array with the relative userid
     *
     * @return array the list of idst corresponding of the userid passed
     */
    public function fromUseridToIdst($user_arr)
    {
        $res = [];
        if (!is_array($user_arr) || empty($user_arr)) {
            return $res;
        }

        $abs_user_arr = [];
        foreach ($user_arr as $user_rel) {
            $abs_user_arr[] = "'" . $this->absoluteId($user_rel) . "'";
        }
        $query = 'SELECT idst FROM ' . $this->_getTableUser()
            . ' WHERE userid IN ( ' . implode(',', $abs_user_arr) . ' )';
        $re_user = $this->_executeQuery($query);
        if (!$re_user) {
            return $res;
        }

        while ($row = sql_fetch_array($re_user)) {
            $res[] = $row['idst'];
        }

        return $res;
    }

    /**
     * return the idst corresponding to a user.
     *
     * @param array $arr_idst the arr_idst to check
     *
     * @return array the idst corresponding to a user
     */
    public function getUsersFromMixedIdst($arr_idst)
    {
        foreach ($arr_idst as $index => $idst) {
            if (!is_numeric($idst)) {
                unset($arr_idst[$index]);
            }
        }

        $inString = (implode(',', $arr_idst) == '' ? 'NULL' : implode(',', $arr_idst));

        $query = ' SELECT u.idst '
            . ' FROM ' . $this->_getTableUser() . ' AS u'
            . ' WHERE u.idst IN ( ' . $inString . ' )';
        $rs = $this->_executeQuery($query);
        $arr_user = [];
        if (!$rs) {
            return $arr_user;
        }
        while (list($idst) = sql_fetch_row($rs)) {
            $arr_user[] = (int) $idst;
        }

        return $arr_user;
    }

    /**
     * return the idst corresponding to a group.
     *
     * @param array $arr_idst the arr_idst to check
     *
     * @return array the idst corresponding to a group
     */
    public function getGroupsFromMixedIdst($arr_idst)
    {
        foreach ($arr_idst as $index => $idst) {
            if (!is_numeric($idst)) {
                unset($arr_idst[$index]);
            }
        }

        $inString = (implode(',', $arr_idst) == '' ? 'NULL' : implode(',', $arr_idst));

        $query = ' SELECT g.idst '
            . ' FROM ' . $this->_getTableGroup() . ' AS g '
            . ' WHERE g.idst IN ( ' . $inString . ' )';
        $rs = $this->_executeQuery($query);
        $arr_groups = [];
        if (!$rs) {
            return $arr_groups;
        }
        while (list($idst) = sql_fetch_row($rs)) {
            $arr_groups[] = (int) $idst;
        }

        return $arr_groups;
    }

    /**
     * return the idst of all the user related to the idst passed.
     *
     * @param array $arr_idst the arr_idst to check
     *
     * @return array the idst corresponding to a group
     */
    public function getAllUsersFromIdst($arr_idst)
    {
        return $this->getAllUsersFromSelection($arr_idst);
    }

    /**
     * This function returns a list of roles with extra information
     * starting from a given user idst and roleid path.
     *
     * @param string $user_idst  idst of the user that is member of the role/s
     * @param string $path_start the pattern with roleid shall start with
     * @param string $path_end   the pattern with roleid shall end with (optional)
     *
     * @return mixed FALSE if nothing found; else array with that looks like:
     *               Array(
     *               [role_info] => Array(
     *               [$roleid] => $role_info
     *               )
     *
     *                     [idst] => Array(
     *                          [$roleid] => $idst
     *                      )
     *                 )
     *
     *                 $role_info is the first information found after the given path,
     *                 presumably an id
     *
     **@author Giovanni Derks <virtualdarkness[AT]gmail-com>
     */
    public function getUserRoleFromPath($user_idst, $path_start, $path_end = false, $owned_directly = false)
    {
        $res = false;

        // Find all roles of the user:
        if ($owned_directly) {
            $all_roles = $this->getRolesContainer($user_idst);
        } else {
            $acl = Docebo::user()->getAcl();
            $all_roles = $acl->getUserAllST(false, '', $user_idst);
        }

        // Find roles of the user starting with $path_start:
        $qtxt = 'SELECT * FROM ' . $this->_getTableRole() . ' WHERE ';
        $qtxt .= 'idst IN (' . implode(',', $all_roles) . ') AND ';
        $qtxt .= "roleid LIKE '" . $path_start . "%'";

        // ..and ending with $path_end, if specified:
        if ($path_end !== false) {
            $qtxt .= " AND roleid LIKE '%" . $path_end . "'";
        }

        $q = $this->_executeQuery($qtxt);
        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $roleid = $row['roleid'];

                if ($path_end !== false) {
                    $role_info = substr($roleid, 0, strlen($roleid) - strlen($path_end));
                } else {
                    $role_info = $roleid;
                }

                $role_info = trim(substr($role_info, strlen($path_start)), '/');
                if (strpos($role_info, '/') !== false) {
                    $tmp_arr = explode('/', $role_info);
                    $role_info = $tmp_arr[0];
                    unset($tmp_arr);
                }
                $res['role_info'][$roleid] = $role_info;
                $res['idst'][$roleid] = $row['idst'];
            }
        }

        return $res;
    }

    public function _getTableUserFieldNames()
    {
        $res = [];
        $res[ACL_INFO_IDST] = 'idst';
        $res[ACL_INFO_USERID] = 'userid';
        $res[ACL_INFO_FIRSTNAME] = 'firstname';
        $res[ACL_INFO_LASTNAME] = 'lastname';
        $res[ACL_INFO_PASS] = 'pass';
        $res[ACL_INFO_EMAIL] = 'email';
        $res[ACL_INFO_AVATAR] = 'avatar';
        $res[ACL_INFO_SIGNATURE] = 'signature';
        $res[ACL_INFO_VALID] = 'valid';
        $res[ACL_INFO_PWD_EXPIRE_AT] = 'pwd_expire_at';
        $res[ACL_INFO_REGISTER_DATE] = 'register_date';
        $res[ACL_INFO_LASTENTER] = 'lastenter';
        $res[ACL_INFO_FORCE_CHANGE] = 'force_change';

        return $res;
    }

    /**
     * @param string $like_type off / both / start / end
     */
    public function searchUsers($internal_fields, $extra_fields = false, $idst_filter = false, $ini = false, $vis_item = false)
    {
        $res = [];

        $qtxt = $this->_getSearchUsersQuery($internal_fields, $extra_fields, $idst_filter, $ini, $vis_item);
        // echo $qtxt;

        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_row($q)) {
                $res[] = $row[ACL_INFO_IDST];
            }
        }

        return $res;
    }

    public function getSearchUsersTot($internal_fields, $extra_fields = false, $idst_filter = false)
    {
        $res = 0;

        $qtxt = $this->_getSearchUsersTotQuery($internal_fields, $extra_fields, $idst_filter);
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            list($tot) = sql_fetch_row($q);
            $res = $tot;
        }

        return $res;
    }

    public function _getSearchUsersQuery($internal_fields, $extra_fields = false, $idst_filter = false, $ini = false, $vis_item = false)
    {
        $res = 'SELECT * FROM ' . $this->_getSearchUsersBaseQuery($internal_fields, $extra_fields, $idst_filter);

        if (($ini !== false) && ($vis_item !== false)) {
            $res .= ' LIMIT ' . (int) $ini . ',' . (int) $vis_item . '';
        }

        return $res;
    }

    public function _getSearchUsersTotQuery($internal_fields, $extra_fields = false, $idst_filter = false)
    {
        $res = 'SELECT COUNT(*) FROM ' . $this->_getSearchUsersBaseQuery($internal_fields, $extra_fields, $idst_filter);

        return $res;
    }

    public function _getSearchUsersBaseQuery($internal_fields, $extra_fields = false, $idst_filter = false)
    {
        require_once _adm_ . '/lib/lib.field.php';

        $field_names = $this->_getTableUserFieldNames();

        // "SELECT * FROM ".. is made by the functions that calls this one..
        $res = $this->_getTableUser() . " WHERE valid='1' ";

        $internal_fields_tot = (is_array($internal_fields) ? count($internal_fields) : 0);
        if ($internal_fields_tot > 0) {
            $res .= 'AND (';
        }
        $i = 1;
        foreach ($internal_fields as $field_id => $val) {
            if ((isset($val['add_before'])) && (!empty($val['add_before']))) {
                $res .= ' ' . $val['add_before'];
            }

            $res .= $field_names[$field_id];

            $comp_op = ((isset($val['comp_op'])) && (!empty($val['comp_op'])) ? $val['comp_op'] : '=');

            if ((!isset($val['like'])) || ($val['like'] == 'off')) {
                $res .= $comp_op . "'" . $val['filter'] . "' ";
            } elseif ($val['like'] == 'both') {
                $res .= " LIKE '%" . $val['filter'] . "%' ";
            } elseif ($val['like'] == 'start') {
                $res .= " LIKE '%" . $val['filter'] . "' ";
            } elseif ($val['like'] == 'end') {
                $res .= " LIKE '" . $val['filter'] . "%' ";
            }

            if ((isset($val['add_after'])) && (!empty($val['add_after']))) {
                $res .= $val['add_after'] . ' ';
            }

            if ($i < $internal_fields_tot) {
                if ((isset($val['nextop'])) && (!empty($val['nextop']))) {
                    $next_operator = strtoupper($val['nextop']);
                } else {
                    $next_operator = 'AND';
                }

                $res .= $next_operator . ' ';
            }

            ++$i;
        }

        if ($internal_fields_tot > 0) {
            $res .= ') ';
        }

        if ($extra_fields !== false) {
            $fl = new FieldList();
            $fl->setFieldEntryTable($GLOBALS['prefix_fw'] . '_field_userentry'); // <- ??!
            $fields = $extra_fields['fields'];
            $method = $extra_fields['method'];
            $like_type = $extra_fields['like'];
            $search = $extra_fields['search'];
            $found_users = $fl->quickSearchUsersFromEntry($fields, $method, $like_type, $search);

            if ($idst_filter !== false) {
                $idst_filter = array_intersect($idst_filter, $found_users);
            } else {
                $idst_filter = $found_users;
            }
        }

        if ($idst_filter !== false) {
            $res .= 'AND idst IN (' . implode(',', $idst_filter) . ') ';
        }

        $res .= 'ORDER BY userid ';

        return $res;
    }

    public function &getGroupsIdByPaths($paths = false)
    {
        $db = DbConn::getInstance();

        $temp = false;

        switch (gettype($paths)) {
            case 'string' :
                $temp = [$paths];

                break;

            case 'array':
                $temp = &$paths;

                break;

            default:
                $temp = false;
        }

        if ($temp) {
            $output = [];

            $query = 'SELECT idst '
                . ' FROM ' . $this->_getTableGroup() . ' AS g '
                . " WHERE g.groupid IN ( '" . implode("','", $temp) . "' )";

            $res = $db->query($query);
            while (list($idst) = $db->fetch_row($res)) {
                $output[] = $idst;
            }
        } else {
            $output = false;
        }

        return $output;
    }

    public function getAllUsersFromSelection($arr_idst)
    {
        if (is_numeric($arr_idst)) {
            $arr_idst = [(int) $arr_idst];
        }
        if (!is_array($arr_idst)) {
            return [];
        }

        $admin_users = $this->getUsersFromMixedIdst($arr_idst);
        $admin_groups = $this->getGroupsFromMixedIdst($arr_idst);

        // retrive parent groups
        $tmp_admin_groups = [];
        foreach ($admin_groups as $id_group) {
            $tmp_admin_groups = array_merge($tmp_admin_groups, $this->getGroupGDescendants($id_group));
        }
        $admin_groups = $tmp_admin_groups;

        $admin_userlist = array_merge($admin_users, $this->getGroupUMembers($admin_groups)); //$this->getAllUsersFromIdst($admin_groups));

        return $admin_userlist;
    }

    public function random_password()
    {
        $pass = '';
        for ($a = 0; $a < 10; ++$a) {
            $seed = mt_rand(0, 15);

            if ($seed > 10) {
                $pass .= mt_rand(0, 9);
            } elseif ($seed > 5) {
                $pass .= chr(mt_rand(65, 90));
            } else {
                $pass .= chr(mt_rand(97, 122));
            }
        }

        return $pass;
    }
} //END ACLManagaer class

require_once _base_ . '/lib/lib.dataretriever.php';

/**
 * Adapter for list view MVC.
 */
class PeopleDataRetriever extends DataRetriever
{
    public $idNotFilters = null;
    public $idFilters = null;
    public $field_filter = [];
    public $custom_join = [];
    public $custom_where = [];

    public function __construct($dbconn = false, $prefix = false)
    {
        parent::__construct($dbconn, $prefix);
        $this->aclManager = new DoceboACLManager($dbconn, $prefix);
    }

    public function setUserFilter($arr_users)
    {
        $this->addFilter($arr_users);
    }

    public function setGroupFilter($idst_group, $deep = false)
    {
        $arr_users = $this->aclManager->getGroupUMembers($idst_group);
        if ($deep) {
            $arr_desc = $this->aclManager->getGroupUDescendants($idst_group);
            $arr_users = array_merge($arr_users, $arr_desc);
        }
        $this->addFilter($arr_users);
    }

    public function intersectGroupFilter($arr_idst_group)
    {
        $arr_users = [];
        foreach ($arr_idst_group as $idst_group) {
            $arr_users = array_merge($arr_users, $this->aclManager->getGroupUMembers($idst_group));
        }
        if (is_array($this->idFilters)) {
            $this->idFilters = array_intersect($this->idFilters, $arr_users);
        } else {
            // if not is set all user was in filter so intersection is $arr_users
            $this->idFilters = $arr_users;
        }
    }

    public function addFilter($idFilters)
    {
        if (is_array($this->idFilters)) {
            $this->idFilters = array_merge($this->idFilters, $idFilters);
        } else {
            $this->idFilters = $idFilters;
        }
    }

    /**
     * Add an idst to the list of idst to be excluded from retrieve.
     *
     * @param array idst    array of idst to be added to exclude list
     *
     * @return null
     **/
    public function addNotFilter($idFilters)
    {
        if (is_array($this->idNotFilters)) {
            $this->idNotFilters = array_merge($this->idNotFilters, $idFilters);
        } else {
            $this->idNotFilters = $idFilters;
        }
    }

    public function addFieldFilter($field, $value, $op = ' = ')
    {
        if ($field == 'userid' && strlen($value) > 0) {
            $this->field_filter[] = '`' . $field . '`' . $op . "'" . $this->aclManager->absoluteId($value) . "'";
        } else {
            $this->field_filter[] = '`' . $field . '`' . $op . "'" . $value . "'";
        }
    }

    public function resetFieldFilter()
    {
        $this->field_filter = [];
    }

    public function addCustomFilter($join_part, $where_part)
    {
        $this->custom_join[] = $join_part;
        $this->custom_where[] = $where_part;
    }

    public function resetCustomFilter()
    {
        $this->custom_join = [];
        $this->custom_where = [];
    }

    public function getRows($startRow = false, $numRows = false)
    {
        $query = 'SELECT idst, userid, firstname, lastname, email, valid, signature FROM %adm_user ';
        foreach ($this->custom_join as $add_join) {
            $query .= $add_join;
        }
        if ($this->idFilters !== null) {
            if (count($this->idFilters) > 0) {
                $query .= ' WHERE idst IN (' . implode(',', $this->idFilters) . ')';
            } else {
                $query .= ' WHERE 0';
            }
        } else {
            $query .= ' WHERE 1';
        }

        if ($this->idNotFilters !== null) {
            if (count($this->idNotFilters) > 0) {
                $query .= ' AND NOT (idst IN (' . implode(',', $this->idNotFilters) . '))';
            }
        }
        foreach ($this->field_filter as $filter_add) {
            $query .= ' AND ' . $filter_add;
        }

        foreach ($this->custom_where as $add_where) {
            $query .= ' AND ' . $add_where;
        }

        //$query .= " ORDER BY userid ";
        //$this->setOrderCol('userid', FALSE );

        return $this->_getData($query, $startRow, $numRows);
    }

    public function getAllRowsIdst()
    {
        $query = 'SELECT idst FROM ' . $this->prefix . '_user ';
        foreach ($this->custom_join as $add_join) {
            $query .= $add_join;
        }
        if ($this->idFilters !== null) {
            if (count($this->idFilters) > 0) {
                $query .= ' WHERE idst IN (' . implode(',', $this->idFilters) . ')';
            } else {
                $query .= ' WHERE 0';
            }
        } else {
            $query .= ' WHERE 1';
        }

        if ($this->idNotFilters !== null) {
            if (count($this->idNotFilters) > 0) {
                $query .= ' AND NOT (idst IN (' . implode(',', $this->idNotFilters) . '))';
            }
        }
        foreach ($this->field_filter as $filter_add) {
            $query .= ' AND ' . $filter_add;
        }

        foreach ($this->custom_where as $add_where) {
            $query .= ' AND ' . $add_where;
        }

        if ($this->dbConn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbConn);
        }

        return $rs;
    }

    public function getTotalRows()
    {
        $query = 'SELECT count(idst) FROM ' . $this->prefix . '_user ';
        foreach ($this->custom_join as $add_join) {
            $query .= $add_join;
        }
        if ($this->idFilters !== null) {
            if (count($this->idFilters) > 0) {
                $query .= ' WHERE idst IN (' . implode(',', $this->idFilters) . ')';
            } else {
                $query .= ' WHERE 0';
            }
        } else {
            $query .= ' WHERE 1';
        }

        if ($this->idNotFilters !== null) {
            if (count($this->idNotFilters) > 0) {
                $query .= ' AND NOT (idst IN (' . implode(',', $this->idNotFilters) . '))';
            }
        }
        foreach ($this->field_filter as $filter_add) {
            $query .= ' AND ' . $filter_add;
        }

        foreach ($this->custom_where as $add_where) {
            $query .= ' AND ' . $add_where;
        }

        if ($this->dbConn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbConn);
        }
        if ($rs === false) {
            return 0;
        }
        list($count) = sql_fetch_row($rs);

        return $count;
    }
}

class GroupDataRetriever extends DataRetriever
{
    public $aclManager = null;

    public $path_filter = false;
    public $group_filter = false;
    public $platforms_filter = false;

    public function __construct($dbconn = false, $prefix = false)
    {
        parent::__construct($dbconn, $prefix);
        $this->aclManager = new DoceboACLManager($dbconn, $prefix);
    }

    public function getFieldCount()
    {
        return 4;
    }

    public function addGroupFilter($group)
    {
        $this->group_filter = $group;
    }

    public function getGroupFilter()
    {
        if ($this->group_filter === false) {
            return [];
        }

        return $this->group_filter;
    }

    public function addPathFilter($path)
    {
        $this->path_filter = $path;
    }

    public function getPathFilter()
    {
        if ($this->path_filter === false) {
            return '';
        }

        return $this->path_filter;
    }

    public function addPlatformFilter($platforms)
    {
        $this->platforms_filter = $platforms;
    }

    public function getRows($startRow, $numRows)
    {
        $query = ' SELECT g.idst, g.groupid, g.description, g.type, COUNT(w.idst_user) AS waiting_user '
            . ' FROM ' . $this->aclManager->_getTableGroup() . ' AS g '
            . ' 		LEFT JOIN ' . $this->aclManager->_getTableUserOfGroupWaiting() . ' AS w ON ( g.idst = w.idst_group ) '
            . " WHERE hidden = 'false' ";

        if ($this->path_filter !== false) {
            $query .= " AND groupid LIKE '" . $this->path_filter . "%'";
        }
        if ($this->group_filter !== false) {
            $query .= ' AND g.idst IN ( ' . implode(',', $this->group_filter) . ' )';
        }
        if ($this->platforms_filter !== false) {
            $query .= " AND show_on_platform LIKE '%" . implode('%', $this->platforms_filter) . "%'";
        }
        $query .= ' GROUP BY g.idst, g.groupid, g.description, g.type';
        $query .= ' ORDER BY g.groupid ';

        return $this->_getData($query, $startRow, $numRows);
    }

    public function getTotalRows()
    {
        $query = ' SELECT COUNT(*) '
            . ' FROM ' . $this->aclManager->_getTableGroup() . ' AS g '
            . " WHERE hidden = 'false' ";

        if ($this->path_filter !== false) {
            $query .= " AND groupid LIKE '" . $this->path_filter . "%'";
        }
        if ($this->platforms_filter !== false) {
            $query .= " AND show_on_platform LIKE '%" . implode('%', $this->platforms_filter) . "%'";
        }

        if ($this->dbConn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbConn);
        }
        if ($rs === false) {
            return 0;
        }
        list($count) = sql_fetch_row($rs);

        return $count;
    }

    public function getAllRowsIdst()
    {
        $query = ' SELECT g.idst '
            . ' FROM ' . $this->aclManager->_getTableGroup() . ' AS g '
            . " WHERE hidden = 'false' ";

        if ($this->path_filter !== false) {
            $query .= " AND groupid LIKE '" . $this->path_filter . "%'";
        }
        if ($this->platforms_filter !== false) {
            $query .= " AND show_on_platform LIKE '%" . implode('%', $this->platforms_filter) . "%'";
        }
        if ($this->dbConn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbConn);
        }

        return $rs;
    }
}

class GroupMembersDataRetriever extends DataRetriever
{
    public $aclManager = null;

    public $idstGroup;

    public function __construct($idstGroup, $dbconn = false, $prefix = false)
    {
        $this->idstGroup = $idstGroup;
        parent::__construct($dbconn, $prefix);
        $this->aclManager = new DoceboACLManager($dbconn, $prefix);
    }

    public function getFieldCount()
    {
        return 2;
    }

    public function getRows($startRow, $numRows)
    {
        $id_filter = Forma\lib\Get::req('user_id', DOTY_MIXED, '');

        $query = 'SELECT tgm.idstMember, tu.userid, tu.firstname, tu.lastname, tu.email '
            . ' FROM ' . $this->aclManager->_getTableGroupMembers() . ' AS tgm  '
            . ' LEFT JOIN ' . $this->aclManager->_getTableUser() . ' AS tu '
            . ' ON tu.idst = tgm.idstMember'
            . ' LEFT JOIN ' . $this->aclManager->_getTableGroup() . ' AS gr'
            . ' ON gr.idst = tgm.idstMember'
            . " WHERE tgm.idst='" . $this->idstGroup . "' ";
        if ($id_filter != '') {
            $query .= " AND tu.userid LIKE '%" . $id_filter . "%'"
                . " OR gr.groupid LIKE '%" . $id_filter . "%'";
        }

        return $this->_getData($query, $startRow, $numRows);
    }

    public function getTotalRows()
    {
        $id_filter = Forma\lib\Get::req('user_id', DOTY_MIXED, '');

        $query = 'SELECT COUNT(*)'
            . ' FROM ' . $this->aclManager->_getTableGroupMembers() . ' AS tgm  '
            . ' LEFT JOIN ' . $this->aclManager->_getTableUser() . ' AS tu '
            . ' ON tu.idst = tgm.idstMember'
            . ' LEFT JOIN ' . $this->aclManager->_getTableGroup() . ' AS gr'
            . ' ON gr.idst = tgm.idstMember'
            . " WHERE tgm.idst='" . $this->idstGroup . "' ";
        if ($id_filter != '') {
            $query .= " AND tu.userid LIKE '%" . $id_filter . "%'"
                . " OR gr.groupid LIKE '%" . $id_filter . "%'";
        }
        if ($this->dbConn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbConn);
        }
        if ($rs === false) {
            return 0;
        }
        list($count) = sql_fetch_row($rs);

        return $count;
    }
}
