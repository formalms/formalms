<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace Plugin\TwitterAuth;

defined('IN_FORMA') or exit('Direct access is forbidden.');

use Docebo;
use DoceboUser;
use Exception;
use Form;
use Lang;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\OAuth1\Service\Twitter;
use FormaLms\lib\Get;

class Authentication extends \PluginAuthentication implements \PluginAuthenticationWithRedirectInterface
{
    public static function getLoginGUI($redirect = '')
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $social = $session->get('social');
        if (isset($social)) {
            if ($social['plugin'] == Plugin::getName()) {
                return Get::img('social/twitter-24.png') . ' '
                        . Lang::t('_YOU_ARE_CONNECTING_SOCIAL_ACCOUNT', 'social')
                        . ' <b>' . $social['data']['name'] . '</b>'
                        . Form::openForm('cancel_social', Get::rel_path('base'))
                          . Form::openButtonSpace()
                              . Form::getButton('cancel', 'cancel_social', Lang::t('_CANCEL', 'standard'))
                          . Form::closeButtonSpace()
                        . Form::closeForm();
            }
        } else {
            try {
                $twitter_service = self::_getService();

                $token = $twitter_service->requestRequestToken();
                $url = $twitter_service->getAuthorizationUri(['oauth_token' => $token->getRequestToken()]);
            } catch (Exception $e) {
                return;
            }

            return "<a href='" . $url . "'>"
                  // . FormaLms\lib\Get::img("social/twitter-24.png")
                    . '<i class="fa fa-twitter"></i>'
                      . '</a>';
        }
    }

    public static function getUserFromLogin()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        
        $oauth_token = Get::req('oauth_token', DOTY_STRING, false);
        $oauth_verifier = Get::req('oauth_verifier', DOTY_STRING, false);

        if (!$oauth_token || !$oauth_verifier) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        $twitter_service = self::_getService();

        try {
            $token = $twitter_service->getStorage()->retrieveAccessToken('Twitter');
            $twitter_service->requestAccessToken(
                $oauth_token,
                $oauth_verifier,
                $token->getRequestTokenSecret()
            );

            $user_info = json_decode($twitter_service->request('account/verify_credentials.json'));
        } catch (Exception $e) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        if (empty($user_info->id)) {
            return EMPTY_SOCIALID;
        }

        $user = DoceboUser::createDoceboUserFromField('twitter_id', $user_info->id, 'public_area');

        if (!$user) {
            ($session)->set('social', ['plugin' => Plugin::getName(),
                                'data' => $user_info,
                    ]);
            ($session)->save();

            return USER_NOT_FOUND;
        }

        return $user;
    }

    public static function setSocial($id)
    {
        $query = ' UPDATE %adm_user'
                . " SET twitter_id = '" . $id . "'"
                . ' WHERE idst=' . Docebo::user()->getIdSt();

        sql_query($query);
    }

    private static function _getService()
    {
        $serviceFactory = new \OAuth\ServiceFactory();

        $storage = new Session(false);

        $credentials = new Credentials(
            Get::sett('twitter.oauth_key'),
            Get::sett('twitter.oauth_secret'),
            Get::abs_path() . 'index.php?r=' . _login_ . '&plugin=' . Plugin::getName()
        );

        return $serviceFactory->createService('twitter', $credentials, $storage);
    }
}
