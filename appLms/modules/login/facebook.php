<?php



function reg_with_fb() {
	$social =new Social();
	$social->includeFacebookLib();
	$facebook =$social->getFacebookObj();

	$_SESSION['fb_from']='register';
	
	$loginUrl = $facebook->getLoginUrl(array(
		'req_perms'=>'email',
		'next'=>Get::sett('url').'index.php?modname=login&op=facebook_login',
	));
	
	session_write_close();
	header('location: '.$loginUrl);
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