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
namespace FormaLms\lib;

use FormaLms\lib\Serializer\FormaSerializer;

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * Acl user class
 * This class is for manage user login, preferences, etc
 * It store acl's security tockens in user session
 * For a detailed check use FormaACL
 * To manage ACLs we must use FormaACLManager.
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

class FormaUser
{
    private static $currentUser = null;

    private $sprefix;
    private $userid;
    private $idst;
    private $arrst = [];
    private $userPreference;

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

    private $user_level = false;

    private array $userCourses;

    public static function getCurrentUser() : FormaUser
    {
        if (!self::$currentUser) {
            self::loadUserFromSession();
        }

        return self::$currentUser;
    }

    public static function loadUserFromSession()
    {
        $sessionUser = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('user');

        self::$currentUser = $sessionUser ?? self::createFormaUserFromSession('public_area');
    }

    public static function setCurrentUser($user)
    {
        self::$currentUser = $user;
    }
    /**
     * create a FormaACLUtil for given user
     * and load all ST stored in session.
     **/
    public function __construct($userid, $sprefix = 'public_area')
    {
        $this->userid = $userid;
        $this->sprefix = $sprefix;
        $session = Session\SessionManager::getInstance()->getSession();

        if ($session->has($sprefix . '_idst')) {
            $this->idst = $session->get($sprefix . '_idst');
        } else {
            $this->idst = \FormaLms\lib\Forma::getAcl()->getUserST($userid);
        }
        if ($session->has($sprefix . '_stlist')) {

            $data = $session->get($sprefix . '_stlist');
            $this->arrst = FormaSerializer::getInstance()->decode($data,'json');
        }

        $user_manager = new \FormaACLManager();
        $userInfo = $user_manager->getUser($this->idst, false);

        if (is_array($userInfo)) {
            $this->firstName = $userInfo[ACL_INFO_FIRSTNAME];
            $this->lastName = $userInfo[ACL_INFO_LASTNAME];
            $this->email = $userInfo[ACL_INFO_EMAIL];
            $this->avatar = $userInfo[ACL_INFO_AVATAR];
            $this->facebookId = $userInfo[ACL_INFO_FACEBOOK_ID];
            $this->twitterId = $userInfo[ACL_INFO_TWITTER_ID];
            $this->linkedinId = $userInfo[ACL_INFO_LINKEDIN_ID];
            $this->googleId = $userInfo[ACL_INFO_GOOGLE_ID];
        }

        $this->userPreference = new \UserPreferences($this->idst);

        $this->load_user_role();

        $this->userCourses = $this->loadUserCourses();

        $this->initRole($this->arrst, $this->idst);
    }

    public function initRole($preset, $idst)
    {
        $arr_levels_idst = [];
        $arr_levels_id  = [];
        $aclManager = \FormaLms\lib\Forma::getAcl()->getACLManager();
        $adminLevels = $aclManager->getAdminLevels();
        if (count($adminLevels)) {
            $arr_levels_id = array_flip($aclManager->getAdminLevels());
            $arr_levels_idst = array_keys($arr_levels_id);
        }

        $level_st = array_intersect($arr_levels_idst, $preset);

        if (count($level_st) == 0) {
            $this->user_level = false;
            $lvl = current($level_st);
        }

        $query = 'SELECT idst FROM %adm_group_members WHERE idstMember=' . (int) $idst . ' AND idst IN (' . implode(',', $arr_levels_idst) . ')';
        $res = sql_query($query);
        if ($res && sql_num_rows($res) > 0) {
            [$lvl] = sql_fetch_row($res);
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
        $userCoursesQuery = 'SELECT idCourse,edition_id as idEdtition,level,date_inscr as subscriptionDate,date_first_access as firstAccess,status,date_complete as completedAt, date_begin_validity as dateBeginValidity, date_expire_validity as dateExpireValidity, status AS user_status, waiting FROM %lms_courseuser where iduser=' . $this->idst;

        $result = sql_query($userCoursesQuery);
        if (sql_num_rows($result) > 0 ) {
            foreach ($result as $userCourse) {
                $userCourses[$userCourse['idCourse']] = $userCourse;
            }
        }
        return $userCourses;
    }

    public function reloadUserCourses()
    {
        $this->userCourses = $this->loadUserCourses();
        $this->saveInSession();
    }

    public function load_user_role()
    {
        if (!empty($this->arrst)) {
            $temp = \FormaLms\lib\Forma::getAclManager()->getRoleFromArraySt($this->arrst);
            $GLOBALS['user_roles'] = array_flip($temp);
        }
    }

    public function saveInSession()
    {
        $fallbackIp = array_key_exists('REMOTE_ADDR', $_SERVER) ? $_SERVER['REMOTE_ADDR'] : '::1';
        $ip = (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER) && $_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $fallbackIp;
        if (strpos($ip, ',') !== false) {
            $ip = substr($ip, 0, strpos($ip, ','));
        }
        $session = Session\SessionManager::getInstance()->getSession();
        $session->set($this->sprefix . '_idst', $this->idst);
        $session->set($this->sprefix . '_username', $this->userid);
        $session->set($this->sprefix . '_stlist', FormaSerializer::getInstance()->encode($this->arrst,'json'));
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
        return Session\SessionManager::getInstance()->getSession()->get($this->sprefix . '_log_ip');
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
        return Get::sett('url') . '/' . _folder_files_ . '/appCore/' . Get::sett('pathphoto') . $this->avatar;
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
     * @return mixed|string
     */
    public function getSprefix()
    {
        return $this->sprefix;
    }

    /**
     * @return bool
     */
    public function isUserLevel(): bool
    {
        return $this->user_level;
    }

    /**
     * @param mixed|string $sprefix
     * @return FormaUser
     */
    public function setSprefix($sprefix)
    {
        $this->sprefix = $sprefix;
        return $this;
    }

    /**
     * @param mixed $userid
     * @return FormaUser
     */
    public function setUserid($userid)
    {
        $this->userid = $userid;
        return $this;
    }

    /**
     * @param false|mixed $idst
     * @return FormaUser
     */
    public function setIdst($idst)
    {
        $this->idst = $idst;
        return $this;
    }

    /**
     * @param array|mixed $arrst
     * @return FormaUser
     */
    public function setArrst($arrst)
    {
        $this->arrst = $arrst;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserPreference()
    {
        return $this->userPreference;
    }

    /**
     * @param mixed $userPreference
     * @return FormaUser
     */
    public function setUserPreference($userPreference)
    {
        $this->userPreference = $userPreference;
        return $this;
    }

    /**
     * @param mixed|string $firstName
     * @return FormaUser
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @param mixed|string $lastName
     * @return FormaUser
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param mixed|string $email
     * @return FormaUser
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param mixed|string $avatar
     * @return FormaUser
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
        return $this;
    }

    /**
     * @param mixed|string $facebookId
     * @return FormaUser
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    /**
     * @param mixed|string $twitterId
     * @return FormaUser
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
        return $this;
    }

    /**
     * @param mixed|string $linkedinId
     * @return FormaUser
     */
    public function setLinkedinId($linkedinId)
    {
        $this->linkedinId = $linkedinId;
        return $this;
    }

    /**
     * @param mixed|string $googleId
     * @return FormaUser
     */
    public function setGoogleId($googleId)
    {
        $this->googleId = $googleId;
        return $this;
    }

    /**
     * @param bool $user_level
     * @return FormaUser
     */
    public function setUserLevel(bool $user_level): FormaUser
    {
        $this->user_level = $user_level;
        return $this;
    }

    /**
     * @param array $userCourses
     * @return FormaUser
     */
    public function setUserCourses(array $userCourses): FormaUser
    {
        $this->userCourses = $userCourses;
        return $this;
    }
    

    /**
     * static public function for load user from session.
     *
     * @param string $prefix optional prefix for session publiciables
     *
     * @return mixed FormaUser instance of logged in user if found user in session
     *               FALSE otherwise
     **/
    public static function &createFormaUserFromSession($prefix = 'base')
    {
        $session = Session\SessionManager::getInstance()->getSession();
        if ($session->has('user_enter_time')) {
            $session->set('user_enter_time', date('Y-m-d H:i:s'));
        }

        if ($session->has($prefix . '_username')) {
            $du = new FormaUser($session->get($prefix . '_username'), $prefix);

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
            if (Get::sett('use_rest_api') != 'off') {
                require_once _base_ . '/api/lib/lib.rest.php';

                if (Get::sett('rest_auth_method') == _REST_AUTH_TOKEN) {
                    //require_once(_base_.'/lib/lib.utils.php');
                    $token = Get::req('auth', DOTY_ALPHANUM, '');

                    if ($token) {
                        $id_user = \RestAPI::getUserIdByToken($token);
                        if ($id_user) {
                            $user_manager = new \FormaACLManager();
                            $user_info = $user_manager->getUser($id_user, false);

                            if ($user_info != false) {
                                $username = $user_info[ACL_INFO_USERID];
                                $du = new FormaUser($username, $prefix);
                                $session->set('last_enter', $user_info[ACL_INFO_LASTENTER]);

                                $du->setLastEnter(date('Y-m-d H:i:s'));
                                $session->set('user_enter_mark', time());
                                $du->loadUserSectionST();
                                $du->saveInSession();
                                $session->save();

                                return $du;
                            }
                        }
                    }
                }
            }

            // kerberos and similar auth
            if (Get::sett('auth_kerberos') == 'on') {
                if (isset($_SERVER['REMOTE_USER'])) {
                    // extract username
                    $username = addslashes(substr($_SERVER['REMOTE_USER'], 0, strpos($_SERVER['REMOTE_USER'], '@')));
                    $user_manager = new \FormaACLManager();
                    $user_info = $user_manager->getUser(false, $username);
                    if ($user_info != false) {
                        $du = new FormaUser($username, $prefix);

                        $du->setLastEnter(date('Y-m-d H:i:s'));
                        $session->set('user_enter_mark', time());
                        $session->save();
                        $du->loadUserSectionST();
                        $du->saveInSession();

                        return $du;
                    }
                }
            }
            $du = new FormaUser('/Anonymous', $prefix);

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
     * @return mixed FormaUser instance of logged in user if success in login
     *               FALSE otherwise
     **/
    public static function &createFormaUserFromLogin($login, $password, $prefix = 'base', $new_lang = false)
    {
        if ($login == '') {
            $false_public = false;

            return $false_public;
        }

        $user_manager = new \FormaACLManager();
        $user_info = $user_manager->getUser(false, $login);
        // first login

        if ($user_info === false) {
            return false;
        }

        if ($user_info[ACL_INFO_VALID] != '1') {
            return false;
        }

        if (Get::sett('ldap_used') == 'on') {
            if ($password == '') {
                $false_public = false;

                return $false_public;
            }
            //connect to ldap server
            if (!($ldap_conn = @ldap_connect(Get::sett('ldap_server'), Get::sett('ldap_port', '389')))) {
                exit('Could not connect to ldap server');
            }

            //bind on server
            $ldap_user = preg_replace('/\$user/', $login, Get::sett('ldap_user_string'));
            if (!(@ldap_bind($ldap_conn, $ldap_user, $password))) {
                ldap_unbind($ldap_conn);

                // Edited by Claudio Redaelli
                if (Get::sett('ldap_alternate_check') == 'on') {
                    if (!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST])) {
                        return false;
                    }
                } else {
                    $false_public = false;

                    return $false_public;
                }
                // End edit
            }
            ldap_unbind($ldap_conn);
        } elseif (!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST])) {
            return false;
        }
        $session = Session\SessionManager::getInstance()->getSession();
        $session->remove($prefix . '_idst');
        $du = new FormaUser($login, $prefix);

        // \Language policy
        if (!$new_lang && $session->has('forced_lang')) {
            $new_lang = \Lang::get();
        }
        if ($new_lang != false) {
            $du->getUserPreference()->setLanguage($new_lang);
        } else {
            if (!Get::cfg('demo_mode', false)) {
                \Lang::set($du->getUserPreference()->getLanguage());
            }
        }

        Session\SessionManager::getInstance()->getSession()->migrate();

        return $du;
    }

    public static function &createFormaUserFromField($field_name, $field_val, $prefix = 'base')
    {
        $user_manager = new \FormaACLManager();
        $user_info = $user_manager->getUserInfoByField($field_name, $field_val);

        $ret_value = false;
        if ($user_info === false) {
            return $ret_value;
        }

        if ($user_info[ACL_INFO_VALID] != '1') {
            return $ret_value;
        }

        $login = $user_info[ACL_INFO_USERID];
        $du = new FormaUser($login, $prefix);

        return $du;
    }

    public static function getFormaUserFromId($field_name, $field_val, $prefix = 'base')
    {
        $user_manager = new \FormaACLManager();
        $user_info = $user_manager->getUserInfoByField($field_name, $field_val);

        $ret_value = false;
        if ($user_info === false) {
            return $ret_value;
        }

        if ($user_info[ACL_INFO_VALID] != '1') {
            return $ret_value;
        }

        $login = $user_info[ACL_INFO_USERID];
        $du = new FormaUser($login, $prefix);

        return $du;
    }

    public static function setupUser($user)
    {
        $user->loadUserSectionST();
        $user->saveInSession();

        resetTemplate();


        $session = Session\SessionManager::getInstance()->getSession();

        $session->set('last_enter', $user->getLastEnter());
        $session->set('user_enter_mark', time());
        $session->save();
        $user->setLastEnter(date('Y-m-d H:i:s'));

        $session = Session\SessionManager::getInstance()->getSession();
    }

    public function setLastEnter($lastenter)
    {
        if (!$this->isAnonymous()) {
            return \FormaLms\lib\Forma::getAclManager()->updateUser(
                $this->idst,
                false,
                false,
                false,
                false,
                false,
                false,
                false,
                $lastenter
            );
        } else {
            return true;
        }
    }

    public function getLastEnter()
    {
        if (!$this->isAnonymous()) {
            $user_info = \FormaLms\lib\Forma::getAclManager()->getUser($this->getIdSt(), false);

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
        $this->arrst = \FormaLms\lib\Forma::getAcl()->getUserAllST($this->userid);
        $this->load_user_role();
    }

    /**
     * @return 0 if the user password is not elapsed, 1 if the password is elapsed or a force change is
     */
    public function isPasswordElapsed()
    {
        //if the password is managed by an external program jump this procedure
        if (Get::sett('ldap_used') == 'on') {
            return 0;
        }

        //change password forced from admin or is the first login. When a new user is created
        // and the setting for a change at irst login is active this flag wil be turned on
        $user_data = \FormaLms\lib\Forma::getAclManager()->getUser($this->idst, false);
        if ($user_data[ACL_INFO_FORCE_CHANGE] == 1) {
            return 2;
        }

        // password expiration?
        if (!$user_data[ACL_INFO_PWD_EXPIRE_AT]) {
            return 0;
        }
        if (Get::sett('pass_max_time_valid', '0') != '0') {
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
        $session = Session\SessionManager::getInstance()->getSession();
        if ($session->has($sprefix . '_stlist')) {
            $this->loadUserSectionST($section);
            $this->saveInSession();
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
        if ($this->user_level == ADMIN_GROUP_GODADMIN && \FormaLms\lib\Forma::getAclManager()->getRole(false, $roleid) === false) {
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
    

    public function getUserLevelId()
    {
        return $this->user_level;
    }

    /** Modifica per inversione cognome nome ... meglio nome cognome **/
    public function getUserName()
    {
        $user_info = \FormaLms\lib\Forma::getAclManager()->getUser(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), false);

        return $user_info[ACL_INFO_FIRSTNAME] . $user_info[ACL_INFO_LASTNAME]
            ? $user_info[ACL_INFO_FIRSTNAME] . ' ' . $user_info[ACL_INFO_LASTNAME]
            : \FormaLms\lib\Forma::getAclManager()->relativeId($user_info[ACL_INFO_USERID]);
    }

    public function getPreference($preference_path)
    {
        return $this->userPreference->getPreference($preference_path);
    }

    public function getQuotaLimit()
    {
        $user_quota = $this->userPreference->getPreference('user_rules.user_quota');
        if ($user_quota == USER_QUOTA_INHERIT) {
            $user_quota = Get::sett('user_quota');
        }

        return $user_quota;
    }

    public function getUsedQuota()
    {
        $user_quota = $this->userPreference->getPreference('user_rules.user_quota_used');

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

        $myfile_size = sql_fetch_row(sql_query($query));

        if ($myfile_size[0]) {
            $used_space = $myfile_size[0];
        }

        $control_query = 'SELECT *' .
            ' FROM ' . $this->getSettingUserTable() . '' .
            " WHERE id_user = '" . $id_user . "'" .
            " AND path_name = 'user_rules.user_quota_used'";

        $result = sql_fetch_row(sql_query($control_query));

        if ($result[0]) {
            $update_query = 'UPDATE ' . $this->getSettingUserTable() . '' .
                " SET value = '" . $used_space . "'" .
                " WHERE id_user = '" . $id_user . "'" .
                " AND path_name = 'user_rules.user_quota_used'";

            if ($result = sql_query($update_query)) {
                return true;
            }

            return false;
        } else {
            $insert_query = 'INSERT INTO ' . $this->getSettingUserTable() . '' .
                ' (path_name, id_user, value)' .
                " VALUES ('user_rules.user_quota_used', '" . $id_user . "', '" . $used_space . "')";

            if ($result = sql_query($insert_query)) {
                return true;
            }

            return false;
        }
    }
}
