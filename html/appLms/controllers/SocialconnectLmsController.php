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

class SocialConnectLmsController extends LmsController {

	public $name = 'socialconnect';

	protected $_default_action = 'show';
	protected $json;
	protected $conf;


	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();

		//require_once(_base_.'/addons/social/config_docebo.php');
		//require_once(_base_.'/addons/social/class.fblinkedtwit.php');

    //$fblinkedtwit   =   new FbLinkedTwit();

		//require_once(_base_.'/addons/social/functions.php');
		$this->conf =Social::getConfig();
		Social::includeTwitterLib();
	}


	public function show() {

		/* $this->render('show', array(
			
		)); */
	}


	public function auth() {  return false;
		require_once(_base_.'/lib/lib.preference.php');
		$preference = new UserPreferences(getLogUserId());

		$pl =Get::gReq('pl', DOTY_STRING);

		switch($pl) {
			case "twitter": {
			/*	include_once  $GLOBALS['social_config']['twitter_library_path'];
				echo $GLOBALS['social_config']['twitter_consumer'];

				$to = new TwitterOAuth($GLOBALS['social_config']['twitter_consumer'], $GLOBALS['social_config']['twitter_secret']);
				$tok = $to->getRequestToken();

				$request_link = $to->getAuthorizeURL($tok);

				$_SESSION['twit_oauth_request_token']        = $token = $tok['oauth_token'];
				$_SESSION['twit_oauth_request_token_secret'] = $tok['oauth_token_secret'];

				header("Location: $request_link");
				exit; */

				$user_pref =array();
				$user_pref['twitter_key']=$preference->getPreference('social.twitter_key');
				$user_pref['twitter_secret']=$preference->getPreference('social.twitter_secret');

				if (empty($user_pref['twitter_key'])) {
					$twitter =new EpiTwitter($this->conf['twitter_key'], $this->conf['twitter_secret']);
					$aUrl = $twitter->getAuthenticateUrl();

					header("location: ".$aUrl);
					exit;
				}
				else {
					$twitter =new EpiTwitter(
						$this->conf['twitter_key'],
						$this->conf['twitter_secret'],
						$user_pref['twitter_key'],
						$user_pref['twitter_secret']
					);

					// debug:
					$userInfo =$twitter->get_accountVerify_credentials();
					echo "Logged in as: ".$userInfo->screen_name;
				}

			} break;

			case "linkedin": {

				$user_pref =array();
				$user_pref['linkedin_key']=$preference->getPreference('social.linkedin_key');
				$user_pref['linkedin_secret']=$preference->getPreference('social.linkedin_secret');

				if (empty($user_pref['linkedin_key'])) {
					$linkedin =new EpiLinkedin($this->conf['linkedin_key'], $this->conf['linkedin_secret']);
					$aUrl = $linkedin->getAuthenticateUrl();

					header("location: ".$aUrl);
					exit;
				}
				else { die("mm");
					$linkedin =new EpiLinkedin(
						$this->conf['linkedin_key'],
						$this->conf['linkedin_secret'],
						$user_pref['linkedin_key'],
						$user_pref['linkedin_secret']
					);

					// debug:
					$userInfo =$linkedin->get_accountVerify_credentials();
					echo "Logged in as: ".$userInfo->screen_name;
				}

			} break;
		}
	}


	public function callback() {   return false;

		$pl =Get::gReq('pl', DOTY_STRING);

		switch($pl) {
			case "twitter": {

				$twitter =new EpiTwitter($this->conf['twitter_key'], $this->conf['twitter_secret']);				

				$oauth_token =Get::gReq('oauth_token', DOTY_STRING);
				$twitter->setToken($oauth_token);

				$resp = $twitter->getAccessToken();

				echo $resp->oauth_token."<br />";
				echo $resp->oauth_token_secret."<br />";
				var_dump($resp->oauth_callback_confirmed); echo "<br />";

				$twitter->setToken($resp->oauth_token, $resp->oauth_token_secret);

				require_once(_base_.'/lib/lib.preference.php');
				$preference = new UserPreferences(getLogUserId());
				$preference->setPreference('social.twitter_key', $resp->oauth_token);
				$preference->setPreference('social.twitter_secret', $resp->oauth_token_secret);

				$statusText = 'Prova 01';
				//$res =$twitter->post('/statuses/update.json', array('status' => $statusText));
				//echo $res->text;

			} break;
		}
	}


	public function disconnect() {
		$field =false;
		$network =Get::gReq('network', DOTY_STRING);

		$social =new Social();
		$social->disconnectAccount($network);

		Util::jump_to('index.php?r=lms/elearning/show');
	}





}