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
 * Acl user class
 * This class is for manage user login, preferences, etc
 * It store acl's security tockens in user session
 * For a detailed check use DoceboACL
 * To manage ACLs we must use DoceboACLManager.
 *
 * @version  $Id: lib.user.php 977 2007-02-23 10:40:19Z fabio $
 *
 * @uses     UserPreference
 */
require_once _base_ . '/lib/lib.acl.php';

require_once _base_ . '/lib/lib.preference.php';

define('REFRESH_LAST_ENTER', 600);    //refresh the user last action every specified seconds

define('USER_QUOTA_INHERIT', -1);
define('USER_QUOTA_UNLIMIT', 0);

define('_US_EMPTY', 0);
define('_NOT_DELETED', 0);

class DoceboUser implements Serializable
{
    public $sprefix = '';
    public $acl = null;
    public $userid;
    public $idst;
    public $arrst = [];
    public $preference;


    /* @var string */
    private $firstName;
    /* @var string */
    private $lastName;
    /* @var string */
    private $email;
    /* @var string */
    private $avatar;
    /* @var string */
    private $facebookId;
    /* @var string */
    private $twitterId;
    /* @var string */
    private $linkedinId;
    /* @var string */
    private $googleId;

    public $user_level = false;

    private array $userCourses;

    protected $db = null;
    public  $aclManager;

    /**
     * create a DoceboACLUtil for given user
     * and load all ST stored in session.
     **/
    public function __construct($userid, $sprefix = 'public_area')
    {
        $this->userid = $userid;
        $this->sprefix = $sprefix;
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $this->db = DbConn::getInstance();

        $this->acl = new DoceboACL();
        $this->aclManager = $this->acl->getACLManager();

        if ($session->has($sprefix . '_idst')) {
            $this->idst = $session->get($sprefix . '_idst');
        } else {
            $this->idst = $this->acl->getUserST($userid);
        }
        if ($session->has($sprefix . '_stlist')) {
            require_once _base_ . '/lib/lib.json.php';
            $json = new Services_JSON();
            $this->arrst = $json->decode($session->get($sprefix . '_stlist'));
        }

        $user_manager = new DoceboACLManager();
        $userInfo = $user_manager->getUser($this->idst, false);

        if(is_array($userInfo)) {
           $this->firstName = $userInfo[ACL_INFO_FIRSTNAME];
            $this->lastName = $userInfo[ACL_INFO_LASTNAME];
            $this->email = $userInfo[ACL_INFO_EMAIL];
            $this->avatar = $userInfo[ACL_INFO_AVATAR];
            $this->facebookId = $userInfo[ACL_INFO_FACEBOOK_ID];
            $this->twitterId = $userInfo[ACL_INFO_TWITTER_ID];
            $this->linkedinId = $userInfo[ACL_INFO_LINKEDIN_ID];
            $this->googleId = $userInfo[ACL_INFO_GOOGLE_ID]; 
        }
        

        $this->preference = new UserPreferences($this->idst);

        $this->load_user_role();

        $this->userCourses = $this->loadUserCourses();

        $this->initRole($this->arrst, $this->idst);
    }

    public function initRole($preset, $idst)
    {
        $arr_levels_idst = [];
        $aclManager = $this->acl->getACLManager();
        $adminLevels = $aclManager->getAdminLevels();
        if(count($adminLevels)) {
            $arr_levels_id = array_flip($aclManager->getAdminLevels());
            $arr_levels_idst = array_keys($arr_levels_id);
        }
        
        $level_st = array_intersect($arr_levels_idst, $preset);

        if (count($level_st) == 0) {
            $this->user_level = false;
            $lvl = current($level_st);
        }

        $query = 'SELECT idst FROM %adm_group_members WHERE idstMember=' . (int) $idst . ' AND idst IN (' . implode(',', $arr_levels_idst) . ')';
        $res = $this->db->query($query);
        if ($res && $this->db->num_rows($res) > 0) {
            list($lvl) = $this->db->fetch_row($res);
        }

        if (isset($arr_levels_id[$lvl])) {
            $this->user_level = $arr_levels_id[$lvl];
        } else {
            $this->user_level = array_search(ADMIN_GROUP_USER, $arr_levels_id);
        }
    }

    public function getUserCourses(): array
    {
        return $this->userCourses;
    }

    public function loadUserCourses()
    {
        $userCourses = [];
        $userCoursesQuery = 'SELECT idCourse,edition_id as idEdtition,level,date_inscr as subscriptionDate,date_first_access as firstAccess,status,date_complete as completedAt, date_begin_validity as dateBeginValidity, date_expire_validity as dateExpireValidity FROM %lms_courseuser where iduser=' . $this->idst;

        $result = $this->db->query($userCoursesQuery);

        if(is_countable($result)) {
           foreach ($result as $userCourse) {
                $userCourses[$userCourse['idCourse']] = $userCourse;
            } 
        }
        
        return $userCourses;
    }

    public function reloadUserCourses()
    {
        $this->userCourses = $this->loadUserCourses();
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $session->set('user', $this);
        $session->save();
    }

    public function load_user_role()
    {
        if (!empty($this->arrst)) {
            $temp = $this->aclManager->getRoleFromArraySt($this->arrst);
            $GLOBALS['user_roles'] = array_flip($temp);
        }
    }

    public function SaveInSession()
    {
        require_once _base_ . '/lib/lib.json.php';
        $json = new Services_JSON();
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        if (strpos($ip, ',') !== false) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $session->set($this->sprefix . '_idst', $this->idst);
        $session->set($this->sprefix . '_username', $this->userid);
        $session->set($this->sprefix . '_stlist', $json->encode($this->arrst));
        $session->set($this->sprefix . '_log_ip', $ip);
        $session->set('user', $this);
        $session->save();
    }

    public function isAnonymous()
    {
        return strcmp($this->userid, '/Anonymous') == 0;
    }

    public function isLoggedIn()
    {
        return strcmp($this->userid, '/Anonymous') != 0;
    }

    public function getLogIp()
    {
        return \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get($this->sprefix . '_log_ip');
    }

    public function getIdSt()
    {
        return $this->idst;
    }

    public function getId()
    {
        return $this->idst;
    }

    public function getArrSt()
    {
        return $this->arrst;
    }

    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function hasAvatar()
    {
        return !empty($this->avatar);
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return FormaLms\lib\Get::sett('url') . '/' . _folder_files_ . '/appCore/' . FormaLms\lib\Get::sett('pathphoto') . $this->avatar;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @return string
     */
    public function getLinkedinId()
    {
        return $this->linkedinId;
    }

    /**
     * @return string
     */
    public function getGoogleId()
    {
        return $this->googleId;
    }

    /**
     * static public function for load user from session.
     *
     * @param string $prefix optional prefix for session publiciables
     *
     * @return mixed DoceboUser instance of logged in user if found user in session
     *               FALSE otherwise
     **/
    public static function &createDoceboUserFromSession($prefix = 'base')
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->has('user_enter_time')) {
            $session->set('user_enter_time', date('Y-m-d H:i:s'));
        }

        if ($session->has($prefix . '_username')) {
            $du = new DoceboUser($session->get($prefix . '_username'), $prefix);

            if ($session->has('user_enter_mark')) {
                if ($session->get('user_enter_mark') < time() - REFRESH_LAST_ENTER) {
                    $du->setLastEnter(date('Y-m-d H:i:s'));
                    $session->set('user_enter_mark', time());
                }
            } else {
                $du->setLastEnter(date('Y-m-d H:i:s'));
                $session->set('user_enter_mark', time());
            }
            $session->save();

            return $du;
        } else {
            // rest auth
            if (FormaLms\lib\Get::sett('use_rest_api') != 'off') {
                require_once _base_ . '/api/lib/lib.rest.php';

                if (FormaLms\lib\Get::sett('rest_auth_method') == _REST_AUTH_TOKEN) {
                    //require_once(_base_.'/lib/lib.utils.php');
                    $token = FormaLms\lib\Get::req('auth', DOTY_ALPHANUM, '');

                    if ($token) {
                        $id_user = RestAPI::getUserIdByToken($token);
                        if ($id_user) {
                            $user_manager = new DoceboACLManager();
                            $user_info = $user_manager->getUser($id_user, false);

                            if ($user_info != false) {
                                $username = $user_info[ACL_INFO_USERID];
                                $du = new DoceboUser($username, $prefix);
                                $session->set('last_enter', $user_info[ACL_INFO_LASTENTER]);

                                $du->setLastEnter(date('Y-m-d H:i:s'));
                                $session->set('user_enter_mark', time());
                                $du->loadUserSectionST();
                                $du->SaveInSession();
                                $session->save();

                                return $du;
                            }
                        }
                    }
                }
            }

            // kerberos and similar auth
            if (FormaLms\lib\Get::sett('auth_kerberos') == 'on') {
                if (isset($_SERVER['REMOTE_USER'])) {
                    // extract username
                    $username = addslashes(substr($_SERVER['REMOTE_USER'], 0, strpos($_SERVER['REMOTE_USER'], '@')));
                    $user_manager = new DoceboACLManager();
                    $user_info = $user_manager->getUser(false, $username);
                    if ($user_info != false) {
                        $du = new DoceboUser($username, $prefix);

                        $du->setLastEnter(date('Y-m-d H:i:s'));
                        $session->set('user_enter_mark', time());
                        $session->save();
                        $du->loadUserSectionST();
                        $du->SaveInSession();

                        return $du;
                    }
                }
            }
            $du = new DoceboUser('/Anonymous', $prefix);

            return $du;
        }
    }

    /**
     * static public function for load user from login e password.
     *
     * @param string $login    login of the user
     * @param string $password password of the user in clear text
     * @param string $prefix   optional prefix for session publiciables
     *
     * @return mixed DoceboUser instance of logged in user if success in login
     *               FALSE otherwise
     **/
    public static function &createDoceboUserFromLogin($login, $password, $prefix = 'base', $new_lang = false)
    {
        if ($login == '') {
            $false_public = false;

            return $false_public;
        }

        $user_manager = new DoceboACLManager();
        $user_info = $user_manager->getUser(false, $login);
        // first login

        if ($user_info === false) {
            return false;
        }

        if ($user_info[ACL_INFO_VALID] != '1') {
            return false;
        }

        if (FormaLms\lib\Get::sett('ldap_used') == 'on') {
            if ($password == '') {
                $false_public = false;

                return $false_public;
            }
            //connect to ldap server
            if (!($ldap_conn = @ldap_connect(FormaLms\lib\Get::sett('ldap_server'), FormaLms\lib\Get::sett('ldap_port', '389')))) {
                exit('Could not connect to ldap server');
            }

            //bind on server
            $ldap_user = preg_replace('/\$user/', $login, FormaLms\lib\Get::sett('ldap_user_string'));
            if (!(@ldap_bind($ldap_conn, $ldap_user, $password))) {
                ldap_close($ldap_conn);

                // Edited by Claudio Redaelli
                if (FormaLms\lib\Get::sett('ldap_alternate_check') == 'on') {
                    if (!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST])) {
                        return false;
                    }
                } else {
                    $false_public = false;

                    return $false_public;
                }
                // End edit
            }
            ldap_close($ldap_conn);
        } elseif (!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST])) {
            return false;
        }
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $session->remove($prefix . '_idst');
        $du = new DoceboUser($login, $prefix);

        // language policy
        if (!$new_lang && $session->has('forced_lang')) {
            $new_lang = Lang::get();
        }
        if ($new_lang != false) {
            $du->preference->setLanguage($new_lang);
        } else {
            if (!FormaLms\lib\Get::cfg('demo_mode', false)) {
                Lang::set($du->preference->getLanguage());
            }
        }

        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->migrate();

        return $du;
    }

    public static function &createDoceboUserFromField($field_name, $field_val, $prefix = 'base')
    {
        $user_manager = new DoceboACLManager();
        $user_info = $user_manager->getUserInfoByField($field_name, $field_val);

        $ret_value = false;
        if ($user_info === false) {
            return $ret_value;
        }

        if ($user_info[ACL_INFO_VALID] != '1') {
            return $ret_value;
        }

        $login = $user_info[ACL_INFO_USERID];
        $du = new DoceboUser($login, $prefix);

        return $du;
    }

    public static function setupUser(&$user)
    {
        $user->loadUserSectionST();
        $user->SaveInSession();

        resetTemplate();

        $GLOBALS['current_user'] = $user;
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $session->set('last_enter', $user->getLastEnter());
        $session->set('user_enter_mark', time());
        $session->save();
        $user->setLastEnter(date('Y-m-d H:i:s'));
    }

    public function setLastEnter($lastenter)
    {
        if (!$this->isAnonymous()) {
            return $this->aclManager->updateUser($this->idst,
                false, false, false, false, false, false, false,
                $lastenter);
        } else {
            return true;
        }
    }

    public function getLastEnter()
    {
        if (!$this->isAnonymous()) {
            $user_info = $this->aclManager->getUser($this->getIdSt(), false);

            return $user_info[ACL_INFO_LASTENTER];
        } else {
            return false;
        }
    }

    /**
     * This method load all security tokens associated to a section (course),
     * test the match with user and save in user session positive ST.
     *
     * @param string $section the section to load
     **/
    public function loadUserSectionST($section = false)
    {
        $this->arrst = $this->acl->getUserAllST($this->userid);
        $this->load_user_role();
    }

    /**
     * @return 0 if the user password is not elapsed, 1 if the password is elapsed or a force change is
     */
    public function isPasswordElapsed()
    {
        //if the password is managed by an external program jump this procedure
        if (FormaLms\lib\Get::sett('ldap_used') == 'on') {
            return 0;
        }

        //change password forced from admin or is the first login. When a new user is created
        // and the setting for a change at irst login is active this flag wil be turned on
        $user_data = $this->aclManager->getUser($this->idst, false);
        if ($user_data[ACL_INFO_FORCE_CHANGE] == 1) {
            return 2;
        }

        // password expiration?
        if (!$user_data[ACL_INFO_PWD_EXPIRE_AT]) {
            return 0;
        }
        if (FormaLms\lib\Get::sett('pass_max_time_valid', '0') != '0') {
            $pwd_expire = fromDatetimeToTimestamp($user_data[ACL_INFO_PWD_EXPIRE_AT]);
            if (time() > $pwd_expire) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * This method load all security tokens associated to a section (course),
     * test the match with user and save in user session positive ST.
     *
     * @param string $section the section to load
     **/
    public function saveUserSectionSTInSession($section)
    {
        $sprefix = $this->sprefix;
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->has($sprefix . '_stlist')) {
            $this->loadUserSectionST($section);
            $this->SaveInSession();
        }
    }

    /**
     * This method test if in user's loaded ST ther'is a given ST.
     *
     * @param int $st the security token to test
     *
     * @return bool TRUE, FALSE
     **/
    public function matchUserST($st)
    {
        return in_array($st, $this->arrst);
    }

    /**
     * This method test if user has a role.
     *
     * @param string $roleid the role to test
     *
     * @return bool TRUE, FALSE
     **/
    public function matchUserRole($roleid)
    {
        if (!isset($GLOBALS['user_roles'])) {
            $this->load_user_role();
        }
        if ($this->user_level == ADMIN_GROUP_GODADMIN && $this->aclManager->getRole(false, $roleid) === false) {
            return true;
        }

        return isset($GLOBALS['user_roles'][$roleid]);
    }

    /**
     * This method test if user has one of given roles.
     *
     * @param array $roles the array of roles to test
     *
     * @return bool TRUE, FALSE
     **/
    public function matchUserRoles($roles)
    {
        if (!isset($GLOBALS['user_roles'])) {
            $this->load_user_role();
        }

        foreach ($roles as $r) {
            if ($this->matchUserRole($GLOBALS['user_roles'][$r])) {
                return true;
            }
        }

        return false;
    }

    /**
     * This method test if user has all passed roles.
     *
     * @param array $roles the array of roles to test
     *
     * @return bool TRUE, FALSE
     **/
    public function matchUserAllRoles($roles)
    {
        if (!isset($GLOBALS['user_roles'])) {
            $this->load_user_role();
        }

        foreach ($roles as $r) {
            if (!$this->matchUserRole($GLOBALS['user_roles'][$r])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get refernce to DoceboACL.
     *
     * @return DoceboACL the DoceboACL object
     **/
    public function getACL()
    {
        return $this->acl;
    }

    /**
     * Get refernce to DoceboACLManager.
     *
     * @return DoceboACLManager the DoceboACLManager object
     **/
    public function getACLManager()
    {
        return $this->acl->getACLManager();
    }

    public function getUserLevelId()
    {
        return $this->user_level;
    }

    /** Modifica per inversione cognome nome ... meglio nome cognome **/
    public function getUserName()
    {
        $user_info = $this->aclManager->getUser(getLogUserId(), false);

        return $user_info[ACL_INFO_FIRSTNAME] . $user_info[ACL_INFO_LASTNAME]
            ? $user_info[ACL_INFO_FIRSTNAME] . ' ' . $user_info[ACL_INFO_LASTNAME]
            : $this->aclManager->relativeId($user_info[ACL_INFO_USERID]);
    }

    public function getPreference($preference_path)
    {
        return $this->preference->getPreference($preference_path);
    }

    public function getQuotaLimit()
    {
        $user_quota = $this->preference->getPreference('user_rules.user_quota');
        if ($user_quota == USER_QUOTA_INHERIT) {
            $user_quota = FormaLms\lib\Get::sett('user_quota');
        }

        return $user_quota;
    }

    public function getUsedQuota()
    {
        $user_quota = $this->preference->getPreference('user_rules.user_quota_used');

        return $user_quota;
    }

    /**
     * This public function return the myfile table.
     */
    public function getMyFilesTable()
    {
        return $GLOBALS['prefix_fw'] . '_user_myfiles';
    }

    /**
     * This public function return the setting user table.
     */
    public function getSettingUserTable()
    {
        return $GLOBALS['prefix_fw'] . '_setting_user';
    }

    /**
     * This public function update the used space of an user.
     *
     * @$id_user --> The idst of the user to update
     */
    public function updateUserUsedSpace($id_user)
    {
        $used_space = _US_EMPTY;

        $query = 'SELECT SUM(size)
			FROM ' . $this->getMyFilesTable() . "
			WHERE owner = '" . $id_user . "'";

        $myfile_size = sql_fetch_row($this->db->query($query));

        if ($myfile_size[0]) {
            $used_space = $myfile_size[0];
        }

        $control_query = 'SELECT *' .
            ' FROM ' . $this->getSettingUserTable() . '' .
            " WHERE id_user = '" . $id_user . "'" .
            " AND path_name = 'user_rules.user_quota_used'";

        $result = sql_fetch_row($this->db->query($control_query));

        if ($result[0]) {
            $update_query = 'UPDATE ' . $this->getSettingUserTable() . '' .
                " SET value = '" . $used_space . "'" .
                " WHERE id_user = '" . $id_user . "'" .
                " AND path_name = 'user_rules.user_quota_used'";

            if ($result = $this->db->query($update_query)) {
                return true;
            }

            return false;
        } else {
            $insert_query = 'INSERT INTO ' . $this->getSettingUserTable() . '' .
                ' (path_name, id_user, value)' .
                " VALUES ('user_rules.user_quota_used', '" . $id_user . "', '" . $used_space . "')";

            if ($result = $this->db->query($insert_query)) {
                return true;
            }

            return false;
        }
    }

    public function serialize()
    {
        //
    }

    public function unserialize($data)
    {
        //
    }


    public function __serialize()
    {
        //
    }

    public function __unserialize($data)
    {
        //
    }

}

function getLogUserId()
{
    return Docebo::user()->getIdSt();
}
