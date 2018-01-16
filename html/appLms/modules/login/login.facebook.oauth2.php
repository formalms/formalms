<?php if (!defined('IN_FORMA')) { die('You can\'t access!'); }

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
\ ======================================================================== */

use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

$social =new Social();
$social->includeFacebookLib();

$client_id = Get::sett('social_fb_api');
$client_secret = Get::sett('social_fb_secret');
$redirect_uri = Get::sett('url').'index.php?modname=login&op=facebook_login';



try{
	$serviceFactory = new \OAuth\ServiceFactory();
	$storage = new Session(false);
    $credentials = new Credentials(
			$client_id,
			$client_secret,
			$redirect_uri
	);

	$facebookService = $serviceFactory->createService('facebook', $credentials, $storage, array()); //, 'userinfo_profile'

	// facebook login
	// 1. no params $_REQUEST or $_REQUEST['connect'] -> GOTO URL AUTH OR CONNECT FACEBOOK ACCOUNT
	// 2. $_REQUEST['code'] -> RETURN OK FROM FACEBOOK AUTH
	// 3. $_REQUEST['error'] -> RETURN CANCEL FROM FACEBOOK AUTH
	switch(TRUE){
		// 1. no params $_REQUEST -> GOTO URL AUTH
		case (!isset($_REQUEST['code']) && !isset($_REQUEST['error_code']) || isset($_REQUEST['connect'])):
			$authUrl = $facebookService->getAuthorizationUri();
			header('Location: ' . $authUrl);
			die();
			break;
			// 2. $_REQUEST['code'] -> RETURN OK FROM FACEBOOK AUTH
		case (isset($_REQUEST['code'])):
			$token = $facebookService->requestAccessToken($_GET['code']);

			$objUserInfo = json_decode($facebookService->request('/me'), true);

			if (!empty($objUserInfo["id"])) {
				if($_SESSION['fb_from'] == "register"){
					$_SESSION['fb_info']=$objUserInfo;
					Util::jump_to(Get::rel_path("base") . "/index.php?r=" . _register_);
				    die();
				}
				
				if (Docebo::user()->isAnonymous()) { // sign in the user
					$user = DoceboUser::createDoceboUserFromField('facebook_id', $objUserInfo["id"], 'public_area');
					if ($user) {
						DoceboUser::setupUser($user);
						Util::jump_to('index.php?r=lms/elearning/show');
					}
					else {
						socialConnectLogin($objUserInfo["id"], 'facebook');
						return;
					}
				}
				else { // user is already logged in, so connect the account with user
					$res=$social->connectAccount('facebook', $objUserInfo["id"]);
					if($res==true){
						Util::jump_to(_folder_lms_.'/index.php?'.'feedback_code=_SOCIALCONNECTOK&feedback_type=inf&feedback_extra='.$objUserInfo["id"]);
					}
					else{
						Util::jump_to(_folder_lms_.'/index.php?'.'feedback_code=_SOCIALCONNECTKO&feedback_type=err&feedback_extra='.$objUserInfo["id"]);
					}
				}
			}
			else {
				// no mail in fields
				Util::jump_to('index.php?access_fail=4');
			}
			break;
			// 3. $_REQUEST['error'] -> RETURN CANCEL FROM FACEBOOK AUTH
		case (isset($_REQUEST['error_code']) && $_REQUEST['error_code'] == '4201'):
			// error_message contiene la risposta User canceled the Dialog flow
			Util::jump_to('index.php?access_fail=6');
			break;
		default:
			break;
	}
}
catch(Exception $e){
// reset session auth code
	Util::jump_to('index.php?access_fail=5');
}
//finally{
//  die();
//}
