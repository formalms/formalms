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

define("UP_FILE_LIMIT", 8); 	// when display shared file limit the number showed in the first page of the profile
define("UP_FRIEND_LIMIT", 6); //*DEPRECATED*

define("PFL_POLICY_FREE", 0);
define("PFL_POLICY_TEACHER", 1);
define("PFL_POLICY_FRIENDS", 2); //*DEPRECATED*
define("PFL_POLICY_NOONE", 3);
define("PFL_POLICY_TEACHER_AND_FRIENDS", 4); //*DEPRECATED*

/**
 * @package admin-core
 * @subpackage user
 * @author Fabio Pirovano
 * @since 3.5.0
 *
 * This class will manage the action with performed by the profile (data access, view, etc.)
 */
class UserProfile {

	// class var =========================================================

	/**
	 * @var int the idst of the user
	 * @access private
	 */
	var $_id_user;

	/**
	 * @var int the idst of the viewer
	 * @access private
	 */
	var $_id_viewer;

	/**
	 * @var bool true if the profile must be printed in edit_mode
	 * @access private
	 */
	var $_edit_mode = false;

	var $_god_mode = false;

	var $_policy_mode = true;

	/**
	 * @var UrlManager the instance of the url manager
	 * @access private
	 */
	var $_url_man;

	/**
	 * @var UserProfileViewer the instance of the profile viewver
	 * @access private
	 */
	var $_up_viewer;

	/**
	 * @var UserProfileData the instance of the profile data manager
	 * @access private
	 */
	var $_up_data_man;

	/**
	 * @var DoceboLanguage the instance of the language manager
	 * @access private
	 */
	var $_lang;

	var $_module_name;

	var $_platform;

	var $_std_query;

	var $_varname_action;

	var $_use_avatar = true;

	var $_end_url = false;

	// class method =======================================================

	/**
	 * class constructor
	 */
	function UserProfile($id_user, $edit_mode = false) {

		$this->_id_user = $id_user;

		if($edit_mode !== false) $this->enableEditMode();
		else $this->disableEditMode();
	}

	// initialize functions ===========================================================

	/**
	 * initialize the various class used by this one
	 * @param string 		$std_query 	the std_query for the address
	 * @param resource_id 	$db_conn 	the id of a db connection if different form the standard
	 */
	function init($module_name, $platform, $std_query, $varname_action = 'ap', $db_conn = NULL) {

		$this->_module_name 	= $module_name;
		$this->_platform 		= $platform;
		$this->_std_query 		= $std_query;
		$this->_varname_action 	= $varname_action;

		$this->_id_viewer = getLogUserId();

		$this->_varname_action = $varname_action;

		$this->initLang($module_name, $platform);

		$this->initDataManager($db_conn);
		$this->initUrlManager($std_query);
		$this->initViewer($varname_action, $platform);

		$this->_setReference();

		$options = array('user_use_avatar' => (Get::sett('user_use_avatar', 'on') == 'on' ? true : false));

		$this->_use_avatar 	= $options['user_use_avatar'];

		$this->addStyleSheet($platform);
	}

	/**
	 * instance the viewer class of the profile
	 */
	function initViewer($varname_action, $platform) {

		$this->_up_viewer = new UserProfileViewer($this, $varname_action);
	}

	/**
	 * instance the data manager class of the profile
	 * @param resource_id $db_conn the database connnection
	 */
	function initDataManager($db_conn = NULL) {

		$this->_up_data_man = new UserProfileData($db_conn);
	}

	/**
	 * initialize the internal url manager instance
	 * @param string $std_query the std_query for the address
	 */
	function initUrlManager($std_query) {

		require_once(_base_.'/lib/lib.urlmanager.php');

		$this->_url_man =& UrlManager::getInstance();
		$this->_url_man->setStdQuery($std_query);
	}

	/**
	 * initialize the internal lang manager
	 * @param string $std_query the std_query for the address
	 */
	function initLang($module_name, $platform) {

		$this->_lang =& DoceboLanguage::createInstance('profile', 'framework');
	}

	/**
	 * set the reference trought the urlmanager, viever and data calsses
	 */
	function _setReference() {

		$this->_up_viewer->setUrlManager($this->_url_man);
		$this->_up_viewer->setDataManager($this->_up_data_man);

		$this->_up_data_man->setUserProfile($this);
	}

	function setCahceForUsers($arr_user) {

		$this->_up_data_man->setCacheForUsers($arr_user);
	}

	// setter and getter functions ==============================================

	/**
	 * return the id of the user actually used by the instance of the class
	 * @return int the idst of the user
	 */
	function getIdUser() { return $this->_id_user; }

	/**
	 * set the id of the user used by the instance of the class
	 * @param int $id_user the idst of the user to assign
	 *
	 * @return int the idst of the user assigned
	 */
	function setIdUser($id_user) { return $this->_id_user = $id_user; }


	/**
	 * return the id of the user that it's watching the profile
	 * @return int the idst of the viewer
	 */
	function getViewer() { return $this->_id_viewer; }

	/**
	 * set the id of the user used by the instance of the class
	 * @param int $id_user the idst of the user to assign
	 *
	 * @return int the idst of the user assigned
	 */
	function setViewer($id_viewer) { return ( $id_viewer === false ? $this->_id_viewer = getLogUserId() : $this->_id_viewer = $id_viewer ); }

	/**
	 * enable the edit mode for the profile
	 */
	function enableEditMode() { $this->_edit_mode = true; }

	/**
	 * disable the edit mode for the profile
	 */
	function disableEditMode() { $this->_edit_mode = false; }

	function enableModViewerPolicy() { $this->_policy_mod = true; }

	function disableModViewerPolicy() { $this->_policy_mod = false; }

	function enableGodMode() {

		$this->_edit_mode = true;
		$this->_god_mode = true;
	}

	function disableGodMode() {

		$this->_edit_mode = false;
		$this->_god_mode = false;
	}

	/**
	 * set the end url, when a flow of action is ended the profile jump to this url
	 */
	function setEndUrl($url) { $this->_end_url = $url; }

	/**
	 * @return UrlManager the url manager manager
	 */
	function &getUrlManager() { return $this->_url_man; }

	/**
	 * @return UserProfileData the data manager
	 */
	function &getDataManager() { return $this->_up_data_man; }

	/**
	 * @return DoceboLanguage the class used for lang
	 */
	function &getLang() { return $this->_lang; }

	/**
	 * @return bool true if the edit mode is acrìtive, false otherwise
	 */
	function editMode() { return $this->_edit_mode; }

	function godMode() { return $this->_god_mode; }

	function policyMode() { return $this->_policy_mode; }

	function useAvatar() { return $this->_use_avatar; }

	// function for standard image display =================================================

	/**
	 * add in the head area the style sheet for the profile
	 * @param strng $from_platform the code of the platform, is used to create the path to the correct style sheet
	 */
	function addStyleSheet($from_platform) { return $this->_up_viewer->addStyleSheet($from_platform); }

	/**
	 * print the title of the page
	 * @param mixed $text the title of the area, or the array with zone path and name
	 * @param string $image the image to load before the title
	 *
	 * @return string the html code for space open
	 */
	function getTitleArea() {

		return $this->_up_viewer->getTitleArea();
	}

	/**
	 * Print the head of the module space after the getTitle area
	 * @return string the html code for space open
	 */
	function getHead() {

		return $this->_up_viewer->getHead();
	}

	/**
	 * Print the footer of the module space
	 * @return string the html code for space close
	 */
	function getFooter() {

		return $this->_up_viewer->getFooter();
	}

	/**
	 * print the back command in the page
	 * @param string $url the url used for back, if not passed will be setted with the one of the urlmanager
	 */
	function backUi($url = false) {

		return $this->_up_viewer->getBackUi($url);
	}

	// function for profile management =======================================================

	/**
	 * return the label of the next action
	 * @return string the label of the next action to execute
	 */
	function getAction() {

		if(isset($_REQUEST[$this->_varname_action])) return Get::req($this->_varname_action, DOTY_MIXED, '');
		else return 'profileview';
	}

	/**
	 * this function manage the entire profile, identify the action to perform and do the right sequence of action
	 * @return string the html to display
	 */
	function performAction($viewer = false, $start_action = false) {

		$this->setViewer($viewer);

		if($start_action === false) $start_action = 'profileview';
		$ap = Get::req($this->_varname_action, DOTY_MIXED, $start_action);
		if(isset($_POST['undo'])) $ap = 'profileview';

		switch($ap) {
			case "goprofile" : {

				$ext_prof = new UserProfile(Get::req('id_user', DOTY_INT, 0), false );
				$ext_prof->init(	$this->_module_name,
									$this->_platform,
									$this->_std_query,
									$this->_varname_action  );
				return $ext_prof->getProfile();
			};
			break;
			// display the mod info gui -------------------------------
			case "mod_profile" : {
				return $this->getModUser();
			};
			break;
			case "mod_password" : {
				return $this->_up_viewer->getUserPwdModUi();
			};
			break;

			case "mod_policy" : {
				return $this->_up_viewer->modUserPolicyGui();
			};
			break;
			// save modified info of the user -------------------------
			case "save_policy" : {

				if($this->_up_data_man->setFieldAccessList($this->_id_user, $this->_up_viewer->getFilledPolicy())) {
					// all ok --------------------------------------
					$this->_up_viewer->unloadUserData();

					if($this->_end_url !== false) Util::jump_to($this->_end_url);

					return getResultUi($this->_lang->def('_OPERATION_SUCCESSFULPOLICY'))
						.$this->getProfile();
				} else {
					// some error saving ---------------------------
					return getErrorUi($this->_lang->def('_FAILSAVEPOLICY'))
						.$this->_up_viewer->modUserPolicyGui();
				}
			};
			break;
			// save modified info of the user -------------------------
			case "saveinfo" : {

				if(!$this->checkUserInfo()) {
					// some error in data filling ------------------
					return getErrorUi($this->_last_error)
						.$this->getModUser();
				}
				$model = new UsermanagementAdm();
				$oldUserdata = $model->getProfileData($this->_id_user);

				if($this->saveUserInfo()) {
					// all ok --------------------------------------
					$this->_up_viewer->unloadUserData();

					if($this->_end_url !== false) Util::jump_to($this->_end_url);
					if (isset($_GET['modname']) && $_GET['modname'] == 'reservation')
					{
						require_once($GLOBALS['where_lms'].'/lib/lib.reservation.php');
						$id_event = Get::req('id_event', DOTY_INT, 0);
						$man_res = new Man_Reservation();
						$result = $man_res->addSubscription(getLogUserId(), $id_event);
						Util::jump_to('index.php?modname=reservation&op=reservation');
					}
					else {
						// SET EDIT USER EVENT
						$event = new \appCore\Events\Core\UsersManagementEditEvent();
						$event->setUser($model->getProfileData($this->_id_user));
						$event->setOldUser($oldUserdata);
						\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\UsersManagementEditEvent::EVENT_NAME, $event);

						return getResultUi($this->_lang->def('_OPERATION_SUCCESSFULPROFILE')).$this->getProfile();
					}
				} else {
					// some error saving ---------------------------
					return getErrorUi($this->_lang->def('_OPERATION_FAILURE'))
						.$this->getModUser();
				}
			};break;
			// save password -----------------------------------------
			case "savepwd" : {

				$re = $this->_up_viewer->checkUserPwd();
				if($re !== true) {
					// some error in data filling --------------------
					return getErrorUi($re)
						.$this->_up_viewer->getUserPwdModUi();
				}
				if($this->saveUserPwd()) {					
					// all ok ----------------------------------------
					$this->_up_viewer->unloadUserData();

					// SET EDIT CHANGE PASSWORD EVENT
					$event = new \appCore\Events\Core\UsersManagementChangePasswordEvent();
					$model = new UsermanagementAdm();
					$event->setUser($model->getProfileData($this->_id_user));
					$event->setFilledPwd($this->_up_viewer->getFilledPwd());
					\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\UsersManagementChangePasswordEvent::EVENT_NAME, $event);

					if($this->_end_url !== false) Util::jump_to($this->_end_url);

					$out =  getResultUi($this->_lang->def('_OPERATION_SUCCESSFULPWD'));

					if(Get::sett('profile_only_pwd') == 'on') {
						// maybe is better if we display only the confirmation message if all is ok, but if you
						// want something else add the code here
					} else {
						$out .= $this->getProfile();
					}
					return $out;
				} else {
					// some error saving ----------------------------
					return getErrorUi($this->_lang->def('_OPERATION_FAILURE'))
						.$this->_up_viewer->getUserPwdModUi();
				}
			};break;

			case "uploadavatar" : {

				return $this->_up_viewer->modAvatarGui();
			};break;
			case "saveavatar" : {

				if($this->saveUserAvatar()) {
					// all ok --------------------------------------
					$this->_up_viewer->unloadUserData();

					if($this->_end_url !== false) Util::jump_to($this->_end_url);

					return getResultUi($this->_lang->def('_OPERATION_SUCCESSFULAVATAR'))
						.$this->getProfile();
				} else {
					// some error saving ---------------------------
					return getErrorUi($this->_lang->def('_OPERATION_FAILURE'))
						.$this->_up_viewer->modAvatarGui();
				}
			};break;
			// teacher curriculum
			case "del_teach_curric" : {
				return $this->_up_viewer->delTeacherCurriculumGui();
			};break;
			case "mod_teach_curric" : {
				return $this->_up_viewer->modTeacherCurriculumGui();
			};break;
			case "save_teach_curric" : {

				if($this->_up_data_man->saveTeacherCurriculumAndPublication($this->_id_user, $this->_up_viewer->getFilledCurriculum(), false)) {
					// all ok --------------------------------------
					$this->_up_viewer->unloadUserData();

					if($this->_end_url !== false) Util::jump_to($this->_end_url);

					return getResultUi($this->_lang->def('_OPERATION_SUCCESSFULCURRICULUM'))
						.$this->getProfile();
				} else {
					// some error saving ---------------------------
					return getErrorUi($this->_lang->def('_OPERATION_FAILURE'))
						.$this->_up_viewer->modTeacherCurriculumGui();
				}
			};break;
			case "mod_teach_publ" : {

				return $this->_up_viewer->modTeacherPublicationsGui();
			};break;
			case "save_teach_publ" : {

				if($this->_up_data_man->saveTeacherCurriculumAndPublication($this->_id_user, false, $this->_up_viewer->getFilledPublications())) {
					// all ok --------------------------------------
					$this->_up_viewer->unloadUserData();

					if($this->_end_url !== false) Util::jump_to($this->_end_url);

					return getResultUi($this->_lang->def('_OPERATION_SUCCESSFULPUBLICATIONS'))
						.$this->getProfile();
				} else {
					// some error saving ---------------------------
					return getErrorUi($this->_lang->def('_FAILSAVEPUBLICATIONS'))
						.$this->_up_viewer->modTeacherPublicationsGui();
				}
			};break;
			// display the profile ------------------------------------
			case "view_files" : {
				$this->_id_user = Get::req('id_user', DOTY_INT, getLogUserId() );
				return $this->_up_viewer->getViewUserFiles();
			};break;
			case "file_details" : {
				$this->_id_user = Get::req('id_user', DOTY_INT, getLogUserId() );
				return $this->_up_viewer->getViewUserFileDetail();
			};break;

			case "profileview" :
			default : {

				return $this->getProfile();
			};break;
		}
	}

	function getUserInfo($viewer = false) {

		if($viewer !== false) $this->setViewer($viewer);
		return $this->_up_viewer->getUserInfo();
	}

	function manualLoadUserData($user_info) {

		$this->_up_viewer->manualLoadUserData($user_info);
	}


	function userIdMailProfile($picture = false, $viewer = false, $intest = true) {
		$this->setViewer($viewer);
		return $this->_up_viewer->userIdMailProfile($picture, $viewer, $intest);
	}

	/**
	 * show user profile, print the standard user profile for the user considering the user setting
	 * @param int $viewer_id the id of the user that has request the profile, it is used for the permission
	 */
	function getProfile($viewer = false) {

		if($viewer !== false) $this->setViewer($viewer);

		// save the users view of the profile
		if($this->_id_viewer != $this->_id_user) {
			if($this->_id_viewer == getLogUserId()) {

				if(!Docebo::user()->isAnonymous()) $this->_up_data_man->addView($this->_id_user, $this->_id_viewer);
			} else {
				$acl_man =& Docebo::user()->getAclManager();
				$id_anonymous = $acl_man->getAnonymousId();
				if($this->_id_viewer !== $id_anonymous) $this->_up_data_man->addView($this->_id_user, $this->_id_viewer);
			}
		}
		// user info in general ( name, surname, custom fields, ... )
		return $this->_up_viewer->getUserInfo()

	 		// user info abput the community (stats, friend list, ...)
	 		.$this->_up_viewer->getCommunityInfo()

		 	// teacher profile, if the user is a teacher
		 	.$this->getUserTeacherProfile();
	}

	/**
	 * show a reduced version of the user profile
	 * @param int $viewer_id the id of the user that has request the profile, it is used for the permission
	 * @param string $picture the size of the picture, small, medium, big
	 */
	function tinyUserInfo($viewer = false, $picture = false) {

		if($viewer !== false) $this->setViewer($viewer);
		return $this->_up_viewer->tinyUserInfo($picture);
	}

	/**
	 * show only the essential info of a user
	 * @param int $viewer_id the id of the user that has request the profile, it is used for the permission
	 * @param string $picture the size of the picture, small, medium, big
	 */
	function minimalUserInfo($viewer = false, $picture = false, $link_to = false) {

		if($viewer !== false) $this->setViewer($viewer);
		return $this->_up_viewer->minimalUserInfo($picture, $link_to);
	}

	function homeUserProfile($picture = false, $viewer = false, $intest = true)
	{
		$this->setViewer($viewer);
		return $this->_up_viewer->homeUserProfile($picture, $viewer, $intest);
	}

    function homePhotoProfile($picture = false, $viewer = false, $intest = true)
    {
        $this->setViewer($viewer);
        return $this->_up_viewer->homePhotoProfile($picture, $viewer, $intest);
    }    
    
    
    
	/**
	 * show only username and avatar
	 * @param int $viewer_id the id of the user that has request the profile, it is used for the permission
	 * @param string $picture the size of the picture, small, medium [=default], big
	 */
	function getUserPanelData($viewer = false, $picture = false) {

		if($viewer !== false) $this->setViewer($viewer);
		return $this->_up_viewer->getUserPanelData($picture);
	}


	/**
	 * show user profile, print the modification mask for user profile for the user considering the user setting
	 * @param int $viewer_id the id of the user that has request the profile, it is used for the permission
	 */
	function getModUser() {

		return $this->_up_viewer->getUserInfoModUi();
	}

	/**
	 * return the resolved username (lastname firstaname or, if blank the userid)
	 * @param bool $name_only if true when resolve use only the firstname of the user
	 *
	 * @return string the resolved username
	 */
	function resolveUsername($name_only = false) {

		return $this->_up_viewer->resolveUsername($name_only);
	}

	/**
	 * check the user info filled in the mod gui
	 * @return bool true if all data was filled correctly, false in case of trouble, you can find the error in $this->_last_error
	 */
	function checkUserInfo() {

		$re = $this->_up_viewer->checkUserInfo();
		if($re === true) return true;
		$this->_last_error = $re;
		return false;
	}

	/**
	 * save the user info displaied by  the mod gui
	 * @return bool true if all data was saved correctly, false in case of trouble
	 */
	function saveUserInfo() {

		$filled = $this->_up_viewer->getFilledData();
		return $this->_up_data_man->saveUserData($this->_id_user, $filled, true, true);
	}

	/**
	 * save the user password displaied by  the mod gui
	 * @return bool true if all data was saved correctly, false in case of trouble
	 */
	function saveUserPwd() {

		$filled = $this->_up_viewer->getFilledPwd();
		return $this->_up_data_man->saveUserPwd($this->_id_user, $filled);
	}

	function getUserPhotoOrAvatar($dimension = 'medium') {
		return $this->_up_viewer->getAvailablePhotoAvatar($dimension);
	}

	/**
	 * save the user avatar displaied by the mod avatar gui
	 * @return bool true if all data was saved correctly, false in case of trouble
	 */
	function saveUserAvatar() {

		$avatar = $this->_up_viewer->getFilledAvatar();
		if($avatar == 'delete_current') {

			return $this->_up_data_man->deleteAvatarData($this->_id_user);
		} elseif($avatar == 'nop') {

			return true;
		}
		return $this->_up_data_man->saveAvatarData($this->_id_user, $avatar, $this->_up_viewer->max_dim_avatar, $this->_up_viewer->max_dim_avatar);
	}

	/**
	 * return teacher profile
	 */
	function getUserTeacherProfile($viewer = false, $link_to = false) {

		if($viewer !== false) $this->setViewer($viewer);

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();

		if ($pl_man->isLoaded('lms')) {

			return $this->_up_viewer->getUserTeacherProfile($this->_id_user, $link_to);

		} else return '';
	}

	/**
	 * return some stats for the user relative to the lms platform
	 */
	function getUserLmsStat($viewer = false) {

		if($viewer !== false) $this->setViewer($viewer);

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();

		//if($pl_man->isLoaded('lms')) {

			//if($this->_up_data_man->isTeacher($this->_id_user)) {

				$user_stat = $this->_up_data_man->getUserCourseStat($this->_id_user);
				return $this->_up_viewer->getUserCourseStatUi($user_stat);
			//}
		//}
		return '';
	}

  /**
   * return list of competences of the user
   */
  function getUserCompetencesList($viewer = false) {

		if($viewer !== false) $this->setViewer($viewer);

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();

	//	if($pl_man->isLoaded('lms')) {
				$user_comp = $this->_up_data_man->getUserCompetences($this->_id_user);
				return $this->_up_viewer->getUserCompetences($user_comp);
		//}
		//return '';
	}

	 /**
   * return list of competences of the user
   */
  function getUserFunctionalRolesList($viewer = false) {

		if($viewer !== false) $this->setViewer($viewer);

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();

		//if($pl_man->isLoaded('lms')) {
				$user_fncroles = $this->_up_data_man->getUserFunctionalRoles($this->_id_user);
				return $this->_up_viewer->getUserFunctionalRoles($user_fncroles);
		//}
		//return '';
	}


	function getUserGroupsList($viewer = false) {
		if($viewer !== false) $this->setViewer($viewer);

		require_once(_base_.'/lib/lib.platform.php');
		$pl_man =& PlatformManager::CreateInstance();

		//if($pl_man->isLoaded('lms')) {
				$user_groups = $this->_up_data_man->getUserGroupsList($this->_id_user);
				return $this->_up_viewer->getUserGroupsList($user_groups);
		//}
		//return '';
	}

}

// ========================================================================================================== //
// ========================================================================================================== //
// ========================================================================================================== //

/**
 * @category library
 * @package user_management
 * @subpackage profile
 * @author Fabio Pirovano
 * @since 3.1.0
 *
 * This class will manage the display of the data readed by the data manager
 */
class UserProfileViewer {

	/**
	 * @var UserProfile the instance of the profile
	 * @access protected
	 */
	var $_user_profile;

	/**
	 * @var UrlManager the instance of the url manager
	 * @access protected
	 */
	var $_url_man;

	/**
	 * @var DoceboLanguage the instance of the language manager
	 * @access protected
	 */
	var $_lang;

	/**
	 * @var UserProfileData the instance of the profile data manager
	 * @access protected
	 */
	var $_up_data_man;

	var $_id_user;

	/**
	 * @var array cache for user info
	 * @access protected
	 */
	var $user_info = false;

	/**
	 * @var string the name of the var in GET,POST in which the next acrion is contained
	 * @access protected
	 */
	var $_varname_action;

	var $max_dim_avatar = 150;

	var $_next_refresh = false;

	var $_alredy_init = false;

	/**
	 * class constructor
	 */
	function UserProfileViewer(&$user_profile, $varname_action = 'ap') {

		$this->_user_profile =& $user_profile;
		$this->acl_man = Docebo::user()->getAclManager();

		$this->_lang =& $this->_user_profile->getLang();

		$this->_id_user = $this->_user_profile->getIdUser();

		$this->_varname_action = $varname_action;
	}

	function getViewer() {

		return $this->_user_profile->getViewer();
	}

	function setVarAction($varname_action) {

		$this->_varname_action = $varname_action;
	}

	/**
	 * set the reference to the UrlManager
	 * @param UrlManager $url_man the url manager instance
	 */
	function setUrlManager(&$url_man) {

		$this->_url_man =& $url_man;
	}

	/**
	 * set the reference to the DataManager
	 * @param UserProfileData $up_data_man the data manager instance
	 */
	function setDataManager(&$up_data_man) {

		$this->_up_data_man =& $up_data_man;
	}

	//--------------------------------------------------------------------------------------//
	//- Some display related function ------------------------------------------------------//
	//--------------------------------------------------------------------------------------//

	/**
	 * print the title of the page
	 * @param mixed $text the title of the area, or the array with zone path and name
	 * @param string $image the image to load before the title
	 *
	 * @return string the html code for space open
	 */
	function getTitleArea() {

		return '';
	}

	/**
	 * Print the head of the module space after the getTitle area
	 * @return string the html code for space open
	 */
	function getHead() {

		return '<div class="std_block">'."\n";
	}

	/**
	 * Print the footer of the module space
	 * @return string the html code for space close
	 */
	function getFooter() {

		return '</div>'."\n";
	}

	/**
	 * print the back command in the page
	 * @param string $url the url used for back, if not passed will be setted with the one of the urlmanager
	 */
	function backUi($url = false) {

		if($url === FALSE && $this->_url_man != false) $url = $this->_url_man->getUrl();
		else $url = '';
		return getBackUi($url, $this->_lang->def( '_BACK' ));
	}

	/**
	 * add in the head area the style sheet for the profile
	 * @param strng $from_platform the code of the platform, is used to create the path to the correct style sheet
	 */
	function addStyleSheet($from_platform) {
		return true;
	}

	//--------------------------------------------------------------------------------------//
	//- Load user data ---------------------------------------------------------------------//
	//--------------------------------------------------------------------------------------//

	function manualLoadUserData($user_info) {

		$this->user_info = $user_info;
	}

	/**
	 * cache the user data  for internal use
	 */
	function loadUserData($viewer = false) {

		$this->_id_user = $this->_user_profile->getIdUser();
		if($this->_user_profile->godMode()) {

			$this->user_info = $this->_up_data_man->getUserDataNoRestriction($this->_id_user);
		} else {

			$this->user_info = $this->_up_data_man->getUserData($this->_id_user, $this->_next_refresh);
		}
		$this->_next_refresh = false;
	}

	/**
	 * unload the user data cached
	 */
	function unloadUserData() {

		$this->user_info = false;
		$this->_next_refresh  = true;
	}

	/**
	 * resolve the username(name surname or username)
	 * @return string return the username
	 */
	function resolveUsername($name_only = false) {

		$this->loadUserData();
		if($name_only) {

			return ( $this->user_info[ACL_INFO_FIRSTNAME]
				? $this->user_info[ACL_INFO_FIRSTNAME]
				: $this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) );
		} else {

			return ( $this->user_info[ACL_INFO_LASTNAME].$this->user_info[ACL_INFO_FIRSTNAME]
				? $this->user_info[ACL_INFO_LASTNAME].' '.$this->user_info[ACL_INFO_FIRSTNAME]
				: $this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) );
		}
	}

	function loadUserField() {

		if($this->_user_profile->godMode()) {

			return $this->_up_data_man->getUserFieldNoRestriction($this->_user_profile->getIdUser());
		} else {

			return $this->_up_data_man->getUserField($this->_user_profile->getIdUser());
		}
	}

	function loadUserContact() {

		if($this->_user_profile->godMode()) {

			return $this->_up_data_man->getUserContactNoRestriction($this->_user_profile->getIdUser());
		} else {

			return $this->_up_data_man->getUserContact($this->_user_profile->getIdUser());
		}
	}

	function getPlayField() {

		return $this->_up_data_man->getPlayField($this->_user_profile->getIdUser(), $this->_user_profile->godMode());
	}

	//--------------------------------------------------------------------------------------//
	//- Main gui functions -----------------------------------------------------------------//
	//--------------------------------------------------------------------------------------//

	/**
	 * this function simplify the display of an avatar
	 *
	 * @return string the path to the avatar
	 */
	function getPASrc($image, $alt, $base_reduce_class) {

		if($image == '') return;
		$img_size = @getimagesize($GLOBALS['where_files_relative'].$this->_up_data_man->getPAPath().$image);

		$class_image = '';
		if($img_size[0] > $this->max_dim_avatar && $img_size[0] > $img_size[1]) $class_image .= $base_reduce_class.' boxed_width';
		elseif($img_size[1] > $this->max_dim_avatar) $class_image .= ' '.$base_reduce_class.' boxed_height';

		return '<img'.( $class_image != '' ? ' class="'.$class_image.'"' : '' ).' '
			.'src="'.$GLOBALS['where_files_relative'].$this->_up_data_man->getPAPath().$image.'" '
			.'alt="'.$alt.'" />';
	}

    
    function getPASrcHome($image, $alt, $base_reduce_class) {


        return '<img class=boxedhome width=30px; src="'.$GLOBALS['where_files_relative'].$this->_up_data_man->getPAPath().$image.'" ></img> &nbsp; ';
    }    
    
    
    
	function getPhotoLimit($dimension) {

		$class_picture = false;
		switch($dimension) {
			case "micro" : {
				$class_picture = 'image_limit_micro';
				$max_dim = '28';
			};break;
			case "small" : {
				$class_picture = 'image_limit_small';
				$max_dim = '50';
			};break;
			case "normal" : {
				$class_picture = 'image_limit_normal';
				$max_dim = '100';
			};break;
			case "medium" : {
				$class_picture = 'image_limit_medium';
				$max_dim = '150';
			};break;
			case "large" : {
				$class_picture = 'image_limit_big';
				$max_dim = '250';
			};break;
		}
		if($class_picture == false) {
			$class_picture = 'image_limit_medium';
			$max_dim = '150';
		}
		return array($class_picture, $max_dim);
	}

	public function getAvailablePhotoAvatar($dimension = 'medium') {
       
		list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($dimension);
		
        // avatar ------------------------------------------------------------------------------
		if($this->user_info[ACL_INFO_AVATAR] != "")
		{
			return $this->getPASrc(	$this->user_info[ACL_INFO_AVATAR],
									$this->_lang->def('_AVATAR'),
									$class_picture);
		}
        
		$img_size = getimagesize(getPathImage().'standard/user.png');

		$class_image = '';
		if($img_size[0] > $this->max_dim_avatar && $img_size[0] > $img_size[1]) $class_image .= $class_picture.'_width';
		elseif($img_size[1] > $this->max_dim_avatar) $class_image .= ' '.$class_picture.'_height';
          
     
                
		return '<img'.( $class_picture != '' ? ' class="'.$class_picture.'"' : '' ).' '
			.'src="'.getPathImage().'standard/user.png'.'" '
			.'alt="'.$this->_lang->def('_NOAVATAR').'" />';
	}

	function initAjax() {

		if(!isset($GLOBALS['page'])) return;
		if($this->_alredy_init) return;

		YuiLib::load();
		//addJs($GLOBALS['where_framework_relative'].'/lib/', 'ajax.user_profile.js');
		Util::get_js(Get::rel_path('base').'/lib/ajax.user_profile.js', true, true);

		$lang =& DoceboLanguage::createInstance( $this->_user_profile->_module_name, $this->_user_profile->_platform);

		$GLOBALS['page']->add('<script type="text/javascript">'

			."	setup_user_profile('".$GLOBALS['where_framework_relative']."/ajax.adm_server.php?file=user_profile', "
			." '".getPathImage('fw')."' ); "

			." var user_profile_lang = {"
				//."_TITLE_ASK_A_FRIEND : '"	.addslashes($lang->def('_TITLE_ASK_A_FRIEND'))."', "
				//."_WRITE_ASK_A_FRIEND : '"	.addslashes($lang->def('_WRITE_ASK_A_FRIEND'))."', "
				."_SEND : '"			.addslashes($lang->def('_SEND'))."',  "
				."_UNDO : '"					.addslashes($lang->def('_UNDO'))."', "
				."_ASK_FRIEND_SEND : '"		.addslashes($lang->def('_SEND'))."', "
				."_ASK_FRIEND_FAIL : '"		.addslashes($lang->def('failed'))."',  "
				."_SUBJECT :  '"		.addslashes($lang->def('_SUBJECT'))."', "
				."_MESSAGE_TEXT : '"			.addslashes($lang->def('_MESSAGE_TEXT'))."',  "
				."_OPERATION_SUCCESSFUL : '"			.addslashes($lang->def('_OPERATION_SUCCESSFUL'))."',  "
				."_OPERATION_FAILURE : '"			.addslashes($lang->def('_OPERATION_FAILURE'))."' "
			."};"

			.'</script>', 'page_head');

		$this->_alredy_init = true;
	}

	/**
	 * this function is used to print the row with the user data
	 * @param string $head the header of the data (i.e. name)
	 * @param string $head the header of the data (i.e. name)
	 */
	function getUIRowCode($head, $data) {

		return '<tr><th scope="row">'.$head.'</th><td>'.$data.'</td></tr>';
	}

	function getUserRelatedAction($selected, $with_file = true, $reduced = false) {

		$this->initAjax();
		$from = Get::req('from', DOTY_INT, 0);
		$viewer = $this->getViewer();

		YuiLib::load(array(
			'animation' 		=> 'animation-min.js',
			'dragdrop' 			=> 'dragdrop-min.js',
			'button' 			=> 'button-min.js',
			'container' 		=> 'container-min.js',
			'my_window' 		=> 'windows.js'
		), array(
			'container/assets/skins/sam' => 'container.css',
			'button/assets/skins/sam' => 'button.css'
		));

		//$pre = ( $reduced === true ? 'l_' : '' );

		$is_friend 	= $this->_up_data_man->isFriend($viewer, $this->_id_user, true);
		$is_online 	= $this->_up_data_man->isOnline($this->_id_user);
		//$send_msg 	= $this->_up_data_man->canSendMessage($this->_id_user, $viewer);

		$action = array();

		if($with_file) {

			$files_info = $this->_up_data_man->getFileInfo($this->_id_user, $viewer);
			$id_thread = Get::req('idThread', DOTY_INT, 0);
			$file_type = array('image', 'video', 'audio', 'other');
			$id_thread = Get::req('idThread', DOTY_INT, 0);
			foreach($file_type as $type) {

				if($files_info[$type]) {

					$action[] = '<li>'
						.'<a href="'.$this->_url_man->getUrl('id_user='.$this->_id_user.'&'.$this->_varname_action.'=view_files&type='.$type.'&idThread='.$id_thread.'&from='.$from).'" title="'.$this->_lang->def('_VIEW_USER_'.strtoupper($type)).'">'
							.'<img src="'.getPathImage('fw').'profile/'.$pre.$type.'.png" alt="'.$this->_lang->def('_EN_USER_'.strtoupper($type).'_ALT').'" />'
							.'</a>'
						.'</li>';
				}

			} // end for
		}
		if($is_online && $is_online !== 'unk') {

			$action[] = '<li>'
				//.'<img src="'.getPathImage('fw').'standard/online.png" alt="'.$this->_lang->def('_USERONLINE').'" /> '
				.'<span class="glyphicon glyphicon-record text-success"></span>'
				//.$this->_lang->def('_USERONLINE')
				.'</li>';
		} else/*if(!$reduced)*/ {

			$action[] = '<li>'
				//.'<img src="'.getPathImage('fw').'standard/offline.png" alt="'.$this->_lang->def('_UP_OFFLINE').'" /> '
			.'<span class="glyphicon glyphicon-record text-danger"></span>'
				//.$this->_lang->def('_UP_OFFLINE')
				.'</li>';
		}

		// action with the user ---------------------------------------------------------------
		if(empty($action)) return '';

		$html = '<ul class="list_user_actions">'
			.implode('', $action)
			.'</ul>';

		return $html;
	}

	/**
	 * gui for user info in general ( name, surname, custom fields, ... )
	 */
	function getUserInfo() {

		$viewer = $this->getViewer();

		$this->loadUserData($viewer);
		$user_field 	= $this->loadUserField($viewer);
		$user_contacts 	= $this->loadUserContact($viewer);

		$max_quota 		= $this->_up_data_man->getQuotaLimit( $this->_user_profile->getIdUser() );
		$actual_size 	= $this->_up_data_man->getUsedQuota( $this->_user_profile->getIdUser() );

		$edit_mode 		= $this->_user_profile->editMode();

		$class_picture = 'image_limit_medium';
		$this->max_dim_avatar = '150';

		// main container ---------------------------------------------------------------------
		$html="<div style=\"width:95%;\">";

		$html .= '<h3>'.$this->_lang->def('_PROFILE').': '.$this->resolveUsername().'</h3>';

		$html .= '<div id="up_user_info" class="up_user_info">';

		// avatar -------------------------------------------------------------------
		if($this->_user_profile->useAvatar())
		{
			$html .= '<div class="up_img_container">';
		}

		// show avatar ------------------------------------------------------------------------

		if($this->_user_profile->useAvatar()) {

			$html .= ( $edit_mode ? '<a href="'.$this->_url_man->getUrl($this->_varname_action.'=uploadavatar').'" title="'.$this->_lang->def('_MOD').'">' : '' )

				.( ($this->user_info[ACL_INFO_AVATAR] != "")
					? $this->getPASrc(	$this->user_info[ACL_INFO_AVATAR],
										$this->_lang->def('_AVATAR'),
										$class_picture )
					: '<img src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" />' )
				.'<br />'
				. $this->_lang->def('_AVATAR')

				.( $edit_mode ? '</a>' : '' );
		}
		// end avatar --------------------------------------------------------------------------
		if($this->_user_profile->useAvatar()) {
			$html .= '<br />';
		}

		if($this->_user_profile->useAvatar()) {
			$html .= '</div>';
		}

		$html .= $this->getUserRelatedAction('profile');

		// user standard info -----------------------------------------------------------------
		$html .= '<table class="up_user_field" summary="'.$this->_lang->def('_USERPROFILE_SUMMARY').'">'
				.'<caption class="up_name">'.$this->resolveUsername().'</caption>';

		$html .= $this->getUIRowCode($this->_lang->def('_USERNAME'), 	$this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) )
			.$this->getUIRowCode($this->_lang->def('_LASTNAME'),  ( $this->user_info[ACL_INFO_LASTNAME] !== false
					? $this->user_info[ACL_INFO_LASTNAME]
					: $this->_lang->def('_HIDDEN') ) )

			.$this->getUIRowCode($this->_lang->def('_FIRSTNAME'), ( $this->user_info[ACL_INFO_FIRSTNAME] !== false
					? $this->user_info[ACL_INFO_FIRSTNAME]
					: $this->_lang->def('_HIDDEN') ) );

		// user extra field ------------------------------------------------------------------

		if(!empty($user_field))
		while(list(, $value) = each($user_field)) {

			$html .= $this->getUIRowCode($value['name'], $value['value']);
		}

		if($this->_user_profile->godMode()) {

			// show user level
			$lv_lang =& DoceboLanguage::createInstance('admin_directory', 'framework');

			$acl_man =& Docebo::user()->getAclManager();
			switch($acl_man->getUserLevelId($this->_user_profile->getIdUser())) {
				case ADMIN_GROUP_GODADMIN 	: $user_level_string = $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_GODADMIN);break;
				case ADMIN_GROUP_ADMIN 		: $user_level_string = $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_ADMIN);break;
				case ADMIN_GROUP_USER 		: $user_level_string = $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_USER);break;
				default :$user_level_string = $acl_man->getUserLevelId($this->_user_profile->getIdUser());
			}
			$html .= $this->getUIRowCode($this->_lang->def('_LEVEL'), $user_level_string);
		}
		if($viewer == $this->_id_user) {

			// convert from bytes in mbytes
			$actual_size = number_format( ($actual_size / (1024*1024)), '2');
			$percent = ( $actual_size != 0 ?  number_format( (($actual_size / $max_quota) * 100), '2')  : '0' );
			$html .= '<tr><th scope="row" id="up_quota">'.$this->_lang->def('_USER_QUOTA').'</th>'
				.'<td>'
				.''.$actual_size.' / '.$max_quota.' MB '
				.( $max_quota == USER_QUOTA_UNLIMIT
							? ' '.$actual_size.' MB / '.$this->_lang->def('_UNLIMITED_QUOTA').' '
							: Util::draw_progress_bar($percent, true, 'progress_bar up_quota_bar', false, false)
						)
				.'</td></tr>';
		}

		$html .= '<tr><th scope="col" colspan="2" id="up_type2">'.$this->_lang->def('_CONTACTS').'</th></tr>';

		// end extra field -------------------------------------------------------------------

		if(!empty($user_contacts))
		while(list(, $value) = each($user_contacts)) {

			if($value['head']) $GLOBALS['page']->add($value['head'], 'page_head');
			$prefix = '';
			$suffix = '';
			if($value['image']) {

				// attach as prefix the image
				$prefix = '<img class="up_'.$value['field_type'].'"
								src="'.$value['image'].'"
								alt="'.$this->_lang->def('_ALT_'.strtoupper($value['field_type'])).'" /> ';
			}
			if($value['href']) {

				//attach link
				$prefix = '<a href="'.$value['href'].'">'.$prefix;
				if($prefix != '')  $prefix .= '</a>';
				else $suffix .= '</a>';
			}
			$html .= $this->getUIRowCode($prefix.$value['name'].$suffix, $value['value']);
		}
		$html .= '<tr><th scope="row" id="up_email">'.$this->_lang->def('_EMAIL').'</th>'
			.'<td>'
			.( $this->user_info[ACL_INFO_EMAIL] !== false
				? '<a href="mailto:'.$this->user_info[ACL_INFO_EMAIL].'">'.$this->user_info[ACL_INFO_EMAIL].'</a>'
				: $this->_lang->def('_HIDDEN') )
			.'</td></tr>';

		// end print contacts ----------------------------------------------------------------
		$html .= '</table>';

		// close the floating of the photo and avatar ----------------------------------------
		$html .= '<div class="nofloat"></div>';

		//signature --------------------------------------------------------------------------
		$html .= '<b id="up_signature_b">'.$this->_lang->def('_SIGNATURE').':</b>'
				.'<div id="up_signature">'.$this->user_info[ACL_INFO_SIGNATURE].'</div>';

		// link to modify
		if($edit_mode) {
			$html .= '<ul class="up_profile_action">';
			if($this->_user_profile->policyMode()) {

				$html .='<li id="up_modify_policy">'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=mod_policy').'"
							 title="'.$this->_lang->def('_VIEW_PERMISSION').'">'
							.$this->_lang->def('_CHANGEPOLICY').'</a>'
					.'</li>';
			}
			if(!$this->_user_profile->godMode()) {

				$html .= '<li id="up_modify_pwd">'
							.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=mod_password').'"
								 title="'.$this->_lang->def('_CHANGEPASSWORD').'">'
								.$this->_lang->def('_CHANGEPASSWORD').'</a>'
						.'</li>';
			}
			$html .= '<li id="up_modify_profile">'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=mod_profile').'"
							 title="'.$this->_lang->def('_MOD').'">'
							.$this->_lang->def('_MOD').'</a>'
					.'</li>'
					.'</ul>';
		}
		$html .= '</div>';

		$html .= '</div>';
		return $html;
	}

	function getViewUserFiles() {

		require_once(_base_.'/lib/lib.navbar.php');

		$viewer = $this->getViewer();
		$type 	= Get::req('type', DOTY_MIXED, '');

		$this->loadUserData($viewer);

		// main container ---------------------------------------------------------------------
		$html = '<h1>'.$this->_lang->def('_PROFILE').': '.$this->resolveUsername().'</h1>';
		$html .= '<div id="up_user_info" class="up_user_info">';

		$html .= $this->getUserRelatedAction($type);

		require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');

		$user_file = new MyFilesPolicy(	$this->_user_profile->getIdUser(),
										$this->getViewer(),
										$this->_up_data_man->isFriend($this->_user_profile->getIdUser(), $this->getViewer()),
										$this->_up_data_man->isTeacher($this->getViewer())
									);

		$num_files 	= $user_file->getFileCount($type);

		$nav_bar 	= new NavBar('ini', UP_FILE_LIMIT, $num_files, 'link');
		$ini 		= $nav_bar->getSelectedElement();
		$nav_bar->setLink($this->_url_man->getUrl($this->_varname_action.'=view_files&type='.$type.''));

		$re_files 	= $user_file->getFileList($type, false, $ini, UP_FILE_LIMIT);

		switch($type) {

			case "image" : {

				$html .= '<h2 class="up_type1">'.$this->_lang->def('_IMAGES').'</h2>'

					.$nav_bar->getNavBar($ini)
					.'<div class="up_box_files">';
				if($re_files && $num_files > 0) {

					$html .= '<ul class="image_list">';
					while($image = $user_file->fetch_row($re_files)) {

						$size = @getimagesize($GLOBALS['where_files_relative'].$user_file->getFileAddress($image[MYFILE_FILE_NAME]));

						$class_limit = '';
						if(($size[0] > 200) && ($size[0] > $size[1])) $class_limit = 'image_limit_width';
						if($size[1] > 200 && ($size[0] < $size[1])) $class_limit = 'image_limit_height';
						$html .= '<li>'
								.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=file_details&id_user='.$this->_id_user.'&type='.$type.'&id_file='.$image[MYFILE_ID_FILE]).'">'
								.'<img class="user_image '.$class_limit.'" src="'.$GLOBALS['where_files_relative'].$user_file->getFileAddress($image[MYFILE_FILE_NAME]).'" '
									.'title="'.strip_tags($image[MYFILE_DESCRIPTION]).'" alt="'.strip_tags($image[MYFILE_TITLE]).'" />'
								.'<br/>'
								.$image[MYFILE_TITLE]
								.'</a>'
								.'</li>';


					}
					$html .= '</ul>';
				} else {
					$html .= '<p>'.$this->_lang->def('_NO_IMAGE_FOUND').'</p>';
				}
				$html .= '<div class="nofloat"></div>'
					.'</div>';

			};break;
			case "video" : {

				$html .= '<h2 class="up_type1">'.$this->_lang->def('_USER_VIDEOS').'</h2>'

					.$nav_bar->getNavBar($ini)
					.'<div class="up_box_files">';
				if($re_files && $num_files > 0) {
					require_once(_base_.'/lib/lib.multimedia.php');

					$html .= '<ul class="video_list">';
					while($video = $user_file->fetch_row($re_files)) {

						$play_code = getEmbedPlay(	$GLOBALS['where_files_relative'].$user_file->getFilePath(),
												$video[MYFILE_FILE_NAME],
												false,
												'250',
												false,
												false );

						$class_limit = '';
						$html .= '<li>'
								.( $play_code === false
									? implode('_', array_slice(explode('_', $video[MYFILE_FILE_NAME]), 3))
									: $play_code )
								.'<br />'
								.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=file_details&id_user='.$this->_id_user.'&type='.$type.'&id_file='.$video[MYFILE_ID_FILE]).'">'
								.$video[MYFILE_TITLE].( $play_code === false ? ' ('.$this->_lang->def('_DOWNLOAD').')' : '' )
								.'</a>'
								.'</li>';
					}
					$html .= '</ul>';
				} else {
					$html .= '<p>'.$this->_lang->def('_NO_IMAGE_FOUND').'</p>';
				}
				$html .= '<div class="nofloat"></div>'
					.'</div>';

			};break;
			case "audio" : {

				$html .= '<h2 class="up_type1">'.$this->_lang->def('_USER_AUDIO').'</h2>'

					.$nav_bar->getNavBar($ini)
					.'<div class="up_box_files">';
				if($re_files && $num_files > 0) {
					require_once(_base_.'/lib/lib.multimedia.php');

					$html .= '<ul class="video_list">';
					while($audio = $user_file->fetch_row($re_files)) {

						$play_code = getEmbedPlay(	$GLOBALS['where_files_relative'].$user_file->getFilePath(),
												$audio[MYFILE_FILE_NAME],
												false,
												'250',
												false,
												false );

						$class_limit = '';
						$html .= '<li>'
								.( $play_code === false
									? implode('_', array_slice(explode('_', $audio[MYFILE_FILE_NAME]), 3))
									: $play_code )
								.'<br />'
								.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=file_details&id_user='.$this->_id_user.'&type='.$type.'&id_file='.$audio[MYFILE_ID_FILE]).'">'
								.$audio[MYFILE_TITLE].( $play_code === false ? ' ('.$this->_lang->def('_DOWNLOAD').')' : '' )
								.'</a>'
								.'</li>';
					}
					$html .= '</ul>';
				} else {
					$html .= '<p>'.$this->_lang->def('_NO_IMAGE_FOUND').'</p>';
				}
				$html .= '<div class="nofloat"></div>'
					.'</div>';

			};break;
			case "other" : {

				$html .= '<h2 class="up_type1">'.$this->_lang->def('_USER_OTHER').'</h2>'

					.$nav_bar->getNavBar($ini)
					.'<div class="up_box_files">';
				if($re_files && $num_files > 0) {
					require_once(_base_.'/lib/lib.multimedia.php');

					$html .= '<ul class="other_list">';
					while($other = $user_file->fetch_row($re_files)) {

						$class_limit = '';
						$html .= '<li>'
								.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=file_details&id_user='.$this->_id_user.'&type='.$type.'&id_file='.$other[MYFILE_ID_FILE]).'">'
								.'<img src="'.getPathImage('fw').mimeDetect($user_file->getFilePath().$other[MYFILE_FILE_NAME]).'" alt="myme-type" />'
								.$other[MYFILE_TITLE]
								.'</a>'
								.'<br/>'
								.$other[MYFILE_DESCRIPTION]
								.'</li>';
					}
					$html .= '</ul>';
				} else {
					$html .= '<p>'.$this->_lang->def('_NO_IMAGE_FOUND').'</p>';
				}
				$html .= '<div class="nofloat"></div>'
					.'</div>';

			};break;
		}
		$html .= $nav_bar->getNavBar($ini);
		$html .= '</div>';
		return $html;
	}

	function getViewUserFileDetail() {

		require_once(_base_.'/lib/lib.navbar.php');

		$viewer = $this->getViewer();
		$type 	= Get::req('type', DOTY_MIXED, '');
		$id_file = Get::req('id_file', DOTY_MIXED, '');

		$this->loadUserData($viewer);

		// main container ---------------------------------------------------------------------
		$html = '<h1>'.$this->_lang->def('_PROFILE').': '.$this->resolveUsername().'</h1>';
		$html .= '<div id="up_user_info" class="up_user_info">';

		$html .= $this->getUserRelatedAction($type);

		require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');

		$user_file = new MyFilesPolicy(	$this->_user_profile->getIdUser(),
										$this->getViewer(),
										$this->_up_data_man->isFriend($this->_user_profile->getIdUser(), $this->getViewer()),
										$this->_up_data_man->isTeacher($this->getViewer())
									);

		$file 	= $user_file->getFileInfo($id_file);

		if($file == false) {
			$html .= $this->_lang->def('_THE_FILE_DOESNT_EXIST')
				.'</div>';
			return $html;
		}

		$html .= '<h2 class="up_type1">'.$file[MYFILE_TITLE].'</h2>';
		if($file[MYFILE_DESCRIPTION] != '') {

			$html .= '<div class="up_description">'
				.'<b>'.$this->_lang->def('_DESCRIPTION').': </b>'
				.$file[MYFILE_DESCRIPTION]
				.'</div>';
		}
		$id_thread = Get::req('idThread', DOTY_INT, 0);
		$html .= getBackUi( $this->_url_man->getUrl('id_user='.$this->_id_user.'&'.$this->_varname_action.'=view_files&type='.$type.'&idThread='.$id_thread.''),
				$this->_lang->def('_BACK') );

		$html .= '<div class="up_box_details">';
		$type = $file[MYFILE_AREA];
		switch($type) {

			case "image" : {
				$html .= '<img src="'.$GLOBALS['where_files_relative'].$user_file->getFileAddress($file[MYFILE_FILE_NAME]).'" '
						.'title="'.strip_tags($file[MYFILE_DESCRIPTION]).'" alt="'.strip_tags($file[MYFILE_TITLE]).'" />';
			};break;
			case "video" : {

				require_once(_base_.'/lib/lib.multimedia.php');
				$html .= getEmbedPlay($GLOBALS['where_files_relative'].$user_file->getFilePath(), $file[MYFILE_FILE_NAME]);
			};break;
			case "audio" : {

				require_once(_base_.'/lib/lib.multimedia.php');
				$html .= getEmbedPlay($GLOBALS['where_files_relative'].$user_file->getFilePath(), $file[MYFILE_FILE_NAME]);
			};break;
			case "other" : {

				require_once(_base_.'/lib/lib.multimedia.php');
				$html .= getEmbedPlay($user_file->getFilePath(), $file[MYFILE_FILE_NAME]);
			};break;
		}
		$html .= '</div>';
		$id_thread = Get::req('idThread', DOTY_INT, 0);
		$html .= getBackUi( $this->_url_man->getUrl('id_user='.$this->_id_user.'&'.$this->_varname_action.'=view_files&type='.$type.'&idThread='.$id_thread.''),
				$this->_lang->def('_BACK') );

		$html .= '</div>';
		return $html;
	}

	/**
	 * display a little version of the user profile
	 */
	function tinyUserInfo($picture) {

		$this->loadUserData($this->getViewer());
		$user_field = $this->loadUserField($this->getViewer());

		$edit_mode = $this->_user_profile->editMode();

		list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($picture);

		// main container ---------------------------------------------------------------------
		$html = '<div class="up_user_info">';

		//avatar -------------------------------------------------------------------
		$html .= '<div class="up_img_container">';

		$html .=( ($this->user_info[ACL_INFO_AVATAR] != "")
				? $this->getPASrc($this->user_info[ACL_INFO_AVATAR], $this->_lang->def('_AVATAR'), $class_picture)
				: '<img class="'.$class_picture.'" src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" />' )
			.'<br />'
			.$this->_lang->def('_AVATAR');
		// end avatar --------------------------------------------------------------------------
		$html .= '</div>';

		// user standard info -----------------------------------------------------------------
		$html .= '<table summary="'.$this->_lang->def('_USERPROFILE_SUMMARY').'">'
				.'<caption class="up_name">'.$this->resolveUsername(false).'</caption>';

		$html .= $this->getUIRowCode($this->_lang->def('_USERNAME'), 	$this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) )
			.$this->getUIRowCode($this->_lang->def('_LASTNAME'), 	$this->user_info[ACL_INFO_LASTNAME] )
			.$this->getUIRowCode($this->_lang->def('_FIRSTNAME'), 	$this->user_info[ACL_INFO_FIRSTNAME] )
			.$this->getUIRowCode($this->_lang->def('_EMAIL'), 		( $this->user_info[ACL_INFO_EMAIL] !== false
				? '<a href="mailto:'.$this->user_info[ACL_INFO_EMAIL].'">'.$this->user_info[ACL_INFO_EMAIL].'</a>'
				: $this->_lang->def('_HIDDEN') ) );
		// user extra field ------------------------------------------------------------------

		if(!empty($user_field))
		while(list(, $value) = each($user_field)) {

			$html .= $this->getUIRowCode($value['name'], $value['value']);
		}

		// end print contacts ----------------------------------------------------------------
		$html .= '</table>';

		// close the floating of the avatar ----------------------------------------
		$html .= '<div class="nofloat"></div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * display a minimal version of the user profile
	 */
	function minimalUserInfo($picture, $link_to = false) {

		$this->loadUserData($this->getViewer());
		$is_online = $this->_up_data_man->isOnline( $this->_user_profile->getIdUser() );
		$online_status = ( $is_online === 'unk'
							? $this->_lang->def('_HIDDEN')
							: ( $is_online ? $this->_lang->def('_USERONLINE') : $this->_lang->def('_USEROFFLINE') )
						);

		list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($picture);

		// main container ---------------------------------------------------------------------
		$html = '<div style="width:95%;">';
		$html .= '<div class="up_user_info">';
		if($this->_user_profile->getIdUser() != $this->getViewer()) $html .= $this->getUserRelatedAction('', false);


		// avatar -------------------------------------------------------------------

		if($this->_user_profile->useAvatar()) {

			$html .= '<div class="up_img_container">';
			$html .=( ($this->user_info[ACL_INFO_AVATAR] != "")
					? $this->getPASrc($this->user_info[ACL_INFO_AVATAR], $this->_lang->def('_AVATAR'), $class_picture)
					: '<img class="'.$class_picture.'" src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" />' )
				.'<br />'
				.$this->_lang->def('_AVATAR');
			// end avatar --------------------------------------------------------------------------
			$html .= '</div>';
		}
		// user standard info -----------------------------------------------------------------
		//require_once(_base_.'/lib/lib.urlmanager.php');
		$link = '<a href="'.$this->_url_man->getUrl('modname=profile&op=profile&id_user='.$this->_id_user.'&back=1&ap=goprofile">');
		$html .= '<table summary="'.$this->_lang->def('_USERPROFILE_SUMMARY').'">'
				.'<caption class="up_name">'.$link.$this->resolveUsername(false).'</a></caption>';

		$html .= $this->getUIRowCode($this->_lang->def('_USERNAME'), 	$this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) )
			.$this->getUIRowCode($this->_lang->def('_LASTNAME'), 	$this->user_info[ACL_INFO_LASTNAME] )
			.$this->getUIRowCode($this->_lang->def('_FIRSTNAME'), 	$this->user_info[ACL_INFO_FIRSTNAME] )
			.$this->getUIRowCode($this->_lang->def('_EMAIL'), 		( $this->user_info[ACL_INFO_EMAIL] !== false
				? '<a href="mailto:'.$this->user_info[ACL_INFO_EMAIL].'">'.$this->user_info[ACL_INFO_EMAIL].'</a>'
				: $this->_lang->def('_HIDDEN') ) );
		if ($this->getViewer() != $this->_id_user)
		{
			if ($this->_up_data_man->isFriend($this->_id_user, getLogUserId()))
				$html .=  $this->getUIRowCode($this->_lang->def('_IS_FRIEND'), $this->_lang->def('_YES'));
			else
				$html .=  $this->getUIRowCode($this->_lang->def('_IS_FRIEND'), $this->_lang->def('_NO'));
		}
		if($this->getViewer() != $this->_id_user) {
			$html .= $this->getUIRowCode($this->_lang->def('_STATUS'), $online_status );
		}
		// end print contacts ----------------------------------------------------------------
		$html .= '</table>';
		if($link_to !== false) $html .= '<a href="'.$link_to.'">'.$this->_lang->def('_GOTO_PROFILE').'</a>';
		// close the floating of the avatar ----------------------------------------
		$html .= '<div class="nofloat"></div>';

		$html .= '</div>';

		$html .= '</div>';
		return $html;
	}

    
    function homePhotoProfile($picture = false, $viewer = false, $intest = false) {

        $this->loadUserData($this->getViewer());
        $acl_man=& Docebo::user()->getAclManager();
        list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($picture);

        $html = '';

        $html .= ''
                .( ($this->user_info[ACL_INFO_AVATAR] != "")
                    ? $this->getPASrcHome($this->user_info[ACL_INFO_AVATAR], $this->_lang->def('_AVATAR'), 'boxed').""
                    : '<img width="30px" width="30px"  class="avatar" src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" /> ' )
            .'';
                return $html;
}


    function homeUserProfile($picture = false, $viewer = false, $intest = false) { //crea la parte del profilo riguardante la foto e i certificati/messaggi

        $this->loadUserData($this->getViewer());
        $acl_man =& Docebo::user()->getAclManager();
        list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($picture);

        //$html = ' <div class="container-fluid"> <div class="row">';
        $html = '<div class="row profile">';

        $ma = new Man_MiddleArea();
        if ($ma->currentCanAccessObj('mo_message')) {
            $perm_message = true;
            require_once($GLOBALS['where_framework'] . '/lib/lib.message.php');
            $msg = new Man_Message();
            $unread_num = $msg->getCountUnreaded(getLogUserId(), array(), '', true);
        }

        if ($ma->currentCanAccessObj('mo_7')) {
            $perm_certificate = true;
        }

        if ($ma->currentCanAccessObj('mo_34')) {
            $perm_competence = true;
        }

        $html .= '<div class="col-xs-5">'
                    . (($this->user_info[ACL_INFO_AVATAR] != "") ? $this->getPASrc($this->user_info[ACL_INFO_AVATAR], $this->_lang->def('_AVATAR'), 'boxed') : '<div class="boxed" style="background-image: url(' . getPathRestylingImage() . ')images/icons/user-panel/icon--up-photo-placeholder.png"></div>')
                . '</div>
                   <div class="col-xs-7">
                      <a href="index.php?r=lms/profile/show" title="'.Lang::t('_PROFILE', 'profile').'">
                          <span class="glyphicon glyphicon-pencil">'.Lang::t('_PROFILE', 'profile').'</span>
                      </a>
                      <a href="index.php?r=lms/profile/show">'
                          . $this->acl_man->relativeId($this->user_info[ACL_INFO_LASTNAME]) . ' ' . $this->acl_man->relativeId($this->user_info[ACL_INFO_FIRSTNAME])
                      . '</a>
                      <a href="mailto:' . $this->user_info[ACL_INFO_EMAIL] . '">' . $this->user_info[ACL_INFO_EMAIL] . '</a>
                   </div>'; // /col-xs-7

        $html .= '</div>'; // /row

        $html .= '<div class="row comunication">'; //pulsanti certificati-messaggi

        if ($perm_certificate) $html .= '<div class="col-xs-4"><a class="btn btn-default" href="index.php?r=lms/mycertificate/show&sop=unregistercourse">' . Lang::t('_MY_CERTIFICATE', 'menu_over') . '</a></div>';
        if ($perm_competence) $html .= '<div class="col-xs-4"><a class="btn btn-default" href="index.php?modname=mycompetences&op=mycompetences&op=unregistercourse">' . Lang::t('_COMPETENCES', 'standard') . '</a></div>';


        if ($unread_num > 0 && $perm_message) {
            $html .= '<div class="col-xs-4"><a class="btn btn-default" href="index.php?r=message/show&sop=unregistercourse">' . Lang::t('_MESSAGES', 'standard') . '<b class="num_notify"><i style="font-size:.78em">' . $unread_num . '</i></b></a></div>';
        }
        if ($unread_num == 0 && $perm_message) {
            $html .= '<div class="col-xs-4"><a class="btn btn-default" href="index.php?r=message/show&sop=unregistercourse">' . Lang::t('_MESSAGES', 'standard') . '</a></div>';
        }

        $html .= '</div>'; //chiusura pulsanti certificati-messaggi

        $pg = new PluginManager('UserProfile');
        foreach ($pg->run('show_home') as $_html) {
            $html .= $_html;
        }

        // box carriera
        require_once($GLOBALS['where_lms'] . '/lib/lib.middlearea.php');
        require_once($GLOBALS['where_lms'] . '/modules/course/course.php');
        $ma = new Man_MiddleArea();
        $access_career = $ma->currentCanAccessObj('career');

        if ($access_career) {

            $url = $this->_url_man;
            $course_stats = userCourseList($url, false, false);        //TODO:  review this call . use course list to compute carreer

            $base_url = 'index.php?' . Get::home_page_query() . '&amp;filter=';
            $end = 0;
            if (isset($course_stats['with_ustatus'][_CUS_END]) && $course_stats['with_ustatus'][_CUS_END] != 0) {
                $end = $course_stats['with_ustatus'][_CUS_END];
            }

            $html .= '<div class="row career">';
            $html .= '<div class="col-xs-12">'
                . '<h2>' . $this->_lang->def('_CAREER') . '</h2>'
                . '<ul class="list-group">'
                . '<li class="list-group-item">'
                . $this->_lang->def('_TOTAL_COURSE') . '<span class="badge">' . ($course_stats['total'] - $end) . '</span>'
                . '</li>'
                . (isset($course_stats['with_ustatus'][_CUS_END]) && $course_stats['with_ustatus'][_CUS_END] != 0
                    ? '<li class="list-group-item">' . $this->_lang->def('_COURSE_END') . '<span class="badge">' . $course_stats['with_ustatus'][_CUS_END] . '</span></li>'
                    : '')
                . (isset($course_stats['expiring']) && $course_stats['expiring'] != 0
                    ? '<li class="list-group-item">' . $this->_lang->def('_COURSE_EXPIRING') . '<span class="badge">' . $course_stats['expiring'] . '</span></li>'
                    : '');

            if (count($course_stats['with_ulevel']) > 1) {

                require_once($GLOBALS['where_lms'] . '/lib/lib.levels.php');
                $lvl = CourseLevel::getLevels();
                foreach ($course_stats['with_ulevel'] as $lvl_num => $quantity) {

                    $html .= '<li class="list-group-item">' . str_replace('[level]', $lvl[$lvl_num], $this->_lang->def('_COURSE_AS')) . '<span class="badge">' . $quantity . '</span></li>';
                } //end foreach

            }

            require_once(Forma::inc(_lms_.'/lib/lib.certificate.php'));
            $cert = new Certificate();

            $filter['id_user'] = $this->_id_user;
            $tot_cert = $cert->countAssignment($filter) + $cert->countMetaAssignment($filter);

            $html .= ''

                . (isset($course_stats['cert_relesable']) /*&& $tot_cert != 0*/
                    ? '<li class="list-group-item">' . $this->_lang->def('_CERT_RELESABLE') . '<span class="badge"><a href="index.php?r=lms/mycertificate/show">' . $tot_cert . '</a></span></li>'
                    : '')

                . ($pendent != 0
                    ? '<li class="list-group-item">' . $this->_lang->def('_FRIEND_PENDENT') . '<span class="badge"><a href="index.php?modname=myfriends&amp;op=myfriends">' . $pendent . '</a></span></li>'
                    : '')

                . '</ul>' // ./content
                . '</div>' // ./col-xs-12
                . '</div>'; // ./row

        }

        return $html;
    }


	/**
	 * display username and avatar
	 */
	function getUserPanelData($picture) {

		$this->loadUserData($this->getViewer());

		list($class_picture, $this->max_dim_avatar) = $this->getPhotoLimit($picture);

		// main container ---------------------------------------------------------------------
		$data = array();
		$data['display_name'] = $this->resolveUsername();

		$data['avatar'] = ( ($this->user_info[ACL_INFO_AVATAR] != "")
				? $this->getPASrc($this->user_info[ACL_INFO_AVATAR], $this->_lang->def('_AVATAR'), $class_picture)
				: '<img class="'.$class_picture.'" src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" />' );

		$data['actions'] = $this->getUserRelatedAction('', false, true);

		$data['is_online'] = $this->_up_data_man->isOnline($this->_id_user);

		return $data;
	}

	function userIdMailProfile($picture = false, $viewer = false, $intest = true) {

		$this->loadUserData($this->getViewer());
		$acl_man 	=& Docebo::user()->getAclManager();

		$html = '<div class="user_presentation">'."\n"

			.'<div class="mini_block">'."\n\t"
				.'<p class="userinfo">'."\n\t\t"
					.'<b>'.$this->_lang->def('_USERNAME').':</b> '.$this->acl_man->relativeId($this->user_info[ACL_INFO_USERID])
				.'</p>'."\n\t"
				.'<p class="userinfo">'."\n\t\t"
					.'<b>'.$this->_lang->def('_EMAIL').':</b> '
					.( $this->user_info[ACL_INFO_EMAIL] !== false
						? '<a href="mailto:'.$this->user_info[ACL_INFO_EMAIL].'">'.$this->user_info[ACL_INFO_EMAIL].'</a>'
						: $this->_lang->def('_HIDDEN')
					)."\n\t"
				.'</p>'."\n\t"
			.'</div>'."\n"

		.'</div>'."\n";

		return $html;
	}

	/**
	 * gui for user info management
	 */
	function getUserInfoModUi() {

		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.preference.php');

		$this->loadUserData($this->_user_profile->getIdUser());

		$preference = new UserPreferences($this->_user_profile->getIdUser());

		$html = '<div class="up_user_info">'
				.'<div class="up_name">'.$this->resolveUsername(false, $this->_user_profile->getIdUser()).'</div>';
		// user standard info -----------------------------------------------------------------
		$html .= Form::openForm('mod_up', $this->_url_man->getUrl($this->_varname_action.'=saveinfo'), false, false, 'multipart/form-data');
		if($this->_user_profile->godMode()) {

			$html .= Form::getTextfield($this->_lang->def('_USERNAME'),
										'up_userid',
										'up_userid',
										'255',
										Get::req('up_userid', DOTY_MIXED, $this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]), true ) );
		} else {

			$html .= Form::getLineBox(	$this->_lang->def('_USERNAME'),
										$this->acl_man->relativeId($this->user_info[ACL_INFO_USERID]) );
		}
		$html .= Form::getTextfield(	$this->_lang->def('_LASTNAME'),
										'up_lastname',
										'up_lastname',
										'255',
										Get::req('up_lastname', DOTY_MIXED, $this->user_info[ACL_INFO_LASTNAME], true ) )
				.Form::getTextfield(	$this->_lang->def('_FIRSTNAME'),
										'up_firstname',
										'up_firstname',
										'255',
										Get::req('up_firstname', DOTY_MIXED, $this->user_info[ACL_INFO_FIRSTNAME], true ) )
				.Form::getTextfield(	$this->_lang->def('_EMAIL'),
										'up_email',
										'up_email',
										'255',
										Get::req('up_email', DOTY_MIXED, $this->user_info[ACL_INFO_EMAIL], true ) );
		// user extra field ------------------------------------------------------------------

		$html .= $this->getPlayField();

		$html .= $preference->getModifyMask('ui.');

		if($this->_user_profile->godMode()) {

			$acl_man =& Docebo::user()->getAclManager();

			$html .= Form::getPassword( Lang::t('_NEW_PASSWORD', 'register'),
									'up_new_pwd',
									'up_new_pwd',
									'255' );

			$html .= Form::getPassword(	Lang::t('_RETYPE_PASSWORD', 'register'),
									'up_repeat_pwd',
									'up_repeat_pwd',
									'255' );

			if(Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN && Get::cur_plat() === 'framework')
				$html .= Form::getCheckBox(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'), 'force_changepwd', 'force_changepwd', 1, $this->user_info[ACL_INFO_FORCE_CHANGE]);

			$lv_lang =& DoceboLanguage::createInstance('admin_directory', 'framework');
			if(Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
				$level_list = array(
					ADMIN_GROUP_GODADMIN => $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_GODADMIN),
					ADMIN_GROUP_ADMIN	=> $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_ADMIN),
					ADMIN_GROUP_USER 	=> $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_USER)
				);
			} else {

				$level_list = array(
					ADMIN_GROUP_USER 	=> $lv_lang->def('_DIRECTORY_'.ADMIN_GROUP_USER)
				);
			}

			$html .= Form::getDropdown(	$this->_lang->def('_LEVEL'),
									'up_level',
									'up_level',
									$level_list,
									$acl_man->getUserLevelId($this->_user_profile->getIdUser()) );
		}

		//signature --------------------------------------------------------------------------

		$html .= Form::getTextarea(	$this->_lang->def('_SIGNATURE'),
									'up_signature',
									'up_signature',
									Get::req('up_signature', DOTY_MIXED, $this->user_info[ACL_INFO_SIGNATURE], true ) );

		if (isset($_GET['modname']) && $_GET['modname'] == 'reservation')
		{
			$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo_profile', 'undo_profile', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

			$html .= Form::closeForm()
				.'</div>';
		}
		else
		{
			$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

			$html .= Form::closeForm()
				.'</div>';
		}

		return $html;
	}

	/**
	 * check the user data filled in the mod user gui
	 * @return mixed boolean true if all is ok , else a text that describe the error
	 */
	function checkUserInfo() {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$extra_field 	= new FieldList();
		if(!$this->_user_profile->godMode()) {

			$re_filled 		= $extra_field->isFilledFieldsForUser($this->_user_profile->getIdUser());
			if($re_filled !== true) {

				return implode('<br/>', $re_filled);
			}
		}
		return true;
	}

	/**
	 * return the standard data filled by the user in the mod gui
	 * @return array the data filed by the user (lastname, firstname, email, password, signature)
	 */
	function getFilledData() {

		$std_filled = array(
			'lastname' 		=> Get::req('up_lastname', DOTY_MIXED, '' ),
			'firstname' 	=> Get::req('up_firstname', DOTY_MIXED, '' ),
			'email' 		=> Get::req('up_email', DOTY_MIXED, '' ),
			'signature' 	=> Get::req('up_signature', DOTY_MIXED, '' ),
			'facebook_id' 		=> Get::pReq('facebook_id', DOTY_MIXED, '' ),
			'twitter_id' 		=> Get::pReq('twitter_id', DOTY_MIXED, '' ),
			'linkedin_id' 		=> Get::pReq('linkedin_id', DOTY_MIXED, '' ),
			'google_id' 		=> Get::pReq('google_id', DOTY_MIXED, '' ),
		);
		if($this->_user_profile->godMode()) {
			$std_filled['userid'] 		= Get::req('up_userid', DOTY_MIXED, '' );
			$std_filled['new_pwd'] 		= Get::req('up_new_pwd', DOTY_MIXED, '' );
			$std_filled['repeat_pwd'] 	= Get::req('up_repeat_pwd', DOTY_MIXED, '' );
			$std_filled['force_change']	= (isset($_POST['force_changepwd']) ? Get::req('force_changepwd', DOTY_INT, 0) : 'no_mod');
			$std_filled['level'] 		= Get::req('up_level', DOTY_MIXED, '' );
		}
		return $std_filled;
	}

	/**
	 * gui for user info password
	 */
	function getUserPwdModUi() {

		require_once(_base_.'/lib/lib.form.php');

		$html = '<div class="up_user_info">'
				.'<div class="up_name">'.$this->resolveUsername(false, getLogUserId()).'</div>';
		// user standard info -----------------------------------------------------------------
		$html .= Form::openForm('mod_pwd', $this->_url_man->getUrl($this->_varname_action.'=savepwd'));

		if(!$this->_user_profile->godMode()) {

			$html .= Form::getPassword(	$this->_lang->def('_OLD_PWD'),
										'up_old_pwd',
										'up_old_pwd',
										'255' );
		}
		$html .= Form::getPassword(	Lang::t('_NEW_PASSWORD', 'register'),
									'up_new_pwd',
									'up_new_pwd',
									'255' )
			.Form::getPassword(	Lang::t('_RETYPE_PASSWORD', 'register'),
									'up_repeat_pwd',
									'up_repeat_pwd',
									'255' );

		$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'));
		if(Get::sett('profile_only_pwd') == 'off') {
			$html .= Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'));
		}
		$html .= Form::closeButtonSpace();

		$html .= Form::closeForm()
				.'</div>';

		return $html;
	}

	/**
	 * check the user password filled in the mod pwd gui
	 * @return mixed boolean true if all is ok , else a text that describe the error
	 */
	function checkUserPwd() {

		$acl_man =& Docebo::user()->getAclManager();

		$this->loadUserData( getLogUserId() );
		if(!$this->_user_profile->godMode()) {

			if(!$acl_man->password_verify_update($_POST['up_old_pwd'], $this->user_info[ACL_INFO_PASS], getLogUserId())) {

				return $this->_lang->def('_OLDPASSWRONG');
			}
			// control password
			if(strlen($_POST['up_new_pwd']) < Get::sett('pass_min_char')) {

				return $this->_lang->def('_PASSWORD_TOO_SHORT');
			}
			if( Get::sett('pass_alfanumeric') == 'on' ) {
				if( !preg_match('/[a-z]/i', $_POST['up_new_pwd']) || !preg_match('/[0-9]/', $_POST['up_new_pwd']) ) {

					return $this->_lang->def('_ERR_PASSWORD_MUSTBE_ALPHA');
				}
			}
			//check password history

			if(Get::sett('user_pwd_history_length', '0') != 0) {

				$new_pwd = $acl_man->encrypt($_POST['up_new_pwd']);
				if($user_info[ACL_INFO_PASS] == $new_pwd) {

					return str_replace('[diff_pwd]', Get::sett('user_pwd_history_length'), Lang::t('_REG_PASS_MUST_DIFF', 'profile'));
				}
				$re_pwd = sql_query("SELECT passw "
				." FROM ".$GLOBALS['prefix_fw']."_password_history"
				." WHERE idst_user = ".getLogUserId().""
				." ORDER BY pwd_date DESC");

				list($pwd_history) = sql_fetch_row($re_pwd);
				for($i = 0;$pwd_history && $i < Get::sett('user_pwd_history_length');$i++) {

					if($pwd_history == $new_pwd) {

						return str_replace('[diff_pwd]', Get::sett('user_pwd_history_length'), Lang::t('_REG_PASS_MUST_DIFF', 'profile'));
					}
					list($pwd_history) = sql_fetch_row($re_pwd);
				}
			}

		}
		if($_POST['up_new_pwd'] != $_POST['up_repeat_pwd']) {

			return Lang::t('_ERR_PASSWORD_NO_MATCH', 'register');
		}
		return true;
	}


	/**
	 * return the password filled by the user in the mod gui
	 * @return string the pwd filed by the user
	 */
	function getFilledPwd() {

		return Get::req('up_new_pwd', DOTY_MIXED, '' );
	}

	function modAvatarGui() {

		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.preference.php');

		$this->loadUserData( getLogUserId() );

		$html = '<div class="up_user_info">'
				.'<div class="up_name">'.$this->resolveUsername(false, getLogUserId()).'</div>';

		// user standard info -----------------------------------------------------------------
		$html .= getInfoUi( str_replace('[max_px]', $this->max_dim_avatar, $this->_lang->def('_AVATAR_PHOTO_INSTRUCTION') ) );
		$html .= Form::openForm('mod_avatar', $this->_url_man->getUrl($this->_varname_action.'=saveavatar'), false, false, 'multipart/form-data');

		$html .= Form::getFilefield(	$this->_lang->def('_AVATAR'),
										'up_avatar',
										'up_avatar' );

		if($this->user_info[ACL_INFO_AVATAR] != '') {

			$html .= Form::getButton('delete_current', 'delete_current', $this->_lang->def('_DEL'), 'up_delete_current');
		}

		$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

		$html .= Form::closeForm()
				.'</div>';

		return $html;
	}

	function getFilledAvatar() {

		if(isset($_POST['delete_current'])) return 'delete_current';
		if(isset($_FILES['up_avatar'])) return $_FILES['up_avatar'];
		return 'nop';
	}

	function getUIPolicyCode($field_name, $field_value, $dropdown) {
		if($field_value === false) {

			return '<tr><th scope="row" colspan="2">'.$field_name.'</th>'
				.'<td>'.$dropdown.'</td></tr>';
		}
		return '<tr><th scope="row">'.$field_name.'</th>'
				.'<td>'.$field_value.'</td>'
				.'<td>'.$dropdown.'</td></tr>';
	}

	function modUserPolicyGui() {

		require_once(_base_.'/lib/lib.form.php');

		$this->loadUserData( $this->_id_user );
		$policy_arr = array(
			PFL_POLICY_FREE => $this->_lang->def('_ALL'),
			//PFL_ _FRIENDS => $this->_lang->def('_PFL_POLICY_FRIENDS'),
			PFL_POLICY_TEACHER => $this->_lang->def('_PFL_POLICY_TEACHER'),
			//PFL_POLICY_TEACHER_AND_FRIENDS => $this->_lang->def('_PFL_POLICY_TEACHER_AND_FRIENDS'),
			PFL_POLICY_NOONE => $this->_lang->def('_PFL_POLICY_NOONE')
		);
		$reduced_policy_arr = array(
			PFL_POLICY_FREE => $this->_lang->def('_ALL'),
			//PFL_POLICY_TEACHER_AND_FRIENDS => $this->_lang->def('_PFL_POLICY_TEACHER_AND_FRIENDS')
		);

		$field_policy 	= $this->_up_data_man->getFieldAccessList($this->_id_user);

		$user_field 	= $this->loadUserField($this->_id_user);
		$user_contacts 	= $this->loadUserContact($this->_id_user);

		$html = '<div class="up_user_info">';
		// user standard info -----------------------------------------------------------------
		$html .= Form::openForm('mod_policy', $this->_url_man->getUrl($this->_varname_action.'=save_policy'));

		$html .= '<table class="mod_policy_table" summary="'.$this->_lang->def('_USERPROFILE_SUMMARY').'">'
				.'<caption class="up_name">'.$this->resolveUsername(false, $this->_id_user).'</caption>';

		$html .= '<thead><tr>'
				.'<th scope="col">'.$this->_lang->def('_FIELD_NAME').'</th>'
				.'<th scope="col">'.$this->_lang->def('_FIELD_VALUE').'</th>'
				.'<th scope="col">'.$this->_lang->def('_POLICY_ASSIGNED').'</th>'
				.'</tr></thead>';

		$html .= '<tbody>'
			/*.$this->getUIPolicyCode(	$this->_lang->def('_LASTNAME'),
										$this->user_info[ACL_INFO_LASTNAME],
										Form::getInputDropdown(	'dropdown_wh',
											'policy_selected_lastname',
											'policy_selected[lastname]',
											$policy_arr,
											(isset($field_policy['lastname']) ? $field_policy['lastname'] : PFL_POLICY_NOONE ) ,
											''
										)
									)

			.$this->getUIPolicyCode(	$this->_lang->def('_FIRSTNAME'),
										$this->user_info[ACL_INFO_FIRSTNAME],
										Form::getInputDropdown(	'dropdown_wh',
											'policy_selected_firstname',
											'policy_selected[firstname]',
											$policy_arr,
											(isset($field_policy['firstname']) ? $field_policy['firstname'] : PFL_POLICY_NOONE ) ,
											''
										)
									)*/;

		// user extra field ------------------------------------------------------------------

		if(!empty($user_field))
		while(list($id, $value) = each($user_field)) {

			$html .= $this->getUIPolicyCode(	$value['name'],
												$value['value'],
												Form::getInputDropdown(	'dropdown_wh',
																		'policy_selected_'.$id,
																		'policy_selected['.$id.']',
																		$policy_arr,
																		(isset($field_policy[$id]) ? $field_policy[$id] : PFL_POLICY_NOONE ) ,
											''
																	)
											);
		}

		$html .= '<tr><th scope="col" colspan="3" id="up_type2">'.$this->_lang->def('_CONTACTS').'</th></tr>';

		// end extra field -------------------------------------------------------------------

		$html .= $this->getUIPolicyCode(	$this->_lang->def('_EMAIL'),
											$this->user_info[ACL_INFO_EMAIL],
											Form::getInputDropdown(	'dropdown_wh',
												'policy_selected_email',
												'policy_selected[email]',
												$policy_arr,
												(isset($field_policy['email']) ? $field_policy['email'] : PFL_POLICY_NOONE ) ,
												''
											)
										);
		if(!empty($user_contacts))
		while(list($id, $value) = each($user_contacts)) {

			$html .= $this->getUIPolicyCode(	$value['name'],
												$value['value'],
												Form::getInputDropdown(	'dropdown_wh',
																		'policy_selected_'.$id,
																		'policy_selected['.$id.']',
																		$policy_arr,
																		(isset($field_policy[$id]) ? $field_policy[$id] : PFL_POLICY_NOONE ),
																		''
																	)
											);
		}

		// end print contacts ----------------------------------------------------------------

		$html .= '<tr><th scope="col" colspan="3" id="up_type2">'.$this->_lang->def('_OTHER_POLICY').'</th></tr>';

		$html .= $this->getUIPolicyCode(	$this->_lang->def('_PRIVATE_MESSAGE_FROM'),
											false,
											Form::getInputDropdown(	'dropdown_wh',
												'policy_selected_message_recipients',
												'policy_selected[message_recipients]',
												$reduced_policy_arr,
												(isset($field_policy['message_recipients']) ? $field_policy['message_recipients'] : /*PFL_POLICY_TEACHER_AND_FRIENDS*/PFL_POLICY_FREE ) ,
												''
											)
										);
		$html .= $this->getUIPolicyCode(	$this->_lang->def('_SHOWME_ONLINE'),
											false,
											Form::getInputDropdown(	'dropdown_wh',
												'policy_selected_online_satus',
												'policy_selected[online_satus]',
												$reduced_policy_arr,
												(isset($field_policy['online_satus']) ? $field_policy['online_satus'] : /*PFL_POLICY_TEACHER_AND_FRIENDS*/PFL_POLICY_FREE ) ,
												''
											)
										);

		$html .= '</tbody></table>';

		$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

		$html .= Form::closeForm()
				.'</div>';

		return $html;
	}

	function getFilledPolicy() {

		$arr_data = $_POST['policy_selected'];
		//id_field => policy_selected
		return $arr_data;

	}

	//---------------------------------------------------------------------------//
	//- community info ----------------------------------------------------------//
	//---------------------------------------------------------------------------//

	/**
	 * user info abput the community (stats, friend list, ...)
	 */
	function getCommunityInfo()  {

		$friend_list 	=& $this->_up_data_man->getUserFriend( $this->_user_profile->getIdUser() );
		$last_view 		= $this->_up_data_man->getUserProfileViewList( $this->_user_profile->getIdUser(), 15 );
		$user_stat 		= $this->_up_data_man->getUserStats( $this->_user_profile->getIdUser() );

		$acl_man =& Docebo::user()->getAclManager();

		$html = '<h2 class="up_type1">'.$this->_lang->def('_COMMUNITY').'</h2>';

		// some usefull statistic ---------------------------------------
		$html .= '<div class="up_left_block">'
			.'<h3>'.str_replace('[firstname]', $this->resolveUsername(true), $this->_lang->def('_ACTIVITY_OF')).'</h3>'
				.'<b>'.$this->_lang->def('_FORUM_MESSAGE')	.': </b>'.$user_stat['forum_post'].'<br />'
				.'<b>'.$this->_lang->def('_LOADED_FILE')	.': </b>'.$user_stat['loaded_file'].'<br />'
				.'<b>'.$this->_lang->def('_REGISTER_DATE')	.': </b>'
					.Format::date($this->user_info[ACL_INFO_REGISTER_DATE], 'date')
			.'</div>';
		if(!empty($friend_list) && is_array($friend_list)) {
			$html .= '<div class="up_right_block">'
					.'<h3>'.str_replace('[firstname]', $this->resolveUsername(true), $this->_lang->def('_FRIENDS_OF')).'</h3>';
			$html .= '<ul>';
			$i = 0;
			while((list($id, $info) = each($friend_list)) && $i < 7) {

				$friend_username = $acl_man->getConvertedUserName($info);

				$html .= '<li>'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=goprofile&id_user='.$id).'"'
							.' title="'.str_replace('[firstname]', $friend_username, $this->_lang->def('_GO_TO_PROFILE')).'">'
						.( $info[ACL_INFO_AVATAR] != ""
							? $this->getPASrc(	$info[ACL_INFO_AVATAR],
												$this->_lang->def('_AVATAR'),
												'image_limit_small')
							: '<img class="image_limit_small" src="'.getPathImage().'standard/user.png" alt="'.$this->_lang->def('_NOAVATAR').'" />' )
						.$friend_username
						.'</a></li>';
				$i++;
			}
			$html .= '</ul>';
			$html .= '</div>';
			reset($friend_list);
		} /*else {

			$html .= '<i>'.$this->_lang->def('_NO_FRIENDS').'</i>';
		}*/


		// some specific action ---------------------------------------
		if(!empty($friend_list) && is_array($friend_list))
		{
			$html .= '<p class="up_middle_other_action">'
					/*.'<a id="up_goblog" href="'.$this->_url_man->getUrl($this->_varname_action.'=goblog').'" '
						.'title="'.$this->_lang->def('_GOTO_BLOG_TITLE').'">'
						.str_replace('[firstname]', $this->resolveUsername(true), $this->_lang->def('_GOTO_BLOG') )
					.'</a> '*/
					.'<a id="up_gofriend" href="'.$this->_url_man->getUrl($this->_varname_action.'=goblog').'" title="'
						.$this->_lang->def('_OTHER_FRIENDS_TITLE').'">'
						.str_replace('[firstname]', $this->resolveUsername(true), $this->_lang->def('_OTHER_FRIENDS'))
						.' ('.( empty($friend_list) || !is_array($friend_list) ? 0 : count($friend_list) ).')'
					.'</a> '
				.'</p>';
		}

		// last profile view -----------------------------------------
		$html .= '<p class="up_last_view">'
				.'<b>'.$this->_lang->def('_LAST_PROFILE_VIEW').':</b> ';

		if(!empty($last_view)){

			$first = true;
			while(list($id, $info) = each($last_view)) {

				if(!$first) $html .= ', ';
				else $first = false;
				$html .= '<a '.( $info['days_ago'] <= 15 ? ' class="last_visit"' : '' )
							.'href="'.$this->_url_man->getUrl($this->_varname_action.'=goprofile&id_user='.$id).'"'
							.' title="'.str_replace('[firstname]', $info['username'], $this->_lang->def('_GO_TO_PROFILE')).' '
							.( $info['days_ago'] <= 15 ? str_replace('[days]', '15', $this->_lang->def('_NEW_VISIT') ) : '' ).'">'
						.$info['username']
						.'</a>';
			}
		} else {

			$html .= '<i>'.$this->_lang->def('_NO_PROFILE_VIEW').'</i>';
		}
		$html .= '</p>';

		return $html;
	}

	/**
	 * the complete list of file of the user (audio, video, images, other)
	 */
	function getFileInfo() {

		return;
		/*
		require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');

		$user_file = new MyFilesPolicy(	$this->_user_profile->getIdUser(),
										$this->getViewer(),
										$this->_up_data_man->isFriend($this->_user_profile->getIdUser(), $this->getViewer()),
										$this->_up_data_man->isTeacher($this->getViewer())
									);

		$re_images 	= $user_file->getFileList('image', false, UP_FILE_LIMIT);
		$num_images = $user_file->getFileCount('image');

		$re_video 	= $user_file->getFileList('video', false, UP_FILE_LIMIT);
		$num_video 	= $user_file->getFileCount('video');

		$re_audio 	= $user_file->getFileList('audio', false, UP_FILE_LIMIT);
		$num_audio 	= $user_file->getFileCount('audio');

		$re_other 	= $user_file->getFileList('other', false, UP_FILE_LIMIT);
		$num_other 	= $user_file->getFileCount('other');

		$html = '<h2 class="up_type1">'.$this->_lang->def('_SHARED_FILE').'</h2>';
		// file of area image -----------------------------------------------------
		$html .= '<div class="up_box_files">'
				.'<h3 id="up_image">'.$this->_lang->def('_IMAGES').'</h3>';

		if($re_images && $num_images > 0) {

			$html .= '<ul>';
			while($image = $user_file->fetch_row($re_images)) {

				$html .= '<li>'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=view_photo').'">'
						.'<img class="image_limit" src="'.$user_file->getFileAddress($image[MYFILE_FILE_NAME]).'" '
							.'title="'.strip_tags($image[MYFILE_DESCRIPTION]).'" alt="'.strip_tags($image[MYFILE_TITLE]).'" />'
						.'</a>'
						.'</li>';
			}
			$html .= '</ul>';
			if($num_images > UP_FILE_LIMIT) {

				$html .= '<a id="up_otherimg" href="'.$this->_url_man->getUrl($this->_varname_action.'=showallfile&area=image').'">'
						.$this->_lang->def('_OTHER_IMAGES').'</a>';
			}
		} else {

			$html .= '<p>'.$this->_lang->def('_NO_IMAGE_FOUND').'</p>';
		}
		$html .= '</div>';
		// end --------------------------------------------------------------------

		// file of area video -----------------------------------------------------
		$html .= '<div class="up_box_files">'
				.'<h3 id="up_video">'.$this->_lang->def('_USER_VIDEO').'</h3>';

		if($re_video && $num_video > 0) {

			$html .= '<ul>';
			while($video = $user_file->fetch_row($re_video)) {

				$html .= '<li>'
						.$video[MYFILE_TITLE]
						.'</li>';
			}
			$html .= '</ul>';
			if($num_images > UP_FILE_LIMIT) {

				$html .= '<a id="up_othervideo" href="'.$this->_url_man->getUrl($this->_varname_action.'=showallfile&area=video').'">'
						.$this->_lang->def('_OTHER_VIDEO').'</a>';
			}
		} else {

			$html .= '<p>'.$this->_lang->def('_NO_VIDEO_FOUND').'</p>';
		}
		$html .= '</div>';
		// end --------------------------------------------------------------------

		// file of area audio -----------------------------------------------------
		$html .= '<div class="up_box_files_left">'
				.'<h3 id="up_audio">'.$this->_lang->def('_USER_AUDIO').'</h3>';
		if($re_audio && $num_audio > 0) {

			$html .= '<ul>';
			while($audio = $user_file->fetch_row($re_audio)) {

				$html .= '<li>'
						.$audio[MYFILE_TITLE]
						.'</li>';
			}
			$html .= '</ul>';
			if($num_audio > UP_FILE_LIMIT) {

				$html .= '<a id="up_otheraudio" href="'.$this->_url_man->getUrl($this->_varname_action.'=showallfile&area=audio').'">'
						.$this->_lang->def('_OTHER_AUDIO').'</a>';
			}
		} else {

			$html .= '<p>'.$this->_lang->def('_NO_AUDIO_FOUND').'</p>';
		}
		$html .= '</div>';
		// end --------------------------------------------------------------------

		// file of area other -----------------------------------------------------
		$html .= '<div class="up_box_files_right">'
				.'<h3 id="up_file">'.$this->_lang->def('_USER_OTHER').'</h3>';
		if($re_other && $num_other > 0) {

			$html .= '<ul>';
			while($other = $user_file->fetch_row($re_other)) {

				$html .= '<li>'
						.$other[MYFILE_TITLE]
						.'</li>';
			}
			$html .= '</ul>';
			if($num_other > UP_FILE_LIMIT) {

				$html .= '<a id="up_otherfile" href="'.$this->_url_man->getUrl($this->_varname_action.'=showallfile&area=other').'">'
						.$this->_lang->def('_OTHER_OTHER').'</a>';
			}
		} else {

			$html .= '<p>'.$this->_lang->def('_NO_OTHER_FOUND').'</p>';
		}
		$html .= '</div>'
				.'<div class="nofloat"></div>';
		// end --------------------------------------------------------------------

		return $html;
		*/
	}

	//--------------------------------------------------------------------------------------//
	//- user statistics --------------------------------------------------------------------//
	//--------------------------------------------------------------------------------------//

	function getUserTeacherProfile($link_to = false) {

		$id_user = $this->_user_profile->getIdUser();
		$html = '';

		$teacher_course 	= $this->_up_data_man->getCourseAsTeacher($id_user);
		$mentor_course 		= $this->_up_data_man->getCourseAsMentor($id_user);
		$tutor_course 		= $this->_up_data_man->getCourseAsTutor($id_user);

		$curriculum 		= $this->_up_data_man->getTeacherCurriculum($id_user);
		$publications 		= $this->_up_data_man->getTeacherPublications($id_user);

		if((count($teacher_course) + count($mentor_course) + count($tutor_course)) == 0 ) return $html;

		$html .= '<h2 class="up_type1">'.$this->_lang->def('_TEACHER_PROFILE').'</h2>';

		// teacher course list
		if(!empty($teacher_course)) {
			$html .= '<div class="up_teacher_course">'
					.'<h3>'.$this->_lang->def('_COURSE_AS_TEACHER').'</h3>'
					.'<ul>';
			while(list($id, $data) = each($teacher_course)) {
				if ($this->userCourseSubscrived($id))
					$html .= '<li><a href="'.Get::rel_path('lms').'/index.php?modname=course&amp;op=aula&amp;idCourse='.$id.'">['.$data['code'].'] '.$data['name'].'</a></li>';
				else
					$html .= '<li>['.$data['code'].'] '.$data['name'].'</li>';
			}
			$html .= '</ul>'
				.'</div>';
		}
		// tutor course list
		if(!empty($tutor_list)) {
			$html .= '<div class="up_tutor_course">'
					.'<h3>'.$this->_lang->def('_COURSE_AS_TUTOR').'</h3>'
					.'<ul>';
			while(list($id, $data) = each($tutor_list)) {
				$html .= '<li>['.$data['code'].'] '.$data['name'].'</li>';
			}
			$html .= '</ul>'
				.'</div>';
		}
		// menor course list
		if(!empty($mentor_list)) {
			$html .= '<div class="up_mentor_course">'
					.'<h3>'.$this->_lang->def('_COURSE_AS_MENTOR').'</h3>'
					.'<ul>';
			while(list($id, $data) = each($mentor_list)) {
				$html .= '<li>['.$data['code'].'] '.$data['name'].'</li>';
			}
			$html .= '</ul>'
				.'</div>';
		}
		$html .= '<div class="nofloat"></div>';
		if($curriculum != '') {

			$html .= '<div class="up_teacher_curriculum">'
					.'<h3>'.$this->_lang->def('_TEACHER_CURRICULUM').'</h3>'
					.'<div class="up_teacher_curriculum_text">'.$curriculum.'</div>'
					.'</div>';
		}
		if($publications != '') {

			$html .= '<div class="up_teacher_publications">'
					.'<h3>'.$this->_lang->def('_TEACHER_PUBLICATIONS').'</h3>'
					.'<div class="up_teacher_publications_text">'.$publications.'</div>'
					.'</div>';
		}
		if($this->_user_profile->editMode()) {
			$html .= '<ul class="up_profile_action">';
			if($curriculum != '')
			$html .= '<li id="tp_del_curriculum">'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=del_teach_curric').'"
							 title="'.$this->_lang->def('_DEL').'">'
							.$this->_lang->def('_DEL').': '.$this->_lang->def('_TEACHER_CURRICULUM').'</a>'
					.'</li>';
			$html .= '<li id="tp_mod_curriculum">'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=mod_teach_curric').'"
							 title="'.$this->_lang->def('_MOD').'">'
							.$this->_lang->def('_MOD').': '.$this->_lang->def('_TEACHER_CURRICULUM').'</a>'
					.'</li>'
					.'<li id="tp_mod_publications">'
						.'<a href="'.$this->_url_man->getUrl($this->_varname_action.'=mod_teach_publ').'"
							 title="'.$this->_lang->def('_MOD').'">'
							.$this->_lang->def('_MOD').': '.$this->_lang->def('_TEACHER_PUBLICATIONS').'</a>'
					.'</li>'
					.'</ul>';
		}
		return $html;
	}

	function userCourseSubscrived($id_course)
	{
		$query = "SELECT COUNT(*)" .
				" FROM ".$GLOBALS['prefix_lms']."_courseuser" .
				" WHERE idCourse = '".$id_course."'" .
				" AND idUser = '".getLogUserId()."'";

		$result = sql_fetch_row(sql_query($query));

		return $result[0];
	}
	function delTeacherCurriculumGui()
	{
		if( isset($_GET['confirm']))
		{
			$query = "UPDATE ".$GLOBALS['prefix_lms']."_teacher_profile" .
					" SET curriculum = ''" .
					" WHERE id_user = '".getLogUserId()."'";

			$result = sql_query($query);

			Util::jump_to($this->_url_man->getUrl());//'index.php?modname=profile&op=profile');
		}
		else
		{
			require_once (_base_.'/lib/lib.template.php');

			$html = '<div class="std_block">';

			$html .= getDeleteUi(
					$this->_lang->def('_AREYOUSURE'),
					$this->_lang->def('_NAME'),
					true,
					$this->_url_man->getUrl('ap=del_teach_curric&confirm=1'),//'index.php?modname=profile&op=profile&ap=del_teach_curric&confirm=1',
					$this->_url_man->getUrl()//'index.php?modname=profile&op=profile'
				);
			$html .= '</div>';

			return $html;
		}
	}
	function modTeacherCurriculumGui() {

		require_once(_base_.'/lib/lib.form.php');

		$html = '<div class="up_user_info">'
				.'<div class="up_name">'.$this->resolveUsername(false).'</div>';

		// user standard info -----------------------------------------------------------------
		$html .= Form::openForm('mod_curriculum', $this->_url_man->getUrl($this->_varname_action.'=save_teach_curric'));

		$html .= Form::getTextarea(	$this->_lang->def('_TEACHER_CURRICULUM'),
										'tp_curriculum',
										'tp_curriculum',
										Get::req('tp_curriculum', DOTY_MIXED, $this->_up_data_man->getTeacherCurriculum( $this->_user_profile->getIdUser() )) );

		$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

		$html .= Form::closeForm()
				.'</div>';

		return $html;
	}

	function getFilledCurriculum() {

		return ( isset($_POST['tp_curriculum']) ? $_POST['tp_curriculum'] : false );
	}

	function modTeacherPublicationsGui() {

		require_once(_base_.'/lib/lib.form.php');

		$html = '<div class="up_user_info">'
				.'<div class="up_name">'.$this->resolveUsername(false, getLogUserId()).'</div>';

		// user standard info -----------------------------------------------------------------
		$html .= Form::openForm('mod_publications', $this->_url_man->getUrl($this->_varname_action.'=save_teach_publ'));

		$html .= Form::getTextarea(	$this->_lang->def('_TEACHER_PUBLICATIONS'),
										'tp_publications',
										'tp_publications',
										Get::req('tp_publications', DOTY_MIXED, $this->_up_data_man->getTeacherPublications( $this->_user_profile->getIdUser() )) );

		$html .= Form::openButtonSpace()
				.Form::getButton('save', 'save', $this->_lang->def('_SAVE'))
				.Form::getButton('undo', 'undo', $this->_lang->def('_UNDO'))
				.Form::closeButtonSpace();

		$html .= Form::closeForm()
				.'</div>';

		return $html;
	}

	function getFilledPublications() {

		return ( isset($_POST['tp_publications']) ? $_POST['tp_publications'] : false );
	}

	// stats -----------------------------------------------------------------------



	function getUserCourseStatUi($stats_data) {

		$lang_test =& DoceboLanguage::createInstance('test', 'lms');

		$tb = new Table(0, $this->_lang->def('_USERCOURSE_CAPTION'), $this->_lang->def('_USERCOURSE_STATS_SUMMARY'));
		$tb->addHead(array(
			$this->_lang->def('_CODE'),
			$this->_lang->def('_COURSE_NAME'),
			$this->_lang->def('_STATUS'),
			$this->_lang->def('_USERCOURSE_STATUS'),
			$this->_lang->def('_USER_STATUS_SUBS'),
			$this->_lang->def('_DATE_FIRST_ACCESS'),
			$this->_lang->def('_COMPLETED'),
			$this->_lang->def('_DATE_LAST_ACCESS'),
			$this->_lang->def('_ACCESS_COUNT'),
			$this->_lang->def('_ACCESS_TIME'),
			$this->_lang->def('_SCORE_INIT'),
			$this->_lang->def('_SCORE_FINAL'),
			str_replace(':', '', $lang_test->def('_TEST_TOTAL_SCORE'))
		));
		while(list($id_c, $info) = each($stats_data)) {

			$tb->addBody(array(
				$info['course_code'],
				$info['course_name'],
				$info['course_status'],
				$info['user_status'],
				Format::date($info['date_inscr']),
				Format::date($info['date_first_access']),
				Format::date($info['date_complete']),
				Format::date($info['access_last']),
				( isset($info['access_count']) ? $info['access_count'] : '' ),
				( isset($info['access_time']) ?
							substr('0'.((int)($info['access_time']/3600)),-2).'h '
							.substr('0'.((int)(($info['access_time']%3600)/60)),-2).'m '
							.substr('0'.((int)($info['access_time']%60)),-2).'s ' : '' ),
				( isset($info['score_init']) ? $info['score_init'] : '' ),
				( isset($info['score_final']) ? $info['score_final'] : '' ),
				$info['point_do']
			));
		}

		return $tb->getTable();
	}


  //competences ----------------------------------------------------------------

	function getUserCompetences(&$comp_data) {

		$tb = new Table(0, Lang::t('_USERCOMPETENCES_CAPTION', 'profile'), Lang::t('_USERCOMPETENCES_SUMMARY', 'profile'));

		$tb->addHead(array(
			Lang::t('_NAME', 'competences'),
			Lang::t('_CATEGORY', 'competences'),
			Lang::t('_TYPOLOGY', 'standard'),
			Lang::t('_TYPE', 'standard'),
			Lang::t('_SCORE', 'competences'),
			Lang::t('_MANDATORY', 'competences'),
			Lang::t('_GAP', 'fncroles')
		), array('','','img-cell','img-cell','img-cell','img-cell','img-cell'));

		$icon_flag_ok = '<span class="ico-sprite subs_actv"><span>'.Lang::t('_MEET', 'competences').'</span></span>';
		$icon_flag_no = '<span class="ico-sprite subs_noac"><span>'.Lang::t('_NOT_SATISFIED', 'competences').'</span></span>';
		$icon_active = '<span class="ico-sprite subs_actv"><span>'.Lang::t('_MANDATORY', 'competences').'</span></span>';
		$icon_warn = '<span class="ico-sprite fd_notice"><span>'.Lang::t('_NOT_SATISFIED', 'competences').'</span></span>';

		$cmodel = new CompetencesAdm();
		$_types = $cmodel->getCompetenceTypes();
		$_typologies = $cmodel->getCompetenceTypologies();
		$_categories = $cmodel->getCategoriesLangs();
		$lang_code = getLanguage();

		if (count($comp_data) > 0) {
			foreach ($comp_data as $id_competence => $value) {

				$_category_name = "";
				if ($value->id_category <= 0) {
					$_category_name .= '<i>('.Lang::t('_NO_CATEGORY', 'competences').')</i>';
				} else {
					if (isset($_categories[$value->id_category][$lang_code]['name']))
						$_category_name .= $_categories[$value->id_category][$lang_code]['name'];
				}
				$_score = '';
				switch ($value->type) {
					case 'flag': {
						$_score = ($value->score>0 ? $icon_flag_ok : $icon_flag_no);
					} break;
					case 'score': {
						$_score = (int)$value->score;
					} break;
				}

				$_gap = "";
				if ($value->required !== false) {
					if ($value->type == 'score') {
						if ($value->gap < 0)
							$_gap .= '<b class="red">'.(int)$value->gap.'</b>&nbsp;'.$icon_warn;
						else
							$_gap .= '<b class="green">'.(int)$value->gap.'</b>&nbsp;'.$icon_flag_ok;
					} else {
						if ($value->score>0)
							$_gap = $icon_flag_ok;
						else
							$_gap = $icon_warn;
					}
				} else {
					$_gap .= '-';
				}

				$tb->addBody(array(
					$value->name,
					$_category_name,
					$_typologies[$value->typology],
					$_types[$value->type],
					$_score,
					$value->required ? $icon_active : '',
					$_gap
				));
			}
		} else {
			$line = array(
				array('colspan' => 5, 'value' => Lang::t('_NO_CONTENT', 'competences'))
			);
			$tb->addBody($line);
		}

		return $tb->getTable();
  }


	function getUserFunctionalRoles(&$fncroles_data) {

		$tb = new Table(0, Lang::t('_FUNCTIONAL_ROLE', 'profile'), Lang::t('_FUNCTIONAL_ROLE', 'profile'));

		$tb->addHead(array(
			Lang::t('_NAME', 'fncroles'),
			Lang::t('_SCORE', 'fncroles'),
			'<img src="'.Get::tmpl_path('base').'images/blank.png" />'
		), array('', 'img-cell', 'img-cell'));

		$icon_flag_ok = '<span class="ico-sprite subs_actv"><span>'.Lang::t('_MEET', 'fncroles').'</span></span>';
		$icon_flag_no = '<span class="ico-sprite fd_notice"><span>'.Lang::t('_NOT_SATISFIED', 'fncroles').'</span></span>';

		if (count($fncroles_data) > 0) {
			foreach ($fncroles_data as $id_fncrole => $value) {

				$obt = (int)$value->competences_obtained;
				$req = (int)$value->competences_required;

				$line = array();
				$line[] = $value->name;
				$line[] = '<b'.($obt < $req ? ' class="red"' : '').'>'.$obt.' / '.$req.'</b>';
				$line[] = ($obt < $req ? $icon_flag_no : $icon_flag_ok);

				$tb->addBody($line);
			}
		} else {
			$line = array(
				array('colspan' => 3, 'value' => Lang::t('_NO_CONTENT', 'fncroles'))
			);
			$tb->addBody($line);
		}

		return $tb->getTable();
  }


	function getUserGroupsList(&$groups_data) {
		$output = "";
		$array_content_1 = array();
		$array_content_2 = array();

		if (is_array($groups_data)) {
			if (isset($groups_data['folders']) && !empty($groups_data['folders'])) {
				foreach($groups_data['folders'] as $idst => $label)
				{
				$array_content_1[] = array('folders' => $label);
				}

				$array_style = array(
				'folders' => ''
				);

				$array_header = array(
				'folders' => Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory')
				);

				asort($array_content_1);


				$output.=Util::widget('table', array(
				'id'		=> 'folders_table',
				'styles'	=> $array_style,
				'header'	=> $array_header,
				'data'		=> $array_content_1,
				//'summary'	=> Lang::t('_LEVELS', 'subscribe'),
				'caption'	=> false//Lang::t('_LEVELS', 'subscribe')
				), true);
			}


			if (isset($groups_data['groups']) && !empty($groups_data['groups'])) {
				if ($output != "") $output .= '<br /><br />';
				foreach($groups_data['groups'] as $idst => $label)
				{
				$array_content_2[] = array('groups' => $label);
				}

				$array_style = array(
				'groups' => ''
				);

				$array_header = array(
				'groups' => Lang::t('_GROUPS', 'standard')
				);

				asort($array_content_2);

				$output.=Util::widget('table', array(
				'id'		=> 'groups_table',
				'styles'	=> $array_style,
				'header'	=> $array_header,
				'data'		=> $array_content_2,
				//'summary'	=> Lang::t('_LEVELS', 'subscribe'),
				'caption'	=> false//Lang::t('_LEVELS', 'subscribe')
				), true);
			}
		}

		if ($output == "") {
			$output .= '<p>'.Lang::t('_NO_CONTENT', 'standard').'</p>';
		}

		return $output;
	}


}

// ========================================================================================================== //
// ========================================================================================================== //
// ========================================================================================================== //

/**
 * @category library
 * @package user_management
 * @subpackage profile
 * @author Fabio Pirovano
 * @since 3.1.0
 *
 * This class will manage the display of the data readed by the
 */
class UserProfileData {

	var $_db_conn = NULL;

	var $acl;

	var $acl_man;

	/**
	 * @var UserProfile a refer to the main class that must have istanciate this one
	 */
	var $_user_profile;

	/* Now a series of cache variables, is usefull if you want to display a lot of profile
	 * , you can load the data before in this class and than call normaly the standard profile class
	 **/

	/**
	 * @var array contains the users main data such as firstname, lastname and so from the table core_user
	 **/
	var $_user_data;

	/**
	 * @var array contains the access rule setted by the user for its data,
	 * 			these data is stored in the preferences of the user
	 **/
	var $_field_access_list;

	var $_user_quota;

	/**
	 * contains the complete list of friends of a user
	 */
	var $_user_friend_list;

	/**
	 * @var array contains the user pubblications, curiculum, course data and if the user is a theacer or not
	 **/
	var $_teacher_data;

	var $_user_file_list;

	/**
	 * @return string the table containing the list of view of the user
	 * */
	function _getTableProfileView() { return $GLOBALS['prefix_fw'].'_user_profileview'; }

	/**
	 * class constructor
	 */
	function UserProfileData($db_conn = NULL) {

		require_once(_base_.'/lib/lib.user.php');
		require_once(_base_.'/lib/lib.preference.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		$this->_db_conn = $db_conn;

		$this->acl 		= Docebo::user()->getAcl();
		$this->acl_man 	= Docebo::user()->getAclManager();
	}

	function _query($query) {

		if($this->_db_conn === NULL)
			$re =  sql_query($query);
		else
			$re =  sql_query($query, $this->_db_conn);

		return $re;
	}

	/**
	 * set the refrence to the user_profile class, is needed
	 */
	function setUserProfile(&$user_profile) { $this->_user_profile =& $user_profile; }

	/**
	 * retrive the id of the usre that has request the profile, is needed for access control
	 */
	function getViewer() { return $this->_user_profile->getViewer(); }

	/**
	 * this function return the path to the folder in which the avatat are stored
	 *
	 * @return string the path to the avatar
	 */
	function getPAPath() { return '/appCore/'.Get::sett('pathphoto'); }

	function setCacheForUsers($arr_user) {

		if(!is_array($arr_user)) return false;

		// caching users data
		$this->_user_data = $this->acl_man->getUsers($arr_user);

		// caching user access list
		$this->_field_access_list = $this->acl_man->getSettingValueOfUsers('user_rules.field_policy', $arr_user);

		$this->_user_quota['used'] = $this->acl_man->getSettingValueOfUsers('user_rules.user_quota_used', $arr_user);
		$this->_user_quota['limit'] = $this->acl_man->getSettingValueOfUsers('user_rules.user_quota', $arr_user);

		// caching user friends
		require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
		$my_friend 	= new MyFriends(0);
		$this->_user_friend_list = $my_friend->getAllFriendsSubdividedForUsers($arr_user);

		// caching teachers data
		$t_query = "
		SELECT id_user, curriculum, publications
		FROM ".$GLOBALS['prefix_lms']."_teacher_profile
		WHERE id_user IN ( '".implode(', ', $arr_user)."' )";
		$re_teacher_data = $this->_query($t_query);
		while(list($user, $curr, $pubb) = sql_fetch_row($re_teacher_data)) {

			$this->_teacher_data[$user]['curriculum'] = $user;
			$this->_teacher_data[$user]['pubblications'] = $pubb;
		}

		$teachers = Man_CourseUser::getUserWithLevelFilter(array('4', '5', '6', '7'), $arr_user);
		while(list(, $id) = each($teachers)) {

			$this->_teacher_data[$id]['is_teacher'] = true;
		}
	}

	/**
	 * check if the user can view
	 */
	function getVisibilityResponse($rule, $id_user, $viewer, $is_teacher, $is_friend) {

		if($id_user == $viewer) return true;
		switch($rule) {
			case PFL_POLICY_FREE : 							return true; break;
			case PFL_POLICY_TEACHER : if($is_teacher) 		return true; break;
			//case PFL_POLICY_FRIENDS : if($is_friend) 		return true; break;
			//case PFL_POLICY_TEACHER_AND_FRIENDS : if($is_teacher || $is_friend) return true; break;
			case PFL_POLICY_NOONE : if($id_user == $viewer) return true; break;
		}
		return false;
	}

	/**
	 * return the user data without the control of the access list
	 */
	function getUserDataNoRestriction($id_user) {

		if(isset($this->_user_data[$id_user])) $user_data = $this->_user_data[$id_user];
		else {
			$user_data = $this->acl_man->getUser($id_user, false);
			$this->_user_data[$id_user] = $user_data;
		}
		return $user_data;
	}

	/**
	 * retrive the user data also check if a field is visibile to the user
	 */
	function getUserData($id_user, $refresh = false) {

		$viewer = $this->getViewer();

		// retrive the user data from cache if there is
		if(isset($this->_user_data[$id_user]) && !$refresh) $user_data = $this->_user_data[$id_user];
		else {
			$user_data = $this->acl_man->getUser($id_user, false);
			$this->_user_data[$id_user] = $user_data;
		}

		// load other data
		$fal 			= $this->getFieldAccessList($id_user);
		$is_teacher 	= $this->isTeacher($viewer);
		$is_friend 		= $this->isFriend($id_user, $viewer);

		// check visibile data to the viewer
		$visible = false;
		if(isset($fal['firstname'])) {

			$visible = $this->getVisibilityResponse($fal['firstname'], $id_user, $viewer, $is_teacher, $is_friend);
			if(!$visible) $user_data[ACL_INFO_FIRSTNAME] = false;
		}
		if(isset($fal['lastname'])) {

			$visible = $this->getVisibilityResponse($fal['lastname'], $id_user, $viewer, $is_teacher, $is_friend);
			if(!$visible) $user_data[ACL_INFO_LASTNAME] = false;
		}
		if(isset($fal['email'])) {

			$visible = $this->getVisibilityResponse($fal['email'], $id_user, $viewer, $is_teacher, $is_friend);
			if(!$visible) $user_data[ACL_INFO_EMAIL] = false;
		} elseif($id_user != $viewer) {

			$user_data[ACL_INFO_EMAIL] = false;
		}
		return $user_data;
	}

	/**
	 * retrive the disk quota assigned to the user
	 */
	function getQuotaLimit($id_user) {

		if(isset($this->_user_quota['limit'])) $user_quota = $this->_user_quota['limit'];
		else {
			$preference = new UserPreferences($id_user);
			$user_quota = $preference->getPreference('user_rules.user_quota');
		}
		if($user_quota == USER_QUOTA_INHERIT) $user_quota = Get::sett('user_quota');
		return $user_quota;
	}

	/**
	 * retrive the disk quota used by this user
	 */
	function getUsedQuota($id_user) {


		if(isset($this->_user_quota['used'])) $user_quota = $this->_user_quota['used'];
		else {
			$preference = new UserPreferences($id_user);
			$user_quota = $preference->getPreference('user_rules.user_quota_used');
		}
		return $user_quota;
	}

	/**
	 * save the new value for the access list for the passed user
	 */
	function setFieldAccessList($id_user, $data) {

		$preference = new UserPreferences($id_user);

		$ser_value =  addslashes(serialize($data));
		$result = $preference->setPreference('user_rules.field_policy', $ser_value);
		$result = $preference->setPreference('user_rules.online_status', $data['online_status']);
		if($result) $this->_field_access_list[$id_user] = $data;

		return $result;
	}

	/**
	 * retrive the access list setted by the user
	 */
	function getFieldAccessList($id_user) {

		if(isset($this->_field_access_list[$id_user])) return $this->_field_access_list[$id_user];
		else {

			// load from database the saved access list for the user
			$preference = new UserPreferences($id_user);
			$this->_field_access_list[$id_user] = unserialize(stripslashes($preference->getPreference('user_rules.field_policy')));
		}
		// add default value to list if needed
		if(!isset($this->_field_access_list[$id_user]['email'])) {
			$this->_field_access_list[$id_user]['email'] = PFL_POLICY_NOONE;
		}
		if(!isset($this->_field_access_list[$id_user]['online_satus'])) {
			$this->_field_access_list[$id_user]['online_satus'] = PFL_POLICY_TEACHER_AND_FRIENDS;
		}
		if(!isset($this->_field_access_list[$id_user]['message_recipients'])) {
			$this->_field_access_list[$id_user]['message_recipients'] = PFL_POLICY_TEACHER_AND_FRIENDS;
		}
		return $this->_field_access_list[$id_user];
	}

	/**
	 * return the list of user friends
	 */
	function &getUserFriend($id_user) {

		$my_fr 		= new MyFriends($id_user);
		$users_info =& $my_fr->getFriendsInfo(false, UP_FRIEND_LIMIT);

		return $users_info;
	}

	/**
	 * check if check_user is a friend of id_user (if also_waiting) a user that is waiting for approval is considered as a friend
	 */
	function isFriend($id_user, $check_user, $also_waiting = false) {

		if($id_user == $check_user) return true;
		if(!isset($this->_user_friend_list[$id_user])) {

			require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
			$my_friend = new MyFriends($id_user);
			$this->_user_friend_list[$id_user] = $my_friend->getAllFriendsSubdivided();
		}
		if(isset($this->_user_friend_list[$id_user]['effective'][$check_user])) {
			return true;
		}
		if(isset($this->_user_friend_list[$id_user]['waiting'][$check_user]) && $also_waiting === true) {
			return true;
		}
		return false;
	}

	/**
	 * check if a check_user is firend of id_user with a direct query, ignore cahced data
	 */
	function isFriendNoCache($id_user, $check_user, $also_waiting = false) {

		if($id_user == $check_user) return true;
		require_once($GLOBALS['where_framework'].'/lib/lib.myfriends.php');
		$mf = new MyFriends($id_user);
		return $mf->isFriend($check_user, $also_waiting);
	}

	/**
	 * check if a user is a tutor, mentor, teacher or admin
	 */
	function isTeacher($id_user) {

		if(isset($this->_teacher_data[$id_user]['is_teacher'])) return $this->_teacher_data[$id_user]['is_teacher'];

		$re = Man_CourseUser::getUserWithLevelFilter(array('4', '5', '6', '7'), array($id_user));
		$this->_teacher_data[$id_user]['is_teacher'] = !empty($re);
		return $this->_teacher_data[$id_user]['is_teacher'];
	}

	/**
	 * check if the sender can send a message to the user
	 */
	function canSendMessage($id_user, $sender) {

		if($id_user == $sender) return false;
		$is_friend 	= $this->isFriend($sender, $id_user, false);
		$is_teacher = $this->isTeacher($sender);
		$fal 		= $this->getFieldAccessList($id_user);

		$can_send = false;
		switch($fal['message_recipients']) {
			case PFL_POLICY_FREE : $can_send = true; break;
			case PFL_POLICY_TEACHER_AND_FRIENDS : if($is_teacher || $is_friend) $can_send = true; break;
		}
		return $can_send;
	}

	/**
	 * check if a user is online or not
	 */
	function isOnline($id_user) {

		$u_info = $this->getUserDataNoRestriction($id_user);
		$fal 	= $this->getFieldAccessList($id_user);

		$viewer = $this->getViewer();

		$is_teacher = $this->isTeacher($viewer);
		$is_friend 	= $this->isFriend($id_user, $viewer);

		switch ($fal['online_satus'])
		{
			case PFL_POLICY_FREE:
				$online = ( strcmp($u_info[ACL_INFO_LASTENTER], date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER)) >= 0);
				break;
			case PFL_POLICY_TEACHER:
				if ($is_teacher)
					$online = ( strcmp($u_info[ACL_INFO_LASTENTER], date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER)) >= 0);
				else
					$online = 'unk';
				break;
			case PFL_POLICY_FRIENDS:
				if($is_friend)
					$online = ( strcmp($u_info[ACL_INFO_LASTENTER], date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER)) >= 0);
				else
					$online = 'unk';
				break;
			case PFL_POLICY_NOONE:
				$online = 'unk';
				break;
			case PFL_POLICY_TEACHER_AND_FRIENDS:
				if($is_friend || $is_teacher)
					$online = ( strcmp($u_info[ACL_INFO_LASTENTER], date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER)) >= 0);
				else
					$online = 'unk';
				break;
		}

		return $online;
	}

	/**
	 * retrive the user statistic
	 */
	function getUserStats($id_user) {

		require_once($GLOBALS['where_lms'].'/lib/lib.forum.php');

		$u_info = $this->getUserDataNoRestriction($id_user);

		/*
		require_once($GLOBALS['where_cms'].'/lib/lib.forum.php');
		$forum_cms = new Man_Forum_Cms();
		$forum_cms->getUserForumPostCms($id_user) +
		*/
		$forum_lms = new Man_Forum();
		$forum_post = $forum_lms->getUserForumPostLms($id_user);

		$my_file = new MyFile($id_user);
		$stats = array(
			'forum_post' => $forum_post,
			'blog_post' => 'unk',
			'loaded_file' => 0
		);
		return $stats;
	}

	/**
	 * retrive the value of the extra field for the user and check the user policy
	 */
	function getUserField($id_user) {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$viewer = $this->getViewer();

		$fal 		= $this->getFieldAccessList($id_user);
		$is_teacher = $this->isTeacher($viewer);
		$is_friend 	= $this->isFriend($id_user, $viewer);

		$field_man 		= new FieldList();
		$field_founded 	= $field_man->getFieldsAndValueFromUser($id_user, false, false, array('standard'));

		$field = array();
		foreach($field_founded as $field_id => $value) {

			if(isset($fal[$field_id])) {

				if($this->getVisibilityResponse($fal[$field_id], $id_user, $viewer, $is_teacher, $is_friend)) {

					$field[$field_id] = array(	'name' 	=> $value[0],
												'value' => $value[1] );
				}
			} else {
				if($id_user === Docebo::user()->getIdSt())
					$field[$field_id] = array(	'name' 	=> $value[0],
											'value' => $value[1] );
			}
		}
		return $field;
	}

	/**
	 * retrive the value of the extra field for the user without check the user policy
	 */
	function getUserFieldNoRestriction($id_user) {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$field_man 		= new FieldList();
		$field_founded 	= $field_man->getFieldsAndValueFromUser($id_user, false, true, array('standard'));

		$field = array();
		foreach($field_founded as $field_id => $value) {

			$field[$field_id] = array(	'name' 	=> $value[0],
										'value' => $value[1] );

		}
		return $field;
	}

	/**
	 * retrive the value of the extra field for the user that is classiied as contact
	 */
	function getUserContact($id_user, $god_admin = false) {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$viewer = $this->getViewer();

		$fal 		= $this->getFieldAccessList($id_user);
		$is_teacher = $this->isTeacher($viewer);
		$is_friend 	= $this->isFriend($id_user, $viewer);

		$field_man 		= new FieldList();
		$field_founded 	= $field_man->getFieldsAndValueFromUser($id_user, false, false, array('contact'));

		$field = array();
		foreach($field_founded as $id => $arr_value) {

			if(isset($fal[$id])) {

				if($this->getVisibilityResponse($fal[$id], $id_user, $viewer, $is_teacher, $is_friend)) {

					$ob =& $field_man->getFieldInstance($id, $arr_value[5], $arr_value[6]);
                    $field[$id] = array(    'name'  => $arr_value[0],
											'value' => $arr_value[1],
											'href' 	=> $ob->getIMBrowserHref($id_user, $arr_value[1]),
											'image' => $ob->getIMBrowserImageSrc($id_user, $arr_value[1]),
											'head'	=> $ob->getIMBrowserHead($id_user, $arr_value[1]),
											'field_type' => $arr_value[4]
										);
				}
			} else {

				$ob =& $field_man->getFieldInstance($id, $arr_value[5], $arr_value[6]);
				$field[$id] = array(	'name' 	=> $arr_value[0],
										'value' => $arr_value[1],
										'href' 	=> $ob->getIMBrowserHref($id_user, $arr_value[1]),
										'image' => $ob->getIMBrowserImageSrc($id_user, $arr_value[1]),
										'head'	=> $ob->getIMBrowserHead($id_user, $arr_value[1]),
										'field_type' => $arr_value[4]
									);
			}

		}
		return $field;
	}

	/**
	 * retrive the value of the extra field for the user that is classiied as contact without checking the access list
	 */
	function getUserContactNoRestriction($id_user) {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$field_man 		= new FieldList();
		$field_founded 	= $field_man->getFieldsAndValueFromUser($id_user, false, false, array('contact'));

		$field = array();
		foreach($field_founded as $id => $arr_value) {

			$ob =& $field_man->getFieldInstance($id, $arr_value[5], $arr_value[6]);
			$field[$id] = array(	'name' 	=> $arr_value[0],
									'value' => $arr_value[1],
									'href' 	=> $ob->getIMBrowserHref($id_user, $arr_value[1]),
									'image' => $ob->getIMBrowserImageSrc($id_user, $arr_value[1]),
									'head'	=> $ob->getIMBrowserHead($id_user, $arr_value[1]),
									'field_type' => $arr_value[4]
								);
		}
		return $field;
	}

	/**
	 * get the standard form for user field modification
	 */
	function getPlayField($id_user, $god_mode = false) {

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		$field_man 		= new FieldList();
		if($god_mode) {

			return $field_man->playFieldsForUser( $id_user, false, false, true );
		}
		return $field_man->playFieldsForUser( $id_user, false, false, true, array('readonly') );
	}

	/**
	 * save the data sended by a user profile modification
	 */
	function saveUserData($id_user, $data, $also_preference, $also_extra_field) {

		if(!$this->acl_man->updateUser( 	$id_user,
											( isset($data['userid']) ? $data['userid'] : false ),
											$data['firstname'],
											$data['lastname'],
											( isset($data['new_pwd']) && ($data['new_pwd'] != '') && ($data['new_pwd'] == $data['repeat_pwd'])
												? $data['new_pwd']
												: false ),
											$data['email'],
											FALSE,
											$data['signature'], FALSE, FALSE,
											(isset($data['force_change']) ? $data['force_change'] : ''),
											$data['facebook_id'], $data['twitter_id'], $data['linkedin_id'],
											$data['google_id'])) {
			return false;
		}
		if(isset($data['level'])) {

			$acl_man =& Docebo::user()->getAclManager();
			$current_level = $acl_man->getUserLevelId($id_user);
			if($data['level'] != $current_level) {

				$arr_levels = $acl_man->getAdminLevels();

				$acl_man->addToGroup($arr_levels[$data['level']],  $id_user );
				$acl_man->removeFromGroup( $arr_levels[$current_level], $id_user );
			}
		}
		if($also_extra_field) {

			require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
			$extra_field = new FieldList();
			if(!$extra_field->storeFieldsForUser( $id_user )) return false;
		}
		if($also_preference) {

			require_once(_base_.'/lib/lib.preference.php');
			$preference = new UserPreferences($id_user);
			if(!$preference->savePreferences( $_POST, 'ui.' )) return false;
		}
		return true;
	}

	/**
	 * save the user new password
	 */
	function saveUserPwd($id_user, $new_pwd) {

		if(!$this->acl_man->updateUser( 	$id_user,
											FALSE,
											false,
											false,
											$new_pwd,
											false,
											FALSE,
											false )) {
			return false;
		}
		return true;
	}

	/**
	 * save a new avatar for the user
	 */
	function saveAvatarData($id_user, $file_descriptor, $max_width, $max_height) {

		if(!$this->deleteAvatarData($id_user)) return false;

		if(!isset($file_descriptor['error'])) return false;
		if($file_descriptor['error'] != UPLOAD_ERR_OK) return false;
		if($file_descriptor['name'] == '') return false;

		require_once(_base_.'/lib/lib.upload.php');
		require_once(_base_.'/lib/lib.multimedia.php');

		$savefile = $id_user.'a_'.mt_rand(0,100).'_'.time(); //.'_'.$file_descriptor['name'];     removed for vulnerability
		if(file_exists($GLOBALS['where_files_relative'].$this->getPAPath().$savefile)) return false;

		sl_open_fileoperations();
		if(createImageFromTmp(	$file_descriptor['tmp_name'],
								$this->getPAPath().$savefile,
								$file_descriptor['name'],
								$max_width,
								$max_height,
								true ) != 0) {

			sl_close_fileoperations();
			return false;
		}
		sl_close_fileoperations();
		if(!$this->acl_man->updateUser( $id_user,
										false,
										false,
										false,
										false,
										false,
										$savefile,
										false )) {

			sl_unlink(substr($this->getPAPath(), 1).$savefile);
			return false;
		}
		return true;
	}

	/**
	 * delete the current user avatar
	 */
	function deleteAvatarData($id_user) {

		require_once(_base_.'/lib/lib.upload.php');
		$user_info = $this->getUserDataNoRestriction($id_user);

		if($user_info[ACL_INFO_AVATAR] != '') {

			if(!sl_unlink(substr($this->getPAPath(), 1).$user_info[ACL_INFO_AVATAR])) return false;
			if(!$this->acl_man->updateUser( $id_user,
											false,
											false,
											false,
											false,
											false,
											'',
											false )) {
				return false;
			}
		}

		return true;
	}

	/**
	 * retrive the list of the user thata have seen this profile
	 */
	function getUserProfileViewList($id_user, $limit) {

		$user_list = array();
		$id_list = array();

		$query = "
		SELECT id_viewer, date_view
		FROM ".$this->_getTableProfileView()."
		WHERE id_owner = '".$id_user."'
		ORDER BY date_view DESC
		LIMIT 0,".$limit;
		if(!$re_query = sql_query($query)) return $user_list;

		while($row = sql_fetch_row($re_query)) {

			$id_list[$row[0]] = $row[0];
			$user_list[$row[0]]['id'] = $row[0];
			$user_list[$row[0]]['username'] = $row[0];
			$user_list[$row[0]]['days_ago'] = (int)((time() - fromDatetimeToTimestamp($row[1])) / (60*60*24));
		}
		$user_info =& $this->acl_man->getUsers($id_list);
		foreach($id_list as $id) {

			$user_list[$id]['username'] = ( $user_info[$id][ACL_INFO_LASTNAME].$user_info[$id][ACL_INFO_FIRSTNAME]
				? $user_info[$id][ACL_INFO_LASTNAME].' '.$user_info[$id][ACL_INFO_FIRSTNAME]
				: $this->acl_man->relativeId($user_info[$id][ACL_INFO_USERID]) );
		}

		return $user_list;
	}

	/**
	 * save the user view of this profile
	 */
	function addView($id_user, $id_viewer) {

		if($id_user == 0 || $id_viewer == 0) return ;

		// delete old profile view
		$query = "
		DELETE FROM ".$this->_getTableProfileView()."
		WHERE date_view < '".date("Y-m-d H:i:s", time() - 3600*24*31*6)."' OR
			( id_owner = ".(int)$id_user." AND id_viewer = ".(int)$id_viewer." )";
		sql_query($query);

		// save the new profile view
		$query_ins = "
		INSERT INTO ".$this->_getTableProfileView()."
		( id_owner, id_viewer, date_view ) VALUES (
			".(int)$id_user.",
			".(int)$id_viewer.",
			'".date("Y-m-d H:i:s")."'
		)";
		sql_query($query_ins);
	}

	/**
	 * retrive info about the file uploaded by the user
	 */
	function getFileInfo($id_user, $viewer) {

		$is_friend 	= $this->isFriend($viewer, $id_user, true);
		$is_teacher = $this->isTeacher($viewer);

		$files_info = array();
		require_once($GLOBALS['where_framework'].'/lib/lib.myfiles.php');
		$user_file 	= new MyFilesPolicy(	$this->_user_profile->getIdUser(),
											$this->getViewer(),
											$is_friend,
											$is_teacher );

		$files_info['image'] 	= $user_file->getFileCount('image');
		$files_info['video'] 	= $user_file->getFileCount('video');
		$files_info['audio'] 	= $user_file->getFileCount('audio');
		$files_info['other'] 	= $user_file->getFileCount('other');
		return $files_info;
	}

	//-----------------------------------------------------------------------------------//
	//- specific for lms stats ----------------------------------------------------------//
	//-----------------------------------------------------------------------------------//

	/**
	 * @return array the list of the course with the carachteristic of it array( id_course => array(
	 * 					idCourse, code, name, description
	 */
	function getCourseAsTeacher($id_user) {

		return $this->getCourseAtLevel($id_user, '6');
	}

	function getCourseAsMentor($id_user) {

		return $this->getCourseAtLevel($id_user, '5');
	}

	function getCourseAsTutor($id_user) {

		return $this->getCourseAtLevel($id_user, '4');
	}

	function getCourseAtLevel($id_user, $lv) {

		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		$man_courseuser = new Man_CourseUser();
		$course = $man_courseuser->getUserCoursesLevelFilter($id_user, $lv, true);

		return $course;
	}

	function loadTeacherData($id_user) {

		$query = "
		SELECT curriculum, publications
		FROM ".$GLOBALS['prefix_lms']."_teacher_profile
		WHERE id_user = '".$id_user."'";
		$re = $this->_query($query);
		if(!sql_num_rows($re)) {
			$this->_teacher_data[$id_user]['curriculum'] = false;
			$this->_teacher_data[$id_user]['pubblications'] = false;
			return;
		}
		list($this->_teacher_data[$id_user]['curriculum'], $this->_teacher_data[$id_user]['pubblications']) = sql_fetch_row($re);
	}

	/**
	 * return the curriculum of the user
	 */
	function getTeacherCurriculum($id_user) {

		if(isset($this->_teacher_data[$id_user]['curriculum'])) return $this->_teacher_data[$id_user]['curriculum'];
		$this->loadTeacherData($id_user);
		return $this->_teacher_data[$id_user]['curriculum'];
	}

	/**
	 * return the pubblications of the user
	 */
	function getTeacherPublications($id_user) {

		if(isset($this->_teacher_data[$id_user]['pubblications'])) return $this->_teacher_data[$id_user]['pubblications'];
		$this->loadTeacherData($id_user);
		return $this->_teacher_data[$id_user]['pubblications'];
	}

	/**
	 * save the user curriculum and publications in database (it's not need to specify both)
	 */
	function saveTeacherCurriculumAndPublication($id_user, $curriculum = false, $publications = false) {

		if($curriculum === false && $publications === false) return true;

		$query = "
		SELECT COUNT(*)
		FROM ".$GLOBALS['prefix_lms']."_teacher_profile
		WHERE id_user = '".$id_user."'";
		$re = $this->_query($query);
		list($num_of) = sql_fetch_row($re);
		if($num_of) {

			$query = "
			UPDATE ".$GLOBALS['prefix_lms']."_teacher_profile
			SET ".( $curriculum !== false
					? " curriculum = '".$curriculum."' ".($publications !== false ? ", " : "" )
					: "" )
				.( $publications !== false
					? " publications = '".$publications."' "
					: "" )."
			WHERE id_user = '".$id_user."'";
			$re = $this->_query($query);
		} else {

			$query = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_teacher_profile
			( id_user ".( $curriculum !== false ? ", curriculum " : "" ).( $publications !== false ? ", publications " : "" )." ) VALUES (
				'".$id_user."'
				".( $curriculum !== false 		? ", '".$curriculum."' " 	: "" )."
				".( $publications !== false 	? ", '".$publications."' " 	: "" )."
			)";
			$re = $this->_query($query);
		}
		return $re;
	}

	//-----------------------------------------------------------------------------------//
	//- statistic from lms --------------------------------------------------------------//
	//-----------------------------------------------------------------------------------//

	/**
	 * retrive the user statistic in the lms
	 */
	function getUserCourseStat($id_user) {

		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

		$stats = array();

		$c_lang 	=& DoceboLanguage::CreateInstance('course', 'lms');
		$lang =& DoceboLanguage::createInstance('course', 'lms');

		$id_courses = array();
		$query_course_user = "
		SELECT c.idCourse, c.code, c.name, c.status,
			cu.status, cu.date_inscr, cu.date_first_access, cu.date_complete
		FROM  ".$GLOBALS['prefix_lms']."_courseuser AS cu
			JOIN ".$GLOBALS['prefix_lms']."_course AS c
		WHERE cu.idCourse = c.idCourse
			AND cu.idUser = '".$id_user."'";
		$re_course_user = sql_query($query_course_user);
		while(list($id_c, $code, $name, $status,
				$status_user, $date_inscr, $date_first_access, $date_complete) =  sql_fetch_row($re_course_user) ) {

			$id_courses[] = $id_c;
			$stats[$id_c]['course_name'] 	= $name;
			$stats[$id_c]['course_code'] 	= $code;

			$course_status = array(
				CST_PREPARATION => $c_lang->def('_CST_PREPARATION', 'course', 'lms'),
				CST_AVAILABLE 	=> $c_lang->def('_CST_AVAILABLE', 'course', 'lms'),
				CST_EFFECTIVE 	=> $c_lang->def('_CST_CONFIRMED', 'course', 'lms'),
				CST_CONCLUDED 	=> $c_lang->def('_CST_CONCLUDED', 'course', 'lms'),
				CST_CANCELLED 	=> $c_lang->def('_CST_CANCELLED', 'course', 'lms')
			);

			$stats[$id_c]['course_status'] = $course_status[$status];

			$cman = new CourseSubscribe_Manager();
			$arr_status = $cman->getUserStatus();
			$stats[$id_c]['user_status'] = isset($arr_status[$status_user]) ? $arr_status[$status_user] : "";

			$stats[$id_c]['date_inscr'] = $date_inscr;
			$stats[$id_c]['date_first_access'] = $date_first_access;
			$stats[$id_c]['date_complete'] = $date_complete;
			$stats[$id_c]['score_init'] = '';
			$stats[$id_c]['score_final'] = '';
			$stats[$id_c]['access_count'] 	= '';
			$stats[$id_c]['access_time'] 	= '';
			$stats[$id_c]['access_last'] 	= '';
			$stats[$id_c]['point_do']		= '';
		}
		//recover start and final score
		require_once($GLOBALS['where_lms'].'/lib/lib.orgchart.php');
		$org_man = new OrganizationManagement(false);

		require_once($GLOBALS['where_lms'].'/lib/lib.coursereport.php');
		$rep_man = new CourseReportManager();
		$score_course = $rep_man->getUserFinalScore(array($id_user));

		$score_start = $org_man->getStartObjectScore(array($id_user), $id_courses);
		$score_final = $org_man->getFinalObjectScore(array($id_user), $id_courses);
		while(list(,$id_c) = each($id_courses)) {

			if(isset($stats[$id_c])) {

			$stats[$id_c]['score_init'] = ( isset($score_start[$id_c][$id_user])  && $score_start[$id_c][$id_user]['max_score']
				? $score_start[$id_c][$id_user]['score'].' / '.$score_start[$id_c][$id_user]['max_score']
				: '' );;
			$stats[$id_c]['score_final'] = ( isset($score_final[$id_c][$id_user]) && $score_final[$id_c][$id_user]['max_score']
				? $score_final[$id_c][$id_user]['score'].' / '.$score_final[$id_c][$id_user]['max_score']
				: '' );
			}

			$point_do 	= ( isset($score_course[$id_user][$id_c]) ? $score_course[$id_user][$id_c]['score'] : '' );
			$point_max = ( isset($score_course[$id_user][$id_c]) ? $score_course[$id_user][$id_c]['max_score'] : '' );
			$stats[$id_c]['point_do'] = ($point_do !== '' ? number_format($point_do, 2).' / '.number_format($point_max, 2) : '');
		}

		$query = "
		SELECT idCourse, COUNT(*), SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)), MAX(lastTime)
		FROM ".$GLOBALS['prefix_lms']."_tracksession
		WHERE idUser = '".$id_user."'
		GROUP BY idCourse ";
		$re_time = sql_query($query);
		while(list($id_c, $session_num, $time_num, $last_num) = sql_fetch_row($re_time)) {

			if(isset($stats[$id_c])) {

				$stats[$id_c]['access_count'] 	= $session_num;
				$stats[$id_c]['access_time'] 	= $time_num;
				$stats[$id_c]['access_last'] 	= $last_num;

			}
		}

		return $stats;
	}


  //competences ----------------------------------------------------------------

  function getUserCompetences($id_user) {
    $cmodel = new CompetencesAdm();
		$scores = $cmodel->getUserCompetences($id_user, true);

		if (is_array($scores)) {
			$info = $cmodel->getCompetencesInfo(array_keys($scores));
		} else {
			$info = array();
		}
		$output = array();
		$lang_code = getLanguage();

		foreach ($info as $id_competence => $cdata) {
			$obj = new stdClass();
			$obj->id_competence = $id_competence;
			$obj->name = $cdata->langs[$lang_code]['name'];
			$obj->id_category = $cdata->id_category;
			$obj->typology = $cdata->typology;
			$obj->type = $cdata->type;
			$obj->score = $scores[$id_competence]->score_got;
			$obj->required = property_exists($scores[$id_competence], 'required') && $scores[$id_competence]->required ? true : false;
			$obj->gap = property_exists($scores[$id_competence], 'gap') ? (int)$scores[$id_competence]->gap : false;

			$output[$id_competence] = $obj;
		}

		return $output;
  }


	//functional roles -----------------------------------------------------------

  function getUserFunctionalRoles($id_user) {
    $fmodel = new FunctionalrolesAdm();
		$roles = $fmodel->getUserFunctionalRoles($id_user, true);

		if (is_array($roles)) {
			$info = $fmodel->getFunctionalRolesInfo(array_keys($roles));
		} else {
			$info = array();
		}
		$output = array();
		$lang_code = getLanguage();

		foreach ($info as $id_fncrole => $fdata) {
			$obj = new stdClass();
			$obj->id_fncrole = $id_fncrole;
			$obj->name = $fdata->langs[$lang_code]['name'];
			$obj->competences_obtained = $roles[$id_fncrole]->competences_obtained;
			$obj->competences_required = $roles[$id_fncrole]->competences_required;

			$output[$id_fncrole] = $obj;
		}

		return $output;
  }


	//groups and orgchart folders list -------------------------------------------

	function getUserGroupsList($id_user) {
		if ($id_user <= 0) return false;

		$umodel = new UsermanagementAdm();

		return array(
			'folders' => $umodel->getUserFolders($id_user),
			'groups' => $umodel->getUserGroups($id_user)
		);
	}


}

?>