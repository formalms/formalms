<?php if (!defined('IN_FORMA')) { die('You can\'t access!'); }

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
\ ======================================================================== */

use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

$social =new Social();
$social->includeGoogleLib();

$client_id = Get::sett('social_google_client_id');
$client_secret = Get::sett('social_google_secret');
//$redirect_uri = Get::sett('url').'index.php?modname=login&op=google_login';
$protocol = (    (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' )
				 or (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
				 or (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == 'on') ) ? 'https' : 'http' ;

$redirect_uri = $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'?modname=login&op=google_login';

try{
	$serviceFactory = new \OAuth\ServiceFactory();
	$storage = new Session(false);
	$credentials = new Credentials(
			$client_id,
			$client_secret,
			$redirect_uri
	);

	$googleService = $serviceFactory->createService('google', $credentials, $storage, array('userinfo_email')); //, 'userinfo_profile'

	// google login
	// 1. no params $_REQUEST or $_REQUEST['connect'] -> GOTO URL AUTH OR CONNECT GOOGLE ACCOUNT
	// 2. $_REQUEST['code'] -> RETURN OK FROM GOOGLE AUTH
	// 3. $_REQUEST['error'] -> RETURN CANCEL FROM GOOGLE AUTH

	switch(TRUE){
		// 1. no params $_REQUEST -> GOTO URL AUTH
		case (!isset($_REQUEST['code']) && !isset($_REQUEST['error']) || isset($_REQUEST['connect'])):
			$authUrl = $googleService->getAuthorizationUri();
			header('Location: ' . $authUrl);
			die();
			break;
			// 2. $_REQUEST['code'] -> RETURN OK FROM GOOGLE AUTH
		case (isset($_REQUEST['code'])):
			$token = $googleService->requestAccessToken($_GET['code']);

			$objUserInfo = json_decode($googleService->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);

			if (!empty($objUserInfo["email"])) {
				if (Docebo::user()->isAnonymous()) { // sign in the user
					$user = DoceboUser::createDoceboUserFromField('google_id', $objUserInfo["email"], 'public_area');
					if ($user) {
						DoceboUser::setupUser($user);
						Util::jump_to('index.php?r=lms/elearning/show');
					}
					else {
						socialConnectLogin($objUserInfo["email"], 'google');
						return;
					}
				}
				else { // user is already logged in, so connect the account with user
					$res=$social->connectAccount('google', $objUserInfo["email"]);
					if($res==true){
						Util::jump_to(_folder_lms_.'/index.php?'.'feedback_code=_SOCIALCONNECTOK&feedback_type=inf&feedback_extra='.$objUserInfo["email"]);
					}
					else{
						Util::jump_to(_folder_lms_.'/index.php?'.'feedback_code=_SOCIALCONNECTKO&feedback_type=err&feedback_extra='.$objUserInfo["email"]);
					}
				}
			}
			else {
				// no mail in fields
				Util::jump_to('index.php?access_fail=4');
			}
			break;
			// 3. $_REQUEST['error'] -> RETURN CANCEL FROM GOOGLE AUTH
		case (isset($_REQUEST['error']) && $_REQUEST['error'] == 'access_denied'):
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
