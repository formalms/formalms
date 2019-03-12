<?php
namespace Plugin\GoogleAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');

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



use OAuth\OAuth2\Service\Google;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use Get;
use Lang;
use Form;
class Authentication extends \PluginAuthentication implements \PluginAuthenticationInterface {
    
    public static function getLoginGUI() {

        $form = '';
        if(isset($_SESSION['social'])) {
            
            if($_SESSION['social']['plugin'] == Plugin::getName()) {

                $form =   '<i class="fa fa-google"></i>' . " "
                        . Lang::t("_YOU_ARE_CONNECTING_SOCIAL_ACCOUNT", "social")
                        . " <b>" . ($_SESSION['social']['data']['name'] != "" ? $_SESSION['social']['data']['name'] : $_SESSION['social']['data']['email']) . "</b>"
                        . Form::openForm("cancel_social", Get::rel_path("base"))
                          . Form::openButtonSpace()
                              . Form::getButton("cancel", "cancel_social", Lang::t("_CANCEL", "standard"))
                          . Form::closeButtonSpace()
                        . Form::closeForm();
            }
        } else {
        
            $google_service = self::_getService();

            $url = $google_service->getAuthorizationUri();

            $form =  "<a href='" . $url . "'>"
				    . '<i class="fa fa-google"></i>'
					. "</a>";
        }

        return [
            'name' => 'GoogleAuth',
            'type' => self::AUTH_TYPE_SOCIAL,
            'form' => $form
        ];
    }
    
    public static function getUserFromLogin() {
        
        $error  = Get::req("error", DOTY_STRING, false);
        $code   = Get::req("code", DOTY_STRING, false);
        
        if($error || !$code) return UNKNOWN_SOCIAL_ERROR;
        
        $google_service = self::_getService();
        
        try {
            
            $google_service->requestAccessToken($code);        
            $user_info = json_decode($google_service->request("https://www.googleapis.com/oauth2/v2/userinfo"), true);
        } catch(Exception $e){
            
            return UNKNOWN_SOCIAL_ERROR;
        }
        
        if(empty($user_info['id'])) return EMPTY_SOCIALID;
        
        $user = \DoceboUser::createDoceboUserFromField("google_id", $user_info['id'], "public_area");
        
        if(!$user) {
            
            $_SESSION['social'] = array(
                'plugin'    => Plugin::getName(),
                'data'      => $user_info
            );
            return USER_NOT_FOUND;
        }
        
        return $user;
    }
    
    public static function setSocial($id) {
        
        $query  = " UPDATE %adm_user"
                . " SET google_id = '" . $id . "'"
                . " WHERE idst=" . \Docebo::user()->getIdSt();
        
        sql_query($query);
    }
    
    private static function _getService() {
                
        $serviceFactory = new \OAuth\ServiceFactory();
        
        $storage = new Session(false);

        $credentials = new Credentials(
            Get::sett("google.oauth_key"),
            Get::sett("google.oauth_secret"),
            Get::abs_path() . "index.php?r=" . _login_ . "&plugin=".Plugin::getName()
        );
        
        return $serviceFactory->createService("google", $credentials, $storage, array("userinfo_email"));
    }
}