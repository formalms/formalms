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

if (Docebo::user()->isAnonymous()) die("You can't access!");

class ProfileLmsController extends LmsController {

	protected $db;
	protected $model;
	protected $json;
	protected $aclManager;

	protected $max_dim_avatar;

	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->model = new ProfileLms();
		$this->json = new Services_JSON();
		$this->aclManager = Docebo::user()->getAClManager();
		$this->max_dim_avatar = 150;
	}


	protected function _profileBackUrl()	{
		$id_user = Get::req('id_user', DOTY_INT, 0);
		$type = Get::req('type', DOTY_STRING, 'false');
		$from = Get::req('from', DOTY_INT, 0);
		$back_my_friend = Get::req('back', DOTY_INT, 0);
		if ($type !== 'false')
			if ($from == 0)
				return getBackUi('index.php?modname=profile&op=profile&id_user='.$id_user.'&ap=goprofile', Lang::t('_BACK', 'standard'));
			else
				return getBackUi('index.php?modname=myfiles&op=myfiles&working_area='.$type, Lang::t('_BACK', 'standard'));
		if ($back_my_friend)
			return getBackUi('index.php?modname=myfriends&op=myfriends', Lang::t('_BACK', 'standard'));
		return false;
	}

	public function show() {
		if (!defined("LMS")) {
			checkRole('/lms/course/public/profile/view', false);
		} else {
			checkPerm('view', false, 'profile', 'lms');
		}

		require_once(_lms_.'/lib/lib.lms_user_profile.php');

		$id_user = Docebo::user()->getIdST();
		$profile = new LmsUserProfile($id_user);
		$profile->init('profile', 'framework', 'r=lms/profile/show'/*&id_user'.(int)$id_user*/, 'ap');//'modname=profile&op=profile&id_user='.$id_user

		$_check = false;
		if (!defined("LMS")) {
			$_check = checkRole('/lms/course/public/profile/mod', true);
		} else {
			$_check = checkPerm('mod', true, 'profile', 'lms');
		}
		if ($_check) $profile->enableEditMode();

		//view part
		if(Get::sett('profile_only_pwd') == 'on') {

			echo $profile->getTitleArea();
			echo $profile->getHead();
			echo $profile->performAction(false, 'mod_password');
			echo $this->_profileBackUrl();
			echo $profile->getFooter();

		} else {

			echo $profile->getTitleArea();
			echo $profile->getHead();
			echo $profile->performAction();
			echo $this->_profileBackUrl();
			echo $profile->getFooter();

		}
	}

	function renewalpwd() {
		require_once(_base_.'/lib/lib.usermanager.php');
		$user_manager = new UserManager();

		$_title = "";
		$_error_message = "";
		$_content = "";

		$url = 'index.php?r=lms/profile/renewalpwd';//'index.php?modname=profile&amp;op=renewalpwd'

		if ($user_manager->clickSaveElapsed()) {
			$error = $user_manager->saveElapsedPassword();
			if ($error['error'] == true) {
				$res = Docebo::user()->isPasswordElapsed();
				
				if ($res == 2)
					$_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
				else
					$_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));

				$_error_message = $error['msg'];
				$_content = $user_manager->getElapsedPassword($url);

			} else {
				unset($_SESSION['must_renew_pwd']);
				//Util::jump_to('index.php?r=lms/profile/show');
				Util::jump_to('index.php');
			}

		} else {
				$_SESSION['must_renew_pwd'] = 1;
				$res = Docebo::user()->isPasswordElapsed();
				if ($res == 2) 
					$_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
				else
					$_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));
				$_content = $user_manager->getElapsedPassword($url);
				

		}
		
		//view part
		echo $_title.'<div class="std_block">'.$_error_message.$_content.'</div>';
	}

}


?>