<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

namespace Plugin\GoogleAuth;

defined('IN_FORMA') or exit('Direct access is forbidden.');

use Form;
use Lang;
use OAuth\Common\Consumer\Credentials;
use OAuth\Common\Storage\Session;

class Authentication extends \PluginAuthentication implements \PluginAuthenticationWithRedirectInterface
{
    public static function getLoginGUI()
    {
        $session = self::$session;
        $social = $session->get('social');
        $form = '';
        if (isset($social)) {
            if ($social['plugin'] == Plugin::getName()) {
                $form = '<i class="fa fa-google"></i>' . ' '
                        . Lang::t('_YOU_ARE_CONNECTING_SOCIAL_ACCOUNT', 'social')
                        . ' <b>' . ($social['data']['name'] != '' ? $social['data']['name'] : $social['data']['email']) . '</b>'
                        . Form::openForm('cancel_social', FormaLms\lib\Get::rel_path('base'))
                          . Form::openButtonSpace()
                              . Form::getButton('cancel', 'cancel_social', Lang::t('_CANCEL', 'standard'))
                          . Form::closeButtonSpace()
                        . Form::closeForm();
            }
        } else {
            $google_service = self::_getService();

            $url = $google_service->getAuthorizationUri();

            $form = "<a href='" . $url . "'>"
                    . '<i class="fa fa-google"></i>'
                    . '</a>';
        }

        return [
            'name' => 'GoogleAuth',
            'type' => self::AUTH_TYPE_SOCIAL,
            'form' => $form,
        ];
    }

    public static function getUserFromLogin()
    {
        $error = FormaLms\lib\Get::req('error', DOTY_STRING, false);
        $code = FormaLms\lib\Get::req('code', DOTY_STRING, false);

        if ($error || !$code) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        $google_service = self::_getService();

        try {
            $google_service->requestAccessToken($code);
            $user_info = json_decode($google_service->request('https://www.googleapis.com/oauth2/v2/userinfo'), true);
        } catch (Exception $e) {
            return UNKNOWN_SOCIAL_ERROR;
        }

        if (empty($user_info['id'])) {
            return EMPTY_SOCIALID;
        }

        $user = \DoceboUser::createDoceboUserFromField('google_id', $user_info['id'], 'public_area');

        if (!$user) {
            (self::$session)->set('social', ['plugin' => Plugin::getName(),
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
                . " SET google_id = '" . $id . "'"
                . ' WHERE idst=' . \Docebo::user()->getIdSt();

        sql_query($query);
    }

    private static function _getService()
    {
        $serviceFactory = new \OAuth\ServiceFactory();

        $storage = new Session(false);

        $credentials = new Credentials(
            FormaLms\lib\Get::sett('google.oauth_key'),
            FormaLms\lib\Get::sett('google.oauth_secret'),
            FormaLms\lib\Get::abs_path() . 'index.php?r=' . _login_ . '&plugin=' . Plugin::getName()
        );

        return $serviceFactory->createService('google', $credentials, $storage, ['userinfo_email']);
    }
}
