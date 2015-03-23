<?php if (!defined('IN_FORMA')) { die('You can\'t access!'); }

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
\ ======================================================================== */

$social =new Social();
$social->includeOpenidLib();

try {
	if (!isset($_GET['openid_mode'])) {
		$openid = new LightOpenID;
		$openid->identity = 'https://www.google.com/accounts/o8/id';
		$openid->required =array('contact/email', 'namePerson/first', 'namePerson/last');
		header('Location: ' . str_replace('&amp;', '&', $openid->authUrl()));
	} elseif ($_GET['openid_mode'] == 'cancel') {
		Util::jump_to('index.php?access_fail=3');
	} else {
		$openid = new LightOpenID;
		$_GET['openid_return_to']=$_REQUEST['openid_return_to']; // to avoid having &amp; instead of &

		if ($openid->validate()) {
			$user_data =$social->getGoogleUserInfo();
			if (!empty($user_data['email'])) {

				if (Docebo::user()->isAnonymous()) { // sign in the user
					$user = DoceboUser::createDoceboUserFromField('google_id', $user_data['email'], 'public_area');
					if ($user) {
						DoceboUser::setupUser($user);

						Util::jump_to('index.php?r=lms/elearning/show');
					} else {
						//Util::jump_to('index.php?access_fail=2');
						socialConnectLogin($user_data['email'], 'google');
						return;
					}
				} else { // user is already logged in, so connect the account with user
					$social->connectAccount('google', $user_data['email']);
					Util::jump_to('index.php?r=lms/elearning/show');
					die();
				}

				print_r($user_data);
			} else {
				Util::jump_to('index.php?access_fail=2');
			}
		} else {
			Util::jump_to('index.php?access_fail=3');
		}
		die();
	}
} catch (ErrorException $e) {
	echo $e->getMessage();
}

