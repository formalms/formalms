<?php
use OAuth\OAuth2\Service\Facebook;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;

function reg_with_fb() {
  $_SESSION['fb_from'] = "register";

  $social =new Social();
  $social->includeFacebookLib();
  
  $client_id = Get::sett('social_fb_api');
  $client_secret = Get::sett('social_fb_secret');
  $redirect_uri = Get::sett('url').'index.php?modname=login&op=facebook_login';
  
	$serviceFactory = new \OAuth\ServiceFactory();
	$storage = new Session(false);
	$credentials = new Credentials(
			$client_id,
			$client_secret,
			$redirect_uri
	);

	$facebookService = $serviceFactory->createService('facebook', $credentials, $storage, array()); //, 'userinfo_profile'
	$authUrl = $facebookService->getAuthorizationUri();
	header('Location: ' . $authUrl);
	die();
}


function setFbRegData($data) {
	$user_id =strtolower(preg_replace('/[\\W]/', '_', $data['first_name'])).
		'.'.strtolower(preg_replace('/[\\W]/', '_', $data['last_name']));

	$_POST['register']['userid']=$user_id;
	$_POST['register']['firstname']=$data['first_name'];
	$_POST['register']['lastname']=$data['last_name'];
	$_POST['register']['email']=$data['email'];
}


function getFbRegisterBox() {
	$res ='';
	$lang=& DoceboLanguage::createInstance('login', 'cms');

	$res ='<div class="container-feedback"><span class="ico-sprite fd_info"><span></span></span>
		<b>'.$lang->def('_REGISTER_WITH_FACEBOOK').'</b>:
		<a href="index.php?modname=login&amp;op=reg_with_fb">'.
		Get::img('social/bt_fConnect.png', $lang->def('_REGISTER_WITH_FACEBOOK')).
		'</a></div>';
	
	return $res;
}


?>