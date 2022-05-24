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

namespace Plugin\FacebookAuth;

defined('IN_FORMA') or exit('Direct access is forbidden.');

use Form;
use Get;
use Lang;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;
use OAuth\OAuth2\Service\Facebook;

class Authentication extends \PluginAuthentication implements \PluginAuthenticationWithRedirectInterface
{
    public static function getLoginGUI($redirect = '')
    {
        $form = '';
        $session = self::$session;
        $social = $session->get('social');
        if (isset($social)) {
            if ($social['plugin'] == Plugin::getName()) {
                $form = Forma\lib\Get::img('social/facebook-24.png') . ' '
                        . Lang::t('_YOU_ARE_CONNECTING_SOCIAL_ACCOUNT', 'social')
                        . ' <b>' . $social['data']['name'] . '</b>'
                        . Form::openForm('cancel_social', Forma\lib\Get::rel_path('base'))
                          . Form::openButtonSpace()
                              . Form::getButton('cancel', 'cancel_social', Lang::t('_CANCEL', 'standard'))
                          . Form::closeButtonSpace()
                        . Form::closeForm();
            }
        } else {
            $facebook_service = self::_getService();

            $url = $facebook_service->getAuthorizationUri();

            $form = "<a href='" . $url . "'>"
                    . '<i class="fa fa-facebook"></i>'
                      . '</a>';
        }

        return [
            'name' => 'FacebookAuth',
            'type' => self::AUTH_TYPE_SOCIAL,
            'form' => $form,
            ];
    }

    public static function getUserFromLogin()
    {
        $error = Forma\lib\Get::req('error', DOTY_STRING, false);
        $code = Forma\lib\Get::req('code', DOTY_STRING, false);

        if ($error || !$code) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        $facebook_service = self::_getService();

        try {
            $facebook_service->requestAccessToken($code);
            $user_info = json_decode($facebook_service->request('/me'), true);
        } catch (Exception $e) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        if (empty($user_info['id'])) {
            return EMPTY_SOCIALID;
        }

        $user = \DoceboUser::createDoceboUserFromField('facebook_id', $user_info['id'], 'public_area');

        if (!$user) {
            (self::$session)->set('social',['plugin' => Plugin::getName(),
                                            'data' => $user_info,
                ]);
            (self::$session)->save();
            return USER_NOT_FOUND;
        }

        return $user;
    }

    public static function setSocial($id)
    {
        $query = ' UPDATE %adm_user'
                . " SET facebook_id = '" . $id . "'"
                . ' WHERE idst=' . \Docebo::user()->getIdSt();

        sql_query($query);
    }

    private static function _getService()
    {
        $serviceFactory = new \OAuth\ServiceFactory();

        $storage = new Session(false);

        $credentials = new Credentials(
            Forma\lib\Get::sett('facebook.oauth_key'),
            Forma\lib\Get::sett('facebook.oauth_secret'),
            Forma\lib\Get::abs_path() . 'index.php?r=' . urlencode(_login_) . '&plugin=' . Plugin::getName()
        );

        return $serviceFactory->createService('facebook', $credentials, $storage, []);
    }
}
