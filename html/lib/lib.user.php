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
 * Acl user class
 * This class is for manage user login, preferences, etc
 * It store acl's security tockens in user session
 * For a detailed check use DoceboACL
 * To manage ACLs we must use DoceboACLManager
 *
 * @package admin-core
 * @subpackage user
 * @version  $Id: lib.user.php 977 2007-02-23 10:40:19Z fabio $
 * @uses 	 UserPreference
 */

require_once(_base_.'/lib/lib.acl.php' );

require_once(_base_.'/lib/lib.preference.php');

define("REFRESH_LAST_ENTER", 600);	//refresh the user last action every specified seconds

define("USER_QUOTA_INHERIT", -1);
define("USER_QUOTA_UNLIMIT", 0);

define("_US_EMPTY", 0);
define("_NOT_DELETED", 0);

class DoceboUser {

	public $sprefix = '';
	public $acl = NULL;
	public $userid;
	public $idst;
	public $arrst = array();
	public $preference;

	public $user_level = false;

	protected $db = NULL;

	/**
	 * create a DoceboACLUtil for given user
	 * and load all ST stored in session
	 **/
	public function  __construct($userid, $sprefix = 'public_area') {
		$this->userid = $userid;
		$this->sprefix = $sprefix;

		$this->db = DbConn::getInstance();

		$this->acl = new DoceboACL();
		$this->aclManager =& $this->acl->getACLManager();


		if( isset( $_SESSION[$sprefix.'_idst'] ) ) {
			$this->idst = $_SESSION[$sprefix.'_idst'];
		} else {
			$this->idst = $this->acl->getUserST( $userid );
		}
		if( isset( $_SESSION[$sprefix.'_stlist'] ) ) {

			require_once(_base_.'/lib/lib.json.php');
			$json = new Services_JSON();
			$this->arrst = $json->decode($_SESSION[$sprefix.'_stlist']);
		}

		$this->preference = new UserPreferences($this->idst);

		$this->load_user_role();

		$aclManager =& $this->acl->getACLManager();
		$arr_levels_id = array_flip($aclManager->getAdminLevels());
		$arr_levels_idst = array_keys($arr_levels_id);

		$level_st = array_intersect($arr_levels_idst, $this->arrst);
		if(count($level_st) == 0) $this->user_level = false;
		$lvl = current($level_st);
		$query = "SELECT idst FROM %adm_group_members WHERE idstMember=".(int)$this->idst." AND idst IN (".implode(",", $arr_levels_idst).")";
		$res = $this->db->query($query);
		if ($res &&  $this->db->num_rows($res)>0) list($lvl) = $this->db->fetch_row($res);

		if(isset($arr_levels_id[$lvl])) $this->user_level = $arr_levels_id[$lvl];
		else $this->user_level = array_search(ADMIN_GROUP_USER, $arr_levels_id);

	}

	public function load_user_role() {

		if(!empty($this->arrst)) {
			$temp = $this->aclManager->getRoleFromArraySt($this->arrst);
			$GLOBALS['user_roles'] = array_flip($temp);
		}
	}

	public function SaveInSession() {
		require_once(_base_.'/lib/lib.json.php');
	    $json = new Services_JSON();

		$_SESSION[$this->sprefix.'_idst'] = $this->idst;
		$_SESSION[$this->sprefix.'_username'] = $this->userid;
		$_SESSION[$this->sprefix.'_stlist'] = $json->encode($this->arrst);
		$_SESSION[$this->sprefix.'_log_ip'] = $_SERVER['REMOTE_ADDR'];
	}

	public function isAnonymous()	{ return (strcmp($this->userid,'/Anonymous') == 0); }
	public function isLoggedIn() 	{ return (strcmp($this->userid,'/Anonymous') != 0); }
	public function getLogIp() 	{ return $_SESSION[$this->sprefix.'_log_ip']; }
	public function getIdSt()		{ return $this->idst; }
	public function getId()		{ return $this->idst; }
	public function getArrSt()		{ return $this->arrst; }
	public function getUserId()	{ return $this->userid; }

	/**
	 * static public function for load user from session
	 * @param string $prefix optional prefix for session publiciables
	 * @return mixed DoceboUser instance of logged in user if found user in session
	 *				 FALSE otherwise
	 **/
	public static function &createDoceboUserFromSession($prefix = 'base') {

		if(!isset($_SESSION['user_enter_time']))
					$_SESSION['user_enter_time'] = date('Y-m-d H:i:s');

		if( isset( $_SESSION[$prefix.'_username'] ) ) {
			$du = new DoceboUser( $_SESSION[$prefix.'_username'], $prefix );

			if(isset($_SESSION['user_enter_mark'])) {

				if($_SESSION['user_enter_mark'] < (time() - REFRESH_LAST_ENTER)) {
					$du->setLastEnter(date("Y-m-d H:i:s"));
					$_SESSION['user_enter_mark'] = time();
				}
			} else {
				$du->setLastEnter(date("Y-m-d H:i:s"));
				$_SESSION['user_enter_mark'] = time();
			}

			return $du;
		} else {

			// rest auth
			if(Get::sett('use_rest_api') != 'off') {

				require_once(_base_.'/api/lib/lib.rest.php');

				if(Get::sett('rest_auth_method') == _REST_AUTH_TOKEN) {

					//require_once(_base_.'/lib/lib.utils.php');
					$token = Get::req('auth', DOTY_ALPHANUM, '');

					if($token) {

						$id_user = RestAPI::getUserIdByToken($token);
						if($id_user) {
							$user_manager = new DoceboACLManager();
							$user_info 	= $user_manager->getUser($id_user, false);
							
							if($user_info != false) {
								$username 	= $user_info[ACL_INFO_USERID];
								$du = new DoceboUser( $username, $prefix );

								$_SESSION['last_enter'] = $user_info[ACL_INFO_LASTENTER];
								$du->setLastEnter(date("Y-m-d H:i:s"));
								$_SESSION['user_enter_mark'] = time();
								$du->loadUserSectionST();
								$du->SaveInSession();
								return $du;
							}
						}
					}
				}

			}

			// kerberos and similar auth
			if(Get::sett('auth_kerberos') == 'on') {

				if(isset($_SERVER['REMOTE_USER'])) {
					// extract username
					$username = addslashes(substr($_SERVER['REMOTE_USER'], 0, strpos($_SERVER['REMOTE_USER'], '@')));
					$user_manager = new DoceboACLManager();
					$user_info = $user_manager->getUser(false, $username);
					if($user_info != false) {

						$du = new DoceboUser( $username, $prefix );

						$du->setLastEnter(date("Y-m-d H:i:s"));
						$_SESSION['user_enter_mark'] = time();
						$du->loadUserSectionST();
						$du->SaveInSession();
						return $du;
					}
				}
			}
			$du = new DoceboUser( '/Anonymous', $prefix );
			return $du;
		}
	}

	/**
	 * static public function for load user from login e password
	 * @param string $login login of the user
	 * @param string $password password of the user in clear text
	 * @param string $prefix optional prefix for session publiciables
	 * @return mixed DoceboUser instance of logged in user if success in login
	 *				 FALSE otherwise
	 **/
	public static function &createDoceboUserFromLogin($login, $password, $prefix = 'base', $new_lang = false) {

		if($login == '') {
			$false_public = FALSE;
			return $false_public;
		}

		$user_manager = new DoceboACLManager();
		$user_info = $user_manager->getUser(false, $login);
		// first login

		if( $user_info === false )
			return false;

		if( $user_info[ACL_INFO_VALID] != '1' )
			return false;

		if(Get::sett('ldap_used') == 'on') {

			if($password == '') {
				$false_public = FALSE;
				return $false_public;
			}
			//connect to ldap server
			if( !($ldap_conn = @ldap_connect( Get::sett('ldap_server'), Get::sett('ldap_port', '389') )) ) {
				die("Could not connect to ldap server");
			}

			//bind on server
			$ldap_user = preg_replace( '/\$user/', $login, Get::sett('ldap_user_string') );
			if (!(@ldap_bind($ldap_conn, $ldap_user, $password))) {
				ldap_close($ldap_conn);

				// Edited by Claudio Redaelli
				if (Get::sett('ldap_alternate_check') == 'on') {
					if(!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST]))
						return false;
				} else {
					$false_public = FALSE;
					return $false_public;
				}
				// End edit
			}
			ldap_close($ldap_conn);

		} elseif(!$user_manager->password_verify_update($password, $user_info[ACL_INFO_PASS], $user_info[ACL_INFO_IDST])) {

			return false;
		}
		unset($_SESSION[$prefix."_idst"]);
		$du = new DoceboUser( $login, $prefix );

		// language policy
		if(!$new_lang && isset($_SESSION['forced_lang'])) $new_lang = Lang::get();
		if($new_lang != false) {

			$du->preference->setLanguage($new_lang);
		} else {

			if(!Get::cfg('demo_mode', false)) Lang::set($du->preference->getLanguage());
		}
		if(function_exists('session_regenerate_id')) session_regenerate_id();
		return $du;
	}


	public static function &createDoceboUserFromField($field_name, $field_val, $prefix = 'base') {

		$user_manager = new DoceboACLManager();
		$user_info = $user_manager->getUserInfoByField($field_name, $field_val);

		$ret_value = false;
		if( $user_info === false )
			return $ret_value;

		if( $user_info[ACL_INFO_VALID] != '1' )
			return $ret_value;

		$login =$user_info[ACL_INFO_USERID];
		$du = new DoceboUser( $login, $prefix );

		return $du;
	}


	public static function setupUser(&$user) {
		
            $user->loadUserSectionST();
            $user->SaveInSession();
            
            resetTemplate();

            $GLOBALS['current_user'] = $user;
            $_SESSION['last_enter'] = $user->getLastEnter();
            $_SESSION['user_enter_mark'] = time();

            $user->setLastEnter(date("Y-m-d H:i:s"));
	}


	public function setLastEnter($lastenter) {

		if (!$this->isAnonymous()) {
			return $this->aclManager->updateUser($this->idst,
				FALSE,  FALSE, FALSE,  FALSE,  FALSE,  FALSE, FALSE,
				$lastenter );
		}
		else {
			return TRUE;
		}
	}


	public function getLastEnter() {

		if (!$this->isAnonymous()) {
			$user_info =$this->aclManager->getUser($this->getIdSt(), false);
			return $user_info[ACL_INFO_LASTENTER];
		}
		else {
			return false;
		}
	}


	/**
	 * This method load all security tokens associated to a section (course),
	 * test the match with user and save in user session positive ST
	 * @param string $section the section to load
	 **/
	public function loadUserSectionST($section = false) {
		$this->arrst = $this->acl->getUserAllST($this->userid);
		$this->load_user_role();
	}

	/**
	 * @return 0 if the user password is not elapsed, 1 if the password is elapsed or a force change is
	 */
	function isPasswordElapsed() {
		//if the password is managed by an external program jump this procedure
		if(Get::sett('ldap_used') == 'on')
			return 0;

		//change password forced from admin or is the first login. When a new user is created
		// and the setting for a change at irst login is active this flag wil be turned on
		$user_data = $this->aclManager->getUser($this->idst, false);
		if($user_data[ACL_INFO_FORCE_CHANGE] == 1) return 2;

		// password expiration?
		if(!$user_data[ACL_INFO_PWD_EXPIRE_AT]) return 0;
		if(Get::sett('pass_max_time_valid', '0') != '0') {

			$pwd_expire = fromDatetimeToTimestamp($user_data[ACL_INFO_PWD_EXPIRE_AT]);
			if(time() > $pwd_expire) return 1;
		}
		return 0;
	}

	/**
	 * This method load all security tokens associated to a section (course),
	 * test the match with user and save in user session positive ST
	 * @param string $section the section to load
	 **/
	public function saveUserSectionSTInSession($section) {
		$sprefix=$this->sprefix;

		if (!isset($_SESSION[$sprefix."_stlist"])) {
			$this->loadUserSectionST($section);
			$this->SaveInSession();
		}
	}

	/**
	 * This method test if in user's loaded ST ther'is a given ST
	 * @param int $st the security token to test
	 * @return bool TRUE, FALSE
	 **/
	public function matchUserST($st) {
		return in_array( $st, $this->arrst );
	}

	/**
	 * This method test if user has a role
	 * @param string $roleid the role to test
	 * @return bool TRUE, FALSE
	 **/
	public function matchUserRole($roleid) {

		if(!isset($GLOBALS['user_roles'])) $this->load_user_role();

		if ($this->user_level == ADMIN_GROUP_GODADMIN && $this->aclManager->getRole(false, $roleid) === FALSE ) return TRUE;

		return isset($GLOBALS['user_roles'][$roleid]);
	}

	/**
	 * This method test if user has one of given roles
	 * @param array $roles the array of roles to test
	 * @return bool TRUE, FALSE
	 **/
	public function matchUserRoles($roles) {

		if(!isset($GLOBALS['user_roles'])) $this->load_user_role();

		foreach( $roles as $r ) {

			if($this->matchUserRole($GLOBALS['user_roles'][$r])) return true;
		}
		return FALSE;
	}

	/**
	 * This method test if user has all passed roles
	 * @param array $roles the array of roles to test
	 * @return bool TRUE, FALSE
	 **/
	public function matchUserAllRoles($roles) {

		if(!isset($GLOBALS['user_roles'])) $this->load_user_role();

		foreach( $roles as $r ) {

			if(!$this->matchUserRole($GLOBALS['user_roles'][$r])) return false;
		}
		return TRUE;
	}

	/**
	 * Get refernce to DoceboACL
	 * @return DoceboACL the DoceboACL object
	 **/
	public function &getACL() {
		return $this->acl;
	}

	/**
	 * Get refernce to DoceboACLManager
	 * @return DoceboACLManager the DoceboACLManager object
	 **/
	public function &getACLManager() {
		return $this->acl->getACLManager();
	}

	public function getUserLevelId() {

 		return $this->user_level;
	}
/** Modifica per inversione cognome nome ... meglio nome cognome **/
	public function getUserName() {

		$user_info = $this->aclManager->getUser(getLogUserId(), false);
		return ( $user_info[ACL_INFO_FIRSTNAME].$user_info[ACL_INFO_LASTNAME]
			? $user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME]
			: $this->aclManager->relativeId($user_info[ACL_INFO_USERID]) );
	}

	public function getPreference($preference_path) {

		return $this->preference->getPreference($preference_path);
	}

	public function getQuotaLimit() {

		$user_quota = $this->preference->getPreference('user_rules.user_quota');
		if($user_quota == USER_QUOTA_INHERIT) $user_quota = Get::sett('user_quota');
		return $user_quota;
	}

	public function getUsedQuota() {

		$user_quota = $this->preference->getPreference('user_rules.user_quota_used');
		return $user_quota;
	}

	/**
	 * This public function return the myfile table
	 */
	public function getMyFilesTable()
	{
		return $GLOBALS['prefix_fw'].'_user_myfiles';
	}

	/**
	 * This public function return the setting user table
	 */
	public function getSettingUserTable()
	{
		return $GLOBALS['prefix_fw'].'_setting_user';
	}

	/**
	 * This public function update the used space of an user
	 * @$id_user --> The idst of the user to update
	 */
	public function updateUserUsedSpace($id_user)
	{
		$used_space = _US_EMPTY;

		$query = "SELECT SUM(size)
			FROM ".$this->getMyFilesTable()."
			WHERE owner = '".$id_user."'";

		$myfile_size = sql_fetch_row($this->db->query($query));

		if ($myfile_size[0])
			$used_space = $myfile_size[0];

		$control_query = "SELECT *" .
				" FROM ".$this->getSettingUserTable()."" .
				" WHERE id_user = '".$id_user."'" .
				" AND path_name = 'user_rules.user_quota_used'";

		$result = sql_fetch_row($this->db->query($control_query));

		if ($result[0])
		{
			$update_query = "UPDATE ".$this->getSettingUserTable()."" .
					" SET value = '".$used_space."'" .
					" WHERE id_user = '".$id_user."'" .
					" AND path_name = 'user_rules.user_quota_used'";

			if ($result = $this->db->query($update_query))
				return true;
			return false;
		}
		else
		{
			$insert_query = "INSERT INTO ".$this->getSettingUserTable()."" .
					" (path_name, id_user, value)" .
					" VALUES ('user_rules.user_quota_used', '".$id_user."', '".$used_space."')";

			if ($result = $this->db->query($insert_query))
				return true;
			return false;
		}
	}

}

function getLogUserId() {

	return Docebo::user()->getIdSt();
}

?>
