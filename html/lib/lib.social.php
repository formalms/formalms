<?php defined("IN_FORMA") or die('Direct access is forbidden.');


class Social {

	private $_facebook_obj;
	private $_userinfo;

	public function  __construct() {

	}


	protected function getAvailNetworks() {
		return array(
			'twitter',
			'linkedin',
			'facebook',
			'google',
			//'google_apps',
		);
	}


	public function getConfig() {
		$config = array();

		$config['twitter_key'] = Get::sett('social_twitter_consumer');
		$config['twitter_secret'] = Get::sett('social_twitter_secret');
		$config['linkedin_key'] = Get::sett('social_linkedin_access');
		$config['linkedin_secret'] = Get::sett('social_linkedin_secret');
		$config['fb_api'] = Get::sett('social_fb_api');
		$config['fb_secret'] = Get::sett('social_fb_secret');

		return $config;
	}


	public function isActive($network_code) {
		$res =false;

		$cfg =$this->getConfig();

		switch ($network_code) {
			case 'twitter': {
				$res =(Get::sett('social_twitter_active') == 'on' &&
				       !empty($cfg['twitter_key']) &&
				       !empty($cfg['twitter_secret']));
			} break;
			case 'linkedin': {
				$res =(Get::sett('social_linkedin_active') == 'on' &&
				       !empty($cfg['linkedin_key']) &&
				       !empty($cfg['linkedin_secret']));
			} break;
			case 'facebook': {
				$res =(Get::sett('social_fb_active') == 'on' &&
				       !empty($cfg['fb_api']) &&
				       !empty($cfg['fb_secret']));
			} break;
			case 'google': {
				$res =(Get::sett('social_google_active') == 'on');
			} break;
			case 'google_apps': {
				$res =false;
			} break;
		}


		return $res;
	}


	public function connectedToUser($network_code, $user_idst=false) {
		$res =false;
		$user_idst =($user_idst > 0 ? $user_idst : getLogUserId());

		if (!isset($this->_userinfo[$user_idst])) {
			$acl_man =Docebo::user()->getAclManager();
			$this->_userinfo[$user_idst] =$acl_man->getUser($user_idst, false);
		}

		switch ($network_code) {
			case 'twitter': {
				$res =(!empty($this->_userinfo[$user_idst][ACL_INFO_TWITTER_ID]));
			} break;
			case 'linkedin': {
				$res =(!empty($this->_userinfo[$user_idst][ACL_INFO_LINKEDIN_ID]));
			} break;
			case 'facebook': {
				$res =(!empty($this->_userinfo[$user_idst][ACL_INFO_FACEBOOK_ID]));
			} break;
			case 'google': {
				$res =(!empty($this->_userinfo[$user_idst][ACL_INFO_GOOGLE_ID]));
			} break;
			case 'google_apps': {
				$res =true;
			} break;
		}

		return $res;
	}


	/**
	 * return true if there is at least one network active
	 * (then social features will be enabled)
	 */
	public function enabled() {
		$res =false;

		foreach($this->getAvailNetworks() as $network) {
			if ($this->isActive($network)) $res =true;
		}

		return $res;
	}


	public function allConnected($user_idst=false) {
		$res =true;
		$user_idst =($user_idst > 0 ? $user_idst : getLogUserId());

		foreach($this->getAvailNetworks() as $network) {
			if (!$this->connectedToUser($network, $user_idst)) $res =false;
		}

		return $res;
	}


	public function someConnected($user_idst=false) {
		$res =false;
		$user_idst =($user_idst > 0 ? $user_idst : getLogUserId());

		foreach($this->getAvailNetworks() as $network) {
			if ($this->connectedToUser($network, $user_idst)) $res =true;
		}

		return $res;
	}


	public function includeTwitterLib() {
		include_once(_base_ . '/addons/social/oauth/EpiOAuth.php');
		include_once(_base_ . '/addons/social/oauth/EpiTwitter.php');
		include_once(_base_ . '/addons/social/oauth/EpiCurl.php');
	}


	public function includeLinkedinLib() {
		include_once(_base_ . '/addons/social/oauth_lib/OAuth.php');
	}

	public function includeGoogleLib() {
		include_once(_base_ . '/addons/social/OAuthLib/vendor/autoload.php');
	}


	public function includeFacebookLib() {
		include_once(_base_ . '/addons/social/OAuthLib/vendor/autoload.php');
	}


	public function includeOpenidLib() {
		// Set the default separator to '&', used by http_build_query of openid.php
		ini_set('arg_separator.output', '&');
		include_once(_base_ . '/addons/social/openid/openid.php');
	}


	public function getFacebookObj() {
		$conf =$this->getConfig();

		if (!$this->_facebook_obj) {
			$res = new Facebook(array(
					'appId'=>$conf['fb_api'],
					'secret'=>$conf['fb_secret'],
					'cookie'=>true,
				));

			Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
		}
		else {
			$res =$this->_facebook_obj;
		}

		return $res;
	}


	//TODO: check if there are better ways of achieving this:
	public function checkLinkedinAuth($linkedin_key, $linkedin_secret, $user_linkedin_key, $user_linkedin_secret) {
		Social::includeLinkedinLib();

		$res =false;
		$url = "https://api.linkedin.com/v1/people/~";
		$signature = new OAuthSignatureMethod_HMAC_SHA1();

		$consumer = new OAuthConsumer($linkedin_key, $linkedin_secret, NULL);
		$token = new OAuthConsumer($user_linkedin_key, $user_linkedin_secret, 1);

		$request = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $url);
		$request->sign_request($signature, $consumer, $token);
		$header = $request->to_header("https://api.linkedin.com");
		$response =  $this->_linkedin_http_request($url, $header);
		parse_str($response);
		if(!strpos($response, '</error-code>') === FALSE) {
			$res =false;
		} else {
			$res =true;
		}

		return $res;
	}


	public function linkedinRequestToken($linkedin_key, $linkedin_secret) {
		Social::includeLinkedinLib();

		$res =false;
		$base_url = "https://api.linkedin.com/uas/oauth";
		$consumer = new OAuthConsumer($linkedin_key, $linkedin_secret, NULL);
		$signature = new OAuthSignatureMethod_HMAC_SHA1();
		$random = md5(rand());
		$callback = Get::sett('url').'index.php?modname=login&op=linkedin_login&back=1';
		$url = $base_url . "/requestToken";

		$request = OAuthRequest::from_consumer_and_token($consumer, NULL, 'POST', $url);
		$request->set_parameter("oauth_callback", $callback);
		$request->sign_request($signature, $consumer, NULL);
		$header = $request->to_header();
		$response = $this->_linkedin_http_request($url, $header, 'token_request');
		parse_str($response, $oauth);

		if(isset($oauth['oauth_problem']) && !empty($oauth['oauth_problem'])){
			//echo('There was a problem with the configuration of LinkedIn on this website. Please try again later.');
			Util::fatal('Linkedin report error: '.$oauth['oauth_problem']);
			return $res;
		}

		if ($oauth['oauth_token']) {
			$_SESSION['user_linkedin_key']=$oauth['oauth_token'];
			$_SESSION['user_linkedin_secret']=$oauth['oauth_token_secret'];
		}

		header('location: '.$base_url . '/authorize?oauth_token=' . $oauth['oauth_token']);
	}


	public function linkedinAccess($linkedin_key, $linkedin_secret) {
		Social::includeLinkedinLib();

		// must be set by Social::linkedinRequestToken
		$user_linkedin_key =$_SESSION['user_linkedin_key'];
		$user_linkedin_secret =$_SESSION['user_linkedin_secret'];

		$res =false;
		$base_url = "https://api.linkedin.com/uas/oauth";
		$consumer = new OAuthConsumer($linkedin_key, $linkedin_secret, NULL);
		$signature = new OAuthSignatureMethod_HMAC_SHA1();


		$url = $base_url . '/accessToken';
		$token = new OAuthConsumer($_REQUEST['oauth_token'], $user_linkedin_secret, 1);
		$request = OAuthRequest::from_consumer_and_token($consumer, $token, "POST", $url);
		$request->set_parameter("oauth_verifier", $_REQUEST['oauth_verifier']);
		$request->sign_request($signature, $consumer, $token);
		$header = $request->to_header();
		$response = $this->_linkedin_http_request($url, $header, 'token_request');
		parse_str($response, $oauth);


		if(isset($oauth['oauth_problem']) && !empty($oauth['oauth_problem'])){
			Util::fatal('Linkedin report error: '.$oauth['oauth_problem']);
			return $res;
		}


		if (isset($oauth['oauth_token'])) {
			// Update this with new authenticated token:
			$_SESSION['user_linkedin_key']=$oauth['oauth_token'];
			$_SESSION['user_linkedin_secret']=$oauth['oauth_token_secret'];

			$res =true;
		}

		return $res;
	}


	public function getLinkedinUserInfo($linkedin_key, $linkedin_secret, $user_linkedin_key=false, $user_linkedin_secret=false) {
		Social::includeLinkedinLib();

		$user_linkedin_key =(!empty($user_linkedin_key) ? $user_linkedin_key : $_SESSION['user_linkedin_key']);
		$user_linkedin_secret =(!empty($user_linkedin_secret) ? $user_linkedin_secret : $_SESSION['user_linkedin_secret']);

		$res =false;
		$base_url = "https://api.linkedin.com/v1/people/~";
		$url =$base_url.':(id,first-name,last-name,current-status)';
		$signature = new OAuthSignatureMethod_HMAC_SHA1();

		$consumer = new OAuthConsumer($linkedin_key, $linkedin_secret, NULL);
		$token = new OAuthConsumer($user_linkedin_key, $user_linkedin_secret, 1);

		$request = OAuthRequest::from_consumer_and_token($consumer, $token, "GET", $url);
		$request->sign_request($signature, $consumer, $token);
		$header = $request->to_header("https://api.linkedin.com");
		$response =  $this->_linkedin_http_request($url, $header);
		parse_str($response);
		if(!strpos($response, '</error-code>') === FALSE) {
			$res =false;
		} else {
			// TODO: parse xml!
			preg_match('/<id>([^<]*)<\\/id>/i', $response, $fake_parse);
			$res =array(
				'id'=>$fake_parse[1],
			);
		}

		return $res;
	}


	public function getGoogleUserInfo() {
		$res =array();

		$res['email']=Get::gReq('openid_ext1_value_contact_email', DOTY_STRING);
		$res['firstname']=Get::gReq('openid_ext1_value_namePerson_first', DOTY_STRING);
		$res['lastname']=Get::gReq('openid_ext1_value_namePerson_last', DOTY_STRING);

		return $res;
	}


	public function connectAccount($network, $id, $user_idst=false, $temp_user=false) {
		$res =false;
		$user_idst =($user_idst > 0 ? $user_idst : getLogUserId());

		switch ($network) {
			case "facebook": {
				$field ='facebook_id';
			} break;
			case "twitter": {
				$field ='twitter_id';
			} break;
			case "linkedin": {
				$field ='linkedin_id';
			} break;
			case "google": {
				$field ='google_id';
			} break;
		}

		if (!empty($field)) {
			$db = DbConn::getInstance();
			$qtxt ="UPDATE ".(!$temp_user ? '%adm_user' : '%adm_user_temp').
				" SET ".$field." = '".$id."' WHERE idst=".$user_idst." LIMIT 1";
			$res =$db->query($qtxt);
		}

		return $res;
	}


	public function disconnectAccount($network, $user_idst=false) {
		$res =false;
		$user_idst =($user_idst > 0 ? $user_idst : getLogUserId());

		switch ($network) {
			case "facebook": {
				$field ='facebook_id';
			} break;
			case "twitter": {
				$field ='twitter_id';
			} break;
			case "linkedin": {
				$field ='linkedin_id';
			} break;
			case "google": {
				$field ='google_id';
			} break;
		}

		if (!empty($field)) {
			$db = DbConn::getInstance();
			$qtxt ="UPDATE %adm_user SET ".$field." = NULL WHERE idst=".$user_idst." LIMIT 1";
			$res =$db->query($qtxt);
		}

		return $res;
	}



	// ---------------------------------------------------------------------------
	/* Code from the drupal linkedin integration
	 * Authors: Pascal Morin (bellesmanieres)
              Greg Harvey (greg.harvey)
	 * License: GPL V2
	 */

	function _linkedin_http_request($url, $header, $body = NULL) {

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array($header));
		curl_setopt($ch, CURLOPT_URL, $url);

		if ($body) {
			curl_setopt($ch, CURLOPT_POST, 1);
			if ($body == 'token_request') {
				curl_setopt($ch, CURLOPT_POSTFIELDS, '');
			} else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array($header, 'Content-Type: text/xml;charset=utf-8'));
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
			}
		}

		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	// ---------------------------------------------------------------------------


}

?>
